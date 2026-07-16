<?php
/**
 * Quick Edit Overlay Service for Elonix – Toolkit for Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Toolkit_Edit_Overlay {

	private static $css_enqueued = false;

	/**
	 * Render the edit overlay button for a given post ID.
	 *
	 * @param int $post_id The ID of the post.
	 */
	public static function render( $post_id ) {
		// Only show for logged in users
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Only show if user has permission to edit this specific post
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Frontend only (including Elementor preview)
		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			return;
		}

		$edit_link = get_edit_post_link( $post_id );
		if ( ! $edit_link ) {
			return;
		}

		self::enqueue_styles();

		?>
		<a href="<?php echo esc_url( $edit_link ); ?>" class="tv-edit-overlay__button" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Edit Post', 'elonix' ); ?>">
			<span class="dashicons dashicons-edit" aria-hidden="true"></span>
		</a>
		<?php
	}

	/**
	 * Enqueue required styles lazily.
	 */
	private static function enqueue_styles() {
		if ( self::$css_enqueued ) {
			return;
		}

		wp_enqueue_style( 'dashicons' );

		wp_enqueue_style(
			'tv-edit-overlay-css',
			ELONIX_ACC_URL . 'assets/css/edit-overlay.css',
			array(),
			ELONIX_VERSION
		);

		self::$css_enqueued = true;
	}
}
