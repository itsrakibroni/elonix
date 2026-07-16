<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elonix Service Downloads Widget
 * * Standards: ThemeForest Approved (Sanitized, Escaped, Translation Ready).
 * * Dependency: Requires the 'elonix_admin_widget_scripts' JS function provided previously.
 */
class Elonix_Service_Download_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'elonix_service_download_widget',
			'description' => esc_html__( 'Displays download buttons for reports and brochures.', 'elonix' ),
		);
		parent::__construct( 'elonix_service_download_widget', esc_html__( 'Elonix: Service Downloads', 'elonix' ), $widget_ops );
	}

	/**
	 * Front-end display of widget.
	 */
	public function widget( $args, $instance ) {
		// Extract standard widget arguments
		$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '';
		$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : '';
		$before_title  = isset( $args['before_title'] ) ? $args['before_title'] : '';
		$after_title   = isset( $args['after_title'] ) ? $args['after_title'] : '';

		// Retrieve and escape variables
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? esc_html__( 'Downloads', 'elonix' ) : $instance['title'] );

		// File 1 Data
		$file1_text = ! empty( $instance['file1_text'] ) ? $instance['file1_text'] : '';
		$file1_url  = ! empty( $instance['file1_url'] ) ? $instance['file1_url'] : '';
		$file1_icon = ! empty( $instance['file1_icon'] ) ? $instance['file1_icon'] : '';

		// File 2 Data
		$file2_text = ! empty( $instance['file2_text'] ) ? $instance['file2_text'] : '';
		$file2_url  = ! empty( $instance['file2_url'] ) ? $instance['file2_url'] : '';
		$file2_icon = ! empty( $instance['file2_icon'] ) ? $instance['file2_icon'] : '';

		// Output Widget Wrapper
		echo wp_kses_post( $before_widget );
		?>

		<div class="widget-box service-download-box">

			<?php if ( ! empty( $title ) ) : ?>
				<?php echo wp_kses_post( $before_title . $title . $after_title ); ?>
			<?php endif; ?>

			<?php if ( ! empty( $file1_text ) || ! empty( $file1_url ) ) : ?>
				<div class="service-download-btn mb-10">
					<a href="<?php echo esc_url( $file1_url ); ?>" download target="_blank" class="theme-btn btn-style-1 d-grid">
						<span class="btn-title">
							<?php if ( ! empty( $file1_icon ) ) : ?>
								<img class="mr-10" src="<?php echo esc_url( $file1_icon ); ?>" alt="<?php echo esc_attr( 'Icon', 'elonix' ); ?>">
							<?php endif; ?>
							<?php echo esc_html( $file1_text ); ?>
						</span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $file2_text ) || ! empty( $file2_url ) ) : ?>
				<div class="service-download-btn">
					<a href="<?php echo esc_url( $file2_url ); ?>" download target="_blank" class="theme-btn btn-style-2 d-grid bg-dark">
						<span class="btn-title">
							<?php if ( ! empty( $file2_icon ) ) : ?>
								<img class="mr-10" src="<?php echo esc_url( $file2_icon ); ?>" alt="<?php echo esc_attr( 'Icon', 'elonix' ); ?>">
							<?php endif; ?>
							<?php echo esc_html( $file2_text ); ?>
						</span>
					</a>
				</div>
			<?php endif; ?>

		</div>

		<?php
		// Output Widget Closer
		echo wp_kses_post( $after_widget );
	}

	/**
	 * Sanitize widget form values.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		// File 1
		$instance['file1_text'] = sanitize_text_field( $new_instance['file1_text'] );
		$instance['file1_url']  = esc_url_raw( $new_instance['file1_url'] );
		$instance['file1_icon'] = esc_url_raw( $new_instance['file1_icon'] );

		// File 2
		$instance['file2_text'] = sanitize_text_field( $new_instance['file2_text'] );
		$instance['file2_url']  = esc_url_raw( $new_instance['file2_url'] );
		$instance['file2_icon'] = esc_url_raw( $new_instance['file2_icon'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Downloads', 'elonix' );

		// Defaults
		$file1_text = ! empty( $instance['file1_text'] ) ? $instance['file1_text'] : 'Company Report 2025.pdf';
		$file1_url  = ! empty( $instance['file1_url'] ) ? $instance['file1_url'] : '#';
		$file1_icon = ! empty( $instance['file1_icon'] ) ? $instance['file1_icon'] : '';

		$file2_text = ! empty( $instance['file2_text'] ) ? $instance['file2_text'] : 'Company Brochure.doc';
		$file2_url  = ! empty( $instance['file2_url'] ) ? $instance['file2_url'] : '#';
		$file2_icon = ! empty( $instance['file2_icon'] ) ? $instance['file2_icon'] : '';
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'elonix' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<hr>
		<h4><?php esc_html_e( 'File 1 (Style 1)', 'elonix' ); ?></h4>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'file1_text' ) ); ?>"><?php esc_html_e( 'Button Text:', 'elonix' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'file1_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'file1_text' ) ); ?>" type="text" value="<?php echo esc_attr( $file1_text ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'file1_url' ) ); ?>"><?php esc_html_e( 'Download Link (URL):', 'elonix' ); ?></label>
			<input class="widefat elonix-media-input" id="<?php echo esc_attr( $this->get_field_id( 'file1_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'file1_url' ) ); ?>" type="text" value="<?php echo esc_url( $file1_url ); ?>" />
			<button type="button" class="button button-secondary elonix-media-upload-btn" style="margin-top: 5px;"><?php esc_html_e( 'Upload File', 'elonix' ); ?></button>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'file1_icon' ) ); ?>"><?php esc_html_e( 'Icon Image:', 'elonix' ); ?></label>
			<input class="widefat elonix-media-input" id="<?php echo esc_attr( $this->get_field_id( 'file1_icon' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'file1_icon' ) ); ?>" type="text" value="<?php echo esc_url( $file1_icon ); ?>" />
			<button type="button" class="button button-secondary elonix-media-upload-btn" style="margin-top: 5px;"><?php esc_html_e( 'Upload Icon', 'elonix' ); ?></button>
		</p>

		<hr>
		<h4><?php esc_html_e( 'File 2 (Style 2 Dark)', 'elonix' ); ?></h4>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'file2_text' ) ); ?>"><?php esc_html_e( 'Button Text:', 'elonix' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'file2_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'file2_text' ) ); ?>" type="text" value="<?php echo esc_attr( $file2_text ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'file2_url' ) ); ?>"><?php esc_html_e( 'Download Link (URL):', 'elonix' ); ?></label>
			<input class="widefat elonix-media-input" id="<?php echo esc_attr( $this->get_field_id( 'file2_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'file2_url' ) ); ?>" type="text" value="<?php echo esc_url( $file2_url ); ?>" />
			<button type="button" class="button button-secondary elonix-media-upload-btn" style="margin-top: 5px;"><?php esc_html_e( 'Upload File', 'elonix' ); ?></button>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'file2_icon' ) ); ?>"><?php esc_html_e( 'Icon Image:', 'elonix' ); ?></label>
			<input class="widefat elonix-media-input" id="<?php echo esc_attr( $this->get_field_id( 'file2_icon' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'file2_icon' ) ); ?>" type="text" value="<?php echo esc_url( $file2_icon ); ?>" />
			<button type="button" class="button button-secondary elonix-media-upload-btn" style="margin-top: 5px;"><?php esc_html_e( 'Upload Icon', 'elonix' ); ?></button>
		</p>

		<?php
	}
}

// Register the Widget
function elonix_register_download_widget() {
	register_widget( 'Elonix_Service_Download_Widget' );
}
add_action( 'widgets_init', 'elonix_register_download_widget' );
