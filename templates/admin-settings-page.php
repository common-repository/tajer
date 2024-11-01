<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>
<div class="Tajer">
	<div class="tajer-container">
		<div class="ui padded grid">
			<div class="row">
				<h3 class="ui header"><?php esc_html_e( 'Tajer Settings', 'tajer' ) ?></h3>
			</div>
			<div class="row">
				<div class="twelve wide column">
					<div id="tajer-user-settings">
						<form class="ui fluid form tajer-settings-form">
							<div class="ui pointing secondary menu">
								<a class="item active"
								   data-tab="general"><?php esc_html_e( 'General Settings', 'tajer' ) ?></a>
								<a class="item"
								   data-tab="payment"><?php esc_html_e( 'Payment Gateways', 'tajer' ) ?></a>
								<a class="item" data-tab="emails"><?php esc_html_e( 'Emails', 'tajer' ) ?></a>
								<a class="item" data-tab="taxes"><?php esc_html_e( 'Taxes', 'tajer' ) ?></a>
								<a class="item" data-tab="tools"><?php esc_html_e( 'Tools', 'tajer' ) ?></a>
								<a class="item"
								   data-tab="support"><?php esc_html_e( 'Support & Licensing', 'tajer' ) ?></a>
							</div>

							<div class="ui tab segment active" data-tab="general">
								<?php do_action( 'tajer_render_general_settings' ); ?>
							</div>
							<div class="ui tab segment" data-tab="payment">
								<?php do_action( 'tajer_render_payment_settings' ); ?>
							</div>
							<div class="ui tab segment" data-tab="emails">
								<?php do_action( 'tajer_render_emails_settings' ); ?>
							</div>
							<div class="ui tab segment" data-tab="taxes">
								<?php do_action( 'tajer_render_taxes_settings' ); ?>
							</div>
							<div class="ui tab segment" data-tab="tools">
								<?php do_action( 'tajer_render_tools_settings' ); ?>
								<?php wp_nonce_field( 'tajer_save_settings_nonce', 'tajer_save_settings_nonce_field' ); ?>
							</div>
							<div class="ui tab segment" data-tab="support">
								<?php do_action( 'tajer_render_support_settings' ); ?>
							</div>
						</form>
					</div>
				</div>
				<div class="four wide column">
					<div class="ui segment">
						<div class="tajer-save-settings-group">
							<button type="button" id="tajer-save-settings"
							        class="fluid small ui blue labeled icon button">
								<i class="save icon"></i>
								<span><?php _e( 'Save All Changes', 'tajer' ) ?></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>