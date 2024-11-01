<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<div id="price-options-dialog" title="Price Options">
	<form class="price-options-form">
		<fieldset>
			<table>
				<tr>
					<td><label
							for="tajer-price-expiration-date"><?php esc_html_e( 'Price Expiration', 'tajer' ); ?></label>
					</td>
					<td><input type="text" id="tajer-price-expiration-date"
					           name="tajer-price-expiration-date"/>
						<br/><span
							class="description"><?php esc_html_e( 'In days, leave it empty for lifetime option.', 'tajer' ); ?></span>
					</td>
				</tr>
				<tr>
					<td><label
							for="tajer-file-download-limit-price-option"><?php esc_html_e( 'File Download Limit', 'tajer' ); ?></label>
					</td>
					<td><input type="text" id="tajer-file-download-limit-price-option"
					           name="tajer-file-download-limit-price-option"/>
						<br/><span
							class="description"><?php esc_html_e( 'The maximum number of times files can be downloaded, leave it empty for unlimited number of times.', 'tajer' ); ?></span>
					</td>
				</tr>
				<tr>
					<td><label for="tajer-for-free-capabilities-price-option">
							<input type="radio" name="tajer-capabilities-price-option"
							       id="tajer-for-free-capabilities-price-option"
							       value="free"/><?php esc_html_e( 'Free', 'tajer' ); ?> &nbsp;</label>

						<label for="tajer-for-sale-capabilities-price-option">
							<input type="radio" name="tajer-capabilities-price-option" checked
							       id="tajer-for-sale-capabilities-price-option"
							       value="sale"/><?php esc_html_e( 'Sale', 'tajer' ); ?></label>
					<td>
					<td></td>
				</tr>
				<tr id="tajer-roles-fieldset">

					<td><label for="tajer-role-price-option"><?php esc_html_e( 'Roles:', 'tajer' ); ?></label></td>
					<td><select name="tajer-role-price-option[]" id="tajer-role-price-option" multiple>
							<?php
							$roles = tajer_get_user_roles();
							foreach ( $roles as $role_name => $role ) {
								echo '<option value="' . $role_name . '">' . $role . '</option>';
							}
							?>
						</select></td>

				</tr>
				<?php do_action( 'tajer_pricing_options_dialog' ); ?>
				<?php wp_nonce_field( 'price_options_nonce', 'price_options_nonce_field' ); ?>
				<!-- Allow form submission with keyboard without duplicating the dialog button -->
			</table>
			<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
		</fieldset>
		<input type="hidden" name="tajer_price_options_for" value=""/>
	</form>
</div>
