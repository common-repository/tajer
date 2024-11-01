<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
/*
  Plugin Name: Tajer
  Plugin URI: https://mostasharoon.org/tajer/
  Description: Tajer is an electronic merchant that can sell your digital products instead of you.
  Version: 1.0.5
  Author: Mohammed Thaer
  Author URI: https://mostasharoon.org
  Text Domain: tajer
 */

define( 'TAJER_VERSION', '1.0.5' );
// Dir to the plugin
define( 'Tajer_DIR', plugin_dir_path( __FILE__ ) );
// URL to the plugin
define( 'Tajer_URL', plugin_dir_url( __FILE__ ) );


require_once( 'lib/tcpdf/tcpdf.php' );

require_once( 'includes/tajer-deprecated-functions.php' );
require_once( 'includes/tajer-upload-functions.php' );
require_once( 'includes/tajer-functions.php' );
require_once( 'includes/gateways/test.php' );
require_once( 'includes/tajer-taxonomy.php' );
require_once( 'includes/tajer-widgets.php' );
require_once( 'includes/templates.php' );
//require_once( 'includes/gateways/2checkout.php' );
require_once( 'includes/gateways/paypal-standard.php' );
require_once( 'includes/tajer-cron.php' );
require_once( 'includes/tajer-internal-hooks.php' );
require_once( 'includes/tajer-upgrades.php' );


spl_autoload_register( 'tajer_autoloader' );
function tajer_autoloader( $class_name ) {
	//replace _ with -
	$class_file_name = str_replace( '_', '-', $class_name );
	$class_file_name = 'class-' . strtolower( $class_file_name ) . '.php';

	$admin_classes_path    = Tajer_DIR . 'classes/admin/' . $class_file_name;
	$frontend_classes_path = Tajer_DIR . 'classes/frontend/' . $class_file_name;

	if ( file_exists( $admin_classes_path ) && is_readable( $admin_classes_path ) ) {
		include $admin_classes_path;
	} elseif ( file_exists( $frontend_classes_path ) && is_readable( $frontend_classes_path ) ) {
		include $frontend_classes_path;
	}
}

class Tajer {
	private static $instance;

	public $coupon;
	public $db;
	public $orders_page;
	public $products;
	public $reports;
	public $settings;
	public $addons;
	public $thickbox;
	public $user_products;
	public $cart_page;
	public $content_seller;
	public $dashboard;
	public $frontend_scripts;
	public $frontend_product;
	public $products_grid;
	public $purchase_link_parser;
	public $thank_you;
	public $bundle;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Tajer constructor.
	 */
	public function __construct() {
		$this->coupons              = Tajer_Coupons::get_instance();
		$this->db                   = Tajer_DB::get_instance();
		$this->orders_page          = Tajer_Orders_Page::get_instance();
		$this->products             = Tajer_Products::get_instance();
		$this->reports              = Tajer_Reports::get_instance();
		$this->settings             = Tajer_Settings::get_instance();
		$this->addons               = Tajer_Addons::get_instance();
		$this->thickbox             = Tajer_Thickbox::get_instance();
		$this->user_products        = Tajer_User_Products::get_instance();
		$this->cart_page            = Tajer_Cart_Page::get_instance();
		$this->content_seller       = Tajer_Content_Seller::get_instance();
		$this->dashboard            = Tajer_Dashboard::get_instance();
		$this->frontend_scripts     = Tajer_Frontend_Scripts::get_instance();
		$this->frontend_product     = Tajer_Frontend_Product::get_instance();
		$this->products_grid        = Tajer_Products_Grid::get_instance();
		$this->purchase_link_parser = Tajer_Purchase_Link_Parser::get_instance();
		$this->thank_you            = Tajer_Thank_You::get_instance();
		$this->bundle               = Tajer_Bundle::get_instance();
	}
}

//Correct the timezone if it was isn't
date_default_timezone_set( tajer_get_timezone_id() );

function Tajer() {
	$tajer = Tajer::get_instance();

	return $tajer;
}

Tajer();

register_activation_hook( __FILE__, 'tajer_activation' );
function tajer_activation() {
	tajer_create_protection_files( true );
}

/**
 *Loads a translation files.
 */
function tajer_load_translation() {
	load_plugin_textdomain( 'tajer', false, 'tajer/languages' );
}

add_action( 'plugins_loaded', 'tajer_load_translation' );



