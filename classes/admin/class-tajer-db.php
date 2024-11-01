<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $tajer_orders_table_version;
global $tajer_order_meta_table_version;
global $tajer_shopping_carts_table_version;
global $tajer_downloads_table_version;
global $tajer_user_products_table_version;
global $tajer_user_product_meta_table_version;
global $tajer_trials_table_version;
global $tajer_statistics_table_version;
global $tajer_statistic_meta_table_version;
$tajer_orders_table_version            = '1.0';
$tajer_order_meta_table_version        = '1.0';
$tajer_shopping_carts_table_version    = '1.0';
$tajer_downloads_table_version         = '1.0';
$tajer_user_products_table_version     = '1.0';
$tajer_user_product_meta_table_version = '1.0';
$tajer_trials_table_version            = '1.0';
$tajer_statistics_table_version        = '1.0';
$tajer_statistic_meta_table_version    = '1.0';

class Tajer_DB {

	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		register_activation_hook( Tajer_DIR . 'tajer.php', array( $this, 'create_tables' ) );
	}

	public static function finalize_order( $tajer_order_id, $gateway_order_id ) {
		global $wpdb;
		$result = $wpdb->update(
			$wpdb->prefix . 'tajer_orders',
			array(
				'gateway_order_id' => $gateway_order_id,
				'status'           => 'completed'
			),
			array( 'id' => (int) $tajer_order_id ),
			array(
				'%s',
				'%s'
			),
			array( '%d' )
		);

		return $result;
	}

	public static function update_order_status( $order_id, $status ) {
		global $wpdb;

		$result = $wpdb->update(
			$wpdb->prefix . 'tajer_orders',
			array(
				'status' => $status
			),
			array( 'id' => $order_id ),
			array(
				'%s'
			),
			array( '%d' )
		);

		return $result;
	}

	public static function update_user_product( $args ) {
		global $wpdb;

		if ( is_email( $args['user'] ) ) {
			$user_data = get_user_by( 'email', $args['user'] );
		} else {
			$user_data = get_user_by( 'login', $args['user'] );
		}

		if ( ! $user_data ) {
			return false;
		}

		$result = $wpdb->update(
			$wpdb->prefix . 'tajer_user_products',
			array(
				'user_id'             => $user_data->ID,
				'buying_date'         => $args['buying_date'],
				'expiration_date'     => $args['expiration_date'],
				'order_id'            => $args['order_id'],
				'product_id'          => $args['product_id'],
				'status'              => $args['status'],
				'number_of_downloads' => $args['number_of_downloads'],
				'product_sub_id'      => $args['product_sub_id'],
				'activation_method'   => $args['activation_method']
			),
			array( 'id' => $args['item_id'] ),
			array(
				'%d',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%d',
				'%d',
				'%s'
			),
			array( '%d' )
		);

		return $result;
	}

	public static function update_user_products_status( $order_id, $status ) {
		global $wpdb;

		$result = $wpdb->update(
			$wpdb->prefix . 'tajer_user_products',
			array(
				'status' => $status
			),
			array( 'order_id' => $order_id ),
			array(
				'%s'
			),
			array( '%d' )
		);

		return $result;
	}

	static function insert_order( $data ) {
		global $wpdb;

		$is_insert = $wpdb->insert( $wpdb->prefix . 'tajer_orders', $data );

		return array( 'is_insert' => $is_insert, 'id' => $wpdb->insert_id );
	}

	public static function insert_user_product_by_email_or_login( $args ) {
		global $tajer_inserted_bundle_product_id;

		if ( is_email( $args['user'] ) ) {
			$user_data = get_user_by( 'email', $args['user'] );
		} else {
			$user_data = get_user_by( 'login', $args['user'] );
		}

		if ( ! $user_data ) {
			return false;
		}

		$data = array(
			'user_id'             => $user_data->ID,
			'buying_date'         => $args['buying_date'],
			'expiration_date'     => $args['expiration_date'],
			'order_id'            => $args['order_id'],
			'product_id'          => $args['product_id'],
			'status'              => $args['status'],
			'number_of_downloads' => $args['number_of_downloads'],
			'product_sub_id'      => $args['product_sub_id'],
			'activation_method'   => $args['activation_method']
		);

		if ( tajer_is_bundle( $args['product_id'] ) ) {
			tajer_handle_bundle_product( $args['product_id'], $args['product_sub_id'], 'tajer_insert_user_product', array( $data ), true );
			$result = $tajer_inserted_bundle_product_id ? array( 'is_insert' => 1 ) : array( 'is_insert' => 0 );
		} else {
			$result = tajer_insert_user_product( $data );
		}

		return $result;
	}

	static function insert_user_product( $data ) {
		global $wpdb;

		$is_insert = $wpdb->insert( $wpdb->prefix . 'tajer_user_products', $data );

		return array( 'is_insert' => $is_insert, 'id' => $wpdb->insert_id );
	}

	static function insert_download( $data ) {
		global $wpdb;

		$is_insert = $wpdb->insert( $wpdb->prefix . 'tajer_downloads', $data );

		return array( 'is_insert' => $is_insert, 'id' => $wpdb->insert_id );
	}

	static function insert_trial_record( $data ) {
		global $wpdb;

		$is_insert = $wpdb->insert( $wpdb->prefix . 'tajer_trials', $data );

		return array( 'is_insert' => $is_insert, 'id' => $wpdb->insert_id );
	}

	static function update_meta( $type = 'order', $id, $meta_key, $meta_value ) {
		global $wpdb;

		switch ( $type ) {
			case 'order':
				$table       = $wpdb->prefix . 'tajer_order_meta';
				$column_name = 'order_id';
				break;
			case 'statistics':
				$table       = $wpdb->prefix . 'tajer_statistic_meta';
				$column_name = 'statistics_id';
				break;

			case 'user_products':
				$table       = $wpdb->prefix . 'tajer_user_product_meta';
				$column_name = 'user_product_id';
				break;

			default:
//				do_action( 'tajer_update_' . $type . '_meta', $meta_key, $meta_value );
				$table       = apply_filters( 'tajer_update_table_meta', '', $id, $type, $meta_key, $meta_value );
				$column_name = apply_filters( 'tajer_update_meta_column_name', '', $id, $type, $meta_key, $meta_value );
				break;
		}

		$exist = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table} WHERE meta_key = %s AND {$column_name} = %d"
			, $meta_key, $id ) );

		if ( $exist == null ) {
			//create new
			$data = array(
				$column_name => $id,
				'meta_key'   => $meta_key,
				'meta_value' => $meta_value
			);

			$result = self::add_meta( $table, $data );

			if ( $result['is_insert'] ) {
				$returned_value = $result['id'];
			} else {
				$returned_value = false;
			}
		} else {
			//update exist
			$result = $wpdb->update(
				$table,
				array(
					'meta_value' => $meta_value
				),
				array( 'meta_key' => $meta_key, $column_name => $id ),
				array(
					'%s'
				),
				array( '%s', '%d' )
			);

			if ( $result !== false ) {
				$returned_value = $exist->meta_id;
			} else {
				$returned_value = false;
			}
		}

		return $returned_value;
	}

	static function insert_statistics( $data ) {
		global $wpdb;

		$is_insert = $wpdb->insert( $wpdb->prefix . 'tajer_statistics', $data );

		return array( 'is_insert' => $is_insert, 'id' => $wpdb->insert_id );
	}

	static function add_meta( $table, $data ) {
		global $wpdb;

		$is_insert = $wpdb->insert( $table, $data );

		return array( 'is_insert' => $is_insert, 'id' => $wpdb->insert_id );
	}

	static function delete_meta( $table, $id, $column_name, $meta_key ) {
		global $wpdb;

		$is_deleted = $wpdb->delete( $wpdb->prefix . $table, array(
			$column_name => $id,
			'meta_key'   => $meta_key
		) );

		return $is_deleted;
	}

	static function get_meta( $table, $id, $column_name, $meta_key ) {
		global $wpdb;

		$table_name = $wpdb->prefix . $table;

		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT meta_value FROM {$table_name} WHERE meta_key = %s AND {$column_name} = %d"
			, $meta_key, $id ) );

		return $row->meta_value;
	}

	static function is_in_cart( $product_id, $product_sub_id ) {
		global $wpdb;

		$customer_details = tajer_customer_details();

		if ( is_array( $product_sub_id ) ) {

			//Source https://coderwall.com/p/zepnaw/sanitizing-queries-with-in-clauses-with-wpdb-on-wordpress
			$how_many = count( $product_sub_id );

			// prepare the right amount of placeholders
			// if you're looing for strings, use '%s' instead
			$placeholders = array_fill( 0, $how_many, '%d' );


			// glue together all the placeholders...
			// $format = '%d, %d, %d, %d, %d, [...]'
			$format = implode( ', ', $placeholders );

			// and put them in the query
			$query = "SELECT * FROM {$wpdb->prefix}tajer_shopping_carts WHERE product_sub_id IN($format)  AND product_id = %d AND user_id = %d";

			$product_sub_id[] = $product_id;
			$product_sub_id[] = $customer_details->id;

			// now you can get the results
			$is_in_cart = $wpdb->get_results( $wpdb->prepare( $query, $product_sub_id ) );
		} else {
			$is_in_cart = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}tajer_shopping_carts WHERE user_id = %d AND product_id = %d AND product_sub_id = %d"
				, $customer_details->id, (int) $product_id, (int) $product_sub_id ) );
		}


		return $is_in_cart;
	}

	static function has_user_product( $order_id, $product_id, $product_sub_id ) {
		global $wpdb;

		$has_user_product = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}tajer_user_products WHERE order_id = %d AND product_id = %d AND product_sub_id = %d"
			, (int) $order_id, (int) $product_id, (int) $product_sub_id ) );

		return $has_user_product;
	}

	static function count_user_products( $order_id, $product_id, $product_sub_id ) {
		global $wpdb;

		$times = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}tajer_user_products WHERE order_id = %d AND product_id = %d AND product_sub_id = %d", $order_id, $product_id, $product_sub_id ) );

		return $times;
	}

	public function create_tables() {
		global $wpdb;
		global $tajer_orders_table_version;
		global $tajer_order_meta_table_version;
		global $tajer_shopping_carts_table_version;
		global $tajer_downloads_table_version;
//		global $tajer_customer_websites_table_version;
		global $tajer_user_products_table_version;
		global $tajer_user_product_meta_table_version;
		global $tajer_trials_table_version;
		global $tajer_statistics_table_version;
		global $tajer_statistic_meta_table_version;


		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$table_order_name = $wpdb->prefix . 'tajer_orders';
		// create the ECPT metabox database table
		$sql = "CREATE TABLE IF NOT EXISTS {$table_order_name} (
 `id` bigint(20) NOT NULL AUTO_INCREMENT,
 `gateway` varchar(100) NOT NULL,
 `gateway_order_id` varchar(100) NOT NULL,
 `user_id` bigint(20) NOT NULL,
 `cart_ids` longtext NOT NULL,
 `total` decimal(15,4) NOT NULL,
 `products` longtext NOT NULL,
 `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `secret_code` varchar(50) NOT NULL,
 `status` varchar(15) NOT NULL,
 `coupon` varchar(100) NOT NULL,
 `action` varchar(25) NOT NULL,
 `action_id` int(11) NOT NULL,
 `ip` varchar(39) NOT NULL,
 PRIMARY KEY (`id`))$charset_collate;";
		dbDelta( $sql );
		add_option( "tajer_orders_table_version", $tajer_orders_table_version );

		$table_shopping_cart_name = $wpdb->prefix . 'tajer_shopping_carts';
		$sql                      = "CREATE TABLE IF NOT EXISTS {$table_shopping_cart_name} (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `product_id` bigint(20) NOT NULL,
  `product_sub_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`)){$charset_collate};";
		dbDelta( $sql );
		add_option( "tajer_shopping_carts_table_version", $tajer_shopping_carts_table_version );


		$table_downloads_name = $wpdb->prefix . 'tajer_downloads';
		$sql                  = "CREATE TABLE IF NOT EXISTS {$table_downloads_name} (
 `id` bigint(20) NOT NULL AUTO_INCREMENT,
 `user_product` bigint(20) NOT NULL,
 `product_id` bigint(20) NOT NULL,
 `product_sub_id` int(11) NOT NULL,
 `user_id` bigint(20) NOT NULL,
 `file_id` bigint(20) NOT NULL,
 `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `ip` varchar(39) NOT NULL,
 PRIMARY KEY (`id`)){$charset_collate};";
		dbDelta( $sql );
		add_option( "tajer_downloads_table_version", $tajer_downloads_table_version );


		$table_user_products_name = $wpdb->prefix . 'tajer_user_products';
		$sql                      = "CREATE TABLE IF NOT EXISTS {$table_user_products_name} (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `buying_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expiration_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `order_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `product_sub_id` int(11) NOT NULL,
  `number_of_downloads` bigint(20) NOT NULL,
  `status` varchar(8) NOT NULL,
  `activation_method` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)){$charset_collate};";
		dbDelta( $sql );
		add_option( "tajer_user_products_table_version", $tajer_user_products_table_version );


		$table_user_products_meta_name = $wpdb->prefix . 'tajer_user_product_meta';
		$sql                           = "CREATE TABLE IF NOT EXISTS {$table_user_products_meta_name} (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_product_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext,
  PRIMARY KEY (`meta_id`),
  KEY `user_product_id` (`user_product_id`),
  KEY `meta_key` (`meta_key`(191))){$charset_collate};";
		dbDelta( $sql );
		add_option( "tajer_user_product_meta_table_version", $tajer_user_product_meta_table_version );


		$table_order_meta_name = $wpdb->prefix . 'tajer_order_meta';
		$sql                   = "CREATE TABLE IF NOT EXISTS {$table_order_meta_name} (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext,
  PRIMARY KEY (`meta_id`),
  KEY `order_id` (`order_id`),
  KEY `meta_key` (`meta_key`(191))){$charset_collate};";
		dbDelta( $sql );
		add_option( "tajer_order_meta_table_version", $tajer_order_meta_table_version );


		$table_statistics_meta_name = $wpdb->prefix . 'tajer_statistic_meta';
		$sql                        = "CREATE TABLE IF NOT EXISTS {$table_statistics_meta_name} (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `statistics_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext,
  PRIMARY KEY (`meta_id`),
  KEY `statistics_id` (`statistics_id`),
  KEY `meta_key` (`meta_key`(191))){$charset_collate};";
		dbDelta( $sql );
		add_option( "tajer_statistic_meta_table_version", $tajer_statistic_meta_table_version );


		$table_trial_name = $wpdb->prefix . 'tajer_trials';
		$sql              = "CREATE TABLE IF NOT EXISTS {$table_trial_name} (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `ip` varchar(39) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `product_sub_id` int(11) NOT NULL,
  `trial_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `number_of_downloads` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)){$charset_collate};";
		dbDelta( $sql );
		add_option( "tajer_trials_table_version", $tajer_trials_table_version );

		$table_statistics_name = $wpdb->prefix . 'tajer_statistics';
		$sql                   = "CREATE TABLE IF NOT EXISTS {$table_statistics_name} (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `buying_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `product_id` bigint(20) NOT NULL,
  `product_sub_id` int(11) NOT NULL,
  `author_id` bigint(20) NOT NULL,
  `earnings` decimal(15,4) NOT NULL,
  `status` varchar(15) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`)){$charset_collate};";
		dbDelta( $sql );
		add_option( "tajer_statistics_table_version", $tajer_statistics_table_version );

	}

	static function add_to_cart( $product_id, $product_sub_id, $user_id = null, $date = null, $quantity = 1 ) {
		global $wpdb;

		$customer_details = tajer_customer_details();

		if ( is_null( $user_id ) ) {
			$user_id = $customer_details->id;
		}

		if ( is_null( $date ) ) {
			$date = date( 'Y-m-d H:i:s' );
		}

		//is the user already have this product
		$is_in_cart = self::is_in_cart( $product_id, $product_sub_id );

		//if the product doesn't in the user's cart add it to cart
		if ( $is_in_cart == null ) {
			$data = array(
				'user_id'        => $user_id,
				'date'           => $date,
				'product_id'     => $product_id,
				'product_sub_id' => $product_sub_id,
				'quantity'       => $quantity
			);

			$result = $wpdb->insert( $wpdb->prefix . 'tajer_shopping_carts', $data );

			if ( $result ) {
				$message = __( 'Product Added To Cart Successfully!', 'tajer' );
				$status  = 'add';
				$row_id  = $wpdb->insert_id;
			} else {
				$message = __( 'I Cant Add The Product!', 'tajer' );
				$status  = 'error';
				$row_id  = 'error';
			}
		} else {
			$message = __( 'Product Already In The Cart!', 'tajer' );
			$status  = 'exist';//todo Mohammed change from 'error' to 'exist'
			$row_id  = $is_in_cart->id;//todo Mohammed change 'error' to $is_in_cart->id
		}

		return array( $message, $status, $row_id );
	}

//	static function delete_cart_item_by_product_details( $product_id, $product_sub_id ) {
//		global $wpdb;
//		$is_deleted = $wpdb->delete( $wpdb->prefix . 'tajer_shopping_carts', array(
//			'id'      => $item_id,
//			'user_id' => get_current_user_id()
//		) );
//
//		return $is_deleted;
//	}

//	static function add_remove_from_cart( $product_id, $product_sub_id ) {
//		global $wpdb;
//
//		//is the user already have this trial active
//		$is_in_cart = self::is_in_cart( $product_id, $product_sub_id );
//
//		//if the product doesn't in the user's cart add it to cart
//		if ( $is_in_cart == null ) {
//			$data = array(
//				'user_id'        => get_current_user_id(),
//				'date'           => date( 'Y-m-d H:i:s' ),
//				'product_id'     => $product_id,
//				'product_sub_id' => $product_sub_id,
//				'quantity'       => 1
//			);
//
//			$result = $wpdb->insert( $wpdb->prefix . 'tajer_shopping_carts', $data );
//
//			if ( $result ) {
//				$message = __( 'Product Added To Cart Successfully!', 'tajer' );
//				$status  = 'add';
//				$row_id  = $wpdb->insert_id;
//			} else {
//				$message = __( 'I Cant Add The Product!', 'tajer' );
//				$status  = 'error';
//				$row_id  = 'error';
//			}
//			//if the product in the user's cart remove it from the cart
//		} else {
//			$is_deleted = $wpdb->delete( $wpdb->prefix . 'tajer_shopping_carts', array( 'id' => $is_in_cart->id ), array( '%d' ) );
//			if ( $is_deleted !== false ) {
//				$message = __( 'Product Removed From Cart!', 'tajer' );
//				$status  = 'remove';
//				$row_id  = 0;
//			} else {
//				$message = __( 'Cant Remove The Product!', 'tajer' );
//				$status  = 'error';
//				$row_id  = 'error';
//			}
//		}
//
//		return array( $message, $status, $row_id );
//	}

	static function empty_cart() {
		global $wpdb;

		$customer_details = tajer_customer_details();

		$is_deleted = $wpdb->delete( $wpdb->prefix . 'tajer_shopping_carts', array( 'user_id' => $customer_details->id ) );

		return $is_deleted;
	}

	static function get_row_by_product_id_product_sub_id_user_id( $table, $product_id, $product_sub_id, $user_id ) {
		global $wpdb;
		$is_deleted = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}{$table} WHERE product_id = %d AND product_sub_id = %d AND user_id = %d"
			, $product_id, $product_sub_id, $user_id ) );

		return $is_deleted;
	}

	static function get_expired_users_products( $date ) {
		global $wpdb;

		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tajer_user_products WHERE DATE (expiration_date) < %s", $date ) );

		return $items;
	}

	static function update_quantity( $item_id, $quantity ) {
		global $wpdb;

		$is_updated = $wpdb->update(
			$wpdb->prefix . 'tajer_shopping_carts',
			array(
				'quantity' => $quantity
			),
			array( 'id' => $item_id ),
			array(
				'%d'
			),
			array( '%d' )
		);

		return $is_updated;
	}

	static function get_cart_items() {
		global $wpdb;

		$customer_details = tajer_customer_details();

		$items = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}tajer_shopping_carts WHERE user_id = %d", $customer_details->id
		) );

		return $items;
	}

	static function get_cart_items_in( $cart_items_ids ) {
		global $wpdb;

		//Source https://coderwall.com/p/zepnaw/sanitizing-queries-with-in-clauses-with-wpdb-on-wordpress
		$how_many = count( $cart_items_ids );

		// prepare the right amount of placeholders
		// if you're looing for strings, use '%s' instead
		$placeholders = array_fill( 0, $how_many, '%d' );


		// glue together all the placeholders...
		// $format = '%d, %d, %d, %d, %d, [...]'
		$format = implode( ', ', $placeholders );

		// and put them in the query
		$query = "SELECT * FROM {$wpdb->prefix}tajer_shopping_carts WHERE id IN($format)";

		// now you can get the results
		$results = $wpdb->get_results( $wpdb->prepare( $query, $cart_items_ids ) );

		return $results;
	}

	static function get_user_product( $item_id ) {
		global $wpdb;

		$result = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}tajer_user_products WHERE id = %d"
			, $item_id ) );

		return $result;
	}

	static function get_user_products() {
		global $wpdb;

		$items = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}tajer_user_products WHERE user_id = %d"
			, get_current_user_id() ) );

		return $items;
	}

	static function get_user_products_in( $user_products_ids ) {
		global $wpdb;

		//Source https://coderwall.com/p/zepnaw/sanitizing-queries-with-in-clauses-with-wpdb-on-wordpress
		$how_many = count( $user_products_ids );

		// prepare the right amount of placeholders
		// if you're looing for strings, use '%s' instead
		$placeholders = array_fill( 0, $how_many, '%d' );


		// glue together all the placeholders...
		// $format = '%d, %d, %d, %d, %d, [...]'
		$format = implode( ', ', $placeholders );

		// and put them in the query
		$query = "SELECT * FROM {$wpdb->prefix}tajer_user_products WHERE id IN($format)";

		// now you can get the results
		$results = $wpdb->get_results( $wpdb->prepare( $query, $user_products_ids ) );

		return $results;
	}


	static function get_upgraded_user_products_in( $user_products_ids ) {
		global $wpdb;

		//Source https://coderwall.com/p/zepnaw/sanitizing-queries-with-in-clauses-with-wpdb-on-wordpress
		$how_many = count( $user_products_ids );

		// prepare the right amount of placeholders
		// if you're looing for strings, use '%s' instead
		$placeholders = array_fill( 0, $how_many, '%d' );


		// glue together all the placeholders...
		// $format = '%d, %d, %d, %d, %d, [...]'
		$format = implode( ', ', $placeholders );

		// and put them in the query
		$query = "SELECT * FROM {$wpdb->prefix}tajer_user_product_meta WHERE user_product_id IN($format) AND meta_key = %s";

		$user_products_ids[] = 'upgrade';

		// now you can get the results
		$results = $wpdb->get_results( $wpdb->prepare( $query, $user_products_ids ) );

		return $results;
	}

	static function get_recurred_user_products_in( $user_products_ids ) {
		global $wpdb;

		//Source https://coderwall.com/p/zepnaw/sanitizing-queries-with-in-clauses-with-wpdb-on-wordpress
		$how_many = count( $user_products_ids );

		// prepare the right amount of placeholders
		// if you're looing for strings, use '%s' instead
		$placeholders = array_fill( 0, $how_many, '%d' );


		// glue together all the placeholders...
		// $format = '%d, %d, %d, %d, %d, [...]'
		$format = implode( ', ', $placeholders );

		// and put them in the query
		$query = "SELECT * FROM {$wpdb->prefix}tajer_user_product_meta WHERE user_product_id IN($format) AND meta_key = %s";

		$user_products_ids[] = 'recurring';

		// now you can get the results
		$results = $wpdb->get_results( $wpdb->prepare( $query, $user_products_ids ) );

		return $results;
	}


	static function update_number_of_downloads( $item_id, $number_of_downloads, $table = 'tajer_user_products' ) {
		global $wpdb;

		$is_updated = $wpdb->update(
			$wpdb->prefix . $table,
			array(
				'number_of_downloads' => $number_of_downloads
			),
			array( 'id' => $item_id ),
			array(
				'%d'
			),
			array( '%d' )
		);

		return $is_updated;
	}


	static function delete_user_product( $item_id ) {
		global $wpdb;

		$is_deleted = $wpdb->delete( $wpdb->prefix . 'tajer_user_products', array( 'id' => $item_id ), array( '%d' ) );

		return $is_deleted;
	}

	static function delete_user_products_in( $user_product_ids ) {
		global $wpdb;

		$how_many = count( $user_product_ids );

		// prepare the right amount of placeholders
		// if you're looing for strings, use '%s' instead
		$placeholders = array_fill( 0, $how_many, '%d' );


		// glue together all the placeholders...
		// $format = '%d, %d, %d, %d, %d, [...]'
		$format = implode( ', ', $placeholders );

		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}tajer_user_products WHERE id IN ($format)", $user_product_ids ) );

		return $result;
	}

	static function delete_user_products_by_order_id( $order_id ) {
		global $wpdb;

		$is_deleted = $wpdb->delete( $wpdb->prefix . 'tajer_user_products', array( 'order_id' => $order_id ), array( '%d' ) );

		return $is_deleted;
	}

	static function has_trial( $user_id, $product_id, $product_sub_id ) {
		global $wpdb;

		$has_trial = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}tajer_user_products WHERE product_id = %d AND product_sub_id = %d AND user_id = %d AND activation_method = %s"
			, (int) $product_id, (int) $product_sub_id, $user_id, 'trial' ) );

		return $has_trial;
	}

	static function get_trial_by_ip( $user_ip, $product_id, $product_sub_id ) {
		global $wpdb;

		$item = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}tajer_trials WHERE ip = %s AND product_id = %d AND product_sub_id = %d"
			, $user_ip, (int) $product_id, (int) $product_sub_id ) );

		return $item;
	}

	static function get_trial_by_email( $user_email, $product_id, $product_sub_id ) {
		global $wpdb;

		$item = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}tajer_trials WHERE email = %s AND product_id = %d AND product_sub_id = %d"
			, $user_email, (int) $product_id, (int) $product_sub_id ) );

		return $item;
	}

	static function delete_by_id( $table, $id ) {
		global $wpdb;

		$is_deleted = $wpdb->delete( $wpdb->prefix . $table, array(
			'id' => $id
		) );

		return $is_deleted;
	}


	static function delete_cart_item( $item_id ) {
		global $wpdb;

		$customer_details = tajer_customer_details();

		$is_deleted = $wpdb->delete( $wpdb->prefix . 'tajer_shopping_carts', array(
			'id'      => $item_id,
			'user_id' => $customer_details->id
		) );

		return $is_deleted;
	}

	static function count_coupon_used( $coupon ) {
		global $wpdb;
		$times = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}tajer_orders WHERE status = %s AND coupon = %s", 'completed', $coupon ) );

		return $times;
	}

	static function get_items_with_offset( $table, $offset, $items, $order_by = 'id', $order = 'ASC', $for_current_user = false, $user_id_column_name = 'user_id' ) {
		global $wpdb;

		if ( ! $for_current_user ) {
			$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$table} ORDER BY {$order_by} {$order} LIMIT %d,%d", $offset, $items ) );
		} else {
			$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$table} WHERE {$user_id_column_name} = %d ORDER BY {$order_by} {$order} LIMIT %d,%d", get_current_user_id(), $offset, $items ) );
		}

		return $items;
	}

	static function get_items( $table, $items, $order_by = 'id', $order = 'ASC' ) {
		global $wpdb;

		//get user items as object
		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$table} ORDER BY {$order_by} {$order} LIMIT %d", $items ) );

		return $items;
	}

	static function count_items( $table, $for_current_user = false, $user_id_column_name = 'user_id' ) {
		global $wpdb;

		//check how many rows we have
		if ( $for_current_user ) {
			$items = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}{$table} WHERE {$user_id_column_name} = %d", get_current_user_id() ) );
		} else {
			$items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}{$table}" );
		}

		return $items;
	}

	static function count_product_sales( $product_id ) {
		global $wpdb;

		$items = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}tajer_statistics WHERE product_id = %d", $product_id ) );

		return $items;
	}

	static function count_product_downloads( $product_id ) {
		global $wpdb;

		$items = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}tajer_downloads WHERE product_id = %d", $product_id ) );

		return $items;
	}

	static function get_product_earnings( $product_id ) {
		global $wpdb;

		$items = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(earnings * quantity) FROM {$wpdb->prefix}tajer_statistics WHERE product_id = %d", $product_id ) );

		return $items;
	}

	static function get_row_by_id( $table, $id ) {
		global $wpdb;
		$item = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}{$table} WHERE id = %d"
			, $id ) );

		return $item;
	}

	static function get_items_statistics_by_buying_date( $from, $to, $output = OBJECT ) {
		global $wpdb;

		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tajer_statistics WHERE DATE (buying_date) BETWEEN %s AND %s ORDER BY buying_date ASC", $from, $to ), $output );

		return $items;
	}

	static function get_downloads_items_by_date( $from, $to, $output = OBJECT ) {
		global $wpdb;

		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tajer_downloads WHERE DATE (date) BETWEEN %s AND %s ORDER BY date ASC", $from, $to ), $output );

		return $items;
	}

	static function get_users_products_by_less_or_equal_expiration_date( $date ) {
		global $wpdb;

		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tajer_user_products WHERE DATE (expiration_date) <= %s", $date ) );

		return $items;
	}

	static function get_unnotified_users_products_by_less_or_equal_expiration_date( $date ) {
		global $wpdb;

		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tajer_user_products up WHERE DATE (expiration_date) <= %s AND up.id NOT IN (SELECT user_product_id FROM {$wpdb->prefix}tajer_user_product_meta upm WHERE meta_key='send_expiration_email' AND meta_value='true')", $date ) );

		return $items;
	}

//	static function get_user_products_by_order_id( $date ) {
//		global $wpdb;
//
//		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tajer_user_products WHERE DATE (expiration_date) <= %s", $date ) );
//
//		return $items;
//	}

	static function get_user_product_by_user_id( $user_id, $product_id, $product_sub_ids = 0 ) {
		global $wpdb;

		$items = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}tajer_user_products WHERE product_id = %d AND user_id = %d AND product_sub_id = %d"
			, $product_id, $user_id, $product_sub_ids ) );


		return $items;
	}

	static function get_user_product_by_product_id( $user_id, $product_id ) {
		global $wpdb;

		$items = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}tajer_user_products WHERE product_id = %d AND user_id = %d"
			, $product_id, $user_id ) );


		return $items;
	}

	public static function fail_order( $order_id, $message = '' ) {
		global $wpdb;
		$result = $wpdb->update(
			$wpdb->prefix . 'tajer_orders',
			array(
				'status' => 'failed',
			),
			array( 'id' => (int) $order_id ),
			array(
				'%s'
			),
			array( '%d' )
		);

		return $result;
	}

	public static function upgrade_customer( $upgrade_to, $new_expiration_date, $user_product_id, $order_id ) {
		global $wpdb;
		$result = $wpdb->update(
			$wpdb->prefix . 'tajer_user_products',
			array(
				'product_sub_id'  => $upgrade_to,
				'expiration_date' => $new_expiration_date,
				'order_id'        => $order_id
			),
			array( 'id' => $user_product_id ),
			array(
				'%d',
				'%s',
				'%d'
			),
			array( '%d' )
		);

		return $result;
	}

	public static function make_recurring( $user_product_id, $new_expiration_date, $number_of_downloads_limit, $order_id ) {
		global $wpdb;
		//get current number
		$item                        = self::get_row_by_id( 'tajer_user_products', $user_product_id );
		$current_number_of_downloads = (int) $item->number_of_downloads;

		//$diff maybe become negative number and we want that, because this will accumulates the number of downloads on renew
		$diff   = $current_number_of_downloads - $number_of_downloads_limit;
		$result = $wpdb->update(
			$wpdb->prefix . 'tajer_user_products',
			array(
				'expiration_date'     => $new_expiration_date,
				'order_id'            => $order_id,
				'number_of_downloads' => $diff
			),
			array( 'id' => $user_product_id ),
			array(
				'%s',
				'%d',
				'%d'
			),
			array( '%d' )
		);

		return $result;
	}

}
