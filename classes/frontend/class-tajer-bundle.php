<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * The advantage of this class is when the admin add new product to a bundle then
 * this class will add it to all current buyers, and if the admin remove product
 * from a bundle, this class will remove it from all current buyers.
 */
class Tajer_Bundle {

	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		add_filter( 'tajer_frontend_dashboard_populate_data', array( $this, 'tajer_fix_user_bundle' ), 10, 2 );
	}

	function tajer_fix_user_bundle( $items, $current_object ) {

		if ( apply_filters( 'tajer_disable_bundle_fixer', false, $items, $current_object ) ) {
			return $items;
		}

		$is_updated = array();

		foreach ( $items as $item ) {
			if ( tajer_is_bundle( $item->product_id ) ) {
				$is_updated[] = self::tajer_update_bundle_if_need( $item, $item->product_id, $item->product_sub_id );
			}
		}

		if ( in_array( true, $is_updated ) ) {
			$page       = ( intval( get_query_var( 'paged' ) ) ) ? intval( get_query_var( 'paged' ) ) : 1;
			$pagination = new Tajer_Pagination( $page, apply_filters( 'tajer_frontend_dashboard_total_items', 20 ), Tajer_DB::count_items( 'tajer_user_products', true ) );
			$items      = Tajer_DB::get_items_with_offset( 'tajer_user_products', $pagination->offset(), $pagination->per_page, 'buying_date', 'DESC', true );
		}

		return $items;
	}


	/**
	 * In case the admin remove or add products to a bundle this function will update the users products that bought this bundle before these changes
	 *
	 * @param $bundle
	 * @param $product_id
	 *
	 * @return bool
	 */
	static function tajer_update_bundle_if_need( $bundle, $product_id, $product_sub_id ) {
		$is_updated = false;

		$current_bundle_user_products_array = unserialize( tajer_get_user_product_meta( (int) $bundle->id, 'bundle_products' ) );

		$user_products_for_this_bundle = Tajer_DB::get_user_products_in( $current_bundle_user_products_array );


		/**
		 * Check if these user products have a product that upgraded to another product.
		 * We don't want to remove any upgraded product.
		 */
		$upgraded_user_products = Tajer_DB::get_upgraded_user_products_in( $current_bundle_user_products_array );


		/**
		 * Check if these user products have a product get renewed.
		 * We don't want to remove any renewed product.
		 */
		$recurred_user_products     = Tajer_DB::get_recurred_user_products_in( $current_bundle_user_products_array );
		$recurred_user_products_ids = array();
		if ( ( ! empty( $recurred_user_products ) ) || ( ! is_null( $recurred_user_products ) ) ) {
			foreach ( $recurred_user_products as $recurred_user_product ) {
				$recurred_user_products_ids[] = $recurred_user_product->user_product_id;
			}
		}

		list( $current_upgraded_to_products, $current_user_products_and_sub_products, $current_upgraded_user_products_ids ) = self::get_upgraded_bundle_products_handler( $upgraded_user_products );

		foreach ( $user_products_for_this_bundle as $user_product ) {
			$current_user_products_and_sub_products[ $user_product->product_id ][] = $user_product->product_sub_id;
		}


		$current_bundle_products = self::get_current_bundle_products( $product_id, $product_sub_id );

		$products_should_add = self::get_products_should_add( $current_bundle_products, $current_user_products_and_sub_products );

		$products_should_remove = self::get_products_should_remove( $current_user_products_and_sub_products, $current_bundle_products, $current_upgraded_to_products );

		$user_products_added_ids = self::insert_missing_user_product( $bundle, $products_should_add );

		$user_products_removed_ids = self::remove_old_user_products( $products_should_remove, $user_products_for_this_bundle, $current_upgraded_user_products_ids, $recurred_user_products_ids );

		if ( ! empty( $user_products_added_ids ) ) {
			$current_bundle_user_products_array = array_merge( $current_bundle_user_products_array, $user_products_added_ids );
			$is_updated                         = true;
		}

		if ( ! empty( $user_products_removed_ids ) ) {
			foreach ( $user_products_removed_ids as $upid ) {
				if ( ( $key = array_search( $upid, $current_bundle_user_products_array ) ) !== false ) {
					unset( $current_bundle_user_products_array[ $key ] );
					$is_updated = true;
				}
			}
		}

		if ( $is_updated ) {
			tajer_update_user_product_meta( (int) $bundle->id, 'bundle_products', serialize( $current_bundle_user_products_array ) );
		}

		return $is_updated;
	}


	public static function get_upgraded_bundle_products_handler( $upgraded_user_products ) {
		$current_upgraded_to_products           = array();
		$current_user_products_and_sub_products = array();
		$current_upgraded_user_products_ids     = array();
		if ( ( ! empty( $upgraded_user_products ) ) || ( ! is_null( $upgraded_user_products ) ) ) {
			foreach ( $upgraded_user_products as $user_product ) {
				//		$upgraded_user_product = 0;
//				$original_product_id = 0;
//				$original_product_sub_id = 1;
				$upgraded_products = unserialize( $user_product->meta_value );
				foreach ( $upgraded_products as $upgraded_product ) {
//					$original_product_id = $upgraded_product['product_id'];

//					if ( $original_product_sub_id > $upgraded_product['product_sub_id'] ) {
//						$original_product_sub_id = $upgraded_product['product_sub_id'];
//					}
//
//					if ( $original_product_id != $upgraded_product['product_id'] ) {
//						$original_product_id = $upgraded_product['product_id'];
//					}
					$current_upgraded_to_products[ $upgraded_product['product_id'] ][]           = (string) $upgraded_product['upgrade_to'];
					$current_user_products_and_sub_products[ $upgraded_product['product_id'] ][] = (string) $upgraded_product['product_sub_id'];
				}
				$current_upgraded_user_products_ids[] = $user_product->user_product_id;
			}

			return array(
				$current_upgraded_to_products,
				$current_user_products_and_sub_products,
				$current_upgraded_user_products_ids
			);
		}

		return array(
			$current_upgraded_to_products,
			$current_user_products_and_sub_products,
			$current_upgraded_user_products_ids
		);
	}

	public static function insert_missing_user_product( $bundle, $products_should_add ) {
		$user_products_added_ids = array();
		if ( ! empty( $products_should_add ) ) {
			foreach ( $products_should_add as $id => $psids ) {

				foreach ( $psids as $psid ) {
					//First check if the removing reason was the expiration date
					$expiration_date = tajer_expiration_date( (int) $id, (int) $psid, $bundle->buying_date );
					if ( $expiration_date < date( 'Y-m-d H:i:s' ) ) {
						continue;
					}

					$args = array(
						'buying_date'       => $bundle->buying_date,
						'order_id'          => $bundle->order_id,
						'product_id'        => $id,
						'product_sub_id'    => $psid,
						'activation_method' => $bundle->activation_method
					);

					switch ( $bundle->activation_method ) {
						case 'buy' :
						case 'free':
							$args['expiration_date'] = tajer_expiration_date( $id, $psid );
							break;
						case 'trial':
//					$period = tajer_get_trial_period( $bundle->product_id, $bundle->product_sub_id )->period;
//					$args['expiration_date'] = date( 'Y-m-d H:i:s', strtotime( "+" . $period . " days", strtotime( $bundle->buying_date ) ) );
							$args['expiration_date'] = $bundle->expiration_date;
							break;
					}

					$result = tajer_insert_user_product( $args );

					$user_products_added_ids[] = $result['id'];


				}
			}

			return $user_products_added_ids;
		}

		return $user_products_added_ids;
	}

	public static function remove_old_user_products( $products_should_remove, $user_products_for_this_bundle, $current_upgraded_user_products_ids, $recurred_user_products_ids ) {
		$user_products_removed_ids = array();
		$user_products_removed     = array();
		if ( ! empty( $products_should_remove ) ) {
			foreach ( $user_products_for_this_bundle as $user_product ) {
				if ( isset( $products_should_remove[ $user_product->product_id ] ) && is_array( $products_should_remove[ $user_product->product_id ] ) && in_array( $user_product->product_sub_id, $products_should_remove[ $user_product->product_id ] ) ) {
					if ( ( in_array( $user_product->id, $current_upgraded_user_products_ids ) ) || ( in_array( $user_product->id, $recurred_user_products_ids ) ) ) {
						continue;
					}

					$user_products_removed[] = $user_product->id;
				}
			}

			if ( ! empty( $user_products_removed ) ) {
				foreach ( $user_products_removed as $upid ) {
					$user_products_removed_ids[] = $upid;
					tajer_delete_user_product( $upid );
				}

				return $user_products_removed_ids;
			}

			return $user_products_removed_ids;
		}

		return $user_products_removed_ids;
	}

	public static function get_products_should_remove( $current_user_products_and_sub_products, $current_bundle_products, $current_upgraded_to_products ) {

		$products_should_remove = array();

		foreach ( $current_user_products_and_sub_products as $pid => $psids ) {
			foreach ( $psids as $psid ) {
				if ( ( ( ! isset( $current_bundle_products[ $pid ] ) ) || ( ! is_array( $current_bundle_products[ $pid ] ) ) || ( is_array( $current_bundle_products[ $pid ] ) && ! in_array( $psid, $current_bundle_products[ $pid ] ) ) ) ) {

					$array_count_values = array_count_values( $psids );

					if ( ! empty( $current_upgraded_to_products ) && isset( $current_upgraded_to_products[ $pid ] ) && in_array( $psid, $current_upgraded_to_products[ $pid ] ) && ( $array_count_values[ $psid ] == 1 ) ) {
						continue;
					}
					$products_should_remove[ $pid ][] = $psid;

				}
			}
		}

		return $products_should_remove;
	}

	public static function get_products_should_add( $current_bundle_products, $current_user_products_and_sub_products ) {

		$products_should_add = array();

		foreach ( $current_bundle_products as $pid => $psids ) {
			foreach ( $psids as $psid ) {
				if ( ( ! isset( $current_user_products_and_sub_products[ $pid ] ) ) || ( ! is_array( $current_user_products_and_sub_products[ $pid ] ) ) || ( is_array( $current_user_products_and_sub_products[ $pid ] ) && ! in_array( $psid, $current_user_products_and_sub_products[ $pid ] ) ) ) {
					$products_should_add[ $pid ][] = $psid;
				}
			}
		}

		return $products_should_add;
	}

	public static function get_current_bundle_products( $product_id, $product_sub_id ) {
		$current_bundle_products   = array();
		$bundle_products_meta_data = get_post_meta( $product_id, 'tajer_bundled_products', true );
		foreach ( $bundle_products_meta_data as $id => $bundled_product ) {
			if ( (int) $bundled_product['price'] != (int) $product_sub_id ) {
				continue;
			}

			if ( in_array( 'all', $bundled_product['sub_ids'] ) ) {
				$get_product_sub_ids = tajer_get_product_sub_ids( (int) $bundled_product['product'] );
			} else {
				$get_product_sub_ids = $bundled_product['sub_ids'];
			}
			foreach ( $get_product_sub_ids as $productSubId ) {

				$current_bundle_products[ (int) $bundled_product['product'] ][] = $productSubId;
			}
		}

		return $current_bundle_products;
	}
}