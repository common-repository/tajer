<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Get string between two strings
 *
 * @since 1.0
 * @deprecated 1.0
 * @deprecated Use Tajer_String_Manipulator() class
 *
 *
 * @param $string
 * @param $start
 * @param $end
 *
 * @return string
 */
function tajer_get_string_between( $string, $start, $end ) {
	$string = " " . $string;
	$ini    = strpos( $string, $start );
	if ( $ini == 0 ) {
		return "";
	}
	$ini += strlen( $start );
	$len = strpos( $string, $end, $ini ) - $ini;

	return substr( $string, $ini, $len );
}