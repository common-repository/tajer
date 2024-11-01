<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
/**
 * Class Tajer_Thank_You
 *
 * The shopping card of Tajer.
 * Receive data from the payment method then parse it.
 */
class Tajer_Thank_You {
	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'fire_required_gateway_action' ) );
		add_shortcode( 'tajer_thank_you', array( $this, 'tajer_thank_you_callback' ) );
	}

	function fire_required_gateway_action() {
		if ( isset( $_REQUEST['tajer_gateway'] ) && ( ! empty( $_REQUEST['tajer_gateway'] ) ) ) {
			do_action( 'tajer_' . $_REQUEST['tajer_gateway'] . '_parameters_parser' );
		}
	}

	function tajer_thank_you_callback() {
		do_action( 'tajer_thank_you_page' );
	}
}
