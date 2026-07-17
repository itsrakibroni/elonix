<?php
/**
 * Elonix – Toolkit for Elementor Widgets Page Renderer
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Widgets_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		// AJAX handlers for widgets toggling
		add_action( 'wp_ajax_elonix_toggle_widget', array( $this, 'ajax_toggle_widget' ) );
		add_action( 'wp_ajax_elonix_bulk_widgets', array( $this, 'ajax_bulk_widgets' ) );
	}

	/**
	 * Enqueue styles for Widgets page.
	 *
	 * @param string $hook Screen hook.
	 */
	public function enqueue_styles( $hook ) {
		if ( 'elonix_page_elonix-widgets' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'elonix-widgets-page-css',
			ELONIX_ACC_URL . 'assets/admin/css/widgets-page.css',
			array(),
			ELONIX_VERSION
		);
	}

	/**
	 * Handle fallback form submissions.
	 */
	public function handle_form_submission() {
		if ( ! isset( $_POST['elonix_widgets_save_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['elonix_widgets_save_nonce'] ) ), 'elonix_widgets_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle bulk actions
		if ( isset( $_POST['bulk_action'] ) ) {
			$bulk_action_val = sanitize_text_field( wp_unslash( $_POST['bulk_action'] ) );
			$widgets         = Elonix_Toolkit_Widget_Manager::get_registered_widgets();
			$new_statuses    = array();

			if ( 'enable_all' === $bulk_action_val ) {
				foreach ( array_keys( $widgets ) as $slug ) {
					$new_statuses[ $slug ] = true;
				}
				Elonix_Toolkit_Widget_Manager::save_statuses( $new_statuses );
				add_settings_error( 'elonix_widgets', 'bulk_enabled', esc_html__( 'All widgets enabled successfully.', 'elonix' ), 'success' );
			} elseif ( 'disable_all' === $bulk_action_val ) {
				foreach ( array_keys( $widgets ) as $slug ) {
					$new_statuses[ $slug ] = false;
				}
				Elonix_Toolkit_Widget_Manager::save_statuses( $new_statuses );
				add_settings_error( 'elonix_widgets', 'bulk_disabled', esc_html__( 'All widgets disabled successfully.', 'elonix' ), 'success' );
			}
			return;
		}

		// Handle standard save
		$submitted_widgets = isset( $_POST['widgets'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['widgets'] ) ) : array();
		$widgets           = Elonix_Toolkit_Widget_Manager::get_registered_widgets();
		$new_statuses      = array();

		foreach ( array_keys( $widgets ) as $slug ) {
			$new_statuses[ $slug ] = isset( $submitted_widgets[ $slug ] ) && '1' === $submitted_widgets[ $slug ];
		}

		Elonix_Toolkit_Widget_Manager::save_statuses( $new_statuses );
		add_settings_error( 'elonix_widgets', 'settings_saved', esc_html__( 'Widget settings saved successfully.', 'elonix' ), 'success' );
	}

	/**
	 * AJAX handler to toggle a single widget status.
	 */
	public function ajax_toggle_widget() {
		check_ajax_referer( 'elonix_widgets_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'elonix' ) ), 403 );
		}

		$slug   = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		$status = isset( $_POST['status'] ) && '1' === $_POST['status'];

		if ( empty( $slug ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid widget slug.', 'elonix' ) ) );
		}

		// Verify widget is registered
		$widgets = Elonix_Toolkit_Widget_Manager::get_registered_widgets();
		if ( ! array_key_exists( $slug, $widgets ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Widget not found in registry.', 'elonix' ) ) );
		}

		Elonix_Toolkit_Widget_Manager::save_widget_status( $slug, $status );

		$message = $status ? esc_html__( 'Widget enabled successfully.', 'elonix' ) : esc_html__( 'Widget disabled successfully.', 'elonix' );
		wp_send_json_success( array( 'message' => $message ) );
	}

	/**
	 * AJAX handler for bulk widget updates.
	 */
	public function ajax_bulk_widgets() {
		check_ajax_referer( 'elonix_widgets_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'elonix' ) ), 403 );
		}

		$bulk_action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_action'] ) ) : '';

		if ( ! in_array( $bulk_action, array( 'enable_all', 'disable_all' ), true ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid bulk action.', 'elonix' ) ) );
		}

		$widgets      = Elonix_Toolkit_Widget_Manager::get_registered_widgets();
		$new_statuses = array();
		$status       = ( 'enable_all' === $bulk_action );

		foreach ( array_keys( $widgets ) as $slug ) {
			$new_statuses[ $slug ] = $status;
		}

		Elonix_Toolkit_Widget_Manager::save_statuses( $new_statuses );

		$message = $status ? esc_html__( 'All widgets enabled successfully.', 'elonix' ) : esc_html__( 'All widgets disabled successfully.', 'elonix' );
		wp_send_json_success( array( 'message' => $message ) );
	}

	/**
	 * Render Widgets page content.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$widgets        = Elonix_Toolkit_Widget_Manager::get_registered_widgets();
		$enabled_count  = count( Elonix_Toolkit_Widget_Manager::get_enabled_widgets() );
		$disabled_count = count( $widgets ) - $enabled_count;
		?>
		<div class="wrap elonix-admin-page elonix-widgets-wrap">
			
			<!-- Compact Professional Header -->
			<div class="es-admin-header">
				<div class="es-header-branding">
					<div class="es-header-logo">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" fill="none">
							<rect width="50" height="50" rx="12" fill="url(#es-head-grad)"/>
							<path d="M25 14L14 22L25 30L36 22L25 14Z" fill="white"/>
							<path d="M14 29L25 36L36 29" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
							<defs>
								<linearGradient id="es-head-grad" x1="0" y1="0" x2="50" y2="50" gradientUnits="userSpaceOnUse">
									<stop stop-color="#6366f1"/>
									<stop offset="1" stop-color="#4f46e5"/>
								</linearGradient>
							</defs>
						</svg>
					</div>
					<div class="es-header-info">
						<div class="es-header-title-row">
							<h2><?php esc_html_e( 'Widgets Manager', 'elonix' ); ?></h2>
							<span class="es-header-badge">ESKIT v<?php echo esc_html( ELONIX_VERSION ); ?></span>
						</div>
						<p class="es-header-subtitle"><?php esc_html_e( 'Enable or disable specific Elementor widgets to optimize loading performance.', 'elonix' ); ?></p>
					</div>
				</div>
				
				<!-- Header Stats Pill Box -->
				<div class="es-header-stats">
					<div class="es-stat-pill all-stat">
						<span class="stat-num"><?php echo count( $widgets ); ?></span>
						<span class="stat-lbl"><?php esc_html_e( 'Total', 'elonix' ); ?></span>
					</div>
					<div class="es-stat-pill enabled-stat">
						<span class="stat-num count-enabled"><?php echo esc_html( $enabled_count ); ?></span>
						<span class="stat-lbl"><?php esc_html_e( 'Active', 'elonix' ); ?></span>
					</div>
					<div class="es-stat-pill disabled-stat">
						<span class="stat-num count-disabled"><?php echo esc_html( $disabled_count ); ?></span>
						<span class="stat-lbl"><?php esc_html_e( 'Inactive', 'elonix' ); ?></span>
					</div>
				</div>
			</div>

			<!-- Toolbar Row -->
			<div class="es-admin-toolbar">
				<div class="es-toolbar-left">
					<div class="es-toolbar-tabs">
						<button type="button" class="es-filter-tab active" data-filter="all">
							<?php esc_html_e( 'All', 'elonix' ); ?>
						</button>
						<button type="button" class="es-filter-tab" data-filter="enabled">
							<?php esc_html_e( 'Active', 'elonix' ); ?>
						</button>
						<button type="button" class="es-filter-tab" data-filter="disabled">
							<?php esc_html_e( 'Inactive', 'elonix' ); ?>
						</button>
					</div>
				</div>
				
				<div class="es-toolbar-right">
					<div class="es-toolbar-search">
						<span class="dashicons dashicons-search search-icon"></span>
						<input type="text" id="es-search-input" placeholder="<?php esc_attr_e( 'Search widgets...', 'elonix' ); ?>">
					</div>
					
					<div class="es-toolbar-actions">
						<button type="button" class="es-bulk-btn button es-btn-enable" data-type="widget" data-action="enable_all">
							<span class="dashicons dashicons-yes"></span>
							<span class="btn-text"><?php esc_html_e( 'Enable All', 'elonix' ); ?></span>
							<span class="es-spinner"></span>
						</button>
						<button type="button" class="es-bulk-btn button es-btn-disable" data-type="widget" data-action="disable_all">
							<span class="dashicons dashicons-no"></span>
							<span class="btn-text"><?php esc_html_e( 'Disable All', 'elonix' ); ?></span>
							<span class="es-spinner"></span>
						</button>
					</div>
				</div>
			</div>

			<!-- Output settings messages -->
			<?php settings_errors( 'elonix_widgets' ); ?>

			<!-- Grid System -->
			<div class="es-cards-container">
				<div class="es-cards-grid">
					<?php if ( ! empty( $widgets ) ) : ?>
						<?php
						foreach ( $widgets as $slug => $data ) :
							$enabled    = Elonix_Toolkit_Widget_Manager::is_widget_enabled( $slug );
							$keywords   = isset( $data['keywords'] ) ? implode( ',', $data['keywords'] ) : '';
							$icon_class = isset( $data['icon'] ) ? $data['icon'] : 'eicon-code';
							$version    = isset( $data['version'] ) ? $data['version'] : '1.0.0';
							?>
							<div class="es-card-item <?php echo $enabled ? '' : 'is-disabled'; ?>" data-slug="<?php echo esc_attr( $slug ); ?>" data-keywords="<?php echo esc_attr( $keywords ); ?>">
								<div class="es-card-header">
									<div class="es-card-icon-box">
										<span class="<?php echo esc_attr( $icon_class ); ?> es-widget-icon"></span>
									</div>
									<div class="es-card-badges">
										<span class="es-badge-kit"><?php echo esc_html( ELONIX_WIDGET_BADGE ); ?></span>
										<span class="es-badge-ver">v<?php echo esc_html( $version ); ?></span>
									</div>
								</div>
								
								<div class="es-card-body">
									<h3 class="es-card-title"><?php echo esc_html( $data['title'] ); ?></h3>
									<p class="es-card-desc"><?php echo esc_html( $data['description'] ); ?></p>
								</div>
								
								<div class="es-card-footer">
									<div class="es-status-badge-container">
										<?php if ( $enabled ) : ?>
											<span class="es-status-badge badge-active"><?php esc_html_e( 'Active', 'elonix' ); ?></span>
										<?php else : ?>
											<span class="es-status-badge badge-inactive"><?php esc_html_e( 'Inactive', 'elonix' ); ?></span>
										<?php endif; ?>
									</div>
									<div class="es-action-toggle">
										<label class="elonix-switch" aria-label="<?php esc_attr_e( 'Toggle Widget Activation State', 'elonix' ); ?>">
											<input type="checkbox" class="es-toggle-input" data-type="widget" data-slug="<?php echo esc_attr( $slug ); ?>" value="1" <?php checked( $enabled ); ?>>
											<span class="slider round"></span>
										</label>
									</div>
								</div>
								<div class="es-card-loading-overlay">
									<span class="es-card-spinner"></span>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="es-empty-state">
							<span class="dashicons dashicons-admin-plugins"></span>
							<h3><?php esc_html_e( 'No widgets found.', 'elonix' ); ?></h3>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
