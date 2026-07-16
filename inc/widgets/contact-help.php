<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elonix Service Help / Contact Widget
 * Standards: ThemeForest Approved (Sanitized, Escaped, Translation Ready).
 */
class Elonix_Service_Help_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname'   => 'elonix_service_help_widget',
			'description' => esc_html__( 'Displays a contact help box with an image upload option.', 'elonix' ),
		);
		parent::__construct( 'elonix_service_help_widget', esc_html__( 'Elonix: Service Help / Contact', 'elonix' ), $widget_ops );
	}

	public function widget( $args, $instance ) {
		$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '';
		$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : '';

		// Variable extraction with fallback checks
		$bg_image = ! empty( $instance['bg_image'] ) ? $instance['bg_image'] : '';
		$icon     = ! empty( $instance['icon'] ) ? $instance['icon'] : '';
		$title    = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$text     = ! empty( $instance['text'] ) ? $instance['text'] : '';
		$btn_text = ! empty( $instance['btn_text'] ) ? $instance['btn_text'] : esc_html__( 'Contact with Us', 'elonix' );
		$btn_url  = ! empty( $instance['btn_url'] ) ? $instance['btn_url'] : '#';

		echo wp_kses_post( $before_widget );
		?>

		<div class="widget-box service-details-help">
			<?php if ( ! empty( $bg_image ) ) : ?>
				<div class="bg image">
					<img src="<?php echo esc_url( $bg_image ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>">
				</div>
			<?php endif; ?>

			<div class="service-details-content">
				<?php if ( ! empty( $icon ) ) : ?>
					<div class="icon">
						<img src="<?php echo esc_url( $icon ); ?>" alt="<?php esc_attr_e( 'Icon', 'elonix' ); ?>">
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $title ) ) : ?>
					<h2 class="help-title"><?php echo wp_kses_post( $title ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $text ) ) : ?>
					<p class="text"><?php echo esc_html( $text ); ?></p>
				<?php endif; ?>

				<div class="help-contact">
					<a href="<?php echo esc_url( $btn_url ); ?>" class="theme-btn br-30">
						<span class="link-effect">
							<span class="effect-1"><?php echo esc_html( $btn_text ); ?></span>
							<span class="effect-1"><?php echo esc_html( $btn_text ); ?></span>
						</span>
						<span class="arrow-all">
							<i>
								<svg width="16" height="19" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 6H10M10 6L6 2M10 6L6 10" stroke="#1053f3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
								<svg width="16" height="19" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 6H10M10 6L6 2M10 6L6 10" stroke="#1053f3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
							</i>
						</span>
					</a>
				</div>
			</div>
		</div>

		<?php
		echo wp_kses_post( $after_widget );
	}

	public function update( $new_instance, $old_instance ) {
		$instance             = array();
		$instance['bg_image'] = esc_url_raw( $new_instance['bg_image'] );
		$instance['icon']     = esc_url_raw( $new_instance['icon'] );
		$instance['title']    = wp_kses_post( $new_instance['title'] );
		$instance['text']     = sanitize_textarea_field( $new_instance['text'] );
		$instance['btn_text'] = sanitize_text_field( $new_instance['btn_text'] );
		$instance['btn_url']  = esc_url_raw( $new_instance['btn_url'] );
		return $instance;
	}

	public function form( $instance ) {
		$bg_image = ! empty( $instance['bg_image'] ) ? $instance['bg_image'] : '';
		$icon     = ! empty( $instance['icon'] ) ? $instance['icon'] : '';
		$title    = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Need Tech Service? <br> Contact Us', 'elonix' );
		$text     = ! empty( $instance['text'] ) ? $instance['text'] : esc_html__( 'Professionally reintermediate technics Credibly pontificate turnkey', 'elonix' );
		$btn_text = ! empty( $instance['btn_text'] ) ? $instance['btn_text'] : esc_html__( 'Contact with Us', 'elonix' );
		$btn_url  = ! empty( $instance['btn_url'] ) ? $instance['btn_url'] : '#';
		?>

		<div class="elonix-widget-upload-wrapper">
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'bg_image' ) ); ?>"><?php esc_html_e( 'Background Image:', 'elonix' ); ?></label>
				<input class="widefat elonix-media-input" id="<?php echo esc_attr( $this->get_field_id( 'bg_image' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'bg_image' ) ); ?>" type="text" value="<?php echo esc_url( $bg_image ); ?>" />
				<button type="button" class="button button-secondary elonix-media-upload-btn" style="margin-top: 5px;"><?php esc_html_e( 'Upload Image', 'elonix' ); ?></button>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'icon' ) ); ?>"><?php esc_html_e( 'Icon Image:', 'elonix' ); ?></label>
				<input class="widefat elonix-media-input" id="<?php echo esc_attr( $this->get_field_id( 'icon' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'icon' ) ); ?>" type="text" value="<?php echo esc_url( $icon ); ?>" />
				<button type="button" class="button button-secondary elonix-media-upload-btn" style="margin-top: 5px;"><?php esc_html_e( 'Upload Icon', 'elonix' ); ?></button>
			</p>
		</div>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'elonix' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php esc_html_e( 'Description:', 'elonix' ); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" rows="4"><?php echo esc_textarea( $text ); ?></textarea>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'btn_text' ) ); ?>"><?php esc_html_e( 'Button Text:', 'elonix' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'btn_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'btn_text' ) ); ?>" type="text" value="<?php echo esc_attr( $btn_text ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'btn_url' ) ); ?>"><?php esc_html_e( 'Button URL:', 'elonix' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'btn_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'btn_url' ) ); ?>" type="text" value="<?php echo esc_attr( $btn_url ); ?>" />
		</p>

		<?php
	}
}
// Register Widget
function elonix_register_service_help_widget() {
	register_widget( 'Elonix_Service_Help_Widget' );
}
add_action( 'widgets_init', 'elonix_register_service_help_widget' );
