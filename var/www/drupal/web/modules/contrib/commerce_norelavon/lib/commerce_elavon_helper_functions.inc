<?php

/**
 * Commerce Elavon Helper Functions
 *
 * This file contains a collection of helper functions used to facilitate various tasks
 * within the Commerce Elavon module. 
 *
 * @file
 */

use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Commerce Payment Method Information for Elavon Integration
 *
 * This function provides information about the Elavon payment method.
 * It defines the characteristics of the payment method, such as its title, description, and
 * integration details. 
 *
 * @return array $payment_methods An array containing information about the Elavon payment method.
 *
 */
function commerce_payment_method_info()
{
    $payment_methods = array();

    $payment_methods['elavon'] = array(
        'base' => 'commerce_elavon',
        'title' => t('Credit card'),
        'short_title' => t('Credit card'),
        'display_title' => t('Credit card'),
        'description' => t('Integrates Elavon processing payment'),
        'terminal' => FALSE,
        'offsite' => FALSE,
        'offsite_autoredirect' => FALSE,
    );

    return $payment_methods;
}

/**
 * Returns the Elavon URL's to the Payment Gateway module determined by transaction mode for calls to Elavon
 *
 * @param $txn_mode
 *   The transaction mode that relates to the production or test server.
 *
 * @return
 *   The URL to be used to submit requests to the Elavon server.
 */
function commerce_elavon_token_url($txn_mode)
{
    switch ($txn_mode) {
        case 'production':
            return 'https://api.convergepay.com/hosted-payments/transaction_token';

        case 'test':
            return 'https://api.convergepay.com/hosted-payments/transaction_token';
    }
}

/**
 * Retrieve Billing Information for Elavon Payment Processing
 *
 * This function retrieves billing information related to the specified order for processing
 * with the Elavon payment method. 
 *
 * @param \Drupal\commerce_order\Entity\OrderInterface $order
 *   The order entity for which billing information is to be retrieved.
 *
 * @return array $billing_data
 *   An array containing the billing information required for Elavon payment processing.
 */
function commerce_elavon_get_billing_info(OrderInterface $order)
{
    $billing_data = array(
        'street' => '',
        'street2' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
        'country' => '',
        'name_on_card' => '',
        'first_name' => '',
        'last_name' => '',
    );

    $billing_profile = $order->getBillingProfile();
    if ($billing_profile) {
        $billing_address = $billing_profile->get('address')->first();
        if ($billing_address) {
            $name_on_card = substr($billing_address->getGivenName(), 0, 50) . ' ' . substr($billing_address->getFamilyName(), 0, 50);

            $billing_data['street'] = substr($billing_address->getAddressLine1(), 0, 30);
            $billing_data['street2'] = substr($billing_address->getAddressLine2(), 0, 30);
            $billing_data['city'] = substr($billing_address->getLocality(), 0, 30);
            $billing_data['state'] = substr($billing_address->getAdministrativeArea(), 0, 30);
            $billing_data['zip'] = substr($billing_address->getPostalCode(), 0, 9);
            $billing_data['country'] = $billing_address->getCountryCode();
            $billing_data['name_on_card'] = $name_on_card;
            $billing_data['first_name'] = $billing_address->getGivenName();
            $billing_data['last_name'] = $billing_address->getFamilyName();
        }
    }

    return $billing_data;
}

function commerce_norelavon_get_shipping_info(OrderInterface $order)
{
    $shipping_data = array(
        'street' => '',
        'street2' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
        'country' => '',
        'name' => '',
    );


   
    $profiles = $order->collectProfiles();
    
    $shipping_profile = isset($profiles['shipping']) ? $profiles['shipping'] : NULL;

    if ($shipping_profile) 
    {
        // Extract the address field.
        $shipping_address = $shipping_profile->get('address')->first();
        if ($shipping_address) {
            $shipping_data['street'] = substr($shipping_address->getAddressLine1(), 0, 30);
            $shipping_data['street2'] = substr($shipping_address->getAddressLine2(), 0, 30);
            $shipping_data['city'] = substr($shipping_address->getLocality(), 0, 30);
            $shipping_data['state'] = substr($shipping_address->getAdministrativeArea(), 0, 30);
            $shipping_data['zip'] = substr($shipping_address->getPostalCode(), 0, 9);
            $shipping_data['country'] = $shipping_address->getCountryCode();
            $shipping_data['first_name'] = $shipping_address->getGivenName();
            $shipping_data['last_name'] = $shipping_address->getFamilyName();
        }
    }
    return $shipping_data;
}

?>