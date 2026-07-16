<?php
/**
 * Elonix – Toolkit for Elementor Header & Footer Admin UI Upgrade
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Admin_UI {

	/**
	 * Display conditions instance.
	 *
	 * @var Elonix_Display_Conditions
	 */
	private $display_conditions;

	/**
	 * Constructor.
	 *
	 * @param Elonix_Display_Conditions $display_conditions Display conditions instance.
	 */
	public function __construct( $display_conditions ) {
		$this->display_conditions = $display_conditions;

		// Add custom columns to all builder CPT list tables
		$post_types = array( 'tv_header', 'tv_footer', 'tv_single', 'tv_archive', 'tv_search_template' );
		foreach ( $post_types as $pt ) {
			add_filter( "manage_{$pt}_posts_columns", array( $this, 'register_custom_columns' ), 11 );
			add_action( "manage_{$pt}_posts_custom_column", array( $this, 'render_custom_columns_data' ), 11, 2 );
		}

		// Quick and Bulk actions hook
		add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
		// Load assets on builder dashboard pages
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_assets' ) );

		// Add persistent admin navigation bar above post editor
		add_action( 'admin_notices', array( $this, 'add_settings_workflow_navigation' ) );
	}

	/**
	 * Define custom columns for CPT lists.
	 */
	public function register_custom_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $title ) {
			if ( 'date' === $key ) {
				$new_columns['tv_display_conditions'] = esc_html__( 'Display Conditions', 'elonix' );
				$new_columns['tv_shortcode']          = esc_html__( 'Shortcode', 'elonix' );
				$new_columns['tv_priority']           = esc_html__( 'Priority', 'elonix' );
			}
			$new_columns[ $key ] = $title;
		}
		return $new_columns;
	}

	/**
	 * Enqueue dashboard assets (specifically Select2).
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_dashboard_assets( $hook ) {
		// Only load on Elonix Header & Footer Builder pages
		if ( false === strpos( $hook, 'elonix-header-footer' ) ) {
			return;
		}

		// Prevent duplicate Select2 loading
		if ( ! wp_script_is( 'elementor-select2', 'enqueued' ) && ! wp_script_is( 'elonix-select2', 'enqueued' ) && ! wp_script_is( 'select2', 'enqueued' ) ) {
			if ( class_exists( 'Elonix_Target_Rules' ) ) {
				Elonix_Target_Rules::enqueue_assets();
			}
		}
	}

	/**
	 * Output persistent admin navigation bar at the top of the post edit screen for Elonix CPTs.
	 */
	public function add_settings_workflow_navigation() {
		global $pagenow;
		if ( ! is_admin() || ! in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
		$post_id   = isset( $_GET['post'] ) ? intval( wp_unslash( $_GET['post'] ) ) : ( isset( $_POST['post_ID'] ) ? intval( wp_unslash( $_POST['post_ID'] ) ) : 0 );
		$post_type = '';
		if ( $post_id ) {
			$post_type = get_post_type( $post_id );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
		} elseif ( isset( $_GET['post_type'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
			$post_type = sanitize_key( wp_unslash( $_GET['post_type'] ) );
		}

		if ( ! in_array( $post_type, array( 'tv_header', 'tv_footer' ), true ) ) {
			return;
		}

		// Retrieve post details
		$post_title = $post_id ? get_the_title( $post_id ) : esc_html__( 'New Template', 'elonix' );
		$type_label = ( 'tv_header' === $post_type ) ? esc_html__( 'Header', 'elonix' ) : esc_html__( 'Footer', 'elonix' );

		// Preserve tab state URL
		$back_url = admin_url( 'admin.php?page=elonix-header-footer' );
		if ( 'tv_header' === $post_type ) {
			$back_url = add_query_arg( 'tv_type', 'tv_header', $back_url );
		} elseif ( 'tv_footer' === $post_type ) {
			$back_url = add_query_arg( 'tv_type', 'tv_footer', $back_url );
		}

		// Secure preview URL
		$preview_url = $post_id ? add_query_arg( 'tv_preview', $post_id, home_url( '/' ) ) : '';

		?>
		<div class="tv-admin-nav-bar" style="background: #fff; border: 1px solid #ccd0d4; padding: 15px 20px; margin: 10px 20px 20px 0; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;">
			<div class="tv-nav-left" style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #64748b;">
				<span style="font-weight: 600; color: #1e293b;"><?php esc_html_e( 'Elonix Header & Footer Builder', 'elonix' ); ?></span>
				<span style="color: #cbd5e1;">&gt;</span>
				<span style="color: #64748b; font-weight: 500;"><?php echo esc_html( $type_label ); ?>:</span>
				<span style="color: #0f172a; font-weight: 600;"><?php echo esc_html( $post_title ); ?></span>
			</div>

			<div class="tv-nav-right" style="display: flex; align-items: center; gap: 12px; margin-top: 5px;">
				<a href="<?php echo esc_url( $back_url ); ?>" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 5px; height: 32px; font-weight: 500;">
					<span class="dashicons dashicons-arrow-left-alt" style="font-size: 16px; width: 16px; height: 16px; margin-top: 1px;"></span>
					<?php esc_html_e( 'Back to Builder', 'elonix' ); ?>
				</a>
				<?php if ( $preview_url ) : ?>
					<a href="<?php echo esc_url( $preview_url ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 5px; height: 32px; font-weight: 500; background: #22c55e; border-color: #16a34a; box-shadow: none; text-shadow: none;">
						<span class="dashicons dashicons-visibility" style="font-size: 16px; width: 16px; height: 16px; margin-top: 1px; color: #fff;"></span>
						<?php esc_html_e( 'Preview Template', 'elonix' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<style>
			/* Enforce visibility on block editor and Gutenberg workspace */
			.tv-admin-nav-bar {
				z-index: 99999;
				position: relative;
			}
			/* Styling adjustments for preview button hover */
			.tv-admin-nav-bar .button-primary:hover,
			.tv-admin-nav-bar .button-primary:focus {
				background: #16a34a !important;
				border-color: #15803d !important;
				color: #fff !important;
			}
		</style>
		<?php
	}

	/**
	 * Map display conditions to modern Dashicons.
	 */
	private function get_location_with_icon( $key ) {
		$label      = Elonix_Target_Rules::get_location_by_key( $key );
		$icon_class = 'dashicons-media-document'; // Default icon

		if ( 'basic-global' === $key ) {
			$icon_class = 'dashicons-admin-site';
		} elseif ( in_array( $key, array( 'special-front-page', 'front-page', 'home' ), true ) ) {
			$icon_class = 'dashicons-admin-home';
		} elseif ( strpos( $key, 'archive' ) !== false || strpos( $key, 'tax' ) !== false || strpos( $key, 'category' ) !== false || strpos( $key, 'post_tag' ) !== false ) {
			$icon_class = 'dashicons-category';
		} elseif ( strpos( $key, 'search' ) !== false ) {
			$icon_class = 'dashicons-search';
		} elseif ( strpos( $key, '404' ) !== false ) {
			$icon_class = 'dashicons-warning';
		}

		return '<span class="dashicons ' . esc_attr( $icon_class ) . '" style="font-size:14px; width:14px; height:14px; vertical-align:text-bottom; margin-right:4px;"></span>' . esc_html( $label );
	}

	/**
	 * Render display conditions optimized for the dashboard.
	 *
	 * @param int $post_id Post ID.
	 */
	private function render_dashboard_display_conditions( $post_id ) {
		$include = Elonix_Target_Rules::normalize_rules( get_post_meta( $post_id, '_tv_target_include_locations', true ) );
		$exclude = Elonix_Target_Rules::normalize_rules( get_post_meta( $post_id, '_tv_target_exclude_locations', true ) );
		$roles   = get_post_meta( $post_id, '_tv_target_user_roles', true );
		$outputs = array();

		if ( ! empty( $include ) ) {
			$lbls = array();
			foreach ( $include as $row ) {
				$r = $row['rule'];
				if ( strpos( $r, 'specific' ) !== false && ! empty( $row['specific'] ) ) {
					foreach ( $row['specific'] as $s ) {
						$lbls[] = $this->get_location_with_icon( $s );
					}
				} else {
					$lbls[] = $this->get_location_with_icon( $r );
				}
			}
			$outputs[] = '<span class="tv-dashboard-condition-inc">' . implode( ', ', $lbls ) . '</span>';
		}

		if ( ! empty( $exclude ) ) {
			$lbls = array();
			foreach ( $exclude as $row ) {
				$r = $row['rule'];
				if ( strpos( $r, 'specific' ) !== false && ! empty( $row['specific'] ) ) {
					foreach ( $row['specific'] as $s ) {
						$lbls[] = $this->get_location_with_icon( $s );
					}
				} else {
					$lbls[] = $this->get_location_with_icon( $r );
				}
			}
			$outputs[] = '<span class="tv-dashboard-condition-exc"><span class="dashicons dashicons-dismiss" style="font-size:14px; width:14px; height:14px; color:var(--tv-danger); vertical-align:text-bottom; margin-right:4px;"></span>' . implode( ', ', $lbls ) . '</span>';
		}

		if ( is_array( $roles ) && ! empty( $roles ) ) {
			$lbls = array();
			foreach ( $roles as $role ) {
				$lbls[] = Elonix_Target_Rules::get_user_by_key( $role );
			}
			$outputs[] = '<span class="tv-dashboard-condition-roles"><span class="dashicons dashicons-groups" style="font-size:14px; width:14px; height:14px; color:var(--tv-primary); vertical-align:text-bottom; margin-right:4px;"></span>' . esc_html( implode( ', ', $lbls ) ) . '</span>';
		}

		if ( empty( $outputs ) ) {
			echo '<span class="tv-dashboard-condition-empty" style="color: var(--tv-slate-400); font-style: italic; font-size: 12.5px;">' . esc_html__( 'No conditions set (Draft)', 'elonix' ) . '</span>';
		} else {
			echo '<div class="tv-dashboard-conditions-list" style="font-size: 13px; font-weight: 500; color: var(--tv-slate-600); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">' . implode( ' <span class="tv-meta-divider" style="color: var(--tv-slate-200); font-size: 8px;">•</span> ', $outputs ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Render custom column contents.
	 */
	public function render_custom_columns_data( $column, $post_id ) {
		switch ( $column ) {
			case 'tv_priority':
				$priority = get_post_meta( $post_id, '_tv_priority', true );
				echo esc_html( '' === $priority ? '0' : $priority );
				break;

			case 'tv_shortcode':
				$post_type = get_post_type( $post_id );
				$shortcode = '';
				if ( 'tv_header' === $post_type ) {
					$shortcode = '[tv_header id="' . $post_id . '"]';
				} elseif ( 'tv_footer' === $post_type ) {
					$shortcode = '[tv_footer id="' . $post_id . '"]';
				}
				if ( ! empty( $shortcode ) ) {
					?>
					<div class="tv-shortcode-wrapper" style="display:flex; align-items:center; gap:8px;">
						<code class="tv-shortcode-code" style="font-family:monospace; background:var(--tv-slate-100); padding:4px 8px; border-radius:6px; border:1px solid var(--tv-slate-200); font-size:12px; font-weight:600; color:var(--tv-slate-800); white-space:nowrap;"><?php echo esc_html( $shortcode ); ?></code>
						<button type="button" class="tv-btn tv-btn-secondary tv-copy-btn" style="height: 28px; padding: 0 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px; border-radius: 6px; cursor: pointer; background:var(--tv-slate-100); border:1px solid var(--tv-slate-250); color:var(--tv-slate-700);" data-copy-text="<?php echo esc_attr( $shortcode ); ?>" data-success-msg="<?php echo esc_attr__( 'Shortcode copied', 'elonix' ); ?>">
							<span class="dashicons dashicons-admin-page" style="font-size:14px; width:14px; height:14px; margin:0;"></span>
							<?php esc_html_e( 'Copy', 'elonix' ); ?>
						</button>
					</div>
					<?php
				} else {
					echo '<span style="color:var(--tv-slate-400);">-</span>';
				}
				break;

			case 'tv_display_conditions':
				$include = Elonix_Target_Rules::normalize_rules( get_post_meta( $post_id, '_tv_target_include_locations', true ) );
				$exclude = Elonix_Target_Rules::normalize_rules( get_post_meta( $post_id, '_tv_target_exclude_locations', true ) );
				$roles   = get_post_meta( $post_id, '_tv_target_user_roles', true );
				$outputs = array();

				if ( ! empty( $include ) ) {
					$lbls = array();
					foreach ( $include as $row ) {
						$r = $row['rule'];
						if ( strpos( $r, 'specific' ) !== false && ! empty( $row['specific'] ) ) {
							foreach ( $row['specific'] as $s ) {
								$lbls[] = $this->get_location_with_icon( $s );
							}
						} else {
							$lbls[] = $this->get_location_with_icon( $r );
						}
					}
					$outputs[] = '<div class="tv-condition-pill"><span class="tv-condition-badge-inc">' . esc_html__( 'Include', 'elonix' ) . '</span> ' . implode( ', ', $lbls ) . '</div>';
				}

				if ( ! empty( $exclude ) ) {
					$lbls = array();
					foreach ( $exclude as $row ) {
						$r = $row['rule'];
						if ( strpos( $r, 'specific' ) !== false && ! empty( $row['specific'] ) ) {
							foreach ( $row['specific'] as $s ) {
								$lbls[] = $this->get_location_with_icon( $s );
							}
						} else {
							$lbls[] = $this->get_location_with_icon( $r );
						}
					}
					$outputs[] = '<div class="tv-condition-pill"><span class="tv-condition-badge-exc">' . esc_html__( 'Exclude', 'elonix' ) . '</span> ' . implode( ', ', $lbls ) . '</div>';
				}

				if ( is_array( $roles ) && ! empty( $roles ) ) {
					$lbls = array();
					foreach ( $roles as $role ) {
						$lbls[] = Elonix_Target_Rules::get_user_by_key( $role );
					}
					$outputs[] = '<div class="tv-condition-pill"><span style="background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; border-radius: 4px; padding: 2px 4px; font-size: 9px; font-weight: 700; text-transform: uppercase;">Audience</span> <span class="dashicons dashicons-groups" style="font-size:14px; width:14px; height:14px; vertical-align:text-bottom; margin-right:4px;"></span>' . esc_html( implode( ', ', $lbls ) ) . '</div>';
				}

				if ( empty( $outputs ) ) {
					echo '<span style="color: var(--tv-slate-400); font-style: italic; font-size: 12px;">' . esc_html__( 'No conditions set (Draft)', 'elonix' ) . '</span>';
				} else {
					echo '<div class="tv-display-condition-wrapper">' . implode( '', $outputs ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				break;
		}
	}

	/**
	 * Custom Row Actions links in CPT tables.
	 */
	public function custom_row_actions( $actions, $post ) {
		if ( ! in_array( $post->post_type, array( 'tv_header', 'tv_footer' ), true ) ) {
			return $actions;
		}

		// Edit with Elementor link
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$editor_url                = \Elementor\Plugin::$instance->documents->get( $post->ID )->get_edit_url();
			$actions['edit_elementor'] = sprintf(
				'<a href="%s" style="color: #6c5ce7; font-weight: bold;">%s</a>',
				esc_url( $editor_url ),
				esc_html__( 'Edit with Elementor', 'elonix' )
			);
		}

		// Duplicate Action
		$duplicate_url        = wp_nonce_url(
			admin_url( 'admin.php?action=tv_duplicate_template&post_id=' . $post->ID ),
			'tv_duplicate_template_' . $post->ID
		);
		$actions['duplicate'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $duplicate_url ),
			esc_html__( 'Duplicate', 'elonix' )
		);

		// Export Action
		$export_url        = wp_nonce_url(
			admin_url( 'admin.php?action=tv_export_template&post_id=' . $post->ID ),
			'tv_export_template_' . $post->ID
		);
		$actions['export'] = sprintf(
			'<a href="%s" style="color: #10b981;">%s</a>',
			esc_url( $export_url ),
			esc_html__( 'Export', 'elonix' )
		);

		return $actions;
	}

	/**
	 * Handle admin actions.
	 */
	public function handle_admin_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

		// 1. BULK ACTIONS
		if ( isset( $_POST['tv_bulk_action_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tv_bulk_action_nonce_field'] ) ), 'tv_bulk_action' ) ) {
			$bulk_action = isset( $_POST['tv_bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['tv_bulk_action'] ) ) : '';
			$layout_ids  = isset( $_POST['layouts'] ) ? array_map( 'intval', $_POST['layouts'] ) : array();

			// Fallback bottom bulk action
			if ( empty( $bulk_action ) && isset( $_POST['tv_bulk_action_bottom'] ) ) {
				$bulk_action = sanitize_text_field( wp_unslash( $_POST['tv_bulk_action_bottom'] ) );
			}

			if ( ! empty( $bulk_action ) && ! empty( $layout_ids ) ) {
				$status_msg = 'bulk_updated';
				$count      = count( $layout_ids );
				foreach ( $layout_ids as $id ) {
					if ( 'activate' === $bulk_action ) {
						wp_update_post(
							array(
								'ID'          => $id,
								'post_status' => 'publish',
							)
						);
					} elseif ( 'deactivate' === $bulk_action ) {
						wp_update_post(
							array(
								'ID'          => $id,
								'post_status' => 'draft',
							)
						);
					} elseif ( 'trash' === $bulk_action ) {
						wp_trash_post( $id );
						$status_msg = 'deleted';
					} elseif ( 'restore' === $bulk_action ) {
						wp_untrash_post( $id );
						$status_msg = 'restored';
					} elseif ( 'delete_permanently' === $bulk_action ) {
						wp_delete_post( $id, true );
						$status_msg = 'deleted_permanently';
					}
				}
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&tv_status=' . $status_msg . '&count=' . $count ) );
				exit;
			}
		}

		// 2. DUPLICATE ACTION
		if ( 'tv_duplicate_template' === $action ) {
			$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
			if ( ! $post_id || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'tv_duplicate_template_' . $post_id ) ) {
				return;
			}

			$new_id = $this->duplicate_template( $post_id );
			if ( $new_id ) {
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&tv_status=duplicated&count=1' ) );
				exit;
			}
		}

		// 3. EXPORT SINGLE ACTION
		if ( 'tv_export_template' === $action ) {
			$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
			if ( ! $post_id || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'tv_export_template_' . $post_id ) ) {
				return;
			}

			$post = get_post( $post_id );
			if ( ! $post ) {
				return;
			}

			$export_data = array(
				'original_id' => $post->ID,
				'title'       => $post->post_title,
				'content'     => $post->post_content,
				'post_type'   => $post->post_type,
				'meta'        => array(
					'_tv_priority'                 => get_post_meta( $post->ID, '_tv_priority', true ),
					'_tv_target_include_locations' => get_post_meta( $post->ID, '_tv_target_include_locations', true ),
					'_tv_target_exclude_locations' => get_post_meta( $post->ID, '_tv_target_exclude_locations', true ),
					'_tv_target_user_roles'        => get_post_meta( $post->ID, '_tv_target_user_roles', true ),
					'_elementor_edit_mode'         => get_post_meta( $post->ID, '_elementor_edit_mode', true ),
					'_elementor_template_type'     => get_post_meta( $post->ID, '_elementor_template_type', true ),
					'_elementor_data'              => get_post_meta( $post->ID, '_elementor_data', true ),
				),
			);

			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/json' );
			header( 'Content-Disposition: attachment; filename="tv-layout-' . sanitize_title( $post->post_title ) . '.json"' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate' );
			header( 'Pragma: public' );
			echo wp_json_encode( $export_data );
			exit;
		}

		// 4. EXPORT ALL ACTION
		if ( 'tv_export_all_templates' === $action ) {
			$posts = get_posts(
				array(
					'post_type'      => array( 'tv_header', 'tv_footer' ),
					'post_status'    => array( 'publish', 'draft' ),
					'posts_per_page' => -1,
				)
			);

			$export_data = array();
			foreach ( $posts as $post ) {
				$export_data[] = array(
					'original_id' => $post->ID,
					'title'       => $post->post_title,
					'content'     => $post->post_content,
					'post_type'   => $post->post_type,
					'meta'        => array(
						'_tv_priority'                 => get_post_meta( $post->ID, '_tv_priority', true ),
						'_tv_target_include_locations' => get_post_meta( $post->ID, '_tv_target_include_locations', true ),
						'_tv_target_exclude_locations' => get_post_meta( $post->ID, '_tv_target_exclude_locations', true ),
						'_tv_target_user_roles'        => get_post_meta( $post->ID, '_tv_target_user_roles', true ),
						'_elementor_edit_mode'         => get_post_meta( $post->ID, '_elementor_edit_mode', true ),
						'_elementor_template_type'     => get_post_meta( $post->ID, '_elementor_template_type', true ),
						'_elementor_data'              => get_post_meta( $post->ID, '_elementor_data', true ),
					),
				);
			}

			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/json' );
			header( 'Content-Disposition: attachment; filename="tv-all-layouts.json"' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate' );
			header( 'Pragma: public' );
			echo wp_json_encode( $export_data );
			exit;
		}

		// 5. CREATE LAYOUT OR IMPORT FORM ACTION
		if ( isset( $_POST['tv_create_template_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tv_create_template_nonce_field'] ) ), 'tv_create_template' ) ) {

			// Check if importing a file first
			if ( ! empty( $_FILES['tv_import_file']['tmp_name'] ) ) {
				$file_path = sanitize_text_field( wp_unslash( $_FILES['tv_import_file']['tmp_name'] ) );
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$file_content = file_get_contents( $file_path );
				$import_data  = json_decode( $file_content, true );

				if ( is_array( $import_data ) ) {
					$layouts_to_import = isset( $import_data['title'] ) ? array( $import_data ) : $import_data;
					$imported_count    = 0;

					foreach ( $layouts_to_import as $layout ) {
						if ( isset( $layout['title'], $layout['post_type'] ) ) {
							$imported_post_id = wp_insert_post(
								array(
									'post_title'   => $layout['title'] . ' (Imported)',
									'post_type'    => $layout['post_type'],
									'post_status'  => 'draft',
									'post_content' => $layout['content'],
								)
							);

							if ( ! is_wp_error( $imported_post_id ) ) {
								if ( isset( $layout['meta'] ) && is_array( $layout['meta'] ) ) {
									foreach ( $layout['meta'] as $meta_key => $meta_val ) {
										if ( is_array( $meta_val ) && isset( $meta_val[0] ) ) {
											$meta_val = maybe_unserialize( $meta_val[0] );
										}
										update_post_meta( $imported_post_id, $meta_key, $meta_val );
									}
								}

								// Update shortcode ID references from original site layout
								$original_id = isset( $layout['original_id'] ) ? intval( $layout['original_id'] ) : 0;
								if ( $original_id && class_exists( 'Elonix_Shortcodes' ) ) {
									Elonix_Shortcodes::update_shortcode_references_on_import( $original_id, $imported_post_id );
								}

								++$imported_count;
							}
						}
					}

					if ( $imported_count > 0 ) {
						wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&tv_status=imported&count=' . $imported_count ) );
						exit;
					}
				}
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&tv_status=import_failed' ) );
				exit;
			}

			// Traditional layout creation
			$title     = isset( $_POST['template_name'] ) ? sanitize_text_field( wp_unslash( $_POST['template_name'] ) ) : '';
			$type      = isset( $_POST['template_type'] ) ? sanitize_text_field( wp_unslash( $_POST['template_type'] ) ) : '';
			$condition = isset( $_POST['display_condition'] ) ? sanitize_text_field( wp_unslash( $_POST['display_condition'] ) ) : '';
			$priority  = isset( $_POST['priority'] ) ? intval( wp_unslash( $_POST['priority'] ) ) : 0;
			$status    = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'publish';
			$clone_id  = isset( $_POST['clone_layout'] ) ? intval( wp_unslash( $_POST['clone_layout'] ) ) : 0;
			$open_el   = isset( $_POST['open_elementor'] ) ? '1' === sanitize_text_field( wp_unslash( $_POST['open_elementor'] ) ) : false;
			$specifics = isset( $_POST['display_condition_specific'] ) && is_array( $_POST['display_condition_specific'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['display_condition_specific'] ) ) : array();

			$allowed_types = array( 'tv_header', 'tv_footer' );

			if ( in_array( $type, $allowed_types, true ) ) {
				$post_content = '';
				$starter_json = '';

				// Clone layout if selected
				if ( $clone_id ) {
					$clone_post = get_post( $clone_id );
					if ( $clone_post ) {
						$post_content = $clone_post->post_content;
						$starter_json = get_post_meta( $clone_id, '_elementor_data', true );
					}
				}

				$new_post_id = wp_insert_post(
					array(
						'post_title'   => $title ? $title : 'Untitled Layout',
						'post_type'    => $type,
						'post_status'  => $status,
						'post_content' => $post_content,
					)
				);

				if ( ! is_wp_error( $new_post_id ) ) {
					update_post_meta( $new_post_id, '_elementor_edit_mode', 'builder' );

					$doc_type = ( 'tv_header' === $type ) ? 'header' : 'footer';
					update_post_meta( $new_post_id, '_elementor_template_type', $doc_type );
					update_post_meta( $new_post_id, '_tv_priority', $priority );

					if ( ! empty( $condition ) ) {
						update_post_meta(
							$new_post_id,
							'_tv_target_include_locations',
							array(
								array(
									'rule'     => $condition,
									'specific' => $specifics,
								),
							)
						);
					} else {
						update_post_meta( $new_post_id, '_tv_target_include_locations', array() );
					}
					update_post_meta( $new_post_id, '_tv_target_exclude_locations', array() );
					update_post_meta( $new_post_id, '_tv_target_user_roles', array() );

					if ( ! empty( $starter_json ) ) {
						update_post_meta( $new_post_id, '_elementor_data', wp_slash( $starter_json ) );
					}

					if ( $open_el && class_exists( '\Elementor\Plugin' ) ) {
						$editor_url = \Elementor\Plugin::$instance->documents->get( $new_post_id )->get_edit_url();
						wp_safe_redirect( $editor_url );
						exit;
					} else {
						wp_safe_redirect( admin_url( 'post.php?post=' . $new_post_id . '&action=edit' ) );
						exit;
					}
				}
			}
		}

		// 6. QUICK EDIT SAVE ACTION
		if ( isset( $_POST['tv_quick_edit_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tv_quick_edit_nonce_field'] ) ), 'tv_quick_edit' ) ) {
			$post_id   = isset( $_POST['layout_id'] ) ? intval( wp_unslash( $_POST['layout_id'] ) ) : 0;
			$title     = isset( $_POST['template_name'] ) ? sanitize_text_field( wp_unslash( $_POST['template_name'] ) ) : '';
			$type      = isset( $_POST['template_type'] ) ? sanitize_text_field( wp_unslash( $_POST['template_type'] ) ) : '';
			$condition = isset( $_POST['display_condition'] ) ? sanitize_text_field( wp_unslash( $_POST['display_condition'] ) ) : '';
			$priority  = isset( $_POST['priority'] ) ? intval( wp_unslash( $_POST['priority'] ) ) : 0;
			$status    = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'publish';
			$specifics = isset( $_POST['display_condition_specific'] ) && is_array( $_POST['display_condition_specific'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['display_condition_specific'] ) ) : array();

			if ( $post_id && current_user_can( 'edit_posts' ) ) {
				wp_update_post(
					array(
						'ID'          => $post_id,
						'post_title'  => $title ? $title : 'Untitled Layout',
						'post_type'   => $type,
						'post_status' => $status,
					)
				);

				update_post_meta( $post_id, '_tv_priority', $priority );

				if ( ! empty( $condition ) ) {
					update_post_meta(
						$post_id,
						'_tv_target_include_locations',
						array(
							array(
								'rule'     => $condition,
								'specific' => $specifics,
							),
						)
					);
				} else {
					update_post_meta( $post_id, '_tv_target_include_locations', array() );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&tv_status=updated&count=1' ) );
				exit;
			}
		}

		// 7. TRASH DIRECT ACTION
		if ( 'tv_trash_layout' === $action ) {
			$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
			if ( $post_id && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'tv_trash_layout_' . $post_id ) ) {
				wp_trash_post( $post_id );
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&tv_status=deleted&count=1' ) );
				exit;
			}
		}

		// 8. RESTORE DIRECT ACTION
		if ( 'tv_restore_layout' === $action ) {
			$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
			if ( $post_id && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'tv_restore_layout_' . $post_id ) ) {
				wp_untrash_post( $post_id );
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&tv_status=restored&count=1' ) );
				exit;
			}
		}

		// 9. DELETE PERMANENTLY DIRECT ACTION
		if ( 'tv_delete_layout_permanently' === $action ) {
			$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
			if ( $post_id && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'tv_delete_layout_permanently_' . $post_id ) ) {
				wp_delete_post( $post_id, true );
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&tv_status=deleted_permanently&count=1' ) );
				exit;
			}
		}
	}

	/**
	 * Helper function to duplicate template post and all meta values.
	 */
	private function duplicate_template( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		$new_post_id = wp_insert_post(
			array(
				'post_title'   => $post->post_title . ' (Copy)',
				'post_type'    => $post->post_type,
				'post_status'  => 'draft',
				'post_content' => $post->post_content,
				'post_excerpt' => $post->post_excerpt,
			)
		);

		if ( is_wp_error( $new_post_id ) ) {
			return false;
		}

		// Copy meta
		$meta = get_post_meta( $post_id );
		foreach ( $meta as $key => $values ) {
			foreach ( $values as $value ) {
				$val = maybe_unserialize( $value );
				$val = wp_slash( $val );
				update_post_meta( $new_post_id, $key, $val );
			}
		}

		// Update shortcode ID references to maintain functionality
		if ( class_exists( 'Elonix_Shortcodes' ) ) {
			Elonix_Shortcodes::update_shortcode_references_on_duplicate( $post_id, $new_post_id );
		}

		return $new_post_id;
	}

	/**
	 * Renders the consolidated dashboard and settings page.
	 */
	public function render_settings_page() {
		$header_active = Elonix_Toolkit_Module_Manager::is_module_enabled( 'header_builder' );
		$footer_active = Elonix_Toolkit_Module_Manager::is_module_enabled( 'footer_builder' );

		// Stats Counts
		$header_counts = wp_count_posts( 'tv_header' );
		$footer_counts = wp_count_posts( 'tv_footer' );

		$total_headers     = intval( $header_counts->publish ) + intval( $header_counts->draft );
		$total_footers     = intval( $footer_counts->publish ) + intval( $footer_counts->draft );
		$active_templates  = intval( $header_counts->publish ) + intval( $footer_counts->publish );
		$draft_templates   = intval( $header_counts->draft ) + intval( $footer_counts->draft );
		$trashed_templates = intval( $header_counts->trash ) + intval( $footer_counts->trash );
		$total_all         = $total_headers + $total_footers; // publish + draft

		// Global Header Template
		$global_header_name = esc_html__( 'None assigned', 'elonix' );
		$global_headers     = get_posts(
			array(
				'post_type'      => 'tv_header',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required for querying layouts based on condition keys.
				'meta_query'     => array(
					array(
						'key'     => '_tv_target_include_locations',
						'value'   => 'basic-global',
						'compare' => 'LIKE',
					),
				),
			)
		);
		if ( ! empty( $global_headers ) ) {
			$global_header_name = $global_headers[0]->post_title;
		}

		// Global Footer Template
		$global_footer_name = esc_html__( 'None assigned', 'elonix' );
		$global_footers     = get_posts(
			array(
				'post_type'      => 'tv_footer',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required for querying layouts based on condition keys.
				'meta_query'     => array(
					array(
						'key'     => '_tv_target_include_locations',
						'value'   => 'basic-global',
						'compare' => 'LIKE',
					),
				),
			)
		);
		if ( ! empty( $global_footers ) ) {
			$global_footer_name = $global_footers[0]->post_title;
		}

		// Pagination & Filters Setup
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status_filter = isset( $_GET['tv_status'] ) ? sanitize_text_field( wp_unslash( $_GET['tv_status'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type_filter = isset( $_GET['tv_type'] ) ? sanitize_text_field( wp_unslash( $_GET['tv_type'] ) ) : 'all';

		$query_types = ( 'all' === $type_filter ) ? array( 'tv_header', 'tv_footer' ) : array( $type_filter );

		if ( 'trash' === $status_filter ) {
			$query_status = array( 'trash' );
		} elseif ( 'all' === $status_filter ) {
			$query_status = array( 'publish', 'draft' );
		} else {
			$query_status = array( $status_filter );
		}

		$per_page = 10;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$paged  = isset( $_GET['paged'] ) ? max( 1, intval( wp_unslash( $_GET['paged'] ) ) ) : 1;
		$offset = ( $paged - 1 ) * $per_page;

		$query_args = array(
			'post_type'      => $query_types,
			'post_status'    => $query_status,
			'posts_per_page' => $per_page,
			'offset'         => $offset,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		);

		$layouts_query = new WP_Query( $query_args );
		$layouts       = $layouts_query->posts;
		$total_items   = $layouts_query->found_posts;
		$total_pages   = ceil( $total_items / $per_page );

		// Notifications notices
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$notice_status = isset( $_GET['tv_status'] ) ? sanitize_text_field( wp_unslash( $_GET['tv_status'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$count       = isset( $_GET['count'] ) ? intval( wp_unslash( $_GET['count'] ) ) : 1;
		$notice_html = '';
		if ( 'imported' === $notice_status ) {
			/* translators: %s: Number of templates */
			$notice_html = '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( _n( '%s layout template imported successfully as Draft!', '%s layout templates imported successfully as Draft!', $count, 'elonix' ), $count ) ) . '</p></div>';
		} elseif ( 'import_failed' === $notice_status ) {
			$notice_html = '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Layout template import failed. Please upload a valid layout JSON file.', 'elonix' ) . '</p></div>';
		} elseif ( 'duplicated' === $notice_status ) {
			/* translators: %s: Number of templates */
			$notice_html = '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( _n( '%s layout template duplicated successfully as Draft!', '%s layout templates duplicated successfully as Draft!', $count, 'elonix' ), $count ) ) . '</p></div>';
		} elseif ( 'deleted' === $notice_status ) {
			/* translators: %s: Number of templates */
			$notice_html = '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( _n( '%s layout template moved to Trash successfully.', '%s layout templates moved to Trash successfully.', $count, 'elonix' ), $count ) ) . '</p></div>';
		} elseif ( 'restored' === $notice_status ) {
			/* translators: %s: Number of templates */
			$notice_html = '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( _n( '%s layout template restored from Trash successfully.', '%s layout templates restored from Trash successfully.', $count, 'elonix' ), $count ) ) . '</p></div>';
		} elseif ( 'deleted_permanently' === $notice_status ) {
			/* translators: %s: Number of templates */
			$notice_html = '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( _n( '%s layout template permanently deleted.', '%s layout templates permanently deleted.', $count, 'elonix' ), $count ) ) . '</p></div>';
		} elseif ( 'updated' === $notice_status ) {
			$notice_html = '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Layout template settings updated successfully.', 'elonix' ) . '</p></div>';
		} elseif ( 'bulk_updated' === $notice_status ) {
			/* translators: %s: Number of templates */
			$notice_html = '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( _n( '%s layout template updated successfully.', '%s layout templates updated successfully.', $count, 'elonix' ), $count ) ) . '</p></div>';
		}

		// Existing layouts for duplicating selection
		$existing_layouts = get_posts(
			array(
				'post_type'      => array( 'tv_header', 'tv_footer' ),
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
			)
		);
		?>
		<!-- REDESIGNED FULL WIDTH PREMIUM STYLE SHEETS -->
		<style>

			:root {
				--tv-primary: #6366f1;
				--tv-primary-hover: #4f46e5;
				--tv-dark: #0f172a;
				--tv-slate-50: #f8fafc;
				--tv-slate-100: #f1f5f9;
				--tv-slate-200: #e2e8f0;
				--tv-slate-300: #cbd5e1;
				--tv-slate-400: #94a3b8;
				--tv-slate-500: #64748b;
				--tv-slate-600: #475569;
				--tv-slate-700: #334155;
				--tv-slate-800: #1e293b;
				--tv-slate-900: #0f172a;

				--tv-success: #10b981;
				--tv-success-bg: #ecfdf5;
				--tv-success-border: #a7f3d0;
				--tv-danger: #ef4444;
				--tv-danger-bg: #fef2f2;
				--tv-danger-border: #fecaca;

				--tv-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.05), 0 2px 4px -2px rgba(15, 23, 42, 0.05);
				--tv-radius: 12px;
			}

			.tv-full-width-wrapper {
				font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				color: var(--tv-slate-800);
				padding: 20px 20px 20px 0;
				box-sizing: border-box;
				width: 100%;
			}
			.tv-full-width-wrapper * {
				box-sizing: border-box;
			}

			/* Accessibility Focus State Indicator */
			.tv-full-width-wrapper *:focus-visible {
				outline: 3px solid rgba(99, 102, 241, 0.5) !important;
				outline-offset: 2px !important;
			}

			/* Stats Dashboard Row */
			.tv-stats-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
				gap: 16px;
				margin-bottom: 24px;
			}
			.tv-stat-card {
				background: #ffffff;
				border: 1px solid var(--tv-slate-200);
				border-radius: var(--tv-radius);
				padding: 22px;
				box-shadow: var(--tv-shadow);
				transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
				position: relative;
				overflow: hidden;
			}
			.tv-stat-card:hover {
				transform: translateY(-3px);
				box-shadow: 0 12px 20px -3px rgba(15, 23, 42, 0.08);
			}
			.tv-stat-card::before {
				content: '';
				position: absolute;
				top: 0;
				left: 0;
				width: 4px;
				height: 100%;
			}
			.tv-stat-card.tv-stat-headers::before { background: #6366f1; }
			.tv-stat-card.tv-stat-footers::before { background: #ec4899; }
			.tv-stat-card.tv-stat-active::before { background: #10b981; }
			.tv-stat-card.tv-stat-global-header::before { background: #3b82f6; }
			.tv-stat-card.tv-stat-global-footer::before { background: #f59e0b; }

			.tv-stat-label {
				font-size: 11px;
				font-weight: 700;
				color: var(--tv-slate-500);
				text-transform: uppercase;
				letter-spacing: 0.8px;
			}
			.tv-stat-value {
				font-size: 28px;
				font-weight: 700;
				color: var(--tv-slate-900);
				margin-top: 8px;
				font-family: 'Inter', sans-serif;
			}
			.tv-stat-meta {
				font-size: 12px;
				color: var(--tv-slate-500);
				margin-top: 6px;
				white-space: nowrap;
				text-overflow: ellipsis;
				overflow: hidden;
			}

			/* Modern Buttons */
			.tv-btn {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				gap: 8px;
				font-family: 'Inter', sans-serif;
				font-size: 13px;
				font-weight: 600;
				padding: 10px 18px;
				border-radius: 8px;
				border: 1px solid transparent;
				cursor: pointer;
				transition: all 0.2s ease;
				text-decoration: none;
				height: 38px;
			}
			.tv-btn-primary {
				background: var(--tv-primary);
				color: #ffffff;
			}
			.tv-btn-primary:hover {
				background: var(--tv-primary-hover);
				color: #ffffff;
			}
			.tv-btn-pink {
				background: #ec4899;
				color: #ffffff;
			}
			.tv-btn-pink:hover {
				background: #db2777;
				color: #ffffff;
			}
			.tv-btn-secondary {
				background: #ffffff;
				border-color: var(--tv-slate-200);
				color: var(--tv-slate-700);
			}
			.tv-btn-secondary:hover {
				background: var(--tv-slate-50);
				border-color: var(--tv-slate-300);
				color: var(--tv-slate-900);
			}
			.tv-btn-danger {
				background: var(--tv-danger-bg);
				border-color: var(--tv-danger-border);
				color: var(--tv-danger);
			}
			.tv-btn-danger:hover {
				background: var(--tv-danger);
				color: #ffffff;
			}

			/* Actions Column Button Custom Styling */
			.tv-action-btn {
				display: inline-flex;
				align-items: center;
				gap: 4px;
				font-size: 11px;
				font-weight: 600;
				padding: 5px 10px;
				border-radius: 6px;
				text-decoration: none;
				border: 1px solid var(--tv-slate-200);
				background: #ffffff;
				color: var(--tv-slate-700);
				transition: all 0.2s ease;
				box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
				margin-bottom: 2px;
			}
			.tv-action-btn:hover {
				background: var(--tv-slate-50);
				border-color: var(--tv-slate-300);
				color: var(--tv-slate-900);
			}
			.tv-action-btn .dashicons {
				font-size: 14px;
				width: 14px;
				height: 14px;
				margin-top: 1px;
			}
			.tv-action-elementor {
				background: #8b5cf6;
				border-color: #7c3aed;
				color: #ffffff;
			}
			.tv-action-elementor:hover {
				background: #7c3aed;
				border-color: #6d28d9;
				color: #ffffff;
			}
			.tv-action-trash {
				color: var(--tv-danger);
			}
			.tv-action-trash:hover {
				background: var(--tv-danger-bg);
				border-color: var(--tv-danger-border);
				color: var(--tv-danger);
			}
			.tv-action-delete {
				color: var(--tv-danger);
			}
			.tv-action-delete:hover {
				background: var(--tv-danger);
				border-color: var(--tv-danger);
				color: #ffffff;
			}
			.tv-action-restore {
				color: var(--tv-success);
			}
			.tv-action-restore:hover {
				background: var(--tv-success-bg);
				border-color: var(--tv-success-border);
				color: var(--tv-success);
			}

			/* Filters & Search Toolbar Panel */
			.tv-toolbar-panel {
				background: #ffffff;
				border: 1px solid var(--tv-slate-200);
				border-radius: var(--tv-radius);
				padding: 16px 20px;
				box-shadow: var(--tv-shadow);
				margin-bottom: 24px;
				display: flex;
				flex-wrap: wrap;
				justify-content: space-between;
				align-items: center;
				gap: 16px;
			}
			.tv-filter-links {
				display: flex;
				align-items: center;
				flex-wrap: wrap;
				gap: 8px;
			}
			.tv-filter-link {
				font-size: 13px;
				font-weight: 600;
				color: var(--tv-slate-500);
				text-decoration: none;
				padding: 6px 12px;
				border-radius: 6px;
				transition: all 0.2s ease;
			}
			.tv-filter-link:hover {
				background: var(--tv-slate-50);
				color: var(--tv-slate-900);
			}
			.tv-filter-link.active {
				background: var(--tv-slate-100);
				color: var(--tv-primary);
			}
			.tv-search-bar {
				position: relative;
				width: 280px;
			}
			.tv-search-bar input {
				width: 100%;
				height: 38px;
				border-radius: 8px;
				border: 1px solid var(--tv-slate-300);
				padding: 0 12px 0 34px;
				font-size: 13px;
				font-family: 'Inter', sans-serif;
			}
			.tv-search-bar input:focus {
				border-color: var(--tv-primary);
				box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
				outline: none;
			}
			.tv-search-bar .search-icon {
				position: absolute;
				left: 10px;
				top: 10px;
				color: var(--tv-slate-400);
				font-size: 18px;
			}

			/* WP Native Table Enhancements */
			.tv-list-table-panel {
				background: #ffffff;
				border: 1px solid var(--tv-slate-200);
				border-radius: var(--tv-radius);
				box-shadow: var(--tv-shadow);
				overflow: hidden;
				padding-bottom: 0px;
			}
			.tv-full-width-wrapper .wp-list-table {
				border: none;
				box-shadow: none;
				margin: 0;
			}
			.tv-full-width-wrapper .wp-list-table thead th {
				background: var(--tv-slate-50);
				border-bottom: 1px solid var(--tv-slate-200);
				color: var(--tv-slate-700);
				font-family: 'Inter', sans-serif;
				font-weight: 600;
				text-transform: uppercase;
				font-size: 11px;
				letter-spacing: 0.5px;
				padding: 14px 18px;
			}
			.tv-full-width-wrapper .wp-list-table tbody td {
				padding: 14px 18px;
				vertical-align: middle;
				font-family: 'Inter', sans-serif;
				font-size: 13.5px;
				border-bottom: 1px solid var(--tv-slate-100);
			}
			.tv-full-width-wrapper .wp-list-table tbody tr:last-child td {
				border-bottom: none;
			}
			.tv-full-width-wrapper .wp-list-table tbody tr:hover {
				background: var(--tv-slate-50);
			}
			.tv-full-width-wrapper .row-title {
				font-size: 14.5px;
				font-weight: 700;
				color: var(--tv-slate-900);
				text-decoration: none;
			}

			/* Pills & Badges */
			.tv-badge {
				display: inline-flex;
				align-items: center;
				font-family: 'Inter', sans-serif;
				font-weight: 700;
				text-transform: uppercase;
				border: 1px solid transparent;
				letter-spacing: 0.5px;
			}
			.tv-badge-header { background: #eff6ff; color: #1e40af; border: 1px solid #dbeafe; font-size: 9.5px; padding: 2px 6px; border-radius: 4px; }
			.tv-badge-footer { background: #fdf2f8; color: #9d174d; border: 1px solid #fce7f3; font-size: 9.5px; padding: 2px 6px; border-radius: 4px; }
			.tv-badge-publish { background: #ecfdf5; color: #065f46; border: 1px solid #d1fae5; font-size: 9.5px; padding: 2px 8px; border-radius: 12px; }
			.tv-badge-draft { background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; font-size: 9.5px; padding: 2px 8px; border-radius: 12px; }
			.tv-badge-trash { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; font-size: 9.5px; padding: 2px 8px; border-radius: 12px; }

			/* Empty State */
			.tv-empty-box {
				padding: 80px 40px;
				text-align: center;
				background: #ffffff;
			}
			.tv-empty-box .dashicons {
				font-size: 64px;
				width: 64px;
				height: 64px;
				color: var(--tv-slate-300);
				margin-bottom: 20px;
			}
			.tv-empty-box h3 {
				font-size: 20px;
				font-weight: 700;
				color: var(--tv-slate-900);
				margin: 0 0 10px 0;
			}
			.tv-empty-box p {
				font-size: 14px;
				color: var(--tv-slate-500);
				max-width: 400px;
				margin: 0 auto 24px auto;
				line-height: 1.5;
			}

			/* Glass Modal Overlay & Content Card */
			.tv-modal-overlay {
				background: rgba(15, 23, 42, 0.45);
				backdrop-filter: blur(8px);
				position: fixed;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				display: flex;
				align-items: center;
				justify-content: center;
				z-index: 99999;
				opacity: 0;
				visibility: hidden;
				transition: opacity 0.25s ease, visibility 0.25s ease;
			}
			.tv-modal-overlay.open {
				opacity: 1;
				visibility: visible;
			}
			.tv-modal-card {
				background: #ffffff;
				border: 1px solid var(--tv-slate-200);
				border-radius: 16px;
				width: 600px;
				box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.15);
				padding: 30px;
				transform: scale(0.95);
				transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
				position: relative;
			}
			.tv-modal-overlay.open .tv-modal-card {
				transform: scale(1);
			}
			.tv-modal-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 24px;
				border-bottom: 1px solid var(--tv-slate-200);
				padding-bottom: 16px;
			}
			.tv-modal-title {
				font-size: 18px;
				font-weight: 700;
				color: var(--tv-slate-900);
				margin: 0;
			}
			.tv-modal-close {
				font-size: 24px;
				font-weight: 500;
				color: var(--tv-slate-400);
				cursor: pointer;
				border: none;
				background: none;
				transition: color 0.15s ease;
				padding: 0;
				line-height: 1;
			}
			.tv-modal-close:hover {
				color: var(--tv-slate-900);
			}

			/* Modal Form Fields */
			.tv-field {
				margin-bottom: 20px;
			}
			.tv-field label {
				display: block;
				font-size: 12.5px;
				font-weight: 600;
				color: var(--tv-slate-700);
				margin-bottom: 8px;
			}
			.tv-input, .tv-select {
				width: 100%;
				height: 40px;
				border-radius: 8px;
				border: 1px solid var(--tv-slate-300);
				padding: 0 14px;
				font-size: 13.5px;
				font-family: 'Inter', sans-serif;
				color: var(--tv-slate-800);
				transition: border-color 0.2s ease, box-shadow 0.2s ease;
			}
			.tv-input:focus, .tv-select:focus {
				border-color: var(--tv-primary);
				box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
				outline: none;
			}

			/* Modal Tabs */
			.tv-modal-tabs {
				display: flex;
				border-bottom: 1px solid var(--tv-slate-200);
				margin-bottom: 20px;
				gap: 16px;
			}
			.tv-modal-tab-link {
				font-size: 13px;
				font-weight: 600;
				color: var(--tv-slate-500);
				text-decoration: none;
				padding-bottom: 8px;
				border-bottom: 2px solid transparent;
				cursor: pointer;
			}
			.tv-modal-tab-link.active {
				color: var(--tv-primary);
				border-bottom-color: var(--tv-primary);
			}

			/* WP Native tablenav adjustment */
			.tv-full-width-wrapper .tablenav {
				height: auto;
				margin: 0;
				padding: 14px 18px;
				background: var(--tv-slate-50);
				border-top: 1px solid var(--tv-slate-100);
				display: flex;
				justify-content: space-between;
				align-items: center;
				flex-wrap: wrap;
				gap: 10px;
			}
			.tv-full-width-wrapper .tablenav.top {
				border-top: none;
				border-bottom: 1px solid var(--tv-slate-100);
			}
			.tv-full-width-wrapper .tablenav-pages {
				margin: 0;
				float: none;
			}
			.tv-full-width-wrapper .tablenav .actions {
				padding: 0;
				float: none;
				display: flex;
				align-items: center;
				gap: 8px;
			}
			.tv-full-width-wrapper .tablenav select {
				height: 36px;
				border-radius: 6px;
				border-color: var(--tv-slate-250);
				font-size: 12.5px;
				font-family: 'Inter', sans-serif;
			}
			.tv-full-width-wrapper .tablenav .button.action {
				height: 36px;
				border-radius: 6px;
				font-weight: 600;
				padding: 0 14px;
				font-family: 'Inter', sans-serif;
			}
			/* Compact template list table adjustments - Floating row cards */
			.tv-full-width-wrapper .tv-table-responsive {
				background: transparent;
				border: none;
				box-shadow: none;
				overflow: visible;
			}
			.tv-full-width-wrapper .wp-list-table {
				border-collapse: separate;
				border-spacing: 0 12px;
				background: transparent;
				margin-top: -12px;
			}
			.tv-full-width-wrapper .wp-list-table thead th {
				background: transparent;
				border: none;
				color: var(--tv-slate-500);
				font-size: 11px;
				font-weight: 600;
				text-transform: uppercase;
				letter-spacing: 0.5px;
				padding: 10px 20px;
			}
			.tv-full-width-wrapper .wp-list-table tbody tr {
				background: #ffffff;
				box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
				border-radius: 10px;
				transition: border-color 0.2s ease, box-shadow 0.2s ease;
				position: relative;
			}
			.tv-full-width-wrapper .wp-list-table tbody tr:hover {
				box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -4px rgba(15, 23, 42, 0.08);
			}
			.tv-full-width-wrapper .wp-list-table tbody tr.tv-row-active {
				z-index: 100 !important;
			}
			.tv-full-width-wrapper .wp-list-table tbody td {
				border-top: 1px solid var(--tv-slate-200);
				border-bottom: 1px solid var(--tv-slate-200);
				padding: 14px 20px;
				vertical-align: middle;
				background: #ffffff;
				transition: border-color 0.2s ease, background 0.2s ease;
			}
			.tv-full-width-wrapper .wp-list-table tbody td:first-child {
				border-left: 1px solid var(--tv-slate-200);
				border-top-left-radius: 10px;
				border-bottom-left-radius: 10px;
			}
			.tv-full-width-wrapper .wp-list-table tbody td:last-child {
				border-right: 1px solid var(--tv-slate-200);
				border-top-right-radius: 10px;
				border-bottom-right-radius: 10px;
			}
			.tv-full-width-wrapper .wp-list-table.striped > tbody > :nth-child(odd) td {
				background: #ffffff;
			}
			.tv-full-width-wrapper .wp-list-table.striped > tbody > :nth-child(even) td {
				background: #ffffff;
			}
			.tv-full-width-wrapper .wp-list-table tbody tr:hover td {
				background: var(--tv-slate-50);
				border-color: #c7d2fe;
			}

			/* Template Name & Metadata Layout */
			.tv-template-title-group {
				display: flex;
				flex-direction: column;
				gap: 4px;
			}
			.tv-template-name-row {
				display: flex;
				align-items: center;
				gap: 8px;
			}
			.tv-template-name-row .row-title {
				font-size: 16px;
				font-weight: 600;
				color: var(--tv-slate-800);
				text-decoration: none;
			}
			.tv-template-name-row .row-title:hover {
				color: var(--tv-primary);
			}
			.tv-template-meta-row {
				display: flex;
				align-items: center;
				gap: 6px;
				font-size: 11.5px;
				color: var(--tv-slate-400);
				flex-wrap: wrap;
			}
			.tv-meta-item {
				color: var(--tv-slate-400);
			}
			.tv-meta-divider {
				color: var(--tv-slate-200);
				font-size: 8px;
			}
			.tv-meta-link {
				color: var(--tv-primary);
				text-decoration: none;
				font-weight: 500;
				cursor: pointer;
				transition: color 0.15s ease;
			}
			.tv-meta-link:hover {
				color: var(--tv-primary-hover);
				text-decoration: underline;
			}
			.tv-name-icon {
				font-size: 16px !important;
				width: 16px !important;
				height: 16px !important;
				color: var(--tv-slate-400);
				margin: 0 !important;
				line-height: 1 !important;
			}

			/* Display Conditions Styling */
			.tv-display-condition-wrapper {
				font-size: 12.5px;
				font-weight: 500;
				color: var(--tv-slate-700);
				display: flex;
				flex-direction: column;
				gap: 4px;
			}
			.tv-condition-pill {
				display: inline-flex;
				align-items: center;
				gap: 6px;
			}
			.tv-condition-badge-inc, .tv-condition-badge-exc {
				font-size: 9px;
				font-weight: 700;
				text-transform: uppercase;
				border-radius: 4px;
				padding: 1px 4px;
				line-height: 1.2;
				display: inline-block;
			}
			.tv-condition-badge-inc {
				background: #f0fdf4;
				color: #16a34a;
				border: 1px solid #dcfce7;
			}
			.tv-condition-badge-exc {
				background: #fef2f2;
				color: #ef4444;
				border: 1px solid #fee2e2;
			}

			/* Action Cell & Dropdown Menu */
			.tv-actions-cell {
				display: flex;
				align-items: center;
				gap: 8px;
			}
			.tv-btn-primary.tv-btn-small {
				height: 28px;
				line-height: 26px;
				padding: 0 10px;
				font-size: 12px;
				font-weight: 600;
				border-radius: 6px;
				display: inline-flex;
				align-items: center;
				gap: 4px;
				border: none;
				color: #ffffff;
				text-decoration: none;
				cursor: pointer;
				background: var(--tv-primary);
				transition: background 0.15s ease;
			}
			.tv-btn-primary.tv-btn-small:hover {
				background: var(--tv-primary-hover);
			}
			.tv-btn-primary.tv-btn-small .dashicons {
				font-size: 14px;
				width: 14px;
				height: 14px;
				margin: 0;
				line-height: 1;
			}
			.tv-actions-dropdown-wrapper {
				position: relative;
				display: inline-block;
			}
			.tv-actions-dropdown-trigger {
				background: #ffffff;
				border: 1px solid var(--tv-slate-200);
				color: var(--tv-slate-600);
				border-radius: 6px;
				width: 28px;
				height: 28px;
				display: flex;
				align-items: center;
				justify-content: center;
				cursor: pointer;
				transition: background 0.15s ease, border-color 0.15s ease;
				padding: 0;
			}
			.tv-actions-dropdown-trigger:hover, .tv-actions-dropdown-wrapper.active .tv-actions-dropdown-trigger {
				background: var(--tv-slate-100);
				border-color: var(--tv-slate-350);
				color: var(--tv-slate-900);
			}
			.tv-actions-dropdown-trigger .dashicons {
				font-size: 18px;
				width: 18px;
				height: 18px;
				margin: 0;
				line-height: 1;
			}
			.tv-actions-dropdown-menu {
				display: none;
				position: absolute;
				top: 100%;
				right: 0;
				z-index: 1000;
				margin-top: 0;
				min-width: 160px;
				background: #ffffff;
				border: 1px solid var(--tv-slate-200);
				border-radius: 8px;
				box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
				overflow: hidden;
				padding: 4px 0;
			}
			.tv-actions-dropdown-wrapper.active .tv-actions-dropdown-menu {
				display: block;
			}
			.tv-dropdown-item {
				display: flex;
				align-items: center;
				gap: 8px;
				padding: 8px 12px;
				font-size: 12.5px;
				font-weight: 500;
				color: var(--tv-slate-700);
				text-decoration: none;
				transition: background 0.15s ease, color 0.15s ease;
				cursor: pointer;
				border: none;
				background: none;
				width: 100%;
				text-align: left;
				box-sizing: border-box;
			}
			.tv-dropdown-item:hover {
				background: var(--tv-slate-50);
				color: var(--tv-slate-900);
			}
			.tv-dropdown-item .dashicons {
				font-size: 16px;
				width: 16px;
				height: 16px;
				color: var(--tv-slate-400);
				margin: 0;
			}
			.tv-dropdown-item.tv-action-trash:hover {
				background: var(--tv-danger-bg);
				color: var(--tv-danger);
			}
			.tv-dropdown-item.tv-action-trash:hover .dashicons {
				color: var(--tv-danger);
			}

			/* Sticky Floating Bulk Actions Bar */
			.tv-sticky-bulk-bar {
				position: fixed;
				bottom: -100px;
				left: 50%;
				transform: translateX(-50%);
				width: auto;
				min-width: 450px;
				max-width: 90%;
				background: var(--tv-slate-900);
				color: #ffffff;
				border-radius: 12px;
				padding: 12px 20px;
				box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
				z-index: 10000;
				transition: bottom 0.3s cubic-bezier(0.16, 1, 0.3, 1);
				border: 1px solid var(--tv-slate-800);
			}
			.tv-sticky-bulk-bar.active {
				bottom: 24px;
			}
			.tv-sticky-bulk-inner {
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 20px;
			}
			.tv-selected-count {
				font-size: 13.5px;
				font-weight: 600;
				color: var(--tv-slate-200);
			}
			.tv-sticky-bulk-actions {
				display: flex;
				align-items: center;
				gap: 10px;
			}
			.tv-sticky-bulk-actions .tv-select {
				height: 32px;
				padding: 0 10px;
				font-size: 12.5px;
				background: var(--tv-slate-800);
				border: 1px solid var(--tv-slate-700);
				color: #ffffff;
				border-radius: 6px;
				width: 180px;
				margin: 0;
			}
			.tv-sticky-bulk-actions .tv-select option {
				background: var(--tv-slate-900);
				color: #ffffff;
			}
			.tv-sticky-bulk-actions .tv-btn {
				height: 32px;
				padding: 0 14px;
				font-size: 12.5px;
				font-weight: 600;
				border-radius: 6px;
				cursor: pointer;
				border: none;
			}
			.tv-sticky-bulk-actions .tv-btn-primary {
				background: var(--tv-primary);
				color: #ffffff;
			}
			.tv-sticky-bulk-actions .tv-btn-primary:hover {
				background: var(--tv-primary-hover);
			}
			.tv-sticky-bulk-actions .tv-btn-secondary {
				background: transparent;
				border: 1px solid var(--tv-slate-700);
				color: var(--tv-slate-300);
			}
			.tv-sticky-bulk-actions .tv-btn-secondary:hover {
				background: var(--tv-slate-800);
				color: #ffffff;
			}

			/* Responsive Admin Design */
			.tv-table-responsive {
				width: 100%;
				overflow: visible !important;
				border-radius: var(--tv-radius);
				border: none !important;
				box-shadow: none !important;
				background: transparent !important;
			}
			.tv-list-table-panel {
				background: transparent !important;
				border: none !important;
				box-shadow: none !important;
				overflow: visible !important;
				padding-bottom: 0;
			}

			/* Mobile Media Queries */
			@media (max-width: 782px) {
				.tv-table-responsive {
					border: none;
					box-shadow: none;
					background: transparent;
				}
				.tv-full-width-wrapper .wp-list-table.posts thead {
					display: none;
				}
				.tv-full-width-wrapper .wp-list-table.posts tbody tr {
					display: block;
					margin-bottom: 16px;
					border: 1px solid var(--tv-slate-200);
					border-radius: 12px;
					background: #ffffff;
					padding: 16px;
					box-shadow: var(--tv-shadow);
				}
				.tv-full-width-wrapper .wp-list-table.posts tbody td {
					display: flex;
					justify-content: space-between;
					align-items: center;
					padding: 10px 0;
					border-bottom: 1px solid var(--tv-slate-100);
					width: 100% !important;
					box-sizing: border-box;
					text-align: right;
				}
				.tv-full-width-wrapper .wp-list-table.posts tbody td:last-child {
					border-bottom: none;
					padding-bottom: 0;
				}
				.tv-full-width-wrapper .wp-list-table.posts tbody td::before {
					content: attr(data-colname);
					font-weight: 600;
					color: var(--tv-slate-500);
					font-size: 11px;
					text-transform: uppercase;
					text-align: left;
					display: inline-block;
				}
				.tv-full-width-wrapper .wp-list-table.posts tbody td.check-column {
					display: none !important;
				}
				.tv-template-title-group {
					align-items: flex-end;
					text-align: right;
				}
				.tv-template-name-row {
					flex-direction: row-reverse;
				}
				.tv-template-meta-row {
					justify-content: flex-end;
				}
				.tv-actions-cell {
					justify-content: flex-end;
					width: 100%;
				}
				.tv-sticky-bulk-bar {
					min-width: calc(100% - 32px);
					left: 16px;
					transform: none;
					box-sizing: border-box;
				}
				.tv-sticky-bulk-inner {
					flex-direction: column;
					gap: 12px;
					align-items: stretch;
				}
				.tv-sticky-bulk-actions {
					flex-direction: column;
					align-items: stretch;
				}
				.tv-sticky-bulk-actions .tv-select {
					width: 100%;
				}
			}
		</style>

		<div class="tv-full-width-wrapper">
			<!-- Render WordPress notices dynamically -->
			<?php
			if ( ! empty( $notice_html ) ) {
				echo $notice_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>

			<!-- Redesigned Top Branding row -->
			<div class="tv-top-row" style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; flex-wrap:wrap; gap:16px;">
				<div>
					<h1 style="font-size:26px; font-weight:700; color:var(--tv-slate-900); margin:0; font-family:'Inter', sans-serif;"><?php esc_html_e( 'Header & Footer Builder', 'elonix' ); ?></h1>
					<p style="font-size:13.5px; color:var(--tv-slate-500); margin:4px 0 0 0;"><?php esc_html_e( 'Build, design, and target custom site layout templates with Elementor.', 'elonix' ); ?></p>
				</div>
				<div style="display:flex; gap:10px; flex-wrap:wrap;">
					<?php if ( $header_active ) : ?>
						<button type="button" class="tv-btn tv-btn-primary" id="tv_create_header_btn">
							<span class="dashicons dashicons-plus" style="font-size:16px; margin-top:2px;"></span>
							<?php esc_html_e( 'Create Header', 'elonix' ); ?>
						</button>
					<?php endif; ?>

					<?php if ( $footer_active ) : ?>
						<button type="button" class="tv-btn tv-btn-pink" id="tv_create_footer_btn">
							<span class="dashicons dashicons-plus" style="font-size:16px; margin-top:2px;"></span>
							<?php esc_html_e( 'Create Footer', 'elonix' ); ?>
						</button>
					<?php endif; ?>

					<button type="button" class="tv-btn tv-btn-secondary" id="tv_import_btn">
						<span class="dashicons dashicons-upload" style="font-size:16px; margin-top:2px;"></span>
						<?php esc_html_e( 'Import Template', 'elonix' ); ?>
					</button>

					<a href="<?php echo esc_url( admin_url( 'admin.php?action=tv_export_all_templates' ) ); ?>" class="tv-btn tv-btn-secondary" title="<?php esc_attr_e( 'Export layout templates to JSON', 'elonix' ); ?>">
						<span class="dashicons dashicons-download" style="font-size:16px; margin-top:2px;"></span>
						<?php esc_html_e( 'Export Templates', 'elonix' ); ?>
					</a>
				</div>
			</div>

			<!-- Dashboard Stats Section -->
			<div class="tv-stats-grid">
				<!-- Total Headers -->
				<div class="tv-stat-card tv-stat-headers">
					<div class="tv-stat-label"><?php esc_html_e( 'Total Headers', 'elonix' ); ?></div>
					<div class="tv-stat-value"><?php echo (int) $total_headers; ?></div>
					<div class="tv-stat-meta">
						<?php
						/* translators: %s: Number of active headers */
						echo esc_html( sprintf( __( '%s Active', 'elonix' ), $header_counts->publish ) );
						?>
					</div>
				</div>

				<!-- Total Footers -->
				<div class="tv-stat-card tv-stat-footers">
					<div class="tv-stat-label"><?php esc_html_e( 'Total Footers', 'elonix' ); ?></div>
					<div class="tv-stat-value"><?php echo (int) $total_footers; ?></div>
					<div class="tv-stat-meta">
						<?php
						/* translators: %s: Number of active footers */
						echo esc_html( sprintf( __( '%s Active', 'elonix' ), $footer_counts->publish ) );
						?>
					</div>
				</div>

				<!-- Active Templates -->
				<div class="tv-stat-card tv-stat-active">
					<div class="tv-stat-label"><?php esc_html_e( 'Active Templates', 'elonix' ); ?></div>
					<div class="tv-stat-value"><?php echo (int) $active_templates; ?></div>
					<div class="tv-stat-meta">
						<?php
						/* translators: %s: Number of draft templates */
						echo esc_html( sprintf( __( '%s Draft / Deassigned', 'elonix' ), $total_all - $active_templates ) );
						?>
					</div>
				</div>

				<!-- Global Header -->
				<div class="tv-stat-card tv-stat-global-header">
					<div class="tv-stat-label"><?php esc_html_e( 'Global Header', 'elonix' ); ?></div>
					<div class="tv-stat-value" style="font-size:18px; margin-top:14px; font-weight:600; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">
						<?php echo esc_html( $global_header_name ); ?>
					</div>
					<div class="tv-stat-meta"><?php esc_html_e( 'Assigned to Entire Website', 'elonix' ); ?></div>
				</div>

				<!-- Global Footer -->
				<div class="tv-stat-card tv-stat-global-footer">
					<div class="tv-stat-label"><?php esc_html_e( 'Global Footer', 'elonix' ); ?></div>
					<div class="tv-stat-value" style="font-size:18px; margin-top:14px; font-weight:600; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">
						<?php echo esc_html( $global_footer_name ); ?>
					</div>
					<div class="tv-stat-meta"><?php esc_html_e( 'Assigned to Entire Website', 'elonix' ); ?></div>
				</div>
			</div>

			<!-- Bulk Actions form wrapper -->
			<form method="post" id="tv_bulk_form">
				<?php wp_nonce_field( 'tv_bulk_action', 'tv_bulk_action_nonce_field' ); ?>

				<!-- Toolbar for Filters and Search -->
				<div class="tv-toolbar-panel">
					<!-- Filter tabs linking dynamically using query parameters -->
					<div class="tv-filter-links">
						<!-- All status filter links -->
						<a href="<?php echo esc_url( remove_query_arg( array( 'tv_status', 'tv_type', 'paged' ) ) ); ?>" class="tv-filter-link <?php echo ( 'all' === $status_filter && 'all' === $type_filter ) ? 'active' : ''; ?>">
							<?php esc_html_e( 'All', 'elonix' ); ?>
							<span style="font-weight:400; color:var(--tv-slate-400);"> (<?php echo (int) $total_all; ?>)</span>
						</a>

						<!-- Headers filter link -->
						<a href="
						<?php
						echo esc_url(
							add_query_arg(
								array(
									'tv_type'   => 'tv_header',
									'tv_status' => 'all',
								)
							)
						);
						?>
									" class="tv-filter-link <?php echo ( 'tv_header' === $type_filter ) ? 'active' : ''; ?>">
							<?php esc_html_e( 'Headers', 'elonix' ); ?>
							<span style="font-weight:400; color:var(--tv-slate-400);"> (<?php echo (int) $total_headers; ?>)</span>
						</a>

						<!-- Footers filter link -->
						<a href="
						<?php
						echo esc_url(
							add_query_arg(
								array(
									'tv_type'   => 'tv_footer',
									'tv_status' => 'all',
								)
							)
						);
						?>
									" class="tv-filter-link <?php echo ( 'tv_footer' === $type_filter ) ? 'active' : ''; ?>">
							<?php esc_html_e( 'Footers', 'elonix' ); ?>
							<span style="font-weight:400; color:var(--tv-slate-400);"> (<?php echo (int) $total_footers; ?>)</span>
						</a>

						<!-- Active publish templates filter link -->
						<a href="
						<?php
						echo esc_url(
							add_query_arg(
								array(
									'tv_status' => 'publish',
									'tv_type'   => 'all',
								)
							)
						);
						?>
									" class="tv-filter-link <?php echo ( 'publish' === $status_filter ) ? 'active' : ''; ?>">
							<?php esc_html_e( 'Active', 'elonix' ); ?>
							<span style="font-weight:400; color:var(--tv-slate-400);"> (<?php echo (int) $active_templates; ?>)</span>
						</a>

						<!-- Draft templates filter link -->
						<a href="
						<?php
						echo esc_url(
							add_query_arg(
								array(
									'tv_status' => 'draft',
									'tv_type'   => 'all',
								)
							)
						);
						?>
									" class="tv-filter-link <?php echo ( 'draft' === $status_filter ) ? 'active' : ''; ?>">
							<?php esc_html_e( 'Draft', 'elonix' ); ?>
							<span style="font-weight:400; color:var(--tv-slate-400);"> (<?php echo (int) $draft_templates; ?>)</span>
						</a>

						<!-- Trashed templates filter link (always visible) -->
						<a href="
						<?php
						echo esc_url(
							add_query_arg(
								array(
									'tv_status' => 'trash',
									'tv_type'   => 'all',
								)
							)
						);
						?>
									" class="tv-filter-link <?php echo ( 'trash' === $status_filter ) ? 'active' : ''; ?>" style="color:var(--tv-danger);">
							<?php esc_html_e( 'Trash', 'elonix' ); ?>
							<span style="font-weight:400; color:var(--tv-danger);"> (<?php echo (int) $trashed_templates; ?>)</span>
						</a>
					</div>

					<!-- Search container -->
					<div class="tv-search-bar">
						<span class="dashicons dashicons-search search-icon"></span>
						<input type="text" id="tv_live_search" placeholder="<?php esc_attr_e( 'Search templates...', 'elonix' ); ?>" aria-label="<?php esc_attr_e( 'Search template list', 'elonix' ); ?>">
					</div>
				</div>

				<!-- Table content list card -->
				<div class="tv-list-table-panel">
					<!-- WP Native tablenav top -->
					<div class="tablenav top">
						<div class="alignleft actions bulkactions">
							<select name="tv_bulk_action" id="bulk-action-selector-top" aria-label="<?php esc_attr_e( 'Select bulk action', 'elonix' ); ?>">
								<option value=""><?php esc_html_e( 'Bulk Actions', 'elonix' ); ?></option>
								<?php if ( 'trash' === $status_filter ) : ?>
									<option value="restore"><?php esc_html_e( 'Restore', 'elonix' ); ?></option>
									<option value="delete_permanently"><?php esc_html_e( 'Delete Permanently', 'elonix' ); ?></option>
								<?php else : ?>
									<option value="activate"><?php esc_html_e( 'Set to Published', 'elonix' ); ?></option>
									<option value="deactivate"><?php esc_html_e( 'Set to Draft', 'elonix' ); ?></option>
									<option value="trash"><?php esc_html_e( 'Move to Trash', 'elonix' ); ?></option>
								<?php endif; ?>
							</select>
							<button type="submit" class="button action"><?php esc_html_e( 'Apply', 'elonix' ); ?></button>
						</div>

						<!-- Pagination index controls -->
						<div class="tablenav-pages
						<?php
						if ( $total_pages <= 1 ) {
							echo ' one-page'; }
						?>
						">
							<span class="displaying-num">
								<?php
								/* translators: %s: Number of items */
								echo esc_html( sprintf( _n( '%s item', '%s items', $total_items, 'elonix' ), number_format_i18n( $total_items ) ) );
								?>
							</span>
							<?php if ( $total_pages > 1 ) : ?>
								<span class="pagination-links">
									<?php if ( $paged > 1 ) : ?>
										<a class="prev-page button" href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">‹</span></a>
									<?php else : ?>
										<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
									<?php endif; ?>

									<span class="screen-reader-text">Current Page</span>
									<span class="paging-input">
										<span class="current-page"><?php echo (int) $paged; ?></span> of <span class="total-pages"><?php echo (int) $total_pages; ?></span>
									</span>

									<?php if ( $paged < $total_pages ) : ?>
										<a class="next-page button" href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>"><span class="screen-reader-text">Next page</span><span aria-hidden="true">›</span></a>
									<?php else : ?>
										<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
									<?php endif; ?>
								</span>
							<?php endif; ?>
						</div>
					</div>

					<?php
					$colspan_count = 4;
					?>
					<div class="tv-table-responsive">
						<table class="wp-list-table widefat fixed striped table-view-list posts">
							<thead>
								<tr>
									<th id="cb" class="manage-column column-cb check-column" style="width: 40px; padding-left: 20px; vertical-align: middle;">
										<label class="screen-reader-text" for="cb-select-all-top"><?php esc_html_e( 'Select All', 'elonix' ); ?></label>
										<input type="checkbox" id="cb-select-all-top" style="margin:0; cursor:pointer;">
									</th>
									<th class="manage-column" style="width: 45%;"><?php esc_html_e( 'Template Details', 'elonix' ); ?></th>
									<th class="manage-column" style="width: 40%;"><?php esc_html_e( 'Metadata Panel', 'elonix' ); ?></th>
									<th class="manage-column" style="width: 15%; text-align: right; padding-right: 20px;"><?php esc_html_e( 'Actions', 'elonix' ); ?></th>
								</tr>
							</thead>
							<tbody id="tv_table_body">
								<?php if ( empty( $layouts ) ) : ?>
									<tr>
										<td colspan="4">
											<div class="tv-empty-box">
												<span class="dashicons dashicons-layout"></span>
												<h3><?php esc_html_e( 'No Header or Footer Templates Found', 'elonix' ); ?></h3>
												<p><?php esc_html_e( 'You have no templates in this state. Get started by creating your custom layouts.', 'elonix' ); ?></p>
												<?php if ( $header_active ) : ?>
													<button type="button" class="tv-btn tv-btn-primary" onclick="jQuery('#tv_create_header_btn').click();" style="margin: 4px;">
														<?php esc_html_e( 'Create Header Template', 'elonix' ); ?>
													</button>
												<?php endif; ?>
												<?php if ( $footer_active ) : ?>
													<button type="button" class="tv-btn tv-btn-pink" onclick="jQuery('#tv_create_footer_btn').click();" style="margin: 4px;">
														<?php esc_html_e( 'Create Footer Template', 'elonix' ); ?>
													</button>
												<?php endif; ?>
											</div>
										</td>
									</tr>
								<?php else : ?>
									<?php
									foreach ( $layouts as $tpl ) :
										$type_label = ( 'tv_header' === $tpl->post_type ) ? 'Header' : 'Footer';
										$type_badge = ( 'tv_header' === $tpl->post_type ) ? 'tv-badge-header' : 'tv-badge-footer';
										$type_icon  = ( 'tv_header' === $tpl->post_type ) ? 'dashicons-editor-kitchensink' : 'dashicons-editor-insertmore';

										$priority = get_post_meta( $tpl->ID, '_tv_priority', true );
										$status   = get_post_status( $tpl->ID );

										$include_locations  = get_post_meta( $tpl->ID, '_tv_target_include_locations', true );
										$include_normalized = Elonix_Target_Rules::normalize_rules( $include_locations );
										$saved_condition    = ! empty( $include_normalized ) ? $include_normalized[0]['rule'] : '';
										$saved_specifics    = ! empty( $include_normalized ) && isset( $include_normalized[0]['specific'] ) ? $include_normalized[0]['specific'] : array();

										$specific_details = array();
										if ( ! empty( $saved_specifics ) ) {
											foreach ( $saved_specifics as $spec_val ) {
												$specific_details[] = array(
													'id'   => $spec_val,
													'text' => Elonix_Target_Rules::get_location_by_key( $spec_val ),
												);
											}
										}

										$shortcode = ( 'tv_header' === $tpl->post_type ) ? '[tv_header id="' . $tpl->ID . '"]' : '[tv_footer id="' . $tpl->ID . '"]';
										$php_code  = ( 'tv_header' === $tpl->post_type ) ? '<?php elonix_render_header(' . $tpl->ID . '); ?>' : '<?php elonix_render_footer(' . $tpl->ID . '); ?>';

										$modified_time = get_the_modified_date( 'U', $tpl->ID );
										$time_diff     = human_time_diff( $modified_time, current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'elonix' );

										$status_label = 'DRAFT';
										if ( 'publish' === $status ) {
											$status_label = 'ACTIVE';
										} elseif ( 'trash' === $status ) {
											$status_label = 'TRASH';
										}
										?>
										<tr class="tv-row" data-id="<?php echo (int) $tpl->ID; ?>" data-type="<?php echo esc_attr( $tpl->post_type ); ?>" data-title="<?php echo esc_attr( strtolower( $tpl->post_title ) ); ?>">
											<!-- Selection column checkbox -->
											<td class="check-column" style="padding-left: 20px; vertical-align: middle;">
												<input type="checkbox" name="layouts[]" value="<?php echo (int) $tpl->ID; ?>" class="tv-layout-row-checkbox" style="margin:0; cursor:pointer;" aria-label="<?php /* translators: %s: Template title */ printf( esc_attr__( 'Select template: %s', 'elonix' ), esc_attr( $tpl->post_title ) ); ?>">
											</td>

											<!-- Template Details column -->
											<td data-colname="<?php esc_attr_e( 'Template Details', 'elonix' ); ?>">
												<div class="tv-template-title-group">
													<div class="tv-template-name-row" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
														<span class="row-title" style="font-weight:700; color:var(--tv-slate-900); font-size: 16px;"><?php echo esc_html( $tpl->post_title ); ?></span>
														<span class="tv-badge <?php echo esc_attr( $type_badge ); ?>" style="font-size: 9.5px; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
															<span class="dashicons <?php echo esc_attr( $type_icon ); ?>" style="font-size: 11px; width: 11px; height: 11px; line-height: 1; margin: 0;"></span>
															<?php echo esc_html( $type_label ); ?>
														</span>
														<span class="tv-badge tv-badge-<?php echo esc_attr( $status ); ?>" style="font-size: 9.5px; padding: 2px 8px; border-radius: 12px;">
															<?php echo esc_html( $status_label ); ?>
														</span>
													</div>
													<!-- Display Conditions under Name -->
													<div style="margin-top: 10px; display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: var(--tv-slate-700);">
														<span style="font-size: 12px; color: var(--tv-slate-400); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-right: 4px;">Display Condition:</span>
														<?php $this->render_dashboard_display_conditions( $tpl->ID ); ?>
													</div>
												</div>
											</td>

											<!-- Center Metadata Panel column -->
											<?php
											$priority_int = ( '' === $priority || ! is_numeric( $priority ) ) ? 0 : intval( $priority );
											$locs         = array();
											if ( ! empty( $include_normalized ) ) {
												foreach ( $include_normalized as $inc_row ) {
													$r_rule = $inc_row['rule'];
													if ( strpos( $r_rule, 'specific' ) !== false && ! empty( $inc_row['specific'] ) ) {
														foreach ( $inc_row['specific'] as $s_spec ) {
															$locs[] = Elonix_Target_Rules::get_location_by_key( $s_spec );
														}
													} else {
														$locs[] = Elonix_Target_Rules::get_location_by_key( $r_rule );
													}
												}
											}
											$locs_string = ! empty( $locs ) ? implode( ', ', $locs ) : __( 'Draft / Deassigned', 'elonix' );
											?>
											<td data-colname="<?php esc_attr_e( 'Metadata Panel', 'elonix' ); ?>">
												<div class="tv-metadata-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px 16px; font-size: 11.5px; color: var(--tv-slate-600); line-height: 1.35;">
													<!-- Column 1: Template ID & Location -->
													<div>
														<div style="margin-bottom: 6px;">
															<span style="color: var(--tv-slate-400); font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.3px; display: block;"><?php esc_html_e( 'Template ID', 'elonix' ); ?></span>
															<span style="font-weight: 600; color: var(--tv-slate-700); display: block; margin-top: 2px;">#<?php echo (int) $tpl->ID; ?></span>
														</div>
														<div>
															<span style="color: var(--tv-slate-400); font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.3px; display: block;"><?php esc_html_e( 'Location', 'elonix' ); ?></span>
															<span style="font-weight: 500; color: var(--tv-slate-700); display: block; margin-top: 2px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 130px;" title="<?php echo esc_attr( $locs_string ); ?>">
																<?php echo esc_html( $locs_string ); ?>
															</span>
														</div>
													</div>

													<!-- Column 2: Shortcode & Priority -->
													<div>
														<div style="margin-bottom: 6px;">
															<span style="color: var(--tv-slate-400); font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.3px; display: block;"><?php esc_html_e( 'Shortcode', 'elonix' ); ?></span>
															<div style="display: flex; align-items: center; gap: 4px; margin-top: 2px;">
																<code style="font-family: monospace; background: var(--tv-slate-50); border: 1px solid var(--tv-slate-200); padding: 1px 6px; border-radius: 4px; font-size: 10.5px; font-weight: 600; color: var(--tv-slate-700); white-space: nowrap;"><?php echo esc_html( $shortcode ); ?></code>
																<button type="button" class="tv-copy-btn" data-copy-text="<?php echo esc_attr( $shortcode ); ?>" data-success-msg="<?php echo esc_attr__( 'Shortcode copied', 'elonix' ); ?>" style="border: 1px solid var(--tv-slate-250); background: #ffffff; cursor: pointer; border-radius: 4px; padding: 2px 5px; font-size: 9.5px; font-weight: 600; color: var(--tv-slate-700); display: inline-flex; align-items: center; gap: 2px; height: 18px; transition: all 0.15s ease;" title="<?php esc_attr_e( 'Copy Shortcode', 'elonix' ); ?>">
																	<span class="dashicons dashicons-admin-page" style="font-size: 10px; width: 10px; height: 10px; margin: 0; line-height: 1;"></span>
																	<?php esc_html_e( 'Copy', 'elonix' ); ?>
																</button>
															</div>
														</div>
														<div>
															<span style="color: var(--tv-slate-400); font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.3px; display: block;"><?php esc_html_e( 'Priority', 'elonix' ); ?></span>
															<span style="font-weight: 500; color: var(--tv-slate-700); display: block; margin-top: 2px;">
																<?php if ( 0 === $priority_int ) : ?>
																	<span style="color: var(--tv-slate-400);">0</span>
																<?php else : ?>
																	<span class="tv-priority-badge" style="background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; padding: 1px 5px; border-radius: 4px; font-size: 9.5px; font-weight: 700; display: inline-block; line-height: 1;"><?php echo (int) $priority_int; ?></span>
																<?php endif; ?>
															</span>
														</div>
													</div>

													<!-- Column 3: Created & Modified -->
													<div>
														<div style="margin-bottom: 6px;">
															<span style="color: var(--tv-slate-400); font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.3px; display: block;"><?php esc_html_e( 'Created', 'elonix' ); ?></span>
															<span style="font-weight: 500; color: var(--tv-slate-700); display: block; margin-top: 2px; white-space: nowrap;"><?php echo get_the_date( 'M j, Y', $tpl->ID ); ?></span>
														</div>
														<div>
															<span style="color: var(--tv-slate-400); font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.3px; display: block;"><?php esc_html_e( 'Modified', 'elonix' ); ?></span>
															<span style="font-weight: 500; color: var(--tv-slate-700); display: block; margin-top: 2px; white-space: nowrap;"><?php echo esc_html( $time_diff ); ?></span>
														</div>
													</div>
												</div>
											</td>

											<!-- Actions Column (Edit with Elementor + 3-dot Actions dropdown menu) -->
											<td data-colname="<?php esc_attr_e( 'Actions', 'elonix' ); ?>" style="text-align: right; padding-right: 20px;">
												<?php
												if ( class_exists( 'Elonix_Admin_Row_Actions' ) ) {
													Elonix_Admin_Row_Actions::instance()->render_custom_table_actions( $tpl );
												}
												?>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

					<!-- WP Native tablenav bottom -->
					<div class="tablenav bottom">
						<div class="alignleft actions bulkactions">
							<select name="tv_bulk_action_bottom" id="bulk-action-selector-bottom" aria-label="<?php esc_attr_e( 'Select bulk action', 'elonix' ); ?>">
								<option value=""><?php esc_html_e( 'Bulk Actions', 'elonix' ); ?></option>
								<?php if ( 'trash' === $status_filter ) : ?>
									<option value="restore"><?php esc_html_e( 'Restore', 'elonix' ); ?></option>
									<option value="delete_permanently"><?php esc_html_e( 'Delete Permanently', 'elonix' ); ?></option>
								<?php else : ?>
									<option value="activate"><?php esc_html_e( 'Set to Published', 'elonix' ); ?></option>
									<option value="deactivate"><?php esc_html_e( 'Set to Draft', 'elonix' ); ?></option>
									<option value="trash"><?php esc_html_e( 'Move to Trash', 'elonix' ); ?></option>
								<?php endif; ?>
							</select>
							<button type="submit" class="button action"><?php esc_html_e( 'Apply', 'elonix' ); ?></button>
						</div>

						<!-- Pagination index controls bottom -->
						<div class="tablenav-pages
						<?php
						if ( $total_pages <= 1 ) {
							echo ' one-page'; }
						?>
						">
							<span class="displaying-num">
								<?php
								/* translators: %s: Number of items */
								echo esc_html( sprintf( _n( '%s item', '%s items', $total_items, 'elonix' ), number_format_i18n( $total_items ) ) );
								?>
							</span>
							<?php if ( $total_pages > 1 ) : ?>
								<span class="pagination-links">
									<?php if ( $paged > 1 ) : ?>
										<a class="prev-page button" href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">‹</span></a>
									<?php else : ?>
										<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
									<?php endif; ?>

									<span class="screen-reader-text">Current Page</span>
									<span class="paging-input">
										<span class="current-page"><?php echo (int) $paged; ?></span> of <span class="total-pages"><?php echo (int) $total_pages; ?></span>
									</span>

									<?php if ( $paged < $total_pages ) : ?>
										<a class="next-page button" href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>"><span class="screen-reader-text">Next page</span><span aria-hidden="true">›</span></a>
									<?php else : ?>
										<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
									<?php endif; ?>
								</span>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</form>

			<!-- Floating Sticky Bulk Actions Toolbar -->
			<div id="tv_sticky_bulk_bar" class="tv-sticky-bulk-bar">
				<div class="tv-sticky-bulk-inner">
					<span class="tv-selected-count">0 templates selected</span>
					<div class="tv-sticky-bulk-actions">
						<select name="tv_sticky_bulk_action" id="tv_sticky_bulk_action" class="tv-select">
							<option value=""><?php esc_html_e( 'Choose Action...', 'elonix' ); ?></option>
							<?php if ( 'trash' === $status_filter ) : ?>
								<option value="restore"><?php esc_html_e( 'Restore Selected', 'elonix' ); ?></option>
								<option value="delete_permanently"><?php esc_html_e( 'Delete Permanently', 'elonix' ); ?></option>
							<?php else : ?>
								<option value="activate"><?php esc_html_e( 'Publish Selected', 'elonix' ); ?></option>
								<option value="deactivate"><?php esc_html_e( 'Move to Draft', 'elonix' ); ?></option>
								<option value="trash"><?php esc_html_e( 'Move to Trash', 'elonix' ); ?></option>
							<?php endif; ?>
						</select>
						<button type="button" id="tv_sticky_bulk_apply" class="tv-btn tv-btn-primary"><?php esc_html_e( 'Apply', 'elonix' ); ?></button>
						<button type="button" id="tv_sticky_bulk_cancel" class="tv-btn tv-btn-secondary"><?php esc_html_e( 'Cancel', 'elonix' ); ?></button>
					</div>
				</div>
			</div>

			<!-- MODAL: CREATE LAYOUT (TABBED INCLUDES FORM CREATE AND FILE IMPORT) -->
			<div class="tv-modal-overlay" id="tv_create_modal" role="dialog" aria-modal="true" aria-labelledby="modal_create_title">
				<div class="tv-modal-card">
					<div class="tv-modal-header">
						<h3 class="tv-modal-title" id="modal_create_title"><?php esc_html_e( 'Add Layout Template', 'elonix' ); ?></h3>
						<button type="button" class="tv-modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'elonix' ); ?>" id="tv_create_modal_close">&times;</button>
					</div>

					<!-- Modal Tabs -->
					<div class="tv-modal-tabs">
						<div class="tv-modal-tab-link active" data-tab="create_new"><?php esc_html_e( 'Create New Template', 'elonix' ); ?></div>
						<div class="tv-modal-tab-link" data-tab="import_file"><?php esc_html_e( 'Import JSON Template', 'elonix' ); ?></div>
					</div>

					<!-- Forms container -->
					<div class="tv-modal-tabs-body">
						<!-- TAB 1: CREATE NEW TEMPLATE -->
						<div class="tv-modal-tab-content" id="tab_create_new">
							<form method="post" enctype="multipart/form-data">
								<?php wp_nonce_field( 'tv_create_template', 'tv_create_template_nonce_field' ); ?>

								<div class="tv-field">
									<label for="template_name"><?php esc_html_e( 'Template Name', 'elonix' ); ?></label>
									<input type="text" id="template_name" name="template_name" class="tv-input" placeholder="<?php esc_attr_e( 'e.g. Navigation Header Layout', 'elonix' ); ?>" required>
								</div>

								<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
									<div class="tv-field">
										<label for="template_type"><?php esc_html_e( 'Template Type', 'elonix' ); ?></label>
										<select id="template_type" name="template_type" class="tv-select">
											<?php
											if ( $header_active ) :
												?>
												<option value="tv_header"><?php esc_html_e( 'Header', 'elonix' ); ?></option><?php endif; ?>
											<?php
											if ( $footer_active ) :
												?>
												<option value="tv_footer"><?php esc_html_e( 'Footer', 'elonix' ); ?></option><?php endif; ?>
										</select>
									</div>

									<div class="tv-field">
										<label for="priority"><?php esc_html_e( 'Priority', 'elonix' ); ?></label>
										<input type="number" id="priority" name="priority" value="0" min="0" step="1" class="tv-input">
									</div>
								</div>

								<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
									<div class="tv-field">
										<label for="display_condition"><?php esc_html_e( 'Display Condition', 'elonix' ); ?></label>
										<select id="display_condition" name="display_condition" class="tv-select tv-modal-condition-select">
											<option value=""><?php esc_html_e( 'None (Draft / Deassigned)', 'elonix' ); ?></option>
											<?php
											$location_selections = Elonix_Target_Rules::get_location_selections();
											foreach ( $location_selections as $group_key => $group_data ) {
												?>
												<optgroup label="<?php echo esc_attr( $group_data['label'] ); ?>">
													<?php foreach ( $group_data['value'] as $opt_key => $opt_label ) { ?>
														<option value="<?php echo esc_attr( $opt_key ); ?>"><?php echo esc_html( $opt_label ); ?></option>
													<?php } ?>
												</optgroup>
												<?php
											}
											?>
										</select>
									</div>

									<div class="tv-field">
										<label for="status"><?php esc_html_e( 'Status', 'elonix' ); ?></label>
										<select id="status" name="status" class="tv-select">
											<option value="publish"><?php esc_html_e( 'Published', 'elonix' ); ?></option>
											<option value="draft" selected><?php esc_html_e( 'Draft', 'elonix' ); ?></option>
										</select>
									</div>
								</div>

								<!-- Display conditions Select2 targets search container -->
								<div class="tv-specific-search-wrapper" style="display:none; margin-bottom: 16px;">
									<label for="display_condition_specific"><?php esc_html_e( 'Select Specific Target', 'elonix' ); ?></label>
									<select id="display_condition_specific" name="display_condition_specific[]" class="tv-select2-ajax-search" multiple="multiple" style="width: 100%;"></select>
								</div>

								<div class="tv-field">
									<label for="clone_layout"><?php esc_html_e( 'Duplicate From Existing Template (Optional)', 'elonix' ); ?></label>
									<select id="clone_layout" name="clone_layout" class="tv-select">
										<option value="0"><?php esc_html_e( '-- Select Template --', 'elonix' ); ?></option>
										<?php foreach ( $existing_layouts as $ex_l ) : ?>
											<option value="<?php echo (int) $ex_l->ID; ?>"><?php echo esc_html( $ex_l->post_title ); ?></option>
										<?php endforeach; ?>
									</select>
								</div>

								<div class="tv-field" style="display:flex; align-items:center; gap:8px; margin-top: 10px;">
									<input type="checkbox" id="open_elementor" name="open_elementor" value="1" checked style="width:16px; height:16px; margin:0; cursor:pointer;">
									<label for="open_elementor" style="margin:0; cursor:pointer; font-weight:500; font-size: 13px;"><?php esc_html_e( 'Open in Elementor editor immediately', 'elonix' ); ?></label>
								</div>

								<div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px; border-top:1px solid var(--tv-slate-200); padding-top:16px;">
									<button type="button" class="tv-btn tv-btn-secondary tv-modal-cancel"><?php esc_html_e( 'Cancel', 'elonix' ); ?></button>
									<button type="submit" class="tv-btn tv-btn-primary"><?php esc_html_e( 'Create Template', 'elonix' ); ?></button>
								</div>
							</form>
						</div>

						<!-- TAB 2: IMPORT TEMPLATE FILE -->
						<div class="tv-modal-tab-content" id="tab_import_file" style="display:none;">
							<form method="post" enctype="multipart/form-data">
								<?php wp_nonce_field( 'tv_create_template', 'tv_create_template_nonce_field' ); ?>

								<div class="tv-field">
									<label for="tv_import_file"><?php esc_html_e( 'Upload JSON File', 'elonix' ); ?></label>
									<input type="file" id="tv_import_file" name="tv_import_file" accept=".json" required style="width:100%; border:1px dashed var(--tv-slate-300); border-radius:8px; padding:20px; font-size:13.5px; font-family:'Inter',sans-serif; background:var(--tv-slate-50); cursor:pointer;">
								</div>

								<div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px; border-top:1px solid var(--tv-slate-200); padding-top:16px;">
									<button type="button" class="tv-btn tv-btn-secondary tv-modal-cancel"><?php esc_html_e( 'Cancel', 'elonix' ); ?></button>
									<button type="submit" class="tv-btn tv-btn-primary"><?php esc_html_e( 'Import JSON File', 'elonix' ); ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<!-- MODAL: QUICK EDIT PROPERTIES -->
			<div class="tv-modal-overlay" id="tv_quick_edit_modal" role="dialog" aria-modal="true" aria-labelledby="modal_quick_title">
				<div class="tv-modal-card">
					<div class="tv-modal-header">
						<h3 class="tv-modal-title" id="modal_quick_title"><?php esc_html_e( 'Quick Edit Layout Properties', 'elonix' ); ?></h3>
						<button type="button" class="tv-modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'elonix' ); ?>" id="tv_quick_edit_close">&times;</button>
					</div>
					<form method="post">
						<?php wp_nonce_field( 'tv_quick_edit', 'tv_quick_edit_nonce_field' ); ?>
						<input type="hidden" id="quick_edit_id" name="layout_id" value="0">

						<div class="tv-field">
							<label for="quick_edit_name"><?php esc_html_e( 'Template Name', 'elonix' ); ?></label>
							<input type="text" id="quick_edit_name" name="template_name" class="tv-input" required>
						</div>

						<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
							<div class="tv-field">
								<label for="quick_edit_type"><?php esc_html_e( 'Template Type', 'elonix' ); ?></label>
								<select id="quick_edit_type" name="template_type" class="tv-select">
									<option value="tv_header"><?php esc_html_e( 'Header', 'elonix' ); ?></option>
									<option value="tv_footer"><?php esc_html_e( 'Footer', 'elonix' ); ?></option>
								</select>
							</div>

							<div class="tv-field">
								<label for="quick_edit_priority"><?php esc_html_e( 'Priority', 'elonix' ); ?></label>
								<input type="number" id="quick_edit_priority" name="priority" value="0" min="0" step="1" class="tv-input">
							</div>
						</div>

						<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
							<div class="tv-field">
								<label for="quick_edit_condition"><?php esc_html_e( 'Display Condition', 'elonix' ); ?></label>
								<select id="quick_edit_condition" name="display_condition" class="tv-select tv-modal-condition-select">
									<option value=""><?php esc_html_e( 'None (Draft / Deassigned)', 'elonix' ); ?></option>
									<?php
									foreach ( $location_selections as $group_key => $group_data ) {
										?>
										<optgroup label="<?php echo esc_attr( $group_data['label'] ); ?>">
											<?php foreach ( $group_data['value'] as $opt_key => $opt_label ) { ?>
												<option value="<?php echo esc_attr( $opt_key ); ?>"><?php echo esc_html( $opt_label ); ?></option>
											<?php } ?>
										</optgroup>
										<?php
									}
									?>
								</select>
							</div>

							<div class="tv-field">
								<label for="quick_edit_status"><?php esc_html_e( 'Status', 'elonix' ); ?></label>
								<select id="quick_edit_status" name="status" class="tv-select">
									<option value="publish"><?php esc_html_e( 'Published', 'elonix' ); ?></option>
									<option value="draft"><?php esc_html_e( 'Draft', 'elonix' ); ?></option>
								</select>
							</div>
						</div>

						<!-- Display conditions Select2 targets search container -->
						<div class="tv-specific-search-wrapper" style="display:none; margin-bottom: 16px;">
							<label for="quick_edit_display_condition_specific"><?php esc_html_e( 'Select Specific Target', 'elonix' ); ?></label>
							<select id="quick_edit_display_condition_specific" name="display_condition_specific[]" class="tv-select2-ajax-search" multiple="multiple" style="width: 100%;"></select>
						</div>

						<div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px; border-top:1px solid var(--tv-slate-200); padding-top:16px;">
							<button type="button" class="tv-btn tv-btn-secondary tv-modal-cancel"><?php esc_html_e( 'Cancel', 'elonix' ); ?></button>
							<button type="submit" class="tv-btn tv-btn-primary"><?php esc_html_e( 'Update Layout', 'elonix' ); ?></button>
						</div>
					</form>
				</div>
			</div>

			<!-- MODAL: GET SHORTCODE -->
			<div class="tv-modal-overlay" id="tv_shortcode_modal" role="dialog" aria-modal="true" aria-labelledby="modal_shortcode_title">
				<div class="tv-modal-card">
					<div class="tv-modal-header">
						<h3 class="tv-modal-title" id="modal_shortcode_title"><?php esc_html_e( 'Template Shortcode & PHP Code', 'elonix' ); ?></h3>
						<button type="button" class="tv-modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'elonix' ); ?>" id="tv_shortcode_close">&times;</button>
					</div>
					<div>
						<!-- Shortcode Info -->
						<div class="tv-field" style="margin-bottom:20px;">
							<label style="font-weight:600; display:block; margin-bottom:8px;"><?php esc_html_e( 'WordPress Shortcode', 'elonix' ); ?></label>
							<p style="font-size:12.5px; color:var(--tv-slate-500); margin:0 0 8px 0;"><?php esc_html_e( 'Copy and paste this shortcode into your post, page, widget, block, or Elementor shortcode widget.', 'elonix' ); ?></p>
							<div style="display:flex; gap:10px; align-items:center;">
								<input type="text" id="shortcode_modal_text" class="tv-input" readonly style="font-family:monospace; background:var(--tv-slate-50); flex:1;">
								<button type="button" class="tv-btn tv-btn-primary tv-copy-btn" id="copy_shortcode_modal_btn" data-copy-text="" data-success-msg="<?php echo esc_attr__( 'Shortcode copied', 'elonix' ); ?>" style="white-space:nowrap;">
									<span class="dashicons dashicons-admin-page" style="font-size:16px; width:16px; height:16px; margin:0;"></span>
									<?php esc_html_e( 'Copy Shortcode', 'elonix' ); ?>
								</button>
							</div>
						</div>

						<!-- PHP Function Info -->
						<div class="tv-field" style="margin-bottom:20px; border-top: 1px solid var(--tv-slate-200); padding-top: 20px;">
							<label style="font-weight:600; display:block; margin-bottom:8px;"><?php esc_html_e( 'PHP Function Call', 'elonix' ); ?></label>
							<p style="font-size:12.5px; color:var(--tv-slate-500); margin:0 0 8px 0;"><?php esc_html_e( 'Copy and paste this PHP code snippet into your active theme templates (like header.php or footer.php) to render the layout dynamically.', 'elonix' ); ?></p>
							<div style="display:flex; gap:10px; align-items:center;">
								<input type="text" id="php_modal_text" class="tv-input" readonly style="font-family:monospace; background:var(--tv-slate-50); flex:1;">
								<button type="button" class="tv-btn tv-btn-secondary tv-copy-btn" id="copy_php_modal_btn" data-copy-text="" data-success-msg="<?php echo esc_attr__( 'PHP code copied', 'elonix' ); ?>" style="white-space:nowrap;">
									<span class="dashicons dashicons-admin-page" style="font-size:16px; width:16px; height:16px; margin:0;"></span>
									<?php esc_html_e( 'Copy PHP Code', 'elonix' ); ?>
								</button>
							</div>
						</div>

						<div style="display:flex; justify-content:flex-end; margin-top:20px; border-top:1px solid var(--tv-slate-200); padding-top:16px;">
							<button type="button" class="tv-btn tv-btn-secondary tv-modal-cancel" id="tv_shortcode_cancel"><?php esc_html_e( 'Close', 'elonix' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- MODALS & LIVE SEARCH SCRIPTS -->
		<script>
			jQuery(document).ready(function($) {
				// Debug logging removed for production

				// Setup Focus Trap Utility for Accessibility
				function setupFocusTrap($modal) {
					var focusableElements = $modal.find('a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex="0"], [contenteditable]');
					var $first = focusableElements.first();
					var $last = focusableElements.last();

					$modal.off('keydown.focus_trap').on('keydown.focus_trap', function(e) {
						if (e.key === 'Tab' || e.keyCode === 9) {
							if (e.shiftKey) {
								if (document.activeElement === $first[0]) {
									$last.focus();
									e.preventDefault();
								}
							} else {
								if (document.activeElement === $last[0]) {
									$first.focus();
									e.preventDefault();
								}
							}
						}
					});
				}

				// Search endpoint config
				function initSelect2Ajax(element) {
					var $select = $(element);
					if ($select.hasClass('select2-hidden-accessible')) {
						return; // Already initialized
					}
					$select.select2({
						width: '100%',
						dropdownParent: $select.closest('.tv-modal-card'),
						placeholder: '<?php esc_html_e( 'Search posts, pages, categories, tags...', 'elonix' ); ?>',
						minimumInputLength: 2,
						language: {
							noResults: function() {
								return '<?php echo esc_js( __( 'No matching pages found.', 'elonix' ) ); ?>';
							},
							searching: function() {
								return '<?php echo esc_js( __( 'Searching...', 'elonix' ) ); ?>';
							},
							inputTooShort: function(args) {
								return '<?php echo esc_js( __( 'Please enter 2 or more characters', 'elonix' ) ); ?>';
							}
						},
						ajax: {
							url: ajaxurl,
							dataType: 'json',
							delay: 250,
							method: 'POST',
							data: function(params) {
								var ruleVal = $select.closest('form').find('.tv-modal-condition-select').val();
								return {
									q: params.term,
									rule: ruleVal,
									action: 'elonix_get_posts_by_query',
									nonce: '<?php echo esc_js( wp_create_nonce( 'tv-get-posts-by-query' ) ); ?>'
								};
							},
processResults: function(data) {
									return {
										results: data
									};
								},
							cache: true
						}
					});

					$select.on('select2:select', function(e) {
						});
				}

				// Tab Toggle for create modal
				$('.tv-modal-tab-link').on('click', function(e) {
					e.preventDefault();
					$('.tv-modal-tab-link').removeClass('active');
					$(this).addClass('active');

					var targetTab = $(this).data('tab');
					if ('create_new' === targetTab) {
						$('#tab_create_new').show();
						$('#tab_import_file').hide();
					} else {
						$('#tab_create_new').hide();
						$('#tab_import_file').show();
					}
					setupFocusTrap($(this).closest('.tv-modal-overlay'));
				});

				// Triggers Modal: Create Header
				$('#tv_create_header_btn').on('click', function() {
					$('#tv_create_modal').find('form')[0].reset();
					$('#tv_create_modal').find('.tv-modal-condition-select').val('').trigger('change');
					$('#tv_create_modal').find('.tv-select2-ajax-search').val(null).trigger('change');
					$('#template_type').val('tv_header');
					$('.tv-modal-tab-link[data-tab="create_new"]').click();
					$('#tv_create_modal').addClass('open');
					setupFocusTrap($('#tv_create_modal'));
					$('#template_name').focus();
				});

				// Triggers Modal: Create Footer
				$('#tv_create_footer_btn').on('click', function() {
					$('#tv_create_modal').find('form')[0].reset();
					$('#tv_create_modal').find('.tv-modal-condition-select').val('').trigger('change');
					$('#tv_create_modal').find('.tv-select2-ajax-search').val(null).trigger('change');
					$('#template_type').val('tv_footer');
					$('.tv-modal-tab-link[data-tab="create_new"]').click();
					$('#tv_create_modal').addClass('open');
					setupFocusTrap($('#tv_create_modal'));
					$('#template_name').focus();
				});

				// Triggers Modal: Import JSON
				$('#tv_import_btn').on('click', function() {
					$('#tv_create_modal').addClass('open');
					$('.tv-modal-tab-link[data-tab="import_file"]').click();
					setupFocusTrap($('#tv_create_modal'));
					$('#tv_import_file').focus();
				});

				// Triggers Modal: Close triggers
				$('#tv_create_modal_close, .tv-modal-cancel').on('click', function() {
					$('.tv-modal-overlay').removeClass('open');
				});

				// Escape button modal and dropdown close helper
				$(document).on('keydown', function(e) {
					if (e.keyCode === 27) { // ESC key
						$('.tv-modal-overlay').removeClass('open');
						$('.tv-actions-dropdown-wrapper').removeClass('active');
						$('.tv-row').removeClass('tv-row-active');
					}
				});

				// Select all row checkboxes (supporting top and bottom checkbox syncing)
				$('#cb-select-all-top').on('change', function() {
					var isChecked = $(this).is(':checked');
					$('.tv-layout-row-checkbox').prop('checked', isChecked);
					setTimeout(updateStickyBulkBar, 50);
				});

				// Also support checkbox checking manually
				$('.tv-layout-row-checkbox').on('change', function() {
					var allChecked = $('.tv-layout-row-checkbox:not(:checked)').length === 0;
					$('#cb-select-all-top').prop('checked', allChecked);
					setTimeout(updateStickyBulkBar, 50);
				});

				// Sticky Bulk Bar count/visibility updater
				function updateStickyBulkBar() {
					var selectedCount = $('.tv-layout-row-checkbox:checked').length;
					if (selectedCount > 0) {
						$('.tv-selected-count').text(selectedCount + ' ' + (selectedCount === 1 ? 'template' : 'templates') + ' selected');
						$('#tv_sticky_bulk_bar').addClass('active');
					} else {
						$('#tv_sticky_bulk_bar').removeClass('active');
					}
				}

				$('#tv_sticky_bulk_cancel').on('click', function() {
					$('.tv-layout-row-checkbox, #cb-select-all-top').prop('checked', false);
					updateStickyBulkBar();
				});

				$('#tv_sticky_bulk_apply').on('click', function(e) {
					e.preventDefault();
					var actionVal = $('#tv_sticky_bulk_action').val();
					if (!actionVal) {
						if (typeof ElonixNotifier !== 'undefined') {
							ElonixNotifier.warning('Please select a bulk action to apply.');
						}
						return;
					}
					// Populate the top select and trigger submission
					$('#bulk-action-selector-top').val(actionVal);
					$('#tv_bulk_form').submit();
				});

				// Dropdown Actions Toggle Handler (Click-based dropdown with active row z-index raising)
				$(document).on('click', '.tv-actions-dropdown-trigger', function(e) {
					e.preventDefault();
					e.stopPropagation();
					var $wrapper = $(this).closest('.tv-actions-dropdown-wrapper');
					var $row = $(this).closest('.tv-row');

					// Close other dropdowns and reset row z-index values
					$('.tv-actions-dropdown-wrapper').not($wrapper).removeClass('active');
					$('.tv-row').not($row).removeClass('tv-row-active');

					$wrapper.toggleClass('active');
					$row.toggleClass('tv-row-active', $wrapper.hasClass('active'));
				});

				$(document).on('click', function(e) {
					if (!$(e.target).closest('.tv-actions-dropdown-wrapper').length) {
						$('.tv-actions-dropdown-wrapper').removeClass('active');
						$('.tv-row').removeClass('tv-row-active');
					}
				});

				// Quick Edit triggers handler (Delegated event binding for dynamic rows reliability)
				$(document).on('click', '.tv-quick-edit-trigger', function(e) {
					e.preventDefault();
					var id = $(this).data('id');
					var title = $(this).data('title');
					var type = $(this).data('type');
					var priority = $(this).data('priority');
					var status = $(this).data('status');
					var condition = $(this).data('condition');
					var specifics = $(this).data('specifics');

					// Safe parsing for specifics JSON attribute data
					if (typeof specifics === 'string') {
						try {
							specifics = JSON.parse(specifics);
						} catch (err) {
							specifics = [];
						}
					}
					if (!specifics || !Array.isArray(specifics)) {
						specifics = [];
					}

					$('#quick_edit_id').val(id);
					$('#quick_edit_name').val(title);
					$('#quick_edit_type').val(type);
					$('#quick_edit_priority').val(priority);
					$('#quick_edit_status').val(status);

					var $conditionSelect = $('#quick_edit_condition');
					$conditionSelect.val(condition);

					var $searchWrapper = $('#tv_quick_edit_modal').find('.tv-specific-search-wrapper');
					var $select2 = $searchWrapper.find('.tv-select2-ajax-search');

					// Clear select2
					$select2.empty().val(null).trigger('change');

					if (condition && condition.indexOf('specific') !== -1) {
						$searchWrapper.show();
						initSelect2Ajax($select2);

						// Render selected specifics options
						if (specifics && specifics.length > 0) {
							specifics.forEach(function(item) {
								var opt = new Option(item.text, item.id, true, true);
								$select2.append(opt);
							});
							$select2.trigger('change');
						}
					} else {
						$searchWrapper.hide();
					}

					$('#tv_quick_edit_modal').addClass('open');
					setupFocusTrap($('#tv_quick_edit_modal'));
					$('#quick_edit_name').focus();
				});

				$('#tv_quick_edit_close').on('click', function() {
					$('#tv_quick_edit_modal').removeClass('open');
				});

				// Dynamic display conditions select toggling
				$(document).on('change', '.tv-modal-condition-select', function() {
					var ruleVal = $(this).val();
					var $modal = $(this).closest('.tv-modal-card');
					var $searchWrapper = $modal.find('.tv-specific-search-wrapper');
					var $select2 = $searchWrapper.find('.tv-select2-ajax-search');

					$select2.empty().val(null).trigger('change');

					if (ruleVal && ruleVal.indexOf('specific') !== -1) {
						$searchWrapper.show();
						initSelect2Ajax($select2);
					} else {
						$searchWrapper.hide();
					}
				});

				// JS Search Filter (searches template name, shortcode ID, or literal shortcode syntax, or type)
				$('#tv_live_search').on('keyup', function() {
					var searchValue = $(this).val().toLowerCase().trim();
					// Regex helper to extract ID if user types something like [tv_header id="12"]
					var idMatch = searchValue.match(/\bid\s*=\s*["']?(\d+)["']?/);
					var idSearch = idMatch ? idMatch[1] : ($.isNumeric(searchValue) ? searchValue : '');

					$('.tv-row').each(function() {
						var $row = $(this);
						var rowTitle = $row.data('title') || '';
						var rowId = String($row.data('id') || '');
						var rowType = $row.data('type') || ''; // 'tv_header' or 'tv_footer'
						var typeFriendly = ('tv_header' === rowType) ? 'header' : 'footer';

						var matches = false;
						if (rowTitle.indexOf(searchValue) > -1) {
							matches = true;
						} else if (rowId.indexOf(searchValue) > -1 || (idSearch && rowId === idSearch)) {
							matches = true;
						} else if (rowType.indexOf(searchValue) > -1 || typeFriendly.indexOf(searchValue) > -1) {
							matches = true;
						}

						if (matches) {
							$row.show();
						} else {
							$row.hide();
						}
					});
				});

				// Get Shortcode Modal Trigger
				$('.tv-shortcode-modal-trigger').on('click', function(e) {
					e.preventDefault();
					var shortcode = $(this).data('shortcode');
					var php = $(this).data('php');

					$('#shortcode_modal_text').val(shortcode);
					$('#php_modal_text').val(php);
					$('#copy_shortcode_modal_btn').attr('data-copy-text', shortcode);
					$('#copy_php_modal_btn').attr('data-copy-text', php);

					$('#tv_shortcode_modal').addClass('open');
					setupFocusTrap($('#tv_shortcode_modal'));
				});

				$('#tv_shortcode_close, #tv_shortcode_cancel').on('click', function() {
					$('#tv_shortcode_modal').removeClass('open');
				});

				// Toast Notification Helper
				function tvShowToast(message) {
					var $toast = $('#tv-toast-notification');
					if ($toast.length === 0) {
						$toast = $('<div id="tv-toast-notification" style="position:fixed; bottom:20px; right:20px; background:#ffffff; color:#2c3338; border-left:4px solid #46b450; padding:10px 16px; font-size:13px; font-weight:600; font-family:\'Inter\', sans-serif; box-shadow:0 3px 6px rgba(0,0,0,0.1); z-index:999999; display:none; border-radius:0 4px 4px 0;"></div>');
						$('body').append($toast);
					}
					$toast.text(message).stop(true, true).fadeIn(150).delay(2000).fadeOut(250);
				}

				// Copy to Clipboard Event Handler
				$(document).on('click', '.tv-copy-btn', function(e) {
					e.preventDefault();
					var textToCopy = $(this).attr('data-copy-text');
					var successMsg = $(this).attr('data-success-msg') || 'Copied!';

					if (navigator.clipboard && navigator.clipboard.writeText) {
						navigator.clipboard.writeText(textToCopy).then(function() {
							tvShowToast(successMsg);
						}, function() {
							fallbackCopy(textToCopy, successMsg);
						});
					} else {
						fallbackCopy(textToCopy, successMsg);
					}
				});

				function fallbackCopy(textToCopy, successMsg) {
					var $temp = $('<input>');
					$('body').append($temp);
					$temp.val(textToCopy).select();
					document.execCommand('copy');
					$temp.remove();
					tvShowToast(successMsg);
				}
			});
		</script>
		<?php
	}
}
