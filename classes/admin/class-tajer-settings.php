<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class Tajer_Settings {

	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'tajer_render_general_settings', array( $this, 'tajer_render_general_settings' ), 1 );
		add_action( 'tajer_render_payment_settings', array( $this, 'tajer_render_payment_settings' ), 1 );
		add_action( 'tajer_render_emails_settings', array( $this, 'tajer_render_emails_settings' ), 1 );
		add_action( 'tajer_render_taxes_settings', array( $this, 'tajer_render_taxes_settings' ), 1 );
		add_action( 'tajer_render_tools_settings', array( $this, 'tajer_render_tools_settings' ), 1 );
		add_action( 'tajer_render_support_settings', array( $this, 'tajer_render_support_settings' ), 1 );

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 );
		add_action( 'tajer_render_tools_settings', array( $this, 'add_modal_form' ) );
		add_action( 'init', array( $this, 'export_settings_handler' ) );
		add_action( 'init', array( $this, 'export_product_handler' ) );
		add_action( 'wp_ajax_tajer_settings_file', array( $this, 'tajer_settings_file' ) );
		add_action( 'wp_ajax_tajer_save_settings', array( $this, 'save_settings' ) );

	}

	function tajer_render_general_settings() {
		echo $this->field_renderer( $this->general_fields(), 'tajer_general_settings' );
	}

	function tajer_render_support_settings() {
		echo $this->field_renderer( $this->support_fields(), 'tajer_support_settings' );
	}

	function tajer_render_tools_settings() {
		echo $this->field_renderer( $this->tools_fields(), 'tajer_tools_settings' );
	}

	function tajer_render_taxes_settings() {
		echo $this->field_renderer( $this->taxes_fields(), 'tajer_tax_settings' );
	}

	function tajer_render_emails_settings() {
		echo $this->field_renderer( $this->emails_fields(), 'tajer_emails_settings' );

	}

	function tajer_render_payment_settings() {
		echo $this->field_renderer( $this->payment_fields(), 'tajer_payment_settings' );

	}


	function tajer_settings_file() {
		//security check
		if ( ( ! ( isset( $_REQUEST['upload_nonce_field'] ) ) ) && ( ! ( wp_verify_nonce( $_REQUEST['upload_nonce_field'], 'upload_nonce' ) ) ) ) {
			wp_die( 'Security Check' );
		}

		if ( $_FILES ) {

			$importer = new Tajer_Import_Export( $_REQUEST['tajer_product_id'], $_REQUEST['tajer_importing_type'] );
			$importer->import();
			if ( $importer->get_errors() !== true ) {
				$message = apply_filters( 'tajer_admin_settings_fail_import_message', __( 'Settings haven\'t been Imported', 'tajer' ), $importer );
				$status  = false;
			} else {
				$message = apply_filters( 'tajer_admin_settings_success_import_message', __( 'Settings have been Imported', 'tajer' ), $importer );
				$status  = true;
			}

			$response = array(
				'message' => $message,
				'name'    => __( 'Settings Imported Successfully!' ),
				//todo Mohammed what is going here! all these are messages?
				'status'  => apply_filters( 'tajer_admin_settings_import_status', $status, $importer )
			);
			tajer_response( $response );
		}

	}

	function admin_menu() {
		$tajer_settings_page_hook_suffix = add_submenu_page( 'edit.php?post_type=tajer_products', 'Settings', 'Settings', apply_filters( 'tajer_settings_capability', 'manage_options' ), 'tajer_settings', array(
			$this,
			'page'
		) );

		add_action( 'admin_print_scripts-' . $tajer_settings_page_hook_suffix, array(
			$this,
			'admin_scripts'
		) );
	}

	function admin_scripts() {
		wp_enqueue_style( 'tajer-semantic-ui', Tajer_URL . 'lib/semantic-ui/tajer-semantic-ui.css' );
		wp_enqueue_style( 'chosen-jquery-css', Tajer_URL . 'lib/chosen_v1.2.0/chosen.min.css' );
		wp_enqueue_style( 'tajer-jquery-ui-css', Tajer_URL . 'lib/jquery-ui/jquery-ui.min.css' );
		wp_enqueue_style( 'tajer-admin-css', Tajer_URL . 'css/admin/tajer-admin.css', array(
			'chosen-jquery-css',
			'tajer-semantic-ui'
		) );
		wp_enqueue_style( 'tajer-settings-css', Tajer_URL . 'css/admin/settings.css', array(
			'tajer-semantic-ui'
		) );

		wp_enqueue_media();

		wp_enqueue_script( 'tajer-jquery-ui-widget', Tajer_URL . 'lib/jQuery-File-Upload-master/js/vendor/jquery.ui.widget.js', array( 'jquery' ) );
		wp_enqueue_script( 'tajer-iframe-transport', Tajer_URL . 'lib/jQuery-File-Upload-master/js/jquery.iframe-transport.js', array( 'tajer-jquery-ui-widget' ) );
		wp_enqueue_script( 'tajer-fileupload', Tajer_URL . 'lib/jQuery-File-Upload-master/js/jquery.fileupload.js', array( 'tajer-iframe-transport' ) );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'tajer-semantic-ui-js', Tajer_URL . 'lib/semantic-ui/semantic.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'chosen-jquery-js', Tajer_URL . 'lib/chosen_v1.2.0/chosen.jquery.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'tajer-setting-js', Tajer_URL . 'js/admin/settings.js', array(
			'tajer-semantic-ui-js',
			'tajer-fileupload',
			'chosen-jquery-js'
		) );
	}

	function add_modal_form() {
		tajer_get_template_part( 'admin-settings-modal-form' );
	}

	function general_fields() {
		$currencies = new Tajer_Currency();
		$fields     = array(
			array(
				'label'   => __( 'Cart Page', 'tajer' ),
				'name'    => 'tajer_general_settings[cart]',
				'options' => tajer_get_pages(),
				'type'    => 'select',
				'help'    => __( 'Select the page that contains [tajer_cart] shortcode.', 'tajer' )
			),
			array(
				'label'   => __( 'Thank You Page', 'tajer' ),
				'name'    => 'tajer_general_settings[thank_you_page]',
				'options' => tajer_get_pages(),
				'type'    => 'select',
				'help'    => __( 'Select the page that contains [tajer_thank_you] shortcode.', 'tajer' )
			),
			array(
				'label'   => __( 'Dashboard Page', 'tajer' ),
				'name'    => 'tajer_general_settings[dashboard_page]',
				'options' => tajer_get_pages(),
				'type'    => 'select',
				'help'    => __( 'Select the page that contains [tajer_dashboard] shortcode.', 'tajer' )
			),
			array(
				'label' => __( 'Continue Shopping Button Link', 'tajer' ),
				'name'  => 'tajer_general_settings[continue_shopping]',
				'type'  => 'text',
				'help'  => __( 'For example http://example.com/store', 'tajer' )
			),
			array(
				'label'   => __( 'Login Page', 'tajer' ),
				'name'    => 'tajer_general_settings[login_page]',
				'options' => tajer_get_pages(),
				'type'    => 'select',
				'help'    => __( 'Select your website login page.', 'tajer' )
			),
			array(
				'label'   => __( 'Registration Page', 'tajer' ),
				'name'    => 'tajer_general_settings[registration_page]',
				'options' => tajer_get_pages(),
				'type'    => 'select',
				'help'    => __( 'Select your website registration page.', 'tajer' )
			),
			array(
				'label' => __( 'User Country Meta Key', 'tajer' ),
				'name'  => 'tajer_general_settings[country_meta_key]',
				'type'  => 'text',
				'help'  => __( 'In order to make Tajer work with any registration method just enter country meta key of the country custom field that the user used when registered(optional).', 'tajer' )
			),
			array(
				'label' => __( 'User State / Province Meta Key', 'tajer' ),
				'name'  => 'tajer_general_settings[state_meta_key]',
				'type'  => 'text',
				'help'  => __( 'In order to make Tajer work with any registration method just enter country meta key of the state / province custom field that the user used when registered(optional).', 'tajer' )
			),
			array(
				'label' => __( 'User City/Town Meta Key', 'tajer' ),
				'name'  => 'tajer_general_settings[city_meta_key]',
				'type'  => 'text',
				'help'  => __( 'In order to make Tajer work with any registration method just enter country meta key of the city/town custom field that the user used when registered(optional).', 'tajer' )
			),
			array(
				'label' => __( 'User Postcode Meta Key', 'tajer' ),
				'name'  => 'tajer_general_settings[postcode_meta_key]',
				'type'  => 'text',
				'help'  => __( 'In order to make Tajer work with any registration method just enter country meta key of the postcode custom field that the user used when registered(optional).', 'tajer' )
			),
			array(
				'label' => __( 'User Company Meta Key', 'tajer' ),
				'name'  => 'tajer_general_settings[company_meta_key]',
				'type'  => 'text',
				'help'  => __( 'In order to make Tajer work with any registration method just enter country meta key of the company custom field that the user used when registered(optional).', 'tajer' )
			),
			array(
				'label' => __( 'User Address 1 Meta Key', 'tajer' ),
				'name'  => 'tajer_general_settings[address_1_meta_key]',
				'type'  => 'text',
				'help'  => __( 'In order to make Tajer work with any registration method just enter country meta key of the address 1 custom field that the user used when registered(optional).', 'tajer' )
			),
			array(
				'label' => __( 'User Address 2 Meta Key', 'tajer' ),
				'name'  => 'tajer_general_settings[address_2_meta_key]',
				'type'  => 'text',
				'help'  => __( 'In order to make Tajer work with any registration method just enter country meta key of the address 2 custom field that the user used when registered(optional).', 'tajer' )
			),
			array(
				'label' => __( 'User Phone Meta Key', 'tajer' ),
				'name'  => 'tajer_general_settings[phone_meta_key]',
				'type'  => 'text',
				'help'  => __( 'In order to make Tajer work with any registration method just enter country meta key of the phone custom field that the user used when registered(optional).', 'tajer' )
			),
			array(
				'label'   => __( 'Primary Color', 'tajer' ),
				'name'    => 'tajer_general_settings[color]',
				'options' => tajer_colors(),
				'type'    => 'select',
				'help'    => __( 'Tajer general color.', 'tajer' )
			),
			array(
				'label'   => __( 'Secondary Color', 'tajer' ),
				'name'    => 'tajer_general_settings[secondary_color]',
				'options' => tajer_colors(),
				'type'    => 'select',
				'help'    => __( 'Tajer secondary color.', 'tajer' )
			),
			array(
				'label' => __( 'Restrict Trial Versions By User IP', 'tajer' ),
				'name'  => 'tajer_general_settings[restrict_by_ip]',
				'type'  => 'checkbox',
				'help'  => __( 'Tajer will check if current user IP used this trial before, if the user used it then restrict this user from use this trial version again', 'tajer' )
			),
			array(
				'label' => __( 'Restrict Trial Versions By User Email', 'tajer' ),
				'name'  => 'tajer_general_settings[restrict_by_email]',
				'type'  => 'checkbox',
				'help'  => __( 'Tajer will check if current user email used this trial before, if the user used it then restrict this user from use this trial version again', 'tajer' )
			),
			array(
				'label' => __( 'Enable The Upgrade Links At The Frontend Dashboard', 'tajer' ),
				'name'  => 'tajer_general_settings[enable_upgrade]',
				'type'  => 'checkbox'
			),
			array(
				'label' => __( 'Enable The Recurring Links At The Frontend Dashboard', 'tajer' ),
				'name'  => 'tajer_general_settings[enable_recurring]',
				'type'  => 'checkbox'
			),
			array(
				'label' => __( 'Disable The Download Links At The Frontend Dashboard', 'tajer' ),
				'name'  => 'tajer_general_settings[disable_download]',
				'type'  => 'checkbox'

			),
			array(
				'label' => __( 'Allow The Users To Delete Their Products', 'tajer' ),
				'name'  => 'tajer_general_settings[enable_delete_product]',
				'type'  => 'checkbox'
			),
			array(
				'label' => __( 'Remove all data when delete Tajer?', 'tajer' ),
				'name'  => 'tajer_general_settings[uninstall_on_delete]',
				'type'  => 'checkbox',
				'help'  => __( 'Check this if you want to delete all data when delete Tajer.', 'tajer' )
			),
//			array(
//				'label' => __( 'Disable Firing Bootstrap At The Frontend', 'tajer' ),
//				'name'  => 'tajer_general_settings[disable_bootstrap]',
//				'type'  => 'checkbox',
//				'help'  => __( 'Please note that Tajer depends on this library so please be sure to fire your own library before Tajer fires its styles & JS files, in order to do that you can simply use wp_enqueue_scripts action with priority less than 10', 'tajer' )
//			),
//			array(
//				'label' => __( 'Disable Firing jQuery UI CSS At The Frontend', 'tajer' ),
//				'name'  => 'tajer_general_settings[disable_jquery_ui_css]',
//				'type'  => 'checkbox',
//				'help'  => __( 'Please note that Tajer depends on this library so please be sure to fire your own library before Tajer fires its styles & JS files, in order to do that you can simply use wp_enqueue_scripts action with priority less than 10', 'tajer' )
//			),
//			array(
//				'label' => __( 'Disable Firing Font Awesome At The Frontend', 'tajer' ),
//				'name'  => 'tajer_general_settings[disable_font_awesome]',
//				'type'  => 'checkbox',
//				'help'  => __( 'Please note that Tajer depends on this library so please be sure to fire your own library before Tajer fires its styles & JS files, in order to do that you can simply use wp_enqueue_scripts action with priority less than 10', 'tajer' )
//			),
//			array(
//				'label' => __( 'Disable Firing jQuery UI Spinner At The Frontend', 'tajer' ),
//				'name'  => 'tajer_general_settings[disable_jquery_ui_spinner]',
//				'type'  => 'checkbox',
//				'help'  => __( 'Please note that Tajer depends on this library so please be sure to fire your own library before Tajer fires its styles & JS files, in order to do that you can simply use wp_enqueue_scripts action with priority less than 10', 'tajer' )
//			),
			array(
				'label' => __( 'Currency Settings', 'tajer' ),
				'type'  => 'header'
			),
			array(
				'label'   => __( 'Currency', 'tajer' ),
				'name'    => 'tajer_general_settings[currency]',
				'options' => $currencies->currency_codes_array(),
				'type'    => 'select',
				'help'    => __( 'Please help us add the missing currencies symbols if there is any, if you know a missing symbol of a currency please contact us and we will add it immediately. Please note that some payment gateways have currency restrictions.', 'tajer' )
			),
			array(
				'label'   => __( 'Currency Position', 'tajer' ),
				'name'    => 'tajer_general_settings[currency_position]',
				'options' => array(
					'before' => __( 'Before - &#36;10', 'tajer' ),
					'after'  => __( 'After - 10&#36;', 'tajer' )
				),
				'type'    => 'select',
				'help'    => __( 'Choose the location of the currency sign.', 'tajer' )
			)
		);

		$fields = apply_filters( 'tajer_general_settings_fields', $fields );

		return $fields;
	}

	function payment_fields() {
		$fields = array(
			array(
				'label'   => __( 'Payment Gateways', 'tajer' ),
				'name'    => 'tajer_payment_settings[payment_gateways]',
				'options' => tajer_get_admin_payment_gateways(),
				'type'    => 'multi_check'
			),
			array(
				'label'   => __( 'Default Gateway', 'tajer' ),
				'name'    => 'tajer_payment_settings[default_gateway]',
				'options' => tajer_get_admin_payment_gateways(),
				'type'    => 'select'
			)
		);

		$fields = apply_filters( 'tajer_payment_settings_fields', $fields );

		return $fields;
	}

	function support_fields() {
		$fields = array(
			array(
				'type'   => 'message',
				'header' => 'Thank you for choosing Tajer!',
				'body'   => 'Don\'t forget to take a look at <a href="https://mostasharoon.org/tajer-documentation/" target="_blank">Tajer documentation</a>, also if you faced any issue don\'t be hesitate to create a new thread in the support forum, we will do our best to help you.'
			)
		);

		$fields = apply_filters( 'tajer_support_settings_fields', $fields );

		return $fields;
	}

	function taxes_fields() {
		$fields = array(
			array(
				'label' => __( 'Enable Taxes', 'tajer' ),
				'name'  => 'tajer_tax_settings[enable_taxes]',
				'type'  => 'checkbox'
			),
			array(
				'label' => __( 'Fallback Tax Rate', 'tajer' ),
				'name'  => 'tajer_tax_settings[fallback_tax_rate]',
				'type'  => 'text'
			),
			array(
				'label'   => __( 'Prices entered with tax', 'tajer' ),
				'name'    => 'tajer_tax_settings[prices_include_tax]',
				'options' => array(
					'yes' => __( 'Yes, I will enter prices inclusive of tax', 'tajer' ),
					'no'  => __( 'No, I will enter prices exclusive of tax', 'tajer' )
				),
				'type'    => 'radio'
			),
			array(
				'label'   => __( 'Display during cart', 'tajer' ),
				'name'    => 'tajer_tax_settings[cart_include_tax]',
				'options' => array( 'yes' => __( 'Including tax', 'tajer' ), 'no' => __( 'Excluding tax', 'tajer' ) ),
				'type'    => 'select'
			),
			array(
				'label' => __( 'Display Tax Rate on Prices', 'tajer' ),
				'name'  => 'tajer_tax_settings[display_tax_rate]',
				'type'  => 'checkbox'
			),
			array(
				'label'       => __( 'Tax Rates', 'tajer' ),
				'name'        => 'tajer_tax_settings[tax_rates]',
				'type'        => 'custom',
				'custom_type' => 'tax_rates'
			)
		);

		$fields = apply_filters( 'tajer_taxes_settings_fields', $fields );

		return $fields;
	}

	function export_settings_handler() {
		//todo Mohammed adding another nonce security check layer here.
		if ( ( isset( $_REQUEST['tajer_export_settings'] ) ) && ( $_REQUEST['tajer_export_settings'] == 'Export' ) ) {
			$obj = new Tajer_Import_Export( 0, 'settings' );
			$obj->export();
		}
	}

	function export_product_handler() {
		//todo Mohammed adding another nonce security check layer here.
		if ( ( isset( $_REQUEST['tajer-export-product'] ) ) && ( $_REQUEST['tajer-export-product'] == 'Export Product' ) ) {
			$obj = new Tajer_Import_Export( $_REQUEST['tajer_tools_settings']['export_product'] );
			$obj->export();
		}
	}

	function tools_fields() {
		$fields = array(
//			array(
//				'label'       => __( 'Import & Export', 'tajer' ),
//				'name'        => 'tajer_tools_settings[tools]',
//				'type'        => 'custom',
//				'custom_type' => 'tools'
//			)
			array(
				'label'       => __( 'Export Plugin Settings', 'tajer' ),
				'name'        => 'tajer_export_settings',
				'type'        => 'button',
				'classes'     => 'small',
				'button_type' => 'submit',
				'value'       => 'Export'
			),
			array(
				'label'       => __( 'Import Settings/Product', 'tajer' ),
				'name'        => 'tajer_tools_settings[tajer_import]',
				'type'        => 'button',
				'button_type' => 'button',
				'classes'     => 'small',
				'value'       => 'Import'
			),
			array(
				'label'       => __( 'Export Product', 'tajer' ),
				'name'        => 'tajer_tools_settings[export_product]',
				'type'        => 'custom',
				'custom_type' => 'export_product'
			)
//			array(
//				'label'       => __( 'Import Product', 'tajer' ),
//				'name'        => 'tajer_tools_settings[import_product]',
//				'type'        => 'button',
//				'button_type' => 'button',
//				'classes'     => '',
//				'attributes'  => 'data-toggle="modal" data-target="#gridSystemModal"',
//				'value'       => 'Import'
//			)
		);

		$fields = apply_filters( 'tajer_tools_settings_fields', $fields );

		return $fields;
	}

	function emails_fields() {
		$fields = array(
			array(
				'label' => __( 'Enable Purchase Receipt Email', 'tajer' ),
				'name'  => 'tajer_emails_settings[enable_purchase_receipt_notification]',
				'type'  => 'checkbox',
				'help'  => ''
			),
			array(
				'label' => __( 'Purchase Receipt Email Subject', 'tajer' ),
				'name'  => 'tajer_emails_settings[purchase_receipt_email_subject]',
				'type'  => 'text',
				'help'  => __( 'Allowed template tags: {name}, {username}, {order_number}, {price}, {site_name}, {site_link}, {dashboard_link}', 'tajer' )
			),
			array(
				'label' => __( 'Purchase Receipt Email Body', 'tajer' ),
				'name'  => 'tajer_emails_settings[purchase_receipt_email_body]',
				'type'  => 'textarea',
				'help'  => __( 'Allowed template tags: {name}, {username}, {order_number}, {price}, {site_name}, {site_link}, {dashboard_link}', 'tajer' )
			),
			array(
				'label' => __( 'Enable Admin Sale Notification Emails', 'tajer' ),
				'name'  => 'tajer_emails_settings[enable_sale_notification]',
				'type'  => 'checkbox',
				'help'  => ''
			),
			array(
				'label' => __( 'New Sale Notification Email Subject', 'tajer' ),
				'name'  => 'tajer_emails_settings[new_sale_notification_subject]',
				'type'  => 'text',
				'help'  => __( 'Allowed template tags: {name}, {username}, {order_number}, {price}, {site_name}, {site_link}, {dashboard_link}', 'tajer' )
			),
			array(
				'label' => __( 'New Sale Notification Email Body', 'tajer' ),
				'name'  => 'tajer_emails_settings[new_sale_notification_body]',
				'type'  => 'textarea',
				'help'  => __( 'Allowed template tags: {name}, {username}, {order_number}, {price}, {site_name}, {site_link}, {dashboard_link}', 'tajer' )
			),
			array(
				'label' => __( 'Enable Expiration Notification Emails', 'tajer' ),
				'name'  => 'tajer_emails_settings[enable_expiration_notification_emails]',
				'type'  => 'checkbox',
				'help'  => ''
			),
			array(
				'label' => __( 'Expiration Notification Email Should Send Before The Expiration Date By', 'tajer' ),
				'name'  => 'tajer_emails_settings[expiration_email_period]',
				'type'  => 'text',
				'help'  => __( 'In Days', 'tajer' )
			),
			array(
				'label' => __( 'Expiration Notification Email Subject', 'tajer' ),
				'name'  => 'tajer_emails_settings[expiration_notification_email_subject]',
				'type'  => 'text',
				'help'  => __( 'Allowed template tags: {name}, {username}, {order_number}, {expiration_date}, {number_of_downloads}, {product_name}, {option_name}, {site_name}, {site_link}, {dashboard_link}', 'tajer' )
			),
			array(
				'label' => __( 'Expiration Notification Email Body', 'tajer' ),
				'name'  => 'tajer_emails_settings[expiration_notification_email_body]',
				'type'  => 'textarea',
				'help'  => __( 'Allowed template tags: {name}, {username}, {order_number}, {expiration_date}, {number_of_downloads}, {product_name}, {option_name}, {site_name}, {site_link}, {dashboard_link}', 'tajer' )
			)
		);

		$fields = apply_filters( 'tajer_emails_settings_fields', $fields );

		return $fields;
	}

	function page() {
		tajer_get_template_part( 'admin-settings-page' );
	}

	function field_renderer( $fields, $settings_section ) {
		$html = '';

		if ( is_array( $fields ) ) {

			foreach ( $fields as $field ) {
				//Capturing text between square brackets
				if ( isset( $field['name'] ) ) {
					preg_match( "/\[(.*?)\]/", $field['name'], $matches );
				}

//			$val = tajer_get_option( $matches[1], $settings_section, '' );
				$val  = isset( $matches[1] ) ? tajer_get_option( $matches[1], $settings_section, '' ) : '';
				$help = ( isset( $field['help'] ) && ! empty( $field['help'] ) ) ? 'data-content="' . $field['help'] . '"' : '';
				switch ( $field['type'] ) {
					case 'password':
						$html .= '<div class="field tajer-field" ' . $help . '>';
						$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
						$html .= '<input type="password" name="' . $field['name'] . '" value="' . $val . '" id="' . $field['name'] . '" placeholder="">';
						$html .= '</div>';
						break;

					case 'text':
						$html .= '<div class="field tajer-field" ' . $help . '>';
						$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
						$html .= '<input type="text" name="' . $field['name'] . '" value="' . ( $val ? $val : ( ( isset( $field['default'] ) && $field['default'] ) ? $field['default'] : '' ) ) . '" id="' . $field['name'] . '" placeholder="">';
						$html .= '</div>';
						break;

					case 'input':
						$html .= '<div class="field tajer-field" ' . $help . '>';
						$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
						$html .= '<div class="ui fluid action input">';
						$html .= '<input type="text" name="' . $field['name'] . '" value="' . get_option( 'admin_email' ) . '" id="' . $field['name'] . '" placeholder="">';
						$html .= '<div class="ui button">' . $field['action'] . '</div>';
						$html .= '</div>';
						$html .= '</div>';
						break;

					case 'textarea':
						$html .= '<div class="field tajer-field" ' . $help . '>';
						$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
						$html .= '<textarea name="' . $field['name'] . '" id="' . $field['name'] . '" placeholder="" rows="10">' . $val . '</textarea>';
						$html .= '</div>';

//					ob_start();
//					$content = '';
//					$editor_id = preg_replace('~([^a-zA-Z\n\r()]+)~', '_', $field['name']);
//					wp_editor( $content, $editor_id );
//					$html .= ob_get_contents();
//					ob_end_clean();

						break;

					case 'email':
						$html .= '<div class="field tajer-field" ' . $help . '>';
						$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
						$html .= '<input type="email" name="' . $field['name'] . '" value="' . $val . '" id="' . $field['name'] . '" placeholder="">';
						$html .= '</div>';
						break;

					case 'file':
						$html .= '<div class="field tajer-field" ' . $help . '>';
						$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
						$html .= '<input type="file" name="' . $field['name'] . '" id="' . $field['name'] . '">';
						$html .= '</div>';
						break;

					case 'button':
						$html .= '<div class="two fields tajer-field" ' . $help . '>';
						$html .= '<div class="eight wide field">';
						$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
						$html .= '</div>';
						$html .= '<div class="eight wide field">';

						$html .= '<button type="' . $field['button_type'] . '" ' . ( isset( $field['attributes'] ) ? $field['attributes'] : '' ) . ' name="' . $field['name'] . '" value="' . $field['value'] . '" class="ui button ' . ( isset( $field['classes'] ) ? $field['classes'] : '' ) . '">' . $field['value'] . '</button>';

						$html .= '</div>';
						$html .= '</div>';

						break;

					case 'media_uploader_image':
						$html .= '<div class="two fields tajer-field" ' . $help . '>';
						$html .= '<div class="eight wide field">';
						$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
						$html .= '</div>';
						$html .= '<div class="eight wide field">';

						$html .= '<div class="ui fluid image" ' . ( ( ! $val ? 'style="display: none;"' : '' ) ) . '>';
						$html .= '<a class="ui left corner label tajer-remove-image">';
						$html .= '<i class="remove icon"></i>';
						$html .= '</a>';
						$html .= '<img src="' . wp_get_attachment_url( (int) $val ) . '">';
						$html .= '</div>';

						$html .= '<button type="' . $field['button_type'] . '" ' . ( isset( $field['attributes'] ) ? $field['attributes'] : '' ) . ' value="' . $field['value'] . '" class="ui button ' . ( ( ! $val ? '' : 'bottom attached' ) ) . ' fluid ' . ( isset( $field['classes'] ) ? $field['classes'] : '' ) . '">' . $field['value'] . '</button>';

						$html .= '<input type="hidden" name="' . $field['name'] . '" value="' . $val . '">';
						$html .= '</div>';
						$html .= '</div>';

						break;

					case 'select':
						$html .= '<div class="field tajer-field" ' . $help . '>';
						$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
						$html .= '<select class="ui dropdown" name="' . $field['name'] . '" id="' . $field['name'] . '">';
						foreach ( $field['options'] as $id => $title ) {
							$html .= '<option ' . selected( $val, $id, false ) . ' value="' . $id . '">' . $title . '</option>';
						}
						$html .= '</select>';
						$html .= '</div>';
						break;

					case 'multi_select':
						$html .= '<div class="field tajer-field" ' . $help . '>';
						$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
						$html .= '<select class="ui dropdown" name="' . $field['name'] . '[]" id="' . $field['name'] . '" multiple>';
						foreach ( $field['options'] as $id => $title ) {
							$selected = '';
							foreach ( $val as $single_val ) {
								if ( ! empty( $selected ) ) {
									continue;
								}
								$selected = selected( (int) $id, (int) $single_val, false );
							}
							$html .= '<option ' . $selected . ' value="' . $id . '">' . $title . '</option>';
						}
						$html .= '</select>';
						$html .= '</div>';
						break;


					case 'radio':
						foreach ( $field['options'] as $id => $title ) {

							$html .= '<div class="field tajer-field" ' . $help . '>';
							$html .= '<div class="ui radio checkbox">';
							$html .= '<input type="radio" name="' . $field['name'] . '" value="' . $id . '" ' . checked( $val, $id, false ) . '>';
							$html .= '<label>' . $title . '</label>';
							$html .= '</div>';
							$html .= '</div>';
						}
						break;
					case 'radios':
						$html .= '<div class="grouped fields tajer-field" ' . $help . '>';
						$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';

						foreach ( $field['options'] as $id => $title ) {

							$html .= '<div class="field">';
							$html .= '<div class="ui radio checkbox">';
							$html .= '<input type="radio" name="' . $field['name'] . '" value="' . $id . '" ' . checked( $val, $id, false ) . '>';
							$html .= '<label>' . $title . '</label>';
							$html .= '</div>';
							$html .= '</div>';
						}
						$html .= '</div>';
						break;
					case 'checkbox':
						$html .= '<div class="field tajer-field" ' . $help . '>';
						$html .= '<div class="ui checkbox">';
						$html .= '<input ' . checked( $val, 'yes', false ) . ' type="checkbox" value="yes" name="' . $field['name'] . '"> ';
						$html .= '<label>' . $field['label'] . '</label>';
						$html .= '</div>';
						$html .= '</div>';
						break;
					case 'multi_check':
						$html .= '<div class="two fields tajer-field" ' . $help . '>';
						$html .= '<div class="eight wide field">';
						$html .= '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
						$html .= '</div>';
						$html .= '<div class="eight wide field">';
						$html .= '<div class="grouped fields">';


						foreach ( $field['options'] as $id => $title ) {

							$checked = '';
							if ( is_array( $val ) ) {
								foreach ( $val as $single_val ) {
									if ( ! empty( $checked ) ) {
										continue;
									}
									$checked = checked( $id, $single_val, false );
								}
							}

							$html .= '<div class="field">';
							$html .= '<div class="ui checkbox">';
							$html .= '<input ' . $checked . ' type="checkbox" value="' . $id . '" name="' . $field['name'] . '[' . $id . ']">';
							$html .= '<label>' . $title . '</label>';
							$html .= '</div>';
							$html .= '</div>';
						}

						$html .= '</div>';
						$html .= '</div>';
						$html .= '</div>';
						break;
					case 'header':
						$html .= '<h4 class="ui dividing header tajer-field">' . $field['label'] . '</h4>';
//						$html .= '<h2 class="ui header">';
//						$html .= '<i class="settings icon"></i>';
//						$html .= '<div class="field" ' . $help . '>';
//						$html .= '<div class="content">' . $field['label'] . '</div>';
//						$html .= '</h2>';

						break;
					case 'message':
						$html .= '<div class="ui message">';
						$html .= '<div class="header">';
						$html .= $field['header'];
						$html .= '</div>';
						$html .= '<p>' . $field['body'] . '</p>';
						$html .= '</div>';

						break;
					case 'custom':
						//Incompatible with PHP7
//						$html .= $this->$field['custom_type']( $field, $val );
						$html .= call_user_func_array( array( $this, $field['custom_type'] ), array( $field, $val ) );
						break;
					default:
						$html .= apply_filters( 'tajer_settings_' . $field['type'] . '_field_renderer', $field, $val );
						break;
				}
			}
		}

		return $html = apply_filters( 'tajer_settings_field_renderer_html', $html, $fields, $settings_section );
	}

	function get_products() {
		$products = tajer_get_products();
		unset( $products['all'] );
		$html = '';

		foreach ( $products as $id => $product ) {
			$html .= '<option value="' . $id . '">' . $product . '</option>';
		}

		return $html;
	}

	function export_product( $field, $val ) {
		$html = '';
		$html .= '<div class="two fields tajer-field" data-content="' . __( 'Select the product that you want to export its settings. Product title, product content, product excerpt, and all the product settings will be exported except the files.', 'tajer' ) . '">';

		$html .= '<div class="eight wide field">';
		$html .= '<label for="tajer-export-product">' . $field['label'] . '</label>';
		$html .= '</div>';

		$html .= '<div class="eight wide field">';


		$html .= '<div class="field tajer-field">';
		$html .= '<label></label>';


		$html .= '<select id="tajer-export-product" class="ui dropdown" name="' . $field['name'] . '">';
		$html .= $this->get_products();
		$html .= '</select>';
		$html .= '</div>';

		$html .= '<button type="submit" name="tajer-export-product" value="Export Product" class="ui button">' . __( 'Export', 'tajer' ) . '</button>';
		$html .= '</div>';
		$html .= '</div>';

		$html = apply_filters( 'tajer_settings_export_product_html', $html, $field, $val );

		return $html;
	}

//	function tools( $field, $val ) {
//		$html = '';
//		$html .= '<div class="tajer_tax_rates_div">';
//
//
//		return $html;
//	}

	function tax_rates( $field, $val ) {
		$html = '';
		$html .= '<div class="tajer_tax_rates_div">';
		$html .= '<h4 class="ui dividing header tajer-field" data-content="' . __( 'Enter tax rates for specific regions.', 'tajer' ) . '">' . __( 'Tax Rates' ) . '</h4>';
		$html .= '<table class="ui basic table" id="tajer_tax_rates_table">';

		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="center aligned">' . __( 'Country', 'tajer' ) . '</th>';
		$html .= '<th class="center aligned">' . __( 'State / Province', 'tajer' ) . '</th>';
		$html .= '<th class="center aligned">' . __( 'Country Wide', 'tajer' ) . '</th>';
		$html .= '<th class="center aligned">' . __( 'Rate', 'tajer' ) . '</th>';
		$html .= '<th class="center aligned">' . __( 'Remove', 'tajer' ) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';

		$html .= '<tbody>';
		if ( $val && ( ! empty( $val ) ) ) {
			foreach ( $val as $id => $detail ) {
				$html .= '<tr class="tajer_repeatable_row" data-index="' . $id . '">';
				$html .= '<td class="center aligned">' . $this->countries( 'tajer_tax_settings[tax_rates][' . $id . '][country]', $detail['country'] ) . '</td>';
				$html .= '<td class="center aligned"><input type="text" class="tajer_tax_state tajer_tax_field" name="tajer_tax_settings[tax_rates][' . $id . '][state]" value="' . $detail['state'] . '" /></td>';
				$html .= '<td class="center aligned"><div class="ui checkbox"><input type="checkbox" class="tajer_tax_global tajer_tax_field" name="tajer_tax_settings[tax_rates][' . $id . '][global]" ' . checked( 'yes', ( isset( $detail['global'] ) ? $detail['global'] : '' ), false ) . ' value="' . ( isset( $detail['global'] ) ? $detail['global'] : '' ) . '"><label></label></div></td>';
				$html .= '<td class="center aligned"><input type="text" class="tajer_tax_rate tajer_tax_field" name="tajer_tax_settings[tax_rates][' . $id . '][rate]" value="' . $detail['rate'] . '" /></td>';
				$html .= '<td class="center aligned"><button class="tajer_remove_tax_rate ui red basic button">' . __( 'Remove Rate', 'tajer' ) . '</button></td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr class="tajer_repeatable_row" data-index="1">';
			$html .= '<td class="center aligned">' . $this->countries( 'tajer_tax_settings[tax_rates][1][country]', '' ) . '</td>';
			$html .= '<td class="center aligned"><input type="text" class="tajer_tax_state tajer_tax_field" name="tajer_tax_settings[tax_rates][1][state]" value="" /></td>';
			$html .= '<td class="center aligned"><div class="ui checkbox"><input type="checkbox" class="tajer_tax_global tajer_tax_field" name="tajer_tax_settings[tax_rates][1][global]" value="yes"><label></label></div></td>';
			$html .= '<td class="center aligned"><input type="text" class="tajer_tax_rate tajer_tax_field" name="tajer_tax_settings[tax_rates][1][rate]" value="" /></td>';
			$html .= '<td class="center aligned"><button class="tajer_remove_tax_rate ui red basic button">' . __( 'Remove Rate', 'tajer' ) . '</button></td>';
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '<br>';
		$html .= '<button class="tajer_add_tax_rate ui teal basic button">' . __( 'Add Tax Rate', 'tajer' ) . '</button>';
		$html .= '</div>';

		$html = apply_filters( 'tajer_settings_tax_rates_html', $html, $field, $val );

		return $html;
	}


	function save_settings() {
		if ( isset( $_POST['tajer_save_settings_nonce_field'] ) && wp_verify_nonce( $_POST['tajer_save_settings_nonce_field'], 'tajer_save_settings_nonce' ) ) {
			tajer_create_protection_files( true );
			do_action( 'tajer_save_settings' );
//			$sanitized_data = array_map( 'sanitize_text_field', $_POST['tajer_general_settings'] );
//			$sanitized_payment_data = array_map( 'sanitize_text_field', $_POST['tajer_payment_settings'] );
			$sanitized_support_data = array_map( 'sanitize_text_field', $_POST['tajer_support_settings'] );
			$tajer_emails_settings  = array_map( 'wp_strip_all_tags', $_POST['tajer_emails_settings'] );

			//because $_POST['tajer_tax_settings'] are multidimentional array we will use this method to sanitize it from http://stackoverflow.com/questions/4085623/array-map-for-multidimensional-arrays
			array_walk_recursive( $_POST['tajer_tax_settings'], array( $this, 'special_sanitize' ) );
			array_walk_recursive( $_POST['tajer_payment_settings'], array( $this, 'special_sanitize' ) );
//			$sanitized_tax_data     = array_map( 'wp_strip_all_tags', $_POST['tajer_tax_settings'] );
			update_option( 'tajer_general_settings', $_POST['tajer_general_settings'] );
			update_option( 'tajer_payment_settings', $_POST['tajer_payment_settings'] );
			update_option( 'tajer_support_settings', $sanitized_support_data );
			update_option( 'tajer_emails_settings', $tajer_emails_settings );
			update_option( 'tajer_tax_settings', $_POST['tajer_tax_settings'] );
		}
		$response = apply_filters( 'tajer_settings_response', array(
			'error_messages'   => apply_filters( 'tajer_settings_error_messages', array() ),
			'success_messages' => apply_filters( 'tajer_settings_success_messages', array() ),
			'status'           => apply_filters( 'tajer_settings_response_status', '' )
		) );
		tajer_response( $response );
	}

	function special_sanitize( &$item, $key ) {
		$item = sanitize_text_field( $item );
	}

	function countries( $name, $oldValue ) {
		$html      = '';
		$countries = tajer_countries();
		$html .= '<select class="ui search dropdown tajer_tax_field" name="' . $name . '">';
		foreach ( $countries as $country_code => $country_name ) {
			$html .= '<option value="' . $country_code . '" ' . selected( $oldValue, $country_code, false ) . '>' . $country_name . '</option>';
		}
		$html .= '</select>';

		$html = apply_filters( 'tajer_settings_countries_html', $html, $name, $oldValue );

		return $html;
	}
}
