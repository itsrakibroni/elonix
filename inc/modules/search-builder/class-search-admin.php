<?php
/**
 * Elonix Search Builder Custom Admin Configuration
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.Files.FileName -- Search Builder keeps Archive Builder file naming to preserve shared module architecture.
/**
 * Elonix Search Builder Custom Admin Configuration.
 */
class Elonix_Search_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'manage_tv_search_template_posts_columns', array( $this, 'register_custom_columns' ) );
		add_action( 'manage_tv_search_template_posts_custom_column', array( $this, 'render_custom_columns' ), 10, 2 );

		add_action( 'add_meta_boxes', array( $this, 'add_search_settings_metabox' ) );
		add_action( 'save_post_tv_search_template', array( $this, 'save_search_settings' ) );

		add_action( 'wp_ajax_tv_duplicate_search_template', array( $this, 'duplicate_search_template' ) );
	}

	/**
	 * Define Search Builder list table columns.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function register_custom_columns( $columns ) {
		return array(
			'cb'                      => $columns['cb'],
			'title'                   => $columns['title'],
			'tv_search_header_footer' => esc_html__( 'Header/Footer Theme Integration', 'elonix' ),
			'date'                    => $columns['date'],
		);
	}

	/**
	 * Render Search Builder custom columns.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public function render_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'tv_search_header_footer':
				$header = get_post_meta( $post_id, '_tv_search_show_header', true );
				$footer = get_post_meta( $post_id, '_tv_search_show_footer', true );

				$h_status = ( 'no' === $header ) ? esc_html__( 'Disabled', 'elonix' ) : esc_html__( 'Enabled', 'elonix' );
				$f_status = ( 'no' === $footer ) ? esc_html__( 'Disabled', 'elonix' ) : esc_html__( 'Enabled', 'elonix' );

				/* translators: 1: header status, 2: footer status. */
				echo esc_html( sprintf( __( 'Header: %1$s | Footer: %2$s', 'elonix' ), $h_status, $f_status ) );
				break;
		}
	}

	/**
	 * Register Search Builder settings metabox.
	 */
	public function add_search_settings_metabox() {
		add_meta_box(
			'tv_search_settings_metabox',
			esc_html__( 'Search Builder Settings', 'elonix' ),
			array( $this, 'render_settings_metabox' ),
			'tv_search_template',
			'normal',
			'high'
		);
	}

	/**
	 * Render settings metabox.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_settings_metabox( $post ) {
		wp_nonce_field( 'tv_search_settings_save', 'tv_search_settings_nonce' );

		$show_header  = get_post_meta( $post->ID, '_tv_search_show_header', true );
		$show_footer  = get_post_meta( $post->ID, '_tv_search_show_footer', true );
		$preview_term = get_post_meta( $post->ID, '_tv_search_preview_term', true );

		if ( empty( $show_header ) ) {
			$show_header = 'yes';
		}
		if ( empty( $show_footer ) ) {
			$show_footer = 'yes';
		}
		if ( '' === $preview_term ) {
			$preview_term = 'sample';
		}
		?>
		<style>
			.tv-search-meta-row { display: flex; align-items: flex-start; padding: 14px 0; border-bottom: 1px solid #eee; }
			.tv-search-meta-label { width: 220px; font-weight: bold; padding-top: 4px; }
			.tv-search-meta-field { flex: 1; }
			.tv-search-meta-field select, .tv-search-meta-field input[type="text"] { width: 100%; max-width: 400px; }
			.tv-search-meta-section-title { font-size: 14px; font-weight: bold; background: #f8fafc; padding: 8px 12px; margin: 15px 0 5px 0; border-left: 4px solid #3b82f6; }
		</style>
		<div class="tv-search-meta-wrapper">
			<div class="tv-search-meta-section-title"><?php esc_html_e( 'Theme & Header/Footer Layout Options', 'elonix' ); ?></div>

			<div class="tv-search-meta-row">
				<div class="tv-search-meta-label"><?php esc_html_e( 'Render Theme Header', 'elonix' ); ?></div>
				<div class="tv-search-meta-field">
					<select name="tv_search_show_header">
						<option value="yes" <?php selected( $show_header, 'yes' ); ?>><?php esc_html_e( 'Yes (Call native theme header / Elonix Header Builder)', 'elonix' ); ?></option>
						<option value="no" <?php selected( $show_header, 'no' ); ?>><?php esc_html_e( 'No (Clean Canvas Layout)', 'elonix' ); ?></option>
					</select>
				</div>
			</div>

			<div class="tv-search-meta-row">
				<div class="tv-search-meta-label"><?php esc_html_e( 'Render Theme Footer', 'elonix' ); ?></div>
				<div class="tv-search-meta-field">
					<select name="tv_search_show_footer">
						<option value="yes" <?php selected( $show_footer, 'yes' ); ?>><?php esc_html_e( 'Yes (Call native theme footer / Elonix Footer Builder)', 'elonix' ); ?></option>
						<option value="no" <?php selected( $show_footer, 'no' ); ?>><?php esc_html_e( 'No (Clean Canvas Layout)', 'elonix' ); ?></option>
					</select>
				</div>
			</div>

			<div class="tv-search-meta-section-title"><?php esc_html_e( 'Preview Context Configuration', 'elonix' ); ?></div>

			<div class="tv-search-meta-row">
				<div class="tv-search-meta-label"><?php esc_html_e( 'Sample Search Query', 'elonix' ); ?></div>
				<div class="tv-search-meta-field">
					<input type="text" name="tv_search_preview_term" value="<?php echo esc_attr( $preview_term ); ?>" placeholder="<?php esc_attr_e( 'e.g. business', 'elonix' ); ?>" />
					<p class="description" style="margin-top: 4px; font-size: 11px;">
						<?php esc_html_e( 'Used only while previewing this Search Builder template in Elementor.', 'elonix' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save Search Builder settings.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_search_settings( $post_id ) {
		if ( ! isset( $_POST['tv_search_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tv_search_settings_nonce'] ), 'tv_search_settings_save' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'tv_search_show_header'  => '_tv_search_show_header',
			'tv_search_show_footer'  => '_tv_search_show_footer',
			'tv_search_preview_term' => '_tv_search_preview_term',
		);

		foreach ( $fields as $field => $meta_key ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, $meta_key, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
			}
		}
	}

	/**
	 * Inject duplicate action for Search Builder templates.
	 *
	 * @param array   $actions Existing row actions.
	 * @param WP_Post $post    Current post.
	 * @return array
	 */
	public function inject_duplicate_link( $actions, $post ) {
		if ( 'tv_search_template' === $post->post_type ) {
			$nonce                          = wp_create_nonce( 'tv_duplicate_search_template_' . $post->ID );
			$url                            = admin_url( 'admin-ajax.php?action=tv_duplicate_search_template&post_id=' . $post->ID . '&nonce=' . $nonce );
			$actions['tv_duplicate_search'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( $url ),
				esc_attr__( 'Duplicate this search template', 'elonix' ),
				esc_html__( 'Duplicate', 'elonix' )
			);
		}

		return $actions;
	}

	/**
	 * Handle duplicate template request.
	 */
	public function duplicate_search_template() {
		$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
		$nonce   = isset( $_GET['nonce'] ) ? sanitize_key( $_GET['nonce'] ) : '';

		if ( ! $post_id || ! wp_verify_nonce( $nonce, 'tv_duplicate_search_template_' . $post_id ) ) {
			wp_die( esc_html__( 'Security validation failed.', 'elonix' ) );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to execute this action.', 'elonix' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post || 'tv_search_template' !== $post->post_type ) {
			wp_die( esc_html__( 'Original template not found.', 'elonix' ) );
		}

		$new_post_id = wp_insert_post(
			array(
				'post_title'  => $post->post_title . esc_html__( ' (Copy)', 'elonix' ),
				'post_status' => 'draft',
				'post_type'   => 'tv_search_template',
				'post_author' => get_current_user_id(),
			)
		);

		if ( is_wp_error( $new_post_id ) ) {
			wp_die( esc_html__( 'Unable to insert duplicate template.', 'elonix' ) );
		}

		$meta_keys = array(
			'_tv_search_type',
			'_tv_search_show_header',
			'_tv_search_show_footer',
			'_tv_search_preview_term',
			'_elementor_data',
			'_elementor_template_type',
			'_elementor_edit_mode',
		);

		foreach ( $meta_keys as $key ) {
			$val = get_post_meta( $post_id, $key, true );
			if ( ! empty( $val ) || '0' === $val ) {
				update_post_meta( $new_post_id, $key, $val );
			}
		}

		wp_safe_redirect( admin_url( 'edit.php?post_type=tv_search_template' ) );
		exit;
	}
}
// phpcs:enable WordPress.Files.FileName
