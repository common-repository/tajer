<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<div id="select-file-dialog" title="Select File">

	<form class="tajer-select-file-form">
		<fieldset>
			<label for="tajer-select-file-dropdown"><?php esc_html_e( 'File:', 'tajer' ); ?></label>
			<select name="tajer-select-file-dropdown" id="tajer-select-file-dropdown">
				<?php
				$files = tajer_get_files();
				foreach ( $files as $file ) {
					echo '<option value="' . $file . '">' . basename( $file ) . '</option>';
				}
				?>
			</select>
			<?php wp_nonce_field( 'select_nonce', 'select_nonce_field' ); ?>
			<!-- Allow form submission with keyboard without duplicating the dialog button -->
			<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
		</fieldset>
	</form>
</div>
