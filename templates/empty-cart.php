<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<?php $color = tajer_get_option( 'color', 'tajer_general_settings', 'teal' ); ?>
<div class="Tajer">
	<div class="ui <?php echo $color; ?> segment tajer-clearfix">
		<h2 class="ui header left floated">
			<i class="shop icon"></i>

			<div class="content">
				<?php _e( 'Your Cart Is Empty!', 'tajer' ); ?>
			</div>
		</h2>
		<a href="<?php echo tajer_get_option( 'continue_shopping', 'tajer_general_settings', '#' ); ?>"
		   class="ui <?php echo $color; ?> button right floated tajer-continue-shopping"><?php esc_html_e( 'Continue Shopping?', 'tajer' ); ?></a>
	</div>
</div>


