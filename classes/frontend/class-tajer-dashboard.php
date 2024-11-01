<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Dashboard {

	public $user_products;
	public $pagination_links = array();

	private static $instance;

	private function __construct() {
		add_action( 'wp_ajax_nopriv_tajer_remove_user_product_from_frontend', array(
			$this,
			'remove_user_product_from_frontend'
		) );
		add_action( 'wp_ajax_tajer_remove_user_product_from_frontend', array(
			$this,
			'remove_user_product_from_frontend'
		) );
		add_shortcode( 'tajer_dashboard', array( $this, 'tajer_dashboard_callback' ) );
		add_action( 'init', array( $this, 'send_file_to_the_browser' ) );
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	function remove_user_product_from_frontend() {
		$nonce = $_REQUEST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'tajer_remove_frontend_product' ) ) {
			wp_die( 'Security check' );
		}

		if ( ! is_user_logged_in() ) {
			wp_die( 'Security check' );
		}

		do_action( 'tajer_remove_user_product_from_frontend', $this );

		$item_id    = (int) $_REQUEST['user_product_id'];
		$is_deleted = tajer_delete_user_product( $item_id );

		if ( $is_deleted !== false ) {
			$message = __( 'Product Removed Successfully!', 'tajer' );
			$status  = 'remove';
		} else {
			$message = __( 'Cant Remove The Product!', 'tajer' );
			$status  = 'error';
		}

		$response = array(
			'message' => $message,
			'status'  => $status
		);

		$response = apply_filters( 'tajer_remove_user_product_from_frontend_response', $response, $is_deleted, $this );

		tajer_response( $response );
	}

	function tajer_dashboard_callback() {

		do_action( 'tajer_frontend_dashboard_callback', $this );

		if ( ! is_user_logged_in() ) {
			tajer_get_template_part( 'restrict-dashboard-access' );

//			return apply_filters( 'tajer_frontend_dashboard_not_logged_in_message', __( 'Please login to access your products.', 'tajer' ), $this );
			return;
		}

		//get user items as object
//		$items = Tajer_DB::get_user_products();


		$page                   = ( intval( get_query_var( 'paged' ) ) ) ? intval( get_query_var( 'paged' ) ) : 1;
		$pagination             = new Tajer_Pagination( $page, apply_filters( 'tajer_frontend_dashboard_total_items', 20 ), Tajer_DB::count_items( 'tajer_user_products', true ) );
		$items                  = Tajer_DB::get_items_with_offset( 'tajer_user_products', $pagination->offset(), $pagination->per_page, 'buying_date', 'DESC', true );
		$this->pagination_links = tajer_get_pagination_links( $pagination );


		if ( empty( $items ) || is_null( $items ) ) {
			tajer_get_template_part( 'empty-dashboard' );

//			return apply_filters( 'tajer_frontend_dashboard_no_products_message', __( 'You haven\'t buy any product yet!', 'tajer' ), $items, $this );
			return;
		}

		$this->dashboard_page_render( $items );

	}

	function send_file_to_the_browser() {

		do_action( 'tajer_send_file_to_the_browser', $this );

		if ( isset( $_GET['tajer_action'] ) && ( ( $_GET['tajer_action'] ) == 'download' ) && is_user_logged_in() ) {

			$nonce = $_REQUEST['_wpnonce'];
			if ( ! wp_verify_nonce( $nonce, 'tajer_download' ) ) {
				wp_die( apply_filters( 'tajer_send_file_to_the_browser_violate_nonce_message', __( "Security check", 'tajer' ) ), $this );
			}

			do_action( 'tajer_send_file_to_the_browser_secured', $this );

			$item = (int) $_GET['upid'];
			if ( ! $this->is_customer( $item ) ) {
				wp_die( apply_filters( 'tajer_send_file_to_the_browser_violate_ownership_message', __( "Unfortunately you can't download this file!", 'tajer' ), $this ) );
			}

			$files = tajer_get_files_ids_with_attachments_ids( 0, 0, $item );

			if ( empty( $files ) ) {
				return false;
			}

			$attachment_id = '';
			foreach ( $files as $file_id => $attach_id ) {
				if ( $file_id == $_GET['fid'] ) {
					$attachment_id = $attach_id;
				}
			}

			$attachment_path = get_attached_file( $attachment_id );

			if ( ! ( file_exists( $attachment_path ) ) ) {
				wp_die( apply_filters( 'tajer_send_file_to_the_browser_file_not_exist_message', __( "The product doesn't exist!", 'tajer' ), $files, $attachment_path, $this ) );
			}

			do_action( 'tajer_send_file_to_the_browser_file_exists', $files, $attachment_path, $this );


			//Record this download
			$user_product = Tajer_DB::get_user_product( $item );

			$this->increase_number_of_downloads( $user_product );

			$data = array(
				'user_product'   => $item,
				'product_id'     => $user_product->product_id,
				'product_sub_id' => $user_product->product_sub_id,
				'file_id'        => $attachment_id
			);

			tajer_insert_download( $data );


			/**
			 * You can do a check here, to see if the user is logged in, for example, or if
			 * the current IP address has already downloaded it, the possibilities are endless.
			 */
			if ( file_exists( $attachment_path ) ) {
				$download = new Tajer_Downloader( $attachment_path );
				$download->download();
			}
		}

		return false;
	}

	function is_customer( $item ) {

		do_action( 'tajer_frontend_dashboard_is_customer', $item, $this );

		$result = Tajer_DB::get_user_product( $item );

		if ( is_null( $result ) ) {
			return apply_filters( 'tajer_frontend_dashboard_is_customer_response', false, $item, $this );
		} elseif ( ( date( 'Y-m-d H:i:s' ) > $result->expiration_date ) ) {
			return apply_filters( 'tajer_frontend_dashboard_is_customer_response', false, $item, $this );
		} elseif ( ! tajer_can_download( $result ) ) {
			return apply_filters( 'tajer_frontend_dashboard_is_customer_response', false, $item, $this );
		}

		return apply_filters( 'tajer_frontend_dashboard_is_customer_response', true, $item, $this );
	}

	function dashboard_page_render( $items ) {

		ignore_user_abort( true );

		if ( ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 0 );
		}

		$items = apply_filters( 'tajer_frontend_dashboard_populate_data', $items, $this );

		foreach ( $items as $item ) {

			do_action( 'tajer_frontend_dashboard_populate_item_data', $items, $item, $this );

			//if the product was expired.
			if ( date( 'Y-m-d H:i:s' ) > $item->expiration_date ) {
				//delete user's product
				$is_deleted = tajer_delete_user_product( $item->id );

				if ( $is_deleted === false ) {
					$item->delete_expired_product_error = apply_filters( 'tajer_frontend_dashboard_delete_expired_product_error_message', __( 'Error contact website admin!', 'tajer' ), $is_deleted, $item, $items, $this );
//					return __( 'Error contact website admin!', 'tajer' );
				}

				$item->is_expired = true;
				continue;
			}
			//todo Mohammed add number of downloads and download limit to this dashboard
			//check if the user exceed the number of downloads limits
			$limits = tajer_get_download_limits( $item->product_id, $item->product_sub_id );
			if ( ( 0 != $limits ) && $item->number_of_downloads > $limits ) {
				$item->is_download_limit_exceeded = true;
				continue;
			}

			//check if the user product status is inactive
			if ( $item->status == 'inactive' ) {
				$item->is_inactive = true;
				continue;
			}

			$item->download_links = array();
			$files                = get_files_ids_with_files_names_array( $item->product_id, $item->product_sub_id, 0 );
			if ( ! empty( $files ) ) {
				foreach ( $files as $file_id => $file_name ) {
					$url                                = add_query_arg( apply_filters( 'tajer_frontend_dashboard_download_link_query_arg', array(
						'tajer_action' => 'download',
						'upid'         => $item->id,
						'fid'          => $file_id
					), $files, $file_id, $file_name, $item, $items, $this ) );
					$item->download_links[ $file_name ] = $url;
				}
				$item->is_downloadable = true;
			} else {
				$item->is_downloadable = false;
			}


			//check if there is a file attached to this customer id, in case not downloadable products.
//			$file_name             = tajer_get_file_name( $item->id );
//			$item->is_downloadable = empty( $file_name ) ? false : true;

			if ( tajer_get_option( 'disable_download', 'tajer_general_settings', '' ) != 'yes' ) {
				$item->is_download_disable = false;
			} else {
				$item->is_download_disable = true;
			}
			//check if the product is recurring
			if ( ( $item->activation_method != 'trial' ) && tajer_is_recurring( $item->product_id ) && ( tajer_get_option( 'enable_recurring', 'tajer_general_settings', '' ) == 'yes' ) ) {
				$item->is_recurring = true;
				$item->recurring    = $this->recurring( $item );
//				$item->recurring_url     = $recurring['recurring_url'];
//				$item->recurring_details = $recurring['recurring_details'];
			} else {
				$item->is_recurring = false;
			}

			//check if the product can upgrade
			if ( ( $item->activation_method != 'trial' ) && tajer_is_upgrade( $item->product_id ) && ( tajer_get_option( 'enable_upgrade', 'tajer_general_settings', '' ) == 'yes' ) ) {
				$item->is_upgrade = true;
				$item->upgrade    = $this->upgrade( $item );
//				$item->upgrade_url    = $upgrade['upgrade_url'];
//				$item->upgrade_prices = $upgrade['prices'];
//				$item->upgrade_detail = $upgrade['upgrade_detail'];
			} else {
				$item->is_upgrade = false;
			}

			if ( $item->activation_method == 'trial' ) {
				$item->is_trial         = true;
				$buy_or_add_to_cart_arr = $this->buy_or_add_to_cart( $item );
				$item->buy_now_url      = $buy_or_add_to_cart_arr['buy_now_url'];
				$item->add_to_cart_url  = $buy_or_add_to_cart_arr['add_to_cart_url'];
			} else {
				$item->is_trial = false;
			}

			//check if the product can deleted
			if ( tajer_get_option( 'enable_delete_product', 'tajer_general_settings', '' ) == 'yes' ) {
				$item->can_delete = true;
			} else {
				$item->can_delete = false;
			}

		}

		$items = apply_filters( 'tajer_frontend_dashboard_populated_data', $items );

		$this->user_products = $items;
		tajer_get_template_part( 'dashboard' );
	}


	function buy_or_add_to_cart( $item ) {
		do_action( 'tajer_frontend_dashboard_populate_buy_or_add_to_cart_data', $item );
		$buy_now_url = add_query_arg( apply_filters( 'tajer_frontend_dashboard_buy_now_link_query_arg', array(
			'tajer_action'   => 'buy_now',
			'product_id'     => $item->product_id,
			'product_sub_id' => $item->product_sub_id
		), $item ), get_permalink( intval( tajer_get_option( 'cart', 'tajer_general_settings', '' ) ) ) );

		$add_to_cart_url = add_query_arg( apply_filters( 'tajer_frontend_dashboard_add_to_cart_link_query_arg', array(
			'tajer_action'   => 'add_to_cart',
			'product_id'     => $item->product_id,
			'product_sub_id' => $item->product_sub_id
		), $item ), get_permalink( intval( tajer_get_option( 'cart', 'tajer_general_settings', '' ) ) ) );

		return apply_filters( 'tajer_frontend_dashboard_buy_or_add_to_cart_response', array(
			'buy_now_url'     => $buy_now_url,
			'add_to_cart_url' => $add_to_cart_url
		), $item );
	}

	public function upgrade( $item ) {
		do_action( 'tajer_frontend_dashboard_populate_upgrade_data', $item );
		$upgrade      = get_post_meta( $item->product_id, 'tajer_upgrade', true );
		$prices       = get_post_meta( $item->product_id, 'tajer_product_prices', true );
		$upgrade_urls = array();

		foreach ( $upgrade as $id => $detail ) {
			if ( ( isset( $detail['prices'] ) ) && is_array( $detail['prices'] ) && in_array( $item->product_sub_id, $detail['prices'] ) ) {
				$url            = add_query_arg( apply_filters( 'tajer_frontend_dashboard_upgrade_link_query_arg', array(
					'tajer_action'   => 'upgrade',
					'id'             => $item->id,
					'product_id'     => $item->product_id,
					'product_sub_id' => $item->product_sub_id,
					'action_id'      => $id
				), $upgrade, $prices, $id, $detail ), get_permalink( intval( tajer_get_option( 'cart', 'tajer_general_settings', '' ) ) ) );
				$upgrade_urls[] = array( 'url' => $url, 'detail' => $detail );
			}
		}

		return apply_filters( 'tajer_frontend_dashboard_upgrade_response', array(
			'upgrade_urls' => $upgrade_urls,
			'prices'       => $prices
		), $item, $upgrade );
	}

	public function recurring( $item ) {
		do_action( 'tajer_frontend_dashboard_populate_recurring_data', $item );
		$recurring      = get_post_meta( $item->product_id, 'tajer_recurring', true );
		$recurring_urls = array();
		foreach ( $recurring as $id => $detail ) {
			if ( ( isset( $detail['prices'] ) ) && is_array( $detail['prices'] ) && in_array( $item->product_sub_id, $detail['prices'] ) ) {
				$url              = add_query_arg( apply_filters( 'tajer_frontend_dashboard_recurring_link_query_arg', array(
					'tajer_action'   => 'recurring',
					'id'             => $item->id,
					'product_id'     => $item->product_id,
					'product_sub_id' => $item->product_sub_id,
					'action_id'      => $id
				), $recurring, $id, $detail ), get_permalink( intval( tajer_get_option( 'cart', 'tajer_general_settings', '' ) ) ) );
				$recurring_urls[] = array( 'url' => $url, 'detail' => $detail );
			}
		}

		return apply_filters( 'tajer_frontend_dashboard_recurring_response', $recurring_urls, $item, $recurring );
	}

	function increase_number_of_downloads( $user_product ) {

		do_action( 'tajer_increase_number_of_downloads', $user_product );

		$trial_record            = new stdClass();
		$is_trial_record_updated = false;

		//add new download
		$is_updated = Tajer_DB::update_number_of_downloads( $user_product->id, apply_filters( 'tajer_frontend_dashboard_before_updating_number_of_downloads_for_user_product', ( ( $user_product->number_of_downloads ) + 1 ), $user_product ) );

		//now increase the number_of_downloads in the trial table if the activation_method==trial
		if ( $user_product->activation_method == 'trial' ) {
			$user_data = get_user_by( 'id', $user_product->user_id );

			//get current number of downloads
			$trial_record = Tajer_DB::get_trial_by_email( $user_data->user_email, $user_product->product_id, $user_product->product_sub_id );

			//add new download
			$is_trial_record_updated = Tajer_DB::update_number_of_downloads( $trial_record->id, apply_filters( 'tajer_frontend_dashboard_before_updating_number_of_downloads_for_trial', ( ( $trial_record->number_of_downloads ) + 1 ), $user_product, $trial_record ), 'tajer_trials' );
		}


		if ( $is_updated === false ) {
			$response = false;
		} else {
			$response = true;
		}

		$response = apply_filters( 'tajer_increase_number_of_downloads_response', $response, $user_product, $is_updated, $trial_record, $is_trial_record_updated );

		return $response;
	}

}
