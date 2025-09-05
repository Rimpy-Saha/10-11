<?php

namespace Drupal\commerce_norpromotions\Plugin\Commerce\PromotionOffer;

use Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer\OrderPromotionOfferBase;
use Drupal\commerce_promotion\Plugin\Commerce\PromotionOffer\PercentageOffTrait;
use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides the percentage off offer for orders.
 *
 * The discount is split between order items, to simplify VAT taxes and refunds.
 *
 * @CommercePromotionOffer(
 *   id = "newly_registered_user",
 *   label = @Translation("Newly Registered User"),
 *   entity_type = "commerce_order",
 * )
 */
class NewlyRegisteredUser extends OrderPromotionOfferBase {

  use PercentageOffTrait;

  /**
   * {@inheritdoc}
   */
  public function apply(EntityInterface $entity, PromotionInterface $promotion) {
    $this->assertEntity($entity);

    $currentUid = \Drupal::currentUser()->id();

    // Query to fetch the number of orders for the current user.
    $query = \Drupal::database()->select('commerce_order', 'co');
    $query->addField('co', 'uid');
    $query->addExpression('COUNT(co.order_id)', 'num_orders');
    $query->condition('co.uid', $currentUid); // Filter by the current user's UID.
    $query->condition('co.order_number', NULL, 'IS NOT NULL'); // Ensure order_number is not NULL.

    // Execute the query and fetch the result.
    $result = $query->groupBy('co.uid')
                    ->execute()
                    ->fetchObject();

    // Extract num_orders from the query result.
    $numOrders = isset($result->num_orders) ? (int) $result->num_orders : 0;

    // Log the query and its result.
    \Drupal::logger('commerce_promotion')->notice('Query executed to check number of orders for user @uid. Found @num_orders orders.', [
        '@uid' => $currentUid,
        '@num_orders' => $numOrders,
    ]);

    // Check if the user has placed any orders.
    if ($numOrders > 0) {
        // // Log the number of orders found.
        // \Drupal::logger('commerce_promotion')->notice('User with @uid has placed @num_orders orders.', [
        //     '@uid' => $currentUid,
        //     '@num_orders' => $numOrders,
        // ]);
        
    } else {
        // Log that no orders were found.
        \Drupal::logger('commerce_promotion')->notice('User with @uid has not placed any orders.', [
            '@uid' => $currentUid,
        ]);

        // Apply the promotion to the order items.
        $order = $entity;
        $percentage = $this->getPercentage();
        $subtotal_price = $order->getSubtotalPrice();
        if (!$subtotal_price || !$subtotal_price->isPositive()) {
            return;
        }

        // Calculate the order-level discount and split it between order items.
        $amount = $subtotal_price->multiply($percentage);
        $amount = $this->rounder->round($amount);

        $total_price = $order->getTotalPrice();
        if ($total_price && $amount->greaterThan($total_price)) {
            $amount = $total_price;
        }

        // Skip applying the promotion if there's no amount to discount.
        if ($amount->isZero()) {
            return;
        }

        $amounts = $this->splitter->split($order, $amount, $percentage);

        // Apply the promotion to each order item.
        foreach ($order->getItems() as $orderItem) {
            if (isset($amounts[$orderItem->id()])) {
                $orderItem->addAdjustment(new Adjustment([
                    'type' => 'promotion',
                    'label' => $promotion->getDisplayName() ?: $this->t('Discount'),
                    'amount' => $amounts[$orderItem->id()]->multiply('-1'),
                    'percentage' => $percentage,
                    'source_id' => $promotion->id(),
                ]));
            }
        }
    }
  }
}
