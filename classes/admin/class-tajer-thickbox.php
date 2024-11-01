<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Thickbox {

	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_footer', array( $this, 'tajer_thickbox_html' ) );
		add_action( 'admin_head', array( $this, 'custom_css' ) );
		add_action( 'wp_enqueue_media', array( $this, 'tajer_include_media_button_js_file' ) );
		add_action( 'media_buttons', array( $this, 'tajer_add_media_button' ), 11 );
		add_action( 'wp_ajax_tajer_thickbox_get_sub_id', array( $this, 'thickbox_get_sub_id' ) );

	}

	function custom_css() {
		$images_url = Tajer_URL . 'images/';
		$icon_url   = $images_url . 'tajer-icon.png';
		?>

		<style type="text/css">
			#tajer-media-button {
				background: url(<?php echo $icon_url; ?>) 0 2px no-repeat;
				/*background-size: 16px 16px;*/
			}
		</style>

		<?php
	}

	function thickbox_get_sub_id() {
		if ( ! wp_verify_nonce( $_REQUEST['tajerThickBoxNonce'], 'tajer_thickbox_nonce' ) ) {
			wp_die( 'Security check' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_thickbox_get_sub_id_capability', 'edit_posts' ) ) ) {
			wp_die( 'Security check' );
		}

		do_action( 'tajer_thickbox_get_sub_id' );

		$html       = '';
		$product_id = (int) $_REQUEST['tajerProductId'];
		$prices     = get_post_meta( $product_id, 'tajer_product_prices', true );
		foreach ( $prices as $id => $details ) {
			$html .= '<option value="' . $id . '">' . $details['name'] . '</option>';
		}
		$response = array(
			'html'   => $html,
			'status' => true
		);

		tajer_response( apply_filters( 'tajer_thickbox_get_sub_id_response', $response ) );
	}

//print the button
	function tajer_add_media_button() {
		global $pagenow;

		if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
			$img  = '<span class="wp-media-buttons-icon" id="tajer-media-button"></span>';
			$html = "<a href='#TB_inline?width=400&inlineId=popup_container'
    class='thickbox button' title='" . __( 'Product Link Generator', 'tajer' ) . "'>" . $img . __( 'Insert Product', 'tajer' ) . "</a>";
			$html = apply_filters( 'tajer_thickbox_media_button_html', $html );
			echo $html;
		}
	}

//fire the js script
	function tajer_include_media_button_js_file() {
		do_action( 'tajer_thickbox_include_media_button_scripts' );
		wp_enqueue_style( 'tajer_thickbox_css', Tajer_URL . 'css/admin/thickbox.css' );
		wp_enqueue_script( 'tajer_thickbox', Tajer_URL . 'js/admin/thickbox.js', array( 'jquery' ) );
	}

	function tajer_thickbox_html() {

		$products = tajer_get_products();
		unset( $products['all'] );

		$html = '<div id="popup_container" style="display:none;">';
		$html .= '<h3>' . __( 'Use the form below to generate a purchase link for a product', 'tajer' ) . '</h3>';
		$html .= '<p>' . __( 'Link For', 'tajer' ) . '</p>';
		$html .= '<select name="tajer_thickbox_link_for">';
		$html .= '<option value="buy_now">' . __( 'Buy Now', 'tajer' ) . '</option>';
		$html .= '<option value="add_to_cart">' . __( 'Add To Cart', 'tajer' ) . '</option>';
		$html .= '</select><br />';

		$html .= '<p>' . __( 'Open link in a new window/tab', 'tajer' ) . '</p>';
		$html .= '<select name="tajer_thickbox_link_target">';
		$html .= '<option value="_blank">' . __( 'Yes', 'tajer' ) . '</option>';
		$html .= '<option value="_self">' . __( 'No', 'tajer' ) . '</option>';
		$html .= '</select><br />';

		$html .= '<p>' . __( 'Style', 'tajer' ) . '</p>';
		$html .= '<select name="tajer_thickbox_link_style">';
		$html .= '<option value="button">' . __( 'Button', 'tajer' ) . '</option>';
		$html .= '<option value="link">' . __( 'Link', 'tajer' ) . '</option>';
		$html .= '</select><br />';

		$html .= '<p>' . __( 'Color', 'tajer' ) . '</p>';
		$html .= '<select name="tajer_thickbox_link_color">';
		$html .= '<option value="">' . __( 'Standard Gray', 'tajer' ) . '</option>';
		$html .= '<option value="black">' . __( 'Black', 'tajer' ) . '</option>';
		$html .= '<option value="brown">' . __( 'Brown', 'tajer' ) . '</option>';
		$html .= '<option value="pink">' . __( 'Pink', 'tajer' ) . '</option>';
		$html .= '<option value="purple">' . __( 'Purple', 'tajer' ) . '</option>';
		$html .= '<option value="violet">' . __( 'Violet', 'tajer' ) . '</option>';
		$html .= '<option value="blue">' . __( 'Blue', 'tajer' ) . '</option>';
		$html .= '<option value="teal">' . __( 'Teal', 'tajer' ) . '</option>';
		$html .= '<option value="green">' . __( 'Green', 'tajer' ) . '</option>';
		$html .= '<option value="olive">' . __( 'Olive', 'tajer' ) . '</option>';
		$html .= '<option value="yellow">' . __( 'Yellow', 'tajer' ) . '</option>';
		$html .= '<option value="orange">' . __( 'Orange', 'tajer' ) . '</option>';
		$html .= '<option value="red">' . __( 'Red', 'tajer' ) . '</option>';
		$html .= '</select><br />';

		$html .= '<p>' . __( 'Text', 'tajer' ) . '</p>';
		$html .= '<input type="text" name="tajer_link_text">';


		$html .= '<p>' . __( 'Select the product', 'tajer' ) . '</p>';
		$html .= '<select name="tajer_thickbox_product_id">';
		$html .= '<option value="select">' . __( 'Select', 'tajer' ) . '</option>';

		foreach ( $products as $id => $product ) {
			$html .= '<option value="' . $id . '">' . $product . '</option>';
		}
		$html .= '</select><img class="tajer-thickbox-loading" style="display:none;"
							     src="' . Tajer_URL . 'images/circular-loader.GIF"><br />';

		$html .= '<div class="tajer_sub_product" style="display: none;">';
		$html .= '<p>' . __( 'Select the sub product', 'tajer' ) . '</p>';
		$html .= '<select name="tajer_thickbox_product_sub_id">';
		$html .= '</select>';
		$html .= '</div><br />';

		$html .= '<div class="tajer_link_area" style="display: none;">';
		$html .= '<p>' . __( 'Here is your link', 'tajer' ) . '</p>';
		$html .= '<code class="tajer_purchase_link_area"></code>';
		$html .= '</div><br />';
		$html .= '<input type="button" id="tajer_insert_purchase_link" class="button-primary" value="Insert It!" style="margin-right: 10px;">';
		$html .= '<a id="tajer_close_thickbox" class="button-secondary" title="Cancel">Cancel</a>';
		$html .= wp_nonce_field( 'tajer_thickbox_nonce', 'tajer_thickbox_nonce_field', true, false );
		$html .= '</div>';
		$html = apply_filters( 'tajer_thickbox_html', $html );
		echo $html;
	}
}
