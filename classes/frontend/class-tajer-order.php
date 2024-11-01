<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Order {

	function order_completed( $response, $send_emails = true, $insert_statistics = true, $remove_bought_items_from_cart = true ) {

		Tajer_DB::finalize_order( $response['tajer_order_id'], $response['order_number'] );


		//Add the products to the user
		//First get the products
		$item = Tajer_DB::get_row_by_id( 'tajer_orders', $response['tajer_order_id'] );

		$item = apply_filters( 'tajer_order_completed_order_row_items', $item, $response );

		if ( $insert_statistics ) {
			$this->insert_statistics( $item );
		}

		if ( $remove_bought_items_from_cart ) {
			$this->remove_bought_items_from_cart( $item );
		}

		$response['user_id'] = $item->user_id;
		$response['order'] = $item;//todo Mohammed test this

		if ( $send_emails ) {
			$this->send_notification_emails( $response );
		}

		switch ( $item->action ) {
			case 'cart_products':
				$this->prepare_product( $item, $response );
				break;
			case 'buy_now':
				$this->prepare_product( $item, $response );
				break;
			case 'upgrade':
				$this->prepare_upgrade( $item, $response );
				break;
			case 'recurring':
				$this->prepare_recurring( $item, $response );
				break;
			default:
				do_action( 'tajer_order_completed_prepare_' . $item->action, $item, $response );
				break;
		}

	}

	function remove_bought_items_from_cart( $item ) {
		foreach ( unserialize( $item->cart_ids ) as $cart_id ) {
			Tajer_DB::delete_by_id( 'tajer_shopping_carts', $cart_id );
		}
	}

	public function update_order_status( $order_id, $status, $payment_order_id ) {
		switch ( $status ) {
			case "pending":
			case "failed":
			case "refund":
				$is_deleted = Tajer_DB::delete_user_products_by_order_id( $order_id );
				$is_updated = Tajer_DB::update_order_status( $order_id, $status );
				if ( $is_deleted !== false && $is_updated !== false ) {
					return true;
				}

				return false;
				break;
			case "inactive":
				$is_updated              = Tajer_DB::update_user_products_status( $order_id, $status );
				$is_order_status_updated = Tajer_DB::update_order_status( $order_id, $status );
				if ( $is_order_status_updated !== false && $is_updated !== false ) {
					return true;
				}

				return false;
				break;
			case "active":
				$is_updated              = Tajer_DB::update_user_products_status( $order_id, $status );
				$is_order_status_updated = Tajer_DB::update_order_status( $order_id, $status );
				if ( $is_order_status_updated !== false && $is_updated !== false ) {
					return true;
				}

				return false;
				break;
			case "completed":
				$order                      = Tajer_DB::get_row_by_id( 'tajer_orders', $order_id );
				$response                   = array();
				$response['order_number']   = $payment_order_id;
				$response['total']          = $order->total;
				$response['tajer_order_id'] = $order_id;

				$this->order_completed( $response );

				return true;
				break;
			default:
				do_action( 'update_' . $status . '_order_status', $order_id );
				break;
		}
	}

	public function prepare_product( $item, $response ) {
		do_action( 'tajer_prepare_product', $item, $response );
		foreach ( unserialize( $item->products ) as $product ) {

			//check if these products already added
			$count = Tajer_DB::count_user_products( $item->id, $product['product_id'], $product['product_sub_id'] );

			$final_quantity = (int) $product['quantity'] - $count;

			for ( $quantity = 1; $quantity <= $final_quantity; $quantity ++ ) {
				$product_id = (int) $product['product_id'];
				if ( tajer_is_bundle( $product_id ) ) {
					tajer_handle_bundle_product( $product_id, (int) $product['product_sub_id'], array(
						$this,
						'prepare_customer'
					), array( $response ) );
				} else {
					$this->prepare_customer( $product_id, $product['product_sub_id'], $response );
				}
			}
		}
	}

//	public function update_status_to_complete( $order_id ) {
//		$order = Tajer_DB::get_row_by_id( 'tajer_orders', $order_id );
//		$count = Tajer_DB::count_user_products( $order_id, $product_id, $product_sub_id );
	//tajer_has_products function
//		foreach ( unserialize( $order->products ) as $product ) {
//
//		}
//	}

	public function prepare_recurring( $item, $response ) {
		do_action( 'tajer_prepare_recurring', $item, $response );
		foreach ( unserialize( $item->products ) as $product ) {
			$product_id = (int) $product['product_id'];

			$user_product_id                  = (int) $product['user_product_id'];
			$recurring                        = get_post_meta( $product_id, 'tajer_recurring', true );
			$recurring_to_number              = $recurring[ $item->action_id ]['recurrence_n'];//5
			$recurring_to_period_name         = $recurring[ $item->action_id ]['recurrence_w'];//week,month
			$continue_billing_for_number      = $recurring[ $item->action_id ]['duration_n'];//6
			$continue_billing_for_period_name = $recurring[ $item->action_id ]['duration_w'];//week,month

			//get the user buying date
			$customer = Tajer_DB::get_row_by_id( 'tajer_user_products', $user_product_id );

			//check if the user did this before
			if ( tajer_has_product( $item->id, $product_id, $customer->product_sub_id ) ) {
				return;
			}

			//add $continue_billing_for to the buying date to determine if the user can make this recurring process
			$recurring_expiration_date = date( 'Y-m-d H:i:s', strtotime( '+' . (string) $continue_billing_for_number . ' ' . strtolower( $continue_billing_for_period_name ) . 's', strtotime( $customer->buying_date ) ) );//add date to the user's buying date

			$recurring_expiration_date = apply_filters( 'tajer_recurring_expiration_date', $recurring_expiration_date, $customer, $product, $item, $response );

			if ( ( $recurring_expiration_date < date( 'Y-m-d H:i:s' ) ) && ( $continue_billing_for_period_name != 'Forever' ) ) {
				$this->fail_process( $response, apply_filters( 'tajer_recurring_expiration_date_fail_message', 'You cant recurring after ', $recurring_expiration_date, $customer, $product, $item, $response ) . $continue_billing_for_number . ' ' . $continue_billing_for_period_name );
			} else {
				do_action( 'tajer_do_recurring', $recurring_expiration_date, $customer, $product, $item, $response );
				//do the recurring
				$new_expiration_date = date( 'Y-m-d H:i:s', strtotime( '+' . (string) $recurring_to_number . ' ' . strtolower( $recurring_to_period_name ) . 's', strtotime( $customer->expiration_date ) ) );
				$new_expiration_date = apply_filters( 'tajer_recurring_new_expiration_date', $new_expiration_date, $customer, $product, $item, $response );

				$limits = tajer_get_download_limits( $product_id, (int) $product['product_sub_id'] );
				if ( tajer_is_bundle( $product_id ) ) {
					$this->tajer_recurring_handle_bundle_product( $user_product_id, array(
						$this,
						'make_recurring'
					), array(
						$new_expiration_date,
						$limits,
						$item->id
					), $recurring_to_number, $recurring_to_period_name, $continue_billing_for_number, $continue_billing_for_period_name );
				} else {
					$this->make_recurring( $user_product_id, $new_expiration_date, $limits, $item->id );
				}
			}
		}
	}

	function tajer_recurring_handle_bundle_product( $bundle_user_product_id, $callback, $args = array(), $recurring_to_number, $recurring_to_period_name, $continue_billing_for_number, $continue_billing_for_period_name ) {
		$bundle_products = unserialize( tajer_get_user_product_meta( $bundle_user_product_id, 'bundle_products' ) );

		//First apply the callback on the bundle itself
		$a      = array( $bundle_user_product_id );
		$params = array_merge( $a, $args );
		call_user_func_array( $callback, $params );

		//Now apply the callback on the bundle products
		foreach ( $bundle_products as $bundle_product ) {

			$customer = Tajer_DB::get_row_by_id( 'tajer_user_products', $bundle_product );

			$recurring_expiration_date = date( 'Y-m-d H:i:s', strtotime( '+' . (string) $continue_billing_for_number . ' ' . strtolower( $continue_billing_for_period_name ) . 's', strtotime( $customer->buying_date ) ) );//add date to the user's buying date
			if ( ( $recurring_expiration_date > date( 'Y-m-d H:i:s' ) ) && ( $continue_billing_for_period_name != 'Forever' ) ) {
				//Record error
				$title   = __( 'Recurring Error', 'tajer' );
				$message = __( 'Recurring expiration date for the ', 'tajer' ) . $bundle_product . __( ' user product id is bigger than current date(', 'tajer' ) . date( 'Y-m-d H:i:s' ) . __( ') and the continue billing for its product is not equal to Forever.', 'tajer' );

				tajer_record_recurring_error( end( $args ), $title, $message );
			} else {
				$new_expiration_date = date( 'Y-m-d H:i:s', strtotime( '+' . (string) $recurring_to_number . ' ' . strtolower( $recurring_to_period_name ) . 's', strtotime( $customer->expiration_date ) ) );
				$limits              = tajer_get_download_limits( $customer->product_id, $customer->product_sub_id );

				$a      = array( $bundle_product, $new_expiration_date, $limits, end( $args ) );
				$params = array_merge( $a, $args );
				call_user_func_array( $callback, $params );
			}

		}

	}

	public function fail_process( $response, $message ) {
		do_action( 'tajer_order_completed_fail_process', $response, $message );
		Tajer_DB::fail_order( $response['tajer_order_id'], $message );
	}

	public function upgrade_customer( $user_product_id, $upgrade_to, $new_expiration_date, $product_id, $product_sub_id, $order_id ) {
		do_action( 'tajer_upgrade_customer', $upgrade_to, $new_expiration_date, $user_product_id, $order_id );
		tajer_record_upgrade_user_product( $user_product_id, $product_id, $product_sub_id, $upgrade_to, $new_expiration_date, $order_id );
		Tajer_DB::upgrade_customer( $upgrade_to, $new_expiration_date, $user_product_id, $order_id );
	}

	public function make_recurring( $user_product_id, $new_expiration_date, $limit, $order_id ) {
		do_action( 'tajer_make_recurring', $new_expiration_date, $limit, $user_product_id, $order_id );
		tajer_record_recurring_user_product( $user_product_id, $new_expiration_date, $limit, $order_id );
		Tajer_DB::make_recurring( $user_product_id, $new_expiration_date, $limit, $order_id );
	}

	public function prepare_upgrade( $item, $response ) {
		do_action( 'tajer_prepare_upgrade', $item, $response );
		foreach ( unserialize( $item->products ) as $product ) {
			$product_id     = (int) $product['product_id'];
			$product_sub_id = (int) $product['product_sub_id'];

			$user_product_id = (int) $product['user_product_id'];
			$upgrade         = get_post_meta( $product_id, 'tajer_upgrade', true );
			$upgrade_to      = $upgrade[ $item->action_id ]['upgrade_to'];
			$user_product    = Tajer_DB::get_row_by_id( 'tajer_user_products', $user_product_id );

			$new_expiration_date = $this->user_product_upgrade_expiration_date( $user_product, $product_id, $upgrade_to );

			//check if the user did this before
			if ( tajer_has_product( $item->id, $product_id, $upgrade_to ) ) {
				return;
			}

			do_action( 'tajer_do_upgrade', $product, $item, $response );
			if ( tajer_is_bundle( $product_id ) ) {
				$this->tajer_upgrade_handle_bundle_product2( $user_product_id, $response, $item, $item->action_id, array(
					$this,
					'upgrade_customer'
				), array( $upgrade_to, $new_expiration_date, $product_id, $product_sub_id, $item->id ) );


//				$this->fail_process( $response, apply_filters( 'tajer_prepare_upgrade_bundle_fail_message', 'Cant upgrade bundle product!', $product, $item, $response ) );
			} else {
				$this->upgrade_customer( $user_product_id, $upgrade_to, $new_expiration_date, $product_id, $product_sub_id, $item->id );
			}
		}
	}

	public function prepare_customer( $product_id, $product_sub_id, $response ) {
		do_action( 'tajer_prepare_customer', $product_id, $response, $product_sub_id );
		$args                    = array();
		$args['expiration_date'] = tajer_expiration_date( $product_id, $product_sub_id );

		$args['buying_date']    = date( 'Y-m-d H:i:s' );
		$args['order_id']       = $response['tajer_order_id'];
		$args['product_id']     = $product_id;
		$args['user_id']        = $response['user_id'];
		$args['product_sub_id'] = $product_sub_id;

		$args = apply_filters( 'tajer_prepare_customer_args', $args, $product_id, $response, $product_sub_id );

		tajer_insert_user_product( $args );
	}

	function insert_statistics( $item ) {
		foreach ( unserialize( $item->products ) as $product ) {

			$post = get_post( $product['product_id'] );

			$args = array(
				'earnings'       => $product['earning'],
				'product_id'     => $product['product_id'],
				'quantity'       => $product['quantity'],
				'author_id'      => (int) $post->post_author,
				'status'         => $item->status,
				'product_sub_id' => $product['product_sub_id']
			);

			tajer_insert_statistics( $args );
		}
	}

	function send_notification_emails( $response ) {
		do_action( 'tajer_send_notification_emails', $response );
		$is_purchase_notification_enable = tajer_get_option( 'enable_purchase_receipt_notification', 'tajer_emails_settings', '' );
		$enable_sale_notification        = tajer_get_option( 'enable_sale_notification', 'tajer_emails_settings', '' );

		if ( $enable_sale_notification != 'yes' && $is_purchase_notification_enable != 'yes' ) {
			return;
		}

		$user         = get_user_by( 'id', $response['user_id'] );
		$general_opts = array(
			'user'         => $user,
			'order_number' => $response['tajer_order_id'],
			'price'        => $response['total']
		);

		$general_opts = apply_filters( 'tajer_send_notification_emails_general_opts', $general_opts, $user, $response );

		if ( $is_purchase_notification_enable == 'yes' ) {
			do_action( 'tajer_send_purchase_notification_email', $general_opts, $user, $response );
			$purchase_subject      = tajer_get_option( 'purchase_receipt_email_subject', 'tajer_emails_settings', '' );
			$purchase_message      = tajer_get_option( 'purchase_receipt_email_body', 'tajer_emails_settings', '' );
			$purchase_subject_args = array_merge( $general_opts, array(
				'content' => $purchase_subject
			) );
			$purchase_subject_args = apply_filters( 'tajer_purchase_notification_email_subject_args', $purchase_subject_args, $purchase_subject, $user, $response );
			$purchase_body_args    = array_merge( $general_opts, array(
				'content' => $purchase_message
			) );
			$purchase_body_args    = apply_filters( 'tajer_purchase_notification_email_body_args', $purchase_body_args, $purchase_subject, $purchase_message, $user, $response );

			$purchase_filtered_subject = tajer_prepare_mail_body( $purchase_subject_args );
			$purchase_filtered_subject = apply_filters( 'tajer_purchase_filtered_email_subject', $purchase_filtered_subject, $purchase_subject_args, $purchase_body_args, $purchase_subject, $purchase_message, $user, $response );

			$purchase_filtered_body = tajer_prepare_mail_body( $purchase_body_args );
			$purchase_filtered_body = apply_filters( 'tajer_purchase_filtered_email_body', $purchase_filtered_body, $purchase_subject_args, $purchase_body_args, $purchase_subject, $purchase_message, $user, $response );

			wp_mail( $user->user_email, $purchase_filtered_subject, $purchase_filtered_body, '', apply_filters( 'tajer_purchase_receipt_notification_attachments', array(), $response, $user, $purchase_filtered_subject, $purchase_filtered_body ) );
		}

		if ( $enable_sale_notification == 'yes' ) {
			do_action( 'tajer_send_sale_notification_email', $general_opts, $user, $response );
			$sale_notification_subject      = tajer_get_option( 'new_sale_notification_subject', 'tajer_emails_settings', '' );
			$sale_notification_message      = tajer_get_option( 'new_sale_notification_body', 'tajer_emails_settings', '' );
			$sale_notification_subject_args = array_merge( $general_opts, array(
				'content' => $sale_notification_subject
			) );
			$sale_notification_subject_args = apply_filters( 'tajer_sale_notification_email_subject_args', $sale_notification_subject_args, $sale_notification_subject, $sale_notification_message, $user, $response );

			$sale_notification_body_args = array_merge( $general_opts, array(
				'content' => $sale_notification_message
			) );

			$sale_notification_body_args = apply_filters( 'tajer_sale_notification_email_body_args', $sale_notification_body_args, $sale_notification_subject_args, $sale_notification_subject, $sale_notification_message, $user, $response );

			$sale_notification_filtered_subject = tajer_prepare_mail_body( $sale_notification_subject_args );
			$sale_notification_filtered_subject = apply_filters( 'tajer_sale_notification_email_filtered_subject', $sale_notification_filtered_subject, $sale_notification_subject_args, $sale_notification_body_args, $sale_notification_subject, $sale_notification_message, $user, $response );

			$sale_notification_filtered_body = tajer_prepare_mail_body( $sale_notification_body_args );

			$sale_notification_filtered_body = apply_filters( 'tajer_sale_notification_email_filtered_body', $sale_notification_filtered_body, $sale_notification_filtered_subject, $sale_notification_subject_args, $sale_notification_body_args, $sale_notification_subject, $sale_notification_message, $user, $response );

			wp_mail( get_option( 'admin_email' ), $sale_notification_filtered_subject, $sale_notification_filtered_body );
		}
	}

	/**
	 * This function upgrade the bundle by removing its old products and creating new products.
	 *
	 * @param $bundle_user_product_id
	 * @param $response
	 * @param $item
	 * @param $action_id
	 * @param $callback
	 * @param array $args
	 */
	function tajer_upgrade_handle_bundle_product( $bundle_user_product_id, $response, $item, $action_id, $callback, $args = array() ) {

//		if ( apply_filters( 'tajer_upgrade_bundle_products', false, $bundle_user_product_id, $response, $item, $action_id, $callback, $args ) ) {
//			$this->tajer_upgrade_handle_bundle_product_products_upgrade( $bundle_user_product_id, $response, $item, $action_id, $callback, $args );
//
//			return;
//		}

		$bundle_products = unserialize( tajer_get_user_product_meta( $bundle_user_product_id, 'bundle_products' ) );

		$new_bundle_products = array();

		//First delete current bundle products
		tajer_delete_user_products_in( array_map( 'intval', $bundle_products ) );

		//Upgrade the bundle itself
		$a      = array( $bundle_user_product_id );
		$params = array_merge( $a, $args );
		call_user_func_array( $callback, $params );

		//Get bundle information
		$bundle = Tajer_DB::get_row_by_id( 'tajer_user_products', $bundle_user_product_id );

		//Get current bundle products
		$bundled_products = get_post_meta( $bundle->product_id, 'tajer_bundled_products', true );
		foreach ( $bundled_products as $id => $bundled_product ) {

			//Get only the products that attached with (int) reset($args) price
			if ( (int) $bundled_product['price'] != (int) reset( $args ) ) {
				continue;
			}

			if ( in_array( 'all', $bundled_product['sub_ids'] ) ) {
				$get_product_sub_ids = tajer_get_product_sub_ids( (int) $bundled_product['product'] );
				foreach ( $get_product_sub_ids as $productSubId ) {
					//Insert these products
					$opts = array(
						'user_id'           => $bundle->user_id,
						'buying_date'       => $bundle->buying_date,
						'expiration_date'   => tajer_expiration_date( (int) $bundled_product['product'], $productSubId, $bundle->buying_date ),
						'order_id'          => $item->id,
						'product_id'        => (int) $bundled_product['product'],
						'product_sub_id'    => $productSubId,
						'status'            => 'active',
						'activation_method' => 'buy'
					);;

					$result = tajer_insert_user_product( $opts );

					$new_bundle_products[] = $result['id'];
				}
			} else {
				foreach ( $bundled_product['sub_ids'] as $productSubId ) {

					//Insert these products
					$opts = array(
						'user_id'           => $bundle->user_id,
						'buying_date'       => $bundle->buying_date,
						'expiration_date'   => tajer_expiration_date( (int) $bundled_product['product'], $productSubId, $bundle->buying_date ),
						'order_id'          => $item->id,
						'product_id'        => (int) $bundled_product['product'],
						'product_sub_id'    => $productSubId,
						'status'            => 'active',
						'activation_method' => 'buy'
					);;

					$result = tajer_insert_user_product( $opts );

					$new_bundle_products[] = $result['id'];
				}
			}
		}

		tajer_update_user_product_meta( $bundle_user_product_id, 'bundle_products', serialize( $new_bundle_products ) );
	}

	function tajer_upgrade_handle_bundle_product2( $bundle_user_product_id, $response, $item, $action_id, $callback, $args = array() ) {

		$attached_bundle_products = unserialize( tajer_get_user_product_meta( $bundle_user_product_id, 'bundle_products' ) );

		//Upgrade the bundle itself
		$a      = array( $bundle_user_product_id );
		$params = array_merge( $a, $args );
		call_user_func_array( $callback, $params );

		//Get current bundle products
		$currend_bundled_products = get_post_meta( $args[2], 'tajer_bundled_products', true );


		//Now apply the callback on the bundle products
		foreach ( $attached_bundle_products as $bundle_product ) {
			$customer = Tajer_DB::get_row_by_id( 'tajer_user_products', $bundle_product );

			foreach ( $currend_bundled_products as $id => $bundled_product ) {

				//Get only the products that attached with (int) reset($args) price
				if ( (int) $bundled_product['price'] != (int) reset( $args ) ) {
					continue;
				}

				$product_id = $customer->product_id;

				if ( $product_id != (int) $bundled_product['product'] ) {
					continue;
				}

				if ( in_array( 'all', $bundled_product['sub_ids'] ) ) {
					$get_product_sub_ids = tajer_get_product_sub_ids( (int) $bundled_product['product'] );
					foreach ( $get_product_sub_ids as $productSubId ) {

						if ( (int) $productSubId != (int) reset( $args ) ) {
							continue;
						}

						$this->upgrade_bundle_product( $callback, $customer, $productSubId, $product_id, $bundle_product, $args );
					}
				} else {
					foreach ( $bundled_product['sub_ids'] as $productSubId ) {
						if ( (int) $productSubId != (int) reset( $args ) ) {
							continue;
						}

						$this->upgrade_bundle_product( $callback, $customer, $productSubId, $product_id, $bundle_product, $args );
					}
				}
			}
		}
	}

	/**
	 * Another way to upgrade the bundle.
	 * This function upgrade the current bundle product instead of remove it
	 *
	 * @param $bundle_user_product_id
	 * @param $response
	 * @param $item
	 * @param $action_id
	 * @param $callback
	 * @param array $args
	 */
	function tajer_upgrade_handle_bundle_product_products_upgrade( $bundle_user_product_id, $response, $item, $action_id, $callback, $args = array() ) {

		$bundle_products = unserialize( tajer_get_user_product_meta( $bundle_user_product_id, 'bundle_products' ) );

		//First apply the callback on the bundle itself
		$a      = array( $bundle_user_product_id );
		$params = array_merge( $a, $args );
		call_user_func_array( $callback, $params );

		//Now apply the callback on the bundle products
		foreach ( $bundle_products as $bundle_product ) {
			$customer = Tajer_DB::get_row_by_id( 'tajer_user_products', $bundle_product );

			$product_id     = $customer->product_id;
			$product_sub_id = $customer->product_sub_id;

			$upgrade    = get_post_meta( $product_id, 'tajer_upgrade', true );
			$upgrade_to = $upgrade[ $action_id ]['upgrade_to'];

			$new_expiration_date = tajer_expiration_date( $product_id, $upgrade_to, $customer->buying_date );

			$a = array(
				(int) $bundle_product,
				$upgrade_to,
				$new_expiration_date,
				$product_id,
				$product_sub_id,
				end( $args )
			);
//			$params = array_merge( $a, $args );
			call_user_func_array( $callback, $a );

		}

	}

	public function upgrade_bundle_product( $callback, $user_product, $productSubId, $product_id, $bundle_product, $args ) {
		$product_sub_id = $user_product->product_sub_id;

		$upgrade_to          = $productSubId;
		$new_expiration_date = $this->user_product_upgrade_expiration_date( $user_product, $product_id, $upgrade_to );


		$a = array(
			(int) $bundle_product,
			$upgrade_to,
			$new_expiration_date,
			$product_id,
			$product_sub_id,
			end( $args )
		);
		call_user_func_array( $callback, $a );

//		return array(
//			$product_sub_id,
//			$upgrade_to,
//			$current_user_product_expiration_date,
//			$expiration_date,
//			$renew_interval,
//			$n,
//			$new_expiration_date,
//			$args,
//			$a
//		);
	}

	public function user_product_upgrade_expiration_date( $user_product, $product_id, $upgrade_to ) {
		/**
		 * 1) For the old price id
		 */
		//Expiration date calculated from the buying date
		$expiration_date = new DateTime( tajer_expiration_date( $product_id, $user_product->product_sub_id, $user_product->buying_date ) );

		//Current user product expiration date recorded in the database
		$current_user_product_expiration_date = new DateTime( $user_product->expiration_date );


		/**
		 * We need to know if the user renew its product, then if he/she renew it we want to add this interval to it
		 * $renew_interval in seconds
		 */
		$renew_interval = $current_user_product_expiration_date->getTimestamp() - $expiration_date->getTimestamp();

		/**
		 * 2) For the new price id
		 */

		//Expiration date calculated from the buying date
		$expiration_date2 = new DateTime( tajer_expiration_date( $product_id, $upgrade_to, $user_product->buying_date ) );


		//Add renew interval
		$n = $expiration_date2->modify( '+' . $renew_interval . ' seconds' );

		$new_expiration_date = $n->format( 'Y-m-d H:i:s' );

		return $new_expiration_date;
	}
}
