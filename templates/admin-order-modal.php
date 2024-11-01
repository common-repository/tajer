<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<!-- Modal -->
<div id="tajer-modal" class="ui small suimodal">
	<i class="close icon"></i>

	<div class="header tajer-modal-label">
		<?php esc_html_e( 'Add New Order', 'tajer' ); ?>
	</div>
	<div class="content">
		<form id="tajer-modal-form" class="ui form">
			<img class="gears-loading" style="display: none"
			     src="<?php echo Tajer_URL . 'images/gears.gif'; ?>">
			<input type="hidden" name="tajer_modal_submitting_type" value="add">
			<input type="hidden" name="tajer_add_title"
			       value="<?php esc_attr_e( 'Add New Order', 'tajer' ); ?>">
			<input type="hidden" name="tajer_add_button_label" value="<?php _e( 'Add', 'tajer' ); ?>">
			<input type="hidden" name="tajer_edit_title"
			       value="<?php esc_attr_e( 'Edit Order', 'tajer' ); ?>">
			<input type="hidden" name="tajer_edit_button_label" value="<?php _e( 'Update', 'tajer' ); ?>">
			<input type="hidden" name="tajer-modify-order-nonce" value="">
			<input type="hidden" name="edit-item" value="">
			<?php wp_nonce_field( 'tajer_orders_modal_form_nonce', 'tajer_orders_modal_form_nonce_field' ); ?>
			<div id="form-dynamic-area">
			</div>
		</form>
	</div>
	<div class="actions">
		<div class="tajer_orders_message"></div>
		<div class="ui cancel button"><?php _e( 'Cancel', 'tajer' ); ?></div>
		<div class="ui green button tajer-modal-buttons"><?php _e( 'Add', 'tajer' ); ?></div>
	</div>
</div>