<?php
/**
 * Elonix – Toolkit for Elementor Modules Page Renderer
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Modules_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		// AJAX handlers for modules toggling
		add_action( 'wp_ajax_elonix_toggle_module', array( $this, 'ajax_toggle_module' ) );
		add_action( 'wp_ajax_elonix_bulk_modules', array( $this, 'ajax_bulk_modules' ) );
	}

	/**
	 * Enqueue styles for Modules page.
	 *
	 * @param string $hook Screen hook.
	 */
	public function enqueue_styles( $hook ) {
		if ( 'elonix_page_elonix-modules' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'elonix-modules-page-css',
			ELONIX_ACC_URL . 'assets/admin/css/modules-page.css',
			array(),
			ELONIX_VERSION
		);
	}

	/**
	 * Handle fallback form submissions.
	 */
	public function handle_form_submission() {
		if ( ! isset( $_POST['elonix_modules_save_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['elonix_modules_save_nonce'] ) ), 'elonix_modules_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle bulk actions
		if ( isset( $_POST['bulk_action'] ) ) {
			$bulk_action_val = sanitize_text_field( wp_unslash( $_POST['bulk_action'] ) );
			$modules         = Elonix_Toolkit_Module_Manager::get_registered_modules();
			$new_statuses    = array();

			if ( 'enable_all' === $bulk_action_val ) {
				foreach ( array_keys( $modules ) as $slug ) {
					$new_statuses[ $slug ] = true;
				}
				Elonix_Toolkit_Module_Manager::save_statuses( $new_statuses );
				add_settings_error( 'elonix_modules', 'bulk_enabled', esc_html__( 'All modules enabled successfully.', 'elonix' ), 'success' );
			} elseif ( 'disable_all' === $bulk_action_val ) {
				foreach ( array_keys( $modules ) as $slug ) {
					$new_statuses[ $slug ] = false;
				}
				Elonix_Toolkit_Module_Manager::save_statuses( $new_statuses );
				add_settings_error( 'elonix_modules', 'bulk_disabled', esc_html__( 'All modules disabled successfully.', 'elonix' ), 'success' );
			}
			return;
		}

		// Handle standard save
		$submitted_modules = isset( $_POST['modules'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['modules'] ) ) : array();
		$modules           = Elonix_Toolkit_Module_Manager::get_registered_modules();
		$new_statuses      = array();

		foreach ( array_keys( $modules ) as $slug ) {
			$new_statuses[ $slug ] = isset( $submitted_modules[ $slug ] ) && '1' === $submitted_modules[ $slug ];
		}

		Elonix_Toolkit_Module_Manager::save_statuses( $new_statuses );
		add_settings_error( 'elonix_modules', 'settings_saved', esc_html__( 'Module settings saved successfully.', 'elonix' ), 'success' );
	}

	/**
	 * AJAX handler to toggle a single module status.
	 */
	public function ajax_toggle_module() {
		check_ajax_referer( 'elonix_modules_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'elonix' ) ), 403 );
		}

		$slug   = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		$status = isset( $_POST['status'] ) && '1' === $_POST['status'];

		if ( empty( $slug ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid module slug.', 'elonix' ) ) );
		}

		// Verify module is registered
		$modules = Elonix_Toolkit_Module_Manager::get_registered_modules();
		if ( ! array_key_exists( $slug, $modules ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Module not found in registry.', 'elonix' ) ) );
		}

		Elonix_Toolkit_Module_Manager::save_module_status( $slug, $status );

		$message = $status ? esc_html__( 'Module enabled successfully.', 'elonix' ) : esc_html__( 'Module disabled successfully.', 'elonix' );
		wp_send_json_success( array( 'message' => $message ) );
	}

	/**
	 * AJAX handler for bulk module updates.
	 */
	public function ajax_bulk_modules() {
		check_ajax_referer( 'elonix_modules_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'elonix' ) ), 403 );
		}

		$bulk_action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_action'] ) ) : '';

		if ( ! in_array( $bulk_action, array( 'enable_all', 'disable_all' ), true ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid bulk action.', 'elonix' ) ) );
		}

		$modules      = Elonix_Toolkit_Module_Manager::get_registered_modules();
		$new_statuses = array();
		$status       = ( 'enable_all' === $bulk_action );

		foreach ( array_keys( $modules ) as $slug ) {
			$new_statuses[ $slug ] = $status;
		}

		Elonix_Toolkit_Module_Manager::save_statuses( $new_statuses );

		$message = $status ? esc_html__( 'All modules enabled successfully.', 'elonix' ) : esc_html__( 'All modules disabled successfully.', 'elonix' );
		wp_send_json_success( array( 'message' => $message ) );
	}

	/**
	 * Render Modules page content.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$modules        = Elonix_Toolkit_Module_Manager::get_registered_modules();
		$enabled_count  = count( Elonix_Toolkit_Module_Manager::get_enabled_modules() );
		$disabled_count = count( $modules ) - $enabled_count;

		// Map modules to modern dashicons and dependencies
		$module_meta = array(
			'header_builder'       => array(
				'icon'     => 'dashicons-editor-insertmore',
				'requires' => array(),
			),
			'footer_builder'       => array(
				'icon'     => 'dashicons-editor-insertmore',
				'requires' => array(),
			),
			'popup_builder'        => array(
				'icon'     => 'dashicons-external',
				'requires' => array(),
			),
			'dynamic_tags'         => array(
				'icon'     => 'dashicons-database',
				'requires' => array(),
			),
			'template_library'     => array(
				'icon'     => 'dashicons-format-gallery',
				'requires' => array(),
			),

			'custom_post_types'    => array(
				'icon'     => 'dashicons-admin-post',
				'requires' => array(),
			),
			'custom_icons'         => array(
				'icon'     => 'dashicons-admin-appearance',
				'requires' => array(),
			),

			'search_builder'       => array(
				'icon'     => 'dashicons-search',
				'requires' => array(),
			),
			'screen_loader'        => array(
				'icon'     => 'dashicons-update-alt', // More relevant for a loader/spinner
				'requires' => array(),
			),
			'archive_builder'      => array(
				'icon'     => 'dashicons-portfolio',
				'requires' => array(),
			),
			'single_builder'       => array(
				'icon'     => 'dashicons-media-document',
				'requires' => array(),
			),
			'advanced_404_builder' => array(
				'icon'     => 'dashicons-warning',
				'requires' => array(),
			),
		);

		// Whitelist of strictly production-ready modules
		$implemented_modules = array(
			'header_builder',
			'footer_builder',
			'popup_builder',
			'archive_builder',
			'single_builder',
			'dynamic_tags',
			'search_builder',
			'screen_loader',
			'advanced_404_builder',
			'template_library',
			'custom_post_types',
			'custom_icons',
		);
		?>
		<div class="wrap elonix-admin-page elonix-modules-wrap">

			<!-- Compact Professional Header -->
			<div class="tv-admin-header header-blue">
				<div class="tv-header-branding">
					<div class="tv-header-logo">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" fill="none">
							<rect width="50" height="50" rx="12" fill="url(#tv-head-grad-mod)"/>
							<path d="M25 14L14 22L25 30L36 22L25 14Z" fill="white"/>
							<path d="M14 29L25 36L36 29" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
							<defs>
								<linearGradient id="tv-head-grad-mod" x1="0" y1="0" x2="50" y2="50" gradientUnits="userSpaceOnUse">
									<stop stop-color="#3b82f6"/>
									<stop offset="1" stop-color="#1d4ed8"/>
								</linearGradient>
							</defs>
						</svg>
					</div>
					<div class="tv-header-info">
						<div class="tv-header-title-row">
							<h2><?php esc_html_e( 'Modules Manager', 'elonix' ); ?></h2>
							<span class="tv-header-badge badge-blue">ESKIT v<?php echo esc_html( ELONIX_VERSION ); ?></span>
						</div>
						<p class="tv-header-subtitle"><?php esc_html_e( 'Enable or disable specific features and builders. Disabling unused modules optimizes system execution speed.', 'elonix' ); ?></p>
					</div>
				</div>

				<!-- Header Stats Pill Box -->
				<div class="tv-header-stats">
					<div class="tv-stat-pill all-stat">
						<span class="stat-num"><?php echo count( $modules ); ?></span>
						<span class="stat-lbl"><?php esc_html_e( 'Total', 'elonix' ); ?></span>
					</div>
					<div class="tv-stat-pill enabled-stat stat-blue">
						<span class="stat-num count-enabled"><?php echo esc_html( $enabled_count ); ?></span>
						<span class="stat-lbl"><?php esc_html_e( 'Active', 'elonix' ); ?></span>
					</div>
					<div class="tv-stat-pill disabled-stat">
						<span class="stat-num count-disabled"><?php echo esc_html( $disabled_count ); ?></span>
						<span class="stat-lbl"><?php esc_html_e( 'Inactive', 'elonix' ); ?></span>
					</div>
				</div>
			</div>

			<!-- Toolbar Row -->
			<div class="tv-admin-toolbar">
				<div class="tv-toolbar-left">
					<div class="tv-toolbar-tabs">
						<button type="button" class="tv-filter-tab active" data-filter="all">
							<?php esc_html_e( 'All', 'elonix' ); ?>
						</button>
						<button type="button" class="tv-filter-tab" data-filter="enabled">
							<?php esc_html_e( 'Active', 'elonix' ); ?>
						</button>
						<button type="button" class="tv-filter-tab" data-filter="disabled">
							<?php esc_html_e( 'Inactive', 'elonix' ); ?>
						</button>
					</div>
				</div>

				<div class="tv-toolbar-right">
					<div class="tv-toolbar-search">
						<span class="dashicons dashicons-search search-icon"></span>
						<input type="text" id="tv-search-input" placeholder="<?php esc_attr_e( 'Search modules...', 'elonix' ); ?>">
					</div>

					<div class="tv-toolbar-actions">
						<button type="button" class="tv-bulk-btn button tv-btn-enable btn-blue" data-type="module" data-action="enable_all">
							<span class="dashicons dashicons-yes"></span>
							<span class="btn-text"><?php esc_html_e( 'Enable All', 'elonix' ); ?></span>
							<span class="tv-spinner"></span>
						</button>
						<button type="button" class="tv-bulk-btn button tv-btn-disable" data-type="module" data-action="disable_all">
							<span class="dashicons dashicons-no"></span>
							<span class="btn-text"><?php esc_html_e( 'Disable All', 'elonix' ); ?></span>
							<span class="tv-spinner"></span>
						</button>
					</div>
				</div>
			</div>

			<!-- Output settings messages -->
			<?php settings_errors( 'elonix_modules' ); ?>

			<!-- Grid System -->
			<div class="tv-cards-container">
				<div class="tv-cards-grid">
					<?php if ( ! empty( $modules ) ) : ?>
						<?php
						foreach ( $modules as $slug => $data ) :
							$enabled        = Elonix_Toolkit_Module_Manager::is_module_enabled( $slug );
							$meta           = isset( $module_meta[ $slug ] ) ? $module_meta[ $slug ] : array(
								'icon'     => 'dashicons-block-default',
								'requires' => array(),
							);
							$icon_class     = $meta['icon'];
							$dependencies   = $meta['requires'];
							$is_implemented = in_array( $slug, $implemented_modules, true );

							$card_classes = '';
							if ( ! $enabled ) {
								$card_classes .= ' is-disabled';
							}
							if ( ! $is_implemented ) {
								$card_classes .= ' is-coming-soon';
							}
							?>
							<div class="tv-card-item <?php echo esc_attr( trim( $card_classes ) ); ?>" data-slug="<?php echo esc_attr( $slug ); ?>" data-keywords="">
								<?php if ( ! $is_implemented ) : ?>
									<div class="tv-card-ribbon" aria-hidden="true">
										<span><?php esc_html_e( 'COMING SOON', 'elonix' ); ?></span>
									</div>
								<?php endif; ?>

								<div class="tv-card-header">
									<div class="tv-card-icon-box card-icon-blue">
										<span class="dashicons <?php echo esc_attr( $icon_class ); ?> tv-module-icon"></span>
									</div>
									<div class="tv-card-badges">
										<span class="tv-badge-kit badge-blue"><?php esc_html_e( 'MODULE', 'elonix' ); ?></span>
									</div>
								</div>

								<div class="tv-card-body">
									<h3 class="tv-card-title"><?php echo esc_html( $data['title'] ); ?></h3>
									<p class="tv-card-desc"><?php echo esc_html( $data['description'] ); ?></p>

									<?php if ( ! empty( $dependencies ) ) : ?>
										<div class="tv-card-dependencies">
											<?php foreach ( $dependencies as $dep ) : ?>
												<?php if ( $dep['active'] ) : ?>
													<span class="dependency-badge badge-active-dep">
														<span class="dashicons dashicons-yes-alt"></span>
														<?php echo esc_html( $dep['name'] ); ?>
													</span>
												<?php else : ?>
													<span class="dependency-badge badge-inactive-dep">
														<span class="dashicons dashicons-warning"></span>
														<?php
														/* translators: %s: Plugin name */
														printf( esc_html__( 'Requires: %s (Inactive)', 'elonix' ), esc_html( $dep['name'] ) );
														?>
													</span>
												<?php endif; ?>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>

								<div class="tv-card-footer">
									<div class="tv-status-badge-container">
										<?php if ( ! $is_implemented ) : ?>
											<span class="tv-status-badge badge-coming-soon"><?php esc_html_e( 'COMING SOON', 'elonix' ); ?></span>
										<?php elseif ( $enabled ) : ?>
											<span class="tv-status-badge badge-active"><?php esc_html_e( 'Active', 'elonix' ); ?></span>
										<?php else : ?>
											<span class="tv-status-badge badge-inactive"><?php esc_html_e( 'Inactive', 'elonix' ); ?></span>
										<?php endif; ?>
									</div>
									<div class="tv-action-toggle">
										<label class="elonix-switch" aria-label="<?php esc_attr_e( 'Toggle Module Activation State', 'elonix' ); ?>" <?php echo ( ! $is_implemented ) ? 'title="' . esc_attr__( 'Coming Soon: This module will be available in a future Elonix – Toolkit for Elementor update.', 'elonix' ) . '"' : ''; ?>>
											<input type="checkbox" class="tv-toggle-input" data-type="module" data-slug="<?php echo esc_attr( $slug ); ?>" value="1" <?php checked( $enabled ); ?> <?php disabled( ! $is_implemented ); ?> <?php echo ( ! $is_implemented ) ? 'aria-disabled="true"' : ''; ?>>
											<span class="slider round"></span>
										</label>
									</div>
								</div>
								<div class="tv-card-loading-overlay">
									<span class="tv-card-spinner"></span>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="tv-empty-state">
							<span class="dashicons dashicons-category"></span>
							<h3><?php esc_html_e( 'No modules found.', 'elonix' ); ?></h3>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
