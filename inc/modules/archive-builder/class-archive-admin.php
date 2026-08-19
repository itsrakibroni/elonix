<?php
/**
 * Elonix Archive Builder Custom Admin Metabox Configuration
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Archive_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		// Custom Columns Hooks
		add_filter( 'manage_elonix_archive_posts_columns', array( $this, 'register_custom_columns' ) );
		add_action( 'manage_elonix_archive_posts_custom_column', array( $this, 'render_custom_columns' ), 10, 2 );

		// Custom Meta Box Hooks
		add_action( 'add_meta_boxes', array( $this, 'add_archive_settings_metabox' ) );
		add_action( 'save_post_elonix_archive', array( $this, 'save_archive_settings' ) );

		// Duplicate AJAX cloner hooks
		add_action( 'wp_ajax_elonix_duplicate_archive', array( $this, 'duplicate_archive_template' ) );
	}

	/**
	 * Define custom columns for CPT list table.
	 */
	
	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_admin_assets( $hook ) {
		global $post;
		if ( ! $post || 'elonix_archive' !== $post->post_type ) {
			return;
		}

		wp_enqueue_style(
			'elonix-module-meta',
			ELONIX_ACC_URL . 'assets/admin/css/module-meta.css',
			array(),
			ELONIX_VERSION
		);
	}

	public function register_custom_columns( $columns ) {
		$new_columns = array(
			'cb'                       => $columns['cb'],
			'title'                    => $columns['title'],
			'es_archive_header_footer' => esc_html__( 'Header/Footer Theme Integration', 'elonix' ),
			'date'                     => $columns['date'],
		);
		return $new_columns;
	}

	/**
	 * Render metadata inside custom columns.
	 */
	public function render_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'es_archive_header_footer':
				$header = get_post_meta( $post_id, '_es_archive_show_header', true );
				$footer = get_post_meta( $post_id, '_es_archive_show_footer', true );

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
	public function add_archive_settings_metabox() {
		add_meta_box(
			'es_archive_settings_metabox',
			esc_html__( 'Archive Builder Settings', 'elonix' ),
			array( $this, 'render_settings_metabox' ),
			'elonix_archive',
			'normal',
			'high'
		);
	}

	/**
	 * Render settings layout template inside the meta box.
	 */
	public function render_settings_metabox( $post ) {
		// Nonce check tag
		wp_nonce_field( 'es_archive_settings_save', 'es_archive_settings_nonce' );

		// Load values
		$show_header  = get_post_meta( $post->ID, '_es_archive_show_header', true );
		$show_footer  = get_post_meta( $post->ID, '_es_archive_show_footer', true );
		$preview_type = get_post_meta( $post->ID, '_es_archive_preview_type', true );
		$preview_id   = get_post_meta( $post->ID, '_es_archive_preview_id', true );

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
		<!-- MODULE META CSS MOVED EXTERNALLY -->
		<div class="es-archive-meta-wrapper">
			
			<div class="es-archive-meta-section-title"><?php esc_html_e( 'Theme & Header/Footer Layout Options', 'elonix' ); ?></div>

			<!-- Show Header option -->
			<div class="es-archive-meta-row">
				<div class="es-archive-meta-label"><?php esc_html_e( 'Render Theme Header', 'elonix' ); ?></div>
				<div class="es-archive-meta-field">
					<select name="es_archive_show_header">
						<option value="yes" <?php selected( $show_header, 'yes' ); ?>><?php esc_html_e( 'Yes (Call native theme header / Elonix Header Builder)', 'elonix' ); ?></option>
						<option value="no" <?php selected( $show_header, 'no' ); ?>><?php esc_html_e( 'No (Clean Canvas Layout)', 'elonix' ); ?></option>
					</select>
				</div>
			</div>

			<!-- Show Footer option -->
			<div class="es-archive-meta-row">
				<div class="es-archive-meta-label"><?php esc_html_e( 'Render Theme Footer', 'elonix' ); ?></div>
				<div class="es-archive-meta-field">
					<select name="es_archive_show_footer">
						<option value="yes" <?php selected( $show_footer, 'yes' ); ?>><?php esc_html_e( 'Yes (Call native theme footer / Elonix Footer Builder)', 'elonix' ); ?></option>
						<option value="no" <?php selected( $show_footer, 'no' ); ?>><?php esc_html_e( 'No (Clean Canvas Layout)', 'elonix' ); ?></option>
					</select>
				</div>
			</div>

			<div class="es-archive-meta-section-title"><?php esc_html_e( 'Preview Context Configuration', 'elonix' ); ?></div>

			<!-- Preview context type -->
			<div class="es-archive-meta-row">
				<div class="es-archive-meta-label"><?php esc_html_e( 'Sample Post Preview Type', 'elonix' ); ?></div>
				<div class="es-archive-meta-field">
					<select name="es_archive_preview_type">
						<option value="post" <?php selected( $preview_type, 'post' ); ?>><?php esc_html_e( 'Standard Sample Post', 'elonix' ); ?></option>
						<option value="category" <?php selected( $preview_type, 'category' ); ?>><?php esc_html_e( 'Category Sample Feed', 'elonix' ); ?></option>
						<option value="tag" <?php selected( $preview_type, 'tag' ); ?>><?php esc_html_e( 'Tag Sample Feed', 'elonix' ); ?></option>
						<option value="author" <?php selected( $preview_type, 'author' ); ?>><?php esc_html_e( 'Author Page Feed', 'elonix' ); ?></option>
						<option value="cpt" <?php selected( $preview_type, 'cpt' ); ?>><?php esc_html_e( 'Custom Post Type Feed', 'elonix' ); ?></option>
					</select>
				</div>
			</div>

			<!-- Preview target ID -->
			<div class="es-archive-meta-row">
				<div class="es-archive-meta-label"><?php esc_html_e( 'Sample Query ID / Slug', 'elonix' ); ?></div>
				<div class="es-archive-meta-field">
					<input type="text" name="es_archive_preview_id" value="<?php echo esc_attr( $preview_id ); ?>" placeholder="<?php esc_attr_e( 'e.g. 1 (post/term ID) or portfolio (CPT slug)', 'elonix' ); ?>" />
				</div>
			</div>

		</div>

		</div>
		<?php
	}

	/**
	 * Save Metabox Settings safely.
	 */
	public function save_archive_settings( $post_id ) {
		// Nonce check validation
		if ( ! isset( $_POST['es_archive_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['es_archive_settings_nonce'] ), 'es_archive_settings_save' ) ) {
			return;
		}

		// Autosave check
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Capability checks
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save fields safely
		if ( isset( $_POST['es_archive_show_header'] ) ) {
			update_post_meta( $post_id, '_es_archive_show_header', sanitize_text_field( wp_unslash( $_POST['es_archive_show_header'] ) ) );
		}
		if ( isset( $_POST['es_archive_show_footer'] ) ) {
			update_post_meta( $post_id, '_es_archive_show_footer', sanitize_text_field( wp_unslash( $_POST['es_archive_show_footer'] ) ) );
		}
		if ( isset( $_POST['es_archive_preview_type'] ) ) {
			update_post_meta( $post_id, '_es_archive_preview_type', sanitize_text_field( wp_unslash( $_POST['es_archive_preview_type'] ) ) );
		}
		if ( isset( $_POST['es_archive_preview_id'] ) ) {
			update_post_meta( $post_id, '_es_archive_preview_id', sanitize_text_field( wp_unslash( $_POST['es_archive_preview_id'] ) ) );
		}
	}

	/**
	 * Inject dynamic "Duplicate" link inside row actions of elonix_archive list table.
	 */
	public function inject_duplicate_link( $actions, $post ) {
		if ( 'elonix_archive' === $post->post_type ) {
			$nonce                   = wp_create_nonce( 'es_duplicate_archive_' . $post->ID );
			$url                     = admin_url( 'admin-ajax.php?action=elonix_duplicate_archive&post_id=' . $post->ID . '&nonce=' . $nonce );
			$actions['es_duplicate'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( $url ),
				esc_attr__( 'Duplicate this archive template', 'elonix' ),
				esc_html__( 'Duplicate', 'elonix' )
			);
		}
		return $actions;
	}

	/**
	 * Handle Duplicate Template request.
	 */
	public function duplicate_archive_template() {
		$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
		$nonce   = isset( $_GET['nonce'] ) ? sanitize_key( $_GET['nonce'] ) : '';

		if ( ! $post_id || ! wp_verify_nonce( $nonce, 'es_duplicate_archive_' . $post_id ) ) {
			wp_die( esc_html__( 'Security validation failed.', 'elonix' ) );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to execute this action.', 'elonix' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post || 'elonix_archive' !== $post->post_type ) {
			wp_die( esc_html__( 'Original template not found.', 'elonix' ) );
		}

		// Create duplicate post object
		$new_post_args = array(
			'post_title'  => $post->post_title . esc_html__( ' (Copy)', 'elonix' ),
			'post_status' => 'draft',
			'post_type'   => 'elonix_archive',
			'post_author' => get_current_user_id(),
		);

		$new_post_id = wp_insert_post( $new_post_args );

		if ( is_wp_error( $new_post_id ) ) {
			wp_die( esc_html__( 'Unable to insert duplicate template.', 'elonix' ) );
		}

		// Replicate settings meta keys
		$meta_keys = array(
			'_es_archive_type',
			'_es_archive_target_ids',
			'_es_archive_show_header',
			'_es_archive_show_footer',
			'_es_archive_preview_type',
			'_es_archive_preview_id',
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

		// Redirect back to templates list
		wp_safe_redirect( admin_url( 'edit.php?post_type=elonix_archive' ) );
		exit;
	}
}
