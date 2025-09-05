<?php

/**
 * Defines the Redirect Checkout Offsite Payment Gateway.
 *
 * This class extends the OffsitePaymentGatewayBase and provides implementations for
 * handling the configuration form, form submission, and actions upon return or cancellation
 * during an offsite payment process with redirect method.
 */

namespace Drupal\commerce_norelavon\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\commerce_order\Entity\OrderInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\commerce_payment\Exception\DeclineException;

/**
 * Provides the Elavon offsite Checkout payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "elavon_redirect_checkout",
 *   label = @Translation("Elavon (Redirect to elavon)"),
 *   display_label = @Translation("Elavon Payment Gateway"),
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_norelavon\PluginForm\PaymentOffsiteIFrameForm",
 *   },
 *   modes= {
 *     "test" = "Test transactions with your account",
 *     "production" = "Live transactions in a production account"
 *   },
 *   payment_method_types = {"credit_card"},
 *   credit_card_types = {
 *     "amex", "dinersclub", "discover", "jcb", "maestro", "mastercard", "visa",
 *   },
 * )
 */

class RedirectCheckout extends OffsitePaymentGatewayBase
{
    // Implementation for default configuration settings.
    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration()
    {
        return [
            'accountid' => '',
            'userid' => '',
            'pin' => '',
            'pin_ca' => '',
        ] + parent::defaultConfiguration();
    }

    // Implementation for building the configuration form.
    /**
     * {@inheritdoc}
     */
    public function buildConfigurationForm(array $form, FormStateInterface $form_state)
    {
        $form = parent::buildConfigurationForm($form, $form_state);

        $form['accountid'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Elavon Account ID'),
            '#description' => $this->t('Your Elavon Account ID'),
            '#default_value' => $this->configuration['accountid'],
            '#required' => TRUE,
            '#weight' => -5,
        ];

        $form['userid'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Elavon User ID'),
            '#description' => $this->t('Your Elavon user ID dedicated for web-based transactions.'),
            '#default_value' => $this->configuration['userid'],
            '#required' => TRUE,
            '#weight' => -4,
        ];

        $form['pin'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Elavon PIN for default Terminal'),
            '#description' => $this->t('Your Elavon PIN for the default Terminal'),
            '#default_value' => $this->configuration['pin'],
            '#required' => TRUE,
            '#weight' => -3,
        ];

        $form['pin_ca'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Elavon PIN for Canadian Terminal'),
            '#description' => $this->t('Your Elavon PIN for the Canadian Terminal'),
            '#default_value' => $this->configuration['pin_ca'],
            '#required' => FALSE,
            '#weight' => -3,
        ];

        return $form;
    }

    // Implementation for submitting the configuration form.
    /**
     * {@inheritdoc}
     */
    public function submitConfigurationForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitConfigurationForm($form, $form_state);
        $values = $form_state->getValue($form['#parents']);

        // Save the values to the configuration.
        $this->configuration['accountid'] = $values['accountid'];
        $this->configuration['userid'] = $values['userid'];
        $this->configuration['pin'] = $values['pin'];
        $this->configuration['pin_ca'] = $values['pin_ca'];
    }

    // Implementation for actions upon successful return from the offsite payment process.
    /**
     * {@inheritdoc}
     * @param OrderInterface $order The order object related to the payment.
     * @param Request $request The request object containing the return data from the Elavon Converge payment process.
     * @throws DeclineException If the status in the response is not approval.
     */
    public function onReturn(OrderInterface $order, Request $request)
    {
        /* The values that we are fetching from Elavon Response are : 
        ['elavon_status'] || ['ssl_result_message'] || ['ssl_txn_id'] || ['ssl_result'] || ['ssl_avs_response'] || ['ssl_cvv2_response'] || ['errorCode'] */

        //The status of Transaction : error || cancelled || declined || approval
        $elavon_status = isset($_POST['elavon_status']) ? $_POST['elavon_status'] : 'error';
        $elavon_status = strtolower($elavon_status);
        $state = ($elavon_status == 'approval') ? 'Completed' : $elavon_status;

        //Approval
        $ssl_transaction_reference_number = isset($_POST['ssl_transaction_reference_number']) ? $_POST['ssl_transaction_reference_number'] : '';
        $ssl_result_message = isset($_POST['ssl_result_message']) ? $_POST['ssl_result_message'] : 'error';
        $ssl_avs_response = isset($_POST['ssl_avs_response']) ? $_POST['ssl_avs_response'] : '';
        $ssl_cvv2_response = isset($_POST['ssl_cvv2_response']) ? $_POST['ssl_cvv2_response'] : '';

        //Declined
        $errorMessage = isset($_POST['errorMessage']) ? $_POST['errorMessage'] : '';
        $errorCode = isset($_POST['errorCode']) ? $_POST['errorCode'] : '';

        //Creating a response message with AVS and CVV2 codes mapped to be entered to DB
        $avsResponseMessage = $this->generateAVSResponseMessage($ssl_avs_response, $ssl_cvv2_response);

        // Creates a payment object and stores it in the database if status is approval
        $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');

        if ($state === 'Completed') {
            $payment = $payment_storage->create([
                'remote_id' => $ssl_transaction_reference_number,
                'state' => "authorization",
                'amount' => $order->getTotalPrice(),
                'payment_gateway' => $this->entityId,
                'order_id' => $order->id(),
                'remote_state' => $ssl_result_message,
                'avs_response_code' => $ssl_avs_response,
                'avs_response_code_label' => $avsResponseMessage,
            ]);
            $payment->setState('completed');
            $payment->save();
            \Drupal::messenger()->addStatus('Payment was processed');
        } else if ($state === 'error') {
            \Drupal::messenger()->addStatus('Payment was NOT processed: An error occurred during payment processing, please try again!');
            throw new DeclineException();
        } else if ($state === 'cancelled') {
            \Drupal::messenger()->addStatus('Transaction canceled. Feel free to proceed with the purchase whenever you are ready');
            throw new DeclineException();
        } else if ($state === 'declined') {
            $declineMessage = $this->createDeclineMessage($elavon_status, $errorMessage, $errorCode);
            \Drupal::messenger()->addStatus('Transaction declined: please try again!', TRUE);
            if ($declineMessage) {
                \Drupal::messenger()->addStatus($declineMessage);
            }
            throw new DeclineException();
        } else {
            \Drupal::messenger()->addStatus('Payment was NOT processed');
            throw new DeclineException();
        }
    }

    /**
     * Validates an Elavon response and creates a decline message.
     *
     * @param string $elavon_status The Elavon status.
     * @param string $ssl_result_message The result message.
     * @param string $errorCode The error code.
     * @return string The decline message if the transaction is declined by Elavon.
     */
    public function createDeclineMessage($elavon_status, $errorMessage, $errorCode)
    {
        $declineMessage = '';

        if ($elavon_status == 'declined') {
            $declineMessage .= t('We received the following error processing your card. Please enter your information again or try a different card.');
            if (!empty($errorMessage)) {
                $declineMessage .= ' ' . t($errorMessage) . '.';
            }
        }

        if (!empty($errorCode)) {
            $errorCode = (string) $errorCode;
            $msg = 'There was an error processing your request. Please contact customer service at orders@norgenbiotek.com for help.';
            if ($errorCode == '3000') {
                $msg = 'There was an error processing your request. Please try again. If this issue persists, please contact customer service at orders@norgenbiotek.com for help.';
            } elseif ($errorCode == '4005') {
                $msg = 'The E-mail Address supplied in the authorization request appears to be invalid.';
            } elseif ($errorCode == '4006') {
                $msg = 'The CVV2 indicator was not identified in the authorization request.';
            } elseif ($errorCode == '4007') {
                $msg = 'CVV2 check cannot be performed as no data was supplied in the authorization request.  ';
            } elseif ($errorCode == '4009') {
                $msg = 'A required field was not supplied in the authorization request. Please make sure all address fields are entered correctly, and that a first and last name are provided. If the issue persists, please contact customer service at orders@norgenbiotek.com for help.';
            } elseif ($errorCode == '4013' || $errorCode == '4015') {
                $msg = 'The PIN was not supplied in the authorization request.';
            } elseif ($errorCode == '4017') {
                $msg = 'The request has timed out. The allotted time to complete the request has ended. Please try again.  ';
            } elseif ($errorCode == '5000') {
                $msg = 'The Credit Card Number supplied in the authorization request appears to be invalid.  ';
            } elseif ($errorCode == '5001') {
                $msg = 'The Credit Card Expiration Date supplied in the authorization request appears to be invalid.';
            } elseif ($errorCode == '5005') {
                $msg = 'The value for the # field is too long. # characters (maximum) are allowed. Your entry contains # characters.<br>If you entered the value for this field, use the browser BACK button to return to the order form and modify the field value accordingly. Otherwise, contact Customer Service at orders@norgenbiotek.com.';
            } elseif ($errorCode == '5019') {
                $msg = 'Minimum Field Character Limit not reached.';
            } elseif ($errorCode == '5021') {
                $msg = 'The value for the CVV2 (ssl_cvv2cvc2) field should either be 3 or 4 digits in length. This value must be Numeric.';
            } elseif ($errorCode == '5022') {
                $msg = 'The value for the CVV2 indicator(ssl_cvv2cvc2_indicator) field should be 1 Numeric Character only. Valid values are: 0, 1, 2, 9.';
            } elseif ($errorCode == '5093') {
                $msg = 'The transaction has timed out, you may retry at later time.';
            } elseif ($errorCode > '6001' && $errorCode < '6024') {
                $msg = 'This transaction request has not been approved. You may elect to use another form of payment to complete this transaction or contact customer service for additional options.';
            }
            // $declineMessage .= t($msg);
        }

        return $declineMessage;
    }

    /**
     * Generates a response message based on AVS and CVV2 responses.
     *
     * @param string $ssl_avs_response The AVS response.
     * @param string $ssl_cvv2_response The CVV2 response.
     * @return string The generated response message.
     */
    public function generateAVSResponseMessage($ssl_avs_response, $ssl_cvv2_response)
    {
        //Save the generated message to database
        //Using the StringTranslationTrait for translation functions.
        //$this->stringTranslation = \Drupal::service('string_translation');

        // Build a meaningful response message.
        //$stat = $this->t('@type : ACCEPTED', ['@type' => 'direct_hosting']);

        $avs = '';
        $cvv = '';
        if ($ssl_avs_response !== '') {
            $avs = $this->avsResponseCode($ssl_avs_response);
        }
        if ($ssl_cvv2_response != '') {
            $cvv = $this->cvvResponseCode($ssl_cvv2_response);
        }

        // Use an array to hold the translation functions.
        $message = [
            $this->t('AVS response: @avs', ['@avs' => $avs]),
            $this->t('CVV2 response: @cvv', ['@cvv' => $cvv]),
        ];

        // Concatenate the messages into a single string.
        $resultMessage = implode(' ', $message);

        return $resultMessage;
    }

    /**
     * Returns the message text for an AVS response code.
     *
     * @see AVS Response Codes (Transaction API Guide)
     */
    public function avsResponseCode($code)
    {
        if ($code == 'A') {
            return t('Address: Address matches, Zip does not');
        } else if ($code == 'B') {
            return t('Street Match: Street addresses match for international transaction, but postal code doesn t');
        } else if ($code == 'C') {
            return t('Street Address: Street addresses and postal code not verified for international transaction');
        } else if ($code == 'D') {
            return t('Match: Street addresses and postal codes match for international transaction');
        } else if ($code == 'E') {
            return t('Error: Transaction unintelligible for AVS or edit error found in the message that prevents AVS from being performed');
        } else if ($code == 'G') {
            return t('Unavailable: Address information not available for international transaction');
        } else if ($code == 'I') {
            return t('Not Verified: Address Information not verified for International transaction');
        } else if ($code == 'M') {
            return t('Match: Street addresses and postal codes match for international transaction');
        } else if ($code == 'N') {
            return t('No: Neither address nor Zip matches');
        } else if ($code == 'P') {
            return t('Postal Match: Postal codes match for international transaction, but street address doesn t');
        } else if ($code == 'R') {
            return t('Retry: System unavailable or time-out');
        } else if ($code == 'S') {
            return t('Not Supported: Issuer doesn t support AVS service');
        } else if ($code == 'U') {
            return t('Unavailable: Address information not available');
        } else if ($code == 'W') {
            return t('Whole Zip: 9-digit Zip matches, address doesn t');
        } else if ($code == 'X') {
            return t('Exact: Address and nine-digit Zip match');
        } else if ($code == 'Y') {
            return t('Yes: Address and five-digit Zip match');
        } else if ($code == 'Z') {
            return t('Whole Zip: 9-digit Zip matches, address doesn t');
        } else if ($code == '0') {
            return t('No response sent');
        } else if ($code == '5') {
            return t('Invalid AVS response');
        }
        return $code;
    }

    /**
     * Returns the message text for a CVV match.
     *
     * @see CVV Response Codes (Transaction API Guide)
     */
    public function cvvResponseCode($code)
    {
        if ($code == 'M') {
            return t('CVV2/CVC2/CID Match');
        } else if ($code == 'N') {
            return t('CVV2/CVC2/CID No Match');
        } else if ($code == 'P') {
            return t('Not Processed');
        } else if ($code == 'S') {
            return t('Issuer indicates that the CV data should be present on the card, but the merchant has indicated that the CV data is not present on the card.');
        } else if ($code == 'U') {
            return t('Unknown / Issuer has not certified for CV or issuer has not provided Visa/MasterCard with the CV encryption keys.');
        } else if ($code == 'X') {
            return t('Server Provider did not respond');
        }
        return $code;
    }

}