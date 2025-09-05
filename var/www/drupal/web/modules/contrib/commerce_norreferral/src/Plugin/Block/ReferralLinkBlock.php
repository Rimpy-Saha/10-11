<?php

namespace Drupal\commerce_norreferral\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\commerce_norreferral\ReferralLinkService;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a 'Referral Link' block.
 *
 * @Block(
 *   id = "user_referral_link",
 *   admin_label = @Translation("Referral Link")
 * )
 */
class ReferralLinkBlock extends BlockBase implements ContainerFactoryPluginInterface
{

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current store.
   *
   * @var \Drupal\commerce_store\CurrentStoreInterface
   */
  protected $currentStore;

  /**
   * The commerce referral link service.
   *
   * @var \Drupal\commerce_norreferral\ReferralLinkService
   */
  protected $referralLink;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new CustomBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\commerce_store\CurrentStoreInterface $current_store
   *   The current store.
   * @param \Drupal\commerce_norreferral\ReferralLinkInterface $referral_link
   *   The commerce referral link service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, CurrentStoreInterface $current_store, ReferralLinkService $referral_link, AccountProxyInterface $current_user)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->currentStore = $current_store;
    $this->referralLink = $referral_link;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('commerce_store.current_store'),
      $container->get('commerce_norreferral.referral_link'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration()
  {
    return ['label_display' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge()
  {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $referralLink = "";
    $uid = $this->currentUser->id();
    if ($uid != NULL) {
      $referralLinkCode = $this->referralLink->getLink('uid', $uid);

      if (isset ($referralLinkCode) && !empty ($referralLinkCode)) {
        $linkCode = $referralLinkCode['referral_link_code'];
        $frontPage = Url::fromRoute('<front>')->setAbsolute()->toString();
        $referralLink = $frontPage . 'user/register/referral/' . $linkCode;
      }
    }
    // Get threshold amount
    $sender_referral_points_percentage = $this->configFactory->get('commerce_norreferral.user_referral_points_settings')->get('sender_referral_points_percentage');

    // Get user data from the referral link service
    $user_data = $this->referralLink->getUserData();

    $total_earned_points = $user_data['total_earned_points'];
    $total_used_points = $user_data['total_used_points'];
    $available_points = $total_earned_points - $total_used_points;


    $renderable = [
      '#theme' => 'user_referral_link',
      '#referral_link' => $referralLink,
      '#sender_referral_points_percentage' => $sender_referral_points_percentage,
      '#total_earned_points' => $total_earned_points, // Add the user data to the render array
      '#total_used_points' => $total_used_points, // Add the user data to the render array
      '#available_points' => $available_points, // Add the user data to the render array
      '#attached' => [
        'library' => [
          'commerce_norreferral/referral_link_library',
        ],
      ],
    ];

    // Attach cache tags to the block render array.
    //$renderable['#cache']['tags'] = CacheableMetadata::createFromRenderArray($renderable)->getCacheTags() + $cacheTags;
    $renderable['#cache']['tags'] = CacheableMetadata::createFromRenderArray($renderable)->getCacheTags();

    return $renderable;
  }

}
