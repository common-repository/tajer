<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
/**
 * PayPal Standard Gateway
 *
 * @package     Tajer
 * @subpackage  Gateways
 * @since       1.0
 */

/**
 * Register PayPal Gateway
 *
 * @since 1.0
 *
 * @param $gateways
 *
 * @internal param array $purchase_data Purchase Data
 *
 */
function tajer_register_paypal_gateway( $gateways ) {
	$gateways['paypal'] = array(
		'admin_label'    => __( 'PayPal', 'tajer' ),
		'checkout_label' => __( 'PayPal', 'tajer' )
	);

	return $gateways;
}

add_filter( 'tajer_payment_gateways', 'tajer_register_paypal_gateway' );


/**
 * Register PayPal Gateway Settings
 *
 * @param $fields
 *
 * @return array
 */
function tajer_paypal_payment_gateway_settings_fields( $fields ) {
	$paypal_fields = array(
		array(
			'label' => __( 'PayPal Settings', 'tajer' ),
			'type'  => 'header'
		),
		array(
			'label' => __( 'Test Mode', 'tajer' ),
			'name'  => 'tajer_payment_settings[paypal_test_mode]',
			'type'  => 'checkbox',
			'help'  => __( 'Enable or disable PayPal demo mode.', 'tajer' )
		),
		array(
			'label' => __( 'PayPal Email', 'tajer' ),
			'name'  => 'tajer_payment_settings[paypal_email]',
			'type'  => 'email',
			'help'  => __( 'Enter your PayPal account\'s email', 'tajer' )
		),
		array(
			'label' => __( 'PayPal Page Style', 'tajer' ),
			'name'  => 'tajer_payment_settings[paypal_page_style]',
			'type'  => 'text',
			'help'  => __( 'Enter the name of the page style to use, or leave blank for default', 'tajer' )
		),
		array(
			'label' => __( 'Disable PayPal IPN Verification', 'tajer' ),
			'name'  => 'tajer_payment_settings[disable_paypal_verification]',
			'type'  => 'checkbox',
			'help'  => __( 'If payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases.', 'tajer' )
		)
	);

	return array_merge( $fields, $paypal_fields );
}

add_filter( 'tajer_payment_settings_fields', 'tajer_paypal_payment_gateway_settings_fields' );

/**
 * PayPal Gateway HTML Form
 */
function tajer_paypal_form() {
	$secondary_color = tajer_get_option( 'secondary_color', 'tajer_general_settings', 'green' );
	ob_start(); ?>

	<input type="hidden" name="quantity" value="">

	<div class="field">
		<div class="two fields">
			<div class="field">
				<input type="submit" class='fluid ui <?php echo $secondary_color; ?> button' id="tajer_paypal_submit_button" name="tajer-purchase"
				       value="Checkout">
			</div>
			<div class="field">
				<a href="<?php echo tajer_get_option( 'continue_shopping', 'tajer_general_settings', '#' ); ?>"
				   class="fluid ui button tajer-continue-shopping"><?php _e( 'Continue Shopping?', 'tajer' ); ?></a>
			</div>
		</div>
	</div>

	<?php
	$form_fields = ob_get_clean();

	//AJAX json response
	$response = array(
		'form_fields' => $form_fields,
		'form_action' => esc_url( add_query_arg( array( 'payment-mode' => 'paypal' ), get_permalink( (int) tajer_get_option( 'cart', 'tajer_general_settings', '' ) ) ) )
	);
	tajer_response( $response );
}

//this action is consist of tajer_{gateway ID}_purchase_form used to overwrite the default checkout form
add_action( 'tajer_paypal_purchase_form', 'tajer_paypal_form' );

/**
 * PayPal Gateway Checkout Process
 */
function tajer_submit_paypal_purchase_form() {

	$customer_details = tajer_customer_details();

	$result = tajer_insert_order( array( 'gateway' => 'paypal' ) );

	tajer_update_order_meta( $result['id'], 'currency', tajer_get_option( 'currency', 'tajer_general_settings', 'USD' ) );

	// Get the PayPal redirect uri
	$paypal_redirect = trailingslashit( tajer_get_paypal_redirect() ) . '?';

	// Get the success url
	$return_url = add_query_arg( array(
		'tajer_gateway'     => 'paypal',
		'tajer_order_id'    => $result['id'],
		'tajer_secret_code' => $result['secret_code']
	), get_permalink( (int) tajer_get_option( 'thank_you_page', 'tajer_general_settings', '' ) ) );

	// Setup PayPal arguments
	$paypal_args = array(
		'business'      => tajer_get_option( 'paypal_email', 'tajer_payment_settings' ),
		'email'         => $customer_details->email,
		'invoice'       => $result['id'],
		'no_shipping'   => '1',
		'shipping'      => '0',
		'no_note'       => '1',
		'currency_code' => tajer_get_option( 'currency', 'tajer_general_settings', 'USD' ),
		'charset'       => get_bloginfo( 'charset' ),
		'custom'        => $result['secret_code'],
		'rm'            => '2',
		'return'        => $return_url,
//		'cancel_return' => edd_get_failed_transaction_uri( '?payment-id=' . $payment ),
		'notify_url'    => add_query_arg( 'tajer-listener', 'IPN', home_url( 'index.php' ) ),
		'page_style'    => tajer_get_option( 'paypal_page_style', 'tajer_payment_settings', 'paypal' ),
		'cbt'           => get_bloginfo( 'name' ),
		'bn'            => 'Tajer_IQ',
		'cmd'           => '_cart',
		'quantity_1'    => 1,
		'amount_1'      => tajer_sanitize_amount( $result['total'] ),
		'item_name_1'   => stripslashes_deep( html_entity_decode( apply_filters( 'tajer_paypal_item_name', get_bloginfo( 'name' ) . '-' . __( 'Product(s)', 'tajer' ) ), ENT_COMPAT, 'UTF-8' ) ),
		'upload'        => '1'
	);

	// Build query
	$paypal_redirect .= http_build_query( $paypal_args );

	// Fix for some sites that encode the entities
	$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );

	// Redirect to PayPal
	wp_redirect( $paypal_redirect );
	exit;
}

add_action( 'tajer_submit_paypal_purchase_form', 'tajer_submit_paypal_purchase_form' );


/**
 * Listens for a PayPal IPN requests and then sends to the processing function
 *
 * @since 1.0
 * @return void
 */
function tajer_listen_for_paypal_ipn() {
	// Regular PayPal IPN
	if ( isset( $_GET['tajer-listener'] ) && $_GET['tajer-listener'] == 'IPN' ) {
		do_action( 'tajer_verify_paypal_ipn' );
	}
}

add_action( 'init', 'tajer_listen_for_paypal_ipn' );

/**
 * Process PayPal IPN
 *
 * @since 1.0
 * @return void
 */
function tajer_process_paypal_ipn() {
	// Check the request method is POST
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] != 'POST' ) {
		return;
	}

	// Set initial post data to empty string
	$post_data = '';

	// Fallback just in case post_max_size is lower than needed
	if ( ini_get( 'allow_url_fopen' ) ) {
		$post_data = file_get_contents( 'php://input' );
	} else {
		// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
		ini_set( 'post_max_size', '12M' );
	}
	// Start the encoded data collection with notification command
	$encoded_data = 'cmd=_notify-validate';

	// Get current arg separator
	$arg_separator = tajer_get_php_arg_separator_output();

	// Verify there is a post_data
	if ( $post_data || strlen( $post_data ) > 0 ) {
		// Append the data
		$encoded_data .= $arg_separator . $post_data;
	} else {
		// Check if POST is empty
		if ( empty( $_POST ) ) {
			// Nothing to do
			return;
		} else {
			// Loop through each POST
			foreach ( $_POST as $key => $value ) {
				// Encode the value and append the data
				$encoded_data .= $arg_separator . "$key=" . urlencode( $value );
			}
		}
	}

	// Convert collected post data to an array
	parse_str( $encoded_data, $encoded_data_array );

	$order_id = isset( $encoded_data_array['invoice'] ) ? absint( $encoded_data_array['invoice'] ) : 0;

	// Get the PayPal redirect uri
	$paypal_redirect = tajer_get_paypal_redirect( true );

	if ( tajer_get_option( 'disable_paypal_verification', 'tajer_payment_settings', '' ) != 'yes' ) {

		// Validate the IPN

		$remote_post_vars = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(
				'host'         => 'www.paypal.com',
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',

			),
			'sslverify'   => false,
			'body'        => $encoded_data //todo Mohammed modified
		);

		// Get response
		$api_response = wp_remote_post( tajer_get_paypal_redirect(), $remote_post_vars );

		if ( is_wp_error( $api_response ) ) {
			tajer_record_gateway_error( $order_id, 'IPN Error', sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'tajer' ), json_encode( $api_response ) ) );

			return; // Something went wrong
		}


		//todo Mohammed I think there is no need to this check because it will never happen, because earlier we set if tajer_get_option( 'disable_paypal_verification', 'tajer_payment_settings', '' ) != 'yes' and now we check if tajer_get_option( 'disable_paypal_verification', 'tajer_payment_settings', '' ) == 'yes' ?!
//		if ( $api_response['body'] !== 'VERIFIED' && tajer_get_option( 'disable_paypal_verification', 'tajer_payment_settings', '' ) == 'yes' ) {
////			tajer_record_gateway_error( __( 'IPN Error', 'tajer' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'tajer' ), json_encode( $api_response ) ) );
//			return; // Response not okay
//		}

		//todo Mohammed new condition
		if ( $api_response['body'] !== 'VERIFIED' ) {
			tajer_record_gateway_error( $order_id, 'IPN Error', sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'tajer' ), json_encode( $api_response ) ) );

			return; // Response not okay
		}

	}

	// Check if $post_data_array has been populated
	if ( ! is_array( $encoded_data_array ) && ! empty( $encoded_data_array ) ) {
		return;
	}

	$defaults = array(
		'txn_type'       => '',
		'payment_status' => ''
	);

	$encoded_data_array = wp_parse_args( $encoded_data_array, $defaults );


	if ( has_action( 'tajer_paypal_' . $encoded_data_array['txn_type'] ) ) {
		// Allow PayPal IPN types to be processed separately
		do_action( 'tajer_paypal_' . $encoded_data_array['txn_type'], $encoded_data_array, $order_id );
	} else {
		// Fallback to web accept just in case the txn_type isn't present
		do_action( 'tajer_paypal_web_accept', $encoded_data_array, $order_id );
	}
	exit;
}

add_action( 'tajer_verify_paypal_ipn', 'tajer_process_paypal_ipn' );

function tajer_process_paypal_web_accept_and_cart( $data, $order_id ) {

	if ( $data['txn_type'] != 'web_accept' && $data['txn_type'] != 'cart' ) {
		return;
	}

	if ( empty( $order_id ) ) {
		return;
	}

	// Collect payment details
	$secret_code    = isset( $data['custom'] ) ? $data['custom'] : 0;
	$paypal_amount  = $data['mc_gross'];
	$payment_status = strtolower( $data['payment_status'] );
	$currency_code  = strtolower( $data['mc_currency'] );
	$business_email = isset( $data['business'] ) && is_email( $data['business'] ) ? trim( $data['business'] ) : trim( $data['receiver_email'] );

	//Get order details
	$order = Tajer_DB::get_row_by_id( 'tajer_orders', $order_id );


	if ( $order->gateway != 'paypal' ) {
		tajer_record_gateway_error( $order_id, 'IPN Error', sprintf( __( 'This is not a PayPal standard IPN. IPN data: %s', 'tajer' ), json_encode( $data ) ) );

		return;
	}

	// Verify payment recipient
	if ( strcasecmp( $business_email, trim( tajer_get_option( 'paypal_email', 'tajer_payment_settings' ) ) ) != 0 ) {
		Tajer_DB::update_order_status( $order_id, 'failed' );
		tajer_record_gateway_error( $order_id, 'IPN Error', sprintf( __( 'Invalid business email in IPN response. IPN data: %s', 'tajer' ), json_encode( $data ) ) );

		return;
	}

	// Verify payment currency
	if ( $currency_code != strtolower( tajer_get_order_meta( $order_id, 'currency' ) ) ) {
		Tajer_DB::update_order_status( $order_id, 'failed' );
		tajer_record_gateway_error( $order_id, 'IPN Error', sprintf( __( 'Invalid currency in IPN response. IPN data: %s', 'tajer' ), json_encode( $data ) ) );

		return;
	}


	if ( $order->status == 'completed' ) {
		return; // Only complete payments once
	}

	// Retrieve the total purchase amount (before PayPal)
	$payment_amount = $order->total;

	if ( number_format( (float) $paypal_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {
		// The prices don't match
		Tajer_DB::update_order_status( $order_id, 'failed' );
		tajer_record_gateway_error( $order_id, 'IPN Error', sprintf( __( 'Invalid payment amount in IPN response. IPN data: %s', 'tajer' ), json_encode( $data ) ) );

		return;
	}
	if ( $secret_code != $order->secret_code ) {
		// Secret code don't match
		Tajer_DB::update_order_status( $order_id, 'failed' );
		tajer_record_gateway_error( $order_id, 'IPN Error', sprintf( __( 'Invalid secret code in IPN response. IPN data: %s', 'tajer' ), json_encode( $data ) ) );

		return;
	}

	if ( 'completed' == $payment_status || tajer_is_paypal_test_mode() ) {
		tajer_order_completed( array(
			//order_number is the gateway order number
			'order_number'   => $data['txn_id'],
			'total'          => $order->total,
			'tajer_order_id' => $order_id

		) );

	} else if ( 'pending' == $payment_status && isset( $data['pending_reason'] ) ) {

		// Look for possible pending reasons, such as an echeck

		$note = '';

		switch ( strtolower( $data['pending_reason'] ) ) {

			case 'echeck' :

				$note = __( 'Payment made via eCheck and will clear automatically in 5-8 days', 'tajer' );

				break;

			case 'address' :

				$note = __( 'Payment requires a confirmed customer address and must be accepted manually through PayPal', 'tajer' );

				break;

			case 'intl' :

				$note = __( 'Payment must be accepted manually through PayPal due to international account regulations', 'tajer' );

				break;

			case 'multi-currency' :

				$note = __( 'Payment received in non-shop currency and must be accepted manually through PayPal', 'tajer' );

				break;

			case 'paymentreview' :
			case 'regulatory_review' :

				$note = __( 'Payment is being reviewed by PayPal staff as high-risk or in possible violation of government regulations', 'tajer' );

				break;

			case 'unilateral' :

				$note = __( 'Payment was sent to non-confirmed or non-registered email address.', 'tajer' );

				break;

			case 'upgrade' :

				$note = __( 'PayPal account must be upgraded before this payment can be accepted', 'tajer' );

				break;

			case 'verify' :

				$note = __( 'PayPal account is not verified. Verify account in order to accept this payment', 'tajer' );

				break;

			case 'other' :

				$note = __( 'Payment is pending for unknown reasons. Contact PayPal support for assistance', 'tajer' );

				break;

		}

		if ( ! empty( $note ) ) {
			tajer_record_gateway_error( $order_id, 'Pending Reasons', $note );
		}
	}

}

add_action( 'tajer_paypal_web_accept', 'tajer_process_paypal_web_accept_and_cart', 10, 2 );

function tajer_get_paypal_redirect( $ssl_check = false ) {

	if ( is_ssl() || ! $ssl_check ) {
		$protocal = 'https://';
	} else {
		$protocal = 'http://';
	}

	if ( tajer_is_paypal_test_mode() ) {
		// Test mode
		$paypal_uri = $protocal . 'www.sandbox.paypal.com/cgi-bin/webscr';
	} else {
		// Live mode
		$paypal_uri = $protocal . 'www.paypal.com/cgi-bin/webscr';
	}

	return apply_filters( 'tajer_paypal_uri', $paypal_uri );
}

function tajer_is_paypal_test_mode() {

	$is_test = tajer_get_option( 'paypal_test_mode', 'tajer_payment_settings', '' );

	if ( $is_test == 'yes' ) {
		return true;
	} else {
		return false;
	}
}