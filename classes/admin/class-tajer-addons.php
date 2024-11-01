<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Addons {

	private static $instance;


	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );
	}

	function admin_menu() {
		global $submenu;

		if ( ! is_array( $submenu['edit.php?post_type=tajer_products'] ) ) {
			return;
		}

		$last_key = key( array_slice( $submenu['edit.php?post_type=tajer_products'], - 1, 1, true ) );

		$new_key = ( $last_key ) + 1;

		$submenu['edit.php?post_type=tajer_products'][ $new_key ] = array(
			'Add-ons',
			apply_filters( 'tajer_addons_admin_menu_capability', 'manage_options' ),
			'https://mostasharoon.org/tajer-add-ons/'
		);
	}
}