<?php $excerpt_length = apply_filters( 'excerpt_length', 30 ); ?>
<div itemprop="description" class="description tajer_product_excerpt">
	<?php if ( has_excerpt() ) : ?>
		<?php echo apply_filters( 'tajer_products_excerpt', wp_trim_words( get_post_field( 'post_excerpt', get_the_ID() ), $excerpt_length ) ); ?>
	<?php elseif ( get_the_content() ) : ?>
		<?php echo apply_filters( 'tajer_products_excerpt', wp_trim_words( get_post_field( 'post_content', get_the_ID() ), $excerpt_length ) ); ?>
	<?php endif; ?>
</div>
