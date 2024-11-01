<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Tajer_Pricing_Widget extends WP_Widget {
	// widget constructor
	public function __construct() {
		parent::__construct(

		// base ID of the widget
			'tajer_pricing_widget',

			// name of the widget
			__( 'Tajer Product Pricing', 'tutsplus' ),

			// widget options
			array(
				'description' => __( 'Display product pricing with add to cart options and checkout.', 'tajer' )
			)
		);
	}

	public function widget( $args, $instance ) {
		// outputs the content of the widget

		if ( ! is_singular( 'tajer_products' ) ) {
			return;
		}

		$instance['title'] = ( isset( $instance['title'] ) ) ? $instance['title'] : '';

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		do_action( 'tajer_before_pricing_widget' );

		tajer_get_template_part( 'pricing-widget' );

		do_action( 'tajer_after_pricing_widget' );

		echo $args['after_widget'];
	}

	public function form( $instance ) {
		//back-end form
		$defaults = array(
			'title' => ''
		);

		$instance = wp_parse_args( (array) $instance, $defaults );

		// markup for form ?>
		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'tajer' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
			       value="<?php echo $instance['title']; ?>"/>
		</p>

		<?php
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		// processes widget options on save
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}
}

/**
 * Register Widgets
 *
 */
function tajer_register_widgets() {
	register_widget( 'Tajer_Pricing_Widget' );
}

add_action( 'widgets_init', 'tajer_register_widgets' );

function tajer_pricing_widget_submit() {
	$nonce = $_REQUEST['tajer_pricing_widget_nonce_field'];
	if ( ! wp_verify_nonce( $nonce, 'tajer_pricing_widget_nonce' ) ) {
		wp_die( 'Security check!' );
	}

	$result = tajer_add_to_cart( (int) $_REQUEST['pid'], (int) $_REQUEST['psid'] );

	$response = array(
		'message' => $result->message,
		'status'  => $result->status
	);

	$response = apply_filters( 'tajer_pricing_widget_submit_response', $response );
	tajer_response( $response );
}

add_action( 'wp_ajax_nopriv_tajer_submit_pricing_widget', 'tajer_pricing_widget_submit' );
add_action( 'wp_ajax_tajer_submit_pricing_widget', 'tajer_pricing_widget_submit' );