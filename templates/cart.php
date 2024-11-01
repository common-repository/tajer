<div class="Tajer">
	<?php $tajer_render_cart = Tajer()->cart_page; ?>
	<?php if ( $tajer_render_cart->is_empty_cart ) {
		return;
	} ?>

	<?php if ( ( $tajer_render_cart->pagination_links ) && ( ! empty( $tajer_render_cart->pagination_links ) ) ) {
		echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $tajer_render_cart->pagination_links . '</div></div>';
	} ?>

	<?php $color = tajer_get_option( 'color', 'tajer_general_settings', 'teal' ); ?>

	<form id="tajer_cart_form">
		<table class="ui <?php echo $color; ?> basic table segment tajer-cart-table">
			<thead>
			<tr>
				<th class="eleven wide center aligned"><?php _e( 'Product Details', 'tajer' ); ?></th>
				<th class="five wide center aligned"><?php _e( 'Order Details', 'tajer' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $tajer_render_cart->cart_items as $id => $item ) { ?>
				<tr>
					<td><a href="<?php echo get_permalink( $item['product_id'] ); ?>"
					       class="tajer-product-details-item"><?php echo get_the_post_thumbnail( $item['product_id'], 'thumbnail', array( 'class' => 'ui tiny left floated image' ) ); ?></a>

						<div id="tajer-product-details-container">
							<a href="<?php echo get_permalink( $item['product_id'] ); ?>"
							   class="tajer-product-details-item"><?php echo get_the_title( $item['product_id'] ); ?></a>
							<span class="tajer-price-name">
								<?php echo $item['name']; ?>
							</span>

							<?php do_action( 'tajer_cart_product_details_container', $tajer_render_cart, $id, $item ); ?>
						</div>

					</td>
					<td>
						<?php if ( ! $tajer_render_cart->render_cart_for_one_item ) { ?>
							<a href="#" class="tajer_remove_from_cart"
							   data-nonce="<?php echo wp_create_nonce( 'tajer_cart' ); ?>"
							   data-cart-id="<?php echo $id; ?>" data-id="<?php echo $item['product_id']; ?>"
							   data-sub_id="<?php echo $item['product_sub_id']; ?>"><i class="close icon link icon"></i></a>
						<?php } ?>

						<span><?php echo __( 'Price: ', 'tajer' ) . tajer_number_to_currency( $item['price'], true ) . $item['tax_text']; ?></span><br>
						<?php if ( $item['quantity'] != 0 ) { ?>
							<span><?php _e( 'Quantity: ', 'tajer' ); ?></span>
							<div class="ui input">
								<input type="text" name="tajer_quantity"
								       data-nonce="<?php echo wp_create_nonce( 'tajer_cart' ); ?>"
								       data-cart-id="<?php echo $id; ?>"
								       class="tajer_quantity_field"
								       size="1"
								       value="<?php echo $item['quantity']; ?>"/>
							</div>

							<br>
						<?php } ?>

					</td>
				</tr>
			<?php } ?>
			</tbody>


			<tfoot>
			<tr>

				<th><?php if ( ! $tajer_render_cart->render_cart_for_one_item ) { ?>
						<button type="button" data-nonce="<?php echo wp_create_nonce( 'tajer_cart' ); ?>"
						        class="ui <?php echo $color; ?> basic button tajer_empty_cart"><?php _e( 'Empty Cart', 'tajer' ); ?></button>
					<?php } ?></th>
				<th class="right aligned">

					<p><strong><?php _e( 'Total: ', 'tajer' ); ?><span
								class="tajer_total_price"><?php echo tajer_number_to_currency( $tajer_render_cart->final_price, true, 'span' ); ?></span>
							<i class="notched circle loading icon tajer-calculator-loader" style="display: none;"></i>
						</strong></p>

				</th>

			</tr>
			</tfoot>


		</table>
	</form>
	<?php do_action( 'tajer_before_checkout_form', $tajer_render_cart ); ?>
	<?php
	$errors             = tajer_purchase_form_errors();
	$error_form_classes = empty( $errors ) ? 'ui form tajer-purchase-form-errors-area' : 'ui form error tajer-purchase-form-errors-area';
	?>
	<div class="<?php echo $error_form_classes; ?>">
		<div class="ui error message">
			<div class="header"><?php _e( 'Could you check something!', 'tajer' ); ?></div>
			<ul class="list" id="tajer_purchase_form_errors">
				<?php
				if ( ! empty( $errors ) ) {
					foreach ( $errors as $error ) {
						echo '<li>' . $error . '</li>';
					}
				}
				?>
			</ul>
		</div>
	</div>

	<!-- Checkout Form -->
	<form id="tajer_checkout_form" class="ui form" method="POST"
	      action="<?php echo tajer_get_purchase_form_action(); ?>">
		<?php do_action( 'tajer_start_checkout_form', $tajer_render_cart ); ?>

		<!-- Coupon -->
		<h4 class="ui dividing header"><?php esc_html_e( 'Discount', 'tajer' ); ?></h4>

		<div class="field">
			<div id="tajer_coupon">
				<div id="tajer_coupon_body">
					<div class="ui fluid action input">
						<input type="text" name="tajer_input_discount" placeholder="<?php esc_html_e(
							'Enter a coupon code if you have one.', 'tajer' ); ?>" id="tajer_input_discount">

						<button data-nonce="<?php echo wp_create_nonce( 'tajer_cart' ); ?>" id="tajer_validate_coupon"
						        class="ui button"><?php esc_html_e(
								'Apply', 'tajer' ); ?></button>
					</div>
					<span id="tajer-discount-error-wrap"></span>
				</div>
			</div>
		</div>

		<!-- Sale Details -->
		<input type="hidden" name="tajer_sale_details" value="true">
		<input type="hidden" name="tajer_action"
		       value="<?php echo isset( $_REQUEST['tajer_action'] ) ? $_REQUEST['tajer_action'] : ''; ?>">
		<input type="hidden" name="id" value="<?php echo isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : ''; ?>">
		<input type="hidden" name="product_id"
		       value="<?php echo isset( $_REQUEST['product_id'] ) ? $_REQUEST['product_id'] : ''; ?>">
		<input type="hidden" name="product_sub_id"
		       value="<?php echo isset( $_REQUEST['product_sub_id'] ) ? $_REQUEST['product_sub_id'] : ''; ?>">
		<input type="hidden" name="action_id"
		       value="<?php echo isset( $_REQUEST['action_id'] ) ? $_REQUEST['action_id'] : ''; ?>">

		<!-- Checkout -->
		<div id="tajer_payment_method_selection">
			<div class="ui inverted dimmer">
				<div class="ui indeterminate loader"></div>
			</div>


			<?php $payment_obj = tajer_render_frontend_payment_methods_helper(); ?>
			<?php if ( count( $payment_obj->enabled_payment_gateways ) > 1 ) { ?>
				<h4 class="ui dividing header"><?php esc_html_e( 'Select Payment Method', 'tajer' ); ?></h4>

				<div class="inline fields">
					<?php foreach ( $payment_obj->get_frontend_payment_gateways as $payment_gateway_id => $payment_gateway_label ) { ?>
						<?php if ( ! in_array( $payment_gateway_id, $payment_obj->enabled_payment_gateways ) ) { ?>
							<?php continue; ?>
						<?php } ?>
						<div class="inline field">
							<div class="ui slider checkbox">
								<input
									type="radio" <?php checked( $payment_obj->get_default_payment_gateway, $payment_gateway_id ); ?>
									name="payment-mode" value="<?php echo $payment_gateway_id; ?>" tabindex="0"
									class="hidden">
								<label><?php echo $payment_gateway_label; ?></label>
							</div>
						</div>
					<?php } ?>
				</div>
			<?php } else { ?>
				<?php if ( is_array( $payment_obj->enabled_payment_gateways ) ) { ?>
					<input type="hidden" name="payment-mode"
					       value="<?php echo key( $payment_obj->enabled_payment_gateways ); ?>">
				<?php } ?>
			<?php } ?>

			<div id="tajer_purchase_form_details">
			</div>
		</div>
		<?php wp_nonce_field( 'tajer_checkout_nonce', 'tajer_checkout_nonce_field' ); ?>
		<?php do_action( 'tajer_end_checkout_form', $tajer_render_cart ); ?>
	</form>
	<div id="tajer_extend_cart_page"><?php do_action( 'tajer_extend_cart_page', $tajer_render_cart ); ?></div>
</div>
