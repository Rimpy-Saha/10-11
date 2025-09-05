<?php

/**
 * Main function for building Norgen shipping rates.
 *
 * This function orchestrates the process of invoking sub-libraries to pack and calculate rates 
 * for the given shipment using Norgen shipping services.
 *
 * @param array $shipment An array containing shipment details.
 * @return array An array containing calculated shipping rates for the given shipment.
 */

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_shipping\Entity\ShipmentItem;

include __DIR__ . '/commerce_norshipping_package_types.inc';
include __DIR__ . '/commerce_norshipping_package_information.inc';
include __DIR__ . '/commerce_norshipping_zone_rates.inc';
include __DIR__ . '/commerce_norshipping_zone_types.inc';
include __DIR__ . '/commerce_norshipping_zone_information.inc';

function commerce_norshipping_build_rates_main($shipment)
{
    // Get the shipment's order ID.
    $order_id = $shipment->getOrderId();

    // Load the order entity using the order ID.
    $order = \Drupal\commerce_order\Entity\Order::load($order_id);

    /**
     * Set up shipping rates array
     */
    unset($rates);
    $rates = array();
    $customer_rates = array();
    $rates['status'] = TRUE;
    $rates['cost'] = 0;
    $rates['message'] = '';
    $rates['service'] = '99';
    $rates['order_packages'] = array();
    $number_of_packages = 0;
    if (array_key_exists('multiple', $rates)) {
        unset($rates['multiple']);
    }
    if (array_key_exists('01', $rates)) {
        unset($rates['01']);
    }
    if (array_key_exists('04', $rates)) {
        unset($rates['04']);
    }
    /**
     * End of : Set up shipping rates array
     */

    /**
     * Set up Package Information
     */
    $package_key = commerce_norshipping_package_types();
    $norgen_package = commerce_norshipping_package_information($package_key);
    /**
     * End of : Set up Package Information
     */

    /**
     * Set up Zone Information
     */
    $zone_key = commerce_norshipping_zone_types();
    $zones = commerce_norshipping_zone_information($zone_key);
    /**
     * End of : Set up Zone Information
     */

    /* 
       Set up : Shipping Address Information
    */
    $shipment_address = $shipment->getShippingProfile()->get('address')->first();
    $CountryCode = $shipment_address->getCountryCode();
    $StateProvinceCode = $shipment_address->getAdministrativeArea();
    $PostalCode = $shipment_address->getPostalCode();
    $country = $CountryCode;
    /**
     * End of : Set up Shipping Address Information
     */

    // Invoking Calculate Packing
    include __DIR__ . '/commerce_norshipping_build_rates_calculate_packing.inc';
    /* Output : $order_packages || $num_packages {type : array} */

    /* 
       Finding Customer Zone
    */
    $customer_zone = '';
    foreach ($zones as $key => $value) {
        if (in_array($CountryCode, $zones[$key]['countries'])) {
            $customer_zone = $key;
        }
    }
    /**
     * End of : Finding Customer Zone
     */

    /**
     * Set up Rate Information
     */
    if (!(empty($customer_zone))) {
        $norgen_rates = commerce_norshipping_zone_rates($zone_key);
    }
    /**
     * End of : Set up Rate Information
     */

    //Invoking Build Rates Calculate Rates
    include __DIR__ . '/commerce_norshipping_build_rates_calculate_rates.inc';
    /* Output : $customer_rates {type : array} */

    $package_number = 0;
    if ($rates['status'] == TRUE) {
        $rates['order_packages'] = $order_packages;
        $total = 1;
        for ($pkn = 0; $pkn < $number_of_packages; $pkn++) {
            $pkg_type = $order_packages[$pkn]['package'];
            if (!empty($customer_rates[$pkg_type])) {
                $rates['service'] = $customer_rates['service'];
                $rates['cost'] += $customer_rates[$pkg_type];
                $package_number++;
            } else {
                // Adding an error message using the messenger service
                \Drupal::messenger()->addError('Unspecified Location or Rate Error.');
                // Logging the error message
                \Drupal::logger('commerce_norshipping_build_rates_calculate_rates.inc')->error('Unspecified Location or Rate Error.');
                $rates['status'] = FALSE;
                $rates['message'] = 'Unspecified Location or Rate Error.';
                \Drupal::state()->set('shipping_error_thrown', TRUE);
            }
        }
        if ($rates['status'] == TRUE && $CountryCode == 'US' && array_key_exists('multiple', $customer_rates) && !empty($customer_rates['multiple'])) {
            $rates['multiple'] = $customer_rates['multiple'];
            $rates['01']['cost'] = 0;
            $rates['04']['cost'] = 0;
            for ($pkn = 0; $pkn < $number_of_packages; $pkn++) {
                $pkg_type = $order_packages[$pkn]['package'];
                if (!empty($customer_rates[$pkg_type])) {
                    $rates['01']['cost'] += $customer_rates['01'][$pkg_type];
                    $rates['04']['cost'] += $customer_rates['04'][$pkg_type];
                    $package_number++;
                } else {
                    // Adding an error message using the messenger service
                    \Drupal::messenger()->addError('Unspecified Location or Rate Error.');
                    // Logging the error message
                    \Drupal::logger('commerce_norshipping_build_rates_calculate_rates.inc')->error('Unspecified Location or Rate Error.');
                    $rates['status'] = FALSE;
                    $rates['message'] = 'Unspecified Location or Rate Error.';
                    \Drupal::state()->set('shipping_error_thrown', TRUE);
                }
            }
        }
    }
    return $rates;
}






