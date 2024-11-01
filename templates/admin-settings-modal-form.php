<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<div id="tajer-modal" class="ui small suimodal">
	<i class="close icon"></i>

	<div class="header tajer-modal-label">
		<?php esc_html_e( 'Import Settings', 'tajer' ); ?>
	</div>
	<div class="content" id="drag-and-drop-zone">
		<div class="ui form">
			<div class="fields tajer-field">
				<div class="eight wide field">
					<label><?php esc_html_e( 'Import?', 'tajer' ); ?></label>
				</div>
				<div class="eight wide field">
					<div class="grouped fields">
						<div class="field">
							<div class="ui radio checkbox">
								<input type="radio" checked id="tajer-import-product" value="product"
								       name="tajer-importing-type">
								<label><?php esc_html_e( 'Import Product', 'tajer' ); ?></label>
							</div>
						</div>
						<div class="field">
							<div class="ui radio checkbox">
								<input type="radio" id="tajer-import-settings" value="settings"
								       name="tajer-importing-type">
								<label><?php esc_html_e( 'Import Settings', 'tajer' ); ?></label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="fields tajer-field" id="tajer-modal-products-section">

				<div class="eight wide field"
				     data-content="<?php esc_attr_e( 'Select the product that you want to import the product settings to it. Product title, product content, product excerpt, and all the product settings will be imported except the files.', 'tajer' ); ?>">
					<label for="tajer_product_id"><?php esc_html_e( 'Select Product', 'tajer' ); ?></label>
				</div>
				<div class="eight wide field">
					<select class="ui dropdown" name="tajer_product_id" id="tajer_product_id">
						<?php
						$products = tajer_get_products();
						unset( $products['all'] );
						foreach ( $products as $id => $product ) {
							echo '<option value="' . $id . '">' . $product . '</option>';
						}
						?>
					</select>
				</div>
			</div>
			<p class="drag-area-text"><?php esc_html_e( 'Drag your settings file here to upload', 'tajer' ); ?></p>

			<p class="drag-area-text"><?php esc_html_e( 'or', 'tajer' ); ?></p>
			<button id="tajer-filey-upload-button" class="tajer-filey-upload-button-class">
				<input id="fileupload" class="custom-file-input" type="file" name="tajer_settings_file"
				       data-url="admin-ajax.php?action=tajer_settings_file" multiple>
				<span><?php esc_html_e( 'Select File', 'tajer' ); ?></span>
			</button>
			<?php wp_nonce_field( 'upload_nonce', 'upload_nonce_field' ) ?>
			<div id="progress">
				<div class="bar" style="width: 0%;"></div>
			</div>
		</div>
	</div>
	<div class="actions">
		<div class="tajer_settings_modal_message"></div>
		<div class="ui cancel button"><?php esc_html_e( 'Close', 'tajer' ); ?></div>
	</div>
</div>