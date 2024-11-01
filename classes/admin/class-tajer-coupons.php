<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Coupons extends Tajer_Products {

	private static $instance;
	private $prefix = 'tajer_';
	private $general_meta_key = 'tajer_coupon';
	private $custom_fields = array();

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'save_post', array( $this, 'save_custom_meta' ) );
		add_action( 'init', array( $this, 'tajer_coupons' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_custom_meta_box' ) );
		add_filter( 'manage_edit-tajer_coupons_columns', array( $this, 'admin_column' ) );
		add_action( 'manage_tajer_coupons_posts_custom_column', array( $this, 'admin_column_value' ), 10, 2 );

		$this->custom_fields = $this->fields();
	}

	// Register Custom Post Type
	function tajer_coupons() {
		$capability = apply_filters( 'tajer_coupons_capability', 'manage_options' );
		$labels     = apply_filters( 'tajer_coupon_post_type_labels', array(
			'name'               => _x( 'Coupons', 'Post Type General Name', 'tajer' ),
			'singular_name'      => _x( 'Coupon', 'Post Type Singular Name', 'tajer' ),
			'menu_name'          => __( 'Coupons', 'tajer' ),
			'parent_item_colon'  => __( 'Parent Coupon:', 'tajer' ),
			'view_item'          => __( 'View Coupon', 'tajer' ),
			'add_new_item'       => __( 'Add New Coupon', 'tajer' ),
			'add_new'            => __( 'Add New', 'tajer' ),
			'edit_item'          => __( 'Edit Coupon', 'tajer' ),
			'update_item'        => __( 'Update Coupon', 'tajer' ),
			'search_items'       => __( 'Search Coupon', 'tajer' ),
			'not_found'          => __( 'Not found', 'tajer' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'tajer' ),
		) );
		$args       = apply_filters( 'tajer_coupon_post_type_args', array(
			'labels'          => $labels,
			'supports'        => array( 'title' ),
			'hierarchical'    => false,
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'edit.php?post_type=tajer_products',//or string
			'rewrite'         => array( 'slug' => 'coupon' ),
			'can_export'      => true,
			'capability_type' => 'post',
			'capabilities'    => array(
				'publish_posts'       => $capability,
				'edit_posts'          => $capability,
				'edit_post'           => $capability,
				'edit_others_posts'   => $capability,
				'delete_posts'        => $capability,
				'delete_others_posts' => $capability,
				'read_private_posts'  => $capability,
				'delete_post'         => $capability,
				'read_post'           => $capability,
			)
		) );
		register_post_type( apply_filters( 'tajer_coupon_post_type_name', 'tajer_coupons', $args ), $args );
	}

	function admin_column( $columns ) {
		$columns['coupon_code'] = __( 'Coupon Code', 'tajer' );
		$columns['start_date']  = __( 'Start Date', 'tajer' );
		$columns['expiration']  = __( 'Expiration', 'tajer' );
		$columns['uses']        = __( 'Uses', 'tajer' );
		$columns['status']      = __( 'Status', 'tajer' );
		$columns['max_users']   = __( 'Max Uses', 'tajer' );

		return $columns;
	}

	function admin_column_value( $column_name, $post_id ) {
		$meta              = apply_filters( 'tajer_admin_coupon', get_post_meta( $post_id, 'tajer_coupon', true ), $column_name, $post_id );
		$tajer_coupon_code = apply_filters( 'tajer_admin_coupon_code', get_post_meta( $post_id, 'tajer_coupon_code', true ), $column_name, $post_id );

		//get how many times this coupon used
		$times = apply_filters( 'tajer_admin_coupon_used', Tajer_DB::count_coupon_used( $tajer_coupon_code ), $column_name, $post_id );

		switch ( $column_name ) {
			case 'coupon_code':
				echo '<code>' . $tajer_coupon_code . '</code>';
				break;

			case 'start_date':
				echo $meta['tajer_start_date'];
				break;

			case 'expiration':
				echo $meta['tajer_expiration_date'];
				break;

			case 'uses':
				echo $times;
				break;

			case 'max_users':
				echo empty( $meta['tajer_max_users'] ) ? __( 'Unlimited', 'tajer' ) : $meta['tajer_max_users'];
				break;

			case 'status':

				if ( ( ( $times >= ( (int) $meta['tajer_max_users'] ) ) && ( ! empty( $meta['tajer_max_users'] ) ) ) || ( date( 'Y-m-d H:i:s' ) > $meta['tajer_expiration_date'] ) || ( date( 'Y-m-d H:i:s' ) < $meta['tajer_start_date'] ) || ( $meta['tajer_status'] == 'inactive' ) ) {
					_e( 'Inactive', 'tajer' );
				} else {
					_e( 'Active', 'tajer' );
				}

				break;
		}
	}

	function scripts() {
		global $pagenow, $post;
		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		if ( ! in_array( $post->post_type, array( 'tajer_coupons' ) ) ) {
			return;
		}

		wp_enqueue_style( 'tajer-datetimepicker-css', Tajer_URL . 'lib/datetimepicker-master/jquery.datetimepicker.css' );
		wp_enqueue_style( 'tajer-coupons-css', Tajer_URL . 'css/admin/coupons.css', array( 'tajer-datetimepicker-css' ) );
		wp_enqueue_script( 'tajer-datetimepicker-js', Tajer_URL . 'lib/datetimepicker-master/jquery.datetimepicker.js', array( 'jquery' ) );

		wp_enqueue_script( 'tajer-coupons-js', Tajer_URL . 'js/admin/tajer-coupons.js', array(
			'tajer-datetimepicker-js'
		) );
	}

	function fields() {
		// Field Array
		$prefix        = $this->prefix;
		$custom_fields = apply_filters( 'tajer_admin_coupon_fields', array(
			array(
				'label'    => 'Coupon Code',
				'desc'     => '',
				'id'       => $prefix . 'coupon_code',
				'meta_key' => $prefix . 'coupon_code',
				'type'     => 'text'
			),
			array(
				'label'    => 'Start date',
				'desc'     => '',
				'id'       => 'tajer_coupon[' . $prefix . 'start_date]',
				'meta_key' => $prefix . 'start_date',
				'type'     => 'text'
			),
			array(
				'label'    => 'Expiration Date',
				'desc'     => '',
				'id'       => 'tajer_coupon[' . $prefix . 'expiration_date]',
				'meta_key' => $prefix . 'expiration_date',
				'type'     => 'text'
			),
			array(
				'label'    => 'Savings Type',
				'desc'     => '',
				'id'       => 'tajer_coupon[' . $prefix . 'savings_type]',
				'meta_key' => $prefix . 'savings_type',
				'type'     => 'select',
				'options'  => array( 'amount' => 'Fixed Amount', 'percentage' => 'Percentage' )
			),
			array(
				'label'    => 'Savings',
				'desc'     => '',
				'id'       => 'tajer_coupon[' . $prefix . 'savings]',
				'meta_key' => $prefix . 'savings',
				'type'     => 'text'
			),
			array(
				'label'    => 'Minimum Purchase',
				'desc'     => 'The minimum amount that must be purchased before this discount can be used. Leave blank for no minimum.',
				'id'       => 'tajer_coupon[' . $prefix . 'minimum_purchase]',
				'meta_key' => $prefix . 'minimum_purchase',
				'type'     => 'text'
			),
			array(
				'label'    => 'Apply Coupon to Products',
				'desc'     => '',
				'id'       => 'tajer_coupon[' . $prefix . 'products]',
				'meta_key' => $prefix . 'products',
				'multi'    => 'multiple',
				'type'     => 'select',
				'options'  => tajer_get_products()
			),
			array(
				'label'    => 'Max Uses',
				'desc'     => 'The maximum number of times this discount can be used. Leave blank for unlimited.',
				'id'       => 'tajer_coupon[' . $prefix . 'max_users]',
				'meta_key' => $prefix . 'max_users',
				'type'     => 'text'
			),
			array(
				'label'    => 'Status',
				'desc'     => '',
				'id'       => 'tajer_coupon[' . $prefix . 'status]',
				'meta_key' => $prefix . 'status',
				'type'     => 'select',
				'options'  => array( 'active' => 'Active', 'inactive' => 'Inactive' )
			)
		), $prefix );

		return $custom_fields;
	}

	// Add the Meta Box
	function add_custom_meta_box() {
		add_meta_box(
			'tajer', // $id
			'Coupon Settings', // $title
			array( $this, 'show_tajer' ), // $callback
			'tajer_coupons', // $page
			'normal', // $context
			'high' ); // $priority
	}

	// Save the Data
	function save_custom_meta( $post_id ) {

		// verify nonce
		if ( ( ! isset( $_REQUEST['custom_meta_box_nonce'] ) ) || ( ! wp_verify_nonce( $_POST['custom_meta_box_nonce'], basename( __FILE__ ) ) ) ) {
			return $post_id;
		}
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		// check permissions
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( apply_filters( 'tajer_coupons_save_custom_fields_page_capability', 'edit_page' ) ) ) {
				return $post_id;
			}
		} elseif ( ! current_user_can( apply_filters( 'tajer_coupons_save_custom_fields_capability', 'edit_posts' ) ) ) {
			return $post_id;
		}

		do_action( 'tajer_save_coupon_meta_data' );

		update_post_meta( $post_id, 'tajer_coupon', apply_filters( 'tajer_before_saving_coupon_data', $_POST['tajer_coupon'], $post_id ) );
		update_post_meta( $post_id, 'tajer_coupon_code', apply_filters( 'tajer_before_saving_coupon_code', $_POST['tajer_coupon_code'], $post_id ) );

		// loop through fields and save the data
//		foreach ( $this->custom_fields as $field ) {
//
//			$old = get_post_meta( $post_id, $field['meta_key'], true );
//			$new = $_POST[ $field['meta_key'] ];
//			if ( $new && $new != $old ) {
//				update_post_meta( $post_id, $field['meta_key'], $new );
//			} elseif ( '' == $new && $old ) {
//				delete_post_meta( $post_id, $field['meta_key'], $old );
//			}
//		} // end foreach
	}

	//show the custom fields
	function show_tajer() {
		global $post;
		// Use nonce for verification
		echo '<input type="hidden" name="custom_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />';
		do_action( 'tajer_coupon_admin_page' );

		// Begin the field table and loop
		echo '<table class="form-table">';
		foreach ( $this->custom_fields as $field ) {
			// get value of this field if it exists for this post
			$meta = get_post_meta( $post->ID, 'tajer_coupon', true );

			// begin a table row with
			echo '<tr>
                <th><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
                <td>';
			switch ( $field['type'] ) {
				// text
				case 'text':
//					if ( ( $field['id'] === $this->prefix . 'product_unique_name' ) ) {
//						$meta = tajer_generate_unique_name( $post->ID );
//					}

					if ( $field['meta_key'] == 'tajer_coupon_code' ) {
						$val = get_post_meta( $post->ID, 'tajer_coupon_code', true );
					} else {
						$val = isset($meta[ $field['meta_key'] ])?$meta[ $field['meta_key'] ]:'';
					}

					echo '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $val . '" size="30" />
                            <br /><span class="description">' . $field['desc'] . '</span>';
					break;

				// radio
				case 'radio':

					$html = '';

					foreach ( $field['options'] as $option => $value ) {
						$html .= '<input type="radio" name="' . $field['id'] . '" ' . checked( $meta[ $field['meta_key'] ], $value, false ) . ' id="' . $field['id'] . '" value="' . $value . '">' . $option . ' ';
					}

					$html .= '<br /><span class="description">' . $field['desc'] . '</span>';
					echo $html;
					break;

				// select
				case 'select':

					$multiple = ( isset( $field['multi'] ) && ( $field['multi'] == 'multiple' ) ) ? 'multiple' : '';
					$brackets = ( isset( $field['multi'] ) && ( $field['multi'] == 'multiple' ) ) ? '[]' : '';

					$html = '<select ' . $multiple . ' name="' . $field['id'] . $brackets . '" id="' . $field['id'] . '">';
					foreach ( $field['options'] as $value => $option ) {
						$selected = false;
						if ( isset($meta[ $field['meta_key'] ])&&is_array( $meta[ $field['meta_key'] ] ) && in_array( $value, $meta[ $field['meta_key'] ] ) ) {
							$selected = true;
						} elseif (isset($meta[ $field['meta_key'] ])&& ($meta[ $field['meta_key'] ] == $value) ) {
							$selected = true;
						}
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . $value . '">' . $option . '</option>';
					}
					$html .= '</select>';
					$html .= '<br /><span class="description">' . $field['desc'] . '</span>';
					echo $html;
					break;
			} //end switch
			echo '</td></tr>';
		} // end foreach
		echo '</table>'; // end table
	}
}
