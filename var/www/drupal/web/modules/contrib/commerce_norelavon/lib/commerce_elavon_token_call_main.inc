<?php

/**
 * Main Token Call Function for Elavon Payment Processing
 *
 * This function is invoked from the PluginForm to initiate the process of obtaining a token
 * from Elavon for the specified payment. 
 *
 * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
 *   The payment entity for which the token is being requested.
 *
 * @return $token
 *   The obtained payment token from Elavon
 */

use Drupal\Core\Form\FormStateInterface;

// Including the file containing Main Build Rate function.
\Drupal::moduleHandler()->loadInclude('commerce_norelavon', 'inc', 'lib/commerce_elavon_token_call_request');
\Drupal::moduleHandler()->loadInclude('commerce_norelavon', 'inc', 'lib/commerce_elavon_helper_functions');

function commerce_elavon_token_call_main($payment)
{
    include __DIR__ . '/commerce_elavon_token_call_reqdata_config.inc';

    // Order and billing address.
    $order = $payment->getOrder();

    include __DIR__ . '/commerce_elavon_token_call_reqdata_billing.inc';

    //We get $info populated from above file
    //Call token method with info and payment method and get the token from elavon
    $payment_methods = commerce_payment_method_info();
    $payment_method = $payment_methods['elavon'];

    $token = commerce_elavon_request_token($payment_method, $info, $requestdata);

    return $token;
}

?>