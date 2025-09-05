<?php

/**
 * Convenience function to retrieve detailed information for Norgen package types.
 *
 * This function fetches comprehensive information about Norgen package types based on the provided keys.
 *
 * @param array $package_key An array containing keys to retrieve detailed package information.
 * @return array An array containing detailed information about Norgen package types.
 */

function commerce_norshipping_package_information($package_key = array())
{
    $packages = array();
    foreach ($package_key as $key => $value) {
        if ($value == 'one_kit') {
            $packages[$value] = array(
                'name' => t('One Kit Package'),
                'length' => '10',
                'width' => '6',
                'height' => '6',
                'max' => '1',
                'category' => 'kit',
                'type' => 'base',
                'volume' => '360',
            );
        } elseif ($value == 'two_kit') {
            $packages[$value] = array(
                'name' => t('Two Kit Package'),
                'length' => '12',
                'width' => '12',
                'height' => '6',
                'max' => '2',
                'category' => 'kit',
                'type' => 'secondary',
                'volume' => '864',
            );
        } elseif ($value == 'four_kit') {
            $packages[$value] = array(
                'name' => t('Four Kit Package'),
                'length' => '13',
                'width' => '12',
                'height' => '12',
                'max' => '4',
                'category' => 'kit',
                'type' => 'secondary',
                'volume' => '1872',
            );
        } elseif ($value == 'six_kit') {
            $packages[$value] = array(
                'name' => t('Six Kit Package'),
                'length' => '18',
                'width' => '13',
                'height' => '12',
                'max' => '6',
                'category' => 'kit',
                'type' => 'secondary',
                'volume' => '2808',
            );
        } elseif ($value == 'twelve_kit') {
            $packages[$value] = array(
                'name' => t('Twelve Kit Package'),
                'length' => '20',
                'width' => '20',
                'height' => '12',
                'max' => '12',
                'category' => 'kit',
                'type' => 'secondary',
                'volume' => '4800',
            );
        } elseif ($value == 'thirty_kit') {
            $packages[$value] = array(
                'name' => t('Thirty Kit Package'),
                'length' => '30',
                'width' => '20',
                'height' => '20',
                'max' => '30',
                'category' => 'kit',
                'type' => 'largest',
                'volume' => '12000',
            );
        } elseif ($value == 'saliva_box') {
            $packages[$value] = array(
                'name' => t('Saliva Box Package'),
                'length' => '12',
                'width' => '12',
                'height' => '8',
                'max' => '1',
                'category' => 'saliva',
                'type' => 'base',
                'volume' => '847',
            );
        } elseif ($value == 'small_saliva_box') {
            $packages[$value] = array(
                'name' => t('Small Saliva Box'),
                'length' => '12',
                'width' => '12',
                'height' => '24',
                'max' => '2',
                'category' => 'saliva',
                'type' => 'secondary',
                'volume' => '3456',
            );
        } elseif ($value == 'medium_saliva_box') {
            $packages[$value] = array(
                'name' => t('Medium Saliva Box'),
                'length' => '22',
                'width' => '22',
                'height' => '16',
                'max' => '8',
                'category' => 'saliva',
                'type' => 'secondary',
                'volume' => '7744',
            );
        } elseif ($value == 'large_saliva_box') {
            $packages[$value] = array(
                'name' => t('Large Saliva Box'),
                'length' => '22',
                'width' => '22',
                'height' => '22',
                'max' => '12',
                'category' => 'saliva',
                'type' => 'largest',
                'volume' => '10648',
            );
        } elseif ($value == 'ladder_box') {
            $packages[$value] = array(
                'name' => t('Ladder Box'),
                'length' => '9',
                'width' => '4',
                'height' => '3',
                'max' => '10',
                'category' => 'ladder',
                'type' => 'base',
                'volume' => '108',
            );
        } elseif ($value == 'covid_test') {
            $packages[$value] = array(
                'name' => t('Covid Test'),
                'length' => '9',
                'width' => '4',
                'height' => '3',
                'max' => '1',
                'category' => 'test',
                'type' => 'base',
                'volume' => '108',
            );
        } elseif ($value == 'dry_ice_box') {
            $packages[$value] = array(
                'name' => t('Dry Ice'),
                'length' => '9',
                'width' => '4',
                'height' => '3',
                'max' => '2',
                'category' => 'dry_ice',
                'type' => 'base',
                'volume' => '108',
            );
        } elseif ($value == 'small_dry_ice_box') {
            $packages[$value] = array(
                'name' => t('Dry Ice Small'),
                'length' => '11',
                'width' => '9',
                'height' => '15',
                'max' => '2',
                'category' => 'dry_ice',
                'type' => 'base',
                'volume' => '108',
            );
        } elseif ($value == 'medium_dry_ice_box') {
            $packages[$value] = array(
                'name' => t('Dry Ice Medium'),
                'length' => '15',
                'width' => '13',
                'height' => '12',
                'max' => '30',
                'category' => 'dry_ice',
                'type' => 'base',
                'volume' => '108',
            );
        } elseif ($value == 'large_dry_ice_box') {
            $packages[$value] = array(
                'name' => t('Dry Ice Large'),
                'length' => '16',
                'width' => '16',
                'height' => '16',
                'max' => '45',
                'category' => 'dry_ice',
                'type' => 'base',
                'volume' => '108',
            );
        }

    }
    return $packages;
}