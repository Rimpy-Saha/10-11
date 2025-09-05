<?php

namespace Drupal\commerce_norconditions\Plugin\Commerce\Condition;

use Drupal\commerce\Plugin\Commerce\Condition\ConditionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_price\Price;

/**
 * Provides a basic condition for orders.
 *
 * @CommerceCondition(
 *   id = "commerce_norconditions_order_specific_customer",
 *   label = @Translation("Order total of specific product variations"),
 *   display_label = @Translation("Order total of specific product variations"),
 *   entity_type = "commerce_order",
 * )
 */
class OrderTotalOfProductVariations extends ConditionBase
{

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration()
  {
    return [
      'operator' => '>',
      'amount' => NULL,
      'product_variations' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state)
  {
    $form = parent::buildConfigurationForm($form, $form_state);

    $amount = $this->configuration['amount'];

    if (isset($amount) && !isset($amount['number'], $amount['currency_code'])) {
      $amount = NULL;
    }

    // Adding field for product variations.
    $form['product_variations'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Product Variations'),
      '#description' => $this->t('Enter the SKUs of the product variations separated by commas. For example: 17200, 47050'),
      '#default_value' => $this->configuration['product_variations'],
      '#maxlength' => 999,
      '#required' => TRUE,
    ];

    $form['operator'] = [
      '#type' => 'select',
      '#title' => $this->t('Operator'),
      '#options' => $this->getComparisonOperators(),
      '#default_value' => $this->configuration['operator'],
      '#required' => TRUE,
    ];
    $form['amount'] = [
      '#type' => 'commerce_price',
      '#title' => $this->t('Amount'),
      '#default_value' => $amount,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state)
  {
    parent::submitConfigurationForm($form, $form_state);

    $values = $form_state->getValue($form['#parents']);
    $this->configuration['product_variations'] = $values['product_variations'];
    $this->configuration['operator'] = $values['operator'];
    $this->configuration['amount'] = $values['amount'];
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(EntityInterface $entity)
  {
    $this->assertEntity($entity);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $entity;

    // Initialize total price.
    $total_price = new Price('0', $order->getTotalPrice()->getCurrencyCode());

    //Issue with hiphen -- when hiphen is in SKU its not processed || Sowmya to-do
    //$product_variations = explode(',', $this->configuration['product_variations']);
    $product_variations = preg_split ('/(\s*,*\s*)*,+(\s*,*\s*)*/', $this->configuration['product_variations']);
    //$product_variations = explode(',', ' 27650, 45660, 63700, 45670-B, 27600, 55700, 49500');
    //\Drupal::logger('Commerce Promotion - Product Varitation Dash Test')->notice(print_r($product_variations, TRUE));

    //use pregmatch instead of comma explode 
    //$product_variations = preg_split('/,\s*/', $this->configuration['product_variations']);

    // Calculate the total price of product variations configured.
    // Iterate through each product variation.
    foreach ($product_variations as $product_variation_id) {
      // Check if the product variation is present in the order.
      foreach ($order->getItems() as $order_item) {
        if ($order_item->getPurchasedEntity()->getSku() == $product_variation_id) {

         

          // Get the price and quantity of the order item.
          $unit_price = $order_item->getUnitPrice();
          $quantity = $order_item->getQuantity();

         // \Drupal::logger('Commerce Promotion - Order Item SKU')->notice(print_r($unit_price),TRUE);

          // Calculate the subtotal of the order item.
          $subtotal = $unit_price->multiply($quantity);

          // Add the subtotal to the total price.
          $total_price = $total_price->add($subtotal);
          \Drupal::logger('Commerce Promotion - Total Price')->notice($total_price->getNumber());
        }
      }
    }
    

    if (!$total_price) {
      return FALSE;
    }
    $condition_price = Price::fromArray($this->configuration['amount']);
    if ($total_price->getCurrencyCode() != $condition_price->getCurrencyCode()) {
      return FALSE;
    }

    switch ($this->configuration['operator']) {
      case '>=':
        return $total_price->greaterThanOrEqual($condition_price);

      case '>':
        return $total_price->greaterThan($condition_price);

      case '<=':
        return $total_price->lessThanOrEqual($condition_price);

      case '<':
        return $total_price->lessThan($condition_price);

      case '==':
        return $total_price->equals($condition_price);

      default:
        throw new \InvalidArgumentException("Invalid operator {$this->configuration['operator']}");
    }
  }

}