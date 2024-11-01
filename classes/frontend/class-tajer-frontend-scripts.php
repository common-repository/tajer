<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class Tajer_Frontend_Scripts {

	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Tajer_Frontend_Scripts constructor.
	 */
	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 9999999999 );
	}

	function scripts() {
		if ( is_admin() ) {
			return;
		}

		do_action( 'tajer_fire_frontend_scripts' );

		$css_dependencies = $this->css_dependencies();
		$js_dependencies  = $this->js_dependencies();

		wp_enqueue_style( 'tajer-frontend-css', Tajer_URL . 'css/frontend/style.min.css', $css_dependencies );
		wp_enqueue_script( 'tajer-frontend-js', Tajer_URL . 'js/frontend/script.min.js', $js_dependencies );

		// code to declare the URL to the file handling the AJAX request
		$scheme                                                 = is_ssl() ? 'https' : 'http';
		$delete_warning_message                                 = __( 'Are you sure?', 'tajer' );
		$delete_warning_confirmation_button_text                = __( 'Yes, delete it!', 'tajer' );
		$success_delete_header_text                             = __( 'Deleted!', 'tajer' );
		$add_to_cart                                            = __( 'Add To Cart?', 'tajer' );
		$remove_from_cart                                       = __( 'Remove From Cart?', 'tajer' );
		$success_delete_message_text                            = __( 'Your product has been deleted.', 'tajer' );
		$tajer_add_to_cart_dialog_title_text                    = __( 'Working...', 'tajer' );
		$tajer_add_to_cart_dialog_repeat_text                   = __( 'I logged in, refresh now!', 'tajer' );
		$tajer_add_to_cart_dialog_error_title_text              = __( 'Login!', 'tajer' );
		$tajer_add_to_cart_dialog_exist_error_title_text        = __( 'Exist!', 'tajer' );
		$tajer_add_to_cart_dialog_success_title_text            = __( 'Added!', 'tajer' );
		$tajer_add_to_cart_dialog_message_text                  = __( '', 'tajer' );
		$tajer_add_to_cart_dialog_checkout_button_text          = __( 'Checkout', 'tajer' );
		$tajer_add_to_cart_dialog_continue_shopping_button_text = __( 'Continue Shopping', 'tajer' );
		$tajer_color                                            = tajer_get_option( 'color', 'tajer_general_settings', 'teal' );
		wp_localize_script( 'tajer-frontend-js', 'Tajer', apply_filters( 'tajer_frontend_js_localize_script_args', array(
			'color'                                            => $tajer_color,
			'add_to_cart_dialog_repeat_text'                   => $tajer_add_to_cart_dialog_repeat_text,
			'add_to_cart_dialog_success_title_text'            => $tajer_add_to_cart_dialog_success_title_text,
			'add_to_cart_dialog_exist_error_title_text'        => $tajer_add_to_cart_dialog_exist_error_title_text,
			'add_to_cart_dialog_error_title_text'              => $tajer_add_to_cart_dialog_error_title_text,
			'add_to_cart_dialog_message_text'                  => $tajer_add_to_cart_dialog_message_text,
			'add_to_cart_dialog_title_text'                    => $tajer_add_to_cart_dialog_title_text,
			'add_to_cart_dialog_continue_shopping_button_text' => $tajer_add_to_cart_dialog_continue_shopping_button_text,
			'add_to_cart_dialog_checkout_button_text'          => $tajer_add_to_cart_dialog_checkout_button_text,
			'ajaxurl'                                          => admin_url( 'admin-ajax.php', $scheme ),
			'cartPageURL'                                      => get_permalink( intval( tajer_get_option( 'cart', 'tajer_general_settings', '' ) ) ),
			'delete_warning_message'                           => $delete_warning_message,
			'delete_warning_confirmation_button_text'          => $delete_warning_confirmation_button_text,
			'success_delete_header_text'                       => $success_delete_header_text,
			'add_to_cart_text'                                 => $add_to_cart,
			'remove_from_cart_text'                            => $remove_from_cart,
			'success_delete_message_text'                      => $success_delete_message_text
		), $js_dependencies, $css_dependencies ) );

	}

	function js_dependencies() {
		$dependencies = array( 'jquery' );
//		if ( tajer_get_option( 'disable_bootstrap', 'tajer_general_settings', '' ) != 'yes' ) {
//			wp_enqueue_script( 'tajer-bootstrap-js', Tajer_URL . 'lib/bootstrap/js/bootstrap.min.js', array( 'jquery' ) );
//			$dependencies[] = 'tajer-bootstrap-js';
//		}
//		if ( tajer_get_option( 'disable_jquery_ui_spinner', 'tajer_general_settings', '' ) != 'yes' ) {
//			wp_enqueue_script( 'jquery-ui-spinner' );
//			$dependencies[] = 'jquery-ui-spinner';
//		}

		wp_enqueue_script( 'jquery-ui-spinner' );
		$dependencies[] = 'jquery-ui-spinner';

		wp_enqueue_script( 'tajer-sweetalert-js', Tajer_URL . 'lib/sweetalert/sweetalert.min.js' );
		$dependencies[] = 'tajer-sweetalert-js';


		wp_enqueue_script( 'tajer-semantic-ui-js', Tajer_URL . 'lib/semantic-ui/semantic.min.js', array( 'jquery' ) );
		$dependencies[] = 'tajer-semantic-ui-js';


//		wp_enqueue_script( 'tajer-wookmark-js', Tajer_URL . 'lib/js/wookmark.min.js', array( 'jquery' ) );
//		$dependencies[] = 'tajer-wookmark-js';


		$dependencies = apply_filters( 'tajer_frontend_js_dependencies', $dependencies );

		return $dependencies;
	}

	function css_dependencies() {
		$dependencies = array();

		$color           = tajer_get_option( 'color', 'tajer_general_settings', 'teal' );
		$secondary_color = tajer_get_option( 'secondary_color', 'tajer_general_settings', 'green' );

		wp_enqueue_style( 'tajer-sweetalert-css', Tajer_URL . 'lib/sweetalert/sweetalert.css' );
		$dependencies[] = 'tajer-sweetalert-css';


//		if ( tajer_get_option( 'disable_bootstrap', 'tajer_general_settings', '' ) != 'yes' ) {
//			wp_enqueue_style( 'tajer-bootstrap', Tajer_URL . 'lib/bootstrap/css/bootstrap.min.css' );
//			wp_enqueue_style( 'tajer-bootstrap-theme', Tajer_URL . 'lib/bootstrap/css/bootstrap-theme.min.css', array(
//				'tajer-bootstrap'
//			) );
//
//			$dependencies[] = 'tajer-bootstrap-theme';
//		}

//		if ( tajer_get_option( 'disable_jquery_ui_css', 'tajer_general_settings', '' ) != 'yes' ) {
//			wp_enqueue_style( 'tajer-jquery-ui-css', Tajer_URL . 'lib/jquery-ui/jquery-ui.min.css' );
//			$dependencies[] = 'tajer-jquery-ui-css';
//		}

//		if ( tajer_get_option( 'disable_font_awesome', 'tajer_general_settings', '' ) != 'yes' ) {
//			wp_enqueue_style( 'tajer-font-awesome', Tajer_URL . 'lib/font-awesome/css/font-awesome.min.css' );
//			$dependencies[] = 'tajer-font-awesome';
//		}


		wp_enqueue_style( 'tajer-semantic-ui', Tajer_URL . 'lib/semantic-ui/tajer-semantic-ui.css' );
		$dependencies[] = 'tajer-semantic-ui';


		wp_enqueue_style( 'tajer-color-css', Tajer_URL . 'css/frontend/colors/' . $color . '.css', array( 'tajer-semantic-ui' ) );
		$dependencies[] = 'tajer-color-css';

		wp_enqueue_style( 'tajer-secondary-color-css', Tajer_URL . 'css/frontend/colors/secondary-color/' . $secondary_color . '.css', array( 'tajer-color-css' ) );
		$dependencies[] = 'tajer-secondary-color-css';

		$dependencies = apply_filters( 'tajer_frontend_css_dependencies', $dependencies );

		return $dependencies;
	}
}
