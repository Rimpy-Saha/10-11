<?php

/**
 * Convenience function to get Norgen codes for their package types.
 *
 * This function retrieves package types associated with Norgen codes.
 *
 * @return array An array containing Norgen package types.
 */

function commerce_norshipping_package_types()
{
    return array(
        'one_kit',
        'two_kit',
        'four_kit',
        'six_kit',
        'twelve_kit',
        'thirty_kit',
        'saliva_box',
        'small_saliva_box',
        'medium_saliva_box',
        'large_saliva_box',
        'ladder_box',
        'covid_test',
        'dry_ice_box',
        'small_dry_ice_box',
        'medium_dry_ice_box',
        'large_dry_ice_box',
    );
}