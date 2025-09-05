<?php

/**
 * Request Token from Elavon for Payment Processing
 *
 * This function initiates a request to Elavon's API to obtain a payment token for processing
 * the specified payment. It utilizes cURL to communicate with the Elavon server based on the
 * provided payment method, billing information, and plugin configuration. The result token is
 * returned upon a successful API call, or an empty string is returned if the request fails.
 *
 * @param string $payment_method
 *   The payment method identifier indicating the type of payment being processed.
 *
 * @param array $info
 *   An array containing billing details and other information necessary for the token request.
 *
 * @param array $requestdata
 *   An array containing plugin configuration details required for building the token request.
 *
 * @return string $result_token
 *   The payment token obtained from Elavon's API, or an empty string if the request fails.
 */

\Drupal::moduleHandler()->loadInclude('commerce_norelavon', 'inc', 'lib/commerce_elavon_convert_country_code');

function commerce_elavon_request_token($payment_method, $info = array(), $requestdata = array())
{
    // Get the API endpoint URL for the method's transaction mode and type.
    $url_token = commerce_elavon_token_url($requestdata['txn_mode']);

    // Add the default name-value pairs to the array.
    $info += array(
        'ssl_merchant_id' => $requestdata['accountid'],
        'ssl_user_id' => $requestdata['userid'],
        'ssl_pin' => $requestdata['pin'],
        'ssl_pin_ca' => $requestdata['pin_ca'],
    );

    //Update PIN based on Terminal to be used
    $ssl_pin_chosen = $info['ssl_pin'];
    if ($info['ssl_shipping_country'] == 'CA') {
        $ssl_pin_chosen = $info['ssl_pin_ca'];
    }

    $converted_country_code = '';
    $converted_country_code = commerce_elavon_convert_country_code($info['ssl_country']);

    // Prepare opts for Elavon.
    $curlpost = "ssl_merchant_id=" . $info['ssl_merchant_id'];
    $curlpost .= "&ssl_user_id=" . $info['ssl_user_id'];
    $curlpost .= "&ssl_pin=" . $ssl_pin_chosen;
    $curlpost .= "&ssl_transaction_type=ccauthonly";
    $curlpost .= "&ssl_invoice_number=" . $info['ssl_invoice_number'];
    $curlpost .= "&ssl_amount=" . $info['ssl_amount'];
    $curlpost .= "&ssl_avs_address=" . $info['ssl_avs_address'];
    $curlpost .= "&ssl_avs_zip=" . $info['ssl_avs_zip'];
    $curlpost .= "&ssl_first_name=" . $info['ssl_first_name'];
    $curlpost .= "&ssl_last_name=" . $info['ssl_last_name'];
    $curlpost .= "&ssl_city=" . $info['ssl_city'];
    $curlpost .= "&ssl_country=" . $converted_country_code;
    $curlpost .= "&ssl_state=" . $info['ssl_state'];
    $curlpost .= "&ssl_salestax=" . $info['ssl_salestax'];

    // Setup the cURL request.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_token);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "$curlpost");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_VERBOSE, true);

    $result_token = curl_exec($ch);
    //\Drupal::logger('commerce_elavon_request_token.inc')->error('CURL post: @post', ['@post' => $curlpost]);

    // Log any errors
    if ($error = curl_error($ch)) {
        \Drupal::logger('commerce_elavon_request_token.inc')->error('CURL error: @error', ['@error' => $error]);
    }

    curl_close($ch);

    // If we received data back from the server, but token is empty
    if (empty($result_token)) {
        \Drupal::logger('commerce_elavon_request_token.inc')->error('CURL error: Empty token returned.');
    }

    return $result_token;
}




