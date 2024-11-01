<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<?php $color = tajer_get_option( 'color', 'tajer_general_settings', 'teal' ); ?>
<div class="Tajer">
	<div class="ui <?php echo $color; ?> segment tajer-clearfix tajer-restrict-dashboard-access">
		<h2 class="ui header left floated">
			<i class="dashboard icon"></i>

			<div class="content">
				<?php _e( 'Please login first to access your products.', 'tajer' ); ?>
			</div>
		</h2>
		<div class="ui buttons right floated tajer-conditionals-buttons">
			<a href="<?php echo apply_filters( 'tajer_restrict_dashboard_access_login_url', get_permalink( intval( tajer_get_option( 'login_page', 'tajer_general_settings', '' ) ) ) ); ?>"
			   class="ui button"><?php esc_html_e( 'Login', 'tajer' ); ?></a>

			<div class="or"></div>
			<a href="<?php echo apply_filters( 'tajer_restrict_dashboard_access_registration_url', get_permalink( intval( tajer_get_option( 'registration_page', 'tajer_general_settings', '' ) ) ) ); ?>"
			   class="ui positive button"><?php esc_html_e( 'Register', 'tajer' ); ?></a>
		</div>
	</div>
</div>


