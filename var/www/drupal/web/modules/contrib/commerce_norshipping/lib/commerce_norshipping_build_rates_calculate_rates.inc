<?php

/**
 * Calculate customer-specific Norgen shipping rates.
 *
 * This included file processes customer zone information and Norgen rates to calculate
 * customized shipping rates for the customer. It consumes $customer_zone and $norgen_rates
 * to generate $customer_rates.
 *
 * @param array $customer_zone An array containing customer zone details.
 * @param array $norgen_rates An array containing Norgen shipping rates.
 * @return array An array containing calculated customer-specific shipping rates.
 */

use Drupal\Core\Messenger\MessengerInterface;

if (empty($customer_zone)) {
    // Adding an error message using the messenger service
    \Drupal::messenger()->addError('Unspecified Location or Rate Error.');
    // Logging the error message
    \Drupal::logger('commerce_norshipping_build_rates_calculate_rates.inc')->error('Unspecified Location or Rate Error.');
    $rates['status'] = FALSE;
    $rates['message'] = 'Unspecified Location or Rate Error.';
    \Drupal::state()->set('shipping_error_thrown', TRUE);
} else {
    unset($customer_rates);
    $customer_rates = array();

    if ($customer_zone != '') {
        if (!empty($norgen_rates[$customer_zone])) {
            if ($customer_zone == 'CA') {
                if (!empty($norgen_rates[$customer_zone][$StateProvinceCode])) {
                    $customer_rates = $norgen_rates[$customer_zone][$StateProvinceCode];
                } else {
                    $customer_rates = $norgen_rates[$customer_zone]['OT'];
                }
            } elseif ($customer_zone == 'US') {
                //check for dry ice
                if (!empty($norgen_rates[$customer_zone]) && !$contains_dry_ice) {
                    $customer_rates = $norgen_rates[$customer_zone]['04'];
                    $customer_rates['multiple'] = array('04', '01');
                    $customer_rates['01'] = $norgen_rates[$customer_zone]['01'];
                    $customer_rates['04'] = $norgen_rates[$customer_zone]['04'];
                } else {
                    $customer_rates = $norgen_rates[$customer_zone]['01'];
                }
            } else {
                $customer_rates = $norgen_rates[$customer_zone];
            }
        } else {
            // Adding an error message using the messenger service
            \Drupal::messenger()->addError('Disabled Location Selected.');
            // Logging the error message
            \Drupal::logger('commerce_norshipping_build_rates_calculate_rates.inc')->error('Disabled Location Selected.');
            $rates['status'] = FALSE;
            $rates['message'] = 'Disabled Location Selected.';
            \Drupal::state()->set('shipping_error_thrown', TRUE);
        }
    }
}

