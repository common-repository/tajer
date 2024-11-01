<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class Tajer_Cart_Page {

//	private $user_id = 0;
	private $general_errors = array();
	private static $instance;
	public $cart_items = array();
	public $render_cart_for_one_item = false;
	public $final_price = 0;
	public $pagination_links = array();
	public $is_empty_cart = false;

	private function __construct() {
		add_action( 'init', array( $this, 'checkout' ) );
		add_shortcode( 'tajer_cart', array( $this, 'cart_page_renderer' ) );
		add_action( 'wp_ajax_nopriv_tajer_remove_from_cart', array( $this, 'remove_from_cart' ) );
		add_action( 'wp_ajax_nopriv_tajer_apply_coupon', array( $this, 'apply_coupon_for_the_entire_user_cart' ) );
		add_action( 'wp_ajax_tajer_remove_from_cart', array( $this, 'remove_from_cart' ) );
		add_action( 'wp_ajax_tajer_apply_coupon', array( $this, 'apply_coupon_for_the_entire_user_cart' ) );
		add_action( 'wp_ajax_nopriv_tajer_checkout', array( $this, 'ajax_checkout' ) );
		add_action( 'wp_ajax_nopriv_tajer_get_payment_gateway_form_details', array(
			$this,
			'get_payment_gateway_form_details'
		) );
		add_action( 'wp_ajax_tajer_get_payment_gateway_form_details', array(
			$this,
			'get_payment_gateway_form_details'
		) );
		add_action( 'wp_ajax_tajer_checkout', array( $this, 'ajax_checkout' ) );
		add_action( 'wp_ajax_nopriv_tajer_empty_cart', array( $this, 'empty_cart' ) );

		add_action( 'wp_ajax_tajer_empty_cart', array( $this, 'empty_cart' ) );

		add_action( 'wp_ajax_nopriv_tajer_increase_decrease_quantity', array( $this, 'increase_decrease_quantity' ) );
		add_action( 'wp_ajax_tajer_increase_decrease_quantity', array( $this, 'increase_decrease_quantity' ) );
	}

	function get_payment_gateway_form_details() {
		if ( ! wp_verify_nonce( $_REQUEST['tajer_checkout_nonce_field'], 'tajer_checkout_nonce' ) ) {
			wp_die( 'Security check' );
		}
		$payment_mode = sanitize_text_field( $_REQUEST['payment-mode'] );
		if ( has_action( 'tajer_' . $payment_mode . '_purchase_form' ) ) {
			do_action( 'tajer_' . $payment_mode . '_purchase_form' );
		} else {
			do_action( 'tajer_purchase_form' );
		}
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	function cart_page_renderer() {
		if ( is_user_logged_in() || tajer_session() ) {
//			$nonce = wp_create_nonce( 'tajer_cart' );
			if ( isset( $_REQUEST['tajer_action'] ) && ( ! empty( $_REQUEST['tajer_action'] ) ) && ( $_REQUEST['tajer_action'] != 'add_to_cart' ) ) {
				$this->custom_cart_renderer();
			} else {
				if ( isset( $_REQUEST['tajer_action'] ) && ( $_REQUEST['tajer_action'] == 'add_to_cart' ) ) {
					//add the product to the user cart or user products then render the cart
					$this->custom_add_to_cart();
				}


				$page                   = ( intval( get_query_var( 'paged' ) ) ) ? intval( get_query_var( 'paged' ) ) : 1;
				$pagination             = new Tajer_Pagination( $page, apply_filters( 'tajer_frontend_cart_items_per_page', 20 ), apply_filters( 'tajer_frontend_cart_items_counts', Tajer_DB::count_items( 'tajer_shopping_carts', true ) ) );
				$items                  = Tajer_DB::get_items_with_offset( 'tajer_shopping_carts', $pagination->offset(), $pagination->per_page, 'date', 'DESC', true );
				$items                  = apply_filters( 'tajer_shopping_carts_items', $items, $pagination );
				$this->pagination_links = tajer_get_pagination_links( $pagination );


				//get user's products in his/her cart and put them in table
//				$items = Tajer_DB::get_cart_items();

				if ( $items ) {
					$this->populate_cart_data( $items );
				} else {
					$this->is_empty_cart = true;
					tajer_get_template_part( 'empty-cart' );
				}
				$this->final_price = $this->total_price();
				tajer_get_template_part( 'cart' );
			}
		} else {
			tajer_get_template_part( 'restrict-cart-access' );
		}
	}

	function remove_from_cart() {
		$nonce = $_REQUEST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'tajer_cart' ) ) {
			wp_die( 'Security check' );
		}

		if ( ! is_user_logged_in() && ! tajer_session() ) {
			wp_die( 'Security check' );
		}

		do_action( 'tajer_remove_from_cart' );

		$result = tajer_remove_from_cart( (int) $_REQUEST['cart_id'] );

		$message = $result->message;
		$status  = $result->status;
		$row_id  = $result->row_id;

		list( $statuses, $messages, $total_price ) = $this->apply_coupon_for_the_entire_user_cart( true );

		$response = array(
			'message'    => $message,
			'user_total' => $total_price,
			'status'     => $status,
			'id'         => $row_id
		);

		$response = apply_filters( 'tajer_remove_from_cart_response', $response );
		tajer_response( $response );
	}


	function apply_coupon_for_the_entire_user_cart( $return = false ) {
		$cart = new Tajer_Cart();

		return $cart->apply_coupon_for_the_entire_user_cart( $return );
	}

	function empty_cart() {

		$nonce = $_REQUEST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'tajer_cart' ) ) {
			wp_die( 'Security check' );
		}

		if ( ! is_user_logged_in() && ! tajer_session() ) {
			wp_die( 'Security check' );
		}

		do_action( 'tajer_empty_cart' );

		$is_deleted = Tajer_DB::empty_cart();

		$is_deleted = apply_filters( 'tajer_empty_cart_result', $is_deleted );

		if ( $is_deleted !== false ) {
			$message = __( 'Cart Emptied', 'tajer' );
			$status  = 'empty';
		} else {
			$message = __( 'Cant Empty The Cart', 'tajer' );
			$status  = 'error';
		}

		$response = array(
			'message' => $message,
			'status'  => $status
		);

		$response = apply_filters( 'tajer_empty_cart_response', $response );

		tajer_response( $response );
	}

	function increase_decrease_quantity() {

		$nonce = $_REQUEST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'tajer_cart' ) ) {
			wp_die( 'Security check' );
		}

		if ( ! is_user_logged_in() && ! tajer_session() ) {
			wp_die( 'Security check' );
		}

		do_action( 'tajer_before_increase_decrease_quantity' );

		if ( ( isset( $_REQUEST['tajer_action'] ) ) && ( ! empty( $_REQUEST['tajer_action'] ) ) && ( $_REQUEST['tajer_action'] != 'add_to_cart' ) ) {
			list( $status, $message, $user_total ) = $this->increase_decrease_custom_quantity();

			$response = array(
				'message'    => $message,
				'user_total' => tajer_number_to_currency( $user_total, false ),
				'status'     => $status
			);

			$response = apply_filters( 'tajer_increase_decrease_custom_quantity_response', $response );

			tajer_response( $response );
		}

		$is_updated = Tajer_DB::update_quantity( (int) $_REQUEST['cart_id'], (int) $_REQUEST['quantity'] );

		if ( $is_updated !== false ) {
			$message = __( 'Cart Updated Successfully', "tajer" );
		} else {
			$message = __( 'The Cart Doesn\'t Updated', "tajer" );
		}

		list( $statuses, $messages, $total_price ) = $this->apply_coupon_for_the_entire_user_cart( true );
		$response = array(
			'user_total' => $total_price,
			'message'    => $message
		);
		$response = apply_filters( 'tajer_increase_decrease_quantity_response', $response );
		tajer_response( $response );
	}

	function increase_decrease_custom_quantity() {
		$product_id     = (int) $_REQUEST['product_id'];
		$product_sub_id = (int) $_REQUEST['product_sub_id'];
		$action_id      = (int) $_REQUEST['action_id'];
		$coupon         = sanitize_text_field( $_REQUEST['coupon'] );
		$quantity       = (int) $_REQUEST['quantity'];
		list( $tax, $prices_include_tax, $cart_include_tax, $display_tax_rate ) = tajer_taxValue();
		switch ( $_REQUEST['tajer_action'] ) {
			case 'buy_now':
				$price = tajer_get_product_price( $product_id, $product_sub_id );

				list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $price, $prices_include_tax, $cart_include_tax, true );

				$price = $price * $quantity;

				list( $status, $message, $user_total ) = tajer_apply_coupon( $price, $coupon, $product_id, $product_sub_id );

				return apply_filters( 'tajer_increase_decrease_custom_quantity_buy_now_response', array(
					$status,
					$message,
					$user_total
				), $this );
				break;

			case 'recurring':
				$recurring     = get_post_meta( $product_id, 'tajer_recurring', true );
				$recurring_fee = $recurring[ $action_id ]['recurring_fee'];
				list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $recurring_fee, $prices_include_tax, $cart_include_tax, true );
				$recurring_fee = $price * $quantity;
				list( $status, $message, $user_total ) = tajer_apply_coupon( $recurring_fee, $coupon, $product_id, $product_sub_id );

				return apply_filters( 'tajer_increase_decrease_custom_quantity_recurring_response', array(
					$status,
					$message,
					$user_total
				), $this );
				break;
			case 'upgrade':
				$upgrade     = get_post_meta( $product_id, 'tajer_upgrade', true );
				$upgrade_fee = $upgrade[ $action_id ]['upgrade_fee'];
				list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $upgrade_fee, $prices_include_tax, $cart_include_tax, true );
				$upgrade_fee = $price * $quantity;
				list( $status, $message, $user_total ) = tajer_apply_coupon( $upgrade_fee, $coupon, $product_id, $product_sub_id );

				return apply_filters( 'tajer_increase_decrease_custom_quantity_upgrade_response', array(
					$status,
					$message,
					$user_total
				), $this );
				break;
			default:
				do_action( 'tajer_increase_decrease_' . $_REQUEST['tajer_action'] . '_custom_quantity' );
				break;
		}
	}

	/**
	 * Return the total price for the user's products in cart with tax
	 *
	 * @return int
	 */
	function total_price() {
		$total_price = 0;
		$items       = Tajer_DB::get_cart_items();

		$items = apply_filters( 'tajer_cart_page_items_total_price', $items, $total_price, $this );

		list( $tax, $prices_include_tax, $cart_include_tax, $display_tax_rate ) = tajer_taxValue();
		foreach ( $items as $item ) {
			//Calculate the total coast
			$product_price = tajer_get_product_price( $item->product_id, $item->product_sub_id );
			list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $product_price, $prices_include_tax, $cart_include_tax, true );
			$total_price += ( ( $product_price_with_tax ) * ( $item->quantity ) );

		}

		return apply_filters( 'tajer_cart_page_total_price', $total_price, $items, $this );
	}

	function checkout() {
		$errors = tajer_purchase_form_errors();
		if ( isset( $_GET['payment-mode'] ) && ! empty( $_GET['payment-mode'] ) && empty( $errors ) ) {
			do_action( 'tajer_submit_' . sanitize_text_field( $_GET['payment-mode'] ) . '_purchase_form' );
		}
	}

	function ajax_checkout() {
		if ( isset( $_POST['payment-mode'] ) && ! empty( $_POST['payment-mode'] ) ) {
			do_action( 'tajer_ajax_submit_' . sanitize_text_field( $_POST['payment-mode'] ) . '_purchase_form' );
		}
	}

	public function custom_cart_renderer() {

//		$nonce_url = $_REQUEST['_wpnonce'];
//		if ( ! wp_verify_nonce( $nonce_url, 'tajer_download' ) ) {
//			wp_die( "Security check" );
//		}

		do_action( 'custom_cart_renderer' );

		$action_id      = isset( $_REQUEST['action_id'] ) ? (int) $_REQUEST['action_id'] : 0;
		$product_id     = (int) $_REQUEST['product_id'];
		$product_sub_id = (int) $_REQUEST['product_sub_id'];

		list( $tax, $prices_include_tax, $cart_include_tax, $display_tax_rate ) = tajer_taxValue();

		if ( $_REQUEST['tajer_action'] == 'buy_now' ) {
			if ( tajer_is_free( $product_id, $product_sub_id ) ) {
				//check the user role then add it to the user products table directly
				if ( Tajer_CanAccess( $product_id, $product_sub_id ) ) {
					//add the product to the user products table directly
					echo $this->add_to_user_products( $product_id, $product_sub_id );
				} else {
					echo apply_filters( 'tajer_not_enough_permissions_to_own_free_product', "<p>" . __( 'You don\'t have enough permissions to add this product to your dashboard.', 'tajer' ) . "</p>", $this );
				}
			} else {
				if ( tajer_is_trial( $product_id, $product_sub_id ) ) {
					$this->activeTrialVersion( $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax );
				} else {
					$this->buy_now_helper( $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax );
				}
			}

		} elseif ( $_REQUEST['tajer_action'] == 'recurring' ) {
			//check if the recurring is enabled
			if ( tajer_is_recurring( $product_id ) ) {
				$recurring                  = get_post_meta( $product_id, 'tajer_recurring', true );
				$recurring_price_assignment = $recurring[ $action_id ]['prices'];

				//check if there is really recurring for this sub product
				if ( tajer_is_legal_product( $recurring_price_assignment, $product_sub_id ) ) {
					$recurring_fee = $recurring[ $action_id ]['recurring_fee'];
					list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $recurring_fee, $prices_include_tax, $cart_include_tax );
					$this->populate_single_cart_data( $product_id, $product_sub_id, $price, false, $tax_text );
					$this->final_price = apply_filters( 'tajer_final_recurring_price', $product_price_with_tax, $this );
					tajer_get_template_part( 'cart' );
				} else {
					echo apply_filters( 'tajer_is_not_legal_recurring', '<p>' . __( 'Recurring is impossible for this product!', 'tajer' ) . '</p>', $this );
				}
			} else {
				echo apply_filters( 'tajer_is_not_recurring', '<p>' . __( 'Recurring is impossible for this product!', 'tajer' ) . '</p>', $this );
			}

		} elseif ( $_REQUEST['tajer_action'] == 'upgrade' ) {
			$upgrade = get_post_meta( $product_id, 'tajer_upgrade', true );
			//check if the upgrade is enabled
			if ( tajer_is_upgrade( $product_id ) ) {
				$action_id                = (int) $_REQUEST['action_id'];
				$upgrade_price_assignment = $upgrade[ $action_id ]['prices'];

				//check if there is really upgrade for this sub product
				if ( tajer_is_legal_product( $upgrade_price_assignment, $product_sub_id ) ) {
					$upgrade_fee = $upgrade[ $action_id ]['upgrade_fee'];
					list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $upgrade_fee, $prices_include_tax, $cart_include_tax );
					$this->populate_single_cart_data( $product_id, $product_sub_id, $price, false, $tax_text );
					$this->final_price = apply_filters( 'tajer_final_upgrade_price', $product_price_with_tax, $this );
					tajer_get_template_part( 'cart' );
				} else {
					echo apply_filters( 'tajer_is_not_legal_upgrade', '<p>' . __( 'Upgrading is impossible for this product!', 'tajer' ) . '</p>', $this );
				}
			} else {
				echo apply_filters( 'tajer_is_not_upgrade', '<p>' . __( 'Upgrading is impossible for this product!', 'tajer' ) . '</p>', $this );
			}
		}

	}

	function add_to_user_products( $product_id, $product_sub_id ) {
		if ( tajer_is_bundle( $product_id ) ) {
			tajer_handle_bundle_product( $product_id, $product_sub_id, 'tajer_insert_free_product_into_tajer_user_products' );

			$html = "<p>" . __( 'Bundle Product Added Successfully. You can now manage it from your dashboard.', 'tajer' ) . "</p>";
			$html = apply_filters( 'tajer_add_bundle_to_user_products_success_message', $html, $product_id, $product_sub_id, $this );

//			$results = tajer_handle_bundle_product( $product_id );
//			if ( in_array( false, $results ) ) {
//				$html = "<p>" . __( 'Bundle Product Added Successfully. You can now manage it from your dashboard.', 'tajer' ) . "</p>";
//				$html = apply_filters( 'tajer_add_bundle_to_user_products_success_message', $html, $product_id, $product_sub_id, $this );
//			} else {
//				$html = "<p>" . __( 'I Cant Add The Product!', 'tajer' ) . "</p>";
//				$html = apply_filters( 'tajer_add_bundle_to_user_products_fail_message', $html, $product_id, $product_sub_id, $this );
//			}
		} else {
			$result = tajer_insert_free_product_into_tajer_user_products( $product_id, $product_sub_id );
			if ( $result['is_insert'] ) {
				$html = "<p>" . __( 'Product Added Successfully. You can now manage it from your dashboard.', 'tajer' ) . "</p>";
				$html = apply_filters( 'tajer_add_product_to_user_products_success_message', $html, $product_id, $product_sub_id, $this );
			} else {
				$html = "<p>" . __( 'I Cant Add The Product.', 'tajer' ) . "</p>";
				$html = apply_filters( 'tajer_add_product_to_user_products_fail_message', $html, $product_id, $product_sub_id, $this );
			}
		}

		return $html;
	}

	function activeTrialVersion( $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax ) {
		do_action( 'tajer_active_trial_version', $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax );
		$trial           = get_post_meta( (int) $product_id, 'tajer_trial', true );
		$is_direct_trial = $trial[ $product_sub_id ]['direct_trial'];
		$trial_period    = $trial[ $product_sub_id ]['trial_period'];
		if ( $is_direct_trial == 'yes' ) {
			echo $this->addUserTrial( $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax, $trial_period );
		} else {
			$this->buy_now_helper( $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax );
		}
	}

	function addUserTrial( $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax, $period ) {

		do_action( 'tajer_add_user_trial', $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax, $period, $this );

		//check if the user used this trial before if he/she used it before then add it to his/her cart
		if ( tajer_is_trial_possible( $period, $product_id, $product_sub_id ) ) {
			//record this user used this trial
			$tajer_insert_trial_record = tajer_insert_trial_record( $product_id, $product_sub_id );
			if ( ! ( $tajer_insert_trial_record['is_insert'] ) ) {
				return apply_filters( 'tajer_adding_trial_record_error_message', __( 'Something wrong happen please contact the website admin!', 'tajer' ), $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax, $period, $this );
			}

			if ( tajer_is_bundle( $product_id ) ) {
				tajer_handle_bundle_product( $product_id, $product_sub_id, 'TajerinsertUserProductForTrialPurpose', array( $period ) );

				return apply_filters( 'tajer_bundle_trial_activation_success_message', __( 'The trial version of this product has been activated for you!', 'tajer' ), $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax, $period, $this );

			} else {
				TajerinsertUserProductForTrialPurpose( $product_id, $product_sub_id, $period );

				return apply_filters( 'tajer_product_trial_activation_success_message', __( 'The trial version of this product has been activated for you!', 'tajer' ), $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax, $period, $this );
			}
		} else {
			$this->buy_now_helper( $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax );
		}
	}

	public function populate_cart_data( $items ) {
		do_action( 'tajer_populate_cart_data', $items, $this );
		$total_price = 0;
		list( $tax, $prices_include_tax, $cart_include_tax, $display_tax_rate ) = tajer_taxValue();

		foreach ( $items as $item ) {

			do_action( 'tajer_populate_cart_data_loop', $items, $item, $this );

			$this->general_errors = apply_filters( 'tajer_populate_cart_data_cart_page_errors', $this->general_errors, $item, $items, $this );

			//check if each product the user added it to his/her cart exist if not remove it from the cart
			if ( ! tajer_is_product_exist( $item->product_id, $item->product_sub_id ) ) {
				$is_deleted = tajer_remove_from_cart( $item->id );
				if ( $is_deleted->deleted ) {
					continue;
				} else {
					$this->general_errors[] = apply_filters( 'tajer_error_while_removing_cart_product', __( 'Cant remove incorrect item from the cart, please visit your cart again if you still see this message contact the website admin!', 'tajer' ), $is_deleted, $item, $items, $this );
					continue;
				}
			}

			//Calculate the total coast
			$product_price = tajer_get_product_price( $item->product_id, $item->product_sub_id );
//			$product_price_with_tax = $tax == 0 ? $product_price : ( ( $product_price * $tax ) + $product_price );
			list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $product_price, $prices_include_tax, $cart_include_tax, true );
			$total_price += ( $product_price_with_tax * ( $item->quantity ) );

			//add the tax to the $product_price
			$is_tax_incuded = false;
			if ( $prices_include_tax == 'no' && $cart_include_tax == 'yes' && $tax != 0 ) {
				$is_tax_incuded = true;
				$product_price  = $product_price_with_tax;
			}

			if ( $tax != 0 ) {
				$tax_text = $is_tax_incuded ? __( ' - includes ', 'tajer' ) . $tax . __( '% tax', 'tajer' ) : __( ' - excludes ', 'tajer' ) . $tax . __( '% tax', 'tajer' );
			} else {
				$tax_text = '';
			}

			$this->add_cart_item_data( $item->id, $item->product_id, $item->product_sub_id, $tax_text, $item->quantity, $product_price );

		}

		do_action( 'tajer_populate_cart_data_after_loop', $items, $this );
	}

	function add_cart_item_data( $cart_id, $product_id, $product_sub_id, $tax_text, $quantity, $product_price ) {
		do_action( 'tajer_add_cart_item_data', $cart_id, $product_id, $product_sub_id, $tax_text, $quantity, $product_price, $this );

		$arr = array(
			'product_id' => $product_id,
			'price'      => $product_price,
			'name'       => tajer_get_price_option( $product_id, $product_sub_id, 'name' ),
			'tax_text'   => $tax_text,
			'quantity'   => $quantity
		);

		( $product_sub_id !== false ) ? $arr['product_sub_id'] = $product_sub_id : '';
		$arr                          = apply_filters( 'tajer_add_cart_item_data_args', $arr, $cart_id, $product_id, $product_sub_id, $tax_text, $quantity, $product_price, $this );
		$this->cart_items[ $cart_id ] = $arr;
	}

	public function populate_single_cart_data( $product_id, $product_sub_id, $price, $quantity = false, $tax_text ) {
		do_action( 'tajer_populate_single_cart_data', $product_id, $price, $quantity, $tax_text, $this );
		if ( $quantity ) {
			$quantity = 1;
		} else {
			$quantity = 0;
		}

		$this->render_cart_for_one_item = apply_filters( 'tajer_populate_single_cart_data_render_cart_for_one_item', true, $product_id, $price, $quantity, $tax_text, $this );
		$this->add_cart_item_data( 0, $product_id, $product_sub_id, $tax_text, $quantity, $price );
	}

	public function buy_now_helper( $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax ) {

		do_action( 'tajer_cart_page_buy_now_helper', $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax, $this );

		$price = tajer_get_product_price( $product_id, $product_sub_id );

		list( $product_price_with_tax, $price, $tax_text ) = tajer_taxParameters( $tax, $price, $prices_include_tax, $cart_include_tax );

		$this->populate_single_cart_data( $product_id, $product_sub_id, $price, true, $tax_text );
		$this->final_price = apply_filters( 'tajer_buy_now_helper_final_price', $product_price_with_tax, $product_id, $product_sub_id, $tax, $prices_include_tax, $cart_include_tax, $this );;
		tajer_get_template_part( 'cart' );
	}

	public function custom_add_to_cart() {
		do_action( 'tajer_custom_add_to_cart', $this );
		if ( tajer_is_free( (int) $_REQUEST['product_id'], (int) $_REQUEST['product_sub_id'] ) ) {
			if ( Tajer_CanAccess( (int) $_REQUEST['product_id'], (int) $_REQUEST['product_sub_id'] ) ) {
				if ( tajer_is_bundle( (int) $_REQUEST['product_id'] ) ) {
					tajer_handle_bundle_product( (int) $_REQUEST['product_id'], (int) $_REQUEST['product_sub_id'], 'tajer_insert_free_product_into_tajer_user_products' );
				} else {
					tajer_insert_free_product_into_tajer_user_products( (int) $_REQUEST['product_id'], (int) $_REQUEST['product_sub_id'] );
				}
			} else {
				echo apply_filters( 'tajer_custom_add_to_cart_not_enough_permissions_message', "<p>" . __( 'You don\'t have enough permissions to add this product to your dashboard!', 'tajer' ) . "</p>", $this );
				exit;
			}
		} else {
			if ( tajer_is_trial( (int) $_REQUEST['product_id'], (int) $_REQUEST['product_sub_id'] ) ) {
				add_product_with_trial( 0, (int) $_REQUEST['product_id'], (int) $_REQUEST['product_sub_id'] );
			} else {
				tajer_add_to_cart( (int) $_REQUEST['product_id'], (int) $_REQUEST['product_sub_id'] );
			}
		}
	}
}