<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Products_Grid {

	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'tajer_products', array( $this, 'tajer_products_query' ) );
	}

	function tajer_products_query( $atts, $content = null ) {
		$atts = shortcode_atts( apply_filters( 'tajer_grid_shortcode_atts', array(
			'category'         => '',
			'exclude_category' => '',
			'tags'             => '',
			'exclude_tags'     => '',
			'relation'         => 'OR',
			'number'           => 9,
			'price'            => 'no',
			'excerpt'          => 'yes',
			'full_content'     => 'no',
			'buy_button'       => 'yes',
			'thumbnails'       => 'true',
			'orderby'          => 'post_date',
			'order'            => 'DESC',
			'ids'              => ''
		), $atts, $content ), $atts, 'tajer_products' );

		$query = apply_filters( 'tajer_grid_query', array(
			'post_type'      => 'tajer_products',
			'posts_per_page' => (int) $atts['number'],
			'orderby'        => $atts['orderby'],
			'order'          => $atts['order']
		), $atts, $content );

		if ( $query['posts_per_page'] < - 1 ) {
			$query['posts_per_page'] = abs( $query['posts_per_page'] );
		}

		switch ( $atts['orderby'] ) {

			case 'title':
				$query['orderby'] = 'title';
				break;

			case 'id':
				$query['orderby'] = 'ID';
				break;

			case 'random':
				$query['orderby'] = 'rand';
				break;

			default:
				$query['orderby'] = 'post_date';
				break;
		}

		if ( $atts['tags'] || $atts['category'] || $atts['exclude_category'] || $atts['exclude_tags'] ) {

			$query['tax_query'] = array(
				'relation' => $atts['relation']
			);

			if ( $atts['tags'] ) {

				$tag_list = explode( ',', $atts['tags'] );

				foreach ( $tag_list as $tag ) {

					if ( is_numeric( $tag ) ) {

						$term_id = $tag;

					} else {

						$term = get_term_by( 'slug', $tag, 'tajer_product_tag' );

						if ( ! $term ) {
							continue;
						}

						$term_id = $term->term_id;
					}

					$query['tax_query'][] = apply_filters( 'tajer_grid_tags_query', array(
						'taxonomy' => 'tajer_product_tag',
						'field'    => 'term_id',
						'terms'    => $term_id
					), $atts, $tag_list, $tag, $term );
				}

			}

			if ( $atts['category'] ) {

				$categories = explode( ',', $atts['category'] );

				foreach ( $categories as $category ) {

					if ( is_numeric( $category ) ) {

						$term_id = $category;

					} else {

						$term = get_term_by( 'slug', $category, 'tajer_product_category' );

						if ( ! $term ) {
							continue;
						}

						$term_id = $term->term_id;

					}

					$query['tax_query'][] = apply_filters( 'tajer_grid_categories_query', array(
						'taxonomy' => 'tajer_product_category',
						'field'    => 'term_id',
						'terms'    => $term_id,
					), $atts, $categories, $category, $term );

				}

			}

			if ( $atts['exclude_category'] ) {

				$categories = explode( ',', $atts['exclude_category'] );

				foreach ( $categories as $category ) {

					if ( is_numeric( $category ) ) {

						$term_id = $category;

					} else {

						$term = get_term_by( 'slug', $category, 'tajer_product_category' );

						if ( ! $term ) {
							continue;
						}

						$term_id = $term->term_id;
					}

					$query['tax_query'][] = apply_filters( 'tajer_grid_exclude_category_query', array(
						'taxonomy' => 'tajer_product_category',
						'field'    => 'term_id',
						'terms'    => $term_id,
						'operator' => 'NOT IN'
					), $atts, $categories, $category, $term );
				}

			}

			if ( $atts['exclude_tags'] ) {

				$tag_list = explode( ',', $atts['exclude_tags'] );

				foreach ( $tag_list as $tag ) {

					if ( is_numeric( $tag ) ) {

						$term_id = $tag;

					} else {

						$term = get_term_by( 'slug', $tag, 'tajer_product_tag' );

						if ( ! $term ) {
							continue;
						}

						$term_id = $term->term_id;
					}

					$query['tax_query'][] = apply_filters( 'tajer_grid_exclude_tags_query', array(
						'taxonomy' => 'tajer_product_tag',
						'field'    => 'term_id',
						'terms'    => $term_id,
						'operator' => 'NOT IN'
					), $atts, $tag_list, $tag, $term );

				}

			}
		}

		if ( $atts['exclude_tags'] || $atts['exclude_category'] ) {
			$query['tax_query']['relation'] = 'AND';
		}

		if ( ! empty( $atts['ids'] ) ) {
			$query['post__in'] = explode( ',', $atts['ids'] );
		}

		if ( get_query_var( 'paged' ) ) {
			$query['paged'] = get_query_var( 'paged' );
		} else if ( get_query_var( 'page' ) ) {
			$query['paged'] = get_query_var( 'page' );
		} else {
			$query['paged'] = 1;
		}

		// Allow the query to be manipulated by other plugins
		$query = apply_filters( 'tajer_products_query', $query, $atts );

		$color = tajer_get_option( 'color', 'tajer_general_settings', 'teal' );

		$products = new WP_Query( $query );
		if ( $products->have_posts() ) :
//			$i = 1;
//			$wrapper_class = 'tajer_product_columns_';
			ob_start(); ?>
			<!--			<div class="tajer_products_list --><?php //echo apply_filters( 'tajer_products_list_wrapper_class', $wrapper_class, $atts );
			?><!--">-->
			<div class="Tajer tajer-container">
				<div class="ui stackable four column centered grid">
					<?php while ( $products->have_posts() ) : $products->the_post(); ?>
						<div class="column">
							<div class="ui fluid <?php echo $color; ?> card tajer-product">
								<?php $schema = tajer_add_schema_microdata() ? 'itemscope itemtype="http://schema.org/Product" ' : ''; ?>
								<div <?php echo $schema; ?>class="
						<?php echo apply_filters( 'tajer_product_class', 'tajer_product', get_the_ID(), $atts ); ?>"
								     id="tajer_product_
						<?php echo get_the_ID(); ?>">
									<div class="tajer_product_inner">

										<?php
										do_action( 'tajer_product_before', $atts );
										if ( 'false' != $atts['thumbnails'] ) :
											tajer_get_template_part( 'grid-image' );
											do_action( 'tajer_product_after_thumbnail', $atts );
										endif; ?>

										<div class="content">
											<?php
											tajer_get_template_part( 'grid-title' );
											do_action( 'tajer_product_after_title', $atts );

											if ( $atts['excerpt'] == 'yes' && $atts['full_content'] != 'yes' ) {
												tajer_get_template_part( 'grid-excerpt' );
												do_action( 'tajer_product_after_content', $atts );
											} else if ( $atts['full_content'] == 'yes' ) {
												tajer_get_template_part( 'grid-full-content' );
												do_action( 'tajer_product_after_content', $atts );
											} ?>

										</div>
										<div class="ui fitted divider"></div>
										<div class="extra content">
											<?php
											if ( $atts['price'] == 'yes' ) {
												tajer_get_template_part( 'grid-price' );
												do_action( 'tajer_product_after_price', $atts );
											}

											if ( $atts['buy_button'] == 'yes' ) {
												tajer_get_template_part( 'grid-purchase-link' );
											}

											do_action( 'tajer_product_after', $atts );
											?>
										</div>
									</div>
								</div>
								<!--					-->
								<?php //if ( $atts['columns'] != 0 && $i % $atts['columns'] == 0 ) { ?><!--<div style="clear:both;"></div>--><?php //} ?>
							</div>
						</div>
						<!--					--><?php //$i++;

					endwhile; ?>


				</div>
			</div>
			<!--				<div style="clear:both;"></div>-->

			<?php wp_reset_postdata(); ?>

			<?php
			//pagination logic
			if ( is_single() ) {
				$paginate_links = paginate_links( apply_filters( 'tajer_product_pagination_args', array(
					'base'      => get_permalink() . '%#%',
					'format'    => '?paged=%#%',
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;',
					'current'   => max( 1, $query['paged'] ),
					'total'     => $products->max_num_pages
				), $atts, $products, $query ) );
			} else {
				$big            = 999999;
				$search_for     = array( $big, '#038;' );
				$replace_with   = array( '%#%', '&' );
				$paginate_links = paginate_links( apply_filters( 'tajer_product_pagination_args', array(
					'base'      => str_replace( $search_for, $replace_with, get_pagenum_link( $big ) ),
					'format'    => '?paged=%#%',
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;',
					'current'   => max( 1, $query['paged'] ),
					'total'     => $products->max_num_pages
				), $atts, $products, $query ) );
			}
			//pagination render
			//test
//			foreach ( $paginate_links as $paginate_link ) {
//				if ( strpos( $paginate_link, 'current' ) !== false ) {
//					$current =  $paginate_link ;
//				}
//			}
//			$data = array( 'paginate_links' => $paginate_links, 'current' => $current );
//			$template_parser = Tajer_Twig_Initializer::initialize_templates();
//			echo $template_parser->render('grid-navigation.html', $data);
			//testest
			?>
			<?php if ( $paginate_links ) { ?>
			<div class="Tajer">
				<?php

				echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $paginate_links . '</div></div>'

				?>

				<!--				<div id="tajer_product_pagination" class="ui pagination menu">-->
				<!--					--><?php
				//					foreach ( $paginate_links as $paginate_link ) {
				//						if ( strpos( $paginate_link, 'current' ) !== false ) {
				//							echo '<span class="active item">' . $paginate_link . '</span>';
				//						} else {
				//							echo '<span class="item">' . $paginate_link . '</span>';
				//						}
				//					}
				//					?>
				<!--				</div>-->
			</div>
		<?php } ?>


			<!--			</div>-->
			<?php
			$display = ob_get_clean();
		else:
			$display = apply_filters( 'tajer_grid_no_products_message', sprintf( _x( 'No %s found', 'product post type name', 'tajer' ), 'products' ), $atts, $products, $query );
		endif;

		return apply_filters( 'tajer_products_shortcode', $display, $atts, $products, $query );
	}
}
