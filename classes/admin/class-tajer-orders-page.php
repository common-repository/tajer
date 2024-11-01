<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class Tajer_Orders_Page {

	private static $instance;
	private $allowed_rows = 20;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		$this->allowed_rows = apply_filters( 'tajer_admin_orders_allowed_rows', get_option( 'tajer_orders_items_per_page', 20 ) );
		add_action( 'admin_menu', array( $this, 'tajer_pages' ) );
		add_action( 'wp_ajax_tajer_edit_order', array( $this, 'tajer_edit_order' ) );
		add_action( 'wp_ajax_tajer_orders_modal_form_parser', array( $this, 'tajer_orders_modal_form_parser' ) );
		add_action( 'wp_ajax_tajer_get_this_order_page', array( $this, 'tajer_get_this_page' ) );
		add_action( 'wp_ajax_tajer_set_items_per_order_page', array( $this, 'set_items_per_page' ) );
	}

	function tajer_pages() {
		$tajer_orders_page_hook_suffix = add_submenu_page( 'edit.php?post_type=tajer_products', 'Orders', 'Orders', apply_filters( 'tajer_orders_admin_menu_capability', 'manage_options' ), 'tajer_orders', array(
			$this,
			'tajer_orders_page'
		) );

		add_action( 'admin_print_scripts-' . $tajer_orders_page_hook_suffix, array(
			$this,
			'tajer_admin_scripts'
		) );
	}

	function tajer_orders_modal_form_parser() {

		if ( ! ( isset( $_POST['tajer_orders_modal_form_nonce_field'] ) && wp_verify_nonce( $_POST['tajer_orders_modal_form_nonce_field'], 'tajer_orders_modal_form_nonce' ) ) ) {
			wp_die( 'Security Check' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_orders_modal_form_parser_capability', 'manage_options' ) ) ) {
			$response = array(
				'content' => apply_filters( 'tajer_orders_modal_form_parser_capability_restriction_response', '' )
			);
			tajer_response( $response );
		}

		$type = sanitize_text_field( $_REQUEST['tajer_modal_submitting_type'] );

		switch ( $type ) {
			case 'add':
				$content = $this->form();
				break;
			case 'edit':
				$nonce = $_REQUEST['tajer-modify-order-nonce'];
				if ( ! wp_verify_nonce( $nonce, 'tajer-modify-order-nonce' ) ) {
					wp_die( 'Security check' );
				}
				$item    = intval( $_REQUEST['edit-item'] );
				$content = $this->form( $item );
				break;
			default:
				$content = apply_filters( 'tajer_orders_modal_form_parse_' . $type );
				break;
		}

		$response = array(
			'content' => apply_filters( 'tajer_orders_modal_form_parser_response', $content, $type )
		);
		tajer_response( $response );
	}

	function tajer_edit_order() {
		if ( ! ( isset( $_POST['tajer_orders_modal_form_nonce_field'] ) && wp_verify_nonce( $_POST['tajer_orders_modal_form_nonce_field'], 'tajer_orders_modal_form_nonce' ) ) ) {
			wp_die( 'Security Check' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_edit_order_capability', 'manage_options' ) ) ) {
			$response = array(
				'message' => apply_filters( 'tajer_admin_edit_order_restriction_response_message', '' )
			);
			tajer_response( $response );
		}

		$order_id         = (int) ( $_REQUEST['edit-item'] );
		$payment_order_id = $_REQUEST['order_id'];
		$status           = wp_strip_all_tags( $_REQUEST['status'] );

		$order      = new Tajer_Order();
		$is_updated = $order->update_order_status( $order_id, $status, $payment_order_id );


		if ( $is_updated !== false ) {
			$message = __( 'Order Updated Successfully!', 'tajer' );
		} else {
			$message = __( 'The Order Doesn\'t Updated!', 'tajer' );
		}

		$response = array(
			'message' => apply_filters( 'tajer_admin_edit_order_response_message', $message, $is_updated )
		);
		tajer_response( $response );
	}

	function form( $id = null ) {

		if ( ! is_null( $id ) ) {
			$item      = Tajer_DB::get_row_by_id( 'tajer_orders', $id );
			$user_data = get_user_by( 'id', $item->user_id );
		}

		$html = '<div class="field">
    <label for="order_id">' . __( 'Gateway Order Id', 'tajer' ) . '</label>
    <input type="text" id="order_id" name="order_id" value="' . $item->gateway_order_id . '"/>
</div>';

		$html .= '<div class="field">
    <label for="status">' . __( 'Status', 'tajer' ) . '</label>
    <select class="ui dropdown" name="status" id="status">
        <option ' . selected( $item->status, 'completed', false ) . ' value="completed">' . __( 'Completed', 'tajer' ) . '</option>
        <option ' . selected( $item->status, 'pending', false ) . ' value="pending">' . __( 'Pending', 'tajer' ) . '</option>
        <option ' . selected( $item->status, 'failed', false ) . ' value="failed">' . __( 'Failed', 'tajer' ) . '</option>
        <option ' . selected( $item->status, 'refund', false ) . ' value="refund">' . __( 'Refund', 'tajer' ) . '</option>
        <option ' . selected( $item->status, 'active', false ) . ' value="active">' . __( 'Active', 'tajer' ) . '</option>
        <option ' . selected( $item->status, 'inactive', false ) . ' value="inactive">' . __( 'Inactive', 'tajer' ) . '</option>
    </select>
</div>';

		return apply_filters( 'tajer_admin_orders_form_html', $html, $id, $item, $user_data );
	}

	function tajer_get_this_page() {
		if ( ! ( isset( $_POST['tajer_orders_pagination_nonce_field'] ) && wp_verify_nonce( $_POST['tajer_orders_pagination_nonce_field'], 'tajer_orders_pagination_nonce' ) ) ) {
			wp_die( 'Security Check' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_admin_orders_get_this_page_capability', 'manage_options' ) ) ) {
			wp_die( 'Security Check' );
		}

		$page = (int) $_POST['page'];

		$pagination = new Tajer_Pagination( $page, $this->allowed_rows, Tajer_DB::count_items( 'tajer_orders' ) );

		$items = Tajer_DB::get_items_with_offset( 'tajer_orders', $pagination->offset(), $pagination->per_page, 'id', 'DESC' );

		$status = 'true';
		if ( empty( $items ) || is_null( $items ) ) {
			$status = 'false';
		}

		$html = $this->rows_table( '', $items );

		$response = array(
			'html'           => apply_filters( 'tajer_admin_orders_get_this_page_html', $html, $status ),
			'consoleMessage' => __( 'can\'t get the result from the database please contact the plugin author.', 'tajer' ),
			'status'         => $status
		);
		tajer_response( $response );
	}

	function tajer_admin_scripts() {
//		wp_enqueue_style( 'tajer-bootstrap', Tajer_URL . 'lib/bootstrap/css/bootstrap.min.css' );
//		wp_enqueue_style( 'tajer-bootstrap-theme', Tajer_URL . 'lib/bootstrap/css/bootstrap-theme.min.css', array(
//			'tajer-bootstrap'
//		) );
		wp_enqueue_style( 'tajer-semantic-ui', Tajer_URL . 'lib/semantic-ui/tajer-semantic-ui.css' );
		wp_enqueue_style( 'chosen-jquery-css', Tajer_URL . 'lib/chosen_v1.2.0/chosen.min.css' );
//		wp_enqueue_style( 'tajer-jquery-ui-css', Tajer_URL . 'lib/jquery-ui/jquery-ui.min.css' );
		wp_enqueue_style( 'tajer-datetimepicker-css', Tajer_URL . 'lib/datetimepicker-master/jquery.datetimepicker.css' );
//		wp_enqueue_style( 'tajer-admin-css', Tajer_URL . 'css/admin/tajer-admin.css', array(
//			'chosen-jquery-css',
//			'tajer-semantic-ui'
//		) );
		wp_enqueue_style( 'tajer-orders', Tajer_URL . 'css/admin/tajer-orders.css', array(
			'chosen-jquery-css',
			'tajer-semantic-ui'
		) );


//		wp_enqueue_script( 'jquery-ui-datepicker' );
//		wp_enqueue_script( 'tajer-bootstrap-js', Tajer_URL . 'lib/bootstrap/js/bootstrap.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'tajer-semantic-ui-js', Tajer_URL . 'lib/semantic-ui/semantic.min.js', array( 'jquery' ) );
//		wp_enqueue_script( 'tajer-jquery-ui-js', Tajer_URL . 'lib/jquery-ui/jquery-ui.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'tajer-datetimepicker-js', Tajer_URL . 'lib/datetimepicker-master/jquery.datetimepicker.js', array( 'jquery' ) );
		wp_enqueue_script( 'chosen-jquery-js', Tajer_URL . 'lib/chosen_v1.2.0/chosen.jquery.min.js', array( 'jquery' ) );
//		wp_enqueue_script( 'tajer-setting-js', Tajer_URL . 'js/tajer-admin.js', array(
//			'tajer-bootstrap-js',
//			'chosen-jquery-js'
//		) );
		wp_enqueue_script( 'tajer-orders-js', Tajer_URL . 'js/admin/tajer-orders.js', array(
			'tajer-semantic-ui-js',
			'tajer-datetimepicker-js',
			'chosen-jquery-js'
		) );
	}

	function tajer_orders_page() {
		$items = Tajer_DB::get_items( 'tajer_orders', $this->allowed_rows, 'id', 'DESC' );

		$html = '<div class="Tajer"><div class="tajer-container"><div class="ui padded grid">
			<div class="row">
			<div class="two wide column">
					<h3 class="ui header">' . __( 'Orders', 'tajer' ) . '</h3>
			</div>
			</div>
			<div class="row">
			<div class="eight wide column">
			' . $this->render_items_per_page_form() . '
			</div>
			<div class="eight wide column">
			' . $this->nav_render() . '
			</div>
			</div>
			<div class="row">
				<div id="orders-table-container" class="sixteen wide column">';
		$html = $this->rows_table( $html, $items );
		$html .= '</div>
			</div>
		</div></div></div>';
		echo apply_filters( 'tajer_admin_orders_page_html', $html, $items );
		$this->modal();
	}

	function modal() {
		tajer_get_template_part( 'admin-order-modal' );
	}

	function set_items_per_page() {
		if ( ! ( isset( $_POST['tajer_items_per_page_nonce_field'] ) && wp_verify_nonce( $_POST['tajer_items_per_page_nonce_field'], 'tajer_items_per_page_nonce' ) ) ) {
			wp_die( 'Security Check' );
		}

		if ( ! current_user_can( apply_filters( 'tajer_admin_orders_set_items_per_page_capability', 'manage_options' ) ) ) {
			wp_die( 'Security Check' );
		}

		do_action( 'tajer_admin_orders_set_items_per_page' );

		$items = (int) $_POST['items'];

		update_option( 'tajer_orders_items_per_page', apply_filters( 'tajer_admin_orders_filter_set_items_per_page', $items ) );

		$response = array(
			'consoleMessage' => 'can\'t set the number of orders items per page please contact the plugin author',
			'status'         => 'true'
		);
		tajer_response( $response );
	}

	function render_items_per_page_form() {

		$html = '<form id="items-per-page-form" class="ui small form">
			<div class="inline field">
				<input type="text" name="items" id="items-per-page" value="' . $this->allowed_rows . '" placeholder="20">
				<i id="items-per-page-loading" style="display: none;" class="big setting loading icon"></i>
				<label for="items-per-page" class="orders-input-label">' . __( 'Item(s) per page', 'tajer' ) . '</label>
			</div>
		' . wp_nonce_field( 'tajer_items_per_page_nonce', 'tajer_items_per_page_nonce_field', true, false ) . '
		</form>';

		return apply_filters( 'tajer_items_per_page_form_html', $html, $this->allowed_rows );
	}

	public function nav_render() {
		$pagination = new Tajer_Pagination( 1, $this->allowed_rows, Tajer_DB::count_items( 'tajer_orders' ) );

		$html = '<form id="pagination-form" class="ui small form">
    <div class="inline field">
        <i id="get-this-page-loading" style="display: none;" class="big setting loading icon"></i>
        <button type="button" data-nav="prev" title="Previous"
                class="tajer-nav ui grey basic icon button"><i class="angle left icon"></i></button>
        <label for="tajer-page-number" class="orders-input-label">' . __( 'Page', 'tajer' ) . '</label>
        <input type="text" id="tajer-page-number" value="1" name="page" placeholder="5">
        <label for="tajer-page-number"
               class="orders-input-label">' . __( 'of', 'tajer' ) . '<span
                class="number-of-pages"> ' . $pagination->total_pages() . '</span></label>
        <button type="button" data-nav="next" title="Next"
                class="tajer-nav ui grey basic icon button"><i class="angle right icon"></i>
        </button>
    </div>
    ' . wp_nonce_field( 'tajer_orders_pagination_nonce', 'tajer_orders_pagination_nonce_field', true, false ) . '
</form>';

		return apply_filters( 'tajer_admin_orders_nav_render_html', $html, $pagination, $this->allowed_rows );
	}

	public function rows_table( $html, $items ) {
		$nonce = wp_create_nonce( 'tajer-modify-order-nonce' );
		$html .= '<table class="ui teal basic table segment">
					<thead>
						<tr>
							<th class="center aligned">' . __( 'ID', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Gateway', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Gateway ID', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'User ID', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'IP', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Total', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Date', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Products', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Status', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Coupon', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Action', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Action ID', 'tajer' ) . '</th>
							<th class="center aligned">' . __( 'Edit', 'tajer' ) . '</th>
						</tr></thead>';

		$html .= '<tbody>';
		if ( $items && ! empty( $items ) ) {
			foreach ( $items as $item ) {
				if ( ($item->status == 'completed') ||($item->status == 'active') ) {
					$class = 'tajer-completed';
				} else {
					if ( $item->status == 'pending' ) {
						$class = ( 'tajer-pending' );
					} else {
						$class = ( 'tajer-failed' );
					}
				}
				$html .= '<tr>
							<td class="center aligned">' . $item->id . '</td>
							<td class="center aligned">' . $item->gateway . '</td>
							<td class="center aligned">' . $item->gateway_order_id . '</td>
							<td class="center aligned">' . $item->user_id . '</td>
							<td class="center aligned">' . $item->ip . '</td>
							<td class="center aligned">' . $item->total . '</td>
							<td class="center aligned">' . $item->date . '</td>
							<td>' . $this->parseSerializeProducts( $item->products ) . '</td>
							<td class="center aligned ' . $class . '">' . ucfirst( $item->status ) . '</td>
							<td class="center aligned">' . $item->coupon . '</td>
							<td class="center aligned">' . $item->action . '</td>
							<td class="center aligned">' . $item->action_id . '</td>
							<td class="center aligned">
								<button type="button" data-item="' . $item->id . '" data-nonce="' . $nonce . '" class="tajer-edit-order mini ui teal button">' . __( 'Edit', 'tajer' ) . '</button>
							</td>
						</tr>';
			}
		} else {
			$html .= '<tr><td class="center aligned negative" colspan="14">' . __( 'No data available so far.', 'tajer' ) . '</td></tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';

		return apply_filters( 'tajer_admin_orders_rows_table_html', $html, $items, $nonce );
	}

	function parseSerializeProducts( $products ) {
		$unserializeProducts = apply_filters( 'tajer_admin_orders_unserialize_products', unserialize( $products ), $products );
		$html                = '<ol class="ui list">';
		foreach ( $unserializeProducts as $unserializeProduct ) {
			$html .= '<li>';
			$html .= __( 'Product ID: ', 'tajer' ) . $unserializeProduct['product_id'] . '<br>';
			$html .= __( 'Product Sub ID: ', 'tajer' ) . $unserializeProduct['product_sub_id'] . '<br>';
			$html .= __( 'Quantity: ', 'tajer' ) . $unserializeProduct['quantity'] . '<br>';
			$html .= '</li>';
		}
		$html .= '</ol>';

		return apply_filters( 'tajer_admin_orders_parse_serialize_products_html', $html );

	}
}
