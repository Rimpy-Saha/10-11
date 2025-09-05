<?php

namespace Drupal\commerce_nor_pricelist\Repository;

use Drupal\commerce_pricelist\PriceListRepository;
use Drupal\commerce\Context;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class CustomPriceListRepository extends PriceListRepository {

  protected $currentUser;
  protected $currentStore;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user, CurrentStoreInterface $current_store) {
    parent::__construct($entity_type_manager);
    $this->currentUser = $current_user;
    $this->currentStore = $current_store;
  }

  public function loadPriceListIds($bundle, Context $context) {
    // $is_guest_canada = commerce_norquote_check_canada_guest();
    // \Drupal::logger('commerce_nor_pricelist')->error('this is canada '. $is_guest_canada);
    try{
      // Manual price override vai URL param for anonymous users. Necessary for Google Merchant Center URLs because the page needs to always display the same currency as stated in the product listing.
      // Google crawls from US as anon user. If it goes to the product page linked for our CAD product, it will see USD price and then limit our listings.

      // Check if "currency" is URL parameter
      $current_request = \Drupal::request();
      $query = $current_request->query;
      if (str_contains($current_request->getRequestUri(), '/product/') && $query->has('currency')) {
        if ($query->get('currency') == 'USD'){
          //\Drupal::logger('commerce_nor_pricelist')->info('Returning US price');
          return ['0'];
        }
        elseif($query->get('currency') == 'CAD'){
          //\Drupal::logger('commerce_nor_pricelist')->info('Returning Canadian price');
          return['1'];
        }
      }

      // Check if anonymous user is Canadian, show CAD if true
      $is_guest_canada = commerce_norquote_check_canada_guest();
      if ($is_guest_canada) {
        // \Drupal::logger('commerce_nor_pricelist')->error('the array is : 1');
        return ['1'];
      }
    }
    catch (\Exception $e){
      \Drupal::logger('commerce_nor_pricelist')->error('Error checking Canada guest: @message', ['@message' => $e->getMessage()]);
    }
    return parent::loadPriceListIds($bundle, $context);
  }
}
