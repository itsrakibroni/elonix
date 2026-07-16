<?php
/**
 * Elonix – Toolkit for Elementor Dashboard Page Renderer
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Dashboard_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_styles' ) );
	}

	/**
	 * Enqueue styles for the dashboard page.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_dashboard_styles( $hook ) {
		if ( 'toplevel_page_elonix' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'elonix-dashboard-css',
			ELONIX_ACC_URL . 'assets/admin/css/dashboard.css',
			array(),
			ELONIX_VERSION
		);
	}

	/**
	 * Render the Dashboard page content.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Retrieve active widgets and modules counts dynamically
		$active_widgets = 0;
		if ( class_exists( 'Elonix_Toolkit_Widget_Manager' ) ) {
			$active_widgets = count( Elonix_Toolkit_Widget_Manager::get_enabled_widgets() );
		}

		$active_modules = 0;
		if ( class_exists( 'Elonix_Toolkit_Module_Manager' ) ) {
			$active_modules = count( Elonix_Toolkit_Module_Manager::get_enabled_modules() );
		}

		// Elementor Status
		if ( did_action( 'elementor/loaded' ) ) {
			/* translators: %s: string */
			$elementor_status  = sprintf( esc_html__( 'Active (v%s)', 'elonix' ), ELEMENTOR_VERSION );
			$elementor_version = ELEMENTOR_VERSION;
		} else {
			$elementor_status  = esc_html__( 'Inactive', 'elonix' );
			$elementor_version = esc_html__( 'Not Installed', 'elonix' );
		}

		// System Details
		$php_version  = phpversion();
		$wp_version   = get_bloginfo( 'version' );
		$memory_limit = defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : ini_get( 'memory_limit' );
		$upload_limit = size_format( wp_max_upload_size() );

		?>
		<div class="wrap elonix-dashboard-wrap">
			<!-- Welcome Header / Hero -->
			<div class="elonix-welcome-card">
				<div class="elonix-welcome-logo">
					<img src="<?php echo esc_url( ELONIX_ACC_URL . 'assets/images/logo.png' ); ?>" alt="Elonix – Toolkit for Elementor Logo">
				</div>
				<div class="elonix-welcome-content">
					<h1><?php esc_html_e( 'Elonix – Toolkit for Elementor', 'elonix' ); ?></h1>
					<span class="elonix-version">v<?php echo esc_html( ELONIX_VERSION ); ?></span>
					<p><?php esc_html_e( 'Welcome to Elonix – Toolkit for Elementor. Power up your Elementor page building experience with creative widgets, modern templates, and advanced features designed for performance and flexibility.', 'elonix' ); ?></p>
				</div>
			</div>

			<!-- Main Grid -->
			<div class="elonix-dashboard-grid">
				<!-- Left: Main Contents -->
				<div class="elonix-grid-column left-column">

					<!-- Quick Stats -->
					<div class="elonix-dashboard-section">
						<h2 class="section-title"><?php esc_html_e( 'Quick Statistics', 'elonix' ); ?></h2>
						<div class="elonix-stats-grid">
							<div class="elonix-stat-card">
								<span class="dashicons dashicons-admin-plugins"></span>
								<div class="stat-info">
									<span class="stat-value"><?php echo esc_html( $active_widgets ); ?></span>
									<span class="stat-label"><?php esc_html_e( 'Active Widgets', 'elonix' ); ?></span>
								</div>
							</div>
							<div class="elonix-stat-card">
								<span class="dashicons dashicons-category"></span>
								<div class="stat-info">
									<span class="stat-value"><?php echo esc_html( $active_modules ); ?></span>
									<span class="stat-label"><?php esc_html_e( 'Active Modules', 'elonix' ); ?></span>
								</div>
							</div>
							<div class="elonix-stat-card">
								<span class="dashicons dashicons-performance"></span>
								<div class="stat-info">
									<span class="stat-value"><?php echo esc_html( $elementor_status ); ?></span>
									<span class="stat-label"><?php esc_html_e( 'Elementor Status', 'elonix' ); ?></span>
								</div>
							</div>
							<div class="elonix-stat-card">
								<span class="dashicons dashicons-wordpress"></span>
								<div class="stat-info">
									<span class="stat-value">v<?php echo esc_html( $wp_version ); ?></span>
									<span class="stat-label"><?php esc_html_e( 'WordPress Version', 'elonix' ); ?></span>
								</div>
							</div>
						</div>
					</div>

					<!-- Quick Actions -->
					<div class="elonix-dashboard-section">
						<h2 class="section-title"><?php esc_html_e( 'Quick Actions', 'elonix' ); ?></h2>
						<div class="elonix-actions-grid">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=elonix-widgets' ) ); ?>" class="elonix-action-btn">
								<span class="dashicons dashicons-admin-plugins"></span>
								<?php esc_html_e( 'Manage Widgets', 'elonix' ); ?>
							</a>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=elonix-modules' ) ); ?>" class="elonix-action-btn">
								<span class="dashicons dashicons-category"></span>
								<?php esc_html_e( 'Manage Modules', 'elonix' ); ?>
							</a>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=elonix-settings' ) ); ?>" class="elonix-action-btn">
								<span class="dashicons dashicons-admin-generic"></span>
								<?php esc_html_e( 'Settings', 'elonix' ); ?>
							</a>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=elonix-system-info' ) ); ?>" class="elonix-action-btn">
								<span class="dashicons dashicons-info"></span>
								<?php esc_html_e( 'System Info', 'elonix' ); ?>
							</a>
						</div>
					</div>

					<!-- Resources -->
					<div class="elonix-dashboard-section">
						<h2 class="section-title"><?php esc_html_e( 'Helpful Resources', 'elonix' ); ?></h2>
						<div class="elonix-resources-grid">
							<div class="elonix-resource-card">
								<span class="dashicons dashicons-media-document"></span>
								<h3><?php esc_html_e( 'Documentation', 'elonix' ); ?></h3>
								<p><?php esc_html_e( 'Learn how to configure and build with Elonix – Toolkit for Elementor widgets.', 'elonix' ); ?></p>
								<a href="https://docs.elonix.com" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read Docs →', 'elonix' ); ?></a>
							</div>
							<div class="elonix-resource-card">
								<span class="dashicons dashicons-welcome-learn-more"></span>
								<h3><?php esc_html_e( 'Community Support', 'elonix' ); ?></h3>
								<p><?php esc_html_e( 'Need help? Submit a ticket or browse the user forums for answers.', 'elonix' ); ?></p>
								<a href="https://elonix.com/support" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get Support →', 'elonix' ); ?></a>
							</div>
							<div class="elonix-resource-card">
								<span class="dashicons dashicons-format-aside"></span>
								<h3><?php esc_html_e( 'Changelog', 'elonix' ); ?></h3>
								<p><?php esc_html_e( 'Keep track of all new features, bug fixes, and core updates.', 'elonix' ); ?></p>
								<a href="https://elonix.com/changelog" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View Changelog →', 'elonix' ); ?></a>
							</div>
							<div class="elonix-resource-card">
								<span class="dashicons dashicons-excerpt-view"></span>
								<h3><?php esc_html_e( 'Roadmap', 'elonix' ); ?></h3>
								<p><?php esc_html_e( 'See what we\'re working on and vote for upcoming widgets and modules.', 'elonix' ); ?></p>
								<a href="https://elonix.com/roadmap" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Our Roadmap →', 'elonix' ); ?></a>
							</div>
						</div>
					</div>

				</div>

				<!-- Right: Sidebar Status -->
				<div class="elonix-grid-column right-column">
					<div class="elonix-dashboard-section">
						<h2 class="section-title"><?php esc_html_e( 'System Status', 'elonix' ); ?></h2>
						<div class="elonix-status-card">
							<ul class="elonix-status-list">
								<li>
									<span class="status-label"><?php esc_html_e( 'PHP Version', 'elonix' ); ?></span>
									<span class="status-val"><?php echo esc_html( $php_version ); ?></span>
								</li>
								<li>
									<span class="status-label"><?php esc_html_e( 'WordPress Version', 'elonix' ); ?></span>
									<span class="status-val"><?php echo esc_html( $wp_version ); ?></span>
								</li>
								<li>
									<span class="status-label"><?php esc_html_e( 'Elementor Version', 'elonix' ); ?></span>
									<span class="status-val"><?php echo esc_html( $elementor_version ); ?></span>
								</li>
								<li>
									<span class="status-label"><?php esc_html_e( 'Memory Limit', 'elonix' ); ?></span>
									<span class="status-val"><?php echo esc_html( $memory_limit ); ?></span>
								</li>
								<li>
									<span class="status-label"><?php esc_html_e( 'Upload Max Filesize', 'elonix' ); ?></span>
									<span class="status-val"><?php echo esc_html( $upload_limit ); ?></span>
								</li>
							</ul>
							<div class="status-footer">
								<span class="dashicons dashicons-yes-alt status-check-icon"></span>
								<span><?php esc_html_e( 'System meets minimum requirements.', 'elonix' ); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
