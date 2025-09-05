<?php

namespace Drupal\commerce_norreferral\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_store\Entity\Store;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_order\Adjustment;
use Drupal\commerce_price\Price;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\commerce_norreferral\Database\UserReferralDatabase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides the CommerceUserPoints.
 *
 * @CommerceCheckoutPane(
 *   id = "coupons",
 *   label = @Translation("Redeem Referral Points"),
 *   default_step = "order_information",
 * )
 */
class CommerceUserPoints extends CheckoutPaneBase implements CheckoutPaneInterface
{
  protected $maxRedeemablePoints;

  /**
   * The UserReferralDatabase service.
   *
   * @var \Drupal\commerce_norreferral\Database\UserReferralDatabase
   */
  protected $userReferralDatabase;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new CommerceUserPoints object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface $checkout_flow
   *   The parent checkout flow.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_norreferral\Database\UserReferralDatabase $user_referral_database
   *   The UserReferralDatabase service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager, UserReferralDatabase $user_referral_database, ConfigFactoryInterface $config_factory)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);
    $this->userReferralDatabase = $user_referral_database;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow = NULL)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $checkout_flow,
      $container->get('entity_type.manager'),
      $container->get('commerce_norreferral.user_referral_database'),
      $container->get('config.factory'),
    );
  }

  /**
   * Default Configuration.
   *
   * @return array
   *   Description of the return value, which is a array.
   */
  public function defaultConfiguration()
  {
    return [
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   *
   * Build Configuration Summary.
   *
   * @return string
   *   Description of the return value, which is a string.
   */
  public function buildConfigurationSummary()
  {
    return "";
  }

  /**
   * {@inheritdoc}
   *
   * Configuration Form.
   *
   * @param array $form
   *   Array $form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   FormStateInterface $form_state.
   *
   * @return array
   *   Description of the return value, which is a array.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state)
  {
    $form = parent::buildConfigurationForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * Configuration Form Submission.
   *
   * @param array $form
   *   Array $form_state.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   FormStateInterface $form_state.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state)
  {
    parent::submitConfigurationForm($form, $form_state);
  }

 /**
 * {@inheritdoc}
 *
 * Build Pane Form.
 *
 * @param array $pane_form
 *   Array $pane_form.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   FormStateInterface $form_state.
 * @param array $complete_form
 *   Array complete_form.
 *
 * @return array
 *   Description of the return value, which is a array.
 */
public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {

    $arrNidPoints = $totalUsablePoints = [];
    $user = \Drupal::currentUser();
    $orderAdjustment = $this->order->getAdjustments();
    $flagPointsApplied = FALSE;

    foreach ($orderAdjustment as $adjustmentValue) {
        if ($adjustmentValue->getType() == 'custom') {
            $flagPointsApplied = TRUE;
        }
    }

    if (!$flagPointsApplied && !empty($user->id())) {
        // The options to display in Order Information page radio buttons.
        $options = [
            // '0' => t("Don't use points"),
            '1' => t('Utilize all available points'),
            '2' => t('Utilize designated points'),
        ];

        // Get all valid user points.
        $arrNidPoints = $this->calculateUsablePoints();
        $totalUsablePoints = round($arrNidPoints['total_usable_points']);

        $price = $complete_form['sidebar']['order_summary']['#order_entity'];
        $total_value = $price->get("total_price")->getValue();

        // We only show the Redeem User Points checkbox during checkout if the user has any Usable Points
        if ($totalUsablePoints > 0) {
            $pane_form['user_points'] = [
                '#type' => 'checkbox',
                '#title' => t('Redeem User Points'),
                '#default_value' => '0',
                '#attributes' => ['class' => ['user-point-checkbox']],
            ];

            $pane_form['user_points_redemption_type'] = [
                '#type' => 'radios',
                '#title' => t('User points Redeems'),
                '#options' => $options,
                '#description' => t(' (Currently, you possess') . " " . $totalUsablePoints . " points)",
                '#default_value' => '1',
                '#states' => [
                    'visible' => [
                        ':input[name="coupons[user_points]"]' => [
                            'checked' => TRUE,
                        ],
                    ],
                ],
                '#attributes' => ['class' => ['user-points-redemption-div']],
            ];


            // Replace the text field with a number field with step increments of 100
            $pane_form['user_points_redemption'] = [
                '#type' => 'number',
                '#title' => $this->t('Enter Points for Redemption'),
                '#default_value' => '',
                '#required' => FALSE,
                '#min' => 1000,
                '#step' => 10,
                '#max' => floor($totalUsablePoints / 10) * 10,      
                '#attributes' => ['class' => ['user-points-redemption-field']],
            ];

            $pane_form['user_points_redemption']['#states'] = [
                'visible' => [
                    ':input[name="coupons[user_points_redemption_type]"]' => ['value' => '2'],
                ],
            ];
        }

        if ($totalUsablePoints > 0 && $options['1']) {
            // Calculate the points to be redeemed in 100-point increments
            $redeemablePoints = floor($totalUsablePoints / 10) * 10;
            $remainingPoints = $totalUsablePoints - $redeemablePoints;
            // Update the description to reflect the adjusted redemption points
            $pane_form['user_points_redemption_type']['#description'] = t('(Currently, you possess') . " " . $totalUsablePoints . " points, redeemable in increments of 10. Max points redeemable: " . $redeemablePoints . ", remaining points: " . $remainingPoints . ")";
        }
    }

    $pane_form['#attached']['library'][] = 'commerce_norreferral/referral_link_library';
    return $pane_form;
}


 /**
 * {@inheritdoc}
 *
 * Validate Pane Form.
 *
 * @param array $pane_form
 *   FormStateInterface $form_state, $complete_form.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   FormStateInterface $form_state, $complete_form.
 */

public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {

    $arrNidPoints = $totalUsablePoints = [];

    $values = $form_state->getValue($pane_form['#parents']);

    //The maximum points that can be redeemed in a single order 
    $max_redeemable_points = (int) $this->configFactory->get('commerce_norreferral.user_referral_points_settings')->get('user_max_redeemable_points');

    // Fetching the points factor from configuration.
    $points_factor = (int) $this->configFactory->get('commerce_norreferral.user_referral_points_settings')->get('points_factor');

    // Calculating the minimum redeemable points for one dollar equivalent.
    // Add mod - Sowmya
    if ($points_factor > 0) {
        $minimum_redeemable_points_one_dollar = $points_factor;
    } else {
        $minimum_redeemable_points_one_dollar = 0;
    }

    // $user_entered_points_to_redeem = $values['user_points_redemption'];

    

    if (isset($values['user_points']) 
    && $values['user_points'] == 1 
    && isset($values['user_points_redemption_type']) 
    && !empty($values['user_points_redemption_type'])) {
      $user_entered_points_to_redeem = $values['user_points_redemption'];
        switch ($values['user_points_redemption_type']) {
            case '2':

                // Check if the value is numeric
                if (!is_numeric($values['user_points_redemption'])) {
                    $form_state->setErrorByName('user_points', $this->t('Please ensure you input a numeric value for Points.'));
                }

                // Get all valid user points.
                $arrNidPoints = $this->calculateUsablePoints();
                $totalUsablePoints = round($arrNidPoints['total_usable_points']);

                if ($totalUsablePoints > 0) {

                    if ($user_entered_points_to_redeem < $minimum_redeemable_points_one_dollar) {

                        $form_state->setErrorByName(
                            'user_points',
                            $this->t(
                                'Please note that a minimum of @min_redeemable_points points must be applied to an order, equivalent to $1. If you have fewer points than this minimum, consider earning more points before you can redeem and apply them to your order.',
                                [
                                    '@min_redeemable_points' => $minimum_redeemable_points_one_dollar,
                                ]
                            )
                        );
                    } elseif ($user_entered_points_to_redeem > $max_redeemable_points) {
                        $form_state->setErrorByName('user_points', $this->t('We want to inform you that you can utilize a maximum of @max_redeemable_points points for a single order.', ['@max_redeemable_points' => number_format($max_redeemable_points)]));
                    } elseif ($user_entered_points_to_redeem % 10 !== 0) {
                        // Check if the entered points are divisible by 100
                        $form_state->setErrorByName('user_points', $this->t('Points can only be redeemed in increments of 10.'));
                    }
                }

                break;

            case '1':

                // Get all valid user points.
                $arrNidPoints = $this->calculateUsablePoints();
                $totalUsablePoints = round($arrNidPoints['total_usable_points']);
                // dump($values);
                // dump($arrNidPoints);
                // exit;
                if ($totalUsablePoints > 0) {

                    if ($totalUsablePoints < $minimum_redeemable_points_one_dollar) {
                        $form_state->setErrorByName(
                            'user_points',
                            $this->t(
                                'Please note that a minimum of @min_redeemable_points points must be applied to an order, equivalent to $1. If you have fewer points than this minimum, consider earning more points before you can redeem and apply them to your order.',
                                [
                                    '@min_redeemable_points' => $minimum_redeemable_points_one_dollar,
                                ]
                            )
                        );
                    } 
                    // elseif ($totalUsablePoints > $max_redeemable_points) {
                    //     $form_state->setErrorByName('user_points', $this->t('We want to inform you that you can utilize a maximum of @max_redeemable_points points for a single order.', ['@max_redeemable_points' => number_format($max_redeemable_points)]));
                    // } elseif ($user_entered_points_to_redeem % 100 !== 0) {
                    //     // Check if the entered points are divisible by 100
                    //     $form_state->setErrorByName('user_points', $this->t('Points can only be redeemed in increments of 100.'));
                    // }
                }

                break;

            default:
                break;
        }
    }
}


  /**
   * {@inheritdoc}
   *
   * Submit Pane Form.
   *
   * @param array $pane_form
   *   FormStateInterface $form_state, array $complete_form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   FormStateInterface $form_state, array $complete_form.
   * @param array $complete_form
   *   FormStateInterface $form_state, array $complete_form.
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form){

    $arrNidPoints = $totalUsablePoints = [];
    $values = $form_state->getValue($pane_form['#parents']);

    if ($values['user_points']==1 && isset($values['user_points_redemption_type']) && !empty($values['user_points_redemption_type'])) {

      $arrNidPoints = $this->calculateUsablePoints();

      // Calculate usable points based on user selected value.
      switch ($values['user_points_redemption_type']) {
        case '2':
          $totalUsablePoints = $values['user_points_redemption'];
          break;

        case '1':
          // Get all valid user points.
          $totalUsablePoints = round($arrNidPoints['total_usable_points']);
          break;

        default:
          $totalUsablePoints = '0';
          break;
      }

      if (!empty($totalUsablePoints)) {

        $orderItemTotal = 0;

        if ($this->order->hasItems()) {
          foreach ($this->order->getItems() as $orderItem) {
            $orderItemTotal += $orderItem->getTotalPrice()->getNumber();
          }
        }

        //Dividing points by points_factor before comparing with order total
        $points_factor = (int) $this->configFactory->get('commerce_norreferral.user_referral_points_settings')->get('points_factor');
        if ($points_factor > 0) {
          $totalUsablePoints = $totalUsablePoints / $points_factor;
        }

        if ($orderItemTotal < $totalUsablePoints) {
          $totalUsablePoints = $orderItemTotal;
        }

        foreach ($this->order->getItems() as $orderItem) {
          $purchasedEntity = $orderItem->getPurchasedEntity();
          $productId = $purchasedEntity->get('product_id')->getString();
          $product = Product::load($productId);
          // To get the store details for currency code.
          $store = Store::load(reset($product->getStoreIds()));
        }

        $orderCurrency = $this->order->getTotalPrice()->getCurrencyCode();

        // Create adjustment object for current order.
        $adjustments = new Adjustment([
          'type' => 'custom',
          'label' => 'Points Redemption Discount ',
          'amount' => new Price('-' . $totalUsablePoints, $orderCurrency),
        ]);

        $userPointsNids = $arrNidPoints['user_points_nids'];

        //Multiplying points by points_factor again before updating in database
        if ($points_factor > 0) {
          $totalUsablePoints = $totalUsablePoints * $points_factor;
          //needs to be mod here and save remaining points if not redeemed 
        }

        $deductUserPoints = $this->deductUserPoints($userPointsNids, $totalUsablePoints);
        if ($deductUserPoints) {
          // Add adjustment to order and save.
          $this->order->addAdjustment($adjustments);
          $this->order->save();
        }
      }
    }
  }

  /**
   * Deduct user points from nodes $userPointsNids $totalUsablePoints.
   *
   * @param array $userPointsNids
   *   array $totalUsablePoints.
   * @param string $totalUsablePoints
   *   string $totalUsablePoints.
   *
   * @return bool
   *   Description of the return value, which is a boolean.
   */
  public function deductUserPoints(array $userPointsNids, $totalUsablePoints) {

    $updatedPoints = 0;
    $calculatedRemainingPoints = $totalUsablePoints;

    $conditions = [
      'id' => $userPointsNids,
    ];
    $additionalComparisionPrama = [
      'id' => 'IN',
    ];

    // Get all valid user points
    $userPointsRecords = $this->userReferralDatabase->selectUserPointsRecords($conditions, $additionalComparisionPrama);

    foreach ($userPointsRecords as $userPointsRecord) {

      $earnedNodePoints = $userPointsRecord['earned_points'];
      $usedNodePoints = $userPointsRecord['used_points'];
      $availableNodePoints = $earnedNodePoints - $usedNodePoints;

      $nextDeductPoints = $calculatedRemainingPoints - $availableNodePoints;
      if ($updatedPoints < $totalUsablePoints) {
        if ($nextDeductPoints > 0) {
          $updatedPoints += $availableNodePoints;
          $calculatedRemainingPoints = $nextDeductPoints;
          $nodeUpdatePoints = $usedNodePoints + $availableNodePoints;

          // Determine the point status as an integer (1 or 0).
          $pointStatus = ($earnedNodePoints - $nodeUpdatePoints) <= 0 ? 0 : 1;

          // Create $values array
          $values = [
            'point_status' => $pointStatus,
            'used_points' => $nodeUpdatePoints
          ];
          $this->userReferralDatabase->updateUserPointsRecord($values, ['id' => $userPointsRecord['id']]);
        } else {
          $updatedPoints += $calculatedRemainingPoints;
          $nodeUpdatePoints = $usedNodePoints + $calculatedRemainingPoints;

          // Determine the point status as an integer (1 or 0).
          $pointStatus = ($earnedNodePoints - $nodeUpdatePoints) <= 0 ? 0 : 1;

          $values = [
            'point_status' => $pointStatus,
            'used_points' => $nodeUpdatePoints
          ];
          $this->userReferralDatabase->updateUserPointsRecord($values, ['id' => $userPointsRecord['id']]);
        }
      }
    }

    return TRUE;
  }

  /**
   * Calculate user available points.
   *
   * @return arrNidPoints
   *   Description of the return value, which is a array.
   */
  public function calculateUsablePoints()
  {

    // Get all valid user points.
    $user = \Drupal::currentUser();
    $conditions = [
      'uid' => $user->id(),
    ];
    $additionalComparisionPrama = [
    ];
    $sortCondition = [
      'points_acquisition_date' => 'ASC',
    ];

    $userPointsRecords = $this->userReferralDatabase->selectUserPointsRecords($conditions, $additionalComparisionPrama, $sortCondition);

    $totalEarnedPoints = 0;
    $totalUsedPoints = 0;
    $totalUsablePoints = 0;

    $recordIds = [];

    foreach ($userPointsRecords as $userPointsRecord) {
      $totalEarnedPoints += (int) $userPointsRecord['earned_points'];
      $totalUsedPoints += (int) $userPointsRecord['used_points'];
      $recordIds[] = $userPointsRecord['id'];
      //$UsuablePoints = $totalUsedPoints mod 1000
      //$redeemablePoints = $arrNidPoints[$selectedIncrement]
      //$totalUsuablePoints = $totalEarnedPoints - totalEarnedPoints 
    }

    // Total usable points by logged in user.
    $totalUsablePoints = $totalEarnedPoints - $totalUsedPoints;

    $arrNidPoints = [];
    $arrNidPoints['total_usable_points'] = round($totalUsablePoints);

    $arrNidPoints['user_points_nids'] = $recordIds;

    return $arrNidPoints;
  }

}
