<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class Tajer_Import_Export {

	public $file_name = 'tajer_settings_file';
	public $messages = array();

	public $product_id = 0;
	public $type = 'product';

	function __construct( $id = 0, $type = 'product' ) {
		$this->product_id = $id;
		$this->type       = $type;
	}

	function import() {
		$import_file = $_FILES[ $this->file_name ]['tmp_name'];

		if ( empty( $import_file ) ) {
			$this->messages[] = array( 'message' => __( 'Please upload a file to import.', 'tajer' ), 'status' => 0 );
		}

		if ( tajer_get_file_extension( $_FILES[ $this->file_name ]['name'] ) != 'txt' ) {
			$this->messages[] = array( 'message' => __( 'Please upload a valid .txt file', 'tajer' ), 'status' => 0 );
		}

		$messages = apply_filters( 'tajer_import_messages', $this->messages, $this->type, $this->product_id );

		if ( ! empty( $messages ) ) {
			return;
		}

		$settings = apply_filters( 'tajer_import_settings_array', unserialize( file_get_contents( $import_file ) ), $this->type, $this->product_id );

		switch ( $this->type ) {
			case'product':

				$product = apply_filters( 'tajer_product_imported', array(
					'ID'           => $this->product_id,
					'post_title'   => $settings['product_title'],
					'post_excerpt' => $settings['product_excerpt'],
					'post_content' => $settings['product_content']
				), $this->type, $this->product_id );

				wp_update_post( $product );

				update_post_meta( $this->product_id, 'tajer_product_prices', $settings['tajer_product_prices'] );
				update_post_meta( $this->product_id, 'tajer_default_multiple_price', $settings['tajer_default_multiple_price'] );
				update_post_meta( $this->product_id, 'tajer_bundle', $settings['tajer_bundle'] );
				update_post_meta( $this->product_id, 'tajer_bundled_products', $settings['tajer_bundled_products'] );
				update_post_meta( $this->product_id, 'tajer_files', $settings['tajer_files'] );
				update_post_meta( $this->product_id, 'tajer_is_recurring', $settings['tajer_is_recurring'] );
				update_post_meta( $this->product_id, 'tajer_recurring', $settings['tajer_recurring'] );
				update_post_meta( $this->product_id, 'tajer_is_trial', $settings['tajer_is_trial'] );
				update_post_meta( $this->product_id, 'tajer_trial', $settings['tajer_trial'] );
				update_post_meta( $this->product_id, 'tajer_is_upgrade', $settings['tajer_is_upgrade'] );
				update_post_meta( $this->product_id, 'tajer_upgrade', $settings['tajer_upgrade'] );
				do_action( 'tajer_import_product_meta_data', $settings, $this->type, $this->product_id );
				break;
			default:
				update_option( 'tajer_general_settings', $settings['tajer_general_settings'] );
				update_option( 'tajer_payment_settings', $settings['tajer_payment_settings'] );
				update_option( 'tajer_support_settings', $settings['tajer_support_settings'] );
				update_option( 'tajer_tax_settings', $settings['tajer_tax_settings'] );
				update_option( 'tajer_tools_settings', $settings['tajer_tools_settings'] );
				update_option( 'tajer_emails_settings', $settings['tajer_emails_settings'] );
				do_action( 'tajer_import_settings', $settings, $this->type, $this->product_id );
				break;
		}

	}

	function export() {
		$final_array = array();


		switch ( $this->type ) {

			case'product':
				$product = get_post( $this->product_id );
				$label   = $product->post_title . ' ' . date( "Y-m-d" );
				$arr     = apply_filters( 'tajer_export_meta_boxes', array(
					'tajer_product_prices',
					'tajer_default_multiple_price',
					'tajer_bundle',
					'tajer_bundled_products',
					'tajer_files',
					'tajer_is_recurring',
					'tajer_recurring',
					'tajer_is_trial',
					'tajer_trial',
					'tajer_is_upgrade',
					'tajer_upgrade'
				), $this->type, $this->product_id );

				$final_array['product_title']   = $product->post_title;
				$final_array['product_content'] = $product->post_content;
				$final_array['product_excerpt'] = $product->post_excerpt;

				foreach ( $arr as $section ) {
					$final_array[ $section ] = get_post_meta( $this->product_id, $section, true );
				}
				break;
			default:
				$label = __( 'Tajer-Settings', 'tajer' ) . ' ' . date( "Y-m-d" );
				$arr   = apply_filters( 'tajer_export_settings', array(
					'tajer_general_settings',
					'tajer_payment_settings',
					'tajer_support_settings',
					'tajer_tax_settings',
					'tajer_tools_settings',
					'tajer_emails_settings'
				), $this->type, $this->product_id );
				foreach ( $arr as $section ) {
					$final_array[ $section ] = get_option( $section );
				}
				break;
		}


		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment;filename=' . apply_filters( 'tajer_exported_file_name', $label, $this->type, $this->product_id ) . '.txt' );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		$output = fopen( 'php://output', 'w' );

		fwrite( $output, serialize( apply_filters( 'tajer_before_export_to_file', $final_array, $this->type, $this->product_id ) ) );

		fclose( $output );
		exit;

	}

	function get_errors() {

		$messages = apply_filters( 'tajer_import_export_messages', $this->messages, $this->type, $this->product_id );
		if ( ! empty( $messages ) ) {
			$errors = array();
			foreach ( $this->messages as $message ) {
				$errors[] = $message['message'];
			}

			return $errors;
		}

		return true;
	}
}
