<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
// If uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load Tajer file
include_once( 'tajer.php' );

global $wpdb;

if ( tajer_get_option( 'uninstall_on_delete', 'tajer_general_settings', '' ) == 'yes' ) {
	$tajer_taxonomies = array( 'tajer_product_category', 'tajer_product_tag' );
	$tajer_post_types = array( 'tajer_coupons', 'tajer_products' );

	/** Delete All the Custom Post Types*/
	foreach ( $tajer_post_types as $post_type ) {

		$tajer_taxonomies = array_merge( $tajer_taxonomies, get_object_taxonomies( $post_type ) );
		$items            = get_posts( array(
			'post_type'   => $post_type,
			'post_status' => 'any',
			'numberposts' => - 1,
			'fields'      => 'ids'
		) );

		if ( $items ) {
			foreach ( $items as $item ) {
				wp_delete_post( $item, true );
			}
		}
	}

	/** Delete All the Terms & Taxonomies */
	foreach ( array_unique( array_filter( $tajer_taxonomies ) ) as $taxonomy ) {

		$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

		// Delete Terms
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
			}
		}

		// Delete Taxonomies
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
	}

	/** Delete all the Plugin Options */
	delete_option( 'tajer_general_settings' );
	delete_option( 'tajer_payment_settings' );
	delete_option( 'tajer_support_settings' );
	delete_option( 'tajer_tax_settings' );
	delete_option( 'tajer_tools_settings' );
	delete_option( 'tajer_emails_settings' );

	/** Remove all database tables */
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "tajer_orders" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "tajer_shopping_carts" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "tajer_statistics" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "tajer_trials" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "tajer_user_products" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "tajer_statistic_meta" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "tajer_order_meta" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "tajer_user_product_meta" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "tajer_downloads" );

	/** Cleanup Cron Events */
	wp_clear_scheduled_hook( 'tajer_check_expiration_date' );
}
