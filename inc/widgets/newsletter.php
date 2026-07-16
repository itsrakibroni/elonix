<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Newsletter Widget
 */
class Elonix_Newsletter_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'classname'   => 'elonix_newsletter_widget',
			'description' => esc_html__( 'A custom newsletter subscription form.', 'elonix' ),
		);
		parent::__construct( 'elonix_newsletter', esc_html__( 'Elonix: Newsletter', 'elonix' ), $widget_ops );
	}

	// Frontend Display
	public function widget( $args, $instance ) {
		$title           = apply_filters( 'widget_title', empty( $instance['title'] ) ? esc_html__( 'Newsletter', 'elonix' ) : $instance['title'] );
		$placeholder     = ! empty( $instance['placeholder'] ) ? $instance['placeholder'] : esc_html__( 'Enter email', 'elonix' );
		$newsletter_type = ! empty( $instance['newsletter_type'] ) ? $instance['newsletter_type'] : 'custom';

		// Determine form action URL based on type
		if ( $newsletter_type === 'mailchimp' ) {
			$action_url = ! empty( $instance['mailchimp_action_url'] ) ? $instance['mailchimp_action_url'] : '';
		} else {
			$action_url = ! empty( $instance['action_url'] ) ? $instance['action_url'] : '';
		}

		// If no action URL, set to '#' to prevent submission and show warning
		if ( empty( $action_url ) ) {
			$action_url = '#';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];

		if ( $title ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		?>
		<div class="widget-box mb-0">
			<form class="newsletter-form" action="<?php echo esc_url( $action_url ); ?>" method="post" data-newsletter-type="<?php echo esc_attr( $newsletter_type ); ?>" data-ajax-form="true">
				<div class="form-group">
					<input type="email" name="<?php echo esc_attr( $newsletter_type === 'mailchimp' ? 'EMAIL' : 'email' ); ?>" class="email mb-0" placeholder="<?php echo esc_attr( $placeholder ); ?>" autocomplete="on" required>

					<?php if ( $newsletter_type === 'mailchimp' ) : ?>
						<div style="position: absolute; left: -5000px;" aria-hidden="true">
							<input type="text" name="b_<?php echo esc_attr( uniqid() ); ?>" tabindex="-1" value="">
						</div>
					<?php endif; ?>

					<button type="submit">
						<i class="far fa-paper-plane"></i>
					</button>
				</div>
			</form>

			<?php if ( $action_url === '#' ) : ?>
				<small style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">
					<?php esc_html_e( '⚠️ Please configure form action URL in widget settings.', 'elonix' ); ?>
				</small>
			<?php endif; ?>
		</div>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget'];
	}

	// Backend Form
	public function form( $instance ) {
		$title                = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Newsletter', 'elonix' );
		$newsletter_type      = ! empty( $instance['newsletter_type'] ) ? $instance['newsletter_type'] : 'custom';
		$action_url           = ! empty( $instance['action_url'] ) ? $instance['action_url'] : '';
		$mailchimp_action_url = ! empty( $instance['mailchimp_action_url'] ) ? $instance['mailchimp_action_url'] : '';
		$placeholder          = ! empty( $instance['placeholder'] ) ? $instance['placeholder'] : esc_html__( 'Enter email', 'elonix' );
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'elonix' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'newsletter_type' ) ); ?>"><?php esc_html_e( 'Newsletter Type:', 'elonix' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'newsletter_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'newsletter_type' ) ); ?>" onchange="toggleNewsletterFields(this)">
				<option value="custom" <?php selected( $newsletter_type, 'custom' ); ?>><?php esc_html_e( 'Custom Form (e.g., Formspree)', 'elonix' ); ?></option>
				<option value="mailchimp" <?php selected( $newsletter_type, 'mailchimp' ); ?>><?php esc_html_e( 'Mailchimp', 'elonix' ); ?></option>
			</select>
		</p>

		<p class="newsletter-field-custom" style="<?php echo esc_attr( $newsletter_type === 'custom' ? '' : 'display:none;' ); ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'action_url' ) ); ?>"><?php esc_html_e( 'Custom Form Action URL:', 'elonix' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'action_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'action_url' ) ); ?>" type="text" value="<?php echo esc_attr( $action_url ); ?>">
			<small><?php esc_html_e( 'Enter your Formspree or other custom form endpoint URL.', 'elonix' ); ?></small>
		</p>

		<p class="newsletter-field-mailchimp" style="<?php echo esc_attr( $newsletter_type === 'mailchimp' ? '' : 'display:none;' ); ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'mailchimp_action_url' ) ); ?>"><?php esc_html_e( 'Mailchimp Form Action URL:', 'elonix' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'mailchimp_action_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'mailchimp_action_url' ) ); ?>" type="text" value="<?php echo esc_attr( $mailchimp_action_url ); ?>">
			<small><?php esc_html_e( 'Get this from: Mailchimp → Audience → Signup forms → Embedded forms.', 'elonix' ); ?></small>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'placeholder' ) ); ?>"><?php esc_html_e( 'Email Placeholder:', 'elonix' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'placeholder' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'placeholder' ) ); ?>" type="text" value="<?php echo esc_attr( $placeholder ); ?>">
		</p>
		<?php
	}

	// Save Settings
	public function update( $new_instance, $old_instance ) {
		$instance                         = $old_instance;
		$instance['title']                = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['newsletter_type']      = ( ! empty( $new_instance['newsletter_type'] ) ) ? wp_strip_all_tags( $new_instance['newsletter_type'] ) : 'custom';
		$instance['action_url']           = ( ! empty( $new_instance['action_url'] ) ) ? esc_url_raw( $new_instance['action_url'] ) : '';
		$instance['mailchimp_action_url'] = ( ! empty( $new_instance['mailchimp_action_url'] ) ) ? esc_url_raw( $new_instance['mailchimp_action_url'] ) : '';
		$instance['placeholder']          = ( ! empty( $new_instance['placeholder'] ) ) ? wp_strip_all_tags( $new_instance['placeholder'] ) : '';

		return $instance;
	}
}

// Register the Widget
function elonix_register_newsletter_widget() {
	register_widget( 'Elonix_Newsletter_Widget' );

	// Add inline script for backend widget form
	add_action(
		'admin_footer-widgets.php',
		function () {
			?>
		<script type="text/javascript">
			function toggleNewsletterFields(select) {
				var widget = select.closest('.widget-content');
				var type = select.value;
				widget.querySelector('.newsletter-field-custom').style.display = (type === 'custom') ? 'block' : 'none';
				widget.querySelector('.newsletter-field-mailchimp').style.display = (type === 'mailchimp') ? 'block' : 'none';
			}
		</script>
			<?php
		}
	);
}
add_action( 'widgets_init', 'elonix_register_newsletter_widget' );
