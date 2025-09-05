<?php

namespace Drupal\nor_forms\EventSubscriber;

use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartOrderItemRemoveEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_checkout\Event\CheckoutEvents;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GA4CommerceEvents implements EventSubscriberInterface {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  protected $session;

  
  public function __construct(SessionInterface $session, MessengerInterface $messenger) {  // Use messenger to dump variables and troubleshoot
    $this->session = $session;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      CartEvents::CART_ENTITY_ADD => 'ga4AddToCart', // add to cart
      CartEvents::CART_ORDER_ITEM_REMOVE => 'ga4RemoveFromCart', // remove from cart
      'commerce_order.place.post_transition' => 'ga4CompleteCheckout', // complete purchase. Cannot use CheckoutEvents::COMPLETION because the order number is not added until the 'commerce_order.place.pre_transition' via \commerce\modules\order\src\EventSubscriber\OrderNumberSubscriber
    ];
    return $events;
  }

  /**
   * Displays an add to cart message.
   *
   * @param \Drupal\commerce_cart\Event\CartEntityAddEvent $event
   *   The add to cart event.
   */
  public function ga4AddToCart(CartEntityAddEvent $event) { // TO DO: throws error when items are added to cart from nodes. Cannot get currency from null
    $cart_variables['add_to_cart'][] = [
        'currency' => $event->getEntity()->getPrice()->getCurrencyCode(),
        'value' => $event->getEntity()->getPrice()->multiply($event->getQuantity())->getNumber(),
        'sku' => $event->getEntity()->getSku(),
        'title' => $event->getEntity()->getOrderItemTitle(),
        'price' => round($event->getEntity()->getPrice()->getNumber(), 2),
        'quantity' => $event->getQuantity(),
    ];

    $this->session->set('ga4_cart_variables', $cart_variables);
  }

  public function ga4RemoveFromCart(CartOrderItemRemoveEvent $event) {
    $cart_variables['remove_from_cart'][] = [
        'currency' => $event->getOrderItem()->getTotalPrice()->getCurrencyCode(),
        'value' => $event->getOrderItem()->getTotalPrice()->getNumber(),
        'sku' => $event->getOrderItem()->getPurchasedEntity()->getSku(),
        'title' => $event->getOrderItem()->getTitle(),
        'price' => round($event->getOrderItem()->getUnitPrice()->getNumber(), 2),
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
  }

  public function ga4CompleteCheckout(WorkflowTransitionEvent $event){
    $rounder = \Drupal::service('commerce_price.rounder');
    $order = $event->getEntity();

    $checkout_variables['purchase']['currency'] = $order->getSubtotalPrice()->getCurrencyCode();
    $checkout_variables['purchase']['value'] = $order->getSubtotalPrice()->getNumber();
    $checkout_variables['purchase']['order_id'] = $order->getOrderNumber();

    $calculate_shipping = $calculate_tax = $calculate_promotion = 0;
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
      if($order_item_discount_value!=0) $checkout_variables['purchase']['order_items'][$index]['discount'] = $order_item_discount_value;
      if(count($order_item_discount_names)>0) $checkout_variables['purchase']['order_items'][$index]['promotion_name'] = join('|',$order_item_discount_names);  
    }

    if($calculate_tax>0) $checkout_variables['purchase']['tax'] = $calculate_tax;

    $this->session->set('ga4_checkout_variables', $checkout_variables);
  }

}
