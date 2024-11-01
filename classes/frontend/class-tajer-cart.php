<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class Tajer_Cart {

	public $cart_items = null;
	public $is_custom = false;
	public $coupon = '';
	public $secret_code = '';

	public function __construct() {
		$this->cart_items  = apply_filters( 'tajer_get_cart_items', Tajer_DB::get_cart_items() );
		$this->secret_code = tajer_get_token( 40 );
		$this->coupon      = sanitize_text_field( $_REQUEST['tajer_input_discount'] );
		if ( ( isset( $_REQUEST['tajer_action'] ) ) && ( ! empty( $_REQUEST['tajer_action'] ) ) ) {
			$this->is_custom = true;
		}
	}

	function validate_cart() {
		$nonce = $_REQUEST['tajer_checkout_nonce_field'];
		if ( ! wp_verify_nonce( $nonce, 'tajer_checkout_nonce' ) ) {
			wp_die( 'Security check' );
		}

		if ( ! is_user_logged_in() && ! tajer_session() ) {
			wp_die( 'Security check' );
		}
	}

	function get_user_total() {
		do_action( 'tajer_cart_get_user_total_start', $this );
		$this->validate_cart();
		if ( $this->is_custom ) {
			$total_price = $this->custom_checkout();
		} else {
			list( $statuses, $messages, $total_price ) = $this->apply_coupon_for_the_entire_user_cart( true );
		}

		return apply_filters( 'tajer_cart_get_user_total', $total_price, $this );
	}

	function products() {
		$coupon   = $this->coupon;
		$products = array();
		if ( $this->is_custom ) {
			$product_id     = (int) $_REQUEST['product_id'];
			$product_sub_id = (int) $_REQUEST['product_sub_id'];
			$quantity       = (int) $_REQUEST['quantity'];
			$products[]     = array(
				'product_id'      => $product_id,
				'user_product_id' => (int) $_REQUEST['id'],
				'product_sub_id'  => $product_sub_id,
				'earning'         => tajer_sanitize_amount($this->get_user_total()),
				'quantity'        => $quantity
			);
		} else {
			$items = $this->cart_items;
			foreach ( $items as $item ) {
				$products[] = array(
					'product_id'     => $item->product_id,
					'product_sub_id' => $item->product_sub_id,
					'earning'        => tajer_sanitize_amount($this->get_earning( $item->product_id, $item->product_sub_id, $coupon )),
					'quantity'       => $item->quantity
				);
			}
		}

		return serialize( apply_filters( 'tajer_cart_before_serialize_products', $products, $coupon, $this ) );
	}

	function get_earning( $product_id, $product_sub_id, $coupon = '' ) {
		$price = tajer_get_product_price( $product_id, $product_sub_id );
		list( $tax, $prices_include_tax, $cart_include_tax, $display_tax_rate ) = tajer_taxValue();
		list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $price, $prices_include_tax, $cart_include_tax, true );
		list( $status, $message, $user_total ) = tajer_apply_coupon( $product_price_with_tax, $coupon, $product_id, $product_sub_id );

		return apply_filters( 'tajer_cart_get_earning', $user_total, $product_id, $product_sub_id, $coupon, $this );

	}

	function action() {
		if ( $this->is_custom ) {
			$action = sanitize_text_field( $_REQUEST['tajer_action'] );
		} else {
			$action = 'cart_products';
		}

		return apply_filters( 'tajer_cart_action', $action, $this );
	}

	function action_id() {
		$action_id = isset( $_REQUEST['action_id'] ) ? (int) $_REQUEST['action_id'] : 0;

		return apply_filters( 'tajer_cart_action', $action_id, $this );
	}

	function apply_coupon_for_this_item() {
		$action         = sanitize_text_field( $_REQUEST['tajer_action'] );
		$coupon         = $this->coupon;
		$id             = (int) $_REQUEST['id'];
		$product_id     = (int) $_REQUEST['product_id'];
		$product_sub_id = (int) $_REQUEST['product_sub_id'];
		$action_id      = (int) $_REQUEST['action_id'];
		$quantity       = (int) $_REQUEST['quantity'];

		$user_total    = 0;
		$product_price = tajer_get_product_price( $product_id, $product_sub_id );
		$messages      = array();
		$statuses      = array();
		list( $tax, $prices_include_tax, $cart_include_tax, $display_tax_rate ) = tajer_taxValue();

		switch ( $action ) {
			case 'buy_now':
				list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $product_price, $prices_include_tax, $cart_include_tax, true );
				list( $statuses[], $messages[], $user_total ) = tajer_apply_coupon( ( $product_price_with_tax * $quantity ), $coupon, $product_id, $product_sub_id );
				break;
			case 'recurring':
				$recurring = get_post_meta( $product_id, 'tajer_recurring', true );
				//check if this recurring is legal
				if ( tajer_is_recurring( $product_id ) ) {
					$recurring_price_assignment = $recurring[ $action_id ]['prices'];
					if ( tajer_is_legal_product( $recurring_price_assignment, $product_sub_id ) ) {
						$recurring_fee = $recurring[ $action_id ]['recurring_fee'];
						list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $recurring_fee, $prices_include_tax, $cart_include_tax, true );
						list( $statuses[], $messages[], $user_total ) = tajer_apply_coupon( ( $product_price_with_tax * $quantity ), $coupon, $product_id, $product_sub_id );
					} else {
						$user_total = 0;
						$statuses[] = 'error';
						$messages[] = __( 'Recurring is impossible for this product!', 'tajer' );
					}
				} else {
					$user_total = 0;
					$statuses[] = 'error';
					$messages[] = __( 'Recurring is impossible for this product!', 'tajer' );
				}
				break;
			case 'upgrade':
				$upgrade = get_post_meta( $product_id, 'tajer_upgrade', true );
				if ( tajer_is_upgrade( $product_id ) ) {
					$upgrade_price_assignment = $upgrade[ $action_id ]['prices'];
					if ( tajer_is_legal_product( $upgrade_price_assignment, $product_sub_id ) ) {
						$upgrade_fee = $upgrade[ $action_id ]['upgrade_fee'];
						list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $upgrade_fee, $prices_include_tax, $cart_include_tax, true );
						list( $statuses[], $messages[], $user_total ) = tajer_apply_coupon( ( $product_price_with_tax * $quantity ), $coupon, $product_id, $product_sub_id );
					} else {
						$user_total = 0;
						$statuses[] = 'error';
						$messages[] = __( 'Upgrading is impossible for this product!', 'tajer' );
					}
				} else {
					$user_total = 0;
					$statuses[] = 'error';
					$messages[] = __( 'Upgrading is impossible for this product!', 'tajer' );
				}
				break;
		}

		if ( ! in_array( 'success', $statuses ) ) {
			$this->coupon = '';
		}

		return apply_filters( 'tajer_cart_apply_coupon_for_this_item', array(
			$statuses,
			$messages,
			$user_total
		), $this );
	}

	function custom_checkout() {
		list( $status, $message, $total_price ) = $this->apply_coupon_for_this_item();

		return apply_filters( 'tajer_cart_custom_checkout', $total_price, $this );
	}

	function apply_coupon_for_the_entire_user_cart( $return = false ) {

		if ( ( isset( $_REQUEST['tajer_action'] ) ) && ( ! empty( $_REQUEST['tajer_action'] ) ) && ( $_REQUEST['tajer_action'] != 'add_to_cart' ) ) {
			list( $status, $message, $user_total ) = $this->apply_coupon_for_this_item();
			$this->response( $status, $message, tajer_number_to_currency( $user_total, false ) );
		}

		$user_total          = 0;
		$messages            = array();
		$statuses            = array();
		$tajer_user_products = $this->cart_items;

		$tajer_user_products = apply_filters( 'apply_coupon_for_the_entire_user_cart_items', $tajer_user_products, $return );

		foreach ( $tajer_user_products as $user_product ) {

			$product_price = tajer_get_product_price( $user_product->product_id, $user_product->product_sub_id );
			list( $tax, $prices_include_tax, $cart_include_tax, $display_tax_rate ) = tajer_taxValue();
			list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $product_price, $prices_include_tax, $cart_include_tax, true );
			$coupon = $this->coupon;
			list( $status, $message, $price_after_discount ) = tajer_apply_coupon( ( $product_price_with_tax * $user_product->quantity ), $coupon, $user_product->product_id, $user_product->product_sub_id );
			$user_total += $price_after_discount;
			! in_array( $message, $messages ) ? $messages[] = $message : '';
			! in_array( $status, $statuses ) ? $statuses[] = $status : '';
//			$statuses[] = $status;

		}

		if ( ! in_array( 'success', $statuses ) ) {
			$this->coupon = '';
		}

		$statuses   = apply_filters( 'apply_coupon_for_the_entire_user_cart_status', $statuses, $this, $return );
		$messages   = apply_filters( 'apply_coupon_for_the_entire_user_cart_message', $messages, $this, $return );
		$user_total = apply_filters( 'apply_coupon_for_the_entire_user_cart_user_total', $user_total, $this, $return );

		if ( $return ) {
			return array( $statuses, $messages, tajer_number_to_currency( $user_total, false ) );
		} else {
			$this->response( $statuses, $messages, tajer_number_to_currency( $user_total, false ) );
		}
	}

	function response( $status, $message, $user_total = 0 ) {
		$response = array(
			'message'    => $message,
			'user_total' => $user_total,
			'status'     => $status
		);
		tajer_response( $response );
	}

	function cartIds() {
		if ( $this->is_custom ) {
			$ids = array();
		} else {
			$ids   = array();
			$items = $this->cart_items;
			foreach ( $items as $item ) {
				$ids[] = $item->id;
			}
		}

		return serialize( apply_filters( 'tajer_cart_before_serialize_cart_ids', $ids, $this ) );
	}
}
