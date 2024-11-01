<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class Tajer_User_Products {

	private static $instance;
	private $allowed_rows = 20;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		$this->allowed_rows = apply_filters( 'tajer_admin_user_products_allowed_rows', get_option( 'tajer_user_products_items_per_page', 20 ) );
		add_action( 'admin_menu', array( $this, 'tajer_pages' ) );
//		add_action( 'tajer_admin_user_products_render_items_per_page_form', array(
//			$this,
//			'tajer_admin_user_products_render_items_per_page_form'
//		) );
//		add_action( 'tajer_admin_user_products_nav_render', array( $this, 'tajer_admin_user_products_nav_render' ) );
		add_action( 'tajer_admin_user_products_items_area', array( $this, 'tajer_admin_user_products_items_area' ) );
		add_action( 'wp_ajax_tajer_modal_form_parser', array( $this, 'tajer_modal_form_parser' ) );
		add_action( 'wp_ajax_tajer_add_user_product', array( $this, 'tajer_add_user_product' ) );
		add_action( 'wp_ajax_tajer_delete_user_product', array( $this, 'tajer_delete_user_product' ) );
		add_action( 'wp_ajax_tajer_get_this_page', array( $this, 'tajer_get_this_page' ) );
		add_action( 'wp_ajax_tajer_set_items_per_page', array( $this, 'set_items_per_page' ) );
	}

//	function tajer_admin_user_products_render_items_per_page_form() {
//		echo $this->render_items_per_page_form();
//	}

//	function tajer_admin_user_products_nav_render() {
//		echo $this->nav_render();
//	}

	function tajer_admin_user_products_items_area() {
		//get user items as object
		$items = Tajer_DB::get_items( 'tajer_user_products', $this->allowed_rows, 'buying_date', 'DESC' );
		echo $this->rows_table( '', $items );
	}

	function tajer_pages() {
		$tajer_user_products_page_hook_suffix = add_submenu_page( 'edit.php?post_type=tajer_products', 'User Products', 'User Products', apply_filters( 'tajer_user_products_admin_menu_capability', 'manage_options' ), 'tajer_user_products', array(
			$this,
			'tajer_user_products_page'
		) );

		add_action( 'admin_print_scripts-' . $tajer_user_products_page_hook_suffix, array(
			$this,
			'tajer_admin_scripts'
		) );
	}

	function tajer_get_this_page() {
		if ( ! ( isset( $_POST['tajer_user_products_pagination_nonce_field'] ) && wp_verify_nonce( $_POST['tajer_user_products_pagination_nonce_field'], 'tajer_user_products_pagination_nonce' ) ) ) {
			wp_die( 'Security Check' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_user_products_get_this_page_capability', 'manage_options' ) ) ) {
			wp_die( 'Security Check' );
		}

		do_action( 'tajer_admin_user_products_get_this_page' );

		$page = (int) $_POST['page'];

		$pagination = new Tajer_Pagination( $page, $this->allowed_rows, Tajer_DB::count_items( 'tajer_user_products' ) );
		$items      = Tajer_DB::get_items_with_offset( 'tajer_user_products', $pagination->offset(), $pagination->per_page, 'buying_date', 'DESC' );

		$status = 'true';
		if ( empty( $items ) || is_null( $items ) ) {
			$status = 'false';
		}

		$html = $this->rows_table( '', $items );

		$response = array(
			'html'           => $html,
			'consoleMessage' => 'Can\'t get the result from the database please contact the plugin author!',
			'status'         => $status
		);

		$response = apply_filters( 'tajer_admin_user_products_get_this_page_response', $response );

		tajer_response( $response );
	}

	function tajer_admin_scripts() {

		do_action( 'tajer_admin_user_products_scripts' );

		wp_enqueue_style( 'tajer-semantic-ui', Tajer_URL . 'lib/semantic-ui/tajer-semantic-ui.css' );

//		wp_enqueue_style( 'tajer-bootstrap', Tajer_URL . 'lib/bootstrap/css/bootstrap.min.css' );
//		wp_enqueue_style( 'tajer-bootstrap-theme', Tajer_URL . 'lib/bootstrap/css/bootstrap-theme.min.css', array(
//			'tajer-bootstrap'
//		) );
		wp_enqueue_style( 'chosen-jquery-css', Tajer_URL . 'lib/chosen_v1.2.0/chosen.min.css' );
//		wp_enqueue_style( 'tajer-jquery-ui-css', Tajer_URL . 'lib/jquery-ui/jquery-ui.min.css' );
		wp_enqueue_style( 'tajer-datetimepicker-css', Tajer_URL . 'lib/datetimepicker-master/jquery.datetimepicker.css' );
		wp_enqueue_style( 'tajer-admin-css', Tajer_URL . 'css/admin/tajer-admin.css', array(
			'chosen-jquery-css',
			'tajer-semantic-ui'
		) );
		wp_enqueue_style( 'tajer-user-products', Tajer_URL . 'css/admin/tajer-user-products.css', array(
			'chosen-jquery-css',
			'tajer-semantic-ui'
		) );


		wp_enqueue_script( 'tajer-semantic-ui-js', Tajer_URL . 'lib/semantic-ui/semantic.min.js', array( 'jquery' ) );
//		wp_enqueue_script( 'jquery-ui-datepicker' );
//		wp_enqueue_script( 'tajer-bootstrap-js', Tajer_URL . 'lib/bootstrap/js/bootstrap.min.js', array( 'jquery' ) );
//		wp_enqueue_script( 'tajer-jquery-ui-js', Tajer_URL . 'lib/jquery-ui/jquery-ui.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'tajer-datetimepicker-js', Tajer_URL . 'lib/datetimepicker-master/jquery.datetimepicker.js', array( 'jquery' ) );
		wp_enqueue_script( 'chosen-jquery-js', Tajer_URL . 'lib/chosen_v1.2.0/chosen.jquery.min.js', array( 'jquery' ) );
//		wp_enqueue_script( 'tajer-setting-js', Tajer_URL . 'js/tajer-admin.js', array(
//			'tajer-bootstrap-js',
//			'chosen-jquery-js'
//		) );
		wp_enqueue_script( 'tajer-user-products-js', Tajer_URL . 'js/admin/tajer-user-products.js', array(
			'tajer-semantic-ui-js',
			'tajer-datetimepicker-js',
			'chosen-jquery-js'
		) );
	}

	function tajer_delete_user_product() {

		$nonce = $_REQUEST['tajerModifyUserProductNonce'];
		if ( ! wp_verify_nonce( $nonce, 'tajer-modify-user-product-nonce' ) ) {
			wp_die( 'Security check' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_admin_delete_user_product_capability', 'manage_options' ) ) ) {
			wp_die( 'Security Check' );
		}

		do_action( 'tajer_admin_user_products_delete_user_product' );

		$item_id = (int) $_REQUEST['itemId'];

		// Using where formatting.
		$is_deleted = tajer_delete_user_product( $item_id );

		if ( $is_deleted !== false ) {
			$message = __( 'User Product Deleted', 'tajer' );
			$status  = 'true';
		} else {
			$message = __( 'The User Product Doesn\'t Deleted', 'tajer' );
			$status  = 'false';
		}

		$response = array(
			'message' => $message,
			'status'  => $status
		);
		$response = apply_filters( 'tajer_admin_user_products_delete_user_product_response', $response );
		tajer_response( $response );
	}

	function tajer_add_user_product() {
		if ( ! ( isset( $_POST['tajer_user_products_modal_form_nonce_field'] ) && wp_verify_nonce( $_POST['tajer_user_products_modal_form_nonce_field'], 'tajer_user_products_modal_form_nonce' ) ) ) {
			wp_die( 'Security Check' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_admin_add_user_product_capability', 'manage_options' ) ) ) {
			wp_die( 'Security Check' );
		}

		do_action( 'tajer_admin_user_products_add_user_product' );

		$item_id = (int) ( $_REQUEST['edit-item'] );
		$args    = apply_filters( 'tajer_admin_user_products_add_user_product_args', array(
			'order_id'            => intval( $_REQUEST['order-id'] ),
			'user'                => sanitize_text_field( $_REQUEST['username'] ),
			'item_id'             => $item_id,
			'buying_date'         => sanitize_text_field( $_REQUEST['buying-date'] ),
			'expiration_date'     => sanitize_text_field( $_REQUEST['expiration-date'] ),
			'status'              => sanitize_text_field( $_REQUEST['status'] ),
			'activation_method'   => sanitize_text_field( $_REQUEST['activation_method'] ),
			'number_of_downloads' => sanitize_text_field( $_REQUEST['number-of-downloads'] ),
			'product_sub_id'      => sanitize_text_field( $_REQUEST['product-sub-id'] ),
			'product_id'          => intval( $_REQUEST['product-id'] )
		) );

		if ( $item_id && ! empty( $item_id ) ) {
			$is_updated = Tajer_DB::update_user_product( $args );

			if ( $is_updated !== false ) {
				$message = __( 'User Product Updated Successfully', 'tajer' );
			} else {
				$message = __( 'The User Product Doesn\'t Updated', 'tajer' );
			}
		} else {
			$result = Tajer_DB::insert_user_product_by_email_or_login( $args );

			if ( $result['is_insert'] ) {
				$message = __( 'User Product Added Successfully', 'tajer' );
			} else {
				$message = __( 'The User Product Doesn\'t Added', 'tajer' );
			}
		}

		$response = array(
			'message' => $message
		);

		$response = apply_filters( 'tajer_admin_user_products_add_user_product_response', $response );

		tajer_response( $response );
	}

	function tajer_modal_form_parser() {

		if ( ! ( isset( $_POST['tajer_user_products_modal_form_nonce_field'] ) && wp_verify_nonce( $_POST['tajer_user_products_modal_form_nonce_field'], 'tajer_user_products_modal_form_nonce' ) ) ) {
			wp_die( 'Security Check' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_admin_user_products_modal_form_parser_capability', 'manage_options' ) ) ) {
			wp_die( 'Security Check' );
		}

		do_action( 'tajer_admin_user_products_modal_form_parser' );

		$type = sanitize_text_field( $_REQUEST['tajer_modal_submitting_type'] );

		switch ( $type ) {
			case 'add':
				$content = $this->form();
				break;
			case 'edit':
				$nonce = $_REQUEST['tajer-modify-user-product-nonce'];
				if ( ! wp_verify_nonce( $nonce, 'tajer-modify-user-product-nonce' ) ) {
					wp_die( 'Security check' );
				}
				$item    = intval( $_REQUEST['edit-item'] );
				$content = $this->form( $item );
				break;
			default:
				do_action( 'tajer_admin_user_products_modal_form_' . $type . '_parser' );
				break;
		}

		$response = array(
			'content' => $content
		);
		$response = apply_filters( 'tajer_admin_user_products_modal_form_parser_response', $response );
		tajer_response( $response );
	}

	function tajer_user_products_page() {
		tajer_get_template_part( 'admin-user-products-page' );
	}

	function set_items_per_page() {
		if ( ! ( isset( $_POST['tajer_items_per_page_nonce_field'] ) && wp_verify_nonce( $_POST['tajer_items_per_page_nonce_field'], 'tajer_items_per_page_nonce' ) ) ) {
			wp_die( 'Security Check' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_admin_user_products_set_items_per_page_capability', 'manage_options' ) ) ) {
			wp_die( 'Security Check' );
		}

		do_action( 'tajer_admin_user_products_set_items_per_page' );

		$items = (int) $_POST['items'];

		update_option( 'tajer_user_products_items_per_page', apply_filters( 'tajer_admin_user_products_update_items_per_page', $items ) );

		$response = array(
			'consoleMessage' => 'can\'t set the number of user products items per page please contact the plugin author',
			'status'         => 'true'
		);
		$response = apply_filters( 'tajer_admin_user_products_set_items_per_page_response', $response );
		tajer_response( $response );
	}

//	function render_items_per_page_form() {
//
//		$html = '';
//
//		return $html;
//	}

	function form( $id = null ) {

		if ( ! is_null( $id ) ) {
			$item      = Tajer_DB::get_row_by_id( 'tajer_user_products', $id );
			$user_data = get_user_by( 'id', $item->user_id );
		}

		do_action( 'tajer_admin_user_products_form', $id, $item, $user_data );

		$html = '<div class="two fields">
    <div class="field">
        <label for="username">' . __( 'Username or Email', 'tajer' ) . '</label>

        <input type="text" name="username" id="username" value="' . $user_data->user_login . '"
               placeholder="' . __( 'Enter Username or Email Here', 'tajer' ) . '">

    </div>
    <div class="field">
        <label for="order-id">' . __( 'Order ID', 'tajer' ) . '</label>

        <input type="text" name="order-id" id="order-id"
               value="' . $item->order_id . '" placeholder="' . __( 'Enter Order ID Here', 'tajer' ) . '">
    </div>
</div>
<div class="two fields">
    <div class="field">
        <label for="product-id">' . __( 'Product ID', 'tajer' ) . '</label>

        <input type="text" name="product-id" id="product-id"
               value="' . $item->product_id . '" placeholder="' . __( 'Enter Product ID Here', 'tajer' ) . '">
    </div>
    <div class="field">
        <label for="product-sub-id">' . __( 'Product Sub ID', 'tajer' ) . '</label>

        <input type="text" name="product-sub-id" id="product-sub-id"
               value="' . $item->product_sub_id . '" placeholder="' . __( 'Enter Product Sub ID Here', 'tajer' ) . '">
    </div>
</div>
<div class="two fields">
    <div class="field">
        <label for="buying-date">' . __( 'Buying Date', 'tajer' ) . '</label>
        <input type="text" name="buying-date" id="buying-date"
               value="' . $item->buying_date . '" placeholder="' . __( 'Enter Buying Date Here', 'tajer' ) . '">
    </div>
    <div class="field">
        <label for="expiration-date">' . __( 'Expiration Date', 'tajer' ) . '</label>
        <input type="text" name="expiration-date" id="expiration-date"
               value="' . $item->expiration_date . '" placeholder="' . __( 'Enter Expiration Date Here', 'tajer' ) . '">
    </div>
</div>
<div class="two fields">
    <div class="field">
        <label for="number-of-downloads">' . __( 'Number Of Downloads', 'tajer' ) . '</label>
        <input type="text" name="number-of-downloads" id="number-of-downloads"
               value="' . $item->number_of_downloads . '"
               placeholder="' . __( 'Enter Number Of Downloads Here', 'tajer' ) . '">

    </div>
    <div class="field">
        <label for="status">' . __( 'Status', 'tajer' ) . '</label>
        <select class="ui fluid search dropdown" id="status" name="status">
            <option class="tajer-active" ' . ( isset( $item->status ) ? selected( 'active', $item->status, false ) : '' ) . ' value="active">Active</option>
            <option class="tajer-inactive" ' . ( isset( $item->status ) ? selected( 'inactive', $item->status, false ) : '' ) . ' value="inactive">Inactive</option>
        </select>
    </div>
</div>
<div class="two fields">
    <div class="field">
        <label for="activation_method">' . __( 'Activation Method', 'tajer' ) . '</label>
        <select class="ui fluid search dropdown" id="activation_method" name="activation_method">
            <option ' . ( isset( $item->activation_method ) ? selected( 'buy', $item->activation_method, false ) : '' ) . ' value="buy">Buy</option>
            <option ' . ( isset( $item->activation_method ) ? selected( 'free', $item->activation_method, false ) : '' ) . ' value="free">Free</option>
            <option ' . ( isset( $item->activation_method ) ? selected( 'trial', $item->activation_method, false ) : '' ) . ' value="trial">Trial</option>
        </select>
    </div>
    <div class="field">
    </div>
</div>';

		$html = apply_filters( 'tajer_admin_user_products_form_html', $html, $id, $item, $user_data );

		return $html;
	}

//	public function nav_render() {
//
//
//		$html = '';
//
//		return $html;
//	}


	public function rows_table( $html, $items ) {
		$nonce = wp_create_nonce( 'tajer-modify-user-product-nonce' );
		do_action( 'tajer_admin_user_products_rows_table', $html, $items, $nonce );
		$html .= '<table class="ui teal basic table segment">
						<thead>
						<tr>
							<th class="center aligned">' . __( 'ID', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Order ID', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'User ID', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Product ID', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Product Sub ID', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Buying Date', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Expiration Date', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Number Of Downloads', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Status', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Activation Method', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Edit', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Delete', 'tajer' ) . '</th>
						</tr>
						</thead>';

		$html .= '<tbody>';
		if ( $items && ! empty( $items ) ) {
			foreach ( $items as $item ) {
				$active_class = $item->status == 'active' ? 'tajer-active' : 'tajer-inactive';
				$html .= '<tr>
							<td class="center aligned">' . $item->id . '</td>
							<td class="center aligned">' . $item->order_id . '</td>
							<td class="center aligned">' . $item->user_id . '</td>
							<td class="center aligned">' . $item->product_id . '</td>
							<td class="center aligned">' . $item->product_sub_id . '</td>
							<td class="center aligned">' . $item->buying_date . '</td>
							<td class="center aligned">' . $item->expiration_date . '</td>
							<td class="center aligned">' . $item->number_of_downloads . '</td>
							<td class="center aligned ' . $active_class . '">' . ucfirst( $item->status ) . '</td>
							<td class="center aligned">' . $item->activation_method . '</td>
							<td class="center aligned">
								<button type="button" data-item="' . $item->id . '" data-nonce="' . $nonce . '" class="tajer-edit-user-product mini ui teal button">' . __( 'Edit', 'tajer' ) . '</button>
							</td>
							<td class="center aligned">
								<button type="button" data-item="' . $item->id . '" data-nonce="' . $nonce . '" class="tajer-delete-user-product mini ui red button">' . __( 'Delete', 'tajer' ) . '</button>
							</td>
						</tr>';
			}
		} else {
			$html .= '<tr><td class="center aligned negative" colspan="12">' . __( 'No data available so far.', 'tajer' ) . '</td></tr>';
		}

		$html .= '</tbody>';
		$html .= '</table>';

		$html = apply_filters( 'tajer_admin_user_products_rows_table_html', $html, $items, $nonce );

		return $html;
	}
}
