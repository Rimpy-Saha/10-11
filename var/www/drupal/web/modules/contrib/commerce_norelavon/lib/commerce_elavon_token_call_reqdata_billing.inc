<?php

/**
 * Build Token Call Request Data for Elavon Billing Information
 *
 * This function is responsible for constructing the $info array parameter used in the
 * commerce_elavon_token_call_main function. It includes billing details necessary for
 * initiating a token call request to Elavon's API. 
 *
 * @Uses \Drupal\commerce_order\Entity\OrderInterface $order
 *   The order entity containing billing information for constructing the token call request.
 *
 * @Build array $info
 *   An array containing billing details required for building the token call request.
 */

use Drupal\commerce_order\Entity\Order;

// Including the file containing the Helper function.
module_load_include('inc', 'commerce_norelavon', 'lib/commerce_elavon_helper_functions');

$billing_data = commerce_elavon_get_billing_info($order);
$shipping_data = commerce_norelavon_get_shipping_info($order);
$card_name_parts = explode(' ', $billing_data['name_on_card']);
$card_name_array['first_name'] = array_shift($card_name_parts);
$card_name_array['last_name'] = implode(' ', $card_name_parts);

// Set Tax amount
$sales_tax_total = 0;
$order_total = 0;
$currency_code = 'USD';

// Loop through and get all tax elements and add them up
$order_totals = $order->get('total_price')->getValue();
$order_total = $order_totals[0]['number'];
$order_currency_code = $order_totals[0]['currency_code'];

$order_components = $order->getData('components');
if (!empty($order_components)) {
    foreach ($order_components as $order_working_array_element) {
        if (strpos($order_working_array_element['name'], 'tax') !== FALSE) {
            $sales_tax_total += $order_working_array_element['price']['number'];
        }
    }
}

$ssl_amount = $order_total;
$ssl_salestax = $sales_tax_total;

// Build a name-value pair array for this transaction.
$info = [
    'ssl_avs_address' => $billing_data['street'],
    'ssl_avs_zip' => $billing_data['zip'],
    'ssl_first_name' => $card_name_array['first_name'],
    'ssl_last_name' => $card_name_array['last_name'],
    'ssl_city' => $billing_data['city'],
    'ssl_country' => $billing_data['country'],
    'ssl_shipping_country' => $shipping_data['country'],
    'ssl_state' => $billing_data['state'],
    'ssl_transaction_type' => 'ccauthonly',
    'ssl_amount' => $ssl_amount,
    'ssl_salestax' => $ssl_salestax,
    'ssl_invoice_number' => $order->id(),
];

?>