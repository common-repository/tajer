<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Purchase_Link_Parser {
	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'tajer_purchase_link', array( $this, 'tajer_purchase_link_shortcode_parser' ) );
	}

	function tajer_purchase_link_shortcode_parser( $attr ) {

		$id     = isset( $attr['id'] ) ? $attr['id'] : 0;
		$sub_id = isset( $attr['sub_id'] ) ? $attr['sub_id'] : 0;
		$text   = isset( $attr['text'] ) ? $attr['text'] : '';
		$style  = isset( $attr['style'] ) ? $attr['style'] : 'link';
		$color  = isset( $attr['color'] ) ? $attr['color'] : 'inherit';
		$target = isset( $attr['target'] ) ? $attr['target'] : '_self';
		$action = isset( $attr['action'] ) ? $attr['action'] : 'buy_now';


		$class = $style == 'link' ? 'tajer-link' : 'ui button ' . $color;

		$class = apply_filters( 'tajer_purchase_link_class', $class, $attr );

		$recurring_url = add_query_arg( apply_filters( 'tajer_purchase_link_parser_query_args', array(
			'tajer_action'   => $action,
			'product_id'     => $id,
			'product_sub_id' => $sub_id
		), $attr, $class ), get_permalink( intval( tajer_get_option( 'cart', 'tajer_general_settings', '' ) ) ) );

		$html = '<div class="Tajer">';
		$html .= '<a href="' . esc_url( wp_nonce_url( $recurring_url, 'tajer_download' ) ) . '" target="' . $target . '" class="' . $class . '">' . $text . '</a>';
		$html .= '</div';

		$html = apply_filters( 'tajer_purchase_link_html', $html, $attr, $recurring_url, $class );

		return $html;
	}
}