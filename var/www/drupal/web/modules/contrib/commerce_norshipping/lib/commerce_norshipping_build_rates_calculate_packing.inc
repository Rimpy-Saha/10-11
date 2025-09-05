<?php

/**
 * Package Processor for Norgen Orders
 *
 * This included file manages the packaging process for Norgen orders. It consumes
 * information about order packages, the number of packages, and performs tasks such as
 * sorting packages based on zone and package type. It checks for specific package
 * requirements like dry ice, selects appropriate shipping boxes based on quantity and SKU,
 * and fetches line items from the order for processing.
 *
 * @return array $order_packages An array containing information about the order packages.
 * @return array $num_packages The number of packages in the order.
 */

$i = 0;
$imax = 14;
unset($order_packages);
unset($num_packages);
$order_packages = array();
$num_packages = array();

$no_sale = ['28299', '53810', '42720', '43902', '44302', '44506', '66000', '62200', '62100', 'SGM4000', 'SGM3000', 'SGM2000', 'SGM1000', 'TMM2010', 'TMM2000', 'TMM1010', 'TMM1000', 'EPM2000', 'EPM1000'];
$no_sale_ca = ['53800', '69800', 'DxTM67200', 'DxTM67120', 'DxTM67100', 'Dx24380', 'Dx24350', 'Dx69300', 'Dx69100', 'Dx69200', 'Dx63950', 'DxTM62100', 'DxTM64400', 'DxTM30400', 'DxTM32100', 'DxTM36900', 'DxTM32600', 'DxTM32400', 'DxTM37200', 'DxTM45200', 'DxTM41000', 'DxTM41400', 'DxTM62200', 'DxTM39100', 'DxTM43800', 'DxTM44200', 'DxTM39700', 'DxTM27900', 'DxTM38500', 'DxTM36700', 'DxTM37100', 'DxTM42100', 'DxTM34000', 'DxTM67300', 'DxTM39600'];
$no_sale_us = ['49010', '53800', '69800', 'DxTM45200', 'DxTM64400', 'Dx24380', 'Dx24350', 'Dx69300', 'Dx69100', 'Dx24300', 'Dx54300', 'Dx47400', 'Dx29600', 'Dx25300', 'Dx17200', 'Dx48800', 'Dx69200', 'Dx55800', 'Dx55600', 'Dx55100', 'Dx55500', 'Dx42800', 'Dx50600', 'Dx46300', 'Dx45660', 'Dx49500', 'Dx27600', 'Dx63950', 'Dx45400', 'DxTM67100', '49000', 'DxTM67120', 'DxTM67200', 'DxTM62100', 'DxTM30400', 'DxTM32100', 'DxTM36900', 'DxTM32600', 'DxTM32400', 'DxTM37200', 'DxTM41000', 'DxTM41400', 'DxTM62200', 'DxTM39100', 'DxTM43800', 'DxTM44200', 'DxTM39700', 'DxTM27900', 'DxTM38500', 'DxTM36700', 'DxTM37100', 'DxTM42100', 'DxTM34000', 'DxTM67300', 'DxTM39600'];

$zone = 'US';
$contains_dry_ice = False;

$available_zones = ['US', 'CA', 'NAA', 'NAB', 'NAC', 'EUW', 'EUE'];
foreach ($zones as $key => $value) {
    if (in_array($country, $zones[$key]['countries'])) {
        $zone = $key;
    }
}

// Get the line items from the order.
$line_items = $order->getItems();

foreach ($line_items as $line_item) {

    $line_item_dry_ice = 0;
    $line_item_shipping_box = '';
    $line_item_shipping_box_amount = 1;
    $to_not_sell = 0;

    $purchased_entity = $line_item->getPurchasedEntity();

    if ($purchased_entity->hasField('field_commerce_shipping_box')) {
        // Check if the line item belongs to a specific shipping box.
        $shipping_box_field = $purchased_entity->get('field_commerce_shipping_box')->getValue();

        foreach ($shipping_box_field as $item) {
            $value = $item['value'];

            if (!empty($value)) {
                if ($value === 'dry_ice_box') {
                    $line_item_dry_ice = 1;
                }

                $line_item_shipping_box = $value;
                break; // Break loop after handling the first value
            }
        }
    } elseif ($purchased_entity->hasField('commerce_shipping_box')) {
        // Check if the line item belongs to a specific shipping box.
        $shipping_box_field = $purchased_entity->get('commerce_shipping_box')->getValue();

        foreach ($shipping_box_field as $item) {
            $value = $item['value'];

            if (!empty($value)) {
                if ($value === 'dry_ice_box') {
                    $line_item_dry_ice = 1;
                }

                $line_item_shipping_box = $value;
                break; // Break loop after handling the first value
            }
        }
    }

    if ($purchased_entity->hasField('field_commerce_dry_ice')) {
        $to_not_sell_field = $purchased_entity->get('field_commerce_dry_ice')->first();
        if (!empty($to_not_sell_field)) {
            $to_not_sell = $to_not_sell_field->getValue()['value'];
        }
    } elseif ($purchased_entity->hasField('commerce_dry_ice')) {
        $to_not_sell_field = $purchased_entity->get('commerce_dry_ice')->first();
        if (!empty($to_not_sell_field)) {
            $to_not_sell = $to_not_sell_field->getValue()['value'];
        }
    }
    $to_not_sell =0;
    if ($purchased_entity->hasField('field_commerce_shipping_box_am')) {
        $line_item_shipping_box_amount_field = $purchased_entity->get('field_commerce_shipping_box_am')->first();
        if (!empty($line_item_shipping_box_amount_field)) {
            $line_item_shipping_box_amount = $line_item_shipping_box_amount_field->getValue()['value'];
        }
    } elseif ($purchased_entity->hasField('commerce_shipping_box_amount')) {
        $line_item_shipping_box_amount_field = $purchased_entity->get('commerce_shipping_box_amount')->first();
        if (!empty($line_item_shipping_box_amount_field)) {
            $line_item_shipping_box_amount = $line_item_shipping_box_amount_field->getValue()['value'];
        }
    }

    $sku = $line_item->getPurchasedEntity()->getSku(); // Get the SKU of the line item entity.

    if (
        ($country == 'CA' && !in_array($sku, $no_sale_ca) && !in_array($sku, $no_sale)) ||
        ($country == 'US' && !in_array($sku, $no_sale_us) && !in_array($sku, $no_sale) && (strpos($sku, 'Dx')  === false)) ||
        ($country != 'CA' && $country != 'US' && !in_array($sku, $no_sale))
    ) {
        if ($to_not_sell == 0) {
            if (($line_item_dry_ice > 0 && in_array($zone, $available_zones)) || ($line_item_dry_ice == 0)) {
                //Collect a total number of similar base boxes together type = 'base'
                if (!($line_item_shipping_box == '')) {
                    //Key defines package - one_kit, etc
                    foreach ($norgen_package as $key => $value) {
                        $found = 0;
                        if (($norgen_package[$key]['type'] == 'base') && ($key == $line_item_shipping_box)) {
                            $found = 1;
                            //KIT 
                            $subkey = '';
                            $subkey = $norgen_package[$key]['category'];
                            //if amount in a box is 1 - set in commerce product
                            if ($line_item_shipping_box_amount == 1) {
                                if (empty($num_packages[$subkey])) {
                                    $num_packages[$subkey] = $line_item->getQuantity();
                                } else {
                                    $num_packages[$subkey] += $line_item->getQuantity();
                                }

                            } else {
                                if (empty($num_packages[$subkey])) {
                                    $num_packages[$subkey] = ceil($line_item->getQuantity() / $line_item_shipping_box_amount);
                                } else {
                                    $num_packages[$subkey] += ceil($line_item->getQuantity() / $line_item_shipping_box_amount);
                                }
                            }
                        }
                        //If the package is not a base package, assume it is packed alone
                        if ((!$found) && ($key == $line_item_shipping_box)) {
                            for ($j = 0; $j < $line_item->getQuantity(); $j++) {
                                $order_packages[$i]['package'] = $key;
                                $i++;
                                $number_of_packages++;
                            }
                        }
                    }
                }
            } else {
                \Drupal::messenger()->addError('This item requires special handling, please contact us at orders@norgenbiotek.com.');
                \Drupal::state()->set('shipping_error_thrown', TRUE);
                //\Drupal::logger('commerce_norshipping_build_rates_calculate_packing.inc')->error('There was a problem calculating your shipping rates.');
                $rates['status'] = FALSE;
                $rates['message'] = 'Can not ship Dry Ice.';
            }
        } else {
            \Drupal::messenger()->addError('This item requires special handling, please contact us at orders@norgenbiotek.com');
            \Drupal::state()->set('shipping_error_thrown', TRUE);
            //\Drupal::logger('commerce_norshipping_build_rates_calculate_packing.inc')->error('There was a problem calculating your shipping rates.');
            $rates['status'] = FALSE;
            $rates['message'] = 'Can not ship Dry Ice.';

        } //there is dry ice in the cart from a zone that is unavailable or a a location is disabled
    } else {
        \Drupal::messenger()->addError('This item requires special handling, please contact us at orders@norgenbiotek.com');
        \Drupal::state()->set('shipping_error_thrown', TRUE);
        //\Drupal::logger('commerce_norshipping_build_rates_calculate_rates.inc')->error('There was a problem calculating your shipping rates.');
        $rates['status'] = FALSE;
        $rates['message'] = 'Disabled Location Selected.';
    }
}

//FIRST PACK ALL IN MAXIMUM SIZE,
// If there is a new box which is not dependent on zones, make sure it has the type largest
foreach ($num_packages as $pkey => $value) {
    foreach ($norgen_package as $npkey => $value) {
        if (($norgen_package[$npkey]['category'] == $pkey) && ($norgen_package[$npkey]['type'] == 'largest') && ($num_packages[$pkey] > $norgen_package[$npkey]['max'])) {
            $num_packages_mod = $num_packages[$pkey] % $norgen_package[$npkey]['max'];
            $inumpackages = $num_packages[$pkey] / $norgen_package[$npkey]['max'];
            for ($j = 0; $j < $inumpackages; $j++) {
                $order_packages[$i]['package'] = $npkey;
                $i++;
            }
            $num_packages[$pkey] = $num_packages_mod;
        }
    }
}

if (!empty($num_packages)) {
    if (!empty($num_packages['ladder'])) {
        if (!empty($num_packages['kit'])) {
            if (($num_packages['ladder'] / 5) < $num_packages['kit']) {
            } else {
                $num_remaining_mod = ($num_packages['ladder'] / 5) % $num_packages['kit'];
                $num_packages['ladder'] = ($num_packages['ladder'] / 5) / $num_packages['kit'];
                if ($num_remaining_mod > 0) {
                    $num_packages['ladder'] = $num_packages['ladder'] + 1;
                }
                $order_packages[$i]['package'] = 'ladder_box';
                $i++;
            }
        } else {
            $num_packages_mod = $num_packages['ladder'] % 5;
            $num_packages['ladder'] = ($num_packages['ladder'] - ($num_packages['ladder'] % 5)) / 5;
            if ($num_packages_mod > 0) {
                $num_packages['ladder'] = $num_packages['ladder'] + 1;
            }
            $order_packages[$i]['package'] = 'ladder_box';
            $i++;
        }
    }
    if (!empty($num_packages['kit'])) {
        //Find out what to package in:
        if ($num_packages['kit'] < 2) {
            $order_packages[$i]['package'] = 'one_kit';
            $i++;
        } elseif ($num_packages['kit'] == 2) {
            $order_packages[$i]['package'] = 'two_kit';
            $i++;
        } elseif (($num_packages['kit'] > 2) && ($num_packages['kit'] < 5)) {
            $order_packages[$i]['package'] = 'four_kit';
            $i++;
        } elseif (($num_packages['kit'] > 4) && ($num_packages['kit'] < 7)) {
            $order_packages[$i]['package'] = 'six_kit';
            $i++;
        } elseif (($num_packages['kit'] > 6) && ($num_packages['kit'] < 13)) {
            $order_packages[$i]['package'] = 'twelve_kit';
            $i++;
        } elseif (($num_packages['kit'] > 12) && ($num_packages['kit'] < 15)) {
            $order_packages[$i]['package'] = 'twelve_kit';
            $i++;
            $order_packages[$i]['package'] = 'two_kit';
            $i++;
        } elseif (($num_packages['kit'] > 13) && ($num_packages['kit'] < 17)) {
            $order_packages[$i]['package'] = 'twelve_kit';
            $i++;
            $order_packages[$i]['package'] = 'four_kit';
            $i++;
        } elseif (($num_packages['kit'] > 16) && ($num_packages['kit'] < 19)) {
            $order_packages[$i]['package'] = 'twelve_kit';
            $i++;
            $order_packages[$i]['package'] = 'six_kit';
            $i++;
        } elseif (($num_packages['kit'] > 18) && ($num_packages['kit'] < $norgen_package['thirty_kit']['max'] + 1)) {
            $order_packages[$i]['package'] = 'thirty_kit';
            $i++;
        } else {
        }
    }
    if (!empty($num_packages['saliva'])) {
        if ($num_packages['saliva'] < 2) {
            $order_packages[$i]['package'] = 'saliva_box';
            $i++;
        } elseif (($num_packages['saliva'] > 1) && ($num_packages['saliva'] < 4)) {
            $order_packages[$i]['package'] = 'small_saliva_box';
            $i++;
        } elseif (($num_packages['saliva'] > 3) && ($num_packages['saliva'] < 9)) {
            $order_packages[$i]['package'] = 'medium_saliva_box';
            $i++;
        } elseif (($num_packages['saliva'] > 8) && ($num_packages['saliva'] < 13)) {
            $order_packages[$i]['package'] = 'large_saliva_box';
            $i++;
        } else {
        }
    }

    //Dry ice pricing and packaging is dependent on zones
    //zone was calculated near the beginning of this file
    if (!empty($num_packages['dry_ice'])) {
        $contains_dry_ice = True;
        if ($zone == "CA") {
            while ($num_packages['dry_ice'] > 0) {
                if (($num_packages['dry_ice'] > 4)) //max 8 in largest
                {
                    $order_packages[$i++]['package'] = 'large_dry_ice_box';
                    if ($num_packages['dry_ice'] <= 8) {
                        $num_packages['dry_ice'] = 0;
                    } else {
                        $num_packages['dry_ice'] -= 8;
                    }
                }

                if (($num_packages['dry_ice'] > 0 && $num_packages['dry_ice'] < 5)) //max 4 in medium
                {
                    $order_packages[$i++]['package'] = 'medium_dry_ice_box';
                    $num_packages['dry_ice'] = 0;
                }
            }
        } else if ($zone == "US" || $zone == "NAA" || $zone == "NAB" || $zone == "NAC") {
            while ($num_packages['dry_ice'] > 0) {
                if (($num_packages['dry_ice'] > 4)) //max 6 in largest - the only option
                {
                    $order_packages[$i++]['package'] = 'large_dry_ice_box';
                    if ($num_packages['dry_ice'] <= 6) {
                        $num_packages['dry_ice'] = 0;
                    } else {
                        $num_packages['dry_ice'] -= 6;
                    }
                }

                if (($num_packages['dry_ice'] > 0 && $num_packages['dry_ice'] < 5)) {
                    $order_packages[$i++]['package'] = 'medium_dry_ice_box';
                    $num_packages['dry_ice'] = 0;
                }
            }
        } else if ($zone == "EUW" || $zone == "EUE") {
            while ($num_packages['dry_ice'] > 0) {
                if (($num_packages['dry_ice'] > 0)) //max 4 in the largest - the only option
                {
                    $order_packages[$i++]['package'] = 'large_dry_ice_box';
                    if ($num_packages['dry_ice'] <= 4) {
                        $num_packages['dry_ice'] = 0;
                    } else {
                        $num_packages['dry_ice'] -= 4;
                    }
                }
            }
        }
    }
    if (!empty($num_packages['test'])) {
        $order_packages[$i]['package'] = 'covid_test';
        $i++;
    }
    $number_of_packages = $i++;
}

