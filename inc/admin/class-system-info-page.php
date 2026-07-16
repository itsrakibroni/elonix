<?php
/**
 * Elonix – Toolkit for Elementor System Info Page Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_System_Info_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Enqueue stylesheet.
	 *
	 * @param string $hook Screen hook.
	 */
	public function enqueue_styles( $hook ) {
		if ( 'elonix_page_elonix-system-info' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'elonix-system-info-css',
			ELONIX_ACC_URL . 'assets/admin/css/system-info.css',
			array(),
			ELONIX_VERSION
		);
	}

	/**
	 * Render System Info & Diagnostics page content.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Retrieve diagnostic information
		require_once ELONIX_ACC_PATH . 'inc/diagnostics/class-system-info.php';

		$wp      = Elonix_Toolkit_System_Info::get_wordpress_info();
		$server  = Elonix_Toolkit_System_Info::get_server_info();
		$plugins = Elonix_Toolkit_System_Info::get_plugin_info();
		$db      = Elonix_Toolkit_System_Info::get_database_info();
		$active  = Elonix_Toolkit_System_Info::get_active_plugins();

		// Generate the clipboard raw report
		$report = Elonix_Toolkit_System_Info::generate_report();

		?>
		<div class="wrap elonix-sysinfo-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Elonix – Toolkit for Elementor System Info & Diagnostics', 'elonix' ); ?></h1>
			<hr class="wp-header-end">

			<!-- Header actions bar -->
			<div class="elonix-sysinfo-header">
				<div class="header-intro">
					<p><?php esc_html_e( 'View the environment variables, server settings, database specifications, and plugins configuration. You can copy the diagnostic report below to attach it to support tickets.', 'elonix' ); ?></p>
				</div>
				<div class="header-actions">
					<button type="button" id="elonix-copy-report-btn" class="elonix-copy-btn">
						<span class="dashicons dashicons-admin-page"></span>
						<span class="btn-text"><?php esc_html_e( 'Copy System Information', 'elonix' ); ?></span>
					</button>
				</div>
			</div>

			<!-- Hidden Report Textarea for Clipboard Copying -->
			<textarea id="elonix-sysinfo-report" style="position: absolute; left: -9999px; top: -9999px;" aria-hidden="true"><?php echo esc_textarea( $report ); ?></textarea>

			<!-- Diagnostics Panels Grid -->
			<div class="elonix-sysinfo-panels">
				
				<!-- Row 1: WordPress Environment -->
				<div class="elonix-sysinfo-card">
					<div class="elonix-sysinfo-card-header">
						<h2>
							<span class="dashicons dashicons-wordpress"></span>
							<?php esc_html_e( 'WordPress Environment', 'elonix' ); ?>
						</h2>
					</div>
					<table class="elonix-sysinfo-table">
						<tbody>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'WordPress Version', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $wp['version'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Site URL', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_url( $wp['site_url'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Home URL', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_url( $wp['home_url'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Multisite Status', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $wp['multisite'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Debug Mode Status', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $wp['debug_mode'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Language', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $wp['locale'] ); ?></td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Row 2: Server Environment -->
				<div class="elonix-sysinfo-card">
					<div class="elonix-sysinfo-card-header">
						<h2>
							<span class="dashicons dashicons-performance"></span>
							<?php esc_html_e( 'Server Environment', 'elonix' ); ?>
						</h2>
					</div>
					<table class="elonix-sysinfo-table">
						<tbody>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'PHP Version', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $server['php_version'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'PHP Memory Limit', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $server['memory_limit'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Max Execution Time', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $server['max_execution_time'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Upload Max Filesize', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $server['upload_max_size'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Post Max Size', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $server['post_max_size'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Max Input Vars', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $server['max_input_vars'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Server Software', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $server['server_software'] ); ?></td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Row 3: Plugin & Theme Environment -->
				<div class="elonix-sysinfo-card">
					<div class="elonix-sysinfo-card-header">
						<h2>
							<span class="dashicons dashicons-admin-plugins"></span>
							<?php esc_html_e( 'Plugin Environment', 'elonix' ); ?>
						</h2>
					</div>
					<table class="elonix-sysinfo-table">
						<tbody>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Elonix – Toolkit for Elementor Version', 'elonix' ); ?></td>
								<td class="parameter-value">v<?php echo esc_html( $plugins['toolkit_version'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Elementor Version', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $plugins['elementor_version'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Elementor Pro Version', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $plugins['elementor_pro'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Active Theme', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $plugins['theme_name'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Theme Version', 'elonix' ); ?></td>
								<td class="parameter-value">v<?php echo esc_html( $plugins['theme_version'] ); ?></td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Row 4: Database Information -->
				<div class="elonix-sysinfo-card">
					<div class="elonix-sysinfo-card-header">
						<h2>
							<span class="dashicons dashicons-database"></span>
							<?php esc_html_e( 'Database Information', 'elonix' ); ?>
						</h2>
					</div>
					<table class="elonix-sysinfo-table">
						<tbody>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Database Version', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $db['db_version'] ); ?></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'Database Prefix', 'elonix' ); ?></td>
								<td class="parameter-value"><code><?php echo esc_html( $db['prefix'] ); ?></code></td>
							</tr>
							<tr>
								<td class="parameter-label"><?php esc_html_e( 'WordPress Memory Limit', 'elonix' ); ?></td>
								<td class="parameter-value"><?php echo esc_html( $db['wp_mem_limit'] ); ?></td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Row 5: Active Plugins list -->
				<div class="elonix-sysinfo-card">
					<div class="elonix-sysinfo-card-header">
						<h2>
							<span class="dashicons dashicons-admin-plugins"></span>
							<?php esc_html_e( 'Active Plugins', 'elonix' ); ?>
						</h2>
					</div>
					<ul class="active-plugins-list">
						<?php if ( ! empty( $active ) ) : ?>
							<?php foreach ( $active as $item ) : ?>
								<li>
									<span class="plugin-title"><?php echo esc_html( $item['name'] ); ?></span>
									<div>
										<span class="plugin-version-badge">v<?php echo esc_html( $item['version'] ); ?></span>
										<span class="plugin-status-badge"><?php echo esc_html( $item['status'] ); ?></span>
									</div>
								</li>
							<?php endforeach; ?>
						<?php else : ?>
							<li style="padding: 20px;"><?php esc_html_e( 'No active plugins found.', 'elonix' ); ?></li>
						<?php endif; ?>
					</ul>
				</div>

			</div>
		</div>
		<?php
	}
}
