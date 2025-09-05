<?php

namespace Drupal\commerce_nor_cart_popup\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartOrderItemRemoveEvent;
use Drupal\commerce_cart\Event\CartOrderItemUpdateEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Drupal\Core\Messenger\MessengerInterface;
/* Wish List Flagging */
use Drupal\flag\Event\FlagEvents;
use Drupal\flag\Event\FlaggingEvent;

class CartSubscriber implements EventSubscriberInterface {

  protected $session;
  use StringTranslationTrait;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  public function __construct(SessionInterface $session, MessengerInterface $messenger) {
    $this->session = $session;
    $this->messenger = $messenger;
  }

  public static function getSubscribedEvents() {
    return [
      FlagEvents::ENTITY_FLAGGED => 'add_to_wishlist', // add to wishlist
      CartEvents::CART_ENTITY_ADD => 'onCartEntityAdd',
      CartEvents::CART_ORDER_ITEM_UPDATE => 'onCartEntityUpdate',
      CartEvents::CART_ORDER_ITEM_REMOVE => 'onCartEntityRemove',
      // 'commerce_order.place.post_transition' => 'ga4CompleteCheckout', // complete purchase. Cannot use CheckoutEvents::COMPLETION because the order number is not added until the 'commerce_order.place.pre_transition' via \commerce\modules\order\src\EventSubscriber\OrderNumberSubscriber
    ];
  }

  public function add_to_wishlist(FlaggingEvent $event){
    $add_to_wishlist = [
      'currency' => $event->getFlagging()->getFlaggable()->getPrice()->getCurrencyCode(),
      'value' => $event->getFlagging()->getFlaggable()->getPrice()->getNumber(),
      'sku' => $event->getFlagging()->getFlaggable()->getSku(),
      'title' => $event->getFlagging()->getFlaggable()->getOrderItemTitle(),
      'price' => round($event->getFlagging()->getFlaggable()->getPrice()->getNumber(), 2),
    ];
    if($this->session->has('ga4_other_commerce_variables') && $this->session->get('ga4_other_commerce_variables')['add_to_wishlist']){
      $ga4_other_commerce_variables = $this->session->get('ga4_other_commerce_variables');
      $ga4_other_commerce_variables['add_to_wishlist']['value'] += $add_to_wishlist['value'];
      $ga4_other_commerce_variables['add_to_wishlist']['items'][] = $add_to_wishlist;
      $this->session->set('ga4_other_commerce_variables', $ga4_other_commerce_variables);
    }
    else {
      $ga4_other_commerce_variables['add_to_wishlist']['value'] = $add_to_wishlist['value'];
      $ga4_other_commerce_variables['add_to_wishlist']['items'][] = $add_to_wishlist;
      $this->session->set('ga4_other_commerce_variables', $ga4_other_commerce_variables);
    }
  }

  public function onCartEntityAdd(CartEntityAddEvent $event) {
    $rounder = \Drupal::service('commerce_price.rounder');
    $this->session->set('item_added_to_cart', TRUE);

    $this->applyMinimumQuantity($event->getOrderItem());

    /* GOOGLE TRACKING */
    $cart_variables['add_to_cart'][] = [
      'currency' => $event->getEntity()->getPrice()->getCurrencyCode(),
      'value' => $event->getEntity()->getPrice()->multiply($event->getQuantity())->getNumber(),
      'sku' => $event->getEntity()->getSku(),
      'title' => $event->getEntity()->getOrderItemTitle(),
      'price' => $rounder->round($event->getEntity()->getPrice())->getNumber(),
      'quantity' => $event->getQuantity(),
    ];
    $this->session->set('ga4_cart_variables', $cart_variables);

    // MAGBEAD
    $purchased_entity = $event->getEntity();
    $added_product_title = $event->getEntity()->getOrderItemTitle();
    $sku = $event->getEntity()->getSku();
    $cart = $event->getCart();
    if($added_product_title && $sku && ($sku == 75500 || $sku == 75400)){
      $necessary_info = [
        'magbead_product_title' => $added_product_title,
        'sku' => $sku,
      ];
      $this->session->set('magbead_dnase_bundle_promo', $necessary_info);
    }
    // END MAGBEAD

    // dump($this->session->get('item_added_to_cart'));
    // dump($this->session->get('ga4_cart_variables'));
    // exit;
  }

  public function onCartEntityUpdate(CartOrderItemUpdateEvent $event) {
    $this->session->set('item_in_cart_updated', TRUE);

    $this->applyMinimumQuantity($event->getOrderItem());
  }
  
  public function onCartEntityRemove(CartOrderItemRemoveEvent $event) {
    $rounder = \Drupal::service('commerce_price.rounder');
    $this->session->set('item_in_cart_removed', TRUE);

    /* GOOGLE TRACKING */
    $cart_variables['remove_from_cart'][] = [
      'currency' => $event->getOrderItem()->getTotalPrice()->getCurrencyCode(),
      'value' => $event->getOrderItem()->getTotalPrice()->getNumber(),
      'sku' => $event->getOrderItem()->getPurchasedEntity()->getSku(),
      'title' => $event->getOrderItem()->getTitle(),
      'price' => $rounder->round($event->getOrderItem()->getUnitPrice())->getNumber(),
      'quantity' => $event->getOrderItem()->getQuantity(),
    ];
    /* $this->messenger->addMessage($this->t('@title (@sku) [@quantity x @price = @value @currency] was removed from <a href=":url">your cart</a>.', [
      '@currency' => $event->getOrderItem()->getTotalPrice()->getCurrencyCode(),
      '@value' => $event->getOrderItem()->getTotalPrice()->getNumber(),
      '@sku' => $event->getOrderItem()->getPurchasedEntity()->getSku(),
      '@title' => $event->getOrderItem()->getTitle(),
      '@price' => round($event->getOrderItem()->getUnitPrice()->getNumber(), 2),
      '@quantity' => $event->getOrderItem()->getQuantity(),
      ':url' => Url::fromRoute('commerce_cart.page')->toString(),
    ])); */
    $this->session->set('ga4_cart_variables', $cart_variables);
    // dump($event);
    // dump($this->session->get('item_in_cart_updated'));
    // exit;
  }

  /* public function ga4CompleteCheckout(WorkflowTransitionEvent $event){
    $rounder = \Drupal::service('commerce_price.rounder');
    $order = $event->getEntity();

    $checkout_variables['purchase']['currency'] = $order->getSubtotalPrice()->getCurrencyCode();
    $checkout_variables['purchase']['value'] = $order->getSubtotalPrice()->getNumber();
    $checkout_variables['purchase']['order_id'] = $order->getOrderNumber();

    $calculate_shipping = $calculate_tax = $calculate_promotion = 0;
    $order_promotion_value = 0;
    $order_promotion_names = [];
    foreach($order->getAdjustments(['shipping', 'promotion', 'shipping_promotion']) as $index=>$adjustment){ // add up any order level adjustments
      if($adjustment->getType()=='shipping') $calculate_shipping += $adjustment->getAmount()->getNumber(); // shipping
      elseif($adjustment->getType()=='promotion' || $adjustment->getType()=='shipping_promotion'){ // promotions
        $order_promotion_value += $adjustment->getAmount()->getNumber();
        $order_promotion_names = array_merge($order_promotion_names, [$adjustment->getLabel()]);
      }
    }
    if($calculate_shipping>0) $checkout_variables['purchase']['shipping'] = $calculate_shipping;
    if($order_promotion_value>0) $checkout_variables['purchase']['discount'] = $order_promotion_value; // GA4 wants discounts in each order_item
    if(count($order_promotion_names)>0) $checkout_variables['purchase']['promotion_name'] = join('|',$order_promotion_names);

    $order_items = $order->getItems();
    foreach($order_items as $index=>$order_item){
      // get order item adjustments
      
      $order_item_discount_value = 0;
      $order_item_discount_names = [];
      
      foreach($order_item->getAdjustments(['tax']) as $adjustment){
        $calculate_tax += $adjustment->getAmount()->getNumber(); // tax is calculcated at order_item level, not order.
      }

      foreach ($order_item->getAdjustments(['promotion', 'shipping_promotion']) as $adjustment) {
        $order_item_discount_value += $adjustment->getAmount()->getNumber();
        $order_item_discount_names = array_merge($order_item_discount_names, [$adjustment->getLabel()]);
      }

      $checkout_variables['purchase']['order_items'][] = [
        'sku' => $order_item->getPurchasedEntity()->getSku(),
        'title' => $order_item->getTitle(),
        'price' => $rounder->round($order_item->getUnitPrice())->getNumber(),
        'quantity' => $order_item->getQuantity(),
        'index' => $index,
      ];
      if($order_item_discount_value!=0) $checkout_variables['purchase']['order_items'][$index]['discount'] = abs($order_item_discount_value);
      if(count($order_item_discount_names)>0) $checkout_variables['purchase']['order_items'][$index]['promotion_name'] = join('|',$order_item_discount_names);  
    }

    if($calculate_tax>0) $checkout_variables['purchase']['tax'] = $calculate_tax;

    $this->session->set('ga4_checkout_variables', $checkout_variables);
  } */

  private function applyMinimumQuantity($order_item) {
    $purchased_entity = $order_item->getPurchasedEntity();
    $sku = $purchased_entity->getSku();

    // Define products with a minimum quantity (by SKU).
    $minimum_quantities = [
      '68400' => 10,
      '53210' => 10,
    ];

    // Check if the SKU exists in the minimum quantity array.
    if (isset($minimum_quantities[$sku])) {
      $min_quantity = $minimum_quantities[$sku];
      $product_title = $purchased_entity->getTitle();

      // Check if the quantity is less than the minimum.
      if ($order_item->getQuantity() < $min_quantity) {
        $order_item->setQuantity($min_quantity);
        $order_item->save();
        $this->messenger->addMessage(t('<strong>Please Note that the Quantity for @product_title has been adjusted to @min_quantity</strong> as that is the minimum purchasable quantity for the product.', [
          '@product_title' => $product_title,
          '@min_quantity' => $min_quantity,
        ]), 'error');
      }
    }
  }

}

