<?php
/**
 * Elonix Single Builder Custom Admin Metabox Configuration
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Single_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Custom Columns Hooks
		add_filter( 'manage_tv_single_posts_columns', array( $this, 'register_custom_columns' ) );
		add_action( 'manage_tv_single_posts_custom_column', array( $this, 'render_custom_columns' ), 10, 2 );

		// Custom Meta Box Hooks
		add_action( 'add_meta_boxes', array( $this, 'add_single_settings_metabox' ) );
		add_action( 'save_post_tv_single', array( $this, 'save_single_settings' ) );

		// Duplicate AJAX cloner hooks
		add_action( 'wp_ajax_tv_duplicate_single', array( $this, 'duplicate_single_template' ) );
	}

	/**
	 * Define custom columns for CPT list table.
	 */
	public function register_custom_columns( $columns ) {
		$new_columns = array(
			'cb'                      => $columns['cb'],
			'title'                   => $columns['title'],
			'tv_single_header_footer' => esc_html__( 'Header/Footer Theme Integration', 'elonix' ),
			'date'                    => $columns['date'],
		);
		return $new_columns;
	}

	/**
	 * Render metadata inside custom columns.
	 */
	public function render_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'tv_single_header_footer':
				$header = get_post_meta( $post_id, '_tv_single_show_header', true );
				$footer = get_post_meta( $post_id, '_tv_single_show_footer', true );

				$h_status = ( 'no' === $header ) ? esc_html__( 'Disabled', 'elonix' ) : esc_html__( 'Enabled', 'elonix' );
				$f_status = ( 'no' === $footer ) ? esc_html__( 'Disabled', 'elonix' ) : esc_html__( 'Enabled', 'elonix' );

				/* translators: %1$s: string */
				echo esc_html( sprintf( __( 'Header: %1$s | Footer: %2$s', 'elonix' ), $h_status, $f_status ) );
				break;
		}
	}

	/**
	 * Register Custom Settings Meta Box.
	 */
	public function add_single_settings_metabox() {
		add_meta_box(
			'tv_single_settings_metabox',
			esc_html__( 'Single Builder Settings', 'elonix' ),
			array( $this, 'render_settings_metabox' ),
			'tv_single',
			'normal',
			'high'
		);
	}

	/**
	 * Render settings layout template inside the meta box.
	 */
	public function render_settings_metabox( $post ) {
		// Nonce check tag
		wp_nonce_field( 'tv_single_settings_save', 'tv_single_settings_nonce' );

		// Load values
		$type         = get_post_meta( $post->ID, '_tv_single_type', true );
		$target_ids   = get_post_meta( $post->ID, '_tv_single_target_ids', true );
		$exclude_type = get_post_meta( $post->ID, '_tv_single_exclude_type', true );
		$exclude_ids  = get_post_meta( $post->ID, '_tv_single_exclude_ids', true );
		$show_header  = get_post_meta( $post->ID, '_tv_single_show_header', true );
		$show_footer  = get_post_meta( $post->ID, '_tv_single_show_footer', true );
		$preview_type = get_post_meta( $post->ID, '_tv_single_preview_type', true );
		$preview_id   = get_post_meta( $post->ID, '_tv_single_preview_id', true );

		if ( empty( $type ) ) {
			$type = 'all_singular';
		}
		if ( empty( $show_header ) ) {
			$show_header = 'yes';
		}
		if ( empty( $show_footer ) ) {
			$show_footer = 'yes';
		}
		if ( empty( $preview_type ) ) {
			$preview_type = 'post';
		}
		?>
		<style>
			.tv-single-meta-row { display: flex; align-items: flex-start; padding: 14px 0; border-bottom: 1px solid #eee; }
			.tv-single-meta-label { width: 220px; font-weight: bold; padding-top: 4px; }
			.tv-single-meta-field { flex: 1; }
			.tv-single-meta-field select, .tv-single-meta-field input[type="text"], .tv-single-meta-field input[type="number"] { width: 100%; max-width: 400px; }
			.tv-single-meta-section-title { font-size: 14px; font-weight: bold; background: #f8fafc; padding: 8px 12px; margin: 15px 0 5px 0; border-left: 4px solid #3b82f6; }
			.tv-desc { font-size: 12px; color: #666; margin-top: 4px; display: block; }
		</style>
		<div class="tv-single-meta-wrapper">
			
			<div class="tv-single-meta-section-title"><?php esc_html_e( 'Theme Canvas Engine', 'elonix' ); ?></div>

			<!-- Header Support -->
			<div class="tv-single-meta-row">
				<div class="tv-single-meta-label"><?php esc_html_e( 'Display Theme Header', 'elonix' ); ?></div>
				<div class="tv-single-meta-field">
					<select name="tv_single_show_header">
						<option value="yes" <?php selected( $show_header, 'yes' ); ?>><?php esc_html_e( 'Yes, show theme header', 'elonix' ); ?></option>
						<option value="no" <?php selected( $show_header, 'no' ); ?>><?php esc_html_e( 'No, use blank canvas', 'elonix' ); ?></option>
					</select>
				</div>
			</div>

			<!-- Footer Support -->
			<div class="tv-single-meta-row">
				<div class="tv-single-meta-label"><?php esc_html_e( 'Display Theme Footer', 'elonix' ); ?></div>
				<div class="tv-single-meta-field">
					<select name="tv_single_show_footer">
						<option value="yes" <?php selected( $show_footer, 'yes' ); ?>><?php esc_html_e( 'Yes, show theme footer', 'elonix' ); ?></option>
						<option value="no" <?php selected( $show_footer, 'no' ); ?>><?php esc_html_e( 'No, use blank canvas', 'elonix' ); ?></option>
					</select>
				</div>
			</div>

			<div class="tv-single-meta-section-title"><?php esc_html_e( 'Editor Preview Settings', 'elonix' ); ?></div>

			<!-- Editor Mockup Target Type -->
			<div class="tv-single-meta-row">
				<div class="tv-single-meta-label"><?php esc_html_e( 'Preview Layout Using', 'elonix' ); ?></div>
				<div class="tv-single-meta-field">
					<select name="tv_single_preview_type">
						<option value="post" <?php selected( $preview_type, 'post' ); ?>><?php esc_html_e( 'Standard Post', 'elonix' ); ?></option>
						<option value="page" <?php selected( $preview_type, 'page' ); ?>><?php esc_html_e( 'Standard Page', 'elonix' ); ?></option>
						<option value="cpt" <?php selected( $preview_type, 'cpt' ); ?>><?php esc_html_e( 'Custom Post Type', 'elonix' ); ?></option>
					</select>
				</div>
			</div>

			<!-- Editor Mockup Target Value -->
			<div class="tv-single-meta-row" style="border-bottom: none;">
				<div class="tv-single-meta-label"><?php esc_html_e( 'Preview Target ID / Name', 'elonix' ); ?></div>
				<div class="tv-single-meta-field">
					<input type="text" name="tv_single_preview_id" value="<?php echo esc_attr( $preview_id ); ?>" placeholder="e.g. 45 or product" />
					<span class="tv-desc"><?php esc_html_e( 'Specify a Post ID, Page ID, or CPT Slug to populate standard Elementor widgets in Editor.', 'elonix' ); ?></span>
				</div>
			</div>

		</div>
		<?php
	}

	/**
	 * Save Meta Box configuration.
	 */
	public function save_single_settings( $post_id ) {
		// Nonce check
		if ( ! isset( $_POST['tv_single_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tv_single_settings_nonce'] ) ), 'tv_single_settings_save' ) ) {
			return $post_id;
		}

		// Capabilities check
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Prevent saving during autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		$fields = array(
			'_tv_single_show_header'  => isset( $_POST['tv_single_show_header'] ) ? sanitize_text_field( wp_unslash( $_POST['tv_single_show_header'] ) ) : 'yes',
			'_tv_single_show_footer'  => isset( $_POST['tv_single_show_footer'] ) ? sanitize_text_field( wp_unslash( $_POST['tv_single_show_footer'] ) ) : 'yes',
			'_tv_single_preview_type' => isset( $_POST['tv_single_preview_type'] ) ? sanitize_text_field( wp_unslash( $_POST['tv_single_preview_type'] ) ) : 'post',
			'_tv_single_preview_id'   => isset( $_POST['tv_single_preview_id'] ) ? sanitize_text_field( wp_unslash( $_POST['tv_single_preview_id'] ) ) : '',
		);

		foreach ( $fields as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	}

	/**
	 * Inject duplicate template action link in list table.
	 */
	public function inject_duplicate_link( $actions, $post ) {
		if ( current_user_can( 'edit_posts' ) && 'tv_single' === $post->post_type ) {
			$nonce                = wp_create_nonce( 'tv_duplicate_single_nonce' );
			$url                  = admin_url( 'admin-ajax.php?action=tv_duplicate_single&post_id=' . $post->ID . '&_wpnonce=' . $nonce );
			$actions['duplicate'] = '<a href="' . esc_url( $url ) . '" title="' . esc_attr__( 'Duplicate this single template', 'elonix' ) . '" rel="permalink">' . esc_html__( 'Duplicate', 'elonix' ) . '</a>';
		}
		return $actions;
	}

	/**
	 * Process Duplicate Single action.
	 */
	public function duplicate_single_template() {
		// Nonce & permission check
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'tv_duplicate_single_nonce' ) ) {
			wp_die( esc_html__( 'Security validation failed.', 'elonix' ) );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'elonix' ) );
		}

		$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
		if ( empty( $post_id ) ) {
			wp_die( esc_html__( 'Invalid Post ID.', 'elonix' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post || 'tv_single' !== $post->post_type ) {
			wp_die( esc_html__( 'Invalid source post.', 'elonix' ) );
		}

		// Create clone array
		$new_post_args = array(
			'post_type'      => $post->post_type,
			'post_title'     => $post->post_title . ' ' . esc_html__( '(Copy)', 'elonix' ),
			'post_content'   => $post->post_content,
			'post_status'    => 'draft',
			'post_author'    => get_current_user_id(),
			'post_name'      => $post->post_name . '-copy',
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
		);

		// Insert clone
		$new_post_id = wp_insert_post( $new_post_args );

		if ( ! is_wp_error( $new_post_id ) && $new_post_id > 0 ) {
			// Duplicate elementor metadata
			$elementor_data = get_post_meta( $post_id, '_elementor_data', true );
			if ( $elementor_data ) {
				update_post_meta( $new_post_id, '_elementor_data', wp_slash( $elementor_data ) );
			}
			$elementor_edit_mode = get_post_meta( $post_id, '_elementor_edit_mode', true );
			if ( $elementor_edit_mode ) {
				update_post_meta( $new_post_id, '_elementor_edit_mode', $elementor_edit_mode );
			}
			$elementor_version = get_post_meta( $post_id, '_elementor_version', true );
			if ( $elementor_version ) {
				update_post_meta( $new_post_id, '_elementor_version', $elementor_version );
			}

			// Duplicate custom module meta
			$meta_keys = array(
				'_tv_single_type',
				'_tv_single_target_ids',
				'_tv_single_exclude_type',
				'_tv_single_exclude_ids',
				'_tv_single_show_header',
				'_tv_single_show_footer',
				'_tv_single_preview_type',
				'_tv_single_preview_id',
			);

			foreach ( $meta_keys as $key ) {
				$val = get_post_meta( $post_id, $key, true );
				if ( ! empty( $val ) ) {
					update_post_meta( $new_post_id, $key, wp_slash( $val ) );
				}
			}

			// Redirect to edit screen
			wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
			exit;
		}

		wp_die( esc_html__( 'Failed to duplicate the template.', 'elonix' ) );
	}
}
