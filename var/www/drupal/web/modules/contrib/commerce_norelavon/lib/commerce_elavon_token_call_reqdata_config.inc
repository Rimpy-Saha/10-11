<?php

/**
 * Build Token Call Request Data for Elavon Plugin Configuration
 *
 * This function is responsible for constructing the $requestdata array parameter used in the
 * commerce_elavon_token_call_main function. It includes plugin configuration details necessary
 * for initiating a token call request to Elavon's API.
 *
 * @Uses Plugin Configuration
 *   Configuration settings for the Elavon payment plugin.
 *
 * @Build array $requestdata
 *   An array containing plugin configuration details required for building the token call request.
 */

/** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface $payment_gateway_plugin */
$payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
$configuration = $payment_gateway_plugin->getConfiguration();

// Payment gateway configuration data.
$requestdata['accountid'] = $configuration['accountid'];
$requestdata['userid'] = $configuration['userid'];
$requestdata['pin'] = $configuration['pin'];
$requestdata['pin_ca'] = $configuration['pin_ca'];
$requestdata['txn_mode'] = $configuration['mode'];
?>