<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Content_Seller {
	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		add_filter( 'the_content', array( $this, 'the_content' ), 99999999999 );
		add_shortcode( 'tajer_product', array( $this, 'tajer_product_shortcode_parser' ) );
		add_action( 'add_meta_boxes', array( $this, 'content_seller_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_custom_meta' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_tajer_get_content_seller_product_sub_ids', array( $this, 'get_product_sub_ids' ) );
	}

	// Save the Data
	function save_custom_meta( $post_id ) {

		// verify nonce
		if ( ( ! isset( $_REQUEST['tajer_content_seller_nonce_field'] ) ) || ( ! wp_verify_nonce( $_POST['tajer_content_seller_nonce_field'], 'tajer_content_seller_nonce' ) ) ) {
			return $post_id;
		}
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		// check permissions
//		if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( apply_filters( 'tajer_content_seller_save_custom_fields_page_capability', 'manage_options' ) ) ) {
			return $post_id;
		}
//		} elseif ( ! current_user_can( apply_filters( 'tajer_content_seller_save_custom_fields_capability', 'edit_posts' ) ) ) {
//			return $post_id;
//		}


		if ( ( isset( $_POST['tajer_content_seller'] ) ) && ( ! empty( $_POST['tajer_content_seller'] ) ) ) {
			update_post_meta( $post_id, 'content_seller', $_POST['tajer_content_seller'] );
		}
	}

	function enqueue_scripts() {

//		if(1){
//
//		}
		wp_enqueue_style( 'chosen-jquery-css', Tajer_URL . 'lib/chosen_v1.2.0/chosen.min.css' );
		wp_enqueue_style( 'tajer-content-seller-css', Tajer_URL . 'css/admin/content-seller.css', array(
			'chosen-jquery-css'
		) );


		wp_enqueue_script( 'chosen-jquery-js', Tajer_URL . 'lib/chosen_v1.2.0/chosen.jquery.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'tajer-content-seller-js', Tajer_URL . 'js/admin/content-seller.js', array(
			'chosen-jquery-js'
		) );
	}

	function tajer_product_shortcode_parser( $attr, $content ) {

		do_action( 'tajer_product_shortcode_parser', $attr, $content );

		$license_message    = isset( $attr['license_message'] ) ? $attr['license_message'] : esc_html__( 'You must be logged in and have a valid license to access this content.', 'tajer' );
		$expiration_message = isset( $attr['expiration_message'] ) ? $attr['expiration_message'] : esc_html__( 'Your subscription to access this content has been expired.', 'tajer' );

		if ( is_user_logged_in() ) {
			$product_id     = isset( $attr['product_id'] ) ? intval( $attr['product_id'] ) : 0;
			$product_sub_id = isset( $attr['product_sub_id'] ) ? intval( $attr['product_sub_id'] ) : 0;
			if ( tajer_is_free( $product_id, $product_sub_id ) ) {
				if ( Tajer_CanAccess( $product_id, $product_sub_id ) ) {
					return do_shortcode( apply_filters( 'tajer_content_seller_can_access_free_product', $content, $attr, $content ) );
				} else {
					return apply_filters( 'tajer_content_seller_can_not_access_message', '<div id="tajer-content-product"><p>' . $license_message . '</p></div>', $attr, $content );
				}
			} else {
				$user_id         = get_current_user_id();
				$item            = Tajer_DB::get_row_by_product_id_product_sub_id_user_id( 'tajer_user_products', $product_id, $product_sub_id, $user_id );
				$expiration_date = $item->expiration_date;
				if ( is_null( $item ) ) {
					return apply_filters( 'tajer_content_seller_no_data_in_database', '<div id="tajer-content-product"><p>' . $license_message . '</p></div>', $attr, $content, $item );
				} elseif ( $item->status == 'inactive' ) {
					return apply_filters( 'tajer_content_seller_inactive_message', '<div id="tajer-content-product"><p>' . esc_html__( 'Your subscription to access this content is inactive, for more information please contact the website administrator.', 'tajer' ) . '</p></div>', $attr, $content, $item );
				} elseif ( ( date( 'Y-m-d H:i:s' ) > $expiration_date ) ) {
					//the product has expired, delete the product
					$is_deleted = Tajer_DB::delete_by_id( 'tajer_user_products', $item->id );

					if ( $is_deleted === false ) {
						return apply_filters( 'tajer_content_seller_error_deleting_product', __( 'Error contact website admin!', 'tajer' ), $is_deleted, $attr, $content, $item );
					}

					return apply_filters( 'tajer_content_seller_expiration_message', '<div id="tajer-content-product"><p>' . $expiration_message . '</p></div>', $is_deleted, $attr, $content, $item );

				} else {
					return do_shortcode( apply_filters( 'tajer_content_seller_can_access_paid_product', $content, $attr, $content ) );
				}
			}
		} else {
			return apply_filters( 'tajer_content_seller_not_logged_in_message', '<div id="tajer-content-product"><p>' . $license_message . '</p></div>', $attr, $content );
		}

	}

	/**
	 * This function will filter the content based on the content seller meta box settings
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	function the_content( $content ) {
		global $post;

		$content_seller = get_post_meta( $post->ID, 'content_seller', true );


		if ( ! isset( $content_seller['enabled'] ) || $content_seller['enabled'] != 'yes' ) {
			return $content;
		}

		$content_seller_products = $content_seller['products'];
		$RestrictionMessage      = $content_seller['restriction_message'];
		$restriction_message     = $this->restriction_message( $RestrictionMessage );
		$can_access              = false;
		$product_sub_ids         = array();


		if ( is_user_logged_in() ) {
			foreach ( $content_seller_products as $id => $content_seller_product ) {
				if ( $can_access ) {
					break;
				}

				$product_id = $content_seller_product['product'];
				if ( isset( $content_seller_product['sub_ids'] ) && is_array( $content_seller_product['sub_ids'] ) ) {
					if ( in_array( 'all', $content_seller_product['sub_ids'] ) ) {
						$product_sub_ids = tajer_get_product_sub_ids( (int) $content_seller_product['product'] );
					} else {
						$product_sub_ids = $content_seller_product['sub_ids'];
					}
				}

				foreach ( $product_sub_ids as $product_sub_id ) {

					if ( $can_access ) {
						break;
					}

					if ( tajer_is_free( $product_id, $product_sub_id ) ) {
						if ( Tajer_CanAccess( $product_id, $product_sub_id ) ) {
							$can_access = true;
						}
					} else {
						$user_id = get_current_user_id();
						$item    = Tajer_DB::get_row_by_product_id_product_sub_id_user_id( 'tajer_user_products', $product_id, $product_sub_id, $user_id );
						if ( is_null( $item ) ) {
							$can_access = false;
						} elseif ( $item->status == 'inactive' ) {
							$can_access = false;
							$message    = apply_filters( 'tajer_content_seller_meta_box_inactive_message', $this->restriction_message( __( 'Your subscription to access this content is inactive, for more information please contact the website administrator.', 'tajer' ) ), $content, $item );
						} elseif ( ( date( 'Y-m-d H:i:s' ) > $item->expiration_date ) ) {
							//the product has expired, delete the product
							$is_deleted = Tajer_DB::delete_by_id( 'tajer_user_products', $item->id );
							if ( $is_deleted === false ) {
								$message = apply_filters( 'tajer_content_seller_meta_box_error_deleting_product', $this->restriction_message( __( 'Error contact website admin!', 'tajer' ) ), $is_deleted, $content, $item );
							} else {
								$message = apply_filters( 'tajer_content_seller_meta_box_expiration_message', $this->restriction_message( __( 'Your subscription has been expired!', 'tajer' ) ), $is_deleted, $content, $item );
							}
						} else {
							$can_access = true;
						}
					}
				}
			}
		} else {
			return $restriction_message;
		}
		if ( $can_access ) {
			return $content;
		} else {
			return $restriction_message;
		}
	}

	function content_seller_meta_box() {
		$post_types = get_post_types( array( 'public' => true ) );
		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'content_seller', // $id
				__( 'Content Seller', 'tajer' ), // $title
				array( $this, 'show_products_selector' ), // $callback
				$post_type, // $page
				'normal', // $context
				'high' ); // $priority
		}
	}

	function show_products_selector( $post = 0, $args = array(), $echo = true, $postId = 0 ) {

		if ( $postId ) {
			$post_id = $postId;
		} else {
			$post_id = $post->ID;
		}

		$content_seller          = get_post_meta( $post_id, 'content_seller', true );
		$is_enabled              = ( isset( $content_seller['enabled'] ) && ( $content_seller['enabled'] == 'yes' ) ) ? true : false;
		$content_seller_products = isset( $content_seller['products'] ) ? $content_seller['products'] : false;

		$args               = array( 'post_type' => 'tajer_products', 'numberposts' => - 1 );
		$products_post_type = get_posts( $args );


		$checked = '';
		if ( ( ! $is_enabled ) || empty( $is_enabled ) ) {
			$checked = 'checked';
		}


		$html = '';
		$html .= '<div class="Tajer">';
		$html .= '<div class="tajer_enable_content_seller">';
		$html .= '<label for="content_seller_yes">';
		$html .= '<input type="radio" ' . checked( true, $is_enabled, false ) . ' name="tajer_content_seller[enabled]" id="content_seller_yes" value="yes"> ' . __( 'Enable', 'tajer' ) . ' &nbsp;</label>';
		$html .= '<label for="content_seller_no">';
		$html .= '<input type="radio" ' . checked( false, $is_enabled, false ) . $checked . ' name="tajer_content_seller[enabled]" id="content_seller_no" value="no"> ' . __( 'Disable', 'tajer' ) . '</label>';
		$html .= '<span class="is-active"></span>';
		$html .= '</div>';
		$html .= '<div class="tajer_content_seller">';
		$html .= '<table id="tajer_content_seller_table">';
		$html .= '<tr>';
		$html .= '<th>' . __( 'Product', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'Sub Product(s)', 'tajer' ) . '</th>';
		$html .= '<th>' . __( 'Delete', 'tajer' ) . '</th>';
		$html .= '</tr>';
		if ( $content_seller_products && ! empty( $content_seller_products ) && is_array( $content_seller_products ) ) {
			foreach ( $content_seller_products as $id => $content_seller_product ) {
				$html .= '<tr class="tajer_repeatable_row" data-index="' . $id . '">';
				$html .= '<td>';
				$html .= '<select class="tajer_content_seller_product" name="tajer_content_seller[products][' . $id . '][product]">';

				foreach ( $products_post_type as $product_post ) {
					$selected = false;
					if ( strval( $product_post->ID ) == $content_seller_product['product'] ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . $product_post->ID . '">' . $product_post->post_title . '</option>';
				}

				$html .= '</select>';
				$html .= '</td>';
				$html .= '<td>';
				$html .= '<select class="tajer_content_seller_products_ids" name="tajer_content_seller[products][' . $id . '][sub_ids][]" multiple>';
				$product_sub_ids        = tajer_get_product_sub_ids_with_names( (int) $content_seller_product['product'] );
				$product_sub_ids['all'] = __( "All", 'tajer' );

				foreach ( $product_sub_ids as $product_sub_id_id => $product_sub_id_name ) {
					$selected = false;
					if ( isset( $content_seller_product['sub_ids'] ) && is_array( $content_seller_product['sub_ids'] ) ) {
						if ( in_array( strval( $product_sub_id_id ), $content_seller_product['sub_ids'] ) ) {
							$selected = true;
						}
					}

					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . $product_sub_id_id . '">' . $product_sub_id_name . '</option>';
				}

				$html .= '</select>';
				$html .= '</td>';

				$html .= '<td><a href="#" class="tajer_content_seller_remove_row" style="background: url(' . Tajer_URL . 'images/xit.gif) no-repeat;">×</a></td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr class="tajer_repeatable_row" data-index="1">';
			$html .= '<td>';
			$html .= '<select class="tajer_content_seller_product" name="tajer_content_seller[products][1][product]">';

			foreach ( $products_post_type as $product_post ) {
				$html .= '<option value="' . $product_post->ID . '">' . $product_post->post_title . '</option>';
			}

			$html .= '</select>';
			$html .= '</td>';
			$html .= '<td>';
			$html .= '<select class="tajer_content_seller_products_ids" name="tajer_content_seller[products][1][sub_ids][]" multiple>';

			$first_product          = reset( $products_post_type );
			$pid                    = is_object( $first_product ) ? $first_product->ID : false;
			$product_sub_ids        = tajer_get_product_sub_ids_with_names( $pid );
			$product_sub_ids['all'] = __( "All", 'tajer' );

			foreach ( $product_sub_ids as $product_sub_id_id => $product_sub_id_name ) {

				$html .= '<option value="' . $product_sub_id_id . '">' . $product_sub_id_name . '</option>';
			}
			$html .= '</select>';
			$html .= '</td>';


			$html .= '<td><a href="#" class="tajer_content_seller_remove_row" style="background: url(' . Tajer_URL . 'images/xit.gif) no-repeat;">×</a></td>';
			$html .= '</tr>';
		}
		$html .= '</table>';
		$html .= wp_nonce_field( 'tajer_content_seller_nonce', 'tajer_content_seller_nonce_field', true, false );
		$html .= '<br>';
		$html .= '<a class="tajer-add-content_seller-product button-secondary">' . __( 'Add Product', 'tajer' ) . '</a>';

		$html .= '<label id="tajer_content_seller_restriction_message_label" for="tajer_content_seller_restriction_message">Restriction Message';
		$html .= '<textarea name="tajer_content_seller[restriction_message]" id="tajer_content_seller_restriction_message">' . ( isset( $content_seller['restriction_message'] ) ? $content_seller['restriction_message'] : '' ) . '</textarea>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '<p>' . __( 'Restrict the content access for only the customers of the selected products.', 'tajer' ) . '</p>';
		$html .= '</div>';

		echo $html;
	}

	function get_product_sub_ids() {
		if ( ! ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'tajer_content_seller_nonce' ) ) ) {
			wp_die( 'Security Check!' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_content_seller_get_product_sub_ids_capability', 'manage_options' ) ) ) {
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

		$response = apply_filters( 'tajer_content_seller_get_product_sub_ids_response', $response );

		tajer_response( $response );
	}

	public function restriction_message( $RestrictionMessage ) {
		$color               = tajer_get_option( 'color', 'tajer_general_settings', 'teal' );
		$restriction_message = '<div class="Tajer">';
		$restriction_message .= '<div class="ui ' . $color . ' segment tajer-clearfix">';
		$restriction_message .= '<h2 class="ui header left floated">';
		$restriction_message .= '<i class="lock icon"></i>';
		$restriction_message .= '<div class="content">';
		$restriction_message .= esc_html( $RestrictionMessage );
		$restriction_message .= '</div>';
		$restriction_message .= '</h2>';
		$restriction_message .= '</div>';
		$restriction_message .= '</div>';

		return $restriction_message;
	}
}
