<?php

namespace Drupal\commerce_norpromotions\Plugin\Commerce\PromotionOffer;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer\PercentageOffTrait;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\Plugin\Commerce\PromotionOffer\ShipmentPromotionOfferBase;

/**
 * Provides a percentage off offer for shipments excluding dry ice products.
 *
 * @CommercePromotionOffer(
 *   id = "shipment_excluding_dry_ice",
 *   label = @Translation("Percentage off shipping (excluding dry ice)"),
 *   entity_type = "commerce_order"
 * )
 */
class ShippingExcludingDryIce extends ShipmentPromotionOfferBase {
  use PercentageOffTrait;

  /**
   * {@inheritdoc}
   */
  public function applyToShipment(ShipmentInterface $shipment, PromotionInterface $promotion) {

    $order = $shipment->getOrder();


    if ($order instanceof OrderInterface) {
      \Drupal::logger('commerce_promotion')->notice('Valid order found. Checking for dry ice products.');

      if ($this->containsDryIce($order)) {
        \Drupal::logger('commerce_promotion')->notice('Dry ice found in the order. The shipping promotion will not be applied.');
        return; 
      } else {
        \Drupal::logger('commerce_promotion')->notice('No dry ice found. Applying shipping discount.');

        $percentage = $this->getPercentage();

        $amount = $shipment->getAmount()->multiply($percentage);
        $amount = $this->rounder->round($amount);

        $remaining_amount = $shipment->getAdjustedAmount();
        if ($amount->greaterThan($remaining_amount)) {
          $amount = $remaining_amount;
        }

        $shipment->addAdjustment(new Adjustment([
          'type' => 'shipping_promotion',
          'label' => $promotion->getDisplayName() ?: $this->t('Discount'),
          'amount' => $amount->multiply('-1'),
          'percentage' => $percentage,
          'source_id' => $promotion->id(),
          'included' => $this->isDisplayInclusive(),
        ]));

        //\Drupal::logger('commerce_promotion')->notice('Shipping discount applied: @amount', ['@amount' => $amount]);
      }
    } else {
     // \Drupal::logger('commerce_promotion')->error('The shipment does not belong to a valid order.');
    }
  }

  /**
   * Checks if the order contains any dry ice products.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order to check.
   *
   * @return bool
   *   TRUE if the order contains dry ice, FALSE otherwise.
   */
  protected function containsDryIce(OrderInterface $order) {

    $line_items = $order->getItems();

    \Drupal::logger('commerce_promotion')->notice('Checking @count line items for dry ice.', ['@count' => count($line_items)]);

    $dry_ice = false;

    foreach ($line_items as $line_item) {
      $purchased_entity = $line_item->getPurchasedEntity();

      if ($purchased_entity->hasField('field_commerce_shipping_box')) {
        $shipping_box_field = $purchased_entity->get('field_commerce_shipping_box')->getValue();
        $dry_ice = $this->checkShippingBox($shipping_box_field);
      }

      if (!$dry_ice && $purchased_entity->hasField('commerce_shipping_box')) {
        $shipping_box_field = $purchased_entity->get('commerce_shipping_box')->getValue();
        $dry_ice = $this->checkShippingBox($shipping_box_field);
      }

      if ($dry_ice) {
        \Drupal::logger('commerce_promotion')->notice('Dry ice box detected in line item: @line_item', ['@line_item' => $line_item->id()]);
        return true;
      }
    }
    return false; 
  }

  /**
   * Helper function to check if the shipping box field contains dry ice values.
   *
   * @param array $shipping_box_field
   *   The shipping box field values.
   *
   * @return bool
   *   TRUE if dry ice is found, FALSE otherwise.
   */
  protected function checkShippingBox(array $shipping_box_field) {
    foreach ($shipping_box_field as $item) {
      $value = $item['value'];
      if (!empty($value) && in_array($value, [
          'dry_ice_box',
          'small_dry_ice_box',
          'medium_dry_ice_box',
          'large_dry_ice_box'
      ])) {
        return true; 
      }
    }
    return false;
  }
}


