<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Products {

	private static $instance;
	private $prefix = 'tajer_';

//	private $custom_fields = array();

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		// Hook into the 'init' action
		add_action( 'init', array( $this, 'tajer_products' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_custom_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_custom_meta' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
//		add_action( 'wp_ajax_tajer_upload_files', array( $this, 'tajer_upload_files' ) );
//		add_action( 'wp_ajax_tajer_show_files', array( $this, 'show_tajer_files' ) );
//		add_action( 'wp_ajax_tajer_get_files_area', array( $this, 'get_files_area' ) );
//		add_action( 'wp_ajax_tajer_delete_file', array( $this, 'delete_file' ) );
		add_action( 'admin_init', array( $this, 'change_files_upload_dir' ), 9999 );
//		add_action( 'wp_ajax_tajer_delete_file_assignment', array( $this, 'delete_file_assignment' ) );
//		add_action( 'wp_ajax_tajer_select_file', array( $this, 'tajer_select_file' ) );
		add_action( 'wp_ajax_tajer_get_product_sub_ids', array( $this, 'get_product_sub_ids' ) );
//		$this->custom_fields = $this->fields();

	}

	function get_product_sub_ids() {
		if ( ! ( isset( $_REQUEST['tajerBundleNonce'] ) && wp_verify_nonce( $_REQUEST['tajerBundleNonce'], 'tajer_bundle_nonce' ) ) ) {
			wp_die( 'Security Check!' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_admin_products_get_product_sub_ids_capability', 'manage_options' ) ) ) {
			wp_die( 'Security Check!' );
		}

		$product_id = $_REQUEST['productId'];

		$product_sub_ids        = tajer_get_product_sub_ids_with_names( (int) $product_id );
		$product_sub_ids['all'] = __( "All", 'tajer' );

		$html = '';
		foreach ( $product_sub_ids as $id => $name ) {
			$html .= '<option value="' . $id . '">' . $name . '</option>';
		}


		$response = array(
			'subIds' => $html
		);

		$response = apply_filters( 'get_product_sub_ids_in_products_post_type', $response );

		tajer_response( $response );
	}

	function change_files_upload_dir() {
		global $pagenow;

		if ( ! empty( $_REQUEST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
			if ( 'tajer_products' == get_post_type( $_REQUEST['post_id'] ) ) {
				tajer_create_protection_files( true );
				add_filter( 'upload_dir', 'tajer_custom_upload_dir' );
			}
		}
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @global string $pagenow
	 * @return void
	 */
	function enqueue_scripts() {
		global $pagenow, $post;

		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		if ( ! in_array( $post->post_type, array( 'tajer_products', 'tajer_coupons' ) ) ) {
			return;
		}
		wp_enqueue_style( 'tajer-font-awesome', Tajer_URL . 'lib/font-awesome/css/font-awesome.min.css' );
		wp_enqueue_style( 'tajer-frontend-product-css', Tajer_URL . 'css/frontend/product.css', array( 'tajer-font-awesome' ) );
//		wp_enqueue_style( 'tajer-dump-uploader-css', Tajer_URL . 'lib/uploader-master/demos/css/simple-demo.css' );
		wp_enqueue_style( 'chosen-jquery-css', Tajer_URL . 'lib/chosen_v1.2.0/chosen.min.css' );
		wp_enqueue_style( 'tajer-jquery-ui-css', Tajer_URL . 'lib/jquery-ui/jquery-ui.min.css' );
		wp_enqueue_style( 'tajer-post-type-css', Tajer_URL . 'css/admin/tajer-products.css', array(
			'chosen-jquery-css'
		) );

		wp_enqueue_media();

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
//		wp_enqueue_script( 'tajer-jquery-ui-widget', Tajer_URL . 'lib/jQuery-File-Upload-master/js/vendor/jquery.ui.widget.js', array( 'jquery' ) );
//		wp_enqueue_script( 'tajer-iframe-transport', Tajer_URL . 'lib/jQuery-File-Upload-master/js/jquery.iframe-transport.js', array( 'tajer-jquery-ui-widget' ) );
//		wp_enqueue_script( 'tajer-fileupload', Tajer_URL . 'lib/jQuery-File-Upload-master/js/jquery.fileupload.js', array( 'tajer-iframe-transport' ) );
		wp_enqueue_script( 'chosen-jquery-js', Tajer_URL . 'lib/chosen_v1.2.0/chosen.jquery.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'tajer-post-type-js', Tajer_URL . 'js/admin/tajer-products.js', array(
			'chosen-jquery-js',
			'jquery-ui-sortable',
			'jquery-ui-dialog'
		) );

	}

//	function tajer_select_file() {
//		//security check
//		if ( ( ! ( isset( $_REQUEST['select_nonce_field'] ) ) ) && ( ! ( wp_verify_nonce( $_REQUEST['select_nonce_field'], 'select_nonce' ) ) ) ) {
//			wp_die( 'Security Check' );
//		}
//
//		$pid  = intval( $_REQUEST['post_ID'] );
//		$file = sanitize_text_field( $_REQUEST['file'] );
//
//
//		$upload_dir = wp_upload_dir();
//		$file_url   = $upload_dir['baseurl'] . '/tajer/' . basename( $file );
//
//		$files     = get_post_meta( $pid, 'tajer_files', true );
//		$file_data = array();
//
//		$file_data['name']   = basename( $file );
//		$file_data['url']    = $file_url;
//		$file_data['path']   = $file;
//		$file_data['prices'] = array();
//		$files[]             = apply_filters( 'tajer_admin_products_select_file', $file_data, $files );
//
//
//		update_post_meta( $pid, 'tajer_files', $files );
//
//		$response = array(
//			'name' => basename( $file )
//		);
//		tajer_response( $response );
//	}

//	function fields() {
//		// Field Array
//		$prefix        = $this->prefix;
//		$custom_fields = array(
//			array(
//				'label'    => __( 'Download Link Expiration', 'tajer' ),
//				'desc'     => __( 'In days, leave it empty for lifetime option.', 'tajer' ),
//				'id'       => $prefix . 'download_link_expiration',
//				'meta_key' => $prefix . 'download_link_expiration',
//				'type'     => 'text'
//			),
//			array(
//				'label'    => __( 'File Download Limit', 'tajer' ),
//				'desc'     => __( 'The maximum number of times files can be downloaded, leave it empty for unlimited number of times.', 'tajer' ),
//				'id'       => $prefix . 'file_download_limit',
//				'meta_key' => $prefix . 'file_download_limit',
//				'type'     => 'text'
//			),
//			array(
//				'label'    => __( 'Capabilities: ', 'tajer' ),
//				'desc'     => '',
//				'id'       => $prefix . 'capabilities',
//				'meta_key' => $prefix . 'capabilities',
//				'type'     => 'radio',
//				'options'  => array( __( 'Free', 'tajer' ) => 'free', __( 'Sale', 'tajer' ) => 'sale' ),
//				'default'  => 'sale'
//			),
//			array(
//				'label'    => __( 'Roles: ', 'tajer' ),
//				'desc'     => '',
//				'id'       => $prefix . 'roles[]',
//				'meta_key' => $prefix . 'roles',
//				'type'     => 'select',
//				'options'  => tajer_get_user_roles()
//			)
//		);
//
//		return $custom_fields;
//	}

	// Register Custom Post Type
	function tajer_products() {
		$capability = apply_filters( 'tajer_admin_products_capability', 'manage_options' );
		$labels     = apply_filters( 'tajer_products_post_type_labels', array(
			'name'               => _x( 'Products', 'Post Type General Name', 'tajer' ),
			'singular_name'      => _x( 'Product', 'Post Type Singular Name', 'tajer' ),
			'menu_name'          => __( 'Products', 'tajer' ),
			'parent_item_colon'  => __( 'Parent Product:', 'tajer' ),
			'all_items'          => __( 'All Products', 'tajer' ),
			'view_item'          => __( 'View Product', 'tajer' ),
			'add_new_item'       => __( 'Add New Product', 'tajer' ),
			'add_new'            => __( 'Add New', 'tajer' ),
			'edit_item'          => __( 'Edit Product', 'tajer' ),
			'update_item'        => __( 'Update Product', 'tajer' ),
			'search_items'       => __( 'Search Product', 'tajer' ),
			'not_found'          => __( 'Not found', 'tajer' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'tajer' ),
		) );
		$args       = apply_filters( 'tajer_product_post_type_args', array(
			'labels'             => $labels,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ),
//			'taxonomies'         => array( 'category', 'post_tag' ),
			'hierarchical'       => false,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,//or string
			'menu_icon'          => 'dashicons-cart',
			'rewrite'            => array( 'slug' => 'product' ),
			'has_archive'        => true,
			'can_export'         => true,
			'capability_type'    => 'post',
			'capabilities'       => array(
				'publish_posts'       => $capability,
				'edit_posts'          => $capability,
				'edit_post'          => $capability,
				'edit_others_posts'   => $capability,
				'delete_posts'        => $capability,
				'delete_others_posts' => $capability,
				'read_private_posts'  => $capability,
				'delete_post'         => $capability,
				'read_post'           => $capability,
			)
		) );
		register_post_type( apply_filters( 'tajer_product_post_type_name', 'tajer_products', $args ), $args );
	}

	// Add the Meta Box
	function add_custom_meta_box() {
//		add_meta_box(
//			'tajer', // $id
//			__( 'Product Settings', 'tajer' ), // $title
//			array( $this, 'show_price_options' ), // $callback
//			'tajer_products', // $page
//			'normal', // $context
//			'high' ); // $priority

		add_meta_box(
			'tajer_prices', // $id
			__( 'Product Prices', 'tajer' ), // $title
			array( $this, 'show_tajer_prices' ), // $callback
			'tajer_products', // $page
			'normal', // $context
			'high' ); // $priority

		add_meta_box(
			'tajer_files', // $id
			__( 'Files & Bundle Settings', 'tajer' ), // $title
			array( $this, 'show_tajer_files' ), // $callback
			'tajer_products', // $page
			'normal', // $context
			'high' ); // $priority

		add_meta_box(
			'tajer_product_stats', // $id
			__( 'Product Stats', 'tajer' ), // $title
			array( $this, 'show_tajer_product_status' ), // $callback
			'tajer_products', // $page
			'side', // $context
			'high' ); // $priority

		add_meta_box(
			'tajer_recurring', // $id
			__( 'Recurring Settings', 'tajer' ), // $title
			array( $this, 'show_tajer_recurring' ), // $callback
			'tajer_products', // $page
			'side', // $context
			'high' ); // $priority
		add_meta_box(
			'tajer_upgrade', // $id
			__( 'Upgrade Settings', 'tajer' ), // $title
			array( $this, 'show_tajer_upgrade' ), // $callback
			'tajer_products', // $page
			'side', // $context
			'high' ); // $priority
		add_meta_box(
			'tajer_trial', // $id
			__( 'Trial Settings', 'tajer' ), // $title
			array( $this, 'show_tajer_trial' ), // $callback
			'tajer_products', // $page
			'side', // $context
			'high' ); // $priority
	}

	function show_tajer_trial() {
		global $post;
		$is_trial = get_post_meta( $post->ID, 'tajer_is_trial', true );
		$trial    = get_post_meta( $post->ID, 'tajer_trial', true );

		$checked = '';
		if ( ( ! $is_trial ) || empty( $is_trial ) ) {
			$checked = 'checked';
		}

		$prices    = get_post_meta( $post->ID, 'tajer_product_prices', true );
		$pricesIds = array();
		if ( is_array( $prices ) ) {
			foreach ( $prices as $price_id => $price_detail ) {
				$pricesIds[] = $price_id;
			}
		}

		$html = '';
		$html .= '<label for="is_trial_yes">';
		$html .= '<input type="radio" ' . checked( 1, $is_trial, false ) . ' name="tajer_is_trial" id="is_trial_yes" value="1"> ' . __( 'Enable', 'tajer' ) . ' &nbsp;</label>';
		$html .= '<label for="is_trial_no">';
		$html .= '<input type="radio" ' . checked( 0, $is_trial, false ) . $checked . ' name="tajer_is_trial" id="is_trial_no" value="0"> ' . __( 'Disable', 'tajer' ) . '</label>';

		$html .= '<div class="tajer_enable_trial">';
		$html .= '<table id="trial_table">';
		if ( $trial && ( ! empty( $trial ) ) ) {
//		if ( 5==9 ) {
			foreach ( $trial as $id => $detail ) {

				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td>' . __( 'Price Assignment', 'tajer' ) . '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td>';
				$html .= '<select name="tajer_trial[' . $id . '][prices][]" class="tajer_multiple_files_prices" multiple>';
				if ( ( is_array( $pricesIds ) ) && ( ! empty( $pricesIds ) ) ) {
					foreach ( $pricesIds as $price_id ) {
						$selected = false;
						if ( isset( $detail['prices'] ) && is_array( $detail['prices'] ) && in_array( $price_id, $detail['prices'] ) ) {
							$selected = true;
						}
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . $price_id . '">' . $price_id . '</option>';
					}
				}
				$html .= '</select>';
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<td>' . __( 'Trial Period(In days)', 'tajer' ) . '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td><input type="text" name="tajer_trial[' . $id . '][trial_period]" class="tajer_trial_trial_period" value="' . $detail['trial_period'] . '" /></td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td>' . __( 'Direct Trial', 'tajer' ) . '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td>';
				$html .= '<select name="tajer_trial[' . $id . '][direct_trial]" class="">';
				$html .= '<option ' . selected( $detail['direct_trial'], 'yes', false ) . ' value="yes">' . __( 'Yes', 'tajer' ) . '</option>';
				$html .= '<option ' . selected( $detail['direct_trial'], 'no', false ) . ' value="no">' . __( 'No', 'tajer' ) . '</option>';
				$html .= '</select>';
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '"><td>-------------------------------------</td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr data-index="1">';
			$html .= '<td>' . __( 'Price Assignment', 'tajer' ) . '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td>';
			$html .= '<select name="tajer_trial[1][prices][]" class="tajer_multiple_files_prices" multiple>';
//			if ( ( is_array( $pricesIds ) ) && ( ! empty( $pricesIds ) ) ) {
//				foreach ( $pricesIds as $price_id ) {
//					$html .= '<option value="' . $price_id . '">' . $price_id . '</option>';
//				}
//			}
			$html .= '<option value="1">1</option>';
			$html .= '</select>';
			$html .= '</td>';
			$html .= '</tr>';
			$html .= '<td>' . __( 'Trial Period(In days)', 'tajer' ) . '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td><input type="text" name="tajer_trial[1][trial_period]" class="tajer_trial_trial_period" value="" /></td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td>' . __( 'Direct Trial', 'tajer' ) . '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td>';
			$html .= '<select name="tajer_trial[1][direct_trial]" class="">';
			$html .= '<option selected value="yes">' . __( 'Yes', 'tajer' ) . '</option>';
			$html .= '<option value="no">' . __( 'No', 'tajer' ) . '</option>';
			$html .= '</select>';
			$html .= '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1"><td>-------------------------------------</td>';
			$html .= '</tr>';
		}
		$html .= '</table>';
		$html .= '<br>';
//		$html .= '<button class="tajer-add-trial button-secondary" >' . __( 'Add Trial', 'tajer' ) . '</button>';
//		$html .= '<button class="tajer-remove-trial button-secondary" >' . __( 'Remove Trial', 'tajer' ) . '</button>';
		$html .= '<a class="tajer-add-trial button-secondary">' . __( 'Add Trial', 'tajer' ) . '</a>';
		$html .= '<a class="tajer-remove-trial button-secondary">' . __( 'Remove Trial', 'tajer' ) . '</a>';
		$html .= '</div>';

		echo apply_filters( 'tajer_trial_meta_box_html', $html, $is_trial, $trial, $prices );
	}

	function show_tajer_upgrade() {
		global $post;
		$is_upgrade = get_post_meta( $post->ID, 'tajer_is_upgrade', true );
		$upgrade    = get_post_meta( $post->ID, 'tajer_upgrade', true );

		$checked = '';
		if ( ( ! $is_upgrade ) || empty( $is_upgrade ) ) {
			$checked = 'checked';
		}

		$prices    = get_post_meta( $post->ID, 'tajer_product_prices', true );
		$pricesIds = array();
		if ( is_array( $prices ) ) {
			foreach ( $prices as $price_id => $price_detail ) {
				$pricesIds[] = $price_id;
			}
		}

		$html = '';
		$html .= '<label for="is_upgrade_yes">';
		$html .= '<input type="radio" ' . checked( 1, $is_upgrade, false ) . ' name="tajer_is_upgrade" id="is_upgrade_yes" value="1"> ' . __( 'Enable', 'tajer' ) . ' &nbsp;</label>';
		$html .= '<label for="is_upgrade_no">';
		$html .= '<input type="radio" ' . checked( 0, $is_upgrade, false ) . $checked . ' name="tajer_is_upgrade" id="is_upgrade_no" value="0"> ' . __( 'Disable', 'tajer' ) . '</label>';

		$html .= '<div class="tajer_enable_upgrade">';
		$html .= '<table id="upgrade_table">';
		if ( $upgrade && ( ! empty( $upgrade ) ) ) {
			foreach ( $upgrade as $id => $detail ) {

				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td>' . __( 'Price Assignment', 'tajer' ) . '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td>';
				$html .= '<select name="tajer_upgrade[' . $id . '][prices][]" class="tajer_multiple_files_prices" multiple>';
				if ( ( is_array( $pricesIds ) ) && ( ! empty( $pricesIds ) ) ) {
					foreach ( $pricesIds as $price_id ) {
						$selected = false;
						if ( isset( $detail['prices'] ) && is_array( $detail['prices'] ) && in_array( $price_id, $detail['prices'] ) ) {
							$selected = true;
						}
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . $price_id . '">' . $price_id . '</option>';
					}
				}
				$html .= '</select>';
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr>';
				$html .= '<td>' . __( 'Upgrade Fee', 'tajer' ) . '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td><input type="text" name="tajer_upgrade[' . $id . '][upgrade_fee]" class="tajer_upgrade_recurrence_n" value="' . $detail['upgrade_fee'] . '" /></td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td>' . __( 'Upgrade TO', 'tajer' ) . '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td>';
				$html .= '<select name="tajer_upgrade[' . $id . '][upgrade_to]" class="tajer_multiple_files_prices">';
				if ( ( is_array( $pricesIds ) ) && ( ! empty( $pricesIds ) ) ) {
					foreach ( $pricesIds as $price_id ) {
						$selected = false;
						if ( $price_id == $detail['upgrade_to'] ) {
							$selected = true;
						}
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . $price_id . '">' . $price_id . '</option>';
					}
				}
				$html .= '</select>';
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '"><td>-------------------------------------</td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr data-index="1">';
			$html .= '<td>' . __( 'Price Assignment', 'tajer' ) . '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td>';
			$html .= '<select name="tajer_upgrade[1][prices][]" class="tajer_multiple_files_prices" multiple>';
//			if ( ( is_array( $pricesIds ) ) && ( ! empty( $pricesIds ) ) ) {
//				foreach ( $pricesIds as $price_id ) {
//					$html .= '<option value="' . $price_id . '">' . $price_id . '</option>';
//				}
//			}
			$html .= '<option value="1">1</option>';
			$html .= '</select>';
			$html .= '</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td>' . __( 'Upgrade Fee', 'tajer' ) . '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td><input type="text" name="tajer_upgrade[1][upgrade_fee]" class="tajer_upgrade_recurrence_n" value="" /></td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td>' . __( 'Upgrade TO', 'tajer' ) . '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td>';
			$html .= '<select name="tajer_upgrade[1][upgrade_to]" class="tajer_multiple_files_prices">';
//			if ( ( is_array( $pricesIds ) ) && ( ! empty( $pricesIds ) ) ) {
//				foreach ( $pricesIds as $price_id ) {
//					$html .= '<option value="' . $price_id . '">' . $price_id . '</option>';
//				}
//			}
			$html .= '<option value="1">1</option>';
			$html .= '</select>';
			$html .= '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1"><td>-------------------------------------</td>';
			$html .= '</tr>';
		}
		$html .= '</table>';
		$html .= '<br>';
//		$html .= '<button class="tajer-add-upgrade button-secondary" >' . __( 'Add Upgrade', 'tajer' ) . '</button>';
		$html .= '<a class="tajer-add-upgrade button-secondary">' . __( 'Add Upgrade', 'tajer' ) . '</a>';
		$html .= '<a class="tajer-remove-upgrade button-secondary">' . __( 'Remove Upgrade', 'tajer' ) . '</a>';
//		$html .= '<button class="tajer-remove-upgrade button-secondary" >' . __( 'Remove Upgrade', 'tajer' ) . '</button>';
		$html .= '</div>';

		echo apply_filters( 'tajer_upgrade_meta_box_html', $html, $is_upgrade, $upgrade, $prices );;
	}

	function show_tajer_recurring() {
		global $post;
		$is_recurring = get_post_meta( $post->ID, 'tajer_is_recurring', true );
		$recurring    = get_post_meta( $post->ID, 'tajer_recurring', true );

		$checked = '';
		if ( ( ! $is_recurring ) || empty( $is_recurring ) ) {
			$checked = 'checked';
		}

		$prices    = get_post_meta( $post->ID, 'tajer_product_prices', true );
		$pricesIds = array();
		if ( is_array( $prices ) ) {
			foreach ( $prices as $price_id => $price_detail ) {
				$pricesIds[] = $price_id;
			}
		}

		$html = '';
		$html .= '<label for="is_recurring_yes">';
		$html .= '<input type="radio" ' . checked( 1, $is_recurring, false ) . ' name="tajer_is_recurring" id="is_recurring_yes" value="1"> ' . __( 'Enable', 'tajer' ) . ' &nbsp;</label>';
		$html .= '<label for="is_recurring_no">';
		$html .= '<input type="radio" ' . checked( 0, $is_recurring, false ) . $checked . ' name="tajer_is_recurring" id="is_recurring_no" value="0"> ' . __( 'Disable', 'tajer' ) . '</label>';

		$html .= '<div class="tajer_enable_recurring">';
		$html .= '<table id="recurring_table">';
		if ( $recurring && ( ! empty( $recurring ) ) ) {
//		if ( 5==9 ) {
			foreach ( $recurring as $id => $detail ) {

				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td>' . __( 'Price Assignment', 'tajer' ) . '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td>';
				$html .= '<select name="tajer_recurring[' . $id . '][prices][]" class="tajer_multiple_files_prices" multiple>';
				if ( ( is_array( $pricesIds ) ) && ( ! empty( $pricesIds ) ) ) {
					foreach ( $pricesIds as $price_id ) {
						$selected = false;
						if ( isset( $detail['prices'] ) && is_array( $detail['prices'] ) && in_array( $price_id, $detail['prices'] ) ) {
							$selected = true;
						}
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . $price_id . '">' . $price_id . '</option>';
					}
				}
				$html .= '</select>';
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<td>' . __( 'Recurring Fee', 'tajer' ) . '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td><input type="text" name="tajer_recurring[' . $id . '][recurring_fee]" class="tajer_recurring_recurrence_n" value="' . $detail['recurring_fee'] . '" /></td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td>' . __( 'Bill Every', 'tajer' ) . '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td><input type="text" name="tajer_recurring[' . $id . '][recurrence_n]" class="tajer_recurring_recurrence_n" value="' . $detail['recurrence_n'] . '"/></td>';
				$html .= '<td>';
				$html .= '<select name="tajer_recurring[' . $id . '][recurrence_w]" class="tajer_recurring_recurrence_w" id="tajer_recurring[recurrence_w]">';
				$html .= '<option ' . selected( $detail['recurrence_w'], 'Week', false ) . ' value="Week">' . __( 'Week(s)', 'tajer' ) . '</option>';
				$html .= '<option ' . selected( $detail['recurrence_w'], 'Month', false ) . ' value="Month">' . __( 'Month(s)', 'tajer' ) . '</option>';
				$html .= '<option ' . selected( $detail['recurrence_w'], 'Year', false ) . ' value="Year">' . __( 'Year', 'tajer' ) . '</option>';
				$html .= '</select>';
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td>' . __( 'Continue Billing For', 'tajer' ) . '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '">';
				$html .= '<td><input type="text" name="tajer_recurring[' . $id . '][duration_n]" class="tajer_recurring_recurrence_n" value="' . $detail['duration_n'] . '"/></td>';
				$html .= '<td>';
				$html .= '<select name="tajer_recurring[' . $id . '][duration_w]" class="tajer_recurring_recurrence_w" id="tajer_recurring[duration_w]">';
				$html .= '<option ' . selected( $detail['duration_w'], 'Week', false ) . ' value="Week">' . __( 'Week(s)', 'tajer' ) . '</option>';
				$html .= '<option ' . selected( $detail['duration_w'], 'Month', false ) . ' value="Month">' . __( 'Month(s)', 'tajer' ) . '</option>';
				$html .= '<option ' . selected( $detail['duration_w'], 'Year', false ) . ' value="Year">' . __( 'Year(s)', 'tajer' ) . '</option>';
				$html .= '<option ' . selected( $detail['duration_w'], 'Forever', false ) . ' value="Forever">' . __( 'Forever', 'tajer' ) . '</option>';
				$html .= '</select>';
				$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr data-index="' . $id . '"><td>-------------------------------------</td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr data-index="1">';
			$html .= '<td>' . __( 'Price Assignment', 'tajer' ) . '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td>';
			$html .= '<select name="tajer_recurring[1][prices][]" class="tajer_multiple_files_prices" multiple>';
//			if ( ( is_array( $pricesIds ) ) && ( ! empty( $pricesIds ) ) ) {
//				foreach ( $pricesIds as $price_id ) {
//					$html .= '<option value="' . $price_id . '">' . $price_id . '</option>';
//				}
//			}
			$html .= '<option value="1">1</option>';
			$html .= '</select>';
			$html .= '</td>';
			$html .= '</tr>';
			$html .= '<td>' . __( 'Recurring Fee', 'tajer' ) . '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td><input type="text" name="tajer_recurring[1][recurring_fee]" class="tajer_recurring_recurrence_n" value="" /></td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td>' . __( 'Bill Every', 'tajer' ) . '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td><input type="text" name="tajer_recurring[1][recurrence_n]" class="tajer_recurring_recurrence_n" value=""/></td>';
			$html .= '<td>';
			$html .= '<select name="tajer_recurring[1][recurrence_w]" class="tajer_recurring_recurrence_w" id="tajer_recurring[recurrence_w]">';
			$html .= '<option value="Week">' . __( 'Week(s)', 'tajer' ) . '</option>';
			$html .= '<option value="Month">' . __( 'Month(s)', 'tajer' ) . '</option>';
			$html .= '<option value="Year">' . __( 'Year', 'tajer' ) . '</option>';
			$html .= '</select>';
			$html .= '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td>' . __( 'Continue Billing For', 'tajer' ) . '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1">';
			$html .= '<td><input type="text" name="tajer_recurring[1][duration_n]" class="tajer_recurring_recurrence_n" value=""/></td>';
			$html .= '<td>';
			$html .= '<select name="tajer_recurring[1][duration_w]" class="tajer_recurring_recurrence_w" id="tajer_recurring[duration_w]">';
			$html .= '<option value="Week">' . __( 'Week(s)', 'tajer' ) . '</option>';
			$html .= '<option value="Month">' . __( 'Month(s)', 'tajer' ) . '</option>';
			$html .= '<option value="Year">' . __( 'Year(s)', 'tajer' ) . '</option>';
			$html .= '<option value="Forever">' . __( 'Forever', 'tajer' ) . '</option>';
			$html .= '</select>';
			$html .= '</td>';
			$html .= '</tr>';
			$html .= '<tr data-index="1"><td>-------------------------------------</td>';
			$html .= '</tr>';
		}
		$html .= '</table>';
		$html .= '<br>';
//		$html .= '<button class="tajer-add-recurring button-secondary" >' . __( 'Add Recurring', 'tajer' ) . '</button>';
		$html .= '<a class="tajer-add-recurring button-secondary">' . __( 'Add Recurring', 'tajer' ) . '</a>';
		$html .= '<a class="tajer-remove-recurring button-secondary">' . __( 'Remove Recurring', 'tajer' ) . '</a>';
//		$html .= '<button class="tajer-remove-recurring button-secondary" >' . __( 'Remove Recurring', 'tajer' ) . '</button>';
		$html .= '</div>';

		echo apply_filters( 'tajer_recurring_meta_box_html', $html, $is_recurring, $recurring, $prices );
	}

	function show_tajer_product_status( $post ) {
		$sales     = Tajer_DB::count_product_sales( $post->ID );
		$earnings  = Tajer_DB::get_product_earnings( $post->ID );
		$downloads = Tajer_DB::count_product_downloads( $post->ID );
		?>
		<div class="inside">
			<p>
				<strong class="label"><?php _e( 'Sales:', 'tajer' ); ?></strong>
				<span><?php echo $sales; ?></span>
			</p>

			<p>
				<strong class="label"><?php _e( 'Earnings:', 'tajer' ); ?></strong>
				<span><?php echo $earnings == null ? tajer_number_to_currency( 0, true ) : tajer_number_to_currency( $earnings, true ); ?></span>
			</p>

			<p>
				<strong class="label"><?php _e( 'Downloads:', 'tajer' ); ?></strong>
				<span><?php echo $downloads; ?></span>
			</p>
			<hr>
		</div>
		<?php
		do_action( 'tajer_admin_product_status_meta_box', $sales, $earnings );
	}

	function show_tajer_prices() {
		global $post;
//		$is_multiple_prices  = get_post_meta( $post->ID, 'tajer_variable_pricing', true );
		$prices        = get_post_meta( $post->ID, 'tajer_product_prices', true );
		$default_price = get_post_meta( $post->ID, 'tajer_default_multiple_price', true );
//		$singe_price         = get_post_meta( $post->ID, 'tajer_price', true );
//		$singe_price_options = get_post_meta( $post->ID, 'tajer_price_options', true );

		$meta = '';

		$html = '';
//		$html .= '<p><label style="width:100%" for="tajer_variable_pricing">';
//		$html .= '<input type="checkbox" ' . checked( 1, $is_multiple_prices, false ) . ' name="tajer_variable_pricing" id="tajer_variable_pricing" value="1">';
//		$html .= __( 'Enable variable pricing', 'tajer' ) . '</label></p>';
//		$html .= '<div class="tajer_enable_single_price">';
//		$html .= '<label for="tajer_single_price">$</label>';
//		$html .= '<input type="text" name="tajer_single_price" id="tajer_single_price" value="' . $singe_price . '" />';
//		$html .= '<a href="#" class="tajer_price_options">' . __( 'Options', 'tajer' ) . '</a>';
//		$html .= $this->show_price_options( 'tajer_price_options', $singe_price_options );
//		$html .= '</div>';
//		$html .= '<br>';

		$html .= '<div class="tajer_enable_multiple_price">';
		$html .= '<table id="multiple_price_table">';
		$html .= '<input type="hidden" name="single_price_options_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '"/>';

		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th></th>';
		$html .= '<th>' . __( 'Option Name', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'Price', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'Options', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'Default', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'ID', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'Delete', 'tajer' ) . '</th>';
		$html .= '</thead>';
		$html .= '<tbody>';
		if ( $prices && ( ! empty( $prices ) ) ) {
			foreach ( $prices as $id => $detail ) {
				$html .= '<tr class="tajer_repeatable_row" data-index="' . $id . '">';
				$html .= '<td><span class="tajer_draghandle"></span></td>';
				$html .= '<td><input type="text" name="tajer_product_prices[' . $id . '][name]" value="' . $detail['name'] . '" /></td>';
				$html .= '<td><input type="text" name="tajer_product_prices[' . $id . '][price]" value="' . $detail['price'] . '" /></td>';
				$html .= '<td><a href="#" class="tajer_product_prices_options">' . __( 'Options', 'tajer' ) . '</a></td>';
				$html .= '<td><input type="radio" ' . checked( $id, $default_price, false ) . ' name="tajer_default_multiple_price" value="' . $id . '"></td>';
				$html .= '<td><span class="tajer_price_id">' . $id . '</span></td>';
				$html .= '<td><a href="#" class="tajer_remove_repeatable" style="background: url(' . Tajer_URL . 'images/xit.gif) no-repeat;">×</a></td>';
				$html .= $this->show_price_options( 'tajer_product_prices[' . $id . ']', $detail );
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr class="tajer_repeatable_row" data-index="1">';
			$html .= '<td><span class="tajer_draghandle"></span></td>';
			$html .= '<td><input type="text" name="tajer_product_prices[1][name]" value="' . $meta . '" /></td>';
			$html .= '<td><input type="text" name="tajer_product_prices[1][price]" value="' . $meta . '" /></td>';
			$html .= '<td><a href="#" class="tajer_product_prices_options">' . __( 'Options', 'tajer' ) . '</a></td>';
			$html .= '<td><input type="radio" ' . checked( '1', '1', false ) . ' name="tajer_default_multiple_price" value="1"></td>';
			$html .= '<td><span class="tajer_price_id">1</span></td>';
			$html .= '<td><a href="#" class="tajer_remove_repeatable" style="background: url(' . Tajer_URL . 'images/xit.gif) no-repeat;">×</a></td>';
			$html .= $this->show_price_options( 'tajer_product_prices[1]', $meta );
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '<br>';
//		$html .= '<button class="tajer-add-price button-secondary" >' . __( 'Add New Price', 'tajer' ) . '</button>';
		$html .= '<a class="tajer-add-price button-secondary">' . __( 'Add New Price', 'tajer' ) . '</a>';
		$html .= '</div>';
		$html .= $this->get_price_options_dialog();

		echo apply_filters( 'tajer_prices_meta_box_html', $html, $default_price, $prices );
	}

	function get_price_options_dialog() {
		ob_start();
		tajer_get_template_part( 'admin-price-options-dialog' );
		$html = ob_get_clean();

		return apply_filters( 'tajer_admin_products_price_options_dialog_html', $html );
	}

	function show_tajer_files( $post = 0, $args = array(), $echo = true, $postId = 0 ) {

		if ( $postId ) {
			$post_id = $postId;
		} else {
			$post_id = $post->ID;
		}


		$is_bundle          = get_post_meta( $post_id, 'tajer_bundle', true );
		$files              = get_post_meta( $post_id, 'tajer_files', true );
		$bundled_products   = get_post_meta( $post_id, 'tajer_bundled_products', true );
		$args               = array( 'post_type' => 'tajer_products', 'numberposts' => - 1 );
		$products_post_type = get_posts( $args );
		$prices             = get_post_meta( $post_id, 'tajer_product_prices', true );

		$pricesIds = array();
		if ( ( is_array( $prices ) ) ) {
			foreach ( $prices as $price_id => $price_detail ) {
				$pricesIds[] = $price_id;
			}
		}
		$pricesIds[] = 'All';


		$html = '';
		$html .= '<div id="tajer-files-area">';
		$html .= '<p><label style="width:100%" for="tajer_bundle">';
		$html .= '<input type="checkbox" ' . checked( 1, $is_bundle, false ) . ' name="tajer_bundle" id="tajer_bundle" value="1">';
		$html .= __( 'Bundle?', 'tajer' ) . '</label> <i style="display: none;" class="fa fa-refresh fa-spin tajer-bundle-loader"></i></p>';
		$html = $this->bundle_render( $html, $products_post_type, $bundled_products, $pricesIds );
		$html .= '<br>';

		$html .= '<div class="tajer_enable_multiple_files">';
		$html .= '<table id="multiple_files_table">';
		$html .= '<tr>';
		$html .= '<th>' . __( 'File Name', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'File', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'Price Assignment', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'Delete', 'tajer' ) . '</th>';
		$html .= '</tr>';
		if ( $files && ( ! empty( $files ) ) ) {
			$html = $this->files_renderer( $files, $html, $post_id, $pricesIds );
		} else {
			$html .= '<tr class="tajer_repeatable_row" data-index="1">';
			$html .= '<td>';
			$html .= '<input type="text" class="tajer-file-name" name="tajer_file[1][name]" value="" placeholder="' . esc_attr__( 'File Name' ) . '" /><input type="hidden" class="tajer-attachment-id" name="tajer_file[1][id]" value=""/>';
			$html .= '</td>';
			$html .= '<td><div class="tajer-file-path-wrapper">
<input type="text" disabled class="tajer-file-url" value="" />
<a href="#" class="tajer-upload-file">' . esc_html__( 'Upload File' ) . '</a>
</div></td>';
			$html .= '<td>';
			$html .= '<select name="tajer_file[1][prices][]" class="tajer_files_dropdown tajer_multiple_files_prices" multiple>';
			if ( ( is_array( $pricesIds ) ) && ( ! empty( $pricesIds ) ) ) {
				foreach ( $pricesIds as $price_id ) {
					$html .= '<option value="' . $price_id . '">' . $price_id . '</option>';
					$html .= '<option value="1">1</option>';
				}
			}
			$html .= '</select>';
			$html .= '</td>';
			$html .= '<td><a href="#" data-pid="' . $post_id . '" data-file_id="1" class="tajer_remove_repeatable_file_assignment" style="background: url(' . Tajer_URL . 'images/xit.gif) no-repeat;">×</a></td>';

			$html .= '</tr>';
		}
		$html .= '</table>';
		$html .= '<br>';
//		$html .= '<button class="tajer-add-file button-secondary" >' . __( "Add New Files", 'tajer' ) . '</button>';
		$html .= '<a class="tajer-add-file button-secondary">' . __( 'Add New Files', 'tajer' ) . '</a>';
//		$html .= '<button class="tajer-select-file button-secondary" >' . __( "Select From Exist", 'tajer' ) . '</button>';
//		$html .= '<p>' . __( '*If variable pricing are off the first file will be used as download file.' ) . '</p>';
		$html .= '</div>';

//		ob_start();
//		tajer_get_template_part( 'admin-products-select-file-dialog' );
//		tajer_get_template_part( 'admin-products-file-upload-dialog' );
//		$output = ob_get_clean();

//		$html .= $output;
		$html .= '</div>';
		$html = apply_filters( 'tajer_files_meta_box_html', $html, $post, $post_id, $is_bundle, $bundled_products, $files, $products_post_type );

		if ( ! $echo ) {
			return $html;
		}
		echo $html;
	}

//	function show_tajer_files( $post = 0, $args = array(), $echo = true, $postId = 0 ) {
//
//		if ( $postId ) {
//			$post_id = $postId;
//		} else {
//			$post_id = $post->ID;
//		}
//
//
//		$is_bundle          = get_post_meta( $post_id, 'tajer_bundle', true );
//		$files              = get_post_meta( $post_id, 'tajer_files', true );
//		$bundled_products   = get_post_meta( $post_id, 'tajer_bundled_products', true );
//		$args               = array( 'post_type' => 'tajer_products' );
//		$products_post_type = get_posts( $args );
//
//
//		$html = '';
//		$html .= '<div id="tajer-files-area">';
//		$html .= '<p><label style="width:100%" for="tajer_bundle">';
//		$html .= '<input type="checkbox" ' . checked( 1, $is_bundle, false ) . ' name="tajer_bundle" id="tajer_bundle" value="1">';
//		$html .= __( 'Bundle?', 'tajer' ) . '</label></p>';
//		$html .= '<div class="tajer_enable_bundle">';
//		$html .= '<label for="tajer_bundled_products[]">' . __( 'Bundled Products:', 'tajer' ) . '  </label>';
//		$html .= '<select name="tajer_bundled_products[]" id="tajer_bundled_products[]" multiple>';
//
//		foreach ( $products_post_type as $product_post ) {
//			$selected = false;
//			if ( is_array( $bundled_products ) && in_array( strval( $product_post->ID ), $bundled_products ) ) {
//				$selected = true;
//			}
//			setup_postdata( $product_post );
//			$html .= '<option ' . selected( $selected, true, false ) . ' value="' . $product_post->ID . '">' . $product_post->post_title . '</option>';
//			wp_reset_postdata();
//		}
//
//		$html .= '</select>';
////		$html .= '<p>' . __( '* Currently the default price will be used for each of the bundle products', 'tajer' );
////		$html .= '</p>';
//		$html .= '</div>';
//		$html .= '<br>';
//
//		$html .= '<div class="tajer_enable_multiple_files">';
//		$html .= '<table id="multiple_files_table">';
//		$html .= '<tr>';
//		$html .= '<th>' . __( 'File Name', 'tajer' ) . '</th>';
//		$html .= '<th>' . __( 'File', 'tajer' ) . '</th>';
//		$html .= '<th>' . __( 'Price Assignment', 'tajer' ) . '</th>';
//		$html .= '<th>' . __( 'Delete File & Assignment', 'tajer' ) . '</th>';
//		$html .= '<th>' . __( 'Delete Assignment', 'tajer' ) . '</th>';
//		$html .= '</tr>';
//		if ( $files && ( ! empty( $files ) ) ) {
//			$html = $this->files_renderer( $files, $html, $post_id );
//		} else {
//			$html .= '<tr class="tajer_repeatable_row" data-index="1">';
//			$html .= '<td>';
//			$html .= __( 'No files uploaded yet', 'tajer' );
//			$html .= '</td>';
//			$html .= '<td>';
//
//			$html .= '</td>';
//			$html .= '<td>';
//			$html .= '</td>';
//			$html .= '<td>';
//			$html .= '</td>';
//			$html .= '<td>';
//			$html .= '</td>';
//			$html .= '</tr>';
//		}
//		$html .= '</table>';
//		$html .= '<br>';
//		$html .= '<button class="tajer-add-file button-secondary" >' . __( "Add New Files", 'tajer' ) . '</button>';
//		$html .= '<button class="tajer-select-file button-secondary" >' . __( "Select From Exist", 'tajer' ) . '</button>';
////		$html .= '<p>' . __( '*If variable pricing are off the first file will be used as download file.' ) . '</p>';
//		$html .= '</div>';
//
//		ob_start();
//		tajer_get_template_part( 'admin-products-select-file-dialog' );
//		tajer_get_template_part( 'admin-products-file-upload-dialog' );
//		$output = ob_get_clean();
//
//		$html .= $output;
//		$html .= '</div>';
//		$html = apply_filters( 'tajer_files_meta_box_html', $html, $post, $post_id, $is_bundle, $bundled_products, $files, $products_post_type );
//
//		if ( ! $echo ) {
//			return $html;
//		}
//		echo $html;
//	}

//	function get_files() {
//		$html  = '';
//		$files = tajer_get_files();
//		foreach ( $files as $file ) {
//			$html .= '<option value="' . $file . '">' . basename( $file ) . '</option>';
//		}
//
//		return $html;
//	}

//	function get_roles() {
//		$html  = '';
//		$roles = tajer_get_user_roles();
//		foreach ( $roles as $role_name => $role ) {
//			$html .= '<option value="' . $role_name . '">' . $role . '</option>';
//		}
//
//		return $html;
//	}


	function show_price_options( $field_name, $meta ) {
		$html = '<input type="hidden" name="' . $field_name . '[roles]" value="' . ( is_array( $meta ) ? $meta['roles'] : '' ) . '"/>';
//		$html .= '<input type="hidden" name="' . $field_name . '[download_link_expiration]" value="' . ( is_array( $meta ) ? $meta['download_link_expiration'] : '' ) . '"/>';
		$html .= '<input type="hidden" name="' . $field_name . '[price_expiration]" value="' . ( is_array( $meta ) ? $meta['price_expiration'] : '' ) . '"/>';
		$html .= '<input type="hidden" name="' . $field_name . '[file_download_limit]" value="' . ( is_array( $meta ) ? $meta['file_download_limit'] : '' ) . '"/>';
		$html .= '<input type="hidden" name="' . $field_name . '[capabilities]" value="' . ( is_array( $meta ) ? $meta['capabilities'] : '' ) . '"/>';

		return apply_filters( 'tajer_price_options_html', $html, $field_name, $meta );
	}

	// Save the Data
	function save_custom_meta( $post_id ) {

		// verify nonce
		if ( ( ! isset( $_REQUEST['single_price_options_nonce'] ) ) || ( ! wp_verify_nonce( $_POST['single_price_options_nonce'], basename( __FILE__ ) ) ) ) {
			return $post_id;
		}
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		// check permissions
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( apply_filters( 'tajer_admin_products_save_custom_fields_page_capability', 'edit_page' ) ) ) {
				return $post_id;
			}
		} elseif ( ! current_user_can( apply_filters( 'tajer_admin_products_save_custom_fields_capability', 'edit_posts' ) ) ) {
			return $post_id;
		}

		do_action( 'tajer_save_products_meta_data' );

//		// loop through fields and save the data
//		foreach ( $this->custom_fields as $field ) {
////fms_debug($field['id'],true,'');
//
//			$old = get_post_meta( $post_id, $field['meta_key'], true );
//			$new = $_POST[ $field['meta_key'] ];
//			if ( $new && $new != $old ) {
//				update_post_meta( $post_id, $field['meta_key'], $new );
//			} elseif ( '' == $new && $old ) {
//				delete_post_meta( $post_id, $field['meta_key'], $old );
//			}
//		} // end foreach

		//save the special fields
		//Product prices
		if ( ( isset( $_POST['tajer_product_prices'] ) ) && ( ! empty( $_POST['tajer_product_prices'] ) ) ) {
//			update_post_meta( $post_id, 'tajer_variable_pricing', 1 );
			update_post_meta( $post_id, 'tajer_product_prices', apply_filters( 'tajer_before_saving_product_prices', $_POST['tajer_product_prices'], $post_id ) );
			update_post_meta( $post_id, 'tajer_default_multiple_price', apply_filters( 'tajer_before_saving_default_multiple_price', $_POST['tajer_default_multiple_price'], $post_id ) );
		} else {
//			update_post_meta( $post_id, 'tajer_price', $_POST['tajer_single_price'] );
//			update_post_meta( $post_id, 'tajer_price_options', $_POST['tajer_price_options'] );
//			update_post_meta( $post_id, 'tajer_variable_pricing', 0 );
		}
		//Files
		if ( ( isset( $_POST['tajer_file'] ) ) || ( isset( $_POST['tajer_bundle'] ) ) ) {

			$is_bundle = ( isset( $_POST['tajer_bundle'] ) && ( $_POST['tajer_bundle'] == '1' ) ) ? 1 : 0;
			update_post_meta( $post_id, 'tajer_bundle', apply_filters( 'tajer_before_saving_is_bundle', $is_bundle, $post_id ) );
			update_post_meta( $post_id, 'tajer_bundled_products', apply_filters( 'tajer_before_saving_bundled_products', ( isset( $_POST['tajer_bundled_products'] ) ? $_POST['tajer_bundled_products'] : '' ), $post_id ) );


			update_post_meta( $post_id, 'tajer_files', apply_filters( 'tajer_before_saving_multiple_files', $_POST['tajer_file'], $post_id ) );

		}
		//recurring
		if ( ( isset( $_POST['tajer_is_recurring'] ) ) ) {
			$is_recurring = ( $_POST['tajer_is_recurring'] == '1' ) ? 1 : 0;
			update_post_meta( $post_id, 'tajer_is_recurring', apply_filters( 'tajer_before_saving_is_recurring', $is_recurring, $post_id ) );
			update_post_meta( $post_id, 'tajer_recurring', apply_filters( 'tajer_before_saving_is_recurring', $_POST['tajer_recurring'], $post_id ) );
		}
		//upgrade
		if ( ( isset( $_POST['tajer_is_upgrade'] ) ) ) {
			$is_upgrade = ( $_POST['tajer_is_upgrade'] == '1' ) ? 1 : 0;
			update_post_meta( $post_id, 'tajer_is_upgrade', apply_filters( 'tajer_before_saving_is_upgrade', $is_upgrade, $post_id ) );
			update_post_meta( $post_id, 'tajer_upgrade', apply_filters( 'tajer_before_saving_upgrade', $_POST['tajer_upgrade'], $post_id ) );
		}
		//Trial
		if ( ( isset( $_POST['tajer_is_trial'] ) ) ) {
			$is_trial = ( $_POST['tajer_is_trial'] == '1' ) ? 1 : 0;
			update_post_meta( $post_id, 'tajer_is_trial', apply_filters( 'tajer_before_saving_is_trial', $is_trial, $post_id ) );
			update_post_meta( $post_id, 'tajer_trial', apply_filters( 'tajer_before_saving_trial', $_POST['tajer_trial'], $post_id ) );
		}
	}

//	function tajer_upload_files() {
//
//		//security check
//		if ( ( ! ( isset( $_REQUEST['upload_nonce_field'] ) ) ) && ( ! ( wp_verify_nonce( $_REQUEST['upload_nonce_field'], 'upload_nonce' ) ) ) ) {
//			wp_die( 'Security Check' );
//		}
//
//
//		$post_ID = intval( $_REQUEST['post_ID'] );
//
//		if ( $_FILES ) {
//			//change the default uploading directory
//			tajer_secure_upload_location();
//
//			tajer_fix_file_array( $_FILES['files'] );
//			if ( ! function_exists( 'wp_handle_upload' ) ) {
//				require_once( ABSPATH . 'wp-admin/includes/file.php' );
//			}
//
//			$upload_overrides = array( 'test_form' => false );
//			foreach ( $_FILES['files'] as $file => $fileitem ) {
//				$movefile = wp_handle_upload( $fileitem, $upload_overrides );
//			}
//
//			tajer_reset_secure_upload_location();
//		}
//
//		$files     = get_post_meta( $post_ID, 'tajer_files', true );
//		$file_data = array();
//
//		$file_data['name']   = basename( $movefile['file'] );
//		$file_data['url']    = $movefile['url'];
//		$file_data['path']   = str_replace( array( '\\', '//' ), '/', $movefile['file'] );
//		$file_data['prices'] = array();
//		$files[]             = apply_filters( 'tajer_admin_products_upload_files', $file_data, $files );;
//
//
//		update_post_meta( $post_ID, 'tajer_files', $files );
//
//		$response = array(
//			'name' => basename( $movefile['file'] )
//		);
//		tajer_response( $response );
//	}

//	function get_files_area() {
//		//security check
//		if ( ( ! ( isset( $_REQUEST['upload_nonce_field'] ) ) ) && ( ! ( wp_verify_nonce( $_REQUEST['upload_nonce_field'], 'upload_nonce' ) ) ) ) {
//			wp_die( 'Security Check' );
//		}
//
//		$post_ID = intval( $_REQUEST['post_ID'] );
//
//		$html     = $this->show_tajer_files( 0, array(), false, $post_ID );
//		$response = array(
//			'html' => apply_filters( 'tajer_admin_products_get_files_area_html', $html )
//		);
//		tajer_response( $response );
//	}

	public function files_renderer( $files, $html, $post_id, $pricesIds ) {
		$prices = get_post_meta( $post_id, 'tajer_product_prices', true );

		foreach ( $files as $id => $detail ) {

			$url = wp_get_attachment_url( $detail['id'] );
//			$name = basename ( get_attached_file( $detail['id'] ) );

			$html .= '<tr class="tajer_repeatable_row" data-index="' . $id . '">';
			$html .= '<td><input type="text" name="tajer_file[' . $id . '][name]" class="tajer-file-name" value="' . $detail['name'] . '" /><input type="hidden" class="tajer-attachment-id" name="tajer_file[' . $id . '][id]" value="' . $detail['id'] . '"/></td>';
			$html .= '<td><div class="tajer-file-path-wrapper">
<input type="text" disabled class="tajer-file-url" value="' . $url . '" />
<a href="#" class="tajer-upload-file">' . esc_html__( 'Upload File' ) . '</a>
</div></td>';

			$html .= '<td>';
			$html .= '<select name="tajer_file[' . $id . '][prices][]" class="tajer_files_dropdown tajer_multiple_files_prices" multiple>';
			if ( ( is_array( $pricesIds ) ) && ( ! empty( $pricesIds ) ) ) {
				foreach ( $pricesIds as $price_id ) {
					if ( isset( $detail['prices'] ) && is_array( $detail['prices'] ) && in_array( $price_id, $detail['prices'] ) ) {
						$selected = 'selected';
					} else {
						$selected = '';
					}
					$html .= '<option value="' . $price_id . '" ' . $selected . '>' . $price_id . '</option>';
				}
			}

			$html .= '</select>';
			$html .= '</td>';

			$html .= '<td><a href="#" data-pid="' . $post_id . '" data-file_id="' . $id . '" class="tajer_remove_repeatable_file_assignment" style="background: url(' . Tajer_URL . 'images/xit.gif) no-repeat;">×</a></td>';
			$html .= '</tr>';
		}

		return apply_filters( 'tajer_admin_products_files_renderer', $html, $post_id, $prices, $files );
	}

	public function bundle_render( $html, $products_post_type, $bundled_products, $pricesIds ) {

		$html .= '<div class="tajer_enable_bundle">';
		$html .= '<table id="tajer_bundle_table">';
		$html .= '<tr>';
		$html .= '<th>' . __( 'Product', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'Sub Product(s)', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'Price Assignment', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'Delete', 'tajer' ) . '</th>';
		$html .= '</tr>';
		if ( $bundled_products && ! empty( $bundled_products ) ) {
			foreach ( $bundled_products as $id => $bundled_product ) {
				$html .= '<tr class="tajer_repeatable_row" data-index="' . $id . '">';
				$html .= '<td>';
				$html .= '<select class="tajer_bundled_products_product" name="tajer_bundled_products[' . $id . '][product]">';

				foreach ( $products_post_type as $product_post ) {
					$selected = false;
					if ( strval( $product_post->ID ) == $bundled_product['product'] ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . $product_post->ID . '">' . $product_post->post_title . '</option>';
				}

				$html .= '</select>';
				$html .= '</td>';
				$html .= '<td>';
				$html .= '<select class="tajer_bundled_products_ids" name="tajer_bundled_products[' . $id . '][sub_ids][]" multiple>';
				$product_sub_ids        = tajer_get_product_sub_ids_with_names( (int) $bundled_product['product'] );
				$product_sub_ids['all'] = __( "All", 'tajer' );

				foreach ( $product_sub_ids as $product_sub_id_id => $product_sub_id_name ) {
					$selected = false;
					if (isset($bundled_product['sub_ids']) && is_array( $bundled_product['sub_ids'] ) ) {
						if ( in_array( strval( $product_sub_id_id ), $bundled_product['sub_ids'] ) ) {
							$selected = true;
						}
					}

					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . $product_sub_id_id . '">' . $product_sub_id_name . '</option>';
				}

				$html .= '</select>';
				$html .= '</td>';


				$html .= '<td>';
				$html .= '<select name="tajer_bundled_products[' . $id . '][price]" class="tajer_multiple_files_prices">';
				if ( ( is_array( $pricesIds ) ) && ( ! empty( $pricesIds ) ) && ( count( $pricesIds ) > 1 || ! in_array( 'All', $pricesIds ) ) ) {
					foreach ( $pricesIds as $price_id ) {
						if ( $price_id == 'All' ) {
							continue;
						}
						$html .= '<option ' . selected( $bundled_product['price'], $price_id, false ) . ' value="' . $price_id . '">' . $price_id . '</option>';
					}
				} else {
					$html .= '<option value="1">1</option>';
				}
				$html .= '</select>';
				$html .= '</td>';


				$html .= '<td><a href="#" class="tajer_remove_repeatable_bundle_product" style="background: url(' . Tajer_URL . 'images/xit.gif) no-repeat;">×</a></td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr class="tajer_repeatable_row" data-index="1">';
			$html .= '<td>';
			$html .= '<select class="tajer_bundled_products_product" name="tajer_bundled_products[1][product]">';

			foreach ( $products_post_type as $product_post ) {
				$html .= '<option value="' . $product_post->ID . '">' . $product_post->post_title . '</option>';
			}

			$html .= '</select>';
			$html .= '</td>';
			$html .= '<td>';
			$html .= '<select class="tajer_bundled_products_ids" name="tajer_bundled_products[1][sub_ids][]" multiple>';

			$first_product          = reset( $products_post_type );
			$pid                    = is_object( $first_product ) ? $first_product->ID : false;
			$product_sub_ids        = tajer_get_product_sub_ids_with_names( $pid );
			$product_sub_ids['all'] = __( "All", 'tajer' );

			foreach ( $product_sub_ids as $product_sub_id_id => $product_sub_id_name ) {

				$html .= '<option value="' . $product_sub_id_id . '">' . $product_sub_id_name . '</option>';
			}
			$html .= '</select>';
			$html .= '</td>';


			$html .= '<td>';
			$html .= '<select name="tajer_bundled_products[1][price]" class="tajer_multiple_files_prices">';
			if ( ( is_array( $pricesIds ) ) && ( ! empty( $pricesIds ) ) && ( count( $pricesIds ) > 1 || ! in_array( 'All', $pricesIds ) ) ) {
				foreach ( $pricesIds as $price_id ) {
					if ( $price_id == 'All' ) {
						continue;
					}
					$html .= '<option value="' . $price_id . '">' . $price_id . '</option>';
				}
			} else {
				$html .= '<option value="1">1</option>';
			}
			$html .= '</select>';
			$html .= '</td>';


			$html .= '<td><a href="#" class="tajer_remove_repeatable_bundle_product" style="background: url(' . Tajer_URL . 'images/xit.gif) no-repeat;">×</a></td>';
			$html .= '</tr>';
		}
		$html .= '</table>';
		$html .= wp_nonce_field( 'tajer_bundle_nonce', 'tajer_bundle_nonce_field', true, false );

		$html .= '<br>';
//		$html .= '<button class="tajer-add-bundle-product button-secondary" >' . __( "Add Bundle Product", 'tajer' ) . '</button>';
		$html .= '<a class="tajer-add-bundle-product button-secondary">' . __( 'Add Bundle Product', 'tajer' ) . '</a>';
		$html .= '</div>';

		return $html;
	}
}
