<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Currency {

	/**
	 * @param flatcurr    float    integer to convert
	 * @param curr    string of desired currency format
	 *
	 * @return formatted number
	 */
	function format_currency( $floatcurr, $curr = "USD" ) {

		$currencies = $this->currencies();

		if ( ! function_exists( 'tajer_formatinr' ) ) {
			function tajer_formatinr( $input ) {
				//CUSTOM FUNCTION TO GENERATE ##,##,###.##
				$dec = "";
				$pos = strpos( $input, "." );
				if ( $pos === false ) {
					//no decimals
				} else {
					//decimals
					$dec   = substr( round( substr( $input, $pos ), 2 ), 1 );
					$input = substr( $input, 0, $pos );
				}
				$num   = substr( $input, - 3 ); //get the last 3 digits
				$input = substr( $input, 0, - 3 ); //omit the last 3 digits already stored in $num
				while ( strlen( $input ) > 0 ) //loop the process - further get digits 2 by 2
				{
					$num   = substr( $input, - 2 ) . "," . $num;
					$input = substr( $input, 0, - 2 );
				}

				return $num . $dec;
			}
		}


		if ( $curr == "INR" ) {
			return apply_filters( 'tajer_format_currency', tajer_formatinr( $floatcurr ), $floatcurr, $curr );
		} else {
			return apply_filters( 'tajer_format_currency', number_format( (float) $floatcurr, $currencies[ $curr ][0], $currencies[ $curr ][1], $currencies[ $curr ][2] ), $floatcurr, $curr );
		}
	}

	function currencies() {
		$currencies['ARS'] = array( 2, ',', '.' );            //	Argentine Peso
		$currencies['AMD'] = array( 2, '.', ',' );            //	Armenian Dram
		$currencies['AWG'] = array( 2, '.', ',' );            //	Aruban Guilder
		$currencies['AUD'] = array( 2, '.', ' ' );            //	Australian Dollar
		$currencies['BSD'] = array( 2, '.', ',' );            //	Bahamian Dollar
		$currencies['BHD'] = array( 3, '.', ',' );            //	Bahraini Dinar
		$currencies['BDT'] = array( 2, '.', ',' );            //	Bangladesh, Taka
		$currencies['BZD'] = array( 2, '.', ',' );            //	Belize Dollar
		$currencies['BMD'] = array( 2, '.', ',' );            //	Bermudian Dollar
		$currencies['BOB'] = array( 2, '.', ',' );            //	Bolivia, Boliviano
		$currencies['BAM'] = array( 2, '.', ',' );            //	Bosnia and Herzegovina, Convertible Marks
		$currencies['BWP'] = array( 2, '.', ',' );            //	Botswana, Pula
		$currencies['BRL'] = array( 2, ',', '.' );            //	Brazilian Real
		$currencies['BND'] = array( 2, '.', ',' );            //	Brunei Dollar
		$currencies['CAD'] = array( 2, '.', ',' );            //	Canadian Dollar
		$currencies['KYD'] = array( 2, '.', ',' );            //	Cayman Islands Dollar
		$currencies['CLP'] = array( 0, '', '.' );            //	Chilean Peso
		$currencies['CNY'] = array( 2, '.', ',' );            //	China Yuan Renminbi
		$currencies['COP'] = array( 2, ',', '.' );            //	Colombian Peso
		$currencies['CRC'] = array( 2, ',', '.' );            //	Costa Rican Colon
		$currencies['HRK'] = array( 2, ',', '.' );            //	Croatian Kuna
		$currencies['CUC'] = array( 2, '.', ',' );            //	Cuban Convertible Peso
		$currencies['CUP'] = array( 2, '.', ',' );            //	Cuban Peso
		$currencies['CYP'] = array( 2, '.', ',' );            //	Cyprus Pound
		$currencies['CZK'] = array( 2, '.', ',' );            //	Czech Koruna
		$currencies['DKK'] = array( 2, ',', '.' );            //	Danish Krone
		$currencies['DOP'] = array( 2, '.', ',' );            //	Dominican Peso
		$currencies['XCD'] = array( 2, '.', ',' );            //	East Caribbean Dollar
		$currencies['EGP'] = array( 2, '.', ',' );            //	Egyptian Pound
		$currencies['SVC'] = array( 2, '.', ',' );            //	El Salvador Colon
		$currencies['ATS'] = array( 2, ',', '.' );            //	Euro
		$currencies['BEF'] = array( 2, ',', '.' );            //	Euro
		$currencies['DEM'] = array( 2, ',', '.' );            //	Euro
		$currencies['EEK'] = array( 2, ',', '.' );            //	Euro
		$currencies['ESP'] = array( 2, ',', '.' );            //	Euro
		$currencies['EUR'] = array( 2, ',', '.' );            //	Euro
		$currencies['FIM'] = array( 2, ',', '.' );            //	Euro
		$currencies['FRF'] = array( 2, ',', '.' );            //	Euro
		$currencies['GRD'] = array( 2, ',', '.' );            //	Euro
		$currencies['IEP'] = array( 2, ',', '.' );            //	Euro
		$currencies['ITL'] = array( 2, ',', '.' );            //	Euro
		$currencies['LUF'] = array( 2, ',', '.' );            //	Euro
		$currencies['NLG'] = array( 2, ',', '.' );            //	Euro
		$currencies['PTE'] = array( 2, ',', '.' );            //	Euro
		$currencies['GHC'] = array( 2, '.', ',' );            //	Ghana, Cedi
		$currencies['GIP'] = array( 2, '.', ',' );            //	Gibraltar Pound
		$currencies['GTQ'] = array( 2, '.', ',' );            //	Guatemala, Quetzal
		$currencies['HNL'] = array( 2, '.', ',' );            //	Honduras, Lempira
		$currencies['HKD'] = array( 2, '.', ',' );            //	Hong Kong Dollar
		$currencies['HUF'] = array( 0, '', '.' );            //	Hungary, Forint
		$currencies['ISK'] = array( 0, '', '.' );            //	Iceland Krona
		$currencies['INR'] = array( 2, '.', ',' );            //	Indian Rupee
		$currencies['IDR'] = array( 2, ',', '.' );            //	Indonesia, Rupiah
		$currencies['IRR'] = array( 2, '.', ',' );            //	Iranian Rial
		$currencies['JMD'] = array( 2, '.', ',' );            //	Jamaican Dollar
		$currencies['JPY'] = array( 0, '', ',' );            //	Japan, Yen
		$currencies['JOD'] = array( 3, '.', ',' );            //	Jordanian Dinar
		$currencies['KES'] = array( 2, '.', ',' );            //	Kenyan Shilling
		$currencies['KWD'] = array( 3, '.', ',' );            //	Kuwaiti Dinar
		$currencies['LVL'] = array( 2, '.', ',' );            //	Latvian Lats
		$currencies['LBP'] = array( 0, '', ' ' );            //	Lebanese Pound
		$currencies['LTL'] = array( 2, ',', ' ' );            //	Lithuanian Litas
		$currencies['MKD'] = array( 2, '.', ',' );            //	Macedonia, Denar
		$currencies['MYR'] = array( 2, '.', ',' );            //	Malaysian Ringgit
		$currencies['MTL'] = array( 2, '.', ',' );            //	Maltese Lira
		$currencies['MUR'] = array( 0, '', ',' );            //	Mauritius Rupee
		$currencies['MXN'] = array( 2, '.', ',' );            //	Mexican Peso
		$currencies['MAD'] = array( 2, '.', ',' );            //	Moroccan Dirham
		$currencies['MZM'] = array( 2, ',', '.' );            //	Mozambique Metical
		$currencies['NPR'] = array( 2, '.', ',' );            //	Nepalese Rupee
		$currencies['NGN'] = array( 2, '.', ',' );            //	Nigerian Naira
		$currencies['ANG'] = array( 2, '.', ',' );            //	Netherlands Antillian Guilder
		$currencies['ILS'] = array( 2, '.', ',' );            //	New Israeli Shekel
		$currencies['TRY'] = array( 2, '.', ',' );            //	New Turkish Lira
		$currencies['NZD'] = array( 2, '.', ',' );            //	New Zealand Dollar
		$currencies['NOK'] = array( 2, ',', '.' );            //	Norwegian Krone
		$currencies['PKR'] = array( 2, '.', ',' );            //	Pakistan Rupee
		$currencies['PEN'] = array( 2, '.', ',' );            //	Peru, Nuevo Sol
		$currencies['UYU'] = array( 2, ',', '.' );            //	Peso Uruguayo
		$currencies['PHP'] = array( 2, '.', ',' );            //	Philippine Peso
		$currencies['PLN'] = array( 2, '.', ' ' );            //	Poland, Zloty
		$currencies['GBP'] = array( 2, '.', ',' );            //	Pound Sterling
		$currencies['OMR'] = array( 3, '.', ',' );            //	Rial Omani
		$currencies['RON'] = array( 2, ',', '.' );            //	Romania, New Leu
		$currencies['ROL'] = array( 2, ',', '.' );            //	Romania, Old Leu
		$currencies['RUB'] = array( 2, ',', '.' );            //	Russian Ruble
		$currencies['SAR'] = array( 2, '.', ',' );            //	Saudi Riyal
		$currencies['SGD'] = array( 2, '.', ',' );            //	Singapore Dollar
		$currencies['SKK'] = array( 2, ',', ' ' );            //	Slovak Koruna
		$currencies['SIT'] = array( 2, ',', '.' );            //	Slovenia, Tolar
		$currencies['ZAR'] = array( 2, '.', ' ' );            //	South Africa, Rand
		$currencies['KRW'] = array( 0, '', ',' );            //	South Korea, Won
		$currencies['SZL'] = array( 2, '.', ', ' );            //	Swaziland, Lilangeni
		$currencies['SEK'] = array( 2, ',', '.' );            //	Swedish Krona
		$currencies['CHF'] = array( 2, '.', '\'' );            //	Swiss Franc
		$currencies['TZS'] = array( 2, '.', ',' );            //	Tanzanian Shilling
		$currencies['THB'] = array( 2, '.', ',' );            //	Thailand, Baht
		$currencies['TOP'] = array( 2, '.', ',' );            //	Tonga, Paanga
		$currencies['AED'] = array( 2, '.', ',' );            //	UAE Dirham
		$currencies['UAH'] = array( 2, ',', ' ' );            //	Ukraine, Hryvnia
		$currencies['USD'] = array( 2, '.', ',' );            //	US Dollar
		$currencies['VUV'] = array( 0, '', ',' );            //	Vanuatu, Vatu
		$currencies['VEF'] = array( 2, ',', '.' );            //	Venezuela Bolivares Fuertes
		$currencies['VEB'] = array( 2, ',', '.' );            //	Venezuela, Bolivar
		$currencies['VND'] = array( 0, '', '.' );            //	Viet Nam, Dong
		$currencies['ZWD'] = array( 2, '.', ' ' );            //	Zimbabwe Dollar

		$currencies = apply_filters( 'tajer_currencies', $currencies );

		return $currencies;
	}


	/*
	format_currency(1000045.25);				//1,000,045.25 (USD)
	format_currency(1000045.25, "CHF");		//1'000'045.25
	format_currency(1000045.25, "EUR");		//1.000.045,25
	format_currency(1000045, "JPY");			//1,000,045
	format_currency(1000045, "LBP");			//1 000 045
	format_currency(1000045.25, "INR");		//10,00,045.25
	*/

	function currency_codes_array() {

		$currency_symbols = $this->currency_symbols_as_HTML_entities();

		$currencies        = array();
		$currencies['ARS'] = 'Argentine Peso (' . $currency_symbols['ARS'] . ')';
		$currencies['AMD'] = 'Armenian Dram (' . $currency_symbols['AMD'] . ')';
		$currencies['AWG'] = 'Aruban Guilder (' . $currency_symbols['AWG'] . ')';
		$currencies['AUD'] = 'Australian Dollar (' . $currency_symbols['AUD'] . ')';
		$currencies['BSD'] = 'Bahamian Dollar (' . $currency_symbols['BSD'] . ')';
		$currencies['BHD'] = 'Bahraini Dinar (' . $currency_symbols['BHD'] . ')';
		$currencies['BDT'] = 'Bangladesh, Taka (' . $currency_symbols['BDT'] . ')';
		$currencies['BZD'] = 'Belize Dollar (' . $currency_symbols['BZD'] . ')';
		$currencies['BMD'] = 'Bermudian Dollar (' . $currency_symbols['BMD'] . ')';
		$currencies['BOB'] = 'Bolivia, Boliviano (' . $currency_symbols['BOB'] . ')';
		$currencies['BAM'] = 'Bosnia and Herzegovina, Convertible Marks (' . $currency_symbols['BAM'] . ')';
		$currencies['BWP'] = 'Botswana, Pula (' . $currency_symbols['BWP'] . ')';
		$currencies['BRL'] = 'Brazilian Real (' . $currency_symbols['BRL'] . ')';
		$currencies['BND'] = 'Brunei Dollar (' . $currency_symbols['BND'] . ')';
		$currencies['CAD'] = 'Canadian Dollar (' . $currency_symbols['CAD'] . ')';
		$currencies['KYD'] = 'Cayman Islands Dollar (' . $currency_symbols['KYD'] . ')';
		$currencies['CLP'] = 'Chilean Peso (' . $currency_symbols['CLP'] . ')';
		$currencies['CNY'] = 'China Yuan Renminbi (' . $currency_symbols['CNY'] . ')';
		$currencies['COP'] = 'Colombian Peso (' . $currency_symbols['COP'] . ')';
		$currencies['CRC'] = 'Costa Rican Colon (' . $currency_symbols['CRC'] . ')';
		$currencies['HRK'] = 'Croatian Kuna (' . $currency_symbols['HRK'] . ')';
		$currencies['CUC'] = 'Cuban Convertible Peso (' . ( isset( $currency_symbols['CUC'] ) ? $currency_symbols['CUC'] : '' ) . ')';
		$currencies['CUP'] = 'Cuban Peso (' . $currency_symbols['CUP'] . ')';
		$currencies['CYP'] = 'Cyprus Pound (' . ( isset( $currency_symbols['CYP'] ) ? $currency_symbols['CYP'] : '' ) . ')';
		$currencies['CZK'] = 'Czech Koruna (' . $currency_symbols['CZK'] . ')';
		$currencies['DKK'] = 'Danish Krone (' . $currency_symbols['DKK'] . ')';
		$currencies['DOP'] = 'Dominican Peso (' . $currency_symbols['DOP'] . ')';
		$currencies['XCD'] = 'East Caribbean Dollar (' . $currency_symbols['XCD'] . ')';
		$currencies['EGP'] = 'Egyptian Pound (' . $currency_symbols['EGP'] . ')';
		$currencies['SVC'] = 'El Salvador Colon (' . $currency_symbols['SVC'] . ')';
		$currencies['ATS'] = 'Euro (ATS) (' . $currency_symbols['EUR'] . ')';
		$currencies['BEF'] = 'Euro (BEF) (' . $currency_symbols['EUR'] . ')';
		$currencies['DEM'] = 'Euro (DEM) (' . $currency_symbols['EUR'] . ')';
		$currencies['EEK'] = 'Euro (EEK) (' . $currency_symbols['EUR'] . ')';
		$currencies['ESP'] = 'Euro (ESP) (' . $currency_symbols['EUR'] . ')';
		$currencies['EUR'] = 'Euro (EUR) (' . $currency_symbols['EUR'] . ')';
		$currencies['FIM'] = 'Euro (FIM) (' . $currency_symbols['EUR'] . ')';
		$currencies['FRF'] = 'Euro (FRF) (' . $currency_symbols['EUR'] . ')';
		$currencies['GRD'] = 'Euro (GRD) (' . $currency_symbols['EUR'] . ')';
		$currencies['IEP'] = 'Euro (IEP) (' . $currency_symbols['EUR'] . ')';
		$currencies['ITL'] = 'Euro (ITL) (' . $currency_symbols['EUR'] . ')';
		$currencies['LUF'] = 'Euro (LUF) (' . $currency_symbols['EUR'] . ')';
		$currencies['NLG'] = 'Euro (NLG) (' . $currency_symbols['EUR'] . ')';
		$currencies['PTE'] = 'Euro (PTE) (' . $currency_symbols['EUR'] . ')';
		$currencies['GHC'] = 'Ghana, Cedi (' . ( isset( $currency_symbols['GHC'] ) ? $currency_symbols['GHC'] : '' ) . ')';
		$currencies['GIP'] = 'Gibraltar Pound (' . $currency_symbols['GIP'] . ')';
		$currencies['GTQ'] = 'Guatemala, Quetzal (' . $currency_symbols['GTQ'] . ')';
		$currencies['HNL'] = 'Honduras, Lempira (' . $currency_symbols['HNL'] . ')';
		$currencies['HKD'] = 'Hong Kong Dollar (' . $currency_symbols['HKD'] . ')';
		$currencies['HUF'] = 'Hungary, Forint (' . $currency_symbols['HUF'] . ')';
		$currencies['ISK'] = 'Iceland Krona (' . $currency_symbols['ISK'] . ')';
		$currencies['INR'] = 'Indian Rupee (' . $currency_symbols['INR'] . ')';
		$currencies['IDR'] = 'Indonesia, Rupiah (' . $currency_symbols['IDR'] . ')';
		$currencies['IRR'] = 'Iranian Rial (' . $currency_symbols['IRR'] . ')';
		$currencies['JMD'] = 'Jamaican Dollar (' . $currency_symbols['JMD'] . ')';
		$currencies['JPY'] = 'Japan, Yen (' . $currency_symbols['JPY'] . ')';
		$currencies['JOD'] = 'Jordanian Dinar (' . $currency_symbols['JOD'] . ')';
		$currencies['KES'] = 'Kenyan Shilling (' . $currency_symbols['KES'] . ')';
		$currencies['KWD'] = 'Kuwaiti Dinar (' . $currency_symbols['KWD'] . ')';
		$currencies['LVL'] = 'Latvian Lats (' . $currency_symbols['LVL'] . ')';
		$currencies['LBP'] = 'Lebanese Pound (' . $currency_symbols['LBP'] . ')';
		$currencies['LTL'] = 'Lithuanian Litas (' . $currency_symbols['LTL'] . ')';
		$currencies['MKD'] = 'Macedonia, Denar (' . $currency_symbols['MKD'] . ')';
		$currencies['MYR'] = 'Malaysian Ringgit (' . $currency_symbols['MYR'] . ')';
		$currencies['MTL'] = 'Maltese Lira (' . ( isset( $currency_symbols['MTL'] ) ? $currency_symbols['MTL'] : '' ) . ')';
		$currencies['MUR'] = 'Mauritius Rupee (' . $currency_symbols['MUR'] . ')';
		$currencies['MXN'] = 'Mexican Peso (' . $currency_symbols['MXN'] . ')';
		$currencies['MAD'] = 'Moroccan Dirham (' . ( isset( $currency_symbols['MAD'] ) ? $currency_symbols['MAD'] : '' ) . ')';
		$currencies['MZM'] = 'Mozambique Metical (' . ( isset( $currency_symbols['MZM'] ) ? $currency_symbols['MZM'] : '' ) . ')';
		$currencies['NGN'] = 'Nigerian Naira (' . $currency_symbols['NGN'] . ')';
		$currencies['NPR'] = 'Nepalese Rupee (' . $currency_symbols['NPR'] . ')';
		$currencies['ANG'] = 'Netherlands Antillian Guilder (' . $currency_symbols['ANG'] . ')';
		$currencies['ILS'] = 'New Israeli Shekel (' . $currency_symbols['ILS'] . ')';
		$currencies['TRY'] = 'New Turkish Lira (' . $currency_symbols['TRY'] . ')';
		$currencies['NZD'] = 'New Zealand Dollar (' . $currency_symbols['NZD'] . ')';
		$currencies['NOK'] = 'Norwegian Krone (' . $currency_symbols['NOK'] . ')';
		$currencies['PKR'] = 'Pakistan Rupee (' . $currency_symbols['PKR'] . ')';
		$currencies['PEN'] = 'Peru, Nuevo Sol (' . $currency_symbols['PEN'] . ')';
		$currencies['UYU'] = 'Peso Uruguayo (' . $currency_symbols['UYU'] . ')';
		$currencies['PHP'] = 'Philippine Peso (' . $currency_symbols['PHP'] . ')';
		$currencies['PLN'] = 'Poland, Zloty (' . $currency_symbols['PLN'] . ')';
		$currencies['GBP'] = 'Pound Sterling (' . $currency_symbols['GBP'] . ')';
		$currencies['OMR'] = 'Rial Omani (' . $currency_symbols['OMR'] . ')';
		$currencies['RON'] = 'Romania, New Leu (' . $currency_symbols['RON'] . ')';
		$currencies['ROL'] = 'Romania, Old Leu (' . ( isset( $currency_symbols['ROL'] ) ? $currency_symbols['ROL'] : '' ) . ')';
		$currencies['RUB'] = 'Russian Ruble (' . $currency_symbols['RUB'] . ')';
		$currencies['SAR'] = 'Saudi Riyal (' . $currency_symbols['SAR'] . ')';
		$currencies['SGD'] = 'Singapore Dollar (' . $currency_symbols['SGD'] . ')';
		$currencies['SKK'] = 'Slovak Koruna (' . ( isset( $currency_symbols['SKK'] ) ? $currency_symbols['SKK'] : '' ) . ')';
		$currencies['SIT'] = 'Slovenia, Tolar (' . ( isset( $currency_symbols['SIT'] ) ? $currency_symbols['SIT'] : '' ) . ')';
		$currencies['ZAR'] = 'South Africa, Rand (' . $currency_symbols['ZAR'] . ')';
		$currencies['KRW'] = 'South Korea, Won (' . $currency_symbols['KRW'] . ')';
		$currencies['SZL'] = 'Swaziland, Lilangeni (' . $currency_symbols['SZL'] . ')';
		$currencies['SEK'] = 'Swedish Krona (' . $currency_symbols['SEK'] . ')';
		$currencies['CHF'] = 'Swiss Franc (' . $currency_symbols['CHF'] . ')';
		$currencies['TZS'] = 'Tanzanian Shilling (' . $currency_symbols['TZS'] . ')';
		$currencies['THB'] = 'Thailand, Baht (' . $currency_symbols['THB'] . ')';
		$currencies['TOP'] = 'Tonga, Paanga (' . $currency_symbols['TOP'] . ')';
		$currencies['AED'] = 'UAE Dirham (' . $currency_symbols['AED'] . ')';
		$currencies['UAH'] = 'Ukraine, Hryvnia (' . $currency_symbols['UAH'] . ')';
		$currencies['USD'] = 'US Dollar (' . $currency_symbols['USD'] . ')';
		$currencies['VUV'] = 'Vanuatu, Vatu (' . $currency_symbols['VUV'] . ')';
		$currencies['VEF'] = 'Venezuela Bolivares Fuertes (' . $currency_symbols['VEF'] . ')';
		$currencies['VEB'] = 'Venezuela, Bolivar (' . ( isset( $currency_symbols['VEB'] ) ? $currency_symbols['VEB'] : '' ) . ')';
		$currencies['VND'] = 'Viet Nam, Dong (' . $currency_symbols['VND'] . ')';
		$currencies['ZWD'] = 'Zimbabwe Dollar (' . ( isset( $currency_symbols['ZWD'] ) ? $currency_symbols['ZWD'] : '' ) . ')';

		return apply_filters( 'tajer_currency_codes', $currencies );

	}

	function currency_symbols_as_HTML_entities() {

		$currency_symbols = array(
			'AED' => '&#1583;.&#1573;', // ?
			'AFN' => '&#65;&#102;',
			'ALL' => '&#76;&#101;&#107;',
			'AMD' => '',
			'ANG' => '&#402;',
			'AOA' => '&#75;&#122;', // ?
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => '&#402;',
			'AZN' => '&#1084;&#1072;&#1085;',
			'BAM' => '&#75;&#77;',
			'BBD' => '&#36;',
			'BDT' => '&#2547;', // ?
			'BGN' => '&#1083;&#1074;',
			'BHD' => '.&#1583;.&#1576;', // ?
			'BIF' => '&#70;&#66;&#117;', // ?
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => '&#36;&#98;',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTN' => '&#78;&#117;&#46;', // ?
			'BWP' => '&#80;',
			'BYR' => '&#112;&#46;',
			'BZD' => '&#66;&#90;&#36;',
			'CAD' => '&#36;',
			'CDF' => '&#70;&#67;',
			'CHF' => '&#67;&#72;&#70;',
			'CLF' => '', // ?
			'CLP' => '&#36;',
			'CNY' => '&#165;',
			'COP' => '&#36;',
			'CRC' => '&#8353;',
			'CUP' => '&#8396;',
			'CVE' => '&#36;', // ?
			'CZK' => '&#75;&#269;',
			'DJF' => '&#70;&#100;&#106;', // ?
			'DKK' => '&#107;&#114;',
			'DOP' => '&#82;&#68;&#36;',
			'DZD' => '&#1583;&#1580;', // ?
			'EGP' => '&#163;',
			'ETB' => '&#66;&#114;',
			'EUR' => '&#8364;',
			'FJD' => '&#36;',
			'FKP' => '&#163;',
			'GBP' => '&#163;',
			'GEL' => '&#4314;', // ?
			'GHS' => '&#162;',
			'GIP' => '&#163;',
			'GMD' => '&#68;', // ?
			'GNF' => '&#70;&#71;', // ?
			'GTQ' => '&#81;',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => '&#76;',
			'HRK' => '&#107;&#110;',
			'HTG' => '&#71;', // ?
			'HUF' => '&#70;&#116;',
			'IDR' => '&#82;&#112;',
			'ILS' => '&#8362;',
			'INR' => '&#8377;',
			'IQD' => '&#1593;.&#1583;', // ?
			'IRR' => '&#65020;',
			'ISK' => '&#107;&#114;',
			'JEP' => '&#163;',
			'JMD' => '&#74;&#36;',
			'JOD' => '&#74;&#68;', // ?
			'JPY' => '&#165;',
			'KES' => '&#75;&#83;&#104;', // ?
			'KGS' => '&#1083;&#1074;',
			'KHR' => '&#6107;',
			'KMF' => '&#67;&#70;', // ?
			'KPW' => '&#8361;',
			'KRW' => '&#8361;',
			'KWD' => '&#1583;.&#1603;', // ?
			'KYD' => '&#36;',
			'KZT' => '&#1083;&#1074;',
			'LAK' => '&#8365;',
			'LBP' => '&#163;',
			'LKR' => '&#8360;',
			'LRD' => '&#36;',
			'LSL' => '&#76;', // ?
			'LTL' => '&#76;&#116;',
			'LVL' => '&#76;&#115;',
			'LYD' => '&#1604;.&#1583;', // ?
			'MAD' => '&#1583;.&#1605;.', //?
			'MDL' => '&#76;',
			'MGA' => '&#65;&#114;', // ?
			'MKD' => '&#1076;&#1077;&#1085;',
			'MMK' => '&#75;',
			'MNT' => '&#8366;',
			'MOP' => '&#77;&#79;&#80;&#36;', // ?
			'MRO' => '&#85;&#77;', // ?
			'MUR' => '&#8360;', // ?
			'MVR' => '.&#1923;', // ?
			'MWK' => '&#77;&#75;',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => '&#77;&#84;',
			'NAD' => '&#36;',
			'NGN' => '&#8358;',
			'NIO' => '&#67;&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#65020;',
			'PAB' => '&#66;&#47;&#46;',
			'PEN' => '&#83;&#47;&#46;',
			'PGK' => '&#75;', // ?
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PYG' => '&#71;&#115;',
			'QAR' => '&#65020;',
			'RON' => '&#108;&#101;&#105;',
			'RSD' => '&#1044;&#1080;&#1085;&#46;',
			'RUB' => '&#1088;&#1091;&#1073;',
			'RWF' => '&#1585;.&#1587;',
			'SAR' => '&#65020;',
			'SBD' => '&#36;',
			'SCR' => '&#8360;',
			'SDG' => '&#163;', // ?
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&#163;',
			'SLL' => '&#76;&#101;', // ?
			'SOS' => '&#83;',
			'SRD' => '&#36;',
			'STD' => '&#68;&#98;', // ?
			'SVC' => '&#36;',
			'SYP' => '&#163;',
			'SZL' => '&#76;', // ?
			'THB' => '&#3647;',
			'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
			'TMT' => '&#109;',
			'TND' => '&#1583;.&#1578;',
			'TOP' => '&#84;&#36;',
			'TRY' => '&#8356;', // New Turkey Lira (old symbol used)
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => '',
			'UAH' => '&#8372;',
			'UGX' => '&#85;&#83;&#104;',
			'USD' => '&#36;',
			'UYU' => '&#36;&#85;',
			'UZS' => '&#1083;&#1074;',
			'VEF' => '&#66;&#115;',
			'VND' => '&#8363;',
			'VUV' => '&#86;&#84;',
			'WST' => '&#87;&#83;&#36;',
			'XAF' => '&#70;&#67;&#70;&#65;',
			'XCD' => '&#36;',
			'XDR' => '',
			'XOF' => '',
			'XPF' => '&#70;',
			'YER' => '&#65020;',
			'ZAR' => '&#82;',
			'ZMK' => '&#90;&#75;', // ?
			'ZWL' => '&#90;&#36;',
		);

		return apply_filters( 'tajer_currency_symbols', $currency_symbols );
	}

}
