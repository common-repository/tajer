<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $post;
$prices          = get_post_meta( $post->ID, 'tajer_product_prices', true );
$default_price   = get_post_meta( $post->ID, 'tajer_default_multiple_price', true );
$secondary_color = tajer_get_option( 'secondary_color', 'tajer_general_settings', 'green' );
$sub_ids         = array();
?>
<div class="Tajer">
	<form class="ui form tajer-pricing-widget">
		<div class="grouped fields">
			<?php foreach ( $prices as $price_id => $price_detail ) { ?>
				<div class="field">
					<div class="ui radio checkbox">
						<input type="radio" <?php checked( $default_price, $price_id ) ?> name="psid"
						       value="<?php echo esc_attr( $price_id ); ?>" tabindex="0" class="hidden">
						<label><?php echo $price_detail['name'] . ' - ' . tajer_number_to_currency( $price_detail['price'], true, 'span' ); ?></label>
					</div>
				</div>
				<?php $sub_ids[] = $price_id; ?>
			<?php } ?>
			<div class="field">
				<a href="#" class="ui <?php echo $secondary_color; ?> button tiny tajer-pricing-widget-submit">
					<?php esc_html_e( 'Add To Cart', 'tajer' ) ?>
				</a>
				<?php
				$style = ( tajer_is_in_cart( $post->ID, $sub_ids ) ) ? '' : 'style="display: none;"';
				?>
				<a href="<?php echo get_permalink( intval( tajer_get_option( 'cart', 'tajer_general_settings', '' ) ) ); ?>"
				   class="tajer-pricing-widget-checkout-link" <?php echo $style; ?>>Checkout</a>
			</div>
		</div>

		<div class="ui success message">
			<p>Added Successfully!</p>
		</div>

		<div class="ui error message">
			<p>Cant Add It!</p>
		</div>

		<?php wp_nonce_field( 'tajer_pricing_widget_nonce', 'tajer_pricing_widget_nonce_field' ); ?>
		<input type="hidden" name="pid" value="<?php echo esc_attr( $post->ID ); ?>">
	</form>
</div>