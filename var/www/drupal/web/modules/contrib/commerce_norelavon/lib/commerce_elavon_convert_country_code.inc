<?php

/**
 * Convert Country Code for Elavon Payment Module
 *
 * This function takes a country as input and returns the corresponding country code
 * format required by the Elavon payment module. It is used to standardize country codes
 * for processing payments, ensuring compatibility with Elavon's specifications.
 *
 * @param string $country The original country code to be converted.
 *
 * @return string $country_code The Elavon-compatible country code.
 */

function commerce_elavon_convert_country_code($country = '')
{
    $country_code = '';

    if ($country == 'AW') {
        $country_code = 'ABW'; //Aruba (ABW) 
    } elseif ($country == 'AF') {
        $country_code = 'AFG'; //Afghanistan (AFG) 
    } elseif ($country == 'AO') {
        $country_code = 'AGO'; //Angola (AGO) 
    } elseif ($country == 'AI') {
        $country_code = 'AIA'; //Anguilla (AIA) 
    } elseif ($country == 'AX') {
        $country_code = 'ALA'; //Åland Island  (ALA) 
    } elseif ($country == 'AL') {
        $country_code = 'ALB'; //Albania (ALB) 
    } elseif ($country == 'AD') {
        $country_code = 'AND'; //Andorra (AND) 
    } elseif ($country == 'AN') {
        $country_code = 'ANT'; //Netherlands Antilles (ANT) 
    } elseif ($country == 'AE') {
        $country_code = 'ARE'; //United Arab Emirates (ARE) 
    } elseif ($country == 'AR') {
        $country_code = 'ARG'; //Argentina (ARG) 
    } elseif ($country == 'AM') {
        $country_code = 'ARM'; //Armenia (ARM) 
    } elseif ($country == 'AS') {
        $country_code = 'ASM'; //American Samoa (ASM) 
    } elseif ($country == 'AQ') {
        $country_code = 'ATA'; //Antarctica (ATA) 
    } elseif ($country == 'TF') {
        $country_code = 'ATF'; //French Southern Territories (ATF) 
    } elseif ($country == 'AG') {
        $country_code = 'ATG'; //Antigua and Barbuda (ATG) 
    } elseif ($country == 'AU') {
        $country_code = 'AUS'; //Australia (AUS) 
    } elseif ($country == 'AT') {
        $country_code = 'AUT'; //Austria (AUT) 
    } elseif ($country == 'AZ') {
        $country_code = 'AZE'; //Azerbaijan (AZE) 
    } elseif ($country == 'BI') {
        $country_code = 'BDI'; //Burundi (BDI) 
    } elseif ($country == 'BE') {
        $country_code = 'BEL'; //Belgium (BEL) 
    } elseif ($country == 'BJ') {
        $country_code = 'BEN'; //Benin (BEN) 
    } elseif ($country == '') {
        $country_code = 'BES'; //Bonaire, Sint Eustatius and Saba (BES) 
    } elseif ($country == 'BF') {
        $country_code = 'BFA'; //Burkina Faso (BFA) 
    } elseif ($country == 'BD') {
        $country_code = 'BGD'; //Bangladesh (BGD) 
    } elseif ($country == 'BG') {
        $country_code = 'BGR'; //Bulgaria (BGR) 
    } elseif ($country == 'BH') {
        $country_code = 'BHR'; //Bahrain (BHR) 
    } elseif ($country == 'BS') {
        $country_code = 'BHS'; //Bahamas (BHS) 
    } elseif ($country == 'BA') {
        $country_code = 'BIH'; //Bosnia and Herzegovina (BIH) 
    } elseif ($country == 'BL') {
        $country_code = 'BLM'; //Saint Barthélemy (BLM) 
    } elseif ($country == 'BY') {
        $country_code = 'BLR'; //Belarus (BLR) 
    } elseif ($country == 'BZ') {
        $country_code = 'BLZ'; //Belize (BLZ) 
    } elseif ($country == 'BM') {
        $country_code = 'BMU'; //Bermuda (BMU) 
    } elseif ($country == 'BO') {
        $country_code = 'BOL'; //Bolivia, Plurinational State of (BOL) 
    } elseif ($country == 'BR') {
        $country_code = 'BRA'; //Brazil (BRA) 
    } elseif ($country == 'BB') {
        $country_code = 'BRB'; //Barbados (BRB) 
    } elseif ($country == 'BN') {
        $country_code = 'BRN'; //Brunei Darussalam (BRN) 
    } elseif ($country == 'BT') {
        $country_code = 'BTN'; //Bhutan (BTN) 
    } elseif ($country == 'BV') {
        $country_code = 'BVT'; //Bouvet Island (BVT) 
    } elseif ($country == 'BW') {
        $country_code = 'BWA'; //Botswana (BWA) 
    } elseif ($country == 'CF') {
        $country_code = 'CAF'; //Central African Republic (CAF) 
    } elseif ($country == 'CA') {
        $country_code = 'CAN'; //Canada (CAN) 
    } elseif ($country == 'CC') {
        $country_code = 'CCK'; //Cocos (Keeling) Islands (CCK) 
    } elseif ($country == 'CH') {
        $country_code = 'CHE'; //Switzerland (CHE) 
    } elseif ($country == 'CL') {
        $country_code = 'CHL'; //Chile (CHL) 
    } elseif ($country == 'CN') {
        $country_code = 'CHN'; //China (CHN) 
    } elseif ($country == 'CI') {
        $country_code = 'CIV'; //Côte d'Ivoire (CIV) 
    } elseif ($country == 'CM') {
        $country_code = 'CMR'; //Cameroon (CMR) 
    } elseif ($country == 'CD') {
        $country_code = 'COD'; //Congo, the Democratic Republic of the (COD) 
    } elseif ($country == 'CG') {
        $country_code = 'COG'; //Congo (COG) 
    } elseif ($country == 'CK') {
        $country_code = 'COK'; //Cook Islands (COK) 
    } elseif ($country == 'CO') {
        $country_code = 'COL'; //Colombia (COL) 
    } elseif ($country == 'KM') {
        $country_code = 'COM'; //Comoros (COM) 
    } elseif ($country == 'CV') {
        $country_code = 'CPV'; //Cape Verde (CPV) 
    } elseif ($country == 'CR') {
        $country_code = 'CRI'; //Costa Rica (CRI) 
    } elseif ($country == 'CU') {
        $country_code = 'CUB'; //Cuba (CUB) 
    } elseif ($country == 'CW') {
        $country_code = 'CUW'; //Curaçao (CUW) 
    } elseif ($country == 'CX') {
        $country_code = 'CXR'; //Christmas Island (CXR) 
    } elseif ($country == 'KY') {
        $country_code = 'CYM'; //Cayman Islands (CYM) 
    } elseif ($country == 'CY') {
        $country_code = 'CYP'; //Cyprus (CYP) 
    } elseif ($country == 'CZ') {
        $country_code = 'CZE'; //Czech Republic (CZE) 
    } elseif ($country == 'DE') {
        $country_code = 'DEU'; //Germany (DEU) 
    } elseif ($country == 'DJ') {
        $country_code = 'DJI'; //Djibouti (DJI) 
    } elseif ($country == 'DM') {
        $country_code = 'DMA'; //Dominica (DMA) 
    } elseif ($country == 'DK') {
        $country_code = 'DNK'; //Denmark (DNK) 
    } elseif ($country == 'DO') {
        $country_code = 'DOM'; //Dominican Republic (DOM) 
    } elseif ($country == 'DZ') {
        $country_code = 'DZA'; //Algeria (DZA) 
    } elseif ($country == 'EC') {
        $country_code = 'ECU'; //Ecuador (ECU) 
    } elseif ($country == 'EG') {
        $country_code = 'EGY'; //Egypt (EGY) 
    } elseif ($country == 'ER') {
        $country_code = 'ERI'; //Eritrea (ERI) 
    } elseif ($country == 'EH') {
        $country_code = 'ESH'; //Western Sahara (ESH) 
    } elseif ($country == 'ES') {
        $country_code = 'ESP'; //Spain (ESP) 
    } elseif ($country == 'EE') {
        $country_code = 'EST'; //Estonia (EST) 
    } elseif ($country == 'ET') {
        $country_code = 'ETH'; //Ethiopia (ETH) 
    } elseif ($country == 'FI') {
        $country_code = 'FIN'; //Finland (FIN) 
    } elseif ($country == 'FJ') {
        $country_code = 'FJI'; //Fiji (FJI) 
    } elseif ($country == 'FK') {
        $country_code = 'FLK'; //Falkland Islands (Malvinas  (FLK) 
    } elseif ($country == 'FR') {
        $country_code = 'FRA'; //France (FRA) 
    } elseif ($country == 'FO') {
        $country_code = 'FRO'; //Faroe Islands (FRO) 
    } elseif ($country == 'FM') {
        $country_code = 'FSM'; //Micronesia, Federated States of (FSM) 
    } elseif ($country == 'GA') {
        $country_code = 'GAB'; //Gabon (GAB) 
    } elseif ($country == 'GB') {
        $country_code = 'GBR'; //United Kingdom (GBR) 
    } elseif ($country == 'GE') {
        $country_code = 'GEO'; //Georgia (GEO) 
    } elseif ($country == 'GG') {
        $country_code = 'GGY'; //Guernsey (GGY) 
    } elseif ($country == 'GH') {
        $country_code = 'GHA'; //Ghana (GHA) 
    } elseif ($country == 'GI') {
        $country_code = 'GIB'; //Gibraltar (GIB) 
    } elseif ($country == 'GN') {
        $country_code = 'GIN'; //Guinea (GIN) 
    } elseif ($country == 'GP') {
        $country_code = 'GLP'; //Guadeloupe (GLP) 
    } elseif ($country == 'GM') {
        $country_code = 'GMB'; //Gambia (GMB) 
    } elseif ($country == 'GW') {
        $country_code = 'GNB'; //Guinea-Bissau (GNB) 
    } elseif ($country == 'GQ') {
        $country_code = 'GNQ'; //Equatorial Guinea (GNQ) 
    } elseif ($country == 'GR') {
        $country_code = 'GRC'; //Greece (GRC) 
    } elseif ($country == 'GD') {
        $country_code = 'GRD'; //Grenada (GRD) 
    } elseif ($country == 'GL') {
        $country_code = 'GRL'; //Greenland (GRL) 
    } elseif ($country == 'GT') {
        $country_code = 'GTM'; //Guatemala (GTM) 
    } elseif ($country == 'GF') {
        $country_code = 'GUF'; //French Guiana (GUF) 
    } elseif ($country == 'GU') {
        $country_code = 'GUM'; //Guam (GUM) 
    } elseif ($country == 'GY') {
        $country_code = 'GUY'; //Guyana (GUY) 
    } elseif ($country == 'HK') {
        $country_code = 'HKG'; //Hong Kong (HKG) 
    } elseif ($country == 'HM') {
        $country_code = 'HMD'; //Heard Island and McDonald Islands (HMD) 
    } elseif ($country == 'HN') {
        $country_code = 'HND'; //Honduras (HND) 
    } elseif ($country == 'HR') {
        $country_code = 'HRV'; //Croatia (HRV) 
    } elseif ($country == 'HT') {
        $country_code = 'HTI'; //Haiti (HTI) 
    } elseif ($country == 'HU') {
        $country_code = 'HUN'; //Hungary (HUN) 
    } elseif ($country == 'ID') {
        $country_code = 'IDN'; //Indonesia (IDN) 
    } elseif ($country == 'IM') {
        $country_code = 'IMN'; //Isle of Man (IMN) 
    } elseif ($country == 'IN') {
        $country_code = 'IND'; //India (IND) 
    } elseif ($country == 'IO') {
        $country_code = 'IOT'; //British Indian Ocean Territory (IOT) 
    } elseif ($country == 'IE') {
        $country_code = 'IRL'; //Ireland (IRL) 
    } elseif ($country == 'IR') {
        $country_code = 'IRN'; //Iran, Islamic Republic of (IRN) 
    } elseif ($country == 'IQ') {
        $country_code = 'IRQ'; //Iraq (IRQ) 
    } elseif ($country == 'IS') {
        $country_code = 'ISL'; //Iceland (ISL) 
    } elseif ($country == 'IL') {
        $country_code = 'ISR'; //Israel (ISR) 
    } elseif ($country == 'IT') {
        $country_code = 'ITA'; //Italy (ITA) 
    } elseif ($country == 'JM') {
        $country_code = 'JAM'; //Jamaica (JAM) 
    } elseif ($country == 'JE') {
        $country_code = 'JEY'; //Jersey (JEY) 
    } elseif ($country == 'JO') {
        $country_code = 'JOR'; //Jordan (JOR) 
    } elseif ($country == 'JP') {
        $country_code = 'JPN'; //Japan (JPN) 
    } elseif ($country == 'KZ') {
        $country_code = 'KAZ'; //Kazakhstan (KAZ) 
    } elseif ($country == 'KE') {
        $country_code = 'KEN'; //Kenya (KEN) 
    } elseif ($country == 'KG') {
        $country_code = 'KGZ'; //Kyrgyzstan (KGZ) 
    } elseif ($country == 'KH') {
        $country_code = 'KHM'; //Cambodia (KHM) 
    } elseif ($country == 'KI') {
        $country_code = 'KIR'; //Kiribati (KIR) 
    } elseif ($country == 'KN') {
        $country_code = 'KNA'; //Saint Kitts and Nevis (KNA) 
    } elseif ($country == 'KR') {
        $country_code = 'KOR'; //Korea, Republic of (KOR) 
    } elseif ($country == 'KW') {
        $country_code = 'KWT'; //Kuwait (KWT) 
    } elseif ($country == 'LA') {
        $country_code = 'LAO'; //Lao People's Democratic Republic (LAO) 
    } elseif ($country == 'LB') {
        $country_code = 'LBN'; //Lebanon (LBN) 
    } elseif ($country == 'LR') {
        $country_code = 'LBR'; //Liberia (LBR) 
    } elseif ($country == 'LY') {
        $country_code = 'LBY'; //Libyan Arab Jamahiriya (LBY) 
    } elseif ($country == 'LC') {
        $country_code = 'LCA'; //Saint Lucia (LCA) 
    } elseif ($country == 'LI') {
        $country_code = 'LIE'; //Liechtenstein (LIE) 
    } elseif ($country == 'LK') {
        $country_code = 'LKA'; //Sri Lanka (LKA) 
    } elseif ($country == 'LS') {
        $country_code = 'LSO'; //Lesotho (LSO) 
    } elseif ($country == 'LT') {
        $country_code = 'LTU'; //Lithuania (LTU) 
    } elseif ($country == 'LU') {
        $country_code = 'LUX'; //Luxembourg (LUX) 
    } elseif ($country == 'LV') {
        $country_code = 'LVA'; //Latvia (LVA) 
    } elseif ($country == 'MO') {
        $country_code = 'MAC'; //Macao (MAC) 
    } elseif ($country == 'MF') {
        $country_code = 'MAF'; //Saint Martin (French part) (MAF) 
    } elseif ($country == 'MA') {
        $country_code = 'MAR'; //Morocco (MAR) 
    } elseif ($country == 'MC') {
        $country_code = 'MCO'; //Monaco (MCO) 
    } elseif ($country == 'MD') {
        $country_code = 'MDA'; //Moldova, Republic of (MDA) 
    } elseif ($country == 'MG') {
        $country_code = 'MDG'; //Madagascar (MDG) 
    } elseif ($country == 'MV') {
        $country_code = 'MDV'; //Maldives (MDV) 
    } elseif ($country == 'MX') {
        $country_code = 'MEX'; //Mexico (MEX) 
    } elseif ($country == 'MH') {
        $country_code = 'MHL'; //Marshall Islands (MHL) 
    } elseif ($country == 'MK') {
        $country_code = 'MKD'; //Macedonia, the former Yugoslav Republic of (MKD) 
    } elseif ($country == 'ML') {
        $country_code = 'MLI'; //Mali (MLI) 
    } elseif ($country == 'MT') {
        $country_code = 'MLT'; //Malta (MLT) 
    } elseif ($country == 'MM') {
        $country_code = 'MMR'; //Myanmar (MMR) 
    } elseif ($country == 'ME') {
        $country_code = 'MNE'; //Montenegro (MNE) 
    } elseif ($country == 'MN') {
        $country_code = 'MNG'; //Mongolia (MNG) 
    } elseif ($country == 'MP') {
        $country_code = 'MNP'; //Northern Mariana Islands (MNP) 
    } elseif ($country == 'MZ') {
        $country_code = 'MOZ'; //Mozambique (MOZ) 
    } elseif ($country == 'MR') {
        $country_code = 'MRT'; //Mauritania (MRT) 
    } elseif ($country == 'MS') {
        $country_code = 'MSR'; //Montserrat (MSR) 
    } elseif ($country == 'MQ') {
        $country_code = 'MTQ'; //Martinique (MTQ) 
    } elseif ($country == 'MU') {
        $country_code = 'MUS'; //Mauritius (MUS) 
    } elseif ($country == 'MW') {
        $country_code = 'MWI'; //Malawi (MWI) 
    } elseif ($country == 'MY') {
        $country_code = 'MYS'; //Malaysia (MYS) 
    } elseif ($country == 'YT') {
        $country_code = 'MYT'; //Mayotte (MYT) 
    } elseif ($country == 'NA') {
        $country_code = 'NAM'; //Namibia (NAM) 
    } elseif ($country == 'NC') {
        $country_code = 'NCL'; //New Caledonia (NCL) 
    } elseif ($country == 'NE') {
        $country_code = 'NER'; //Niger (NER) 
    } elseif ($country == 'NF') {
        $country_code = 'NFK'; //Norfolk Island (NFK) 
    } elseif ($country == 'NG') {
        $country_code = 'NGA'; //Nigeria (NGA) 
    } elseif ($country == 'NI') {
        $country_code = 'NIC'; //Nicaragua (NIC) 
    } elseif ($country == 'NU') {
        $country_code = 'NIU'; //Niue (NIU) 
    } elseif ($country == 'NL') {
        $country_code = 'NLD'; //Netherlands (NLD) 
    } elseif ($country == 'NO') {
        $country_code = 'NOR'; //Norway (NOR) 
    } elseif ($country == 'NP') {
        $country_code = 'NPL'; //Nepal (NPL) 
    } elseif ($country == 'NR') {
        $country_code = 'NRU'; //Nauru (NRU) 
    } elseif ($country == 'NZ') {
        $country_code = 'NZL'; //New Zealand (NZL) 
    } elseif ($country == 'OM') {
        $country_code = 'OMN'; //Oman (OMN) 
    } elseif ($country == 'PK') {
        $country_code = 'PAK'; //Pakistan (PAK) 
    } elseif ($country == 'PA') {
        $country_code = 'PAN'; //Panama (PAN) 
    } elseif ($country == 'PN') {
        $country_code = 'PCN'; //Pitcairn (PCN) 
    } elseif ($country == 'PE') {
        $country_code = 'PER'; //Peru (PER) 
    } elseif ($country == 'PH') {
        $country_code = 'PHL'; //Philippines (PHL) 
    } elseif ($country == 'PW') {
        $country_code = 'PLW'; //Palau (PLW) 
    } elseif ($country == 'PG') {
        $country_code = 'PNG'; //Papua New Guinea (PNG) 
    } elseif ($country == 'PL') {
        $country_code = 'POL'; //Poland (POL) 
    } elseif ($country == 'PR') {
        $country_code = 'PRI'; //Puerto Rico (PRI) 
    } elseif ($country == 'KP') {
        $country_code = 'PRK'; //Korea, Democratic People's Republic of (PRK) 
    } elseif ($country == 'PT') {
        $country_code = 'PRT'; //Portugal (PRT) 
    } elseif ($country == 'PY') {
        $country_code = 'PRY'; //Paraguay (PRY) 
    } elseif ($country == 'PS') {
        $country_code = 'PSE'; //Palestinian Territory, Occupied (PSE) 
    } elseif ($country == 'PF') {
        $country_code = 'PYF'; //French Polynesi  (PYF) 
    } elseif ($country == 'QA') {
        $country_code = 'QAT'; //Qatar (QAT) 
    } elseif ($country == 'RE') {
        $country_code = 'REU'; //Réunion (REU) 
    } elseif ($country == 'RO') {
        $country_code = 'ROU'; //Romania (ROU) 
    } elseif ($country == 'RU') {
        $country_code = 'RUS'; //Russian Federation (RUS) 
    } elseif ($country == 'RW') {
        $country_code = 'RWA'; //Rwanda (RWA) 
    } elseif ($country == 'SA') {
        $country_code = 'SAU'; //Saudi Arabia (SAU) 
    } elseif ($country == 'SD') {
        $country_code = 'SDN'; //Sudan (SDN) 
    } elseif ($country == 'SN') {
        $country_code = 'SEN'; //Senegal (SEN) 
    } elseif ($country == 'SG') {
        $country_code = 'SGP'; //Singapore (SGP) 
    } elseif ($country == 'GS') {
        $country_code = 'SGS'; //South Georgia and the South Sandwich Islands (SGS) 
    } elseif ($country == 'SH') {
        $country_code = 'SHN'; //Saint Helena, Ascension and Tristan da Cunha (SHN) 
    } elseif ($country == 'SJ') {
        $country_code = 'SJM'; //Svalbard and Jan Mayen (SJM) 
    } elseif ($country == 'SB') {
        $country_code = 'SLB'; //Solomon Islands (SLB) 
    } elseif ($country == 'SL') {
        $country_code = 'SLE'; //Sierra Leone (SLE) 
    } elseif ($country == 'SV') {
        $country_code = 'SLV'; //El Salvador (SLV) 
    } elseif ($country == 'SM') {
        $country_code = 'SMR'; //San Marino (SMR) 
    } elseif ($country == 'SO') {
        $country_code = 'SOM'; //Somalia (SOM) 
    } elseif ($country == 'PM') {
        $country_code = 'SPM'; //Saint Pierre and Miquelon (SPM) 
    } elseif ($country == 'RS') {
        $country_code = 'SRB'; //Serbia (SRB) 
    } elseif ($country == 'SS') {
        $country_code = 'SSD'; //South Sudan (SSD) 
    } elseif ($country == 'ST') {
        $country_code = 'STP'; //Sao Tome and Principe (STP) 
    } elseif ($country == 'SR') {
        $country_code = 'SUR'; //Suriname (SUR) 
    } elseif ($country == 'SK') {
        $country_code = 'SVK'; //Slovakia (SVK) 
    } elseif ($country == 'SI') {
        $country_code = 'SVN'; //Slovenia (SVN) 
    } elseif ($country == 'SE') {
        $country_code = 'SWE'; //Sweden (SWE) 
    } elseif ($country == 'SZ') {
        $country_code = 'SWZ'; //Swaziland (SWZ) 
    } elseif ($country == 'SX') {
        $country_code = 'SXM'; //Sint Maarten (Dutch part) (SXM) 
    } elseif ($country == 'SC') {
        $country_code = 'SYC'; //Seychelles (SYC) 
    } elseif ($country == 'SY') {
        $country_code = 'SYR'; //Syrian Arab Republic (SYR) 
    } elseif ($country == 'TC') {
        $country_code = 'TCA'; //Turks and Caicos Islands (TCA) 
    } elseif ($country == 'TD') {
        $country_code = 'TCD'; //Chad (TCD) 
    } elseif ($country == 'TG') {
        $country_code = 'TGO'; //Togo (TGO) 
    } elseif ($country == 'TH') {
        $country_code = 'THA'; //Thailand (THA) 
    } elseif ($country == 'TJ') {
        $country_code = 'TJK'; //Tajikistan (TJK) 
    } elseif ($country == 'TK') {
        $country_code = 'TKL'; //Tokelau (TKL) 
    } elseif ($country == 'TM') {
        $country_code = 'TKM'; //Turkmenistan (TKM) 
    } elseif ($country == 'TL') {
        $country_code = 'TLS'; //Timor-Leste (TLS) 
    } elseif ($country == 'TO') {
        $country_code = 'TON'; //Tonga (TON) 
    } elseif ($country == 'TT') {
        $country_code = 'TTO'; //Trinida and Tobago (TTO) 
    } elseif ($country == 'TN') {
        $country_code = 'TUN'; //Tunisia (TUN) 
    } elseif ($country == 'TR') {
        $country_code = 'TUR'; //Turkey (TUR) 
    } elseif ($country == 'TV') {
        $country_code = 'TUV'; //Tuvalu (TUV) 
    } elseif ($country == 'TW') {
        $country_code = 'TWN'; //Taiwan, Province of China (TWN) 
    } elseif ($country == 'TZ') {
        $country_code = 'TZA'; //Tanzania, United Republic of (TZA) 
    } elseif ($country == 'UG') {
        $country_code = 'UGA'; //Uganda (UGA) 
    } elseif ($country == 'UA') {
        $country_code = 'UKR'; //Ukraine (UKR) 
    } elseif ($country == 'UM') {
        $country_code = 'UMI'; //United States Minor Outlying Islands (UMI) 
    } elseif ($country == 'UY') {
        $country_code = 'URY'; //Uruguay (URY) 
    } elseif ($country == 'US') {
        $country_code = 'USA'; //United States (USA) 
    } elseif ($country == 'UZ') {
        $country_code = 'UZB'; //Uzbekistan (UZB) 
    } elseif ($country == 'VA') {
        $country_code = 'VAT'; //Holy See (Vatican City State) (VAT) 
    } elseif ($country == 'VC') {
        $country_code = 'VCT'; //Saint Vincent and the Grenadines (VCT) 
    } elseif ($country == 'VE') {
        $country_code = 'VEN'; //Venezuela, Bolivarian Republic of (VEN) 
    } elseif ($country == 'VG') {
        $country_code = 'VGB'; //Virgin Islands, British (VGB) 
    } elseif ($country == 'VI') {
        $country_code = 'VIR'; //Virgin Islands, U.S. (VIR) 
    } elseif ($country == 'VN') {
        $country_code = 'VNM'; //Viet Nam (VNM) 
    } elseif ($country == 'VU') {
        $country_code = 'VUT'; //Vanuatu (VUT) 
    } elseif ($country == 'WF') {
        $country_code = 'WLF'; //Wallis and Futuna (WLF) 
    } elseif ($country == 'WS') {
        $country_code = 'WSM'; //Samoa (WSM) 
    } elseif ($country == 'YE') {
        $country_code = 'YEM'; //Yemen (YEM) 
    } elseif ($country == 'ZA') {
        $country_code = 'ZAF'; //South Africa (ZAF) 
    } elseif ($country == 'ZM') {
        $country_code = 'ZMB'; //Zambia (ZMB) 
    } elseif ($country == 'ZW') {
        $country_code = 'ZWE'; //Zimbabwe (ZWE) 
    }

    return $country_code;
}