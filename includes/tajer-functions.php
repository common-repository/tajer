<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

//function tajer_admin_script($hook) {
//	if ( 'profile.php' != $hook ) {
//		return;
//	}
//
////	wp_enqueue_style( 'tajer_jquery_ui_css', plugin_dir_url( __FILE__ ) . 'jquery-ui.min.css' );
////	wp_enqueue_style( 'tajer_jquery_ui_theme', plugin_dir_url( __FILE__ ) . 'jquery-ui.theme.min.css',array( 'tajer_jquery_ui_css' ) );
////	wp_enqueue_script( 'jquery' );
////	wp_enqueue_script( 'tajer_jquery_ui_js', plugin_dir_url( __FILE__ ) . 'jquery-ui.min.js',array( 'jquery' ) );
////	wp_enqueue_script( 'tajer_profile_admin_script', plugin_dir_url( __FILE__ ) . 'tajer-admin.js',array( 'tajer_jquery_ui_js' ) );
//}
//add_action( 'admin_enqueue_scripts', 'tajer_admin_script' );

/**
 * Retrieve or display list of posts as a dropdown (select list).
 *
 * @param string $post_type
 *
 * @return array
 */
function tajer_get_pages( $post_type = 'page' ) {
	$array = array();
	$pages = get_posts( array( 'post_type' => $post_type, 'numberposts' => - 1 ) );
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$array[ $page->ID ] = $page->post_title;
		}
	}

	$array = apply_filters( 'tajer_get_pages', $array, $post_type );

	return $array;
}

function tajer_colors() {
	$colors['teal']   = 'Teal';
	$colors['blue']   = 'Blue';
	$colors['red']    = 'Red';
	$colors['orange'] = 'Orange';
	$colors['yellow'] = 'Yellow';
	$colors['olive']  = 'Olive';
	$colors['green']  = 'Green';
	$colors['violet'] = 'Violet';
	$colors['purple'] = 'Purple';
	$colors['pink']   = 'Pink';
	$colors['brown']  = 'Brown';
	$colors['grey']   = 'Grey';
	$colors['black']  = 'Black';

	return apply_filters( 'tajer_colors', $colors );
}

function tajer_get_color_code() {
	$color = tajer_get_option( 'color', 'tajer_general_settings', 'teal' );

	switch ( $color ) {
		case 'teal':
			$desired_color = '#00B5AD';
			break;
		case 'blue':
			$desired_color = '#2185D0';
			break;
		case 'red':
			$desired_color = '#DB2828';
			break;
		case 'orange':
			$desired_color = '#F2711C';
			break;
		case 'yellow':
			$desired_color = '#FBBD08';
			break;
		case 'olive':
			$desired_color = '#B5CC18';
			break;
		case 'green':
			$desired_color = '#21BA45';
			break;
		case 'violet':
			$desired_color = '#6435C9';
			break;
		case 'purple':
			$desired_color = '#A333C8';
			break;
		case 'pink':
			$desired_color = '#E03997';
			break;
		case 'brown':
			$desired_color = '#A5673F';
			break;
		case 'grey':
			$desired_color = '#767676';
			break;
		case 'black':
			$desired_color = '#1B1C1D';
			break;
		default:
			$desired_color = '#00B5AD';
			break;
	}

	return $desired_color;
}

function tajer_get_secondary_color_code() {
	$color = tajer_get_option( 'color', 'tajer_general_settings', 'teal' );

	switch ( $color ) {
		case 'teal':
			$desired_color = '#00B5AD';
			break;
		case 'blue':
			$desired_color = '#2185D0';
			break;
		case 'red':
			$desired_color = '#DB2828';
			break;
		case 'orange':
			$desired_color = '#F2711C';
			break;
		case 'yellow':
			$desired_color = '#FBBD08';
			break;
		case 'olive':
			$desired_color = '#B5CC18';
			break;
		case 'green':
			$desired_color = '#21BA45';
			break;
		case 'violet':
			$desired_color = '#6435C9';
			break;
		case 'purple':
			$desired_color = '#A333C8';
			break;
		case 'pink':
			$desired_color = '#E03997';
			break;
		case 'brown':
			$desired_color = '#A5673F';
			break;
		case 'grey':
			$desired_color = '#767676';
			break;
		case 'black':
			$desired_color = '#1B1C1D';
			break;
		default:
			$desired_color = '#00B5AD';
			break;
	}

	return $desired_color;
}

//function tajer_is_multiple_prices( $product_id ) {
//	return true;
//	$is_multiple_prices = get_post_meta( $product_id, 'tajer_variable_pricing', true );
//	if ( $is_multiple_prices == 1 ) {
//		return true;
//	} else {
//		return false;
//	}
//}

function tajer_get_default_price_id( $product_id ) {
	$default_price = get_post_meta( (int) $product_id, 'tajer_default_multiple_price', true );

	$default_price = apply_filters( 'tajer_get_default_price_id', $default_price, $product_id );

	return $default_price;
}

function tajer_session() {
	return apply_filters( 'tajer_session', false );
}

function tajer_get_files() {
	//path to directory to scan
	$upload_dir = wp_upload_dir();
	$directory  = $upload_dir['basedir'] . '/tajer/';

	//get all files
	$files = glob( $directory . "*" );


	//remove index.html file from $files array
	if ( ( $key = array_search( $directory . 'index.html', $files ) ) !== false ) {
		unset( $files[ $key ] );
	}

	$files = apply_filters( 'tajer_get_files', $files );

	return $files;
}

function tajer_is_trial( $product_id, $product_sub_id ) {
	$is_trial = get_post_meta( $product_id, 'tajer_is_trial', true );
	$trials   = get_post_meta( (int) $product_id, 'tajer_trial', true );

	$product_sub_ids_that_have_trial = array();

	foreach ( $trials as $trial ) {
		if ( ! is_array( $trial['prices'] ) ) {
			continue;
		}
		$product_sub_ids_that_have_trial = array_merge( $product_sub_ids_that_have_trial, $trial['prices'] );
	}


	if ( ( $is_trial == 1 ) && ( in_array( (string) $product_sub_id, $product_sub_ids_that_have_trial ) ) ) {
		$returned_value = true;
	} else {
		$returned_value = false;
	}
	$returned_value = apply_filters( 'tajer_is_trial', $returned_value, $product_id );

	return $returned_value;
}

function tajer_is_free( $product_id, $product_sub_id ) {
	$prices  = get_post_meta( $product_id, 'tajer_product_prices', true );
	$is_free = $prices[ $product_sub_id ]['capabilities'];
//	$is_free = get_post_meta( $product_id, 'tajer_capabilities', true );
	if ( $is_free == 'free' ) {
		$returned_value = true;
	} else {
		$returned_value = false;
	}

	$returned_value = apply_filters( 'tajer_is_free', $returned_value, $product_id, $product_sub_id );

	return $returned_value;
}

function tajer_is_bundle( $product_id ) {
	$is_bundle = get_post_meta( $product_id, 'tajer_bundle', true );
	if ( $is_bundle == 1 ) {
		$returned_value = true;
	} else {
		$returned_value = false;
	}

	$returned_value = apply_filters( 'tajer_is_bundle', $returned_value, $product_id );

	return $returned_value;
}

function tajer_is_direct_trial( $product_id, $product_sub_id ) {
	$trial           = get_post_meta( (int) $product_id, 'tajer_trial', true );
	$is_direct_trial = $trial[ $product_sub_id ]['direct_trial'];
	if ( $is_direct_trial == 'yes' ) {
		$returned_value = true;
	} else {
		$returned_value = false;
	}

	$returned_value = apply_filters( 'tajer_is_direct_trial', $returned_value, $product_id, $product_sub_id );

	return $returned_value;
}

function tajer_is_recurring( $product_id ) {
	$is_recurring = get_post_meta( $product_id, 'tajer_is_recurring', true );
	if ( $is_recurring == 1 ) {
		$returned_value = true;
	} else {
		$returned_value = false;
	}

	$returned_value = apply_filters( 'tajer_is_recurring', $returned_value, $product_id );

	return $returned_value;
}

function tajer_is_upgrade( $product_id ) {
	$is_upgrade = get_post_meta( $product_id, 'tajer_is_upgrade', true );
	if ( $is_upgrade == 1 ) {
		$returned_value = true;
	} else {
		$returned_value = false;
	}

	$returned_value = apply_filters( 'tajer_is_upgrade', $returned_value, $product_id );

	return $returned_value;
}

/**
 * Get the value of a settings field
 *
 * @param string $option settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 *
 * @return mixed
 */
function tajer_get_option( $option, $section, $default = '' ) {

	$options = get_option( $section );

	if ( isset( $options[ $option ] ) ) {
		$returned_value = $options[ $option ];
	} else {
		$returned_value = $default;
	}

	$returned_value = apply_filters( 'tajer_get_option', $returned_value, $option, $section, $default );

	return $returned_value;
}

function tajer_http_response( $response, $start_delimiter, $end_delimiter, $serialize = true, $echo = true ) {

	$response = apply_filters( 'tajer_http_before_response', $response, $start_delimiter, $end_delimiter, $serialize, $echo );

	if ( $serialize ) {
		$string = serialize( $response );
	} else {
		$string = $response;

	}

	$serialize = $start_delimiter . $string . $end_delimiter;

	$serialize = apply_filters( 'tajer_http_response', $serialize, $response, $start_delimiter, $end_delimiter, $serialize, $echo );

	if ( $echo ) {
		echo $serialize;
	} else {
		return $serialize;
	}
}

function tajer_response( $response ) {

	$response = apply_filters( 'tajer_response', $response );

	$json = json_encode( $response );

	//add our boundary, to make it easy in capturing it with regex in case we got unexpected result
	$json = '[tajer_json]' . $json . '[/tajer_json]';

	$json = apply_filters( 'tajer_response_response', $json, $response );

	echo $json;
	exit;
}

//function tajer_generate_unique_name( $product_id = '' ) {
//	$unique_name = 'm' . $product_id . 'dm';
//	$unique_name = apply_filters( 'tajer_generate_unique_name', $unique_name, $product_id );
//
//	return $unique_name;
//}

function tajer_crypto_rand_secure( $min, $max ) {
	$range = $max - $min;
	if ( $range < 0 ) {
		return $min;
	} // not so random...
	$log    = log( $range, 2 );
	$bytes  = (int) ( $log / 8 ) + 1; // length in bytes
	$bits   = (int) $log + 1; // length in bits
	$filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1
	do {
		$rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
		$rnd = $rnd & $filter; // discard irrelevant bits
	} while ( $rnd >= $range );

	return $min + $rnd;
}

//http://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string
function tajer_get_token( $length ) {
	$token        = "";
	$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
	$codeAlphabet .= "0123456789";
	for ( $i = 0; $i < $length; $i ++ ) {
		$token .= $codeAlphabet[ tajer_crypto_rand_secure( 0, strlen( $codeAlphabet ) ) ];
	}

	$token = apply_filters( 'tajer_get_token', $token, $length );

	return $token;
}

function tajer_get_user_roles() {
	global $wp_roles;

	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	$roles = $wp_roles->get_names();

	$roles = apply_filters( 'tajer_get_user_roles', $roles );

	return $roles;
}

function tajer_get_products() {
	$products = array();
	$args     = array( 'post_type' => 'tajer_products', 'posts_per_page' => - 1, 'post_status' => 'any' );

	$args = apply_filters( 'tajer_get_products_args', $args );

	$products_post_type = get_posts( $args );
	foreach ( $products_post_type as $product_post ) {
		setup_postdata( $product_post );
		$products[ $product_post->ID ] = $product_post->post_title;
		wp_reset_postdata();
	}
	$products['all'] = 'All';

	$products = apply_filters( 'tajer_get_products', $products );

	return $products;
}

function tajer_insert_user_product( array $opts ) {
	global $tajer_inserted_user_product_result;
	$customer_details = tajer_customer_details();

	$default_opts = array(
		'user_id'             => $customer_details->id,
		'buying_date'         => date( 'Y-m-d H:i:s' ),
		'expiration_date'     => date( 'Y-m-d H:i:s', strtotime( '+1 years' ) ),
		'order_id'            => 0,
		'product_id'          => 0,
		'product_sub_id'      => 0,
		'number_of_downloads' => 0,
		'status'              => 'active',
		'activation_method'   => 'buy'
	);

	$opts = array_merge( $default_opts, $opts );

	$opts = apply_filters( 'tajer_new_user_product_args', $opts );

	$result = Tajer_DB::insert_user_product( $opts );

	do_action( 'tajer_user_product_created', $result, $opts );

	$result = apply_filters( 'tajer_new_user_product', $result, $opts );

	$tajer_inserted_user_product_result = $result;

//	do_action( 'tajer_new_user_product_id', $result['id'], $opts );

	return $result;
}

function tajer_delete_user_product( $user_product_id ) {
	$is_deleted = Tajer_DB::delete_user_product( $user_product_id );

	return apply_filters( 'tajer_delete_user_product', $is_deleted, $user_product_id );
}

function tajer_delete_user_products_in( $user_product_ids ) {
	$result = Tajer_DB::delete_user_products_in( $user_product_ids );

	return apply_filters( 'tajer_delete_user_products_in', $result, $user_product_ids );
}

function tajer_get_product_price( $product_id, $product_sub_id = 0, $format_currency = false, $with_symbol = false ) {

	$currency = new Tajer_Currency();

	if ( $product_sub_id == 0 ) {
		if ( $format_currency ) {
			if ( $with_symbol ) {
				$price = tajer_number_to_currency( (float) get_post_meta( $product_id, 'tajer_price', true ), true );
			} else {
				$price = tajer_number_to_currency( (float) get_post_meta( $product_id, 'tajer_price', true ) );
			}
		} else {
			if ( $with_symbol ) {
				$currency_symbols  = $currency->currency_symbols_as_HTML_entities();
				$currency_position = tajer_get_option( 'currency_position', 'tajer_general_settings', '' );
				$currency_code     = tajer_get_option( 'currency', 'tajer_general_settings', '' );

				if ( $currency_position == 'after' ) {
					$price = get_post_meta( $product_id, 'tajer_price', true ) . $currency_symbols[ $currency_code ];
				} else {
					$price = $currency_symbols[ $currency_code ] . get_post_meta( $product_id, 'tajer_price', true );
				}

			} else {
				$price = (float) get_post_meta( $product_id, 'tajer_price', true );
			}
		}
	} else {
		$prices = get_post_meta( $product_id, 'tajer_product_prices', true );
		if ( $format_currency ) {
			if ( $with_symbol ) {
				$price = tajer_number_to_currency( (float) $prices[ $product_sub_id ]['price'], true );
			} else {
				$price = tajer_number_to_currency( (float) $prices[ $product_sub_id ]['price'] );
			}

		} else {
			if ( $with_symbol ) {
				$currency_symbols  = $currency->currency_symbols_as_HTML_entities();
				$currency_position = tajer_get_option( 'currency_position', 'tajer_general_settings', '' );
				$currency_code     = tajer_get_option( 'currency', 'tajer_general_settings', '' );

				if ( $currency_position == 'after' ) {
					$price = $prices[ $product_sub_id ]['price'] . $currency_symbols[ $currency_code ];
				} else {
					$price = $currency_symbols[ $currency_code ] . $prices[ $product_sub_id ]['price'];
				}

			} else {
				$price = (float) $prices[ $product_sub_id ]['price'];
			}
		}
	}

	$price = apply_filters( 'tajer_get_product_price', $price, $product_id, $product_sub_id, $format_currency, $with_symbol );

	return $price;
}

//function tajer_product_price_with_currency_symbol( $product_id, $product_sub_id ) {
//	$currency          = new Tajer_Currency();
//	$price             = tajer_get_product_price( $product_id, $product_sub_id, true );
//	$currency_position = tajer_get_option( 'currency_position', 'tajer_general_settings', '' );
//	$currency_code     = tajer_get_option( 'currency', 'tajer_general_settings', '' );
//	$currency_symbols  = $currency->currency_symbols_as_HTML_entities();
//	if ( $currency_position == 'after' ) {
//		return $price . $currency_symbols[ $currency_code ];
//	} else {
//		return $currency_symbols[ $currency_code ] . $price;
//	}
//}

function tajer_countries() {
	$countries = array(
		''   => '',
		'US' => 'United States',
		'CA' => 'Canada',
		'GB' => 'United Kingdom',
		'AF' => 'Afghanistan',
		'AX' => '&#197;land Islands',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BQ' => 'Bonaire, Saint Eustatius and Saba',
		'BA' => 'Bosnia and Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'BN' => 'Brunei Darrussalam',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CD' => 'Congo, Democratic People\'s Republic',
		'CG' => 'Congo, Republic of',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'CI' => 'Cote d\'Ivoire',
		'HR' => 'Croatia/Hrvatska',
		'CU' => 'Cuba',
		'CW' => 'Cura&Ccedil;ao',
		'CY' => 'Cyprus Island',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'TP' => 'East Timor',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'GQ' => 'Equatorial Guinea',
		'SV' => 'El Salvador',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GR' => 'Greece',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard and McDonald Islands',
		'VA' => 'Holy See (City Vatican State)',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Lao People\'s Democratic Republic',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libyan Arab Jamahiriya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macau',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia',
		'MD' => 'Moldova, Republic of',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'AN' => 'Netherlands Antilles',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'KR' => 'North Korea',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territories',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Phillipines',
		'PN' => 'Pitcairn Island',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'RE' => 'Reunion Island',
		'RO' => 'Romania',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',
		'BL' => 'Saint Barth&eacute;lemy',
		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts and Nevis',
		'LC' => 'Saint Lucia',
		'MF' => 'Saint Martin (French)',
		'SX' => 'Saint Martin (Dutch)',
		'PM' => 'Saint Pierre and Miquelon',
		'VC' => 'Saint Vincent and the Grenadines',
		'SM' => 'San Marino',
		'ST' => 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SK' => 'Slovak Republic',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia',
		'KP' => 'South Korea',
		'SS' => 'South Sudan',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard and Jan Mayen Islands',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syrian Arab Republic',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TL' => 'Timor-Leste',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad and Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks and Caicos Islands',
		'TV' => 'Tuvalu',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'UY' => 'Uruguay',
		'UM' => 'US Minor Outlying Islands',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'VG' => 'Virgin Islands (British)',
		'VI' => 'Virgin Islands (USA)',
		'WF' => 'Wallis and Futuna Islands',
		'EH' => 'Western Sahara',
		'WS' => 'Western Samoa',
		'YE' => 'Yemen',
		'YU' => 'Yugoslavia',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe'
	);

	$countries = apply_filters( 'tajer_countries', $countries );

	return $countries;
}

function tajer_update_order_meta( $order_id, $meta_key, $meta_value ) {
	do_action( 'before_update_order_meta', $order_id, $meta_key, $meta_value );
	$result = Tajer_DB::update_meta( 'order', $order_id, $meta_key, $meta_value );
	$result = apply_filters( 'tajer_update_order_meta', $result, $order_id, $meta_key, $meta_value );

	return $result;
}

function tajer_update_statistics_meta( $statistics_id, $meta_key, $meta_value ) {
	do_action( 'before_update_statistics_meta', $statistics_id, $meta_key, $meta_value );
	$result = Tajer_DB::update_meta( 'statistics', $statistics_id, $meta_key, $meta_value );
	$result = apply_filters( 'update_statistics_meta', $result, $statistics_id, $meta_key, $meta_value );

	return $result;
}

function tajer_update_user_product_meta( $user_product_id, $meta_key, $meta_value ) {
	do_action( 'before_update_user_product_meta', $user_product_id, $meta_key, $meta_value );
	$result = Tajer_DB::update_meta( 'user_products', $user_product_id, $meta_key, $meta_value );
	$result = apply_filters( 'update_user_product_meta', $result, $user_product_id, $meta_key, $meta_value );

	return $result;
}

function tajer_delete_order_meta( $order_id, $meta_key ) {
	do_action( 'before_delete_order_meta', $order_id, $meta_key );
	$result = Tajer_DB::delete_meta( 'tajer_order_meta', $order_id, 'order_id', $meta_key );
	$result = apply_filters( 'delete_order_meta', $result, $order_id, $meta_key );

	return $result;
}

function tajer_delete_statistics_meta( $statistics_id, $meta_key ) {
	do_action( 'before_delete_statistics_meta', $statistics_id, $meta_key );
	$result = Tajer_DB::delete_meta( 'tajer_statistic_meta', $statistics_id, 'statistics_id', $meta_key );
	$result = apply_filters( 'delete_statistics_meta', $result, $statistics_id, $meta_key );

	return $result;
}

function tajer_delete_user_product_meta( $user_product_id, $meta_key ) {
	do_action( 'before_delete_user_product_meta', $user_product_id, $meta_key );
	$result = Tajer_DB::delete_meta( 'tajer_user_product_meta', $user_product_id, 'user_product_id', $meta_key );
	$result = apply_filters( 'delete_user_product_meta', $result, $user_product_id, $meta_key );

	return $result;
}

function tajer_get_user_product_meta( $user_product_id, $meta_key ) {
	do_action( 'tajer_before_get_user_product_meta', $user_product_id, $meta_key );
	$result = Tajer_DB::get_meta( 'tajer_user_product_meta', $user_product_id, 'user_product_id', $meta_key );
	$result = apply_filters( 'tajer_get_user_product_meta', $result, $user_product_id, $meta_key );

	return $result;
}

function tajer_get_statistics_meta( $statistics_id, $meta_key ) {
	do_action( 'tajer_before_get_statistics_meta', $statistics_id, $meta_key );
	$result = Tajer_DB::get_meta( 'tajer_statistic_meta', $statistics_id, 'statistics_id', $meta_key );
	$result = apply_filters( 'tajer_get_statistics_meta', $result, $statistics_id, $meta_key );

	return $result;
}

function tajer_get_order_meta( $order_id, $meta_key ) {
	do_action( 'tajer_before_get_order_meta', $order_id, $meta_key );
	$result = Tajer_DB::get_meta( 'tajer_order_meta', $order_id, 'order_id', $meta_key );
	$result = apply_filters( 'tajer_get_order_meta', $result, $order_id, $meta_key );

	return $result;
}

function tajer_get_php_arg_separator_output() {
	return ini_get( 'arg_separator.output' );
}

function tajer_record_gateway_error( $order_id, $title, $message ) {
	//First get current errors array
	$gateway_errors = tajer_get_order_meta( $order_id, 'gateway_errors' );

	if ( ( $gateway_errors !== null ) && is_array( $gateway_errors ) ) {
		$gateway_errors   = unserialize( $gateway_errors );
		$gateway_errors[] = array(
			'title'   => $title,
			'message' => $message
		);
	} else {
		$gateway_errors   = array();
		$gateway_errors[] = array(
			'title'   => $title,
			'message' => $message
		);
	}

	//update the errors array
	tajer_update_order_meta( $order_id, 'gateway_errors', serialize( $gateway_errors ) );
}

function tajer_insert_download( array $opts ) {

	$default_opts = array(
		'user_product'   => 0,
		'product_id'     => 0,
		'product_sub_id' => 0,
		'file_id'        => 0,
		'user_id'        => get_current_user_id(),
		'date'           => date( 'Y-m-d H:i:s' ),
		'ip'             => sanitize_text_field( $_SERVER["REMOTE_ADDR"] )
	);

	$opts = array_merge( $default_opts, $opts );

	$opts = apply_filters( 'tajer_new_download_args', $opts );

	$result = Tajer_DB::insert_download( $opts );

	do_action( 'tajer_download_created', $result, $opts );

	$result = apply_filters( 'tajer_new_download', $result, $opts );

	return $result;
}

function tajer_record_recurring_error( $order_id, $title, $message ) {
	//First get current errors array
	$gateway_errors = tajer_get_order_meta( $order_id, 'recurring_errors' );

	if ( ( $gateway_errors !== null ) && is_array( $gateway_errors ) ) {
		$gateway_errors[] = array(
			'title'   => $title,
			'message' => $message
		);
	} else {
		$gateway_errors   = array();
		$gateway_errors[] = array(
			'title'   => $title,
			'message' => $message
		);
	}

	//update the errors array
	tajer_update_order_meta( $order_id, 'gateway_errors', $gateway_errors );
}

function tajer_number_to_currency( $number, $with_symbol = false, $number_wrapper = false ) {


	$currency      = new Tajer_Currency();
	$currency_code = tajer_get_option( 'currency', 'tajer_general_settings', '' );

	$with_format = $currency->format_currency( $number, $currency_code );

	if ( $with_symbol ) {
		$currency_symbols  = $currency->currency_symbols_as_HTML_entities();
		$currency_position = tajer_get_option( 'currency_position', 'tajer_general_settings', '' );

		if ( $currency_position == 'after' ) {
			if ( $number_wrapper ) {
				$returned_value = '<' . $number_wrapper . '>' . $with_format . '</' . $number_wrapper . '>' . $currency_symbols[ $currency_code ];
			} else {
				$returned_value = $with_format . $currency_symbols[ $currency_code ];
			}
		} else {
			if ( $number_wrapper ) {
				$returned_value = $currency_symbols[ $currency_code ] . '<' . $number_wrapper . '>' . $with_format . '</' . $number_wrapper . '>';
			} else {
				$returned_value = $currency_symbols[ $currency_code ] . $with_format;
			}

		}

	} else {
		$returned_value = $with_format;
	}

	$returned_value = apply_filters( 'tajer_number_to_currency', $returned_value, $number, $with_symbol, $number_wrapper );

	return $returned_value;

}

function tajer_apply_coupon( $price, $coupon, $product_id, $product_sub_id = 0 ) {
	//Get coupon details
	//get the post from the meta key
	$args = array(
		'post_type'  => 'tajer_coupons',
		'meta_query' => array(
			array(
				'key'     => 'tajer_coupon_code',
				'value'   => $coupon,
				'compare' => '='
			)
		)
	);

	$posts   = get_posts( $args );
	$post_id = $posts[0]->ID;
	$coupon  = get_post_meta( $post_id, 'tajer_coupon', true );
	wp_reset_postdata();

	//get how many times this coupon used
	$times = Tajer_DB::count_coupon_used( $coupon );

	$minimum_purchase = (float) $coupon['tajer_minimum_purchase'];
	$user_total       = $price;

	$products = $coupon['tajer_products'];

	//check if the admin apply the coupon on all products
	$all = false;
	foreach ( $products as $product ) {
		if ( $product == 'all' ) {
			$all = true;
		}
		continue;
	}

	$savings_type = $coupon['tajer_savings_type'];
	$savings      = (float) $coupon['tajer_savings'];

	if ( ( empty( $posts ) ) ) {
		$status  = 'error';
		$message = __( 'This discount is invalid.', 'tajer' );

		$returned_value = array( $status, $message, $price );
	} elseif ( date( 'Y-m-d H:i:s' ) < $coupon['tajer_start_date'] ) {
		$status  = 'error';
		$message = __( 'This discount is not effective yet.', 'tajer' );

		$returned_value = array( $status, $message, $price );
	} elseif ( date( 'Y-m-d H:i:s' ) > $coupon['tajer_expiration_date'] ) {
		$status  = 'error';
		$message = __( 'This discount is expired.', 'tajer' );

		$returned_value = array( $status, $message, $price );
	} elseif ( $coupon['tajer_status'] == 'inactive' ) {
		$status  = 'error';
		$message = __( 'This discount is inactive.', 'tajer' );

		$returned_value = array( $status, $message, $price );
	} elseif ( ( $times >= ( (int) $coupon['tajer_max_users'] ) ) && ( ! empty( $coupon['tajer_max_users'] ) ) ) {
		$status  = 'error';
		$message = __( 'This discount has reached the maximum number of coupons.', 'tajer' );

		$returned_value = array( $status, $message, $price );
	} elseif ( ( $minimum_purchase != 0 ) && ( $user_total < $minimum_purchase ) ) {
		$status  = 'error';
		$message = __( 'Sorry, this coupon is only available for purchases over ', 'tajer' ) . tajer_number_to_currency( $minimum_purchase, true );

		$returned_value = array( $status, $message, $price );
	} elseif ( $all ) {
		if ( $savings_type == 'amount' ) {
			$user_total = $user_total - $savings;
		} else {
			$user_total = $user_total - ( $user_total * ( $savings / 100 ) );
		}
		$status  = 'success';
		$message = '';

		$returned_value = array( $status, $message, $user_total );
	} else {
		//In case the admin doesn't apply the coupon on all products
		foreach ( $products as $product ) {
			if ( $product_id == (int) $product ) {
				if ( $savings_type == 'amount' ) {
					$user_total = $user_total - $savings;
				} else {
					$user_total = ( $user_total - ( $user_total * ( $savings / 100 ) ) );
				}
			}
		}
		$status  = 'success';
		$message = '';

		$returned_value = array( $status, $message, $user_total );
	}

	$returned_value = apply_filters( 'tajer_apply_coupon', $returned_value, $price, $coupon, $product_id, $product_sub_id );

	return $returned_value;
}

function tajer_insert_statistics( array $opts ) {

	$default_opts = array(
		'user_id'        => get_current_user_id(),
		'buying_date'    => date( 'Y-m-d H:i:s' ),
		'author_id'      => 0,
		'earnings'       => 0,
		'product_id'     => 0,
		'product_sub_id' => 0,
		'status'         => '',
		'quantity'       => 1
	);

	$opts = array_merge( $default_opts, $opts );

	$opts = apply_filters( 'tajer_insert_statistics_args', $opts );

	$result = Tajer_DB::insert_statistics( $opts );

	do_action( 'tajer_statistics_inserted', $result, $opts );

	$result = apply_filters( 'tajer_insert_statistics', $result, $opts );

	return $result;
}

/**
 * @param $product_id
 * @param $product_sub_id int|array
 *
 * @return array|bool|mixed|null|object|void
 */
function tajer_is_in_cart( $product_id, $product_sub_id ) {

	$item = Tajer_DB::is_in_cart( $product_id, $product_sub_id );

	if ( $item !== null ) {
		$returned_value = $item;
	} else {
		$returned_value = false;
	}

	$returned_value = apply_filters( 'tajer_is_in_cart', $returned_value, $product_id, $product_sub_id );

	return $returned_value;
}

function tajer_has_product( $order_id, $product_id, $product_sub_id ) {

	$item = Tajer_DB::has_user_product( $order_id, $product_id, $product_sub_id );

	if ( $item !== null ) {
		$returned_value = true;
	} else {
		$returned_value = false;
	}

	$returned_value = apply_filters( 'tajer_has_product', $returned_value, $order_id, $product_id, $product_sub_id );

	return $returned_value;
}

function Tajer_CanAccess( $product_id, $product_sub_id ) {

	$prices      = get_post_meta( $product_id, 'tajer_product_prices', true );
	$roles       = $prices[ $product_sub_id ]['roles'];
	$roles_array = explode( ',', $roles );

//	$roles = get_post_meta( $product_id, 'tajer_roles', true );
	$user = wp_get_current_user();

	$canAccess = false;
	foreach ( $roles_array as $role ) {
		if ( in_array( $role, $user->roles ) ) {
			$canAccess = true;
		}
	}

	$canAccess = apply_filters( 'Tajer_CanAccess', $canAccess, $product_id, $product_sub_id );

	return $canAccess;
}

function tajer_get_download_limits( $product_id, $product_sub_id ) {
	$prices         = get_post_meta( $product_id, 'tajer_product_prices', true );
	$download_limit = $prices[ $product_sub_id ]['file_download_limit'];

	if ( empty( $download_limit ) ) {
		$limits = 0;
	} else {
		$limits = (int) $download_limit;
	}

	$limits = apply_filters( 'tajer_get_download_limits', $limits, $product_id, $product_sub_id );

	return $limits;
}

function tajer_can_download( $user_product ) {

	$download_limits = tajer_get_download_limits( $user_product->product_id, $user_product->product_sub_id );

	if ( $user_product->number_of_downloads > $download_limits ) {
		$returned_value = false;
	} else {
		$returned_value = true;
	}

	$returned_value = apply_filters( 'tajer_can_download', $returned_value, $user_product, $download_limits );

	return $returned_value;
}

/**
 * Retrieve price option value.
 *
 *
 * @param $product_id
 * @param $product_sub_id
 * @param string $option some of the available options:file_download_limit, name, price, roles, download_link_expiration, capabilities
 *
 * @return mixed|void
 */
function tajer_get_price_option( $product_id, $product_sub_id, $option ) {
	$prices       = get_post_meta( $product_id, 'tajer_product_prices', true );
	$price_option = $prices[ $product_sub_id ][ $option ];


	$price_option = apply_filters( 'tajer_get_price_option', $price_option, $product_id, $product_sub_id, $option );

	return $price_option;
}

function tajer_get_pagination_links( $pagination ) {
	$args = array(
		'base'      => add_query_arg( 'paged', '%#%' ),
		'format'    => '',
		'prev_text' => '&laquo;',
		'next_text' => '&raquo;',
		'total'     => $pagination->total_pages(),
		'current'   => $pagination->current_page
	);

	$args = apply_filters( 'tajer_get_pagination_links_args', $args, $pagination );

	$page_links = paginate_links( $args );

	$page_links = apply_filters( 'tajer_get_pagination_links', $page_links, $args, $pagination );

	return $page_links;
}

/**
 * Get product expiration date
 *
 * @param $product_id
 * @param $product_sub_id
 * @param string $custom_date if it empty then the expiration date will be calculated from the current date if not the expiration
 *        date will be calculated from $custom_date
 *
 * @return bool|mixed|string|void
 */
function tajer_expiration_date( $product_id, $product_sub_id, $custom_date = '' ) {
	$prices = get_post_meta( $product_id, 'tajer_product_prices', true );
	$days   = $prices[ $product_sub_id ]['price_expiration'];

	if ( empty( $custom_date ) ) {
		$date = new DateTime();//Get current date object
	} else {
		$date = new DateTime( $custom_date );//Get custom date object
	}

	if ( empty( $days ) ) {
		$date->modify( '+75 years' );//Add 75 year to it
	} else {
		$date->modify( "+" . $days . " days" );//add $days to it
	}

	$expiration_date = $date->format( 'Y-m-d H:i:s' );//Format the returned value
	$expiration_date = apply_filters( 'tajer_expiration_date', $expiration_date, $product_id, $product_sub_id, $custom_date );

	return $expiration_date;
}

function tajer_get_purchase_link( $product_id ) {
	$lowest = tajer_get_lowest_price_with_id( $product_id );

	$recurring_url = add_query_arg( apply_filters( 'tajer_purchase_link_query_args', array(
		'tajer_action'   => 'buy_now',
		'product_id'     => $product_id,
		'product_sub_id' => $lowest['id']
	), $product_id ), get_permalink( intval( tajer_get_option( 'cart', 'tajer_general_settings', '' ) ) ) );

	$html = '<a href="' . esc_url( wp_nonce_url( $recurring_url, 'tajer_download' ) ) . '" target="_blank" class="tajer_grid_purchase_link"><i class="angle double right icon"></i>' . __( 'Buy Now', 'tajer' ) . '</a>';

	$html = apply_filters( 'tajer_get_purchase_link', $html, $product_id );

	return $html;
}

function tajer_customer_details( $user_id = 0 ) {//todo Mohammed test check this function

	if ( $user_id ) {
		$user = get_user_by( 'id', $user_id );//todo Mohammed urgent is this will return everything below
	} else {
		$current_user = wp_get_current_user();
		$user         = $current_user;
	}


	$details             = new  stdClass();
	$details->id         = $user->ID;
	$details->email      = $user->user_email;
	$details->user_login = $user->user_login;

	$details->first_name   = $user->user_firstname;
	$details->last_name    = $user->user_lastname;
	$details->display_name = $user->display_name;

	$user_meta = get_user_meta( $details->id );//todo Mohammed urgent is this will return everything below

	if ( ! empty( $user_meta ) ) {
		$company_meta_key   = tajer_get_option( 'company_meta_key', 'tajer_general_settings', '' );
		$company            = isset( $user_meta[ $company_meta_key ] ) ? reset( $user_meta[ $company_meta_key ] ) : '';
		$phone_meta_key     = tajer_get_option( 'phone_meta_key', 'tajer_general_settings', '' );
		$phone              = isset( $user_meta[ $phone_meta_key ] ) ? reset( $user_meta[ $phone_meta_key ] ) : '';
		$country_meta_key   = tajer_get_option( 'country_meta_key', 'tajer_general_settings', '' );
		$country            = isset( $user_meta[ $country_meta_key ] ) ? reset( $user_meta[ $country_meta_key ] ) : '';
		$address_1_meta_key = tajer_get_option( 'address_1_meta_key', 'tajer_general_settings', '' );
		$address_1          = isset( $user_meta[ $address_1_meta_key ] ) ? reset( $user_meta[ $address_1_meta_key ] ) : '';
		$address_2_meta_key = tajer_get_option( 'address_2_meta_key', 'tajer_general_settings', '' );
		$address_2          = isset( $user_meta[ $address_2_meta_key ] ) ? reset( $user_meta[ $address_2_meta_key ] ) : '';
		$city_meta_key      = tajer_get_option( 'city_meta_key', 'tajer_general_settings', '' );
		$city               = isset( $user_meta[ $city_meta_key ] ) ? reset( $user_meta[ $city_meta_key ] ) : '';
		$state_meta_key     = tajer_get_option( 'state_meta_key', 'tajer_general_settings', '' );
		$state              = isset( $user_meta[ $state_meta_key ] ) ? reset( $user_meta[ $state_meta_key ] ) : '';
		$postcode_meta_key  = tajer_get_option( 'postcode_meta_key', 'tajer_general_settings', '' );
		$postcode           = isset( $user_meta[ $postcode_meta_key ] ) ? reset( $user_meta[ $postcode_meta_key ] ) : '';

		$details->company   = $company ? $company : '';
		$details->phone     = $phone ? $phone : '';
		$details->country   = $country ? $country : '';
		$details->address_1 = $address_1 ? $address_1 : '';
		$details->address_2 = $address_2 ? $address_2 : '';
		$details->city      = $city ? $city : '';
		$details->state     = $state ? $state : '';
		$details->postcode  = $postcode ? $postcode : '';
	}


	$details = apply_filters( 'tajer_customer_details', $details );

	return $details;
}

function tajer_get_customer_address( $user_id ) {//todo Mohammed test check this function

	$user = tajer_customer_details( $user_id );

	$first_name = $user->first_name;
	$last_name  = $user->last_name;
	$company    = $user->company;
	$address_1  = $user->address_1;
	$address_2  = $user->address_2;
	$city       = $user->city;
	$state      = $user->state;
	$postcode   = $user->postcode;
	$country    = $user->country;

	$html = ( ! empty( $first_name ) && ! empty( $last_name ) ) ? $first_name . ' ' . $last_name . '<br/>' : '';
	$html .= ( ! empty( $company ) ) ? $company . '<br/>' : '';
	$html .= ( ! empty( $address_1 ) ) ? $address_1 . '<br/>' : '';
	$html .= ( ! empty( $address_2 ) ) ? $address_2 . '<br/>' : '';
	$html .= ( ! empty( $city ) ) ? $city . '<br/>' : '';
	$html .= ( ! empty( $state ) ) ? $state . '<br/>' : '';
	$html .= ( ! empty( $postcode ) ) ? $postcode . '<br/>' : '';
	$html .= ( ! empty( $country ) ) ? $country . '<br/>' : '';

	return $html;
}

//function tajer_wp_pagination() {
//
//	global $wp_query;
//
//	$big = 999999999; // need an unlikely integer
//
//	$pages = paginate_links( array(
//		'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
//		'format' => '?paged=%#%',
//		'current' => max( 1, get_query_var('paged') ),
//		'total' => $wp_query->max_num_pages,
//		'type'  => 'array',
//	) );
//	if( is_array( $pages ) ) {
//		$paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');
//		echo '<div class="pagination-wrap"><ul class="pagination">';
//		foreach ( $pages as $page ) {
//			echo "<li>$page</li>";
//		}
//		echo '</ul></div>';
//	}
//}

function tajer_is_legal_product( $price_assignment, $product_sub_id ) {
	$is_legal_product = false;
	foreach ( $price_assignment as $price ) {
		if ( $product_sub_id == (int) $price ) {
			$is_legal_product = true;
		}
	}

	$is_legal_product = apply_filters( 'tajer_is_legal_product', $is_legal_product, $price_assignment, $product_sub_id );

	return $is_legal_product;
}

function tajer_get_lowest_price_with_id( $product_id ) {

	$prices = get_post_meta( $product_id, 'tajer_product_prices', true );

	$first_price = reset( $prices );
	$price       = (float) $first_price['price'];
	$id          = key( $prices );

	foreach ( $prices as $the_id => $one_item ) {
		if ( (float) $one_item['price'] < $price ) {
			$price = (float) $one_item['price'];
			$id    = $the_id;
		}
	}

	$returned_value = array( 'price' => $price, 'id' => $id );
	$returned_value = apply_filters( 'tajer_get_lowest_price_with_id', $returned_value, $product_id );

	return $returned_value;
}

function tajer_is_product_exist( $product_id, $product_sub_id ) {

	$returned_value = false;

	//check first if the main product exist
	if ( ! is_string( get_post_status( $product_id ) ) ) {
		$returned_value = false;
	} else {
		//now check the sub product
		$prices = get_post_meta( $product_id, 'tajer_product_prices', true );
		foreach ( $prices as $price_id => $price_detail ) {
			if ( $price_id == $product_sub_id ) {
				$returned_value = true;
			}
		}
	}

	$returned_value = apply_filters( 'tajer_is_product_exist', $returned_value, $product_id, $product_sub_id );

	return $returned_value;
}

function add_remove_product_with_trial( $cart_id, $product_id, $product_sub_id ) {
	$trial        = get_post_meta( (int) $product_id, 'tajer_trial', true );
	$trial_period = $trial[ $product_sub_id ]['trial_period'];
	if ( tajer_is_direct_trial( $product_id, $product_sub_id ) ) {
		$returned_value = TajeraddUserTrial( $product_id, $product_sub_id, $trial_period, $cart_id );
	} else {
		$result         = tajer_add_remove_from_cart( $product_id, $product_sub_id );
		$returned_value = array( $result->message, $result->status, $result->row_id );
	}

	$returned_value = apply_filters( 'add_remove_product_with_trial', $returned_value, $cart_id, $product_id, $product_sub_id );

	return $returned_value;
}

function tajer_get_trial_period( $product_id, $product_sub_id ) {
	$trial           = get_post_meta( (int) $product_id, 'tajer_trial', true );
	$is_direct_trial = $trial[ $product_sub_id ]['direct_trial'];
	$period          = $trial[ $product_sub_id ]['trial_period'];


	$result                  = new stdClass();
	$result->is_direct_trial = $is_direct_trial;
	$result->period          = $period;

	return $result;
}

function add_product_with_trial( $cart_id, $product_id, $product_sub_id ) {
	$trial        = get_post_meta( (int) $product_id, 'tajer_trial', true );
	$trial_period = $trial[ $product_sub_id ]['trial_period'];
	if ( tajer_is_direct_trial( $product_id, $product_sub_id ) ) {
		$returned_value = TajeraddUserTrial( $product_id, $product_sub_id, $trial_period, $cart_id, 'add' );
	} else {
		$returned_value = tajer_add_to_cart( $product_id, $product_sub_id );
	}

	$returned_value = apply_filters( 'add_product_with_trial', $returned_value, $cart_id, $product_id, $product_sub_id );

	return $returned_value;
}

function tajer_add_to_cart( $product_id, $product_sub_id, $user_id = null, $date = null, $quantity = 1 ) {

	$result          = new stdClass();
	$result->message = '';
	$result->status  = '';
	$result->row_id  = 0;

	if ( is_user_logged_in() || ! is_null( $user_id ) ) {

		do_action( 'tajer_before_add_to_cart', $product_id, $product_sub_id );

		$returned_value = Tajer_DB::add_to_cart( $product_id, $product_sub_id, $user_id, $date, $quantity );

		do_action( 'tajer_after_add_to_cart', $product_id, $product_sub_id, $returned_value );

		$returned_value = apply_filters( 'tajer_add_to_cart_login_result', $returned_value, $product_id, $product_sub_id );

		list( $message, $status, $row_id ) = $returned_value;

		$result->message = $message;
		$result->status  = $status;
		$result->row_id  = $row_id;
	} else {
		$result->message = __( 'Please login first!' );
		$result->status  = 'login_error';
	}

	$result = apply_filters( 'tajer_add_to_cart_result', $result, $product_id, $product_sub_id );

	return $result;
}

function tajer_remove_from_cart( $item_id ) {
	do_action( 'tajer_before_remove_from_cart', $item_id );

	$result          = new stdClass();
	$result->deleted = false;
	$result->status  = '';
	$result->message = '';
	$result->row_id  = $item_id;

	if ( is_user_logged_in() ) {

		$is_deleted = Tajer_DB::delete_cart_item( $item_id );

		if ( $is_deleted !== false ) {
			$result->deleted = true;
			$result->message = __( 'Product Removed From Cart!', 'tajer' );
			$result->status  = 'remove';
		} else {
			$result->message = __( 'Cant Remove The Product!', 'tajer' );
			$result->status  = 'error';
		}
	} else {
		$result->status = 'login_error';
	}

	do_action( 'tajer_after_remove_from_cart', $result, $item_id );

	$result = apply_filters( 'tajer_remove_from_cart_result', $result, $item_id );

	return $result;
}

function tajer_add_remove_from_cart( $product_id, $product_sub_id ) {
	do_action( 'tajer_before_add_remove_from_cart', $product_id, $product_sub_id );

	$result = tajer_add_to_cart( $product_id, $product_sub_id );

	if ( $result->status == 'exist' ) {
		$result = tajer_remove_from_cart( $result->row_id );
	}

	do_action( 'tajer_aftre_add_remove_from_cart', $result, $product_id, $product_sub_id );

	$result = apply_filters( 'tajer_add_remove_from_cart_result', $result, $product_id, $product_sub_id );

	return $result;
}

function TajeraddUserTrial( $product_id, $product_sub_id, $period, $cart_id, $type = 'add_remove' ) {

	//check if the user used this trial before if he/she used it before then add it to his/her cart
	if ( tajer_is_trial_possible( $period, $product_id, $product_sub_id ) ) {
		//record this user used this trial
		$is_add_trial_record = tajer_insert_trial_record( $product_id, $product_sub_id );
		if ( ! $is_add_trial_record['is_insert'] ) {
			$message = __( 'Something wrong happen please contact the website admin', 'tajer' );
			$status  = 'error';

			$returned_value = array( $message, $status, 'error' );
		} elseif ( tajer_is_bundle( $product_id ) ) {
			tajer_handle_bundle_product( $product_id, $product_sub_id, 'TajerinsertUserProductForTrialPurpose', array( $period ) );

			$message        = __( 'The trial version of this product has been activated for you!', 'tajer' );
			$status         = 'add';
			$returned_value = array( $message, $status, 1 );
		} else {
			TajerinsertUserProductForTrialPurpose( $product_id, $product_sub_id, $period );
			$message = __( 'The trial version of this product has been activated for you!', 'tajer' );
			$status  = 'add';

			$returned_value = array( $message, $status, 1 );
		}
	} else {
		if ( $type == 'add_remove' ) {
			$result         = tajer_add_remove_from_cart( $product_id, $product_sub_id );
			$returned_value = array( $result->message, $result->status, $result->row_id );
		} else {
			$result         = tajer_add_to_cart( $product_id, $product_sub_id );
			$returned_value = array( $result->message, $result->status, $result->row_id );
		}
	}

	$returned_value = apply_filters( 'TajeraddUserTrial', $returned_value, $product_id, $product_sub_id, $period, $cart_id );

	return $returned_value;
}

function tajer_taxValue() {
	$tax_rate_value = 0;
	$is_tax_enabled = tajer_get_option( 'enable_taxes', 'tajer_tax_settings', '' );
	//if the tax is enabled
	if ( $is_tax_enabled == 'yes' ) {
		$user_country = get_user_meta( get_current_user_id(), tajer_get_option( 'country_meta_key', 'tajer_general_settings', '' ), true );
		$user_state   = get_user_meta( get_current_user_id(), tajer_get_option( 'state_meta_key', 'tajer_general_settings', '' ), true );

		//check if there is an error
		if ( ( ! is_array( $user_country ) && ( ! empty( $user_country ) ) ) ) {
			//now I want to check if the admin specify the tax to all of the country states
			$tax_rates = tajer_get_option( 'tax_rates', 'tajer_tax_settings', '' );
			if ( ! empty( $tax_rates ) ) {
				foreach ( $tax_rates as $tax_rate ) {
					if ( $tax_rate['country'] == $user_country ) {
						if ( isset( $tax_rate['global'] ) && ( $tax_rate['global'] == 'yes' ) ) {
							$tax_rate_value = (float) $tax_rate['rate'];
						} elseif ( ( ! is_array( $user_state ) && ( ! empty( $user_state ) ) ) && ( $user_state == $tax_rate['state'] ) ) {
							$tax_rate_value = (float) $tax_rate['rate'];
						} else {
							$tax_rate_value = (float) tajer_get_option( 'fallback_tax_rate', 'tajer_tax_settings', '' );
						}
					}
				}
			} else {
				$tax_rate_value = (float) tajer_get_option( 'fallback_tax_rate', 'tajer_tax_settings', '' );
			}
		} else {
			$tax_rate_value = (float) tajer_get_option( 'fallback_tax_rate', 'tajer_tax_settings', '' );
		}
	}
	$prices_include_tax = tajer_get_option( 'prices_include_tax', 'tajer_tax_settings', '' );
	$cart_include_tax   = tajer_get_option( 'cart_include_tax', 'tajer_tax_settings', '' );
	$display_tax_rate   = tajer_get_option( 'display_tax_rate', 'tajer_tax_settings', '' );

	$returned_value = array( $tax_rate_value, $prices_include_tax, $cart_include_tax, $display_tax_rate );

	$returned_value = apply_filters( 'tajer_taxValue', $returned_value );

	return $returned_value;
}

function tajer_taxParameters( $tax, $price, $prices_include_tax, $cart_include_tax, $total = false ) {
	$product_price_with_tax = $tax == 0 ? $price : ( ( $price * ( $tax / 100 ) ) + $price );

	$is_tax_incuded = false;
	if ( $prices_include_tax == 'no' && $cart_include_tax == 'yes' && $tax != 0 ) {
		$is_tax_incuded = true;
		$price          = $product_price_with_tax;
	}

	$price = $total ? $product_price_with_tax : $price;

	if ( $tax != 0 ) {
		$tax_text = $is_tax_incuded ? __( ' - includes ', 'tajer' ) . $tax . __( '% tax', 'tajer' ) : __( ' - excludes ', 'tajer' ) . $tax . __( '% tax', 'tajer' );

		$returned_value = array( $product_price_with_tax, $price, $tax_text );
	} else {
		$tax_text = '';

		$returned_value = array( $product_price_with_tax, $price, $tax_text );
	}
	$returned_value = apply_filters( 'tajer_taxParameters', $returned_value, $tax, $price, $prices_include_tax, $cart_include_tax, $total );

	return $returned_value;
}

register_activation_hook( Tajer_DIR . 'tajer.php', 'tajer_default_options' );
function tajer_default_options() {

	if ( get_option( 'tajer_general_settings' ) !== false ) {
		return;
	}

	$purchase_receipt_body = "Dear {name},\r\n\r\n";
	$purchase_receipt_body .= "Thank you for your purchase. Please click on the link below to go to your dashboard so you can download your files.\r\n";
	$purchase_receipt_body .= "{dashboard_link}\r\n\r\n";
	$purchase_receipt_body .= "Order #{order_number}\r\n";
	$purchase_receipt_body .= "{site_name}";

	$new_sale_notification_body = "Hello,\r\n\r\n";
	$new_sale_notification_body .= "A products purchase has been made.\r\n";
	$new_sale_notification_body .= "Details:\r\n";
	$new_sale_notification_body .= "Purchased by: {name}\r\n";
	$new_sale_notification_body .= "Amount: {price}\r\n";
	$new_sale_notification_body .= "Order #{order_number}\r\n\r\n";
	$new_sale_notification_body .= "Thank you";

	$expiration_notification_email_body = "Dear {name},\r\n";
	$expiration_notification_email_body .= "Your product {product_name} {option_name} will expire on {expiration_date}.\r\n";
	$expiration_notification_email_body .= "You can go to your dashboard from here {dashboard_link} and renew this product from there.\r\n";
	$expiration_notification_email_body .= "Please notice that if your product expired you must buy it again from scratch, but if you make a renew the fee might be less than the startup fee.\r\n\r\n";

	$expiration_notification_email_body .= "Kind Regards,\r\n";
	$expiration_notification_email_body .= "{site_name}";

	$settings = array(
		'purchase_receipt_email_subject'        => 'Purchase Receipt',
		'purchase_receipt_email_body'           => $purchase_receipt_body,
		'new_sale_notification_subject'         => 'Sale Notification',
		'new_sale_notification_body'            => $new_sale_notification_body,
		'expiration_notification_email_subject' => "{site_name} Expiration Notification",
		'expiration_notification_email_body'    => $expiration_notification_email_body,
		'expiration_email_period'               => 14
	);
	update_option( 'tajer_emails_settings', $settings );


	$general_settings = array(
		'restrict_by_ip'        => 'yes',
		'enable_recurring'      => 'yes',
		'enable_upgrade'        => 'yes',
		'enable_delete_product' => 'yes',
		'color'                 => 'teal',
		'secondary_color'       => 'green',
		'currency'              => 'USD',
		'currency_position'     => 'before',
		'restrict_by_email'     => 'yes'

	);

	$payment_settings = array(
		'payment_gateways' => array( 'test' ),
		'default_gateway'  => 'test'
	);

	update_option( 'tajer_general_settings', $general_settings );
	update_option( 'tajer_payment_settings', $payment_settings );
//	update_option( 'tajer_support_settings', $settings['tajer_support_settings'] );
//	update_option( 'tajer_tax_settings', $settings['tajer_tax_settings'] );
//	update_option( 'tajer_tools_settings', $settings['tajer_tools_settings'] );
}

function tajer_prepare_mail_body( array $opts ) {
	$default_opts = array(
		'content'         => '',// the defaults will be overridden if set in $opts
		'user'            => new stdClass(),
		'order_number'    => 0,
		'price'           => tajer_number_to_currency( 0, true ),
		'user_product'    => new stdClass(),
		'option_name'     => '',
		'expiration_date' => ''
	);

	$opts = array_merge( $default_opts, $opts );

	$opts = apply_filters( 'tajer_prepare_mail_body_opts', $opts );

	$field_search = array(
		'{name}',
		'{username}',
		'{site_name}',
		'{site_link}',
		'{dashboard_link}',
		'{order_number}',
		'{price}',
		'{product_name}',
		'{option_name}',
		'{number_of_downloads}',
		'{expiration_date}'
	);

	$field_search = apply_filters( 'tajer_prepare_mail_body_field_search', $field_search, $opts );

	$field_replace = array(
		$opts['user']->first_name . ' ' . $opts['user']->last_name,
		$opts['user']->user_login,
		get_bloginfo( 'name' ),
		home_url(),
		get_permalink( (int) tajer_get_option( 'dashboard_page', 'tajer_general_settings', '' ) ),
		$opts['order_number'],
		$opts['price'],
		get_the_title( $opts['user_product']->product_id ),
		$opts['option_name'],
		$opts['user_product']->number_of_downloads,
		$opts['expiration_date']
	);

	$field_replace = apply_filters( 'tajer_prepare_mail_body_field_replace', $field_replace, $field_search, $opts );

	$content = str_replace( $field_search, $field_replace, $opts['content'] );

	$content = apply_filters( 'tajer_prepare_mail_body_content', $content, $field_replace, $field_search, $opts );

	return $content;
}

function tajer_delete_expired_products() {

	do_action( 'tajer_delete_expired_user_products' );

	$date  = date( 'Y-m-d H:i:s' );
	$items = Tajer_DB::get_expired_users_products( $date );

	if ( empty( $items ) || is_null( $items ) ) {
		$returned_value = false;
	} else {
		$errors = array();
		foreach ( $items as $item ) {
			$is_deleted = Tajer_DB::delete_by_id( 'tajer_user_products', $item->id );
			if ( $is_deleted === false ) {
				$errors[] = false;
			}
		}

		$returned_value = $errors;
	}

	$returned_value = apply_filters( 'tajer_delete_expired_user_products_returned_value', $returned_value );

	return $returned_value;
}

/**
 * Remove thousands separators
 *
 * @param $amount
 *
 * @return mixed|void
 */
function tajer_sanitize_amount( $amount ) {
	$currency_obj  = new Tajer_Currency();
	$currency_code = tajer_get_option( 'currency', 'tajer_general_settings', '' );
	$currencies    = $currency_obj->currencies();
	$currency      = $currencies[ $currency_code ];

	$is_negative   = false;
	$thousands_sep = apply_filters( 'tajer_sanitize_amount_thousands_sep', $currency[2], $amount );
	$decimal_sep   = apply_filters( 'tajer_sanitize_amount_decimal_sep', $currency[1], $amount );

	// Sanitize the amount
	if ( $decimal_sep == ',' && false !== ( $found = strpos( $amount, $decimal_sep ) ) ) {
		if ( ( $thousands_sep == '.' || $thousands_sep == ' ' ) && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
			$amount = str_replace( $thousands_sep, '', $amount );
		} elseif ( empty( $thousands_sep ) && false !== ( $found = strpos( $amount, '.' ) ) ) {
			$amount = str_replace( '.', '', $amount );
		}

		$amount = str_replace( $decimal_sep, '.', $amount );
	} elseif ( $thousands_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( $thousands_sep, '', $amount );
	}

	if ( $amount < 0 ) {
		$is_negative = true;
	}

	$amount   = preg_replace( '/[^0-9\.]/', '', $amount );
	$decimals = apply_filters( 'tajer_sanitize_amount_decimals', $currency[0], $amount );
	$amount   = number_format( (double) $amount, $decimals, '.', '' );

	if ( $is_negative ) {
		$amount *= - 1;
	}

	return apply_filters( 'tajer_sanitize_amount', $amount );
}

function tajer_is_trial_possible( $period, $product_id, $product_sub_id ) {

	$current_user = wp_get_current_user();

	//check if the admin deny the user by ip or by email or both then get the ip and the user email and check them
	$restrict_by_ip    = tajer_get_option( 'restrict_by_ip', 'tajer_general_settings', '' );
	$restrict_by_email = tajer_get_option( 'restrict_by_email', 'tajer_general_settings', '' );
	$limits            = tajer_get_download_limits( $product_id, $product_sub_id );

	//is the user already have this trial active
	$has_trial = Tajer_DB::has_trial( $current_user->ID, $product_id, $product_sub_id );

	if ( ! is_user_logged_in() ) {
		$returned_value = false;
	} elseif ( $has_trial != null ) {
		$returned_value = false;
	} elseif ( $restrict_by_ip == 'yes' ) {
		$user_ip   = sanitize_text_field( $_SERVER["REMOTE_ADDR"] );
		$ip_record = Tajer_DB::get_trial_by_ip( $user_ip, $product_id, $product_sub_id );

		//check if the user didn't finish the trial period
		$period_permitted = date( 'Y-m-d H:i:s', strtotime( '+' . $period . ' days', strtotime( $ip_record->trial_date ) ) );

		//check if the user exceeded the download limit of this file
		$can_not_download = ( ( 0 != $limits ) && $ip_record->number_of_downloads > $limits );

		if ( $ip_record == null ) {
			$returned_value = true;
		} elseif ( ( date( 'Y-m-d H:i:s' ) > $period_permitted ) || $can_not_download ) {
			$returned_value = false;
		} else {
			$returned_value = true;
		}
	} elseif ( $restrict_by_email == 'yes' ) {

		$user_email = $current_user->user_email;

		$email_record = Tajer_DB::get_trial_by_email( $user_email, $product_id, $product_sub_id );

		//check if the user didn't finish the trial period
		$period_permitted = date( 'Y-m-d H:i:s', strtotime( '+' . $period . ' days', strtotime( $email_record->trial_date ) ) );

		//check if the user exceeded the download limit of this file
		$can_not_download = ( ( 0 != $limits ) && $email_record->number_of_downloads > $limits );

		if ( $email_record == null ) {
			$returned_value = true;
		} elseif ( ( date( 'Y-m-d H:i:s' ) > $period_permitted ) || $can_not_download ) {
			$returned_value = false;
		} else {
			$returned_value = true;
		}
	} else {
		$returned_value = true;
	}

	$returned_value = apply_filters( 'tajer_is_trial_possible', $returned_value, $period, $product_id, $product_sub_id );

	return $returned_value;
}

function tajer_insert_trial_record( $product_id, $product_sub_id ) {
	$current_user = wp_get_current_user();

	$data = array(
		'email'               => $current_user->user_email,
		'ip'                  => sanitize_text_field( $_SERVER["REMOTE_ADDR"] ),
		'product_id'          => $product_id,
		'product_sub_id'      => $product_sub_id,
		'number_of_downloads' => 0,
		'trial_date'          => date( 'Y-m-d H:i:s' )
	);

	$data = apply_filters( 'tajer_insert_trial_record_opts', $data );

	$result = Tajer_DB::insert_trial_record( $data );

	do_action( 'tajer_trial_record_created', $result, $data );

	$result = apply_filters( 'tajer_insert_trial_record', $result, $data );

	return $result;
}

function TajerinsertUserProductForTrialPurpose( $product_id, $product_sub_id, $period ) {

	$data = array(
		'user_id'             => get_current_user_id(),
		'buying_date'         => date( 'Y-m-d H:i:s' ),
		'expiration_date'     => date( 'Y-m-d H:i:s', strtotime( "+" . $period . " days" ) ),
		'order_id'            => 0,
		'product_id'          => $product_id,
		'product_sub_id'      => $product_sub_id,
		'number_of_downloads' => 0,
		'status'              => 'active',
		'activation_method'   => 'trial'
	);

	$result = tajer_insert_user_product( $data );

	return $result;
}

function tajer_get_timezone_id() {

	// if site timezone string exists, return it
	if ( $timezone = get_option( 'timezone_string' ) ) {
		return $timezone;
	}

	$offset = get_option( 'gmt_offset', 0 );

	// get UTC offset, if it isn't set return UTC
	if ( ! ( $utc_offset = 3600 * $offset ) ) {
		return 'UTC';
	}

	//Guess the timezone from the offset
	$time_zone = TajerOffsetToName( $offset );

	if ( $time_zone ) {
		return $time_zone;
	}

	// fallback
	return 'UTC';
}


/**
 *
 * Source http://php.net/manual/en/function.timezone-name-from-abbr.php
 *
 * @param $offset
 * @param null $isDst
 *
 * @return string
 */
function TajerOffsetToName( $offset, $isDst = null ) {
	if ( $isDst === null ) {
		$isDst = date( 'I' );
	}

	$offset *= 3600;
	$zone = timezone_name_from_abbr( '', $offset, $isDst );

	if ( $zone === false ) {
		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( (bool) $city['dst'] === (bool) $isDst &&
				     strlen( $city['timezone_id'] ) > 0 &&
				     $city['offset'] == $offset
				) {
					$zone = $city['timezone_id'];
					break;
				}
			}

			if ( $zone !== false ) {
				break;
			}
		}
	}

	return $zone;
}

/**
 * This function used for free products
 *
 * @param $product_id
 * @param $product_sub_id
 *
 * @return mixed
 */
function tajer_insert_free_product_into_tajer_user_products( $product_id, $product_sub_id ) {

	$data = array(
		'user_id'             => get_current_user_id(),
		'buying_date'         => date( 'Y-m-d H:i:s' ),
		'expiration_date'     => tajer_expiration_date( $product_id, $product_sub_id ),
		'order_id'            => 0,
		'product_id'          => $product_id,
		'product_sub_id'      => $product_sub_id,
		'number_of_downloads' => 0,
		'status'              => 'active',
		'activation_method'   => 'free'
	);

	$result = tajer_insert_user_product( $data );

	return $result;
}

/**
 * Just for debugging purpose
 *
 * @param $code
 */
function tajer_debug( $code, $log = false, $type = 'p' ) {

	if ( ! $log ) {
		echo '<pre>';
		if ( $type == 'p' ) {
			print_r( $code );
		} else {
			var_dump( $code );
		}
		echo '</pre>';
	} elseif ( ( $log ) && ( $type != 'p' ) ) {
		ob_start();                    // start buffer capture
		echo '[tajer_debug]';
		var_dump( $code );           // dump the values
		echo '[/tajer_debug]';
		$contents = ob_get_contents(); // put the buffer into a variable
		ob_end_clean();                // end capture
		error_log( $contents );        // log contents of the result of var_dump( $object )
	} elseif ( ( $log ) && ( $type == 'p' ) ) {
		ob_start();                    // start buffer capture
		echo '[tajer_debug]';
		print_r( $code );           // dump the values
		echo '[/tajer_debug]';
		$contents = ob_get_contents(); // put the buffer into a variable
		ob_end_clean();                // end capture
		error_log( $contents );        // log contents of the result of var_dump( $object )
	}
}

/**
 * This function currently only work with inserting new bundle products
 *
 * @param $product_id
 * @param $product_sub_id
 * @param $callback
 * @param array $additional_args
 * @param bool $args_only
 */
function tajer_handle_bundle_product( $product_id, $product_sub_id, $callback, $additional_args = array(), $args_only = false ) {
	add_action( 'tajer_handle_bundle_product', $product_id, $product_sub_id, $callback, $additional_args );
	global $tajer_inserted_user_product_result;
	global $tajer_inserted_bundle_products_ids;
	global $tajer_inserted_bundle_product_id;

	//First record or handle the bundle itself.
	( ! $args_only ) ? $a = array( $product_id, $product_sub_id ) : $a = array();
	$params = array_merge( $a, $additional_args );
	call_user_func_array( $callback, $params );

	$tajer_inserted_bundle_product_id = $tajer_inserted_user_product_result['id'];
	$bundle_products                  = array();

	//Now handle each product in the bundle product
	$bundled_products = get_post_meta( $product_id, 'tajer_bundled_products', true );
	foreach ( $bundled_products as $id => $bundled_product ) {
		if ( (int) $bundled_product['price'] != (int) $product_sub_id ) {
			continue;
		}
		if ( in_array( 'all', $bundled_product['sub_ids'] ) ) {
			$get_product_sub_ids = tajer_get_product_sub_ids( (int) $bundled_product['product'] );
			foreach ( $get_product_sub_ids as $productSubId ) {

				if ( ! $args_only ) {
					$a = array( (int) $bundled_product['product'], (int) $productSubId );
				} else {
					$a                                         = array();
					$key                                       = key( $additional_args );
					$additional_args[ $key ]['product_id']     = (int) $bundled_product['product'];
					$additional_args[ $key ]['product_sub_id'] = (int) $productSubId;
				}

				$params = array_merge( $a, $additional_args );
				call_user_func_array( $callback, $params );
				$bundle_products[] = $tajer_inserted_user_product_result['id'];
			}
		} else {
			foreach ( $bundled_product['sub_ids'] as $productSubId ) {

				if ( ! $args_only ) {
					$a = array( (int) $bundled_product['product'], (int) $productSubId );
				} else {
					$a                                         = array();
					$key                                       = key( $additional_args );
					$additional_args[ $key ]['product_id']     = (int) $bundled_product['product'];
					$additional_args[ $key ]['product_sub_id'] = (int) $productSubId;
				}

				$params = array_merge( $a, $additional_args );
				call_user_func_array( $callback, $params );
				$bundle_products[] = $tajer_inserted_user_product_result['id'];
			}
		}
	}
	$tajer_inserted_bundle_products_ids = $bundle_products;
	tajer_update_user_product_meta( $tajer_inserted_bundle_product_id, 'bundle_products', serialize( $bundle_products ) );
}

/**
 * This function work just with exist bundle product.
 *
 * @param $bundle_user_product_id
 * @param $callback
 * @param array $args
 */
//function tajer_handle_exist_bundle_product( $bundle_user_product_id, $callback, $args = array() ) {
//	$bundle_products = unserialize( tajer_get_user_product_meta( $bundle_user_product_id, 'bundle_products' ) );
//
//	//First apply the callback on the bundle itself
//	$a      = array( $bundle_user_product_id );
//	$params = array_merge( $a, $args );
//	call_user_func_array( $callback, $params );
//
//	//Now apply the callback on the bundle products
//	foreach ( $bundle_products as $bundle_product ) {
//		$a      = array( $bundle_product );
//		$params = array_merge( $a, $args );
//		call_user_func_array( $callback, $params );
//	}
//
//}

function tajer_get_product_sub_ids( $product_id ) {
	$product_sub_ids = array();
	$prices          = get_post_meta( $product_id, 'tajer_product_prices', true );
	foreach ( $prices as $id => $detail ) {
		$product_sub_ids[] = $id;
	}

	$product_sub_ids = apply_filters( 'tajer_get_product_sub_ids', $product_sub_ids, $product_id );

	return $product_sub_ids;
}

function tajer_get_product_sub_ids_with_names( $product_id ) {
	$product_sub_ids = array();

	if ( ! $product_id ) {
		return $product_sub_ids;
	}

	$prices = get_post_meta( $product_id, 'tajer_product_prices', true );
	foreach ( $prices as $id => $detail ) {
		$product_sub_ids[ $id ] = $detail['name'];
	}

	$product_sub_ids = apply_filters( 'tajer_get_product_sub_ids_with_names', $product_sub_ids, $product_id );

	return $product_sub_ids;
}

function tajer_get_template_part( $slug, $name = null, $load = true ) {
	// Execute code for this part
	do_action( 'tajer_get_template_part_' . $slug, $slug, $name );

	// Setup possible parts
	$templates = array();
	if ( isset( $name ) ) {
		$templates[] = $slug . '-' . $name . '.php';
	}
	$templates[] = $slug . '.php';

	// Allow template parts to be filtered
	$templates = apply_filters( 'tajer_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return tajer_locate_template( $templates, $load, false );
}

function tajer_locate_template( $template_names, $load = false, $require_once = true ) {
	// No file found yet
	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) ) {
			continue;
		}

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// try locating this template file by looping through the template paths
		foreach ( tajer_get_theme_template_paths() as $template_path ) {

			if ( file_exists( $template_path . $template_name ) ) {
				$located = $template_path . $template_name;
				break;
			}
		}

		if ( $located ) {
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) ) {
		load_template( $located, $require_once );
	}

	return $located;
}

function tajer_get_theme_template_paths() {
	$template_dir = tajer_get_theme_template_dir_name();

	$file_paths = array(
		1   => trailingslashit( get_stylesheet_directory() ) . $template_dir,
		10  => trailingslashit( get_template_directory() ) . $template_dir,
		100 => tajer_get_templates_dir()
	);

	$file_paths = apply_filters( 'tajer_template_paths', $file_paths );

	// sort the file paths based on priority
	ksort( $file_paths, SORT_NUMERIC );

	return array_map( 'trailingslashit', $file_paths );
}

function tajer_get_theme_template_dir_name() {
	return trailingslashit( apply_filters( 'tajer_templates_dir', 'tajer_templates' ) );
}

function tajer_get_templates_dir() {
	return Tajer_DIR . 'templates';
}

function tajer_is_customer( $product_id = 0, $product_sub_ids = 0, $user_id = 0 ) {

	if ( $user_id == 0 ) {
		$user_id = get_current_user_id();
	}

	if ( $product_id == 0 ) {
		global $post;
		$product_id = $post->ID;
	}

	if ( $product_sub_ids == 0 ) {
		$result = Tajer_DB::get_user_product_by_product_id( $user_id, $product_id );
	} else {
		$result = Tajer_DB::get_user_product_by_user_id( $user_id, $product_id, $product_sub_ids );
	}

	$result = apply_filters( 'tajer_is_customer', $result, $product_id, $product_sub_ids, $user_id );

	if ( is_null( $result ) ) {
		return false;
	} elseif ( ( date( 'Y-m-d H:i:s' ) > $result->expiration_date ) ) {
		return false;
	}

	return true;
}

function tajer_add_schema_microdata() {
	// Don't modify anything until after wp_head() is called
	$ret = (bool) did_action( 'wp_head' );

	return apply_filters( 'tajer_add_schema_microdata', $ret );
}

function tajer_microdata_wrapper_open() {
	global $post;

	static $microdata_open = null;

	if ( ! tajer_add_schema_microdata() || true === $microdata_open || ! is_object( $post ) ) {
		return;
	}

	if ( $post && $post->post_type == 'tajer_products' && is_singular( 'tajer_products' ) && is_main_query() ) {
		$microdata_open = true;
		echo '<span itemscope itemtype="http://schema.org/Product">';
	}

}

add_action( 'loop_start', 'tajer_microdata_wrapper_open', 10 );

function tajer_microdata_wrapper_close() {
	global $post;

	static $microdata_close = null;

	if ( ! tajer_add_schema_microdata() || true === $microdata_close || ! is_object( $post ) ) {
		return;
	}

	if ( $post && $post->post_type == 'tajer_products' && is_singular( 'tajer_products' ) && is_main_query() ) {
		$microdata_close = true;
		echo '</span>';
	}
}

add_action( 'loop_end', 'tajer_microdata_wrapper_close', 10 );

//function tajer_get_file_name( $item ) {
//	global $wpdb;
//	$result = $wpdb->get_row( $wpdb->prepare(
//		"SELECT * FROM {$wpdb->prefix}tajer_user_products WHERE id = %d"
//		, $item ) );
//
//	$files     = get_post_meta( $result->product_id, 'tajer_files', true );
//	$file_name = '';
//	if ( ! empty( $files ) ) {
//		foreach ( $files as $file ) {
//			if ( ( isset( $file['prices'] ) ) && $file['prices'] == $result->product_sub_id ) {
//				if ( $result->product_sub_id == 0 && ( ! empty( $file_name ) ) ) {
//					continue;
//				}
//				$file_name = $file['name'];
//			}
//		}
//	}
//
//	return $file_name;
//}

/**
 * File id is the file id relative to the product(equal to "data-index" html attribute in the #multiple_files_table html table),
 * the attachment id is the regular attachment id
 *
 * @param int $product_id
 * @param int $product_sub_id
 * @param int $user_product_id
 *
 * @return array|mixed|void
 */
function tajer_get_files_ids_with_attachments_ids( $product_id = 0, $product_sub_id = 0, $user_product_id = 0 ) {
	if ( $user_product_id != 0 ) {
		$result       = Tajer_DB::get_user_product( $user_product_id );
		$ProductId    = $result->product_id;
		$ProductSubId = $result->product_sub_id;

	} else {
		$ProductId    = $product_id;
		$ProductSubId = $product_sub_id;
	}

	$files = get_post_meta( $ProductId, 'tajer_files', true );
	$array = array();
	if ( ! empty( $files ) ) {
		foreach ( $files as $id => $file ) {
			if ( ( isset( $file['prices'] ) ) ) {
				if ( $ProductSubId == 0 ) {
//					$array[ $file['id'] ] = $file['prices'];
					$array[ $id ] = $file['id'];
				} else {
					if ( ( in_array( $ProductSubId, $file['prices'] ) ) || ( in_array( 'All', $file['prices'] ) ) ) {
//						$array[ $file['id'] ] = $file['prices'];
						$array[ $id ] = $file['id'];
					}
				}
			}
		}
	}

	$array = apply_filters( 'tajer_get_files_ids_with_attachments_ids', $array, $product_id, $product_sub_id, $user_product_id );

	return $array;
}

function get_files_ids_with_attachments_names_array( $product_id, $product_sub_id ) {
	$get_files_ids_with_attachments_ids     = tajer_get_files_ids_with_attachments_ids( $product_id, $product_sub_id );
	$files_ids_with_attachments_names_array = array();
	foreach ( $get_files_ids_with_attachments_ids as $files_id => $attachments_id ) {
		$files_ids_with_attachments_names_array[ $files_id ] = basename( get_attached_file( $attachments_id ) );
	}

	return $files_ids_with_attachments_names_array;
}

function get_files_ids_with_files_names_array( $product_id = 0, $product_sub_id = 0, $user_product_id = 0 ) {
	if ( $user_product_id != 0 ) {
		$result       = Tajer_DB::get_user_product( $user_product_id );
		$ProductId    = $result->product_id;
		$ProductSubId = $result->product_sub_id;

	} else {
		$ProductId    = $product_id;
		$ProductSubId = $product_sub_id;
	}

	$files = get_post_meta( $ProductId, 'tajer_files', true );
	$array = array();
	if ( ! empty( $files ) ) {
		foreach ( $files as $id => $file ) {
			if ( ( isset( $file['prices'] ) ) ) {
				if ( $ProductSubId == 0 ) {
//					$array[ $file['name'] ] = $file['prices'];
					$array[ $id ] = $file['name'];
				} else {
					if ( ( in_array( $ProductSubId, $file['prices'] ) ) || ( in_array( 'All', $file['prices'] ) ) ) {
//						$array[ $file['name'] ] = $file['prices'];
						$array[ $id ] = $file['name'];
					}
				}
			}
		}
	}

	$array = apply_filters( 'tajer_get_files_ids_with_attachments_ids', $array, $product_id, $product_sub_id, $user_product_id );

	return $array;
}

function tajer_get_file_extension( $str ) {
	$parts = explode( '.', $str );
	$parts = apply_filters( 'tajer_get_file_extension', $parts, $str );

	return end( $parts );
}

function tajer_is_func_disabled( $function ) {
	$disabled = explode( ',', ini_get( 'disable_functions' ) );

	return in_array( $function, $disabled );
}

function tajer_get_file_ctype( $extension ) {
	switch ( $extension ):
		case 'ac'       :
			$ctype = "application/pkix-attr-cert";
			break;
		case 'adp'      :
			$ctype = "audio/adpcm";
			break;
		case 'ai'       :
			$ctype = "application/postscript";
			break;
		case 'aif'      :
			$ctype = "audio/x-aiff";
			break;
		case 'aifc'     :
			$ctype = "audio/x-aiff";
			break;
		case 'aiff'     :
			$ctype = "audio/x-aiff";
			break;
		case 'air'      :
			$ctype = "application/vnd.adobe.air-application-installer-package+zip";
			break;
		case 'apk'      :
			$ctype = "application/vnd.android.package-archive";
			break;
		case 'asc'      :
			$ctype = "application/pgp-signature";
			break;
		case 'atom'     :
			$ctype = "application/atom+xml";
			break;
		case 'atomcat'  :
			$ctype = "application/atomcat+xml";
			break;
		case 'atomsvc'  :
			$ctype = "application/atomsvc+xml";
			break;
		case 'au'       :
			$ctype = "audio/basic";
			break;
		case 'aw'       :
			$ctype = "application/applixware";
			break;
		case 'avi'      :
			$ctype = "video/x-msvideo";
			break;
		case 'bcpio'    :
			$ctype = "application/x-bcpio";
			break;
		case 'bin'      :
			$ctype = "application/octet-stream";
			break;
		case 'bmp'      :
			$ctype = "image/bmp";
			break;
		case 'boz'      :
			$ctype = "application/x-bzip2";
			break;
		case 'bpk'      :
			$ctype = "application/octet-stream";
			break;
		case 'bz'       :
			$ctype = "application/x-bzip";
			break;
		case 'bz2'      :
			$ctype = "application/x-bzip2";
			break;
		case 'ccxml'    :
			$ctype = "application/ccxml+xml";
			break;
		case 'cdmia'    :
			$ctype = "application/cdmi-capability";
			break;
		case 'cdmic'    :
			$ctype = "application/cdmi-container";
			break;
		case 'cdmid'    :
			$ctype = "application/cdmi-domain";
			break;
		case 'cdmio'    :
			$ctype = "application/cdmi-object";
			break;
		case 'cdmiq'    :
			$ctype = "application/cdmi-queue";
			break;
		case 'cdf'      :
			$ctype = "application/x-netcdf";
			break;
		case 'cer'      :
			$ctype = "application/pkix-cert";
			break;
		case 'cgm'      :
			$ctype = "image/cgm";
			break;
		case 'class'    :
			$ctype = "application/octet-stream";
			break;
		case 'cpio'     :
			$ctype = "application/x-cpio";
			break;
		case 'cpt'      :
			$ctype = "application/mac-compactpro";
			break;
		case 'crl'      :
			$ctype = "application/pkix-crl";
			break;
		case 'csh'      :
			$ctype = "application/x-csh";
			break;
		case 'css'      :
			$ctype = "text/css";
			break;
		case 'cu'       :
			$ctype = "application/cu-seeme";
			break;
		case 'davmount' :
			$ctype = "application/davmount+xml";
			break;
		case 'dbk'      :
			$ctype = "application/docbook+xml";
			break;
		case 'dcr'      :
			$ctype = "application/x-director";
			break;
		case 'deploy'   :
			$ctype = "application/octet-stream";
			break;
		case 'dif'      :
			$ctype = "video/x-dv";
			break;
		case 'dir'      :
			$ctype = "application/x-director";
			break;
		case 'dist'     :
			$ctype = "application/octet-stream";
			break;
		case 'distz'    :
			$ctype = "application/octet-stream";
			break;
		case 'djv'      :
			$ctype = "image/vnd.djvu";
			break;
		case 'djvu'     :
			$ctype = "image/vnd.djvu";
			break;
		case 'dll'      :
			$ctype = "application/octet-stream";
			break;
		case 'dmg'      :
			$ctype = "application/octet-stream";
			break;
		case 'dms'      :
			$ctype = "application/octet-stream";
			break;
		case 'doc'      :
			$ctype = "application/msword";
			break;
		case 'docx'     :
			$ctype = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
			break;
		case 'dotx'     :
			$ctype = "application/vnd.openxmlformats-officedocument.wordprocessingml.template";
			break;
		case 'dssc'     :
			$ctype = "application/dssc+der";
			break;
		case 'dtd'      :
			$ctype = "application/xml-dtd";
			break;
		case 'dump'     :
			$ctype = "application/octet-stream";
			break;
		case 'dv'       :
			$ctype = "video/x-dv";
			break;
		case 'dvi'      :
			$ctype = "application/x-dvi";
			break;
		case 'dxr'      :
			$ctype = "application/x-director";
			break;
		case 'ecma'     :
			$ctype = "application/ecmascript";
			break;
		case 'elc'      :
			$ctype = "application/octet-stream";
			break;
		case 'emma'     :
			$ctype = "application/emma+xml";
			break;
		case 'eps'      :
			$ctype = "application/postscript";
			break;
		case 'epub'     :
			$ctype = "application/epub+zip";
			break;
		case 'etx'      :
			$ctype = "text/x-setext";
			break;
		case 'exe'      :
			$ctype = "application/octet-stream";
			break;
		case 'exi'      :
			$ctype = "application/exi";
			break;
		case 'ez'       :
			$ctype = "application/andrew-inset";
			break;
		case 'f4v'      :
			$ctype = "video/x-f4v";
			break;
		case 'fli'      :
			$ctype = "video/x-fli";
			break;
		case 'flv'      :
			$ctype = "video/x-flv";
			break;
		case 'gif'      :
			$ctype = "image/gif";
			break;
		case 'gml'      :
			$ctype = "application/srgs";
			break;
		case 'gpx'      :
			$ctype = "application/gml+xml";
			break;
		case 'gram'     :
			$ctype = "application/gpx+xml";
			break;
		case 'grxml'    :
			$ctype = "application/srgs+xml";
			break;
		case 'gtar'     :
			$ctype = "application/x-gtar";
			break;
		case 'gxf'      :
			$ctype = "application/gxf";
			break;
		case 'hdf'      :
			$ctype = "application/x-hdf";
			break;
		case 'hqx'      :
			$ctype = "application/mac-binhex40";
			break;
		case 'htm'      :
			$ctype = "text/html";
			break;
		case 'html'     :
			$ctype = "text/html";
			break;
		case 'ice'      :
			$ctype = "x-conference/x-cooltalk";
			break;
		case 'ico'      :
			$ctype = "image/x-icon";
			break;
		case 'ics'      :
			$ctype = "text/calendar";
			break;
		case 'ief'      :
			$ctype = "image/ief";
			break;
		case 'ifb'      :
			$ctype = "text/calendar";
			break;
		case 'iges'     :
			$ctype = "model/iges";
			break;
		case 'igs'      :
			$ctype = "model/iges";
			break;
		case 'ink'      :
			$ctype = "application/inkml+xml";
			break;
		case 'inkml'    :
			$ctype = "application/inkml+xml";
			break;
		case 'ipfix'    :
			$ctype = "application/ipfix";
			break;
		case 'jar'      :
			$ctype = "application/java-archive";
			break;
		case 'jnlp'     :
			$ctype = "application/x-java-jnlp-file";
			break;
		case 'jp2'      :
			$ctype = "image/jp2";
			break;
		case 'jpe'      :
			$ctype = "image/jpeg";
			break;
		case 'jpeg'     :
			$ctype = "image/jpeg";
			break;
		case 'jpg'      :
			$ctype = "image/jpeg";
			break;
		case 'js'       :
			$ctype = "application/javascript";
			break;
		case 'json'     :
			$ctype = "application/json";
			break;
		case 'jsonml'   :
			$ctype = "application/jsonml+json";
			break;
		case 'kar'      :
			$ctype = "audio/midi";
			break;
		case 'latex'    :
			$ctype = "application/x-latex";
			break;
		case 'lha'      :
			$ctype = "application/octet-stream";
			break;
		case 'lrf'      :
			$ctype = "application/octet-stream";
			break;
		case 'lzh'      :
			$ctype = "application/octet-stream";
			break;
		case 'lostxml'  :
			$ctype = "application/lost+xml";
			break;
		case 'm3u'      :
			$ctype = "audio/x-mpegurl";
			break;
		case 'm4a'      :
			$ctype = "audio/mp4a-latm";
			break;
		case 'm4b'      :
			$ctype = "audio/mp4a-latm";
			break;
		case 'm4p'      :
			$ctype = "audio/mp4a-latm";
			break;
		case 'm4u'      :
			$ctype = "video/vnd.mpegurl";
			break;
		case 'm4v'      :
			$ctype = "video/x-m4v";
			break;
		case 'm21'      :
			$ctype = "application/mp21";
			break;
		case 'ma'       :
			$ctype = "application/mathematica";
			break;
		case 'mac'      :
			$ctype = "image/x-macpaint";
			break;
		case 'mads'     :
			$ctype = "application/mads+xml";
			break;
		case 'man'      :
			$ctype = "application/x-troff-man";
			break;
		case 'mar'      :
			$ctype = "application/octet-stream";
			break;
		case 'mathml'   :
			$ctype = "application/mathml+xml";
			break;
		case 'mbox'     :
			$ctype = "application/mbox";
			break;
		case 'me'       :
			$ctype = "application/x-troff-me";
			break;
		case 'mesh'     :
			$ctype = "model/mesh";
			break;
		case 'metalink' :
			$ctype = "application/metalink+xml";
			break;
		case 'meta4'    :
			$ctype = "application/metalink4+xml";
			break;
		case 'mets'     :
			$ctype = "application/mets+xml";
			break;
		case 'mid'      :
			$ctype = "audio/midi";
			break;
		case 'midi'     :
			$ctype = "audio/midi";
			break;
		case 'mif'      :
			$ctype = "application/vnd.mif";
			break;
		case 'mods'     :
			$ctype = "application/mods+xml";
			break;
		case 'mov'      :
			$ctype = "video/quicktime";
			break;
		case 'movie'    :
			$ctype = "video/x-sgi-movie";
			break;
		case 'm1v'      :
			$ctype = "video/mpeg";
			break;
		case 'm2v'      :
			$ctype = "video/mpeg";
			break;
		case 'mp2'      :
			$ctype = "audio/mpeg";
			break;
		case 'mp2a'     :
			$ctype = "audio/mpeg";
			break;
		case 'mp21'     :
			$ctype = "application/mp21";
			break;
		case 'mp3'      :
			$ctype = "audio/mpeg";
			break;
		case 'mp3a'     :
			$ctype = "audio/mpeg";
			break;
		case 'mp4'      :
			$ctype = "video/mp4";
			break;
		case 'mp4s'     :
			$ctype = "application/mp4";
			break;
		case 'mpe'      :
			$ctype = "video/mpeg";
			break;
		case 'mpeg'     :
			$ctype = "video/mpeg";
			break;
		case 'mpg'      :
			$ctype = "video/mpeg";
			break;
		case 'mpg4'     :
			$ctype = "video/mpeg";
			break;
		case 'mpga'     :
			$ctype = "audio/mpeg";
			break;
		case 'mrc'      :
			$ctype = "application/marc";
			break;
		case 'mrcx'     :
			$ctype = "application/marcxml+xml";
			break;
		case 'ms'       :
			$ctype = "application/x-troff-ms";
			break;
		case 'mscml'    :
			$ctype = "application/mediaservercontrol+xml";
			break;
		case 'msh'      :
			$ctype = "model/mesh";
			break;
		case 'mxf'      :
			$ctype = "application/mxf";
			break;
		case 'mxu'      :
			$ctype = "video/vnd.mpegurl";
			break;
		case 'nc'       :
			$ctype = "application/x-netcdf";
			break;
		case 'oda'      :
			$ctype = "application/oda";
			break;
		case 'oga'      :
			$ctype = "application/ogg";
			break;
		case 'ogg'      :
			$ctype = "application/ogg";
			break;
		case 'ogx'      :
			$ctype = "application/ogg";
			break;
		case 'omdoc'    :
			$ctype = "application/omdoc+xml";
			break;
		case 'onetoc'   :
			$ctype = "application/onenote";
			break;
		case 'onetoc2'  :
			$ctype = "application/onenote";
			break;
		case 'onetmp'   :
			$ctype = "application/onenote";
			break;
		case 'onepkg'   :
			$ctype = "application/onenote";
			break;
		case 'opf'      :
			$ctype = "application/oebps-package+xml";
			break;
		case 'oxps'     :
			$ctype = "application/oxps";
			break;
		case 'p7c'      :
			$ctype = "application/pkcs7-mime";
			break;
		case 'p7m'      :
			$ctype = "application/pkcs7-mime";
			break;
		case 'p7s'      :
			$ctype = "application/pkcs7-signature";
			break;
		case 'p8'       :
			$ctype = "application/pkcs8";
			break;
		case 'p10'      :
			$ctype = "application/pkcs10";
			break;
		case 'pbm'      :
			$ctype = "image/x-portable-bitmap";
			break;
		case 'pct'      :
			$ctype = "image/pict";
			break;
		case 'pdb'      :
			$ctype = "chemical/x-pdb";
			break;
		case 'pdf'      :
			$ctype = "application/pdf";
			break;
		case 'pki'      :
			$ctype = "application/pkixcmp";
			break;
		case 'pkipath'  :
			$ctype = "application/pkix-pkipath";
			break;
		case 'pfr'      :
			$ctype = "application/font-tdpfr";
			break;
		case 'pgm'      :
			$ctype = "image/x-portable-graymap";
			break;
		case 'pgn'      :
			$ctype = "application/x-chess-pgn";
			break;
		case 'pgp'      :
			$ctype = "application/pgp-encrypted";
			break;
		case 'pic'      :
			$ctype = "image/pict";
			break;
		case 'pict'     :
			$ctype = "image/pict";
			break;
		case 'pkg'      :
			$ctype = "application/octet-stream";
			break;
		case 'png'      :
			$ctype = "image/png";
			break;
		case 'pnm'      :
			$ctype = "image/x-portable-anymap";
			break;
		case 'pnt'      :
			$ctype = "image/x-macpaint";
			break;
		case 'pntg'     :
			$ctype = "image/x-macpaint";
			break;
		case 'pot'      :
			$ctype = "application/vnd.ms-powerpoint";
			break;
		case 'potx'     :
			$ctype = "application/vnd.openxmlformats-officedocument.presentationml.template";
			break;
		case 'ppm'      :
			$ctype = "image/x-portable-pixmap";
			break;
		case 'pps'      :
			$ctype = "application/vnd.ms-powerpoint";
			break;
		case 'ppsx'     :
			$ctype = "application/vnd.openxmlformats-officedocument.presentationml.slideshow";
			break;
		case 'ppt'      :
			$ctype = "application/vnd.ms-powerpoint";
			break;
		case 'pptx'     :
			$ctype = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
			break;
		case 'prf'      :
			$ctype = "application/pics-rules";
			break;
		case 'ps'       :
			$ctype = "application/postscript";
			break;
		case 'psd'      :
			$ctype = "image/photoshop";
			break;
		case 'qt'       :
			$ctype = "video/quicktime";
			break;
		case 'qti'      :
			$ctype = "image/x-quicktime";
			break;
		case 'qtif'     :
			$ctype = "image/x-quicktime";
			break;
		case 'ra'       :
			$ctype = "audio/x-pn-realaudio";
			break;
		case 'ram'      :
			$ctype = "audio/x-pn-realaudio";
			break;
		case 'ras'      :
			$ctype = "image/x-cmu-raster";
			break;
		case 'rdf'      :
			$ctype = "application/rdf+xml";
			break;
		case 'rgb'      :
			$ctype = "image/x-rgb";
			break;
		case 'rm'       :
			$ctype = "application/vnd.rn-realmedia";
			break;
		case 'rmi'      :
			$ctype = "audio/midi";
			break;
		case 'roff'     :
			$ctype = "application/x-troff";
			break;
		case 'rss'      :
			$ctype = "application/rss+xml";
			break;
		case 'rtf'      :
			$ctype = "text/rtf";
			break;
		case 'rtx'      :
			$ctype = "text/richtext";
			break;
		case 'sgm'      :
			$ctype = "text/sgml";
			break;
		case 'sgml'     :
			$ctype = "text/sgml";
			break;
		case 'sh'       :
			$ctype = "application/x-sh";
			break;
		case 'shar'     :
			$ctype = "application/x-shar";
			break;
		case 'sig'      :
			$ctype = "application/pgp-signature";
			break;
		case 'silo'     :
			$ctype = "model/mesh";
			break;
		case 'sit'      :
			$ctype = "application/x-stuffit";
			break;
		case 'skd'      :
			$ctype = "application/x-koan";
			break;
		case 'skm'      :
			$ctype = "application/x-koan";
			break;
		case 'skp'      :
			$ctype = "application/x-koan";
			break;
		case 'skt'      :
			$ctype = "application/x-koan";
			break;
		case 'sldx'     :
			$ctype = "application/vnd.openxmlformats-officedocument.presentationml.slide";
			break;
		case 'smi'      :
			$ctype = "application/smil";
			break;
		case 'smil'     :
			$ctype = "application/smil";
			break;
		case 'snd'      :
			$ctype = "audio/basic";
			break;
		case 'so'       :
			$ctype = "application/octet-stream";
			break;
		case 'spl'      :
			$ctype = "application/x-futuresplash";
			break;
		case 'spx'      :
			$ctype = "audio/ogg";
			break;
		case 'src'      :
			$ctype = "application/x-wais-source";
			break;
		case 'stk'      :
			$ctype = "application/hyperstudio";
			break;
		case 'sv4cpio'  :
			$ctype = "application/x-sv4cpio";
			break;
		case 'sv4crc'   :
			$ctype = "application/x-sv4crc";
			break;
		case 'svg'      :
			$ctype = "image/svg+xml";
			break;
		case 'swf'      :
			$ctype = "application/x-shockwave-flash";
			break;
		case 't'        :
			$ctype = "application/x-troff";
			break;
		case 'tar'      :
			$ctype = "application/x-tar";
			break;
		case 'tcl'      :
			$ctype = "application/x-tcl";
			break;
		case 'tex'      :
			$ctype = "application/x-tex";
			break;
		case 'texi'     :
			$ctype = "application/x-texinfo";
			break;
		case 'texinfo'  :
			$ctype = "application/x-texinfo";
			break;
		case 'tif'      :
			$ctype = "image/tiff";
			break;
		case 'tiff'     :
			$ctype = "image/tiff";
			break;
		case 'torrent'  :
			$ctype = "application/x-bittorrent";
			break;
		case 'tr'       :
			$ctype = "application/x-troff";
			break;
		case 'tsv'      :
			$ctype = "text/tab-separated-values";
			break;
		case 'txt'      :
			$ctype = "text/plain";
			break;
		case 'ustar'    :
			$ctype = "application/x-ustar";
			break;
		case 'vcd'      :
			$ctype = "application/x-cdlink";
			break;
		case 'vrml'     :
			$ctype = "model/vrml";
			break;
		case 'vsd'      :
			$ctype = "application/vnd.visio";
			break;
		case 'vss'      :
			$ctype = "application/vnd.visio";
			break;
		case 'vst'      :
			$ctype = "application/vnd.visio";
			break;
		case 'vsw'      :
			$ctype = "application/vnd.visio";
			break;
		case 'vxml'     :
			$ctype = "application/voicexml+xml";
			break;
		case 'wav'      :
			$ctype = "audio/x-wav";
			break;
		case 'wbmp'     :
			$ctype = "image/vnd.wap.wbmp";
			break;
		case 'wbmxl'    :
			$ctype = "application/vnd.wap.wbxml";
			break;
		case 'wm'       :
			$ctype = "video/x-ms-wm";
			break;
		case 'wml'      :
			$ctype = "text/vnd.wap.wml";
			break;
		case 'wmlc'     :
			$ctype = "application/vnd.wap.wmlc";
			break;
		case 'wmls'     :
			$ctype = "text/vnd.wap.wmlscript";
			break;
		case 'wmlsc'    :
			$ctype = "application/vnd.wap.wmlscriptc";
			break;
		case 'wmv'      :
			$ctype = "video/x-ms-wmv";
			break;
		case 'wmx'      :
			$ctype = "video/x-ms-wmx";
			break;
		case 'wrl'      :
			$ctype = "model/vrml";
			break;
		case 'xbm'      :
			$ctype = "image/x-xbitmap";
			break;
		case 'xdssc'    :
			$ctype = "application/dssc+xml";
			break;
		case 'xer'      :
			$ctype = "application/patch-ops-error+xml";
			break;
		case 'xht'      :
			$ctype = "application/xhtml+xml";
			break;
		case 'xhtml'    :
			$ctype = "application/xhtml+xml";
			break;
		case 'xla'      :
			$ctype = "application/vnd.ms-excel";
			break;
		case 'xlam'     :
			$ctype = "application/vnd.ms-excel.addin.macroEnabled.12";
			break;
		case 'xlc'      :
			$ctype = "application/vnd.ms-excel";
			break;
		case 'xlm'      :
			$ctype = "application/vnd.ms-excel";
			break;
		case 'xls'      :
			$ctype = "application/vnd.ms-excel";
			break;
		case 'xlsx'     :
			$ctype = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
			break;
		case 'xlsb'     :
			$ctype = "application/vnd.ms-excel.sheet.binary.macroEnabled.12";
			break;
		case 'xlt'      :
			$ctype = "application/vnd.ms-excel";
			break;
		case 'xltx'     :
			$ctype = "application/vnd.openxmlformats-officedocument.spreadsheetml.template";
			break;
		case 'xlw'      :
			$ctype = "application/vnd.ms-excel";
			break;
		case 'xml'      :
			$ctype = "application/xml";
			break;
		case 'xpm'      :
			$ctype = "image/x-xpixmap";
			break;
		case 'xsl'      :
			$ctype = "application/xml";
			break;
		case 'xslt'     :
			$ctype = "application/xslt+xml";
			break;
		case 'xul'      :
			$ctype = "application/vnd.mozilla.xul+xml";
			break;
		case 'xwd'      :
			$ctype = "image/x-xwindowdump";
			break;
		case 'xyz'      :
			$ctype = "chemical/x-xyz";
			break;
		case 'zip'      :
			$ctype = "application/zip";
			break;
		default         :
			$ctype = "application/force-download";
	endswitch;

	if ( wp_is_mobile() ) {
		$ctype = 'application/octet-stream';
	}

	return apply_filters( 'tajer_file_ctype', $ctype );
}

function tajer_get_purchase_form_action() {
//	$url = get_permalink( (int) tajer_get_option( 'cart', 'tajer_general_settings', '' ) );
	$get_default_payment_gateway = tajer_get_option( 'default_gateway', 'tajer_payment_settings' );

	$action = apply_filters( 'tajer_purchase_form_action', array( 'payment-mode' => $get_default_payment_gateway ) );
	$url    = esc_url( add_query_arg( $action, get_permalink( (int) tajer_get_option( 'cart', 'tajer_general_settings', '' ) ) ) );

	return $url;
}

function tajer_payment_gateways() {
//	$payment_gateways = array();
	return apply_filters( 'tajer_payment_gateways', array() );
}

//function tajer_render_frontend_payment_methods() {
//	$get_frontend_payment_gateways = tajer_get_frontend_payment_gateways();
//	$enabled_payment_gateways      = tajer_get_option( 'payment_gateways', 'tajer_payment_settings' );
//	$get_default_payment_gateway   = tajer_get_option( 'default_gateway', 'tajer_payment_settings' );
//	$html                          = '';
//
//	if ( count( $enabled_payment_gateways ) > 1 ) {
//		$html .= '';
//		foreach ( $get_frontend_payment_gateways as $payment_gateway_id => $payment_gateway_label ) {
//			if ( ! in_array( $payment_gateway_id, $enabled_payment_gateways ) ) {
//				continue;
//			}
//			$html .= '<div class="inline field"><div class="ui slider checkbox">';
//			$html .= '<input type="radio" ' . checked( $get_default_payment_gateway, $payment_gateway_id, false ) . ' name="payment-mode" value="' . $payment_gateway_id . '" tabindex="0" class="hidden">';
//			$html .= '<label>' . $payment_gateway_label . '</label>';
//			$html .= '</div></div>';
//		}
//	} else {
//		if ( in_array( $get_default_payment_gateway, $enabled_payment_gateways ) ) {
//			$html .= '<input type="hidden" name="payment-mode" value="' . $get_default_payment_gateway . '">';
//		}
//	}
//
//	$html = apply_filters( 'tajer_render_frontend_payment_methods', $html );
//
//	return $html;
//}

function tajer_render_frontend_payment_methods_helper() {
	$get_frontend_payment_gateways = tajer_get_frontend_payment_gateways();
	$enabled_payment_gateways      = tajer_get_option( 'payment_gateways', 'tajer_payment_settings' );
	$get_default_payment_gateway   = tajer_get_option( 'default_gateway', 'tajer_payment_settings' );

	$obj                                = new stdClass();
	$obj->enabled_payment_gateways      = $enabled_payment_gateways;
	$obj->get_frontend_payment_gateways = $get_frontend_payment_gateways;
	$obj->get_default_payment_gateway   = $get_default_payment_gateway;

	$obj = apply_filters( 'tajer_render_frontend_payment_methods_obj', $obj );

	return $obj;
}

function tajer_record_upgrade_user_product( $user_product_id, $product_id, $product_sub_id, $upgrade_to, $new_expiration_date, $order_id ) {
	$current_recorded_upgrades = unserialize( tajer_get_user_product_meta( $user_product_id, 'upgrade' ) );
	if ( $current_recorded_upgrades && is_array( $current_recorded_upgrades ) ) {
		$current_recorded_upgrades[] = array(
			'product_id'          => $product_id,
			'product_sub_id'      => $product_sub_id,
			'upgrade_to'          => $upgrade_to,
			'new_expiration_date' => $new_expiration_date,
			'order_id'            => $order_id
		);
	} else {
		$current_recorded_upgrades   = array();
		$current_recorded_upgrades[] = array(
			'product_id'          => $product_id,
			'product_sub_id'      => $product_sub_id,
			'upgrade_to'          => $upgrade_to,
			'new_expiration_date' => $new_expiration_date,
			'order_id'            => $order_id
		);
	}

	tajer_update_user_product_meta( $user_product_id, 'upgrade', serialize( $current_recorded_upgrades ) );
}

function tajer_record_recurring_user_product( $user_product_id, $new_expiration_date, $limit, $order_id ) {
	$current_recorded_recurring = unserialize( tajer_get_user_product_meta( $user_product_id, 'recurring' ) );
	if ( $current_recorded_recurring && is_array( $current_recorded_recurring ) ) {
		$current_recorded_recurring[] = array(
			'new_expiration_date' => $new_expiration_date,
			'limit'               => $limit,
			'order_id'            => $order_id
		);
	} else {
		$current_recorded_recurring   = array();
		$current_recorded_recurring[] = array(
			'new_expiration_date' => $new_expiration_date,
			'limit'               => $limit,
			'order_id'            => $order_id
		);
	}

	tajer_update_user_product_meta( $user_product_id, 'recurring', serialize( $current_recorded_recurring ) );
}

//function tajer_valid_purchase_form() {
//	return apply_filters( 'tajer_valid_purchase_form', true );
//}

function tajer_purchase_form_errors() {
	return apply_filters( 'tajer_purchase_form_errors', array() );
}

//function tajer_validate_purchase_form() {
//}
//
//add_action( 'tajer_before_insert_order', 'tajer_validate_purchase_form' );

/**
 * @param array $opts
 *
 * @return array of total, secret_code, is_insert, id
 */
function tajer_insert_order( array $opts = array() ) {
	do_action( 'tajer_before_insert_order', $opts );

	$cart             = new Tajer_Cart();
	$total            = $cart->get_user_total();
	$customer_details = tajer_customer_details();
	$default_opts     = array(
		'user_id'          => $customer_details->id,
		'gateway_order_id' => '',
		'gateway'          => '',
		'cart_ids'         => $cart->cartIds(),
		'total'            => tajer_sanitize_amount( $total ),
		'products'         => $cart->products(),
		'date'             => date( 'Y-m-d H:i:s' ),
		'coupon'           => $cart->coupon,
		'secret_code'      => $cart->secret_code,
		'action'           => $cart->action(),
		'action_id'        => $cart->action_id(),
		'status'           => 'pending',
		'ip'               => sanitize_text_field( $_SERVER["REMOTE_ADDR"] )
	);

	$opts = array_merge( $default_opts, $opts );

	$opts = apply_filters( 'tajer_insert_order_args', $opts );

	$result = Tajer_DB::insert_order( $opts );

	$result['secret_code'] = $cart->secret_code;
	$result['total']       = $total;

	do_action( 'tajer_order_inserted', $result, $opts );

	$result = apply_filters( 'tajer_insert_order', $result, $opts );

	return $result;
}

function tajer_get_thank_you_page_url() {
	$url = get_permalink( (int) tajer_get_option( 'thank_you_page', 'tajer_general_settings', '' ) );
	$url = apply_filters( 'tajer_thank_you_page_url', $url );

	return $url;
}

function tajer_redirect_to_thank_you_page() {
	do_action( 'tajer_redirect_to_thank_you_page' );
	wp_redirect( tajer_get_thank_you_page_url() );
	exit;
}

function tajer_order_completed( array $opts = array() ) {
	do_action( 'tajer_start_order_completed', $opts );
	$default_opts = array(
		'order_number'   => 0,
		'total'          => 0,
		'tajer_order_id' => 0

	);
	$opts         = array_merge( $default_opts, $opts );
	$opts         = apply_filters( 'tajer_order_completed', $opts );
	$order        = new Tajer_Order();
	$order->order_completed( $opts );
	do_action( 'tajer_end_order_completed', $opts, $order );
}

function tajer_purchase_form() {
	ob_start();
	tajer_get_template_part( 'purchase-form' );
	$form_fields = ob_get_clean();

	//AJAX json response
	$response = array(
		'form_fields' => $form_fields
	);

	$response = apply_filters( 'tajer_purchase_form_response', $response );

	tajer_response( $response );
}

add_action( 'tajer_purchase_form', 'tajer_purchase_form' );

function tajer_get_admin_payment_gateways() {
	$payment_gateways       = tajer_payment_gateways();
	$admin_payment_gateways = array();
	foreach ( $payment_gateways as $payment_gateway_id => $payment_gateway_details ) {
		$admin_payment_gateways[ $payment_gateway_id ] = $payment_gateway_details['admin_label'];
	}

	$admin_payment_gateways = apply_filters( 'tajer_admin_payment_gateways', $admin_payment_gateways );

	return $admin_payment_gateways;
}

function tajer_get_frontend_payment_gateways() {
	$payment_gateways          = tajer_payment_gateways();
	$frontend_payment_gateways = array();
	foreach ( $payment_gateways as $payment_gateway_id => $payment_gateway_details ) {
		$frontend_payment_gateways[ $payment_gateway_id ] = $payment_gateway_details['checkout_label'];
	}

	$frontend_payment_gateways = apply_filters( 'tajer_frontend_payment_gateways', $frontend_payment_gateways );

	return $frontend_payment_gateways;
}
