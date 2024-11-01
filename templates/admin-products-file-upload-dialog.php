<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<div id="drag-and-drop-zone" title="Files Uploader">
	<p class="drag-area-text"><?php esc_html_e( 'Drag your files here to upload', 'tajer' ); ?></p>

	<p class="drag-area-text"><?php esc_html_e( 'or', 'tajer' ); ?></p>

	<br>

	<button id="fileupload-example-5" class="myButton">
		<input id="fileupload" class="custom-file-input" type="file" name="files[]"
		       data-url="admin-ajax.php?action=tajer_upload_files" multiple>
		<span><?php esc_html_e( 'Select Files', 'tajer' ); ?></span>
	</button>
	<br> <?php wp_nonce_field( 'upload_nonce', 'upload_nonce_field' ); ?>
	<div id="progress">
		<div class="bar" style="width: 0%;"></div>
	</div>
</div>

