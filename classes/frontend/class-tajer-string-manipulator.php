<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


class Tajer_String_Manipulator {
	/**
	 * Insert string into another string but before substring
	 *
	 * @param $string
	 * @param $string_to_insert
	 * @param $before
	 *
	 * @return mixed
	 */
	function insert_string_before( $string, $string_to_insert, $before ) {
		$position = strpos( $string, $before );

		$new_string = substr_replace( $string, $string_to_insert, $position, 0 );

		return $new_string;
	}

	/**
	 * Insert string into another string but after substring
	 *
	 * @param $string
	 * @param $string_to_insert
	 * @param $after
	 *
	 * @return mixed
	 */
	function insert_string_after( $string, $string_to_insert, $after ) {

		$count = mb_strlen( $after );

		$position = strpos( $string, $after ) + $count;

		$new_string = substr_replace( $string, $string_to_insert, $position, 0 );

		return $new_string;
	}

	/**
	 * Get string between two strings
	 *
	 * @param $string
	 * @param $start
	 * @param $end
	 *
	 * @return string
	 */
	function get_string_between( $string, $start, $end ) {
		$string = " " . $string;
		$ini    = strpos( $string, $start );
		if ( $ini == 0 ) {
			return "";
		}
		$ini += strlen( $start );
		$len = strpos( $string, $end, $ini ) - $ini;

		return substr( $string, $ini, $len );
	}
}