<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

function tajer_show_upgrade_notices() {

	$tajer_version = get_option( 'tajer_version' );

	if ( ! $tajer_version ) {
		// 1.3 is the first version to use this option so we must add it
		$tajer_version = '1.0';
	}

//	if ( version_compare( $tajer_version, '2.1', '<' ) ) {


	$html = '<div class="updated tajer-upgrade-container"><span class="is-active"></span><p>';
	$html .= sprintf( esc_html__( 'Tajer needs to upgrade its database, click %shere%s to start the upgrade.', 'tajer' ), '<a href="#" id="upgrade-tajer">', '</a>' );
	$html .= '</p><div class="meter" style="display: none"><span></span></div><input type="hidden" name="step" value="0"><input type="hidden" name="total" value="0">';
	$html .= wp_nonce_field( 'tajer_upgrade', 'tajer_upgrade_nonce', true, false );
	$html .= '</div>';


//	printf(
//		'<div class="updated tajer-upgrade-container"><span class="is-active"></span><p>' . esc_html__( 'Tajer needs to upgrade the its database, click %shere%s to start the upgrade.', 'tajer' ) . '</p><div class="meter" style="display: none"><span></span></div><input type="hidden" name="step" value="0"><input type="hidden" name="total" value="0"></div>',
//		'<a href="#" id="upgrade-tajer">',
//		'</a>'
//	);

	echo $html;
//	}
}

//add_action( 'admin_notices', 'tajer_show_upgrade_notices' );

function tajer_enqueue_upgrade_scripts() {
	wp_enqueue_style( 'tajer-post-type-css', Tajer_URL . 'css/admin/upgrade.css' );
	wp_enqueue_script( 'tajer-upgrade-js', Tajer_URL . 'js/admin/upgrade.js', array( 'jquery' ) );


	wp_localize_script( 'tajer-upgrade-js', 'TajerUpgrade', apply_filters( 'tajer_upgrades_js_localize_script_args', array(
		'start_upgrade_process' => __( 'The upgrade process has started, please be patient. This could take several minutes. You will be noticed when the process is finished.', 'tajer' ),
		'success_upgrade'       => __( 'Tajer database upgraded successfully!', 'tajer' )
	) ) );
}

//add_action( 'admin_enqueue_scripts', 'tajer_enqueue_upgrade_scripts' );

function tajer_upgrade() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'tajer_upgrade' ) ) {
		wp_die( 'Security Check!' );
	}

	ignore_user_abort( true );

	if ( ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	$tajer_version = get_option( 'tajer_version' );

	if ( ! $tajer_version ) {
		// 1.3 is the first version to use this option so we must add it
		$tajer_version = '1.0';
	}

	if ( version_compare( $tajer_version, '2.0', '<' ) ) {
		tajer_v20_demo_upgrades();
	}
//	update_option( 'tajer_version', TAJER_VERSION );
}

//add_action( 'wp_ajax_tajer_upgrade', 'tajer_upgrade' );

function tajer_v20_demo_upgrades() {
	$step      = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 1;
	$total     = ( isset( $_POST['total'] ) && $_POST['total'] ) ? absint( $_POST['total'] ) : false;
	$per_batch = 100;
	$completed = false;

	if ( empty( $total ) || $total <= 1 ) {
//		$total    = wp_count_posts();
		$total = 3200;
	}

	//1- get $per_batch record

	//2- handle them, if there is no more record make $completed=true, otherwise process them and increase the $step, ex. $step++


	$step ++;//todo Mohammed delete this

	$percentage = ( ( $step * $per_batch ) / $total ) * 100;

	//dont depend on this but check if there is no more record
	if ( $percentage == 100 ) {//todo Mohammed delete this
		$completed = true;
	}

	if ( $completed ) {
		update_option( 'tajer_version', TAJER_VERSION );
	}

	$response = array(
		'completed'  => $completed,
		'step'       => $step,
		'total'      => $total,
		'percentage' => $percentage
	);

	tajer_response( $response );
}
//ALTER TABLE `wp_tajer_orders` CHANGE `gateway_order_id` `gateway_order_id` VARCHAR(100) NOT NULL;
//ALTER TABLE `wp_tajer_orders` CHANGE `gateway_order_id` `gateway_order_id` VARCHAR(100) NOT NULL;

//function tajer_update_version_1_if_need( $date ) {
//	global $wpdb;
//
////	$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tajer_user_products WHERE DATE (expiration_date) < %s", $date ) );
//	$items = $wpdb->get_results( $wpdb->prepare( "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = {$wpdb->prefix}tajer_orders AND COLUMN_NAME = gateway_order_id", $date ) );
//
//	return $items;
//}
//add_action('init','tajer_update_version_1_if_need');

//Worked
//register_activation_hook( __FILE__, 'tajer_1222_activation' );
//function tajer_1222_activation() {
//	global $wpdb;
//	$table_order_name = $wpdb->prefix . 'tajer_orders';
//	$wpdb->query( "ALTER TABLE {$table_order_name} CHANGE `gateway_order_id` `gateway_order_id` VARCHAR(100) NOT NULL" );
//}