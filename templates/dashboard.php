<div class="Tajer">
	<?php $Tajer_Dashboard_Instance = Tajer()->dashboard; ?>
	<?php $color = tajer_get_option( 'color', 'tajer_general_settings', 'teal' ); ?>
	<?php $secondary_color = tajer_get_option( 'secondary_color', 'tajer_general_settings', 'green' ); ?>
	<form>
		<table class="ui <?php echo $color; ?> basic table segment tajer-dashboard-table">
			<thead>
			<tr>
				<th class="eleven wide center aligned"><?php _e( 'Product Details', 'tajer' ); ?></th>
				<th class="five wide center aligned"><?php _e( 'Product Options', 'tajer' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $Tajer_Dashboard_Instance->user_products as $item ) { ?>
				<tr>
					<td>
						<a href="<?php echo get_permalink( $item->product_id ); ?>"
						   class="tajer-product-details-item"><?php echo
							get_the_post_thumbnail( $item->product_id, 'thumbnail', array( 'class' => 'ui tiny left floated image' ) ); ?></a>

						<div id="tajer-product-details-container">
							<a href="<?php echo get_permalink( $item->product_id ); ?>"
							   class="tajer-product-details-item"><?php echo get_the_title(
									$item->product_id ); ?></a>

							<span class="tajer-price-name">
								<?php echo tajer_get_price_option( $item->product_id, $item->product_sub_id, 'name' ); ?>
							</span>


							<div
								class="ui mini label tajer-product-details-item"><?php echo __( 'Expiration Date: ', 'tajer' ) . $item->expiration_date; ?></div>
							<?php do_action( 'tajer_dashboard_product_details', $item ); ?>
						</div>
					</td>
					<?php if ( property_exists( $item, 'is_expired' ) && ( $item->is_expired == true ) ) { ?>
						<td>
							<a class="ui tiny red label tajer-remove-label">
								<?php _e( 'Expired!', 'tajer' ); ?>
							</a>
						</td>
						<?php continue; ?>
					<?php } ?>
					<?php if ( property_exists( $item, 'is_download_limit_exceeded' ) && ( $item->is_download_limit_exceeded == true ) ) { ?>
						<td>
							<a class="ui tiny red label tajer-remove-label">
								<?php _e( 'You exceeded the download limit of this file!', 'tajer' ); ?>
							</a>
						</td>
						<?php continue; ?>
					<?php } ?>
					<?php if ( property_exists( $item, 'is_inactive' ) && ( $item->is_inactive == true ) ) { ?>
						<td>
							<a class="ui tiny orange label tajer-remove-label">
								<?php _e( 'Your product now is inactive, for more information
				please contact the website administrator.', 'tajer' ); ?>
							</a>
						</td>
						<?php continue; ?>
					<?php } ?>
					<td>
						<?php if ( $item->can_delete == true ) { ?>
							<a href="#" data-id="<?php echo $item->id; ?>"
							   data-nonce="<?php echo wp_create_nonce( 'tajer_remove_frontend_product' ); ?>"
							   class="tajer-remove-frontend-product text-danger"><i
									class="close icon link icon"></i></a>
							<br>
						<?php } ?>

						<?php if ( ( $item->is_downloadable == true ) && ! $item->is_download_disable ) { ?>
							<div
								class="fluid tiny ui labeled icon top right pointing dropdown <?php echo $secondary_color; ?> button">
								<i class="download icon"></i>
								<span class="text"><?php esc_html_e( 'Download', 'tajer' ); ?></span>

								<div class="menu">
									<?php foreach ( $item->download_links as $file_name => $download_link ) { ?>
										<div class="item">
											<a href="<?php echo esc_url( wp_nonce_url( $download_link, 'tajer_download' ) ); ?>"><?php echo $file_name; ?></a>
										</div>
									<?php } ?>
								</div>
							</div>
						<?php } ?>
						<?php if ( $item->is_recurring == true ) { ?>
							<div
								class="fluid tiny ui labeled icon top right pointing dropdown <?php echo $color; ?> basic button">
								<i class="refresh icon"></i>
								<span class="text"><?php esc_html_e( 'Renew', 'tajer' ); ?></span>

								<div class="menu">
									<?php foreach ( $item->recurring as $details ) { ?>
										<div class="item">
											<a href="<?php echo esc_attr( wp_nonce_url( $details['url'], 'tajer_download' ) ); ?>"
											   target="_blank"><?php echo __( 'Renew ', 'tajer' ) . $details['detail']['recurrence_n'] . ' ' . $details['detail']['recurrence_w'] . __( ' for ', 'tajer' ) .
											                              tajer_number_to_currency( $details['detail']['recurring_fee'], true ); ?></a>
										</div>
									<?php } ?>
								</div>
							</div>
						<?php } ?>
						<?php if ( $item->is_upgrade == true ) { ?>
							<div
								class="fluid tiny ui labeled icon top right pointing dropdown <?php echo $color; ?> basic button">
								<i class="arrow circle outline up icon"></i>
								<span class="text"><?php esc_html_e( 'Upgrade', 'tajer' ); ?></span>

								<div class="menu">
									<?php foreach ( $item->upgrade['upgrade_urls'] as $details ) { ?>
										<div class="item">
											<a href="<?php echo esc_attr( wp_nonce_url( $details['url'], 'tajer_download' ) ); ?>"
											   target="_blank"><?php echo
													__( 'Upgrade to ', 'tajer' ) . $item->upgrade['prices'][ $details['detail']['upgrade_to'] ]['name'] . __( ' for ', 'tajer' ) .
													tajer_number_to_currency( $details['detail']['upgrade_fee'], true ); ?></a>
										</div>
									<?php } ?>
								</div>
							</div>

						<?php } ?>
						<?php if ( $item->is_trial == true ) { ?>
							<a href="<?php echo esc_url( wp_nonce_url( $item->buy_now_url, 'tajer_download' ) ); ?>"
							   class="fluid tiny ui labeled icon <?php echo $color; ?> basic button" target="_blank">
								<i class="angle double right icon"></i>
								<?php echo __( 'Buy Now' ); ?></a>

							<a href="<?php echo esc_url( wp_nonce_url( $item->add_to_cart_url, 'tajer_download' ) ); ?>"
							   class="fluid tiny ui labeled icon <?php echo $color; ?> basic button" target="_blank"><i
									class="add to cart icon"></i><?php echo
								__( 'Add To Cart' ); ?></a>
						<?php } ?>

						<?php do_action( 'tajer_dashboard_product_options', $item, $Tajer_Dashboard_Instance ); ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</form>
	<?php if ( ( $Tajer_Dashboard_Instance->pagination_links ) && ( ! empty( $Tajer_Dashboard_Instance->pagination_links ) ) ) {
		echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $Tajer_Dashboard_Instance->pagination_links . '</div></div>';
	} ?>
</div>

