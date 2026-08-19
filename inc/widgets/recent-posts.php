<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Custom Widgets
 *
 * @package Elonix_Toolkit
 */

// Recent Posts with Thumbnails Widget
class elonix_posts_thumbs extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'description' => esc_html__( 'Display Random or Recent posts with the new style.', 'elonix' ) );
		parent::__construct( false, esc_html__( 'Elonix: Recent Posts With Image', 'elonix' ), $widget_ops );
	}

	function widget( $args, $instance ) {

		$before_widget = $args['before_widget'] ?? '';
		$after_widget  = $args['after_widget'] ?? '';
		$before_title  = $args['before_title'] ?? '';
		$after_title   = $args['after_title'] ?? '';

		$title = apply_filters( 'widget_title', $instance['title'] );
		$args  = array(
			'posts_per_page' => $instance['number'],
			'post_type'      => 'post',
			'order'          => 'DESC',
			'orderby'        => $instance['orderby'],
		);
		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return;
		}

		// Widget Wrapper Start
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- standard WP_Widget wrapper markup, supplied by the theme's register_sidebar(), not user input.
		echo $before_widget;

		// Title Output (Usually <h4>)
		if ( $title ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- before/after_title are standard WP_Widget theme wrapper markup; $title is esc_html()'d.
			echo $before_title . esc_html( $title ) . $after_title;
		}

		// If number is missing, default to 4
		if ( ! $instance['number'] ) {
			$instance['number'] = 4;
		}

		if ( $query->have_posts() ) : ?>

			<div class="widget-box">
				<div class="latest-posts">

					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						?>

						<article class="post">
							<?php if ( has_post_thumbnail() ) : ?>
								<a class="thumb" href="<?php the_permalink(); ?>">
									<?php
										// Rendering the image tag directly
										the_post_thumbnail( 88, array( 'alt' => get_the_title() ) );
									?>
								</a>
							<?php endif; ?>

							<div class="post-content">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								<p><?php echo esc_html( get_the_date() ); ?></p>
							</div>
						</article>

					<?php endwhile; ?>

				</div>
			</div>
			<?php
			wp_reset_postdata();
		endif;

		// Widget Wrapper End
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- standard WP_Widget wrapper markup, supplied by the theme's register_sidebar(), not user input.
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance            = $old_instance;
		$instance['title']   = wp_strip_all_tags( $new_instance['title'] );
		$instance['number']  = (int) $new_instance['number'];
		$instance['orderby'] = $new_instance['orderby'];
		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title'   => 'Latest Posts',
			'number'  => 4,
			'orderby' => 'date',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$number   = isset( $instance['number'] ) ? absint( $instance['number'] ) : 4;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'elonix' ); ?>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of posts to show:', 'elonix' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="3" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php esc_html_e( 'Mode:', 'elonix' ); ?> </label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
				<option 
				<?php selected( $instance['orderby'], 'date' ); ?>
				value="date"><?php esc_html_e( 'Recent Posts', 'elonix' ); ?></option>
				<option 
				<?php selected( $instance['orderby'], 'rand' ); ?>
				value="rand"><?php esc_html_e( 'Random Posts', 'elonix' ); ?></option>
				<?php if ( function_exists( 'get_field' ) ) : ?>
					<option 
					<?php selected( $instance['orderby'], 'views' ); ?>
					value="views"><?php esc_html_e( 'Post views', 'elonix' ); ?></option>
				<?php endif; ?>
			</select>
		</p>
		<?php
	}
}

function elonix_register_posts_thumbs() {
	register_widget( 'elonix_posts_thumbs' );
}

add_action( 'widgets_init', 'elonix_register_posts_thumbs' );
