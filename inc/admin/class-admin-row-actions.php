<?php
/**
 * Unified Admin Row Actions Renderer
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Admin_Row_Actions {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'post_row_actions', array( $this, 'filter_native_row_actions' ), 100, 2 );
		add_filter( 'page_row_actions', array( $this, 'filter_native_row_actions' ), 100, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	public function enqueue_styles( $hook ) {
		$screen               = get_current_screen();
		$post_type            = $screen ? $screen->post_type : '';
		$supported_post_types = array( 'tv_header', 'tv_footer', 'tv_single', 'tv_archive', 'tv_search_template', 'tv_popup', 'tv_404_template', 'tv_loop' );

		// Only enqueue on our specific list tables, header/footer page, or the edit page
		$is_valid = false;
		if ( 'edit.php' === $hook && in_array( $post_type, $supported_post_types, true ) ) {
			$is_valid = true;
		} elseif ( 'elonix_page_elonix-header-footer' === $hook ) {
			$is_valid = true;
		} elseif ( 'post.php' === $hook && in_array( $post_type, $supported_post_types, true ) ) {
			$is_valid = true;
		}

		if ( ! $is_valid ) {
			return;
		}

		// Inject the dropdown CSS to ensure it looks correct on native WP tables as well.
		$css = '
		.tv-actions-cell { display: flex; align-items: center; gap: 8px; justify-content: flex-end; }
		.tv-btn-primary.tv-btn-small { height: 28px; line-height: 26px; padding: 0 10px; font-size: 12px; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; border: none; color: #ffffff; text-decoration: none; cursor: pointer; background: #4f46e5; }
		.tv-btn-primary.tv-btn-small:hover { background: #4338ca; color:#ffffff; }
		.tv-btn-primary.tv-btn-small .dashicons { font-size: 14px; width: 14px; height: 14px; margin: 0; line-height: 1; }
		.tv-actions-dropdown-wrapper { position: relative; display: inline-block; }
		.tv-actions-dropdown-trigger { background: #ffffff; border: 1px solid #e2e8f0; color: #475569; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; padding: 0; }
		.tv-actions-dropdown-trigger:hover, .tv-actions-dropdown-wrapper.active .tv-actions-dropdown-trigger { background: #f1f5f9; border-color: #cbd5e1; color: #0f172a; }
		.tv-actions-dropdown-trigger .dashicons { font-size: 18px; width: 18px; height: 18px; margin: 0; line-height: 1; }
		.tv-actions-dropdown-menu { display: none; position: absolute; top: 100%; right: 0; z-index: 1000; margin-top: 0; min-width: 160px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1); overflow: hidden; padding: 4px 0; }
		.tv-actions-dropdown-wrapper:hover .tv-actions-dropdown-menu, .tv-actions-dropdown-wrapper.active .tv-actions-dropdown-menu { display: block; }
		.tv-dropdown-item { display: flex; align-items: center; gap: 8px; padding: 8px 12px; font-size: 12.5px; font-weight: 500; color: #334155; text-decoration: none; cursor: pointer; border: none; background: none; width: 100%; text-align: left; box-sizing: border-box; }
		.tv-dropdown-item:hover { background: #f8fafc; color: #0f172a; }
		.tv-dropdown-item .dashicons { font-size: 16px; width: 16px; height: 16px; color: #94a3b8; margin: 0; }
		.tv-dropdown-item.tv-action-trash:hover { background: #fef2f2; color: #dc2626; }
		.tv-dropdown-item.tv-action-trash:hover .dashicons { color: #dc2626; }
		';
		wp_add_inline_style( 'common', $css );

		$js = "
		jQuery(document).ready(function($) {
			if ( window.location.hash === '#tv_layout_assignments_box' ) {
				var \$box = $('#tv_layout_assignments_box');
				if ( \$box.length ) {
					\$box.removeClass('closed');
					\$box.find('.handlediv').attr('aria-expanded', 'true');
					$('html, body').animate({ scrollTop: \$box.offset().top - 50 }, 500, function() {
						\$box.css({boxShadow: '0 0 15px rgba(79, 70, 229, 0.5)', transition: 'box-shadow 0.3s ease'});
						setTimeout(function() { \$box.css('boxShadow', ''); }, 2000);
					});
				}
			}
		});
		";
		// Ensure jQuery is loaded before adding inline script
		wp_enqueue_script( 'jquery' );
		wp_add_inline_script( 'jquery', $js );
	}

	/**
	 * Generate actions array.
	 */
	/**
	 * Safe KSES wrapper that allows data-* attributes for row actions.
	 */
	public function tv_kses_post( $html ) {
		$allowed = wp_kses_allowed_html( 'post' );

		// Add data attributes to anchor tag
		if ( ! isset( $allowed['a'] ) ) {
			$allowed['a'] = array();
		}
		$allowed['a']['data-id']        = true;
		$allowed['a']['data-nonce']     = true;
		$allowed['a']['data-type']      = true;
		$allowed['a']['data-title']     = true;
		$allowed['a']['data-slug']      = true;
		$allowed['a']['data-shortcode'] = true;
		$allowed['a']['data-php']       = true;
		$allowed['a']['data-priority']  = true;
		$allowed['a']['data-status']    = true;
		$allowed['a']['data-condition'] = true;
		$allowed['a']['data-specifics'] = true;
		$allowed['a']['onclick']        = true;

		return wp_kses( $html, $allowed );
	}

	/**
	 * Generate actions array.
	 */
	public function get_actions( $post ) {
		$actions = array();
		$status  = get_post_status( $post->ID );

		$dev_mode = class_exists( 'Elonix_Settings' ) && Elonix_Settings::is_developer_mode() ? 'yes' : 'no';

		if ( 'trash' === $status ) {
			$restore_url = wp_nonce_url( admin_url( 'admin.php?action=tv_restore_layout&post_id=' . $post->ID ), 'tv_restore_layout_' . $post->ID );
			$delete_url  = wp_nonce_url( admin_url( 'admin.php?action=tv_delete_layout_permanently&post_id=' . $post->ID ), 'tv_delete_layout_permanently_' . $post->ID );

			$actions['restore'] = sprintf(
				'<a href="%s" class="tv-btn tv-btn-primary tv-btn-small" title="%s"><span class="dashicons dashicons-undo"></span> %s</a>',
				esc_url( $restore_url ),
				esc_attr__( 'Restore layout template', 'elonix' ),
				esc_html__( 'Restore', 'elonix' )
			);

			$actions['delete'] = sprintf(
				'<a href="%s" class="tv-dropdown-item tv-action-trash" title="%s" data-confirm="%s"><span class="dashicons dashicons-trash"></span> %s</a>',
				esc_url( $delete_url ),
				esc_attr__( 'Delete template permanently', 'elonix' ),
				esc_attr__( 'Are you sure you want to permanently delete this template?', 'elonix' ),
				esc_html__( 'Delete Permanently', 'elonix' )
			);

			return $actions;
		}

		$all_actions = array();

		if ( class_exists( '\Elementor\Plugin' ) ) {
			$all_actions['edit_elementor'] = sprintf(
				'<a href="%s" class="tv-btn tv-btn-primary tv-btn-small" title="%s"><span class="dashicons dashicons-edit"></span> %s</a>',
				esc_url( \Elementor\Plugin::$instance->documents->get( $post->ID )->get_edit_url() ),
				esc_attr__( 'Edit layout template with Elementor editor', 'elonix' ),
				esc_html__( 'Edit with Elementor', 'elonix' )
			);
		}

		$post_type = get_post_type( $post->ID );
		$post_name = get_post_field( 'post_name', $post->ID );

		// Map post type to its expected frontend preview slug
		$slug = $post_type;
		if ( 'tv_404_template' === $post_type ) {
			$slug = 'tv_404';
		}

		$preview_url = home_url( trailingslashit( $slug ) . trailingslashit( $post_name ) );

		$all_actions['preview'] = sprintf(
			'<a href="%s" class="tv-dropdown-item" title="%s" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-visibility"></span> %s</a>',
			esc_url( $preview_url ),
			esc_attr__( 'View layout template frontend preview', 'elonix' ),
			esc_html__( 'Preview', 'elonix' )
		);

		$all_actions['assign'] = sprintf(
			'<a href="%s" class="tv-dropdown-item tv-assign-action" title="%s"><span class="dashicons dashicons-admin-links"></span> %s</a>',
			esc_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit#tv_layout_assignments_box' ) ),
			esc_attr__( 'Assign Template Display Conditions', 'elonix' ),
			esc_html__( 'Assign', 'elonix' )
		);

		$all_actions['settings'] = sprintf(
			'<a href="%s" class="tv-dropdown-item" title="%s"><span class="dashicons dashicons-admin-generic"></span> %s</a>',
			esc_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) ),
			esc_attr__( 'Edit configuration settings', 'elonix' ),
			esc_html__( 'Settings', 'elonix' )
		);

		$shortcode = ( 'tv_header' === $post->post_type ) ? '[tv_header id="' . $post->ID . '"]' : '[tv_footer id="' . $post->ID . '"]';
		$php_code  = ( 'tv_header' === $post->post_type ) ? '<?php elonix_render_header(' . $post->ID . '); ?>' : '<?php elonix_render_footer(' . $post->ID . '); ?>';

		if ( ! in_array( $post->post_type, array( 'tv_header', 'tv_footer' ), true ) ) {
			$shortcode = '[tv_template id="' . $post->ID . '"]';
			$php_code  = '<?php echo do_shortcode(\'[tv_template id="' . $post->ID . '"]\'); ?>';
		}

		$all_actions['shortcode'] = sprintf(
			'<a href="#" class="tv-dropdown-item tv-shortcode-modal-trigger" data-id="%d" data-shortcode="%s" data-php="%s"><span class="dashicons dashicons-shortcode"></span> %s</a>',
			$post->ID,
			esc_attr( $shortcode ),
			esc_attr( $php_code ),
			esc_html__( 'Get Shortcode', 'elonix' )
		);

		$all_actions['duplicate'] = sprintf(
			'<a href="%s" class="tv-dropdown-item" title="%s"><span class="dashicons dashicons-page"></span> %s</a>',
			esc_url( wp_nonce_url( admin_url( 'admin.php?action=tv_duplicate_template&post_id=' . $post->ID ), 'tv_duplicate_template_' . $post->ID ) ),
			esc_attr__( 'Clone layout template', 'elonix' ),
			esc_html__( 'Duplicate', 'elonix' )
		);

		$all_actions['export'] = sprintf(
			'<a href="%s" class="tv-dropdown-item" title="%s"><span class="dashicons dashicons-download"></span> %s</a>',
			esc_url( wp_nonce_url( admin_url( 'admin.php?action=tv_export_template&post_id=' . $post->ID ), 'tv_export_template_' . $post->ID ) ),
			esc_attr__( 'Export layout template data to JSON file', 'elonix' ),
			esc_html__( 'Export', 'elonix' )
		);

		if ( 'yes' === $dev_mode && current_user_can( 'manage_options' ) ) {
			$all_actions['export_package'] = sprintf(
				'<a href="#" class="tv-dropdown-item tv-dev-export-package" data-id="%d" data-nonce="%s" title="%s"><span class="dashicons dashicons-archive"></span> %s</a>',
				$post->ID,
				wp_create_nonce( 'wp_rest' ),
				esc_attr__( 'Export as ThemeForest-compliant Package', 'elonix' ),
				esc_html__( 'Export Package', 'elonix' )
			);

			$all_actions['add_to_library'] = sprintf(
				'<a href="#" class="tv-dropdown-item tv-dev-add-library" data-id="%d" data-title="%s" data-type="%s" data-nonce="%s" title="%s"><span class="dashicons dashicons-plus-alt"></span> %s</a>',
				$post->ID,
				esc_attr( $post->post_title ),
				esc_attr( $post->post_type ),
				wp_create_nonce( 'wp_rest' ),
				esc_attr__( 'Add to Local Template Library', 'elonix' ),
				esc_html__( 'Add to Library', 'elonix' )
			);
		}

		$all_actions['trash'] = sprintf(
			'<a href="%s" class="tv-dropdown-item tv-action-trash" title="%s" data-confirm="%s"><span class="dashicons dashicons-trash"></span> %s</a>',
			esc_url( wp_nonce_url( admin_url( 'admin.php?action=tv_trash_layout&post_id=' . $post->ID ), 'tv_trash_layout_' . $post->ID ) ),
			esc_attr__( 'Move layout template to trash archive', 'elonix' ),
			esc_attr__( 'Move this layout template to trash?', 'elonix' ),
			esc_html__( 'Delete', 'elonix' )
		);

		// Builder Matrix (In Order)
		$matrix = array(
			'tv_header'          => array( 'edit_elementor', 'preview', 'assign', 'settings', 'shortcode', 'duplicate', 'export', 'export_package', 'add_to_library', 'trash' ),
			'tv_footer'          => array( 'edit_elementor', 'preview', 'assign', 'settings', 'shortcode', 'duplicate', 'export', 'export_package', 'add_to_library', 'trash' ),
			'tv_single'          => array( 'edit_elementor', 'preview', 'assign', 'settings', 'duplicate', 'export', 'export_package', 'add_to_library', 'trash' ),
			'tv_archive'         => array( 'edit_elementor', 'preview', 'assign', 'settings', 'duplicate', 'export', 'export_package', 'add_to_library', 'trash' ),
			'tv_search_template' => array( 'edit_elementor', 'preview', 'assign', 'settings', 'export', 'export_package', 'add_to_library', 'trash' ),
			'tv_404_template'    => array( 'edit_elementor', 'preview', 'assign', 'settings', 'export', 'export_package', 'add_to_library', 'trash' ),
			'tv_popup'           => array( 'edit_elementor', 'preview', 'assign', 'settings', 'duplicate', 'export', 'export_package', 'add_to_library', 'trash' ),
			'tv_loop'            => array( 'edit_elementor', 'preview', 'settings', 'duplicate', 'export', 'export_package', 'add_to_library', 'trash' ),
		);

		$post_type = $post->post_type;
		$supported = isset( $matrix[ $post_type ] ) ? $matrix[ $post_type ] : array();

		foreach ( $supported as $key ) {
			if ( isset( $all_actions[ $key ] ) ) {
				// Remove preview for header/footer as they are usually not standalone. But the user's updated expected examples say:
				// Header: Edit, Assign, Duplicate, Export, Export Package, Add to Library, Delete. (No Preview)
				// Footer: Same as Header.
				if ( in_array( $post_type, array( 'tv_header', 'tv_footer' ), true ) && 'preview' === $key ) {
					continue;
				}
				$actions[ $key ] = $all_actions[ $key ];
			}
		}

		return apply_filters( 'elonix/admin/row_actions', $actions, $post );
	}

	/**
	 * Render for custom HTML tables (Header & Footer).
	 */
	public function render_custom_table_actions( $post ) {
		$actions = $this->get_actions( $post );

		if ( empty( $actions ) ) {
			return;
		}

		$primary_action = '';
		if ( isset( $actions['edit_elementor'] ) ) {
			$primary_action = $actions['edit_elementor'];
			unset( $actions['edit_elementor'] );
		} elseif ( isset( $actions['restore'] ) ) {
			$primary_action = $actions['restore'];
			unset( $actions['restore'] );
		}

		echo '<div class="tv-actions-cell" style="justify-content: flex-end;">';
		do_action( 'elonix/admin/action_before_render', $post );

		if ( $primary_action ) {
			echo $this->tv_kses_post( $primary_action ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( ! empty( $actions ) ) {
			echo '<div class="tv-actions-dropdown-wrapper">';
			echo '<button type="button" class="tv-actions-dropdown-trigger" aria-label="' . esc_attr__( 'More actions', 'elonix' ) . '"><span class="dashicons dashicons-ellipsis"></span></button>';
			echo '<div class="tv-actions-dropdown-menu">';
			foreach ( $actions as $action ) {
				echo $this->tv_kses_post( $action ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</div>';
			echo '</div>';
		}

		do_action( 'elonix/admin/action_after_render', $post );
		echo '</div>';
	}

	/**
	 * Filter for native WP list tables.
	 */
	public function filter_native_row_actions( $actions, $post ) {
		$supported_types = array( 'tv_header', 'tv_footer', 'tv_single', 'tv_archive', 'tv_search_template', 'tv_popup', 'tv_404_template', 'tv_loop' );
		if ( ! in_array( $post->post_type, $supported_types, true ) ) {
			return $actions; // Preserve other post types
		}

		ob_start();
		$this->render_custom_table_actions( $post );
		$html = ob_get_clean();

		$actions['tv_actions'] = $html;

		return $actions;
	}
}
