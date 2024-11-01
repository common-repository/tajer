<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
function tajer_register_test_gateway( $gateways ) {
	$gateways['test'] = array(
		'admin_label'    => __( 'Test Payment', 'tajer' ),
		'checkout_label' => __( 'Test Payment', 'tajer' )
	);

	return $gateways;
}

add_filter( 'tajer_payment_gateways', 'tajer_register_test_gateway' );

function tajer_Test_checkout() {

	$errors = tajer_purchase_form_errors();
	if ( ! empty( $errors ) ) {
		//AJAX json response
		$response = array(
			'errors' => $errors
		);
		tajer_response( $response );
	}

	//here you can make the validation and sanitization of the form elements then either process the checkout by sendind post or get request or something like that or send errors to the cart page.

	//first insert the order
	$result = tajer_insert_order( array( 'gateway' => 'test' ) );
	//then finalize the order to insert the user products and so on.
	tajer_order_completed( array(
		'tajer_order_id' => $result['id']
	) );

	//send AJAX json response, if you want to send errors to the cart page use errors in the $response array and the errors element is an array of errors
	$response = array(
//		'success' => array( 'header' => 'head is hear', 'body' => 'the body is here' ),
//		'errors'  => array(
//			'card[number]'       => 'number error',
//			'card[cvc]'          => 'cvc error',
//			'card[expire-month]' => 'error expire-month',
//			'card[expire-year]'  => 'error expire-year'
//		)
		'redirect_to' => tajer_get_thank_you_page_url()
	);
	tajer_response( $response );
}

//tajer_ajax_submit_{gateway ID}_purchase_form action, fire when the user submit the form
add_action( 'tajer_ajax_submit_test_purchase_form', 'tajer_Test_checkout' );