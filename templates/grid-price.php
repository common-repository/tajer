<?php $price_with_id = tajer_get_lowest_price_with_id( get_the_ID() ); ?>
<div class="right floated" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
	<div itemprop="price" class="tajer_price">
		<?php if ( tajer_is_free( get_the_ID(), $price_with_id['id'] ) ) : ?>
			<?php echo __( 'Free', 'tajer' ); ?>
		<?php else: ?>
			<?php echo tajer_get_product_price( get_the_ID(), $price_with_id['id'], true, true ); ?>
		<?php endif; ?>
	</div>
</div>
