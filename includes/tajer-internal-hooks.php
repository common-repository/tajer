<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

//function tajer_fix_user_bundle( $items, $this ) {
//
//	$is_updated = array();
//
//	foreach ( $items as $item ) {
//		if ( tajer_is_bundle( $item->product_id ) ) {
//			$is_updated[] = Tajer_Bundle::tajer_update_bundle_if_need( $item, $item->product_id );
//		}
//	}
//
//	if ( in_array( true, $is_updated ) ) {
//		$page       = ( intval( get_query_var( 'paged' ) ) ) ? intval( get_query_var( 'paged' ) ) : 1;
//		$pagination = new Tajer_Pagination( $page, apply_filters( 'tajer_frontend_dashboard_total_items', 20 ), Tajer_DB::count_items( 'tajer_user_products', true ) );
//		$items      = Tajer_DB::get_items_with_offset( 'tajer_user_products', $pagination->offset(), $pagination->per_page, 'buying_date', 'DESC', true );
//	}
//
//	return $items;
//}
//
//add_filter( 'tajer_frontend_dashboard_populate_data', 'tajer_fix_user_bundle', 10, 2 );