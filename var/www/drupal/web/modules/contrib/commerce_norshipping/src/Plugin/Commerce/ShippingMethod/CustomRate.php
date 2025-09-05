<?php

/**
 * Provides a custom shipment method for Norgen Shipping Rates based on UPS services.
 *
 * This plugin calculates shipping rates specific to Norgen shipments w.r.t UPS services.
 *
 * @plugin file
 */

namespace Drupal\commerce_norshipping\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\PackageTypeManagerInterface;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Drupal\Core\Form\FormStateInterface;
use Drupal\state_machine\WorkflowManagerInterface;

use Drupal\user\Entity\User;

// Including the file containing Main Build Rate function.
module_load_include('inc', 'commerce_norshipping', 'lib/commerce_norshipping_main');

/**
 * Provides the FlatRatePerItem shipping method.
 *
 * @CommerceShippingMethod(
 *   id = "norgen_shipment_rate",
 *   label = @Translation("Norgen Biotek Shipment Rate"),
 * )
 */
class CustomRate extends ShippingMethodBase
{
    /**
     * Constructs a new Rate object.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param \Drupal\commerce_shipping\PackageTypeManagerInterface $package_type_manager
     *   The package type manager.
     * @param \Drupal\state_machine\WorkflowManagerInterface $workflow_manager
     *   The workflow manager.
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, PackageTypeManagerInterface $package_type_manager, WorkflowManagerInterface $workflow_manager)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $package_type_manager, $workflow_manager);

        $this->services['default'] = new ShippingService('default', $this->configuration['rate_label']);
    }

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration()
    {
        return [
            'rate_label' => '',
            'rate_description' => '',
            'services' => ['default'],
        ] + parent::defaultConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function buildConfigurationForm(array $form, FormStateInterface $form_state)
    {
        $form = parent::buildConfigurationForm($form, $form_state);
        // Check if the 'rate_label' key exists in $this->configuration before accessing it.
        $rate_label = isset($this->configuration['rate_label']) ? $this->configuration['rate_label'] : '';
        $rate_description = isset($this->configuration['rate_description']) ? $this->configuration['rate_description'] : '';
        $form['rate_label'] = [
            '#type' => 'textfield',
            '#title' => t('Rate label'),
            '#description' => t('Shown to customers when selecting the rate.'),
            '#default_value' => $this->configuration['rate_label'],
            '#required' => TRUE,
        ];
        $form['rate_description'] = [
            '#type' => 'textfield',
            '#title' => t('Rate description'),
            '#description' => t('Provides additional details about the rate to the customer.'),
            '#default_value' => $this->configuration['rate_description'],
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitConfigurationForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitConfigurationForm($form, $form_state);

        if (!$form_state->getErrors()) {
            $values = $form_state->getValue($form['#parents']);
            $this->configuration['rate_label'] = $values['rate_label'];
            $this->configuration['rate_description'] = $values['rate_description'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function calculateRates(ShipmentInterface $shipment)
    {
        $service1 = new ShippingService('UPS_Standard', 'UPS Standard');
        $service2 = new ShippingService('UPS_Express_Saver', 'UPS Express Saver');
        $service3 = new ShippingService('UPS_3_Day_Select', 'UPS 3 Day Select');

        // Initial total shipping cost.
        $ship_price = 0;
        $ship_price_express_saver = 0;
        $ship_price_3_day_select = 0;
        $rate_calculated = array();
        $rate_calculated_express_saver = array();
        $rate_calculated_3_day_select = array();

        $shipment_address = $shipment->getShippingProfile()->get('address')->first();
        $CountryCode = $shipment_address->getCountryCode();

        //Restricting shipping to the below countries 
        $excluded_country_codes = [
            'CN', // China  
            'MX', // Mexico   
            'DE', // Germany 
            'IL', // Israel 
            'KR', // South Korea
            'JP', // Japan
            'KE', // Kenya
            'GB', // UK
            'TR', // Turkey
            'AZ', // Azerbaijan
            'CH', // Switzerland
            'HK', // Hong Kong
            'MO', // Macau
            'AU', // Australia
            'DK', // Denmark
            'FI', // Finland
            'NO', // Norway
            'SE', // Sweden
            'NL', // The Netherlands
            'SG', // Singapore
            'ID', // Indonesia
            'MM', // Myanmar
            'IT', // Italy
            'ES', // Spain
        ];

        // Check if the shipping country code is in the list of excluded country codes.
        if (in_array($CountryCode, $excluded_country_codes)) {
            $description = 'No shipping rate';
            $service4 = new ShippingService('Error_Service', $description);
            $rates[] = new ShippingRate([
                'shipping_method_id' => $this->parentEntity->id(),
                'service' => $service4,
                'description' => $description,
            ]);
            return $rates;
        }

        // Get the shipment's order ID.
        $order_id = $shipment->getOrderId();
        // Load the order entity using the order ID.
        $order = \Drupal\commerce_order\Entity\Order::load($order_id);

        //Fetching the latest currency code
        $currency_code = $order->getTotalPrice()->getCurrencyCode();

        // Reset the state variable
        \Drupal::state()->delete('shipping_error_thrown');

        //Changing Roles of User based on Shipping Address
        $logged_in = \Drupal::currentUser()->isAuthenticated();
        if (!$logged_in && $CountryCode == 'CA') {
            // \Drupal::messenger()->addError('Please Log in or create an account to access Canadian Pricing and checkout.');
            // \Drupal::state()->set('shipping_error_thrown', TRUE);
            // $description = 'No shipping rate';
            // $service4 = new ShippingService('Error_Service', $description);
            // $rates[] = new ShippingRate([
            //     'shipping_method_id' => $this->parentEntity->id(),
            //     'service' => $service4,
            //     'description' => $description,
            // ]);
            // return $rates;
            $currency_code = 'CAD';
        } 
        else if(!$logged_in && $CountryCode != 'CA')
        {
            $currency_code = 'USD';
        }
        else 
        {
            $user = \Drupal::currentUser();
            $roles = $user->getRoles();

            if (in_array('canadian', $roles) && $CountryCode != 'CA') {
                // If user have role "canadian" but the Ship To Country selected is not "Canada" 
                // Load the user entity
                $user_entity = User::load($user->id());
                // Remove the "canadian" role from the user account
                $user_entity->removeRole('canadian');

                //Update User country field
                $user_entity->set('field_country', $CountryCode);
                $user_entity->save();

                $currency_code = 'USD';

                $status_messages = \Drupal::messenger()->messagesByType('status');
                \Drupal::messenger()->deleteByType('status');
                foreach ($status_messages as $status_message) {
                    if (str_contains(strtolower($status_message), 'rebuild permissions')) {
                    } else {
                        \Drupal::messenger()->addMessage($status_message, 'status');
                    }
                }

            } else if (!in_array('canadian', $roles) && $CountryCode == 'CA') {
                // If user does not have role "canadian" but the Ship To Country selected is "Canada" 
                // Load the user entity
                $user_entity = User::load($user->id());
                // Add the "canadian" role to the user account
                $user_entity->addRole('canadian');

                //Update User country field
                $user_entity->set('field_country', $CountryCode);
                $user_entity->save();

                $currency_code = 'CAD';

                $status_messages = \Drupal::messenger()->messagesByType('status');
                \Drupal::messenger()->deleteByType('status');
                foreach ($status_messages as $status_message) {
                    if (str_contains(strtolower($status_message), 'rebuild permissions')) {
                    } else {
                        \Drupal::messenger()->addMessage($status_message, 'status');
                    }
                }

            }
            \Drupal::logger('commerce_norshipping')->error('The culprit for changing the currency code. '.json_encode($user->getRoles()));
        }

        if ($CountryCode == 'CA') {
            /*
             * Shipping Service || UPS Standard : Available only within Canada
             */
            // Invoke the commerce_norshipping_build_rates function.
            $rate_calculated = commerce_norshipping_build_rates_main($shipment);

            // Check if there's an error thrown.
            $errorThrown = \Drupal::state()->get('shipping_error_thrown', FALSE);

            if (!$errorThrown) {
                // If there's no error thrown, proceed to add shipping rates.
                if (!is_null($rate_calculated) && isset($rate_calculated['cost'])) {
                    $ship_price = $rate_calculated['cost'];
                } else {
                    $ship_price = 0;
                }
                $ship_price_in_dollars = $ship_price / 100;
                $amount = new Price($ship_price_in_dollars, $currency_code);

                $rates[] = new ShippingRate([
                    'shipping_method_id' => $this->parentEntity->id(),
                    'service' => $service1,
                    'amount' => $amount,
                    'description' => '<img src="/themes/custom/jango/img/ups.png">',
                ]);
            }

        } elseif ($CountryCode == 'US') {
            /*
             * Shipping Service || UPS 3 Day Select: Available only for US if dry ice not exists in order
             */

            if ($order instanceof \Drupal\commerce_order\Entity\Order) {
                // Get the line items from the order.
                $line_items = $order->getItems();
                $dry_ice = 0;
                //Check if dry_ice is a package in the order
                foreach ($line_items as $line_item) {
                    $purchased_entity = $line_item->getPurchasedEntity();
                    if ($purchased_entity->hasField('field_commerce_shipping_box')) {
                        // Check if the line item belongs to a specific shipping box.
                        $shipping_box_field = $purchased_entity->get('field_commerce_shipping_box')->getValue();

                        foreach ($shipping_box_field as $item) {
                            $value = $item['value'];

                            if (!empty($value)) {
                                if (
                                    $value === 'dry_ice_box' ||
                                    $value === 'small_dry_ice_box' ||
                                    $value === 'medium_dry_ice_box' ||
                                    $value === 'large_dry_ice_box'
                                ) {
                                    $dry_ice = 1;
                                }
                            }
                        }
                    } elseif ($purchased_entity->hasField('commerce_shipping_box')) {
                        // Check if the line item belongs to a specific shipping box.
                        $shipping_box_field = $purchased_entity->get('commerce_shipping_box')->getValue();

                        foreach ($shipping_box_field as $item) {
                            $value = $item['value'];

                            if (!empty($value)) {
                                if (
                                    $value === 'dry_ice_box' ||
                                    $value === 'small_dry_ice_box' ||
                                    $value === 'medium_dry_ice_box' ||
                                    $value === 'large_dry_ice_box'
                                ) {
                                    $dry_ice = 1;
                                }
                            }
                        }
                    }

                }
            }

            if ($dry_ice == 0) {
                // Invoke the commerce_norshipping_build_rates function.
                $rate_calculated_3_day_select = commerce_norshipping_build_rates_main($shipment);

                // Check if there's an error thrown.
                $errorThrown = \Drupal::state()->get('shipping_error_thrown', FALSE);

                if (!$errorThrown) {

                    if (!is_null($rate_calculated_3_day_select) && isset($rate_calculated_3_day_select['04']['cost'])) {
                        $ship_price_3_day_select = $rate_calculated_3_day_select['04']['cost'];
                    } else {
                        $ship_price_3_day_select = 0;
                    }
                    $ship_price_in_dollars_3_day_select = $ship_price_3_day_select / 100;
                    $amount_3_day_select = new Price($ship_price_in_dollars_3_day_select, $currency_code);

                    $rates[] = new ShippingRate([
                        'shipping_method_id' => $this->parentEntity->id(),
                        'service' => $service3,
                        'amount' => $amount_3_day_select,
                        'description' => '<img src="/themes/custom/jango/img/ups.png">',
                    ]);
                }
            }

            /*
             * Shipping Service || UPS Express Saver: Available for any country other than Canada
             */
            // Invoke the commerce_norshipping_build_rates function.
            $rate_calculated_express_saver = commerce_norshipping_build_rates_main($shipment);

            // Check if there's an error thrown.
            $errorThrownAgain = \Drupal::state()->get('shipping_error_thrown', FALSE);

            if (!$errorThrownAgain) {
                if ($dry_ice != 0) {
                    if (!is_null($rate_calculated_express_saver) && isset($rate_calculated_express_saver['cost'])) {
                        $ship_price_express_saver = $rate_calculated_express_saver['cost'];
                    } else {
                        $ship_price_express_saver = 0;
                    }

                } else {
                    if (!is_null($rate_calculated_express_saver) && isset($rate_calculated_express_saver['01']['cost'])) {
                        $ship_price_express_saver = $rate_calculated_express_saver['01']['cost'];
                    } else {
                        $ship_price_express_saver = 0;
                    }
                }

                $ship_price_in_dollars_express_saver = $ship_price_express_saver / 100;
                $amount_express_saver = new Price($ship_price_in_dollars_express_saver, $currency_code);

                $rates[] = new ShippingRate([
                    'shipping_method_id' => $this->parentEntity->id(),
                    'service' => $service2,
                    'amount' => $amount_express_saver,
                    'description' => '<img src="/themes/custom/jango/img/ups.png">',
                ]);
            }
        } else {
            /*
             * Shipping Service || UPS Express Saver : Available for any country other than Canada
             */
            // Invoke the commerce_norshipping_build_rates function.
            $rate_calculated = commerce_norshipping_build_rates_main($shipment);
            // Check if there's an error thrown.
            $errorThrown = \Drupal::state()->get('shipping_error_thrown', FALSE);

            if (!$errorThrown) {
                if (!is_null($rate_calculated) && isset($rate_calculated['cost'])) {
                    $ship_price = $rate_calculated['cost'];
                } else {
                    $ship_price = 0;
                }
                $ship_price_in_dollars = $ship_price / 100;
                $amount = new Price($ship_price_in_dollars, $currency_code);

                $rates[] = new ShippingRate([
                    'shipping_method_id' => $this->parentEntity->id(),
                    'service' => $service2,
                    'amount' => $amount,
                    'description' => '<img src="/themes/custom/jango/img/ups.png">',
                ]);
            }
        }

        // Check if there's an error thrown.
        $errorThrown = \Drupal::state()->get('shipping_error_thrown', FALSE);
        if ($errorThrown) {
            $description = 'No shipping rate';
            $service4 = new ShippingService('Error_Service', $description);
            $rates[] = new ShippingRate([
                'shipping_method_id' => $this->parentEntity->id(),
                'service' => $service4,
                'description' => $description,
            ]);
        }

        return $rates;
    }

}
