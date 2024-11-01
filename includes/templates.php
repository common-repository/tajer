<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
add_filter( 'tajer_product_trial_activation_success_message', 'tajet_filter_trial_success_message', 10, 8 );
add_filter( 'tajer_bundle_trial_activation_success_message', 'tajet_filter_trial_success_message', 10, 8 );
function tajet_filter_trial_success_message( $message, $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax, $period, $this ) {
	$html = '<div class="ui positive message">
  <div class="header">
    ' . esc_html( $message ) . '
  </div>
</div>';

	return $html;
}

/*// Tajer class to the html body element
add_filter( 'body_class', 'adding_tajer_class_names' );
function adding_tajer_class_names( $classes ) {
	// add 'class-name' to the $classes array
	$classes[] = 'Tajer';
	// return the $classes array
	return $classes;
}*/