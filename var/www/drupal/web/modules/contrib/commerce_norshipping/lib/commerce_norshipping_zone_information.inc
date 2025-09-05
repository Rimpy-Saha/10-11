<?php

/**
 * Convenience function to get Norgen zones information.
 *
 * This function retrieves information about Norgen zones based on the provided keys.
 *
 * @param array $zone_key An array containing keys to retrieve zone information for (optional).
 * @return array An array containing Norgen zone information.
 */

function commerce_norshipping_zone_information($zone_key = array())
{
    $zones = array();
    foreach ($zone_key as $key => $value) {
        if ($value == 'US') {
            $zones[$value] = array(
                'name' => t('USA & Puerto Rico'),
                'countries' => array('US', 'PR'),
            );
        } elseif ($value == 'CA') {
            $zones[$value] = array(
                'name' => t('Canada'),
                'countries' => array('CA'),
            );
        } elseif ($value == 'NAA') {
            $zones[$value] = array(
                'name' => t('North America Group 1'),
                'countries' => array('BM', 'GD', 'TT', 'AI', 'GP', 'TC', 'AG', 'HT', 'VI', 'AW', 'MQ', 'BQ', 'BS', 'MS', 'BB', 'BL', 'SS', 'SX', 'VG', 'NT', 'MF', 'KY', 'KN', 'JM', 'CB', 'LC', 'DM', 'TB', 'DO', 'VC'),
            );
        } elseif ($value == 'NAB') {
            $zones[$value] = array(
                'name' => t('North America Group 2'),
                'countries' => array('MX', 'GL'),
            );
        } elseif ($value == 'NAC') {
            $zones[$value] = array(
                'countries' => array('BZ', 'CR', 'SV', 'GT', 'HN', 'NI', 'PA'),
                'name' => t('North America Group 3'),
            );
        } elseif ($value == 'EUW') {
            $zones[$value] = array(
                'name' => t('Europe West'),
                'countries' => array('AD', 'SM', 'JE', 'AM', 'ES', 'MO', 'AT', 'SE', 'GG', 'BE', 'CH', 'GI', 'DK', 'GB', 'FI', 'FR', 'DE', 'IT', 'GR', 'MC', 'IS', 'NL', 'IE', 'NO', 'PT', 'FO', 'MT', 'VA'),
            );
        } elseif ($value == 'EUE') {
            $zones[$value] = array(
                'name' => t('Europe East'),
                'countries' => array('AL', 'LT', 'NG', 'NE', 'MU', 'CF', 'GA', 'UZ', 'BY', 'LU', 'TZ', 'ML', 'DJ', 'EH', 'GM', 'VU', 'BA', 'MK', 'CD', 'MW', 'CV', 'TO', 'GW', 'BG', 'MD', 'KE', 'ZM', 'MN',
                    'SD', 'LS', 'HR', 'ET', 'UG', 'SN', 'LI', 'SZ', 'MH', 'CY', 'CG', 'GH', 'TD', 'MM', 'SL', 'MR', 'CZ', 'ME', 'MZ', 'ZW', 'CK', 'RE', 'YT', 'EE', 'PL', 'AO', 'GN', 'KG', 'RW',
                    'FM', 'GE', 'RO', 'CI', 'BJ', 'LR', 'SC', 'PG', 'HU', 'SK', 'MG', 'TG', 'WS', 'KM', 'TL', 'RS', 'SI', 'CM', 'NA', 'AS', 'FJ', 'TM', 'LV', 'UA', 'BF', 'BW', 'BI', 'PF', 'TV'),
            );
        } elseif ($value == 'ASA') {
            $zones[$value] = array(
                'name' => t('Malaysia, Phillipines, Nepal, Thailand, Cambodia, Guam,  Kiribati, Palau '),
                'countries' => array('MY', 'PH', 'NP', 'TH', 'KH', 'GU', 'KI', 'PW'),
            );
        } elseif ($value == 'ASB') {
            $zones[$value] = array(
                'name' => t('AFGHANISTAN, Azerbaijan, Northern mariana Islands'),
                'countries' => array('AF', 'AZ', 'MP'),
            );
        } elseif ($value == 'ASC') {
            $zones[$value] = array(
                'name' => t('South Africa, Kazakhstan'),
                'countries' => array('ZA', 'KZ', 'AC'),
            );
        } elseif ($value == 'ASD') {
            $zones[$value] = array(
                'name' => t('Pakistan, Turkey, Israel, Maldives '),
                'countries' => array('PK', 'TR', 'IL', 'MV'),
            );
        } elseif ($value == 'ASE') {
            $zones[$value] = array(
                'name' => t('India, Sri Lanka, Indonesia, Vietnam, Bangladesh, Brunei '),
                'countries' => array('IN', 'ID', 'LK', 'VN', 'BD', 'BN'),
            );
        } elseif ($value == 'ASF') {
            $zones[$value] = array(
                'name' => t('Japan, Taiwan, South Korea, Singapore, Hong Kong '),
                'countries' => array('JP', 'TW', 'KR', 'SG', 'HK'),
            );
        } elseif ($value == 'ASG') {
            $zones[$value] = array(
                'name' => t('China, Laos '),
                'countries' => array('CN', 'LA'),
            );
        } elseif ($value == 'SAA') {
            $zones[$value] = array(
                'name' => t('SOUTH AMERICA- Argentina, Brazil, Chile, Guyana '),
                'countries' => array('AR', 'BR', 'CL', 'GY', 'CW'),
            );
        } elseif ($value == 'SAB') {
            $zones[$value] = array(
                'name' => t('SOUTH AMERICA- Bolivia, Colombia, Ecuador, French Guiana, Paraguay,  Suriname, Uruguay,Venezuela, PERU'),
                'countries' => array('BO', 'CO', 'EC', 'GF', 'PY', 'SR', 'UY', 'VE', 'PE'),
            );
        } elseif ($value == 'AU') {
            $zones[$value] = array(
                'name' => t('Australia & New Zealand '),
                'countries' => array('AU', 'NZ'),
            );
        }
    }
    return $zones;
}