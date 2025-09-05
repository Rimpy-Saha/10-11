<?php

namespace Drupal\commerce_norreferral\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\user\Entity\User;

/**
 * Provides methods for ordercomplete..
 *
 * @package Drupal\commerce_norreferral
 * Class OrderCompleteSubscriber.
 */
class OrderCompleteSubscriber implements EventSubscriberInterface
{

  /**
   *  Entity type manager service instance.
   */
  protected $entityTypeManager;

  /**
   * Current user service instance.
   */
  protected $currentUser;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The user referral database service.
   *
   * @var \Drupal\commerce_norreferral\Database\UserReferralDatabase
   */
  protected $userReferralDatabase;


  /**
   * Constructor.
   *
   * @param obj $entity_type_manager
   *   Entity type manager.
   * @param obj $entity_type_manager
   *   Entity type manager.
   */
  public function __construct($entity_type_manager, $current_user, $config_factory, $user_referral_database)
  {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
    $this->userReferralDatabase = $user_referral_database;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('config.factory'),
      $container->get('commerce_norreferral.user_referral_database')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Get Subscribed Events.
   *
   * @return events
   *   Description of the return value, which is events.
   */
  public static function getSubscribedEvents()
  {
    $events['commerce_order.place.post_transition'] = ['orderCompleteHandler'];
    return $events;
  }

  /**
   * This method is called whenever a commerce_order is placed
   *
   *   Post_transition.
   *
   *   Event is dispatched.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   Event.
   */
  public function orderCompleteHandler(WorkflowTransitionEvent $event)
  {

    //If the user is Anonymous || Points are not applicable
    if ($this->currentUser->isAnonymous()) {
      return;
    }

    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();

    $orderUid = $order->getCustomerId();
    $orderTotal = $order->getTotalPrice();

    // Update the referral if applicable.
    if (!$this->currentUser->isAnonymous()) {
      $this->updateSenderUserPointsPostOrderSuccess($orderUid, $orderTotal, $order->getOrderNumber());
    }
  }

  /**
   * Update sender referral points post first order.
   */
  public function updateSenderUserPointsPostOrderSuccess($orderUid, $orderTotal, $orderNumber) {
    
    $orderTotal = $orderTotal ? $orderTotal->getNumber() : NULL;
    $user = User::load($orderUid);

    if ($user) {
      // The username of the referred person
      $userNameReferred = $user->getAccountName();

    }


  // Check if the refferal has got user points.
  $conditions = ['referral_receiver' => $orderUid, 'referral_sender_points_status' => 0];
  $records = $this->userReferralDatabase->selectRecords($conditions);
  if (!empty($records)) {
    $records = reset($records);
    $linkOwner = User::load($records['referral_sender']);
    $sender_referral_points_percentage = $this->configFactory->get('commerce_norreferral.user_referral_points_settings')->get('sender_referral_points_percentage');
    // Calculate sender referral points
    if ($orderTotal !== NULL) {
      $sender_referral_points = ($sender_referral_points_percentage / 100) * $orderTotal;
    } else {
      $sender_referral_points = 0;
    }

    //Adding Points Factor Logic
    $points_factor = $this->configFactory->get('commerce_norreferral.user_referral_points_settings')->get('points_factor');

    if ($sender_referral_points > 0 && $points_factor > 0) {
      $sender_referral_points = $sender_referral_points * $points_factor;
    }

    // Check if this is sender first referral, if yes give bonus point.
    $bonusDetails = $this->userReferralDatabase->selectReferralBonus($linkOwner->id());

    // Here referral_status = 0 means its invalid status.
    if ($records['referral_status'] != 0 && $sender_referral_points != 0) {
      $linkOwnerEmail = $linkOwner->getEmail();

      // SENDER POINTS CREATION
      $title = "Referral points to " . $linkOwnerEmail . " (UID: " . $linkOwner->id() . ")" . " for referring " . $userNameReferred . " (UID: " . $orderUid . ")";
      $user_referral_message = "Points for referring " . $userNameReferred;

      // Create new "user point" node with referral points.
      _commerce_norreferral_point_insert($linkOwner->id(), $title, 'Referral', $sender_referral_points, $user_referral_message);

      // SENDER BONUS POINTS CREATION FOR FIRST ORDER
      $sender_bonus_referral_points = $this->configFactory->get('commerce_norreferral.user_referral_points_settings')->get('sender_bonus_referral_points');
      if (empty($bonusDetails) && !empty($sender_bonus_referral_points)) {
        // Add bonus details in bonus table.
        $this->userReferralDatabase->insertReferralBonus($linkOwner->id(), $orderUid, $sender_bonus_referral_points, 1);

        // Add bonus details in user_points_data
        // Add point to owner user of the link.
        $title = "Bonus points to " . $linkOwnerEmail . " (UID: " . $linkOwner->id() . ") for first referral" . " of " . $userNameReferred . " (UID: " . $orderUid . ")";
        $user_bonus_message = "Bonus points for first referral";

        // Create new "user point" node with referral points.
        _commerce_norreferral_point_insert($linkOwner->id(), $title, 'Bonus', $sender_bonus_referral_points, $user_bonus_message);

      } 
      else 
      {
        // Fetch the referral count for $linkOwner.
        $referralCount = $this->userReferralDatabase->getReferralCounts($linkOwner->id());
        
    
        // Log the referral count.
        \Drupal::logger('Tier Bonus')->info('User @uid has @count referral(s).', [
            '@uid' => $linkOwner->id(),
            '@count' => $referralCount,
        ]);
    
        // Calculate and add tiered bonus points based on referral count.
        $tieredBonusPoints = $this->calculateTieredBonusPoints($referralCount);
    
        if ($tieredBonusPoints > 0) {
            // Add tiered bonus points to the user.
            $this->userReferralDatabase->insertReferralBonus($linkOwner->id(), $orderUid, $tieredBonusPoints, 2);
    
            // Add bonus details in user_points_data.
            $title = "Tiered bonus points to " . $linkOwnerEmail . " (UID: " . $linkOwner->id() . ") for " . $referralCount . " referrals";
            $user_bonus_message = "Tiered bonus points based on referral count";
    
            _commerce_norreferral_point_insert($linkOwner->id(), $title, 'Bonus', $tieredBonusPoints, $user_bonus_message);
        }
    
        // Update the referral record status.
        $this->userReferralDatabase->updateRecord(
            ['referral_sender_points_status' => 1, 'referral_status' => 1, 'order_id' => $orderNumber, 'order_amount' => $orderTotal],
            ['referral_sender' => $linkOwner->id(), 'referral_receiver' => $orderUid]
        );
     }

      $new_referal = $this->userReferralDatabase->isNewReferral($orderUid);
      // Log the referral count.
      \Drupal::logger('Additional Bonus TEST')->info(' The receiver with @uid has @count referral(s)', [
        '@uid' => $orderUid,
        '@count' => $new_referal,
      ]);
      if($new_referal==0)
      {
        // Log the referral count.
        \Drupal::logger('Additional Bonus')->info(' The receiver with @uid has @count referral(s)', [
          '@uid' => $orderUid,
          '@count' => $new_referal,
        ]);
    
        // Calculate and add tiered bonus points based on referral count.
        $BonusPoints = 5000;
    
        if ($BonusPoints > 0) {

            // Add bonus details in user_points_data.
            $title = "Bonus points to (UID: " . $orderUid . ") for " . $new_referal . " referrals";
            $user_bonus_message = "Used Referral Link and placed First Order";
    
            _commerce_norreferral_point_insert($orderUid, $title, 'Bonus-First Order', $BonusPoints, $user_bonus_message);
        }
    
        // Update the referral record status.
        $this->userReferralDatabase->updateRecord(
            ['referral_sender_points_status' => 1, 'referral_status' => 1, 'order_id' => $orderNumber, 'order_amount' => $orderTotal],
            ['referral_sender' => $linkOwner->id(), 'referral_receiver' => $orderUid]
        );
      }
    }
  }
}

  private function calculateTieredBonusPoints($referralCount) {
    if ($referralCount == 50) 
    {
      return 50000;
    } 
    elseif ($referralCount == 40) 
    {
      return 40000;
    } 
    elseif ($referralCount ==30) 
    {
      return 30000;
    } 
    elseif ($referralCount == 20) 
    {
      return 20000;
    } 
    elseif ($referralCount == 10) 
    {
      return 10000;
    } 
    elseif ($referralCount == 5) 
    {
      return 5000;
    } 
    else 
    {
      return 0;
    }
  }
}