<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Logger {

	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		// Create the log post type
		add_action( 'init', array( $this, 'register_post_type' ), 1 );

		// Create types taxonomy and default types
		add_action( 'init', array( $this, 'register_taxonomy' ), 1 );
	}

	public function register_post_type() {
		/* Logs post type */
		$log_args = array(
			'labels'              => array( 'name' => __( 'Logs', 'tajer' ) ),
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'editor' ),
			'can_export'          => true
		);

		register_post_type( 'tajer_log', $log_args );
	}

	public function register_taxonomy() {
		register_taxonomy( 'tajer_log_type', 'tajer_log', array( 'public' => false ) );
	}

	public function log_types() {
		$terms = array(
			'sale',
			'file_download',
			'gateway_error'
		);

		return apply_filters( 'tajer_log_types', $terms );
	}

	function valid_type( $type ) {
		return in_array( $type, $this->log_types() );
	}

}