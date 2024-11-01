<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Frontend_Product {
	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		add_filter( 'the_content', array( $this, 'tajer_the_content_filter' ), 20 );
		add_action( 'wp_ajax_nopriv_tajer_add_to_cart_button', array( $this, 'tajer_add_to_cart_button' ) );
		add_action( 'wp_ajax_tajer_add_to_cart_button', array( $this, 'tajer_add_to_cart_button' ) );
	}

	function tajer_the_content_filter( $content ) {
		global $post;
		if ( $post->post_type == "tajer_products" && apply_filters( 'tajer_product_pricing', true, $content ) ) {
			$content = $content . $this->tajer_add_to_cart_buttons_render();
		}

		// Returns the content.
		return $content;
	}

	function tajer_add_to_cart_buttons_render() {
		global $post;

		do_action( 'tajer_add_to_cart_buttons_render' );

		$nonce  = wp_create_nonce( 'tajer-add-to-cart-button' );
		$prices = get_post_meta( $post->ID, 'tajer_product_prices', true );

		$color = tajer_get_option( 'color', 'tajer_general_settings', 'teal' );

		list( $tax, $prices_include_tax, $cart_include_tax, $display_tax_rate ) = tajer_taxValue();


		//check if the user has tis item in his cart
		$html = '';
		$html .= '<div class="Tajer" id="tajer_prices">';
		$html .= '<div id="tajer_buttons_notification">';
		$html .= '</div>';
		$html .= '<div class="ui vertical ' . $color . ' basic buttons tajer-vertical-button-group">';

		foreach ( $prices as $price_id => $price_detail ) {
			$is_in_cart = tajer_is_in_cart( $post->ID, $price_id );
			$cart_id    = $is_in_cart ? $is_in_cart->id : 0;
//			$cart_id  = is_user_logged_in() ? $cart_id : 0;
//			$html .= apply_filters( 'tajer_frontend_add_to_cart_button_tag', '<button class="tajer_add_to_cart" data-cart-text="' . __( 'Processing...', 'tajer' ) . '" data-nonce="' . $nonce . '" data-cart-id="' . $cart_id . '" data-id="' . esc_attr( $post->ID ) . '" data-sub_id="' . esc_attr( $price_id ) . '" itemprop="offers" itemscope itemtype="http://schema.org/Offer"><meta itemprop="priceCurrency" content="' . tajer_get_option( 'currency', 'tajer_general_settings', '' ) . '" /><i class="fa"></i>&nbsp;<span id="tajer-text" itemprop="description">' . $price_detail['name'] . '</span>' . '- ' . '<span itemprop="price">' . tajer_number_to_currency(  $price_detail['price'], true ) . '</span></button>&nbsp;', $nonce, $cart_id, $post, $price_id, $price_detail );
			$html .= apply_filters( 'tajer_frontend_add_to_cart_button_tag', '<div class="ui animated fade button tajer_add_to_cart" data-nonce="' . $nonce . '" data-cart-id="' . $cart_id . '" data-id="' . esc_attr( $post->ID ) . '" data-sub_id="' . esc_attr( $price_id ) . '" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
			<meta itemprop="priceCurrency" content="' . tajer_get_option( 'currency', 'tajer_general_settings', '' ) . '" />
    <div class="visible content"><span id="tajer-text" itemprop="description">' . $price_detail['name'] . '</span>' . ' - ' . '<span itemprop="price">' . tajer_number_to_currency( $price_detail['price'], true ) . '</span></div>
    <div class="hidden content">
        ' . ( $is_in_cart ? esc_html__( 'Remove From Cart?', 'tajer' ) : esc_html__( 'Add To Cart?', 'tajer' ) ) . '
    </div>
</div>', $nonce, $cart_id, $post, $price_id, $price_detail );
		}

		$html .= '</div>';

		if ( $display_tax_rate == 'yes' ) {
			if ( $prices_include_tax == 'yes' ) {
				$text = apply_filters( 'tajer_frontend_add_to_cart_button_include_tax_text', __( 'Including ', 'tajer' ) . $tax . __( '% tax', 'tajer' ), $prices_include_tax, $prices, $nonce );
			} else {
				$text = apply_filters( 'tajer_frontend_add_to_cart_button_exclude_tax_text', __( 'Excluding ', 'tajer' ) . $tax . __( '% tax', 'tajer' ), $prices_include_tax, $prices, $nonce );
			}

			$html .= '<div class="ui pointing ' . $color . ' label">
      ' . $text . '
    </div>';

		}

		$html .= '</div>';
		$html = apply_filters( 'tajer_frontend_add_to_cart_buttons_html', $html );

		return $html;
	}

	function tajer_add_to_cart_button() {
		$nonce = $_REQUEST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'tajer-add-to-cart-button' ) ) {
			wp_die( 'Security check!' );
		}

		do_action( 'tajer_add_to_cart_button_secured' );

		$status = 'login';
		if ( ! is_user_logged_in() && ! tajer_session() ) {
			$message  = __( 'You must login to buy this product!', 'tajer' );
			$response = array(
				'message' => $message,
				'status'  => $status
			);

			$response = apply_filters( 'tajer_add_to_cart_button_not_logged_in_user_response', $response );

			tajer_response( $response );
		}

		$product_id     = (int) $_REQUEST['product_id'];
		$product_sub_id = (int) $_REQUEST['product_sub_id'];
		$cart_id        = (int) $_REQUEST['cart_id'];

		//check if the product is free
		if ( tajer_is_free( $product_id, $product_sub_id ) ) {
			//check the user role then add it to the user products table directly
			$canAccess = Tajer_CanAccess( $product_id, $product_sub_id );
			if ( $canAccess ) {
				$this->add_remove_from_tajer_user_products( $cart_id, $product_id, $product_sub_id );
			} else {
				$message = __( 'You must have enough permissions to add this product to your products.', 'tajer' );
				$status  = 'login';

				$response = array(
					'message' => $message,
					'status'  => $status,
					'id'      => 0
				);

				$response = apply_filters( 'tajer_add_to_cart_button_not_enough_permissions_response', $response );

				tajer_response( $response );
			}
		} else {
			if ( tajer_is_trial( $product_id, $product_sub_id ) ) {
				list( $message, $status, $row_id ) = add_remove_product_with_trial( $cart_id, $product_id, $product_sub_id );

				$response = array(
					'message' => $message,
					'status'  => $status,
					'id'      => $row_id
				);

				$response = apply_filters( 'tajer_add_to_cart_button_trial_response', $response );

				tajer_response( $response );

			} else {
				$this->add_remove_from_cart( $cart_id, $product_id, $product_sub_id );
			}
		}
	}

	function addUserTrial( $product_id, $product_sub_id, $period, $cart_id ) {
		list( $message, $status, $id ) = TajeraddUserTrial( $product_id, $product_sub_id, $period, $cart_id );

		$response = array(
			'message' => $message,
			'status'  => $status,
			'id'      => $id
		);

		$response = apply_filters( 'tajer_add_to_cart_button_add_user_trial_response', $response, $product_id, $product_sub_id, $period, $cart_id );
		tajer_response( $response );
	}

	function add_remove_from_tajer_user_products( $cart_id, $product_id, $product_sub_id ) {

		do_action( 'add_remove_from_tajer_user_products', $cart_id, $product_id, $product_sub_id );

		//the $cart_id here represent the tajer_user_products table id
		if ( tajer_is_bundle( $product_id ) ) {
			$this->handle_bundle_product( $product_id, $product_sub_id, $cart_id );
		} else {
			if ( $cart_id == 0 ) {
				$result = tajer_insert_free_product_into_tajer_user_products( $product_id, $product_sub_id );
				if ( $result['is_insert'] ) {
					$message = apply_filters( 'tajer_add_remove_from_tajer_user_products_success_adding_message', __( 'Product Added Successfully!', 'tajer' ), $cart_id, $product_id, $product_sub_id );
					$status  = 'add';
					$row_id  = $result['id'];
				} else {
					$message = apply_filters( 'tajer_add_remove_from_tajer_user_products_fail_adding_message', __( 'I Cant Add The Product!', 'tajer' ), $cart_id, $product_id, $product_sub_id );
					$status  = 'error';
					$row_id  = 'error';
				}
			} else {
				$is_deleted = Tajer_DB::delete_by_id( 'tajer_user_products', $cart_id );
				if ( $is_deleted !== false ) {
					$message = apply_filters( 'tajer_add_remove_from_tajer_user_products_success_removing_message', __( 'Product Removed!', 'tajer' ), $cart_id, $product_id, $product_sub_id );
					$status  = 'remove';
					$row_id  = 0;
				} else {
					$message = apply_filters( 'tajer_add_remove_from_tajer_user_products_fail_removing_message', __( 'Cant Remove The Product!', 'tajer' ), $cart_id, $product_id, $product_sub_id );
					$status  = 'error';
					$row_id  = 'error';
				}
			}
		}
		$response = array(
			'message' => $message,
			'status'  => $status,
			'id'      => $row_id
		);

		$response = apply_filters( 'tajer_add_remove_from_tajer_user_products_response', $response, $cart_id, $product_id, $product_sub_id );

		tajer_response( $response );
	}

	public function handle_bundle_product( $product_id, $product_sub_id, $cart_id ) {
		//todo Mohammed this function need some enhancements.

		do_action( 'tajer_handle_bundle_product', $product_id, $cart_id );

		if ( $cart_id == 0 ) {
			tajer_handle_bundle_product( $product_id, $product_sub_id, 'tajer_insert_free_product_into_tajer_user_products' );
			$message  = __( 'Product added!', 'tajer' );
			$status   = 'add';
			$response = array(
				'message' => $message,
				'status'  => $status,
				'id'      => 1
			);
		} else {
			$message  = __( 'You just added this product!', 'tajer' );
			$status   = 'error';
			$response = array(
				'message' => $message,
				'status'  => $status,
				'id'      => 'error'
			);
		}

		$response = apply_filters( 'tajer_handle_bundle_product_response', $response, $product_id, $cart_id );
		tajer_response( $response );
	}

	function add_remove_from_cart( $cart_id, $product_id, $product_sub_id ) {

		do_action( 'tajer_add_remove_from_cart', $cart_id, $product_id, $product_sub_id );

		$result = tajer_add_remove_from_cart( $product_id, $product_sub_id );


		$response = array(
			'message' => $result->message,
			'status'  => $result->status,
			'id'      => $result->row_id
		);

		$response = apply_filters( 'tajer_add_remove_from_cart_response', $response, $cart_id, $product_id, $product_sub_id );
		tajer_response( $response );
	}
//	public function response( $message, $status, $row_id ) {
//		$response = array(
//			'message' => $message,
//			'status'  => $status,
//			'id'      => $row_id
//		);
//		tajer_response( $response );
//	}
}
