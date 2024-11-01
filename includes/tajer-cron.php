<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Create cron to check everyday if the expiration notification email should be sent.
 */
register_activation_hook( Tajer_DIR . 'tajer.php', 'tajer_cron_before_expiration_date' );

function tajer_cron_before_expiration_date() {
	// Make sure this event hasn't been scheduled
	if ( ! wp_next_scheduled( 'tajer_check_expiration_date' ) ) {
		// Schedule the event
		wp_schedule_event( time(), apply_filters( 'tajer_expiration_notification_email_checker_recurrence', 'daily' ), apply_filters( 'tajer_expiration_notification_email_checker_hook', 'tajer_check_expiration_date' ) );
	}
}

add_action( 'tajer_check_expiration_date', 'tajer_check_expiration_date_daily' );
function tajer_check_expiration_date_daily() {

	ignore_user_abort( true );

	if ( ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}


	//first check if there are any expired products if there are any, delete them.
	tajer_delete_expired_products();

	$is_enabled = tajer_get_option( 'enable_expiration_notification_emails', 'tajer_emails_settings', '' );
	if ( $is_enabled != 'yes' ) {
		return true;
	}

	//send expiration email notification
	$days          = tajer_get_option( 'expiration_email_period', 'tajer_emails_settings', '' );
	$required_date = apply_filters( 'tajer_expiration_email_period', date( 'Y-m-d H:i:s', strtotime( "+" . $days . " days" ) ), $days );
	$items         = Tajer_DB::get_unnotified_users_products_by_less_or_equal_expiration_date( $required_date );

	$items = apply_filters( 'tajer_expiration_email_for_item', $items, $required_date );

	if ( empty( $items ) || is_null( $items ) ) {
		return false;
	}

	$subject = tajer_get_option( 'expiration_notification_email_subject', 'tajer_emails_settings', '' );
	$message = tajer_get_option( 'expiration_notification_email_body', 'tajer_emails_settings', '' );

	foreach ( $items as $item ) {
		$item = apply_filters( 'tajer_expiration_email_for_item', $item, $items, $required_date );
		$user = get_user_by( 'id', $item->user_id );
//		$post = get_post( $item->product_id );
//		$is_multiple_prices = get_post_meta( $item->product_id, 'tajer_variable_pricing', true );
//		if ( ! tajer_is_multiple_prices( $item->product_id ) ) {
//			$title = $post->post_title;
//		} else {
		$prices = get_post_meta( $item->product_id, 'tajer_product_prices', true );
		$title  = $prices[ $item->product_sub_id ]['name'];
//		}
		$general_args = array(
			'user'            => $user,
			'expiration_date' => $item->expiration_date,
			'order_number'    => $item->order_id,
			'user_product'    => $item,
			'option_name'     => $title
		);

		$subject_args = apply_filters( 'tajer_expiration_email_subject_args', array_merge( $general_args, array(
			'content' => $subject
		) ), $item, $user, $prices, $title );

		$body_args = apply_filters( 'tajer_expiration_email_body_args', array_merge( $general_args, array(
			'content' => $message
		) ), $item, $user, $prices, $title );

		$filtered_subject = tajer_prepare_mail_body( $subject_args );
		$filtered_subject = apply_filters( 'tajer_expiration_email_filtered_subject', $filtered_subject, $item, $user, $prices, $title );

		$filtered_body = tajer_prepare_mail_body( $body_args );
		$filtered_body = apply_filters( 'tajer_expiration_email_filtered_body', $filtered_body, $item, $user, $prices, $title );

		if ( wp_mail( $user->user_email, $filtered_subject, $filtered_body ) ) {
			tajer_update_user_product_meta( $item->id, 'send_expiration_email', 'true' );
		}
	}

}

