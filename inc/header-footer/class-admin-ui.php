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
		$post_types = array( 'es_header', 'es_footer', 'es_single', 'es_archive', 'es_search_template' );
		foreach ( $post_types as $pt ) {
			add_filter( "manage_{$pt}_posts_columns", array( $this, 'register_custom_columns' ), 11 );
			add_action( "manage_{$pt}_posts_custom_column", array( $this, 'render_custom_columns_data' ), 11, 2 );
		}

		// Quick and Bulk actions hook
		add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
		// Load assets on builder dashboard pages
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_assets' ) );

		// Add persistent admin navigation bar above post editor
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_navbar_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_navbar_styles' ) );
		add_action( 'admin_notices', array( $this, 'add_settings_workflow_navigation' ) );
	}

	/**
	 * Define custom columns for CPT lists.
	 */
	public function register_custom_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $title ) {
			if ( 'date' === $key ) {
				$new_columns['es_display_conditions'] = esc_html__( 'Display Conditions', 'elonix' );
				$new_columns['es_shortcode']          = esc_html__( 'Shortcode', 'elonix' );
				$new_columns['es_priority']           = esc_html__( 'Priority', 'elonix' );
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

		wp_enqueue_script(
			'elonix-admin-ui',
			ELONIX_ACC_URL . 'assets/js/admin-ui.js',
			array( 'jquery', 'select2' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'elonix-admin-ui',
			'esAdminUiL10n',
			array(
				'placeholder'   => esc_html__( 'Search posts, pages, categories, tags...', 'elonix' ),
				'noResults'     => esc_js( __( 'No matching pages found.', 'elonix' ) ),
				'searching'     => esc_js( __( 'Searching...', 'elonix' ) ),
				'inputTooShort' => esc_js( __( 'Please enter 2 or more characters', 'elonix' ) ),
				'nonce'         => esc_js( wp_create_nonce( 'es-get-posts-by-query' ) ),
			)
		);

		wp_enqueue_style(
			'elonix-admin-ui',
			ELONIX_ACC_URL . 'assets/admin/css/admin-ui.css',
			array(),
			ELONIX_VERSION
		);
	}

	/**
	 * Output persistent admin navigation bar at the top of the post edit screen for Elonix CPTs.
	 */
	/**
	 * Enqueue nav bar styles on post edit screens.
	 */
	public function enqueue_navbar_styles( $hook ) {
		global $post;
		if ( ! $post || ! in_array( $post->post_type, array( 'es_header', 'es_footer' ), true ) ) {
			return;
		}

		wp_enqueue_style(
			'elonix-admin-ui-nav',
			ELONIX_ACC_URL . 'assets/admin/css/admin-ui.css',
			array(),
			ELONIX_VERSION
		);
	}

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

		if ( ! in_array( $post_type, array( 'es_header', 'es_footer' ), true ) ) {
			return;
		}

		// Retrieve post details
		$post_title = $post_id ? get_the_title( $post_id ) : esc_html__( 'New Template', 'elonix' );
		$type_label = ( 'es_header' === $post_type ) ? esc_html__( 'Header', 'elonix' ) : esc_html__( 'Footer', 'elonix' );

		// Preserve tab state URL
		$back_url = admin_url( 'admin.php?page=elonix-header-footer' );
		if ( 'es_header' === $post_type ) {
			$back_url = add_query_arg( 'es_type', 'es_header', $back_url );
		} elseif ( 'es_footer' === $post_type ) {
			$back_url = add_query_arg( 'es_type', 'es_footer', $back_url );
		}

		// Secure preview URL
		$preview_url = $post_id ? add_query_arg( 'es_preview', $post_id, home_url( '/' ) ) : '';

		?>
		<div class="es-admin-nav-bar" style="background: #fff; border: 1px solid #ccd0d4; padding: 15px 20px; margin: 10px 20px 20px 0; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;">
			<div class="es-nav-left" style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #64748b;">
				<span style="font-weight: 600; color: #1e293b;"><?php esc_html_e( 'Elonix Header & Footer Builder', 'elonix' ); ?></span>
				<span style="color: #cbd5e1;">&gt;</span>
				<span style="color: #64748b; font-weight: 500;"><?php echo esc_html( $type_label ); ?>:</span>
				<span style="color: #0f172a; font-weight: 600;"><?php echo esc_html( $post_title ); ?></span>
			</div>

			<div class="es-nav-right" style="display: flex; align-items: center; gap: 12px; margin-top: 5px;">
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
		<!-- NAV BAR CSS MOVED EXTERNALLY -->
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
		$include = Elonix_Target_Rules::normalize_rules( get_post_meta( $post_id, '_es_target_include_locations', true ) );
		$exclude = Elonix_Target_Rules::normalize_rules( get_post_meta( $post_id, '_es_target_exclude_locations', true ) );
		$roles   = get_post_meta( $post_id, '_es_target_user_roles', true );
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
			$outputs[] = '<span class="es-dashboard-condition-inc">' . implode( ', ', $lbls ) . '</span>';
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
			$outputs[] = '<span class="es-dashboard-condition-exc"><span class="dashicons dashicons-dismiss" style="font-size:14px; width:14px; height:14px; color:var(--es-danger); vertical-align:text-bottom; margin-right:4px;"></span>' . implode( ', ', $lbls ) . '</span>';
		}

		if ( is_array( $roles ) && ! empty( $roles ) ) {
			$lbls = array();
			foreach ( $roles as $role ) {
				$lbls[] = Elonix_Target_Rules::get_user_by_key( $role );
			}
			$outputs[] = '<span class="es-dashboard-condition-roles"><span class="dashicons dashicons-groups" style="font-size:14px; width:14px; height:14px; color:var(--es-primary); vertical-align:text-bottom; margin-right:4px;"></span>' . esc_html( implode( ', ', $lbls ) ) . '</span>';
		}

		if ( empty( $outputs ) ) {
			echo '<span class="es-dashboard-condition-empty" style="color: var(--es-slate-400); font-style: italic; font-size: 12.5px;">' . esc_html__( 'No conditions set (Draft)', 'elonix' ) . '</span>';
		} else {
			echo '<div class="es-dashboard-conditions-list" style="font-size: 13px; font-weight: 500; color: var(--es-slate-600); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">' . implode( ' <span class="es-meta-divider" style="color: var(--es-slate-200); font-size: 8px;">•</span> ', $outputs ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Render custom column contents.
	 */
	public function render_custom_columns_data( $column, $post_id ) {
		switch ( $column ) {
			case 'es_priority':
				$priority = get_post_meta( $post_id, '_es_priority', true );
				echo esc_html( '' === $priority ? '0' : $priority );
				break;

			case 'es_shortcode':
				$post_type = get_post_type( $post_id );
				$shortcode = '';
				if ( 'es_header' === $post_type ) {
					$shortcode = '[es_header id="' . $post_id . '"]';
				} elseif ( 'es_footer' === $post_type ) {
					$shortcode = '[es_footer id="' . $post_id . '"]';
				}
				if ( ! empty( $shortcode ) ) {
					?>
					<div class="es-shortcode-wrapper" style="display:flex; align-items:center; gap:8px;">
						<code class="es-shortcode-code" style="font-family:monospace; background:var(--es-slate-100); padding:4px 8px; border-radius:6px; border:1px solid var(--es-slate-200); font-size:12px; font-weight:600; color:var(--es-slate-800); white-space:nowrap;"><?php echo esc_html( $shortcode ); ?></code>
						<button type="button" class="es-btn es-btn-secondary es-copy-btn" style="height: 28px; padding: 0 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px; border-radius: 6px; cursor: pointer; background:var(--es-slate-100); border:1px solid var(--es-slate-250); color:var(--es-slate-700);" data-copy-text="<?php echo esc_attr( $shortcode ); ?>" data-success-msg="<?php echo esc_attr__( 'Shortcode copied', 'elonix' ); ?>">
							<span class="dashicons dashicons-admin-page" style="font-size:14px; width:14px; height:14px; margin:0;"></span>
							<?php esc_html_e( 'Copy', 'elonix' ); ?>
						</button>
					</div>
					<?php
				} else {
					echo '<span style="color:var(--es-slate-400);">-</span>';
				}
				break;

			case 'es_display_conditions':
				$include = Elonix_Target_Rules::normalize_rules( get_post_meta( $post_id, '_es_target_include_locations', true ) );
				$exclude = Elonix_Target_Rules::normalize_rules( get_post_meta( $post_id, '_es_target_exclude_locations', true ) );
				$roles   = get_post_meta( $post_id, '_es_target_user_roles', true );
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
					$outputs[] = '<div class="es-condition-pill"><span class="es-condition-badge-inc">' . esc_html__( 'Include', 'elonix' ) . '</span> ' . implode( ', ', $lbls ) . '</div>';
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
					$outputs[] = '<div class="es-condition-pill"><span class="es-condition-badge-exc">' . esc_html__( 'Exclude', 'elonix' ) . '</span> ' . implode( ', ', $lbls ) . '</div>';
				}

				if ( is_array( $roles ) && ! empty( $roles ) ) {
					$lbls = array();
					foreach ( $roles as $role ) {
						$lbls[] = Elonix_Target_Rules::get_user_by_key( $role );
					}
					$outputs[] = '<div class="es-condition-pill"><span style="background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; border-radius: 4px; padding: 2px 4px; font-size: 9px; font-weight: 700; text-transform: uppercase;">Audience</span> <span class="dashicons dashicons-groups" style="font-size:14px; width:14px; height:14px; vertical-align:text-bottom; margin-right:4px;"></span>' . esc_html( implode( ', ', $lbls ) ) . '</div>';
				}

				if ( empty( $outputs ) ) {
					echo '<span style="color: var(--es-slate-400); font-style: italic; font-size: 12px;">' . esc_html__( 'No conditions set (Draft)', 'elonix' ) . '</span>';
				} else {
					echo '<div class="es-display-condition-wrapper">' . implode( '', $outputs ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				break;
		}
	}

	/**
	 * Custom Row Actions links in CPT tables.
	 */
	public function custom_row_actions( $actions, $post ) {
		if ( ! in_array( $post->post_type, array( 'es_header', 'es_footer' ), true ) ) {
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
			admin_url( 'admin.php?action=es_duplicate_template&post_id=' . $post->ID ),
			'es_duplicate_template_' . $post->ID
		);
		$actions['duplicate'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $duplicate_url ),
			esc_html__( 'Duplicate', 'elonix' )
		);

		// Export Action
		$export_url        = wp_nonce_url(
			admin_url( 'admin.php?action=es_export_template&post_id=' . $post->ID ),
			'es_export_template_' . $post->ID
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
		if ( isset( $_POST['es_bulk_action_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['es_bulk_action_nonce_field'] ) ), 'es_bulk_action' ) ) {
			$bulk_action = isset( $_POST['es_bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['es_bulk_action'] ) ) : '';
			$layout_ids  = isset( $_POST['layouts'] ) ? array_map( 'inesal', $_POST['layouts'] ) : array();

			// Fallback bottom bulk action
			if ( empty( $bulk_action ) && isset( $_POST['es_bulk_action_bottom'] ) ) {
				$bulk_action = sanitize_text_field( wp_unslash( $_POST['es_bulk_action_bottom'] ) );
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
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&es_status=' . $status_msg . '&count=' . $count ) );
				exit;
			}
		}

		// 2. DUPLICATE ACTION
		if ( 'es_duplicate_template' === $action ) {
			$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
			if ( ! $post_id || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'es_duplicate_template_' . $post_id ) ) {
				return;
			}

			$new_id = $this->duplicate_template( $post_id );
			if ( $new_id ) {
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&es_status=duplicated&count=1' ) );
				exit;
			}
		}

		// 3. EXPORT SINGLE ACTION
		if ( 'es_export_template' === $action ) {
			$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
			if ( ! $post_id || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'es_export_template_' . $post_id ) ) {
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
					'_es_priority'                 => get_post_meta( $post->ID, '_es_priority', true ),
					'_es_target_include_locations' => get_post_meta( $post->ID, '_es_target_include_locations', true ),
					'_es_target_exclude_locations' => get_post_meta( $post->ID, '_es_target_exclude_locations', true ),
					'_es_target_user_roles'        => get_post_meta( $post->ID, '_es_target_user_roles', true ),
					'_elementor_edit_mode'         => get_post_meta( $post->ID, '_elementor_edit_mode', true ),
					'_elementor_template_type'     => get_post_meta( $post->ID, '_elementor_template_type', true ),
					'_elementor_data'              => get_post_meta( $post->ID, '_elementor_data', true ),
				),
			);

			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/json' );
			header( 'Content-Disposition: attachment; filename="es-layout-' . sanitize_title( $post->post_title ) . '.json"' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate' );
			header( 'Pragma: public' );
			echo wp_json_encode( $export_data );
			exit;
		}

		// 4. EXPORT ALL ACTION
		if ( 'es_export_all_templates' === $action ) {
			$posts = get_posts(
				array(
					'post_type'      => array( 'es_header', 'es_footer' ),
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
						'_es_priority'                 => get_post_meta( $post->ID, '_es_priority', true ),
						'_es_target_include_locations' => get_post_meta( $post->ID, '_es_target_include_locations', true ),
						'_es_target_exclude_locations' => get_post_meta( $post->ID, '_es_target_exclude_locations', true ),
						'_es_target_user_roles'        => get_post_meta( $post->ID, '_es_target_user_roles', true ),
						'_elementor_edit_mode'         => get_post_meta( $post->ID, '_elementor_edit_mode', true ),
						'_elementor_template_type'     => get_post_meta( $post->ID, '_elementor_template_type', true ),
						'_elementor_data'              => get_post_meta( $post->ID, '_elementor_data', true ),
					),
				);
			}

			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/json' );
			header( 'Content-Disposition: attachment; filename="es-all-layouts.json"' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate' );
			header( 'Pragma: public' );
			echo wp_json_encode( $export_data );
			exit;
		}

		// 5. CREATE LAYOUT OR IMPORT FORM ACTION
		if ( isset( $_POST['es_create_template_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['es_create_template_nonce_field'] ) ), 'es_create_template' ) ) {

			// Check if importing a file first
			if ( ! empty( $_FILES['es_import_file']['tmp_name'] ) ) {
				$file_path = sanitize_text_field( wp_unslash( $_FILES['es_import_file']['tmp_name'] ) );
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
						wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&es_status=imported&count=' . $imported_count ) );
						exit;
					}
				}
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&es_status=import_failed' ) );
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

			$allowed_types = array( 'es_header', 'es_footer' );

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

					$doc_type = ( 'es_header' === $type ) ? 'header' : 'footer';
					update_post_meta( $new_post_id, '_elementor_template_type', $doc_type );
					update_post_meta( $new_post_id, '_es_priority', $priority );

					if ( ! empty( $condition ) ) {
						update_post_meta(
							$new_post_id,
							'_es_target_include_locations',
							array(
								array(
									'rule'     => $condition,
									'specific' => $specifics,
								),
							)
						);
					} else {
						update_post_meta( $new_post_id, '_es_target_include_locations', array() );
					}
					update_post_meta( $new_post_id, '_es_target_exclude_locations', array() );
					update_post_meta( $new_post_id, '_es_target_user_roles', array() );

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
		if ( isset( $_POST['es_quick_edit_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['es_quick_edit_nonce_field'] ) ), 'es_quick_edit' ) ) {
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

				update_post_meta( $post_id, '_es_priority', $priority );

				if ( ! empty( $condition ) ) {
					update_post_meta(
						$post_id,
						'_es_target_include_locations',
						array(
							array(
								'rule'     => $condition,
								'specific' => $specifics,
							),
						)
					);
				} else {
					update_post_meta( $post_id, '_es_target_include_locations', array() );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&es_status=updated&count=1' ) );
				exit;
			}
		}

		// 7. TRASH DIRECT ACTION
		if ( 'es_trash_layout' === $action ) {
			$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
			if ( $post_id && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'es_trash_layout_' . $post_id ) ) {
				wp_trash_post( $post_id );
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&es_status=deleted&count=1' ) );
				exit;
			}
		}

		// 8. RESTORE DIRECT ACTION
		if ( 'es_restore_layout' === $action ) {
			$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
			if ( $post_id && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'es_restore_layout_' . $post_id ) ) {
				wp_untrash_post( $post_id );
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&es_status=restored&count=1' ) );
				exit;
			}
		}

		// 9. DELETE PERMANENTLY DIRECT ACTION
		if ( 'es_delete_layout_permanently' === $action ) {
			$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
			if ( $post_id && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'es_delete_layout_permanently_' . $post_id ) ) {
				wp_delete_post( $post_id, true );
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-header-footer&es_status=deleted_permanently&count=1' ) );
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
		$header_counts = wp_count_posts( 'es_header' );
		$footer_counts = wp_count_posts( 'es_footer' );

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
				'post_type'      => 'es_header',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required for querying layouts based on condition keys.
				'meta_query'     => array(
					array(
						'key'     => '_es_target_include_locations',
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
				'post_type'      => 'es_footer',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required for querying layouts based on condition keys.
				'meta_query'     => array(
					array(
						'key'     => '_es_target_include_locations',
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
		$status_filter = isset( $_GET['es_status'] ) ? sanitize_text_field( wp_unslash( $_GET['es_status'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type_filter = isset( $_GET['es_type'] ) ? sanitize_text_field( wp_unslash( $_GET['es_type'] ) ) : 'all';

		$query_types = ( 'all' === $type_filter ) ? array( 'es_header', 'es_footer' ) : array( $type_filter );

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
		$notice_status = isset( $_GET['es_status'] ) ? sanitize_text_field( wp_unslash( $_GET['es_status'] ) ) : '';
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
				'post_type'      => array( 'es_header', 'es_footer' ),
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
			)
		);
		?>
		<!-- REDESIGNED FULL WIDTH PREMIUM STYLE SHEETS -->
		<!-- REDESIGNED FULL WIDTH PREMIUM STYLE SHEETS MOVED EXTERNALLY -->

		<div class="es-full-width-wrapper">
			<!-- Render WordPress notices dynamically -->
			<?php
			if ( ! empty( $notice_html ) ) {
				echo $notice_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>

			<!-- Redesigned Top Branding row -->
			<div class="es-top-row" style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; flex-wrap:wrap; gap:16px;">
				<div>
					<h1 style="font-size:26px; font-weight:700; color:var(--es-slate-900); margin:0; font-family:'Inter', sans-serif;"><?php esc_html_e( 'Header & Footer Builder', 'elonix' ); ?></h1>
					<p style="font-size:13.5px; color:var(--es-slate-500); margin:4px 0 0 0;"><?php esc_html_e( 'Build, design, and target custom site layout templates with Elementor.', 'elonix' ); ?></p>
				</div>
				<div style="display:flex; gap:10px; flex-wrap:wrap;">
					<?php if ( $header_active ) : ?>
						<button type="button" class="es-btn es-btn-primary" id="es_create_header_btn">
							<span class="dashicons dashicons-plus" style="font-size:16px; margin-top:2px;"></span>
							<?php esc_html_e( 'Create Header', 'elonix' ); ?>
						</button>
					<?php endif; ?>

					<?php if ( $footer_active ) : ?>
						<button type="button" class="es-btn es-btn-pink" id="es_create_footer_btn">
							<span class="dashicons dashicons-plus" style="font-size:16px; margin-top:2px;"></span>
							<?php esc_html_e( 'Create Footer', 'elonix' ); ?>
						</button>
					<?php endif; ?>

					<button type="button" class="es-btn es-btn-secondary" id="es_import_btn">
						<span class="dashicons dashicons-upload" style="font-size:16px; margin-top:2px;"></span>
						<?php esc_html_e( 'Import Template', 'elonix' ); ?>
					</button>

					<a href="<?php echo esc_url( admin_url( 'admin.php?action=es_export_all_templates' ) ); ?>" class="es-btn es-btn-secondary" title="<?php esc_attr_e( 'Export layout templates to JSON', 'elonix' ); ?>">
						<span class="dashicons dashicons-download" style="font-size:16px; margin-top:2px;"></span>
						<?php esc_html_e( 'Export Templates', 'elonix' ); ?>
					</a>
				</div>
			</div>

			<!-- Dashboard Stats Section -->
			<div class="es-stats-grid">
				<!-- Total Headers -->
				<div class="es-stat-card es-stat-headers">
					<div class="es-stat-label"><?php esc_html_e( 'Total Headers', 'elonix' ); ?></div>
					<div class="es-stat-value"><?php echo (int) $total_headers; ?></div>
					<div class="es-stat-meta">
						<?php
						/* translators: %s: Number of active headers */
						echo esc_html( sprintf( __( '%s Active', 'elonix' ), $header_counts->publish ) );
						?>
					</div>
				</div>

				<!-- Total Footers -->
				<div class="es-stat-card es-stat-footers">
					<div class="es-stat-label"><?php esc_html_e( 'Total Footers', 'elonix' ); ?></div>
					<div class="es-stat-value"><?php echo (int) $total_footers; ?></div>
					<div class="es-stat-meta">
						<?php
						/* translators: %s: Number of active footers */
						echo esc_html( sprintf( __( '%s Active', 'elonix' ), $footer_counts->publish ) );
						?>
					</div>
				</div>

				<!-- Active Templates -->
				<div class="es-stat-card es-stat-active">
					<div class="es-stat-label"><?php esc_html_e( 'Active Templates', 'elonix' ); ?></div>
					<div class="es-stat-value"><?php echo (int) $active_templates; ?></div>
					<div class="es-stat-meta">
						<?php
						/* translators: %s: Number of draft templates */
						echo esc_html( sprintf( __( '%s Draft / Deassigned', 'elonix' ), $total_all - $active_templates ) );
						?>
					</div>
				</div>

				<!-- Global Header -->
				<div class="es-stat-card es-stat-global-header">
					<div class="es-stat-label"><?php esc_html_e( 'Global Header', 'elonix' ); ?></div>
					<div class="es-stat-value" style="font-size:18px; margin-top:14px; font-weight:600; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">
						<?php echo esc_html( $global_header_name ); ?>
					</div>
					<div class="es-stat-meta"><?php esc_html_e( 'Assigned to Entire Website', 'elonix' ); ?></div>
				</div>

				<!-- Global Footer -->
				<div class="es-stat-card es-stat-global-footer">
					<div class="es-stat-label"><?php esc_html_e( 'Global Footer', 'elonix' ); ?></div>
					<div class="es-stat-value" style="font-size:18px; margin-top:14px; font-weight:600; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">
						<?php echo esc_html( $global_footer_name ); ?>
					</div>
					<div class="es-stat-meta"><?php esc_html_e( 'Assigned to Entire Website', 'elonix' ); ?></div>
				</div>
			</div>

			<!-- Bulk Actions form wrapper -->
			<form method="post" id="es_bulk_form">
				<?php wp_nonce_field( 'es_bulk_action', 'es_bulk_action_nonce_field' ); ?>

				<!-- Toolbar for Filters and Search -->
				<div class="es-toolbar-panel">
					<!-- Filter tabs linking dynamically using query parameters -->
					<div class="es-filter-links">
						<!-- All status filter links -->
						<a href="<?php echo esc_url( remove_query_arg( array( 'es_status', 'es_type', 'paged' ) ) ); ?>" class="es-filter-link <?php echo ( 'all' === $status_filter && 'all' === $type_filter ) ? 'active' : ''; ?>">
							<?php esc_html_e( 'All', 'elonix' ); ?>
							<span style="font-weight:400; color:var(--es-slate-400);"> (<?php echo (int) $total_all; ?>)</span>
						</a>

						<!-- Headers filter link -->
						<a href="
						<?php
						echo esc_url(
							add_query_arg(
								array(
									'es_type'   => 'es_header',
									'es_status' => 'all',
								)
							)
						);
						?>
									" class="es-filter-link <?php echo ( 'es_header' === $type_filter ) ? 'active' : ''; ?>">
							<?php esc_html_e( 'Headers', 'elonix' ); ?>
							<span style="font-weight:400; color:var(--es-slate-400);"> (<?php echo (int) $total_headers; ?>)</span>
						</a>

						<!-- Footers filter link -->
						<a href="
						<?php
						echo esc_url(
							add_query_arg(
								array(
									'es_type'   => 'es_footer',
									'es_status' => 'all',
								)
							)
						);
						?>
									" class="es-filter-link <?php echo ( 'es_footer' === $type_filter ) ? 'active' : ''; ?>">
							<?php esc_html_e( 'Footers', 'elonix' ); ?>
							<span style="font-weight:400; color:var(--es-slate-400);"> (<?php echo (int) $total_footers; ?>)</span>
						</a>

						<!-- Active publish templates filter link -->
						<a href="
						<?php
						echo esc_url(
							add_query_arg(
								array(
									'es_status' => 'publish',
									'es_type'   => 'all',
								)
							)
						);
						?>
									" class="es-filter-link <?php echo ( 'publish' === $status_filter ) ? 'active' : ''; ?>">
							<?php esc_html_e( 'Active', 'elonix' ); ?>
							<span style="font-weight:400; color:var(--es-slate-400);"> (<?php echo (int) $active_templates; ?>)</span>
						</a>

						<!-- Draft templates filter link -->
						<a href="
						<?php
						echo esc_url(
							add_query_arg(
								array(
									'es_status' => 'draft',
									'es_type'   => 'all',
								)
							)
						);
						?>
									" class="es-filter-link <?php echo ( 'draft' === $status_filter ) ? 'active' : ''; ?>">
							<?php esc_html_e( 'Draft', 'elonix' ); ?>
							<span style="font-weight:400; color:var(--es-slate-400);"> (<?php echo (int) $draft_templates; ?>)</span>
						</a>

						<!-- Trashed templates filter link (always visible) -->
						<a href="
						<?php
						echo esc_url(
							add_query_arg(
								array(
									'es_status' => 'trash',
									'es_type'   => 'all',
								)
							)
						);
						?>
									" class="es-filter-link <?php echo ( 'trash' === $status_filter ) ? 'active' : ''; ?>" style="color:var(--es-danger);">
							<?php esc_html_e( 'Trash', 'elonix' ); ?>
							<span style="font-weight:400; color:var(--es-danger);"> (<?php echo (int) $trashed_templates; ?>)</span>
						</a>
					</div>

					<!-- Search container -->
					<div class="es-search-bar">
						<span class="dashicons dashicons-search search-icon"></span>
						<input type="text" id="es_live_search" placeholder="<?php esc_attr_e( 'Search templates...', 'elonix' ); ?>" aria-label="<?php esc_attr_e( 'Search template list', 'elonix' ); ?>">
					</div>
				</div>

				<!-- Table content list card -->
				<div class="es-list-table-panel">
					<!-- WP Native tablenav top -->
					<div class="tablenav top">
						<div class="alignleft actions bulkactions">
							<select name="es_bulk_action" id="bulk-action-selector-top" aria-label="<?php esc_attr_e( 'Select bulk action', 'elonix' ); ?>">
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
					<div class="es-table-responsive">
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
							<tbody id="es_table_body">
								<?php if ( empty( $layouts ) ) : ?>
									<tr>
										<td colspan="4">
											<div class="es-empty-box">
												<span class="dashicons dashicons-layout"></span>
												<h3><?php esc_html_e( 'No Header or Footer Templates Found', 'elonix' ); ?></h3>
												<p><?php esc_html_e( 'You have no templates in this state. Get started by creating your custom layouts.', 'elonix' ); ?></p>
												<?php if ( $header_active ) : ?>
													<button type="button" class="es-btn es-btn-primary" onclick="jQuery('#es_create_header_btn').click();" style="margin: 4px;">
														<?php esc_html_e( 'Create Header Template', 'elonix' ); ?>
													</button>
												<?php endif; ?>
												<?php if ( $footer_active ) : ?>
													<button type="button" class="es-btn es-btn-pink" onclick="jQuery('#es_create_footer_btn').click();" style="margin: 4px;">
														<?php esc_html_e( 'Create Footer Template', 'elonix' ); ?>
													</button>
												<?php endif; ?>
											</div>
										</td>
									</tr>
								<?php else : ?>
									<?php
									foreach ( $layouts as $tpl ) :
										$type_label = ( 'es_header' === $tpl->post_type ) ? 'Header' : 'Footer';
										$type_badge = ( 'es_header' === $tpl->post_type ) ? 'es-badge-header' : 'es-badge-footer';
										$type_icon  = ( 'es_header' === $tpl->post_type ) ? 'dashicons-editor-kitchensink' : 'dashicons-editor-insertmore';

										$priority = get_post_meta( $tpl->ID, '_es_priority', true );
										$status   = get_post_status( $tpl->ID );

										$include_locations  = get_post_meta( $tpl->ID, '_es_target_include_locations', true );
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

										$shortcode = ( 'es_header' === $tpl->post_type ) ? '[es_header id="' . $tpl->ID . '"]' : '[es_footer id="' . $tpl->ID . '"]';
										$php_code  = ( 'es_header' === $tpl->post_type ) ? '<?php elonix_render_header(' . $tpl->ID . '); ?>' : '<?php elonix_render_footer(' . $tpl->ID . '); ?>';

										$modified_time = get_the_modified_date( 'U', $tpl->ID );
										$time_diff     = human_time_diff( $modified_time, current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'elonix' );

										$status_label = 'DRAFT';
										if ( 'publish' === $status ) {
											$status_label = 'ACTIVE';
										} elseif ( 'trash' === $status ) {
											$status_label = 'TRASH';
										}
										?>
										<tr class="es-row" data-id="<?php echo (int) $tpl->ID; ?>" data-type="<?php echo esc_attr( $tpl->post_type ); ?>" data-title="<?php echo esc_attr( strtolower( $tpl->post_title ) ); ?>">
											<!-- Selection column checkbox -->
											<td class="check-column" style="padding-left: 20px; vertical-align: middle;">
												<input type="checkbox" name="layouts[]" value="<?php echo (int) $tpl->ID; ?>" class="es-layout-row-checkbox" style="margin:0; cursor:pointer;" aria-label="<?php /* translators: %s: Template title */ printf( esc_attr__( 'Select template: %s', 'elonix' ), esc_attr( $tpl->post_title ) ); ?>">
											</td>

											<!-- Template Details column -->
											<td data-colname="<?php esc_attr_e( 'Template Details', 'elonix' ); ?>">
												<div class="es-template-title-group">
													<div class="es-template-name-row" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
														<span class="row-title" style="font-weight:700; color:var(--es-slate-900); font-size: 16px;"><?php echo esc_html( $tpl->post_title ); ?></span>
														<span class="es-badge <?php echo esc_attr( $type_badge ); ?>" style="font-size: 9.5px; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
															<span class="dashicons <?php echo esc_attr( $type_icon ); ?>" style="font-size: 11px; width: 11px; height: 11px; line-height: 1; margin: 0;"></span>
															<?php echo esc_html( $type_label ); ?>
														</span>
														<span class="es-badge es-badge-<?php echo esc_attr( $status ); ?>" style="font-size: 9.5px; padding: 2px 8px; border-radius: 12px;">
															<?php echo esc_html( $status_label ); ?>
														</span>
													</div>
													<!-- Display Conditions under Name -->
													<div style="margin-top: 10px; display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: var(--es-slate-700);">
														<span style="font-size: 12px; color: var(--es-slate-400); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-right: 4px;">Display Condition:</span>
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
												<div class="es-metadata-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px 16px; font-size: 11.5px; color: var(--es-slate-600); line-height: 1.35;">
													<!-- Column 1: Template ID & Location -->
													<div>
														<div style="margin-bottom: 6px;">
															<span style="color: var(--es-slate-400); font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.3px; display: block;"><?php esc_html_e( 'Template ID', 'elonix' ); ?></span>
															<span style="font-weight: 600; color: var(--es-slate-700); display: block; margin-top: 2px;">#<?php echo (int) $tpl->ID; ?></span>
														</div>
														<div>
															<span style="color: var(--es-slate-400); font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.3px; display: block;"><?php esc_html_e( 'Location', 'elonix' ); ?></span>
															<span style="font-weight: 500; color: var(--es-slate-700); display: block; margin-top: 2px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 130px;" title="<?php echo esc_attr( $locs_string ); ?>">
																<?php echo esc_html( $locs_string ); ?>
															</span>
														</div>
													</div>

													<!-- Column 2: Shortcode & Priority -->
													<div>
														<div style="margin-bottom: 6px;">
															<span style="color: var(--es-slate-400); font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.3px; display: block;"><?php esc_html_e( 'Shortcode', 'elonix' ); ?></span>
															<div style="display: flex; align-items: center; gap: 4px; margin-top: 2px;">
																<code style="font-family: monospace; background: var(--es-slate-50); border: 1px solid var(--es-slate-200); padding: 1px 6px; border-radius: 4px; font-size: 10.5px; font-weight: 600; color: var(--es-slate-700); white-space: nowrap;"><?php echo esc_html( $shortcode ); ?></code>
																<button type="button" class="es-copy-btn" data-copy-text="<?php echo esc_attr( $shortcode ); ?>" data-success-msg="<?php echo esc_attr__( 'Shortcode copied', 'elonix' ); ?>" style="border: 1px solid var(--es-slate-250); background: #ffffff; cursor: pointer; border-radius: 4px; padding: 2px 5px; font-size: 9.5px; font-weight: 600; color: var(--es-slate-700); display: inline-flex; align-items: center; gap: 2px; height: 18px; transition: all 0.15s ease;" title="<?php esc_attr_e( 'Copy Shortcode', 'elonix' ); ?>">
																	<span class="dashicons dashicons-admin-page" style="font-size: 10px; width: 10px; height: 10px; margin: 0; line-height: 1;"></span>
																	<?php esc_html_e( 'Copy', 'elonix' ); ?>
																</button>
															</div>
														</div>
														<div>
															<span style="color: var(--es-slate-400); font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.3px; display: block;"><?php esc_html_e( 'Priority', 'elonix' ); ?></span>
															<span style="font-weight: 500; color: var(--es-slate-700); display: block; margin-top: 2px;">
																<?php if ( 0 === $priority_int ) : ?>
																	<span style="color: var(--es-slate-400);">0</span>
																<?php else : ?>
																	<span class="es-priority-badge" style="background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; padding: 1px 5px; border-radius: 4px; font-size: 9.5px; font-weight: 700; display: inline-block; line-height: 1;"><?php echo (int) $priority_int; ?></span>
																<?php endif; ?>
															</span>
														</div>
													</div>

													<!-- Column 3: Created & Modified -->
													<div>
														<div style="margin-bottom: 6px;">
															<span style="color: var(--es-slate-400); font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.3px; display: block;"><?php esc_html_e( 'Created', 'elonix' ); ?></span>
															<span style="font-weight: 500; color: var(--es-slate-700); display: block; margin-top: 2px; white-space: nowrap;"><?php echo get_the_date( 'M j, Y', $tpl->ID ); ?></span>
														</div>
														<div>
															<span style="color: var(--es-slate-400); font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.3px; display: block;"><?php esc_html_e( 'Modified', 'elonix' ); ?></span>
															<span style="font-weight: 500; color: var(--es-slate-700); display: block; margin-top: 2px; white-space: nowrap;"><?php echo esc_html( $time_diff ); ?></span>
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
							<select name="es_bulk_action_bottom" id="bulk-action-selector-bottom" aria-label="<?php esc_attr_e( 'Select bulk action', 'elonix' ); ?>">
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
			<div id="es_sticky_bulk_bar" class="es-sticky-bulk-bar">
				<div class="es-sticky-bulk-inner">
					<span class="es-selected-count">0 templates selected</span>
					<div class="es-sticky-bulk-actions">
						<select name="es_sticky_bulk_action" id="es_sticky_bulk_action" class="es-select">
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
						<button type="button" id="es_sticky_bulk_apply" class="es-btn es-btn-primary"><?php esc_html_e( 'Apply', 'elonix' ); ?></button>
						<button type="button" id="es_sticky_bulk_cancel" class="es-btn es-btn-secondary"><?php esc_html_e( 'Cancel', 'elonix' ); ?></button>
					</div>
				</div>
			</div>

			<!-- MODAL: CREATE LAYOUT (TABBED INCLUDES FORM CREATE AND FILE IMPORT) -->
			<div class="es-modal-overlay" id="es_create_modal" role="dialog" aria-modal="true" aria-labelledby="modal_create_title">
				<div class="es-modal-card">
					<div class="es-modal-header">
						<h3 class="es-modal-title" id="modal_create_title"><?php esc_html_e( 'Add Layout Template', 'elonix' ); ?></h3>
						<button type="button" class="es-modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'elonix' ); ?>" id="es_create_modal_close">&times;</button>
					</div>

					<!-- Modal Tabs -->
					<div class="es-modal-tabs">
						<div class="es-modal-tab-link active" data-tab="create_new"><?php esc_html_e( 'Create New Template', 'elonix' ); ?></div>
						<div class="es-modal-tab-link" data-tab="import_file"><?php esc_html_e( 'Import JSON Template', 'elonix' ); ?></div>
					</div>

					<!-- Forms container -->
					<div class="es-modal-tabs-body">
						<!-- TAB 1: CREATE NEW TEMPLATE -->
						<div class="es-modal-tab-content" id="tab_create_new">
							<form method="post" enctype="multipart/form-data">
								<?php wp_nonce_field( 'es_create_template', 'es_create_template_nonce_field' ); ?>

								<div class="es-field">
									<label for="template_name"><?php esc_html_e( 'Template Name', 'elonix' ); ?></label>
									<input type="text" id="template_name" name="template_name" class="es-input" placeholder="<?php esc_attr_e( 'e.g. Navigation Header Layout', 'elonix' ); ?>" required>
								</div>

								<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
									<div class="es-field">
										<label for="template_type"><?php esc_html_e( 'Template Type', 'elonix' ); ?></label>
										<select id="template_type" name="template_type" class="es-select">
											<?php
											if ( $header_active ) :
												?>
												<option value="es_header"><?php esc_html_e( 'Header', 'elonix' ); ?></option><?php endif; ?>
											<?php
											if ( $footer_active ) :
												?>
												<option value="es_footer"><?php esc_html_e( 'Footer', 'elonix' ); ?></option><?php endif; ?>
										</select>
									</div>

									<div class="es-field">
										<label for="priority"><?php esc_html_e( 'Priority', 'elonix' ); ?></label>
										<input type="number" id="priority" name="priority" value="0" min="0" step="1" class="es-input">
									</div>
								</div>

								<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
									<div class="es-field">
										<label for="display_condition"><?php esc_html_e( 'Display Condition', 'elonix' ); ?></label>
										<select id="display_condition" name="display_condition" class="es-select es-modal-condition-select">
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

									<div class="es-field">
										<label for="status"><?php esc_html_e( 'Status', 'elonix' ); ?></label>
										<select id="status" name="status" class="es-select">
											<option value="publish"><?php esc_html_e( 'Published', 'elonix' ); ?></option>
											<option value="draft" selected><?php esc_html_e( 'Draft', 'elonix' ); ?></option>
										</select>
									</div>
								</div>

								<!-- Display conditions Select2 targets search container -->
								<div class="es-specific-search-wrapper" style="display:none; margin-bottom: 16px;">
									<label for="display_condition_specific"><?php esc_html_e( 'Select Specific Target', 'elonix' ); ?></label>
									<select id="display_condition_specific" name="display_condition_specific[]" class="es-select2-ajax-search" multiple="multiple" style="width: 100%;"></select>
								</div>

								<div class="es-field">
									<label for="clone_layout"><?php esc_html_e( 'Duplicate From Existing Template (Optional)', 'elonix' ); ?></label>
									<select id="clone_layout" name="clone_layout" class="es-select">
										<option value="0"><?php esc_html_e( '-- Select Template --', 'elonix' ); ?></option>
										<?php foreach ( $existing_layouts as $ex_l ) : ?>
											<option value="<?php echo (int) $ex_l->ID; ?>"><?php echo esc_html( $ex_l->post_title ); ?></option>
										<?php endforeach; ?>
									</select>
								</div>

								<div class="es-field" style="display:flex; align-items:center; gap:8px; margin-top: 10px;">
									<input type="checkbox" id="open_elementor" name="open_elementor" value="1" checked style="width:16px; height:16px; margin:0; cursor:pointer;">
									<label for="open_elementor" style="margin:0; cursor:pointer; font-weight:500; font-size: 13px;"><?php esc_html_e( 'Open in Elementor editor immediately', 'elonix' ); ?></label>
								</div>

								<div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px; border-top:1px solid var(--es-slate-200); padding-top:16px;">
									<button type="button" class="es-btn es-btn-secondary es-modal-cancel"><?php esc_html_e( 'Cancel', 'elonix' ); ?></button>
									<button type="submit" class="es-btn es-btn-primary"><?php esc_html_e( 'Create Template', 'elonix' ); ?></button>
								</div>
							</form>
						</div>

						<!-- TAB 2: IMPORT TEMPLATE FILE -->
						<div class="es-modal-tab-content" id="tab_import_file" style="display:none;">
							<form method="post" enctype="multipart/form-data">
								<?php wp_nonce_field( 'es_create_template', 'es_create_template_nonce_field' ); ?>

								<div class="es-field">
									<label for="es_import_file"><?php esc_html_e( 'Upload JSON File', 'elonix' ); ?></label>
									<input type="file" id="es_import_file" name="es_import_file" accept=".json" required style="width:100%; border:1px dashed var(--es-slate-300); border-radius:8px; padding:20px; font-size:13.5px; font-family:'Inter',sans-serif; background:var(--es-slate-50); cursor:pointer;">
								</div>

								<div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px; border-top:1px solid var(--es-slate-200); padding-top:16px;">
									<button type="button" class="es-btn es-btn-secondary es-modal-cancel"><?php esc_html_e( 'Cancel', 'elonix' ); ?></button>
									<button type="submit" class="es-btn es-btn-primary"><?php esc_html_e( 'Import JSON File', 'elonix' ); ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<!-- MODAL: QUICK EDIT PROPERTIES -->
			<div class="es-modal-overlay" id="es_quick_edit_modal" role="dialog" aria-modal="true" aria-labelledby="modal_quick_title">
				<div class="es-modal-card">
					<div class="es-modal-header">
						<h3 class="es-modal-title" id="modal_quick_title"><?php esc_html_e( 'Quick Edit Layout Properties', 'elonix' ); ?></h3>
						<button type="button" class="es-modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'elonix' ); ?>" id="es_quick_edit_close">&times;</button>
					</div>
					<form method="post">
						<?php wp_nonce_field( 'es_quick_edit', 'es_quick_edit_nonce_field' ); ?>
						<input type="hidden" id="quick_edit_id" name="layout_id" value="0">

						<div class="es-field">
							<label for="quick_edit_name"><?php esc_html_e( 'Template Name', 'elonix' ); ?></label>
							<input type="text" id="quick_edit_name" name="template_name" class="es-input" required>
						</div>

						<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
							<div class="es-field">
								<label for="quick_edit_type"><?php esc_html_e( 'Template Type', 'elonix' ); ?></label>
								<select id="quick_edit_type" name="template_type" class="es-select">
									<option value="es_header"><?php esc_html_e( 'Header', 'elonix' ); ?></option>
									<option value="es_footer"><?php esc_html_e( 'Footer', 'elonix' ); ?></option>
								</select>
							</div>

							<div class="es-field">
								<label for="quick_edit_priority"><?php esc_html_e( 'Priority', 'elonix' ); ?></label>
								<input type="number" id="quick_edit_priority" name="priority" value="0" min="0" step="1" class="es-input">
							</div>
						</div>

						<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
							<div class="es-field">
								<label for="quick_edit_condition"><?php esc_html_e( 'Display Condition', 'elonix' ); ?></label>
								<select id="quick_edit_condition" name="display_condition" class="es-select es-modal-condition-select">
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

							<div class="es-field">
								<label for="quick_edit_status"><?php esc_html_e( 'Status', 'elonix' ); ?></label>
								<select id="quick_edit_status" name="status" class="es-select">
									<option value="publish"><?php esc_html_e( 'Published', 'elonix' ); ?></option>
									<option value="draft"><?php esc_html_e( 'Draft', 'elonix' ); ?></option>
								</select>
							</div>
						</div>

						<!-- Display conditions Select2 targets search container -->
						<div class="es-specific-search-wrapper" style="display:none; margin-bottom: 16px;">
							<label for="quick_edit_display_condition_specific"><?php esc_html_e( 'Select Specific Target', 'elonix' ); ?></label>
							<select id="quick_edit_display_condition_specific" name="display_condition_specific[]" class="es-select2-ajax-search" multiple="multiple" style="width: 100%;"></select>
						</div>

						<div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px; border-top:1px solid var(--es-slate-200); padding-top:16px;">
							<button type="button" class="es-btn es-btn-secondary es-modal-cancel"><?php esc_html_e( 'Cancel', 'elonix' ); ?></button>
							<button type="submit" class="es-btn es-btn-primary"><?php esc_html_e( 'Update Layout', 'elonix' ); ?></button>
						</div>
					</form>
				</div>
			</div>

			<!-- MODAL: GET SHORTCODE -->
			<div class="es-modal-overlay" id="es_shortcode_modal" role="dialog" aria-modal="true" aria-labelledby="modal_shortcode_title">
				<div class="es-modal-card">
					<div class="es-modal-header">
						<h3 class="es-modal-title" id="modal_shortcode_title"><?php esc_html_e( 'Template Shortcode & PHP Code', 'elonix' ); ?></h3>
						<button type="button" class="es-modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'elonix' ); ?>" id="es_shortcode_close">&times;</button>
					</div>
					<div>
						<!-- Shortcode Info -->
						<div class="es-field" style="margin-bottom:20px;">
							<label style="font-weight:600; display:block; margin-bottom:8px;"><?php esc_html_e( 'WordPress Shortcode', 'elonix' ); ?></label>
							<p style="font-size:12.5px; color:var(--es-slate-500); margin:0 0 8px 0;"><?php esc_html_e( 'Copy and paste this shortcode into your post, page, widget, block, or Elementor shortcode widget.', 'elonix' ); ?></p>
							<div style="display:flex; gap:10px; align-items:center;">
								<input type="text" id="shortcode_modal_text" class="es-input" readonly style="font-family:monospace; background:var(--es-slate-50); flex:1;">
								<button type="button" class="es-btn es-btn-primary es-copy-btn" id="copy_shortcode_modal_btn" data-copy-text="" data-success-msg="<?php echo esc_attr__( 'Shortcode copied', 'elonix' ); ?>" style="white-space:nowrap;">
									<span class="dashicons dashicons-admin-page" style="font-size:16px; width:16px; height:16px; margin:0;"></span>
									<?php esc_html_e( 'Copy Shortcode', 'elonix' ); ?>
								</button>
							</div>
						</div>

						<!-- PHP Function Info -->
						<div class="es-field" style="margin-bottom:20px; border-top: 1px solid var(--es-slate-200); padding-top: 20px;">
							<label style="font-weight:600; display:block; margin-bottom:8px;"><?php esc_html_e( 'PHP Function Call', 'elonix' ); ?></label>
							<p style="font-size:12.5px; color:var(--es-slate-500); margin:0 0 8px 0;"><?php esc_html_e( 'Copy and paste this PHP code snippet into your active theme templates (like header.php or footer.php) to render the layout dynamically.', 'elonix' ); ?></p>
							<div style="display:flex; gap:10px; align-items:center;">
								<input type="text" id="php_modal_text" class="es-input" readonly style="font-family:monospace; background:var(--es-slate-50); flex:1;">
								<button type="button" class="es-btn es-btn-secondary es-copy-btn" id="copy_php_modal_btn" data-copy-text="" data-success-msg="<?php echo esc_attr__( 'PHP code copied', 'elonix' ); ?>" style="white-space:nowrap;">
									<span class="dashicons dashicons-admin-page" style="font-size:16px; width:16px; height:16px; margin:0;"></span>
									<?php esc_html_e( 'Copy PHP Code', 'elonix' ); ?>
								</button>
							</div>
						</div>

						<div style="display:flex; justify-content:flex-end; margin-top:20px; border-top:1px solid var(--es-slate-200); padding-top:16px;">
							<button type="button" class="es-btn es-btn-secondary es-modal-cancel" id="es_shortcode_cancel"><?php esc_html_e( 'Close', 'elonix' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- MODALS & LIVE SEARCH SCRIPTS -->
		<!-- SCRIPT MOVED TO ASSETS -->
		<?php
	}
}
