<?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( get_the_ID() ) ) : ?>
	<?php $color = tajer_get_option( 'color', 'tajer_general_settings', 'teal' ); ?>
	<div class="blurring dimmable image tajer_product_image">
		<div class="ui dimmer">
			<div class="content">
				<div class="center">
					<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"
					   class="ui inverted <?php echo $color; ?> button"><?php esc_html_e( 'More' ); ?></a>
				</div>
			</div>
		</div>
		<?php echo get_the_post_thumbnail( get_the_ID(), 'post-thumbnail', array( 'class' => 'ui fluid image' ) ); ?>
	</div>
<?php endif; ?>