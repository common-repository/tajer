<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
$allowed_rows = apply_filters( 'tajer_admin_user_products_allowed_rows_in_template', get_option( 'tajer_user_products_items_per_page', 20 ) );
$pagination   = new Tajer_Pagination( 1, $allowed_rows, Tajer_DB::count_items( 'tajer_user_products' ) ); ?>

<!-- Page -->
<div class="Tajer">
	<div class="tajer-container">
		<div class="ui padded grid">


			<div class="row">
				<div class="three wide column">
					<h3 class="ui header"><?php esc_html_e( 'User Products', 'tajer' ); ?></h3>
				</div>
			</div>


			<div class="row">
				<div class="two wide column">
					<button type="button"
					        class="tajer-add-new mini ui blue button"><?php esc_html_e( 'Add New', 'tajer' ); ?></button>
				</div>
			</div>
			<div class="row">
				<div class="eight wide column">
					<!-- Items Per Page Form -->
					<form id="items-per-page-form" class="ui form">
						<div class="inline field">
							<input name="items" id="items-per-page" value="<?php echo $allowed_rows; ?>" type="text"
							       placeholder="20">
							<i id="items-per-page-loading" style="display: none;" class="big setting loading icon"></i>
							<label for="items-per-page"
							       class="user-products-input-labels"><?php esc_html_e( 'Item(s) per page', 'tajer' ); ?></label>
							<!--						<button class="ui basic blue button"-->
							<!--						        id="tajer-set-items-per-page">-->
							<?php //esc_html_e( 'Set', 'tajer' ); ?><!--</button>-->
						</div>
						<?php wp_nonce_field( 'tajer_items_per_page_nonce', 'tajer_items_per_page_nonce_field' ); ?>
					</form>
					<?php do_action( 'tajer_admin_user_products_render_items_per_page_form' ); ?>
				</div>
				<div class="eight wide column">
					<!-- Nav Form -->
					<form id="pagination-form" class="ui form">
						<div class="inline field">
							<i id="get-this-page-loading" style="display: none;" class="big setting loading icon"></i>
							<button type="button" data-nav="prev" title="Previous"
							        class="tajer-nav ui grey basic icon button"><i class="angle left icon"></i></button>
							<label for="tajer-page-number"
							       class="user-products-input-labels"><?php esc_html_e( 'Page', 'tajer' ); ?></label>
							<input id="tajer-page-number" type="text" value="1" name="page" placeholder="5">
							<label for="tajer-page-number"
							       class="user-products-input-labels"><?php esc_html_e( 'of', 'tajer' ); ?><span
									class="number-of-pages"> <?php echo $pagination->total_pages(); ?></span></label>

							<!--							<button type="button" id="get-user-products-page"-->
							<!--							        class="ui basic blue button">--><?php //esc_html_e( 'Go', 'tajer' ); ?>
							<!--							</button>-->
							<button type="button" data-nav="next" title="Next"
							        class="tajer-nav ui grey basic icon button"><i class="angle right icon"></i>
							</button>
						</div>
						<?php wp_nonce_field( 'tajer_user_products_pagination_nonce', 'tajer_user_products_pagination_nonce_field' ); ?>
					</form>
					<?php do_action( 'tajer_admin_user_products_nav_render' ); ?>
				</div>
			</div>
			<div class="row">
				<div id="user-products-table-container" class="sixteen wide column">
					<?php do_action( 'tajer_admin_user_products_items_area' ); ?>
				</div>
			</div>
		</div>

		<!-- Modal -->
		<div id="tajer-modal" class="ui small suimodal">
			<i class="close icon"></i>

			<div class="header tajer-modal-label">
				<?php _e( 'Add New User Product', 'tajer' ); ?>
			</div>
			<div class="content">
				<form id="tajer-modal-form" class="ui form">
					<img class="gears-loading" style="display: none"
					     src="<?php echo Tajer_URL . 'images/gears.gif'; ?>">
					<input type="hidden" name="tajer_modal_submitting_type" value="add">
					<input type="hidden" name="tajer_add_title"
					       value="<?php _e( 'Add New User Product', 'tajer' ); ?>">
					<input type="hidden" name="tajer_add_button_label" value="<?php _e( 'Add', 'tajer' ); ?>">
					<input type="hidden" name="tajer_edit_title"
					       value="<?php _e( 'Edit User Product', 'tajer' ); ?>">
					<input type="hidden" name="tajer_edit_button_label" value="<?php _e( 'Update', 'tajer' ); ?>">
					<input type="hidden" name="tajer-modify-user-product-nonce" value="">
					<input type="hidden" name="edit-item" value="">
					<?php wp_nonce_field( 'tajer_user_products_modal_form_nonce', 'tajer_user_products_modal_form_nonce_field' ); ?>
					<div id="form-dynamic-area">
					</div>
				</form>
			</div>
			<div class="actions">
				<div class="tajer_user_products_message"></div>
				<div class="ui cancel button"><?php _e( 'Cancel', 'tajer' ); ?></div>
				<div class="ui green button tajer-modal-buttons"><?php _e( 'Add', 'tajer' ); ?></div>
			</div>
		</div>
	</div>
</div>