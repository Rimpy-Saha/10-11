<?php

/**
 * Convenience function to get Norgen zone rates.
 *
 * This function retrieves Norgen zone rates for commerce shipping.
 *
 * @param array $zone_key An array containing zone keys to retrieve rates.
 * @return array An array containing Norgen zone rates.
 */

function commerce_norshipping_zone_rates($zone_key = array())
{
    $zone_rates = array();
    foreach ($zone_key as $key => $value) {
        if ($value == 'US') {
            $zone_rates[$value] = array(
                '01' => array(
                    'one_kit' => '6819.00',
                    'two_kit' => '10861.00',
                    'four_kit' => '15373.00',
                    'six_kit' => '19256.00',
                    'twelve_kit' => '25436.00',
                    'thirty_kit' => '55503.00',
                    'saliva_box' => '12587.00',
                    'small_saliva_box' => '17963.00',
                    'medium_saliva_box' => '36247.00',
                    'large_saliva_box' => '72173.00',
                    'ladder_box' => '5768.00',
                    'service' => '01',
                    'medium_dry_ice_box' => '30314',
                    'large_dry_ice_box' => '37190',
                ),
                '04' => array(
                    'one_kit' => '4825.00',
                    'two_kit' => '6641.00',
                    'four_kit' => '8897.00',
                    'six_kit' => '10860.00',
                    'twelve_kit' => '25436.00',
                    'thirty_kit' => '14233.00',
                    'saliva_box' => '5876.00',
                    'small_saliva_box' => '10202.00',
                    'medium_saliva_box' => '12238.00',
                    'large_saliva_box' => '20254.00',
                    'ladder_box' => '4268.00',
                    'service' => '04',
                    'medium_dry_ice_box' => '',
                    'large_dry_ice_box' => '',
                ),
            );
        } elseif ($value == 'CA') {
            $zone_rates[$value] = array(
                'ON' => array(
                    'one_kit' => '2190.00',
                    'two_kit' => '2473.00',
                    'four_kit' => '3066.00',
                    'six_kit' => '3782.00',
                    'twelve_kit' => '4674.00',
                    'thirty_kit' => '16859.00',
                    'saliva_box' => '2618.00',
                    'small_saliva_box' => '4096.00',
                    'medium_saliva_box' => '6460.00',
                    'large_saliva_box' => '22635.00',
                    'ladder_box' => '3497.00',
                    'covid_test' => '0.00',
                    'service' => '03',
                    'medium_dry_ice_box' => '14590',
                    'large_dry_ice_box' => '14590',
                ),
                'PQ' => array(
                    'one_kit' => '2190.00',
                    'two_kit' => '2473.00',
                    'four_kit' => '3066.00',
                    'six_kit' => '3782.00',
                    'twelve_kit' => '4674.00',
                    'thirty_kit' => '16859.00',
                    'saliva_box' => '2618.00',
                    'small_saliva_box' => '4096.00',
                    'medium_saliva_box' => '6460.00',
                    'large_saliva_box' => '22635.00',
                    'ladder_box' => '3497.00',
                    'covid_test' => '0.00',
                    'service' => '03',
                    'medium_dry_ice_box' => '14590',
                    'large_dry_ice_box' => '14590',
                ),
                'OT' => array(
                    'one_kit' => '2190.00',
                    'two_kit' => '2473.00',
                    'four_kit' => '3066.00',
                    'six_kit' => '3782.00',
                    'twelve_kit' => '4674.00',
                    'thirty_kit' => '16859.00',
                    'saliva_box' => '2618.00',
                    'small_saliva_box' => '4096.00',
                    'medium_saliva_box' => '6460.00',
                    'large_saliva_box' => '22635.00',
                    'ladder_box' => '3497.00',
                    'covid_test' => '0.00',
                    'service' => '03',
                    'medium_dry_ice_box' => '14590',
                    'large_dry_ice_box' => '14590',
                ),
            );
        } elseif ($value == 'NAA') {
            $zone_rates[$value] = array(
                'one_kit' => '6819.00',
                'two_kit' => '10861.00',
                'four_kit' => '15373.00',
                'six_kit' => '19256.00',
                'twelve_kit' => '25436.00',
                'thirty_kit' => '55503.00',
                'saliva_box' => '12587.00',
                'small_saliva_box' => '17963.00',
                'medium_saliva_box' => '36247.00',
                'large_saliva_box' => '72173.00',
                'ladder_box' => '5768.00',
                'service' => '01',
                'medium_dry_ice_box' => '30314',
                'large_dry_ice_box' => '37190',
            );
        } elseif ($value == 'NAB') {
            $zone_rates[$value] = array(
                'one_kit' => '6819.00',
                'two_kit' => '10861.00',
                'four_kit' => '15373.00',
                'six_kit' => '19256.00',
                'twelve_kit' => '25436.00',
                'thirty_kit' => '55503.00',
                'saliva_box' => '12587.00',
                'small_saliva_box' => '17963.00',
                'medium_saliva_box' => '36247.00',
                'large_saliva_box' => '72173.00',
                'ladder_box' => '5768.00',
                'service' => '01',
                'medium_dry_ice_box' => '30314',
                'large_dry_ice_box' => '37190',
            );
        } elseif ($value == 'NAC') {
            $zone_rates[$value] = array(
                'one_kit' => '6819.00',
                'two_kit' => '10861.00',
                'four_kit' => '15373.00',
                'six_kit' => '19256.00',
                'twelve_kit' => '25436.00',
                'thirty_kit' => '55503.00',
                'saliva_box' => '12587.00',
                'small_saliva_box' => '17963.00',
                'medium_saliva_box' => '36247.00',
                'large_saliva_box' => '72173.00',
                'ladder_box' => '5768.00',
                'service' => '01',
                'medium_dry_ice_box' => '30314',
                'large_dry_ice_box' => '37190',
            );
        } elseif ($value == 'EUW') {
            $zone_rates[$value] = array(
                'one_kit' => '5894.00',
                'two_kit' => '8499.00',
                'four_kit' => '11906.00',
                'six_kit' => '14634.00',
                'twelve_kit' => '21402.00',
                'thirty_kit' => '85645.00',
                'saliva_box' => '9834.00',
                'small_saliva_box' => '17298.00',
                'medium_saliva_box' => '35155.00',
                'large_saliva_box' => '107518.00',
                'ladder_box' => '8751.00',
                'service' => '01',
                'large_dry_ice_box' => '31937',
            );
        } elseif ($value == 'EUE') {
            $zone_rates[$value] = array(
                'one_kit' => '9850.00',
                'two_kit' => '14428.00',
                'four_kit' => '21396.00',
                'six_kit' => '28580.00',
                'twelve_kit' => '44076.00',
                'thirty_kit' => '178716.00',
                'saliva_box' => '16694.00',
                'small_saliva_box' => '34714.00',
                'medium_saliva_box' => '75062.00',
                'large_saliva_box' => '219946.00',
                'ladder_box' => '14962.00',
                'service' => '01',
                'large_dry_ice_box' => '57219',
            );
        } elseif ($value == 'ASA') {
            $zone_rates[$value] = array(
                'one_kit' => '13281.00',
                'two_kit' => '18609.00',
                'four_kit' => '26979.00',
                'six_kit' => '34695.00',
                'twelve_kit' => '54510.00',
                'thirty_kit' => '128038.00',
                'saliva_box' => '21368.00',
                'small_saliva_box' => '32092.00',
                'medium_saliva_box' => '93343.00',
                'large_saliva_box' => '157527.00',
                'ladder_box' => '13281.00',
                'service' => '01',
            );
        } elseif ($value == 'ASB') {
            $zone_rates[$value] = array(
                'one_kit' => '13281.00',
                'two_kit' => '18609.00',
                'four_kit' => '26979.00',
                'six_kit' => '34695.00',
                'twelve_kit' => '54510.00',
                'thirty_kit' => '128038.00',
                'saliva_box' => '21368.00',
                'small_saliva_box' => '32092.00',
                'medium_saliva_box' => '93343.00',
                'large_saliva_box' => '157527.00',
                'ladder_box' => '13281.00',
                'service' => '01',
            );
        } elseif ($value == 'ASC') {
            $zone_rates[$value] = array(
                'one_kit' => '13281.00',
                'two_kit' => '18609.00',
                'four_kit' => '26979.00',
                'six_kit' => '34695.00',
                'twelve_kit' => '54510.00',
                'thirty_kit' => '128038.00',
                'saliva_box' => '21368.00',
                'small_saliva_box' => '32092.00',
                'medium_saliva_box' => '93343.00',
                'large_saliva_box' => '157527.00',
                'ladder_box' => '13281.00',
                'service' => '01',
            );
        } elseif ($value == 'ASD') {
            $zone_rates[$value] = array(
                'one_kit' => '13281.00',
                'two_kit' => '18609.00',
                'four_kit' => '26979.00',
                'six_kit' => '34695.00',
                'twelve_kit' => '54510.00',
                'thirty_kit' => '128038.00',
                'saliva_box' => '21368.00',
                'small_saliva_box' => '32092.00',
                'medium_saliva_box' => '93343.00',
                'large_saliva_box' => '157527.00',
                'ladder_box' => '13281.00',
                'service' => '01',
            );
        } elseif ($value == 'ASE') {
            $zone_rates[$value] = array(
                'one_kit' => '13281.00',
                'two_kit' => '18609.00',
                'four_kit' => '26979.00',
                'six_kit' => '34695.00',
                'twelve_kit' => '54510.00',
                'thirty_kit' => '128038.00',
                'saliva_box' => '21368.00',
                'small_saliva_box' => '32092.00',
                'medium_saliva_box' => '93343.00',
                'large_saliva_box' => '157527.00',
                'ladder_box' => '13281.00',
                'service' => '01',
            );
        } elseif ($value == 'ASF') {
            $zone_rates[$value] = array(
                'one_kit' => '13281.00',
                'two_kit' => '18609.00',
                'four_kit' => '26979.00',
                'six_kit' => '34695.00',
                'twelve_kit' => '54510.00',
                'thirty_kit' => '128038.00',
                'saliva_box' => '21368.00',
                'small_saliva_box' => '32092.00',
                'medium_saliva_box' => '93343.00',
                'large_saliva_box' => '157527.00',
                'ladder_box' => '13281.00',
                'service' => '01',
            );
        } elseif ($value == 'ASG') {
            $zone_rates[$value] = array(
                'one_kit' => '13281.00',
                'two_kit' => '18609.00',
                'four_kit' => '26979.00',
                'six_kit' => '34695.00',
                'twelve_kit' => '54510.00',
                'thirty_kit' => '128038.00',
                'saliva_box' => '21368.00',
                'small_saliva_box' => '32092.00',
                'medium_saliva_box' => '93343.00',
                'large_saliva_box' => '157527.00',
                'ladder_box' => '13281.00',
                'service' => '01',
            );
        } elseif ($value == 'SAA') {
            $zone_rates[$value] = array(
                'one_kit' => '13281.00',
                'two_kit' => '18609.00',
                'four_kit' => '26979.00',
                'six_kit' => '34695.00',
                'twelve_kit' => '54510.00',
                'thirty_kit' => '128038.00',
                'saliva_box' => '21368.00',
                'small_saliva_box' => '32092.00',
                'medium_saliva_box' => '93343.00',
                'large_saliva_box' => '157527.00',
                'ladder_box' => '13281.00',
                'service' => '01',
            );
        } elseif ($value == 'SAB') {
            $zone_rates[$value] = array(
                'one_kit' => '13281.00',
                'two_kit' => '18609.00',
                'four_kit' => '26979.00',
                'six_kit' => '34695.00',
                'twelve_kit' => '54510.00',
                'thirty_kit' => '128038.00',
                'saliva_box' => '21368.00',
                'small_saliva_box' => '32092.00',
                'medium_saliva_box' => '93343.00',
                'large_saliva_box' => '157527.00',
                'ladder_box' => '13281.00',
                'service' => '01',
            );
        } elseif ($value == 'AU') {
            $zone_rates[$value] = array(
                'one_kit' => '13281.00',
                'two_kit' => '18609.00',
                'four_kit' => '26979.00',
                'six_kit' => '34695.00',
                'twelve_kit' => '54510.00',
                'thirty_kit' => '128038.00',
                'saliva_box' => '21368.00',
                'small_saliva_box' => '32092.00',
                'medium_saliva_box' => '93343.00',
                'large_saliva_box' => '157527.00',
                'ladder_box' => '13281.00',
                'service' => '01',
            );
        }
    }
    return $zone_rates;
}
