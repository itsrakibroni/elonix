<?php
/**
 * Elonix – Toolkit for Elementor Advanced 404 Builder Admin Interface and Reporting Panel
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_404_Admin {

	/**
	 * Analytics logging instance.
	 *
	 * @var Elonix_Toolkit_404_Analytics
	 */
	private $analytics;

	/**
	 * Constructor.
	 *
	 * @param Elonix_Toolkit_404_Analytics $analytics Analytics instance.
	 */
	public function __construct( $analytics ) {
		$this->analytics = $analytics;

		// Add admin menu item hook
		add_action( 'admin_menu', array( $this, 'register_admin_submenu' ), 40 );

		// Enqueue scripts and styles inside admin area
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Intercept post requests for clearing logs or exporting CSV
		add_action( 'admin_init', array( $this, 'handle_log_actions' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ) );
	}

	/**
	 * Register the 404 Builder submenu page under Elonix – Toolkit for Elementor parent menu.
	 */
	public function register_admin_submenu() {
		add_submenu_page(
			'elonix',
			esc_html__( '404 Builder', 'elonix' ),
			esc_html__( '404 Builder', 'elonix' ),
			'manage_options',
			'elonix-404',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Load scripts, style overrides, and admin assets conditional to the current screen.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'elonix_page_elonix-404' !== $hook ) {
			return;
		}
		// Enqueue common admin stylesheet
		wp_enqueue_style( 'elonix-admin-css' );
	}

	/**
	 * Intercept actions like export CSV or clear logs.
	 */
	public function handle_log_actions() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) || ! isset( $_GET['page'] ) || 'elonix-404' !== $_GET['page'] ) {
			return;
		}

		if ( isset( $_GET['action'] ) ) {
			$action = sanitize_key( $_GET['action'] );

			if ( 'clear_logs' === $action ) {
				check_admin_referer( 'tv_404_clear_logs_nonce', 'nonce' );
				$this->analytics->clear_logs();

				wp_safe_redirect(
					add_query_arg(
						array(
							'status' => 'logs_cleared',
							'tab'    => 'analytics',
						),
						admin_url( 'admin.php?page=elonix-404' )
					)
				);
				exit;
			}

			if ( 'export_csv' === $action ) {
				check_admin_referer( 'tv_404_export_csv_nonce', 'nonce' );

				// Prevent headers already sent errors
				if ( headers_sent( $sent_file, $sent_line ) ) {
					wp_die( esc_html__( 'Unable to export CSV: headers already sent.', 'elonix' ), 500 );
				}

				$logs = $this->analytics->get_recent_logs( 5000 );

				header( 'Content-Type: text/csv; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename=elonix_404_logs_' . gmdate( 'Y-m-d' ) . '.csv' );

				$output = fopen( 'php://output', 'w' );
				fputcsv( $output, array( 'Requested URL', 'Referrer Source', 'User Agent', 'Hits Count', 'Last Seen Date' ) );

				foreach ( $logs as $log ) {
					fputcsv(
						$output,
						array(
							$log->url,
							$log->referrer,
							$log->user_agent,
							$log->hits,
							$log->updated_at,
						)
					);
				}
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
				fclose( $output );
				exit;
			}
		}
	}

	/**
	 * Handle settings update requests.
	 */
	public function save_settings() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) || ! isset( $_POST['tv_404_save_nonce_field'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tv_404_save_nonce_field'] ) ), 'tv_404_save_settings_action' ) ) {
			wp_die( esc_html__( 'Security token check failed. Please refresh and try again.', 'elonix' ) );
		}

		$settings = get_option( 'elonix_settings', array() );

		// 1. General Tab Options
		$settings['tv_404_enable_custom_page']        = isset( $_POST['tv_404_enable_custom_page'] ) ? 'yes' : 'no';
		$settings['tv_404_selected_page_id']          = isset( $_POST['tv_404_selected_page_id'] ) ? intval( $_POST['tv_404_selected_page_id'] ) : 0;
		$settings['tv_404_hide_page_list']            = isset( $_POST['tv_404_hide_page_list'] ) ? 'yes' : 'no';
		$settings['tv_404_allow_admin_direct_access'] = isset( $_POST['tv_404_allow_admin_direct_access'] ) ? 'yes' : 'no';
		$settings['tv_404_send_410_header']           = isset( $_POST['tv_404_send_410_header'] ) ? 'yes' : 'no';
		$settings['tv_404_enable_logging']            = isset( $_POST['tv_404_enable_logging'] ) ? 'yes' : 'no';
		$settings['tv_404_show_header']               = isset( $_POST['tv_404_show_header'] ) ? 'yes' : 'no';
		$settings['tv_404_show_footer']               = isset( $_POST['tv_404_show_footer'] ) ? 'yes' : 'no';

		// 2. Advanced Tab Options
		$settings['tv_404_force_after_load']     = isset( $_POST['tv_404_force_after_load'] ) ? 'yes' : 'no';
		$settings['tv_404_disable_url_guessing'] = isset( $_POST['tv_404_disable_url_guessing'] ) ? 'yes' : 'no';
		$settings['tv_404_custom_status_code']   = isset( $_POST['tv_404_custom_status_code'] ) ? intval( $_POST['tv_404_custom_status_code'] ) : 404;

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$excluded_roles                         = isset( $_POST['tv_404_excluded_user_roles'] ) ? (array) wp_unslash( $_POST['tv_404_excluded_user_roles'] ) : array();
		$settings['tv_404_excluded_user_roles'] = array_map( 'sanitize_text_field', $excluded_roles );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$excluded_post_types                    = isset( $_POST['tv_404_excluded_post_types'] ) ? (array) wp_unslash( $_POST['tv_404_excluded_post_types'] ) : array();
		$settings['tv_404_excluded_post_types'] = array_map( 'sanitize_text_field', $excluded_post_types );

		$settings['tv_404_excluded_urls']             = isset( $_POST['tv_404_excluded_urls'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tv_404_excluded_urls'] ) ) : '';
		$settings['tv_404_excluded_query_parameters'] = isset( $_POST['tv_404_excluded_query_parameters'] ) ? sanitize_text_field( wp_unslash( $_POST['tv_404_excluded_query_parameters'] ) ) : '';

		// Custom Redirect Rules is a list of patterns (e.g. ^/old-link => /new-link)
		$settings['tv_404_custom_redirect_rules'] = isset( $_POST['tv_404_custom_redirect_rules'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tv_404_custom_redirect_rules'] ) ) : '';

		// 3. SEO Tab Options
		$settings['tv_404_seo_noindex']                   = isset( $_POST['tv_404_seo_noindex'] ) ? 'yes' : 'no';
		$settings['tv_404_seo_nofollow']                  = isset( $_POST['tv_404_seo_nofollow'] ) ? 'yes' : 'no';
		$settings['tv_404_seo_disable_redirect_guessing'] = isset( $_POST['tv_404_seo_disable_redirect_guessing'] ) ? 'yes' : 'no';
		$settings['tv_404_seo_410_header']                = isset( $_POST['tv_404_seo_410_header'] ) ? 'yes' : 'no';
		$settings['tv_404_seo_canonical_control']         = isset( $_POST['tv_404_seo_canonical_control'] ) ? esc_url_raw( wp_unslash( $_POST['tv_404_seo_canonical_control'] ) ) : '';
		$settings['tv_404_seo_robots_control']            = isset( $_POST['tv_404_seo_robots_control'] ) ? sanitize_text_field( wp_unslash( $_POST['tv_404_seo_robots_control'] ) ) : '';

		// 4. Custom Scripts (Advanced Code Block Injections)
		// Bypass simple sanitization to allow custom analytics script insertions, safely wrapped in wp_kses_post
		$settings['tv_404_custom_header_code'] = isset( $_POST['tv_404_custom_header_code'] ) ? wp_kses_post( wp_unslash( $_POST['tv_404_custom_header_code'] ) ) : '';
		$settings['tv_404_custom_footer_code'] = isset( $_POST['tv_404_custom_footer_code'] ) ? wp_kses_post( wp_unslash( $_POST['tv_404_custom_footer_code'] ) ) : '';

		update_option( 'elonix_settings', $settings );

		$current_tab = isset( $_POST['tv_404_active_tab'] ) ? sanitize_key( $_POST['tv_404_active_tab'] ) : 'general';
		wp_safe_redirect(
			add_query_arg(
				array(
					'status' => 'success',
					'tab'    => $current_tab,
				),
				admin_url( 'admin.php?page=elonix-404' )
			)
		);
		exit;
	}

	/**
	 * Output Settings Page Layout.
	 */
	public function render_admin_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';

		// Get all pages to populate selected pages dropdown list
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'numberposts' => -1,
				'post_status' => 'publish',
			)
		);

		// Fetch saved values
		$custom_page_enabled = Elonix_Settings::get( 'tv_404_enable_custom_page' ) ?? 'no';
		$selected_page_id    = Elonix_Settings::get( 'tv_404_selected_page_id' ) ?? 0;
		$hide_page_list      = Elonix_Settings::get( 'tv_404_hide_page_list' ) ?? 'no';
		$allow_admin         = Elonix_Settings::get( 'tv_404_allow_admin_direct_access' ) ?? 'yes';
		$send_410            = Elonix_Settings::get( 'tv_404_send_410_header' ) ?? 'no';
		$enable_logging      = Elonix_Settings::get( 'tv_404_enable_logging' ) ?? 'yes';
		$show_header         = Elonix_Settings::get( 'tv_404_show_header' ) ?? 'yes';
		$show_footer         = Elonix_Settings::get( 'tv_404_show_footer' ) ?? 'yes';

		$force_404           = Elonix_Settings::get( 'tv_404_force_after_load' ) ?? 'no';
		$disable_guessing    = Elonix_Settings::get( 'tv_404_disable_url_guessing' ) ?? 'yes';
		$custom_status       = Elonix_Settings::get( 'tv_404_custom_status_code' ) ?? 404;
		$excluded_roles      = Elonix_Settings::get( 'tv_404_excluded_user_roles' ) ?? array();
		$excluded_post_types = Elonix_Settings::get( 'tv_404_excluded_post_types' ) ?? array();
		$excluded_urls       = Elonix_Settings::get( 'tv_404_excluded_urls' ) ?? '';
		$excluded_queries    = Elonix_Settings::get( 'tv_404_excluded_query_parameters' ) ?? '';
		$redirect_rules      = Elonix_Settings::get( 'tv_404_custom_redirect_rules' ) ?? '';

		$seo_noindex       = Elonix_Settings::get( 'tv_404_seo_noindex' ) ?? 'yes';
		$seo_nofollow      = Elonix_Settings::get( 'tv_404_seo_nofollow' ) ?? 'yes';
		$seo_disable_guess = Elonix_Settings::get( 'tv_404_seo_disable_redirect_guessing' ) ?? 'yes';
		$seo_410           = Elonix_Settings::get( 'tv_404_seo_410_header' ) ?? 'no';
		$seo_canonical     = Elonix_Settings::get( 'tv_404_seo_canonical_control' ) ?? '';
		$seo_robots        = Elonix_Settings::get( 'tv_404_seo_robots_control' ) ?? '';

		$header_code = Elonix_Settings::get( 'tv_404_custom_header_code' ) ?? '';
		$footer_code = Elonix_Settings::get( 'tv_404_custom_footer_code' ) ?? '';

		?>
		<div class="wrap elonix-admin-wrap" style="max-width: 1100px; margin-top: 20px;">
			<h1 style="display: flex; align-items: center; font-weight: 700; margin-bottom: 20px;">
				<span class="dashicons dashicons-warning" style="font-size: 28px; width: 28px; height: 28px; margin-right: 10px; color: #ef4444;"></span>
				<?php esc_html_e( 'Elonix Advanced 404 Builder', 'elonix' ); ?>
			</h1>

			<?php if ( 'success' === $status ) : ?>
				<div class="notice notice-success is-dismissible" style="margin-left: 0; margin-right: 0;"><p><?php esc_html_e( 'Settings updated successfully!', 'elonix' ); ?></p></div>
			<?php elseif ( 'logs_cleared' === $status ) : ?>
				<div class="notice notice-success is-dismissible" style="margin-left: 0; margin-right: 0;"><p><?php esc_html_e( 'Analytics logs cleared successfully!', 'elonix' ); ?></p></div>
			<?php endif; ?>

			<h2 class="nav-tab-wrapper" style="margin-bottom: 20px;">
				<a href="?page=elonix-404&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'General', 'elonix' ); ?></a>
				<a href="?page=elonix-404&tab=advanced" class="nav-tab <?php echo 'advanced' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Advanced', 'elonix' ); ?></a>
				<a href="?page=elonix-404&tab=seo" class="nav-tab <?php echo 'seo' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'SEO Settings', 'elonix' ); ?></a>
				<a href="?page=elonix-404&tab=analytics" class="nav-tab <?php echo 'analytics' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Analytics Reports', 'elonix' ); ?></a>
				<a href="?page=elonix-404&tab=integrations" class="nav-tab <?php echo 'integrations' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Integrations', 'elonix' ); ?></a>
			</h2>

			<form method="post" action="">
				<?php wp_nonce_field( 'tv_404_save_settings_action', 'tv_404_save_nonce_field' ); ?>
				<input type="hidden" name="tv_404_active_tab" value="<?php echo esc_attr( $active_tab ); ?>" />

				<div class="elonix-settings-content" style="background: #ffffff; padding: 24px; border: 1px solid #ccd0d4; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

					<!-- GENERAL TAB PANEL -->
					<?php if ( 'general' === $active_tab ) : ?>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Enable Custom 404 Page', 'elonix' ); ?></th>
								<td>
									<label class="tv-switch">
										<input type="checkbox" name="tv_404_enable_custom_page" value="yes" <?php checked( $custom_page_enabled, 'yes' ); ?> />
										<span class="tv-slider"></span>
									</label>
									<p class="description"><?php esc_html_e( 'Toggle to activate the custom template rendering engine.', 'elonix' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="tv_404_selected_page_id"><?php esc_html_e( 'Select 404 Page Template', 'elonix' ); ?></label></th>
								<td>
									<select id="tv_404_selected_page_id" name="tv_404_selected_page_id" class="regular-text">
										<option value=""><?php esc_html_e( '-- Choose Page --', 'elonix' ); ?></option>
										<?php foreach ( $pages as $page ) : ?>
											<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $selected_page_id, $page->ID ); ?>>
												<?php echo esc_html( $page->post_title ); ?> (ID: <?php echo esc_attr( $page->ID ); ?>)
											</option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'Choose any standard page or Elementor custom template to load on 404 status queries.', 'elonix' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Hide Page from List', 'elonix' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="tv_404_hide_page_list" value="yes" <?php checked( $hide_page_list, 'yes' ); ?> />
										<?php esc_html_e( 'Hide the selected 404 template from the standard admin Pages list query.', 'elonix' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Allow Administrator Access', 'elonix' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="tv_404_allow_admin_direct_access" value="yes" <?php checked( $allow_admin, 'yes' ); ?> />
										<?php esc_html_e( 'Allow administrators to access the selected 404 page directly for validation testing.', 'elonix' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Send 410 Gone Status Header', 'elonix' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="tv_404_send_410_header" value="yes" <?php checked( $send_410, 'yes' ); ?> />
										<?php esc_html_e( 'Check to send a 410 Gone status code instead of a 404 status (useful for permanently removed content).', 'elonix' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Enable 404 Analytics Logging', 'elonix' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="tv_404_enable_logging" value="yes" <?php checked( $enable_logging, 'yes' ); ?> />
										<?php esc_html_e( 'Log 404 hits in the database to build reports on broken links and traffic anomalies.', 'elonix' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Display Options', 'elonix' ); ?></th>
								<td>
									<fieldset>
										<legend class="screen-reader-text"><span><?php esc_html_e( 'Display Options', 'elonix' ); ?></span></legend>
										<label for="tv_404_show_header" style="display: block; margin-bottom: 8px;">
											<input type="checkbox" id="tv_404_show_header" name="tv_404_show_header" value="yes" <?php checked( $show_header, 'yes' ); ?> />
											<?php esc_html_e( 'Show Header on the custom 404 page.', 'elonix' ); ?>
										</label>
										<label for="tv_404_show_footer" style="display: block;">
											<input type="checkbox" id="tv_404_show_footer" name="tv_404_show_footer" value="yes" <?php checked( $show_footer, 'yes' ); ?> />
											<?php esc_html_e( 'Show Footer on the custom 404 page.', 'elonix' ); ?>
										</label>
									</fieldset>
								</td>
							</tr>
						</table>

					<!-- ADVANCED TAB PANEL -->
					<?php elseif ( 'advanced' === $active_tab ) : ?>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Force 404 After Page Load', 'elonix' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="tv_404_force_after_load" value="yes" <?php checked( $force_404, 'yes' ); ?> />
										<?php esc_html_e( 'Enforce 404 status matching programmatically for target diagnostic parameters (e.g. testing URL containing /force-404).', 'elonix' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Disable URL Redirect Guessing', 'elonix' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="tv_404_disable_url_guessing" value="yes" <?php checked( $disable_guessing, 'yes' ); ?> />
										<?php esc_html_e( 'Prevent WordPress from guessing URLs (redirect_canonical) to ensure absolute 404 template responses.', 'elonix' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="tv_404_custom_status_code"><?php esc_html_e( 'Custom Status Code', 'elonix' ); ?></label></th>
								<td>
									<input type="number" id="tv_404_custom_status_code" name="tv_404_custom_status_code" class="small-text" value="<?php echo esc_attr( $custom_status ); ?>" min="400" max="499" />
									<p class="description"><?php esc_html_e( 'Optionally override default HTTP response status header. Default: 404.', 'elonix' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Excluded User Roles', 'elonix' ); ?></th>
								<td>
									<?php
									global $wp_roles;
									foreach ( $wp_roles->roles as $role_slug => $role_details ) :
										?>
										<label style="margin-right: 15px;">
											<input type="checkbox" name="tv_404_excluded_user_roles[]" value="<?php echo esc_attr( $role_slug ); ?>" <?php checked( in_array( $role_slug, $excluded_roles, true ) ); ?> />
											<?php echo esc_html( $role_details['name'] ); ?>
										</label>
									<?php endforeach; ?>
									<p class="description"><?php esc_html_e( 'Selected user roles will bypass custom 404 templates and render standard default theme errors.', 'elonix' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Excluded Post Types', 'elonix' ); ?></th>
								<td>
									<?php
									$post_types = get_post_types( array( 'public' => true ), 'objects' );
									foreach ( $post_types as $pt ) :
										?>
										<label style="margin-right: 15px;">
											<input type="checkbox" name="tv_404_excluded_post_types[]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, $excluded_post_types, true ) ); ?> />
											<?php echo esc_html( $pt->label ); ?>
										</label>
									<?php endforeach; ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="tv_404_excluded_urls"><?php esc_html_e( 'Excluded URLs (One per line)', 'elonix' ); ?></label></th>
								<td>
									<textarea id="tv_404_excluded_urls" name="tv_404_excluded_urls" class="large-text" rows="4" placeholder="e.g. /wp-admin/&#10;e.g. /wp-content/"><?php echo esc_textarea( $excluded_urls ); ?></textarea>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="tv_404_excluded_query_parameters"><?php esc_html_e( 'Excluded Query Parameters', 'elonix' ); ?></label></th>
								<td>
									<input type="text" id="tv_404_excluded_query_parameters" name="tv_404_excluded_query_parameters" class="regular-text" value="<?php echo esc_attr( $excluded_queries ); ?>" placeholder="e.g. s, preview, action" />
									<p class="description"><?php esc_html_e( 'Bypass custom 404 layout if these parameters exist in the query string (comma-separated list).', 'elonix' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="tv_404_custom_redirect_rules"><?php esc_html_e( 'Custom Redirect Rules (One per line)', 'elonix' ); ?></label></th>
								<td>
									<textarea id="tv_404_custom_redirect_rules" name="tv_404_custom_redirect_rules" class="large-text" rows="4" placeholder="e.g. ^/old-service/(.*)$ => /services/&#10;e.g. /obsolete-link => /"><?php echo esc_textarea( $redirect_rules ); ?></textarea>
									<p class="description"><?php esc_html_e( 'Define rule mapping inside format: pattern => target. Supports regex wildcards.', 'elonix' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="tv_404_custom_header_code"><?php esc_html_e( 'Custom Header Code (Script Blocks)', 'elonix' ); ?></label></th>
								<td>
									<textarea id="tv_404_custom_header_code" name="tv_404_custom_header_code" class="large-text" rows="4"><?php echo esc_textarea( $header_code ); ?></textarea>
									<p class="description"><?php esc_html_e( 'Code enqueued inside the head area of custom 404 templates. Enclose scripts in tags.', 'elonix' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="tv_404_custom_footer_code"><?php esc_html_e( 'Custom Footer Code (Script Blocks)', 'elonix' ); ?></label></th>
								<td>
									<textarea id="tv_404_custom_footer_code" name="tv_404_custom_footer_code" class="large-text" rows="4"><?php echo esc_textarea( $footer_code ); ?></textarea>
								</td>
							</tr>
						</table>

					<!-- SEO TAB PANEL -->
					<?php elseif ( 'seo' === $active_tab ) : ?>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Send noindex Instruction', 'elonix' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="tv_404_seo_noindex" value="yes" <?php checked( $seo_noindex, 'yes' ); ?> />
										<?php esc_html_e( 'Instruct search crawlers not to index the 404 pages.', 'elonix' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Send nofollow Instruction', 'elonix' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="tv_404_seo_nofollow" value="yes" <?php checked( $seo_nofollow, 'yes' ); ?> />
										<?php esc_html_e( 'Instruct search crawlers not to follow outbound links on the 404 template layout.', 'elonix' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Block WordPress Guessing Redirects', 'elonix' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="tv_404_seo_disable_redirect_guessing" value="yes" <?php checked( $seo_disable_guess, 'yes' ); ?> />
										<?php esc_html_e( 'Disable canonical redirection guessing to protect index visibility from soft 404 errors.', 'elonix' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'SEO 410 Gone Status Override', 'elonix' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="tv_404_seo_410_header" value="yes" <?php checked( $seo_410, 'yes' ); ?> />
										<?php esc_html_e( 'Enable to signal permanently gone index endpoints directly to crawlers.', 'elonix' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="tv_404_seo_canonical_control"><?php esc_html_e( 'Canonical URL Tag Control', 'elonix' ); ?></label></th>
								<td>
									<input type="url" id="tv_404_seo_canonical_control" name="tv_404_seo_canonical_control" class="regular-text" value="<?php echo esc_url( $seo_canonical ); ?>" placeholder="e.g. https://yoursite.com/404" />
									<p class="description"><?php esc_html_e( 'Set an explicit canonical meta URL for the error page layout.', 'elonix' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="tv_404_seo_robots_control"><?php esc_html_e( 'Custom Robots Meta Tag string', 'elonix' ); ?></label></th>
								<td>
									<input type="text" id="tv_404_seo_robots_control" name="tv_404_seo_robots_control" class="regular-text" value="<?php echo esc_attr( $seo_robots ); ?>" placeholder="e.g. noarchive, nosnippet" />
								</td>
							</tr>
						</table>

					<!-- INTEGRATIONS TAB PANEL -->
					<?php elseif ( 'integrations' === $active_tab ) : ?>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Elementor Page Builder Compatibility', 'elonix' ); ?></th>
								<td>
									<p style="color: #10b981; font-weight: 600; display: flex; align-items: center;">
										<span class="dashicons dashicons-yes-alt" style="margin-right: 5px;"></span>
										<?php esc_html_e( 'Fully Active & Supported', 'elonix' ); ?>
									</p>
									<p class="description"><?php esc_html_e( 'Integrates directly with Elementor\'s CSS loading managers, widget render routines, and dynamic query tags.', 'elonix' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Elonix Header & Footer Builder Integration', 'elonix' ); ?></th>
								<td>
									<p style="color: #10b981; font-weight: 600; display: flex; align-items: center;">
										<span class="dashicons dashicons-yes-alt" style="margin-right: 5px;"></span>
										<?php esc_html_e( 'Active & Linked', 'elonix' ); ?>
									</p>
									<p class="description"><?php esc_html_e( 'Automatically fetches and renders the site\'s active Elementor global header/footer templates on custom 404 page routes.', 'elonix' ); ?></p>
								</td>
							</tr>
						</table>

					<!-- ANALYTICS TAB PANEL -->
					<?php elseif ( 'analytics' === $active_tab ) : ?>
						<style>
						/* Elonix – Toolkit for Elementor 404 Analytics Dashboard Styling Overrides */
						.elonix-admin-wrap .tv-reports-grid {
							display: grid !important;
							grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)) !important;
							gap: 20px;
						}
						.elonix-admin-wrap .report-box {
							border: 1px solid #ccd0d4;
							border-radius: 4px;
							padding: 16px;
							background: #fafafa;
							box-sizing: border-box;
							min-width: 0; /* Prevents grid layout stretching */
						}
						.elonix-admin-wrap .tv-table-container {
							width: 100%;
							overflow-x: auto;
							-webkit-overflow-scrolling: touch;
							margin-top: 10px;
						}
						.elonix-admin-wrap .tv-table-container table {
							width: 100%;
							table-layout: fixed;
							border-collapse: collapse;
							min-width: 500px; /* Ensures minimum column width on mobile */
						}
						.elonix-admin-wrap .tv-table-container table td,
						.elonix-admin-wrap .tv-table-container table th,
						.elonix-admin-wrap .tv-recent-logs-container table td,
						.elonix-admin-wrap .tv-recent-logs-container table th {
							vertical-align: middle;
							padding: 10px 8px;
							word-break: break-all;
							word-wrap: break-word;
						}
						.elonix-admin-wrap .tv-table-container td code,
						.elonix-admin-wrap .tv-table-container td a,
						.elonix-admin-wrap .tv-table-container td span.description,
						.elonix-admin-wrap .tv-recent-logs-container td code,
						.elonix-admin-wrap .tv-recent-logs-container td a,
						.elonix-admin-wrap .tv-recent-logs-container td span.description {
							word-break: break-all;
							word-wrap: break-word;
							white-space: normal;
						}
						/* Specific Table Column Width Constraints */
						.tv-table-top-urls th.col-url { width: 55%; }
						.tv-table-top-urls th.col-hits { width: 15%; }
						.tv-table-top-urls th.col-last-seen { width: 30%; }

						.tv-table-broken-links th.col-dest { width: 40%; }
						.tv-table-broken-links th.col-src { width: 45%; }
						.tv-table-broken-links th.col-hits { width: 15%; }

						.tv-table-referrers th.col-ref { width: 85%; }
						.tv-table-referrers th.col-hits { width: 15%; }

						.tv-table-keywords th.col-term { width: 85%; }
						.tv-table-keywords th.col-hits { width: 15%; }

						/* Recent Logs Scrollable and Sticky Table Headers */
						.elonix-admin-wrap .tv-recent-logs-container {
							max-height: 400px;
							overflow-y: auto;
							overflow-x: auto;
							position: relative;
							border: 1px solid #ccd0d4;
							border-radius: 4px;
							margin-top: 10px;
						}
						.elonix-admin-wrap .tv-recent-logs-container table {
							width: 100%;
							table-layout: fixed;
							border-collapse: collapse;
							border: none;
							min-width: 900px; /* Crucial for multi-column logs table to prevent squeezing */
						}
						.elonix-admin-wrap .tv-recent-logs-container thead th {
							position: -webkit-sticky;
							position: sticky;
							top: 0;
							background: #f0f0f0;
							z-index: 5;
							box-shadow: inset 0 -1px 0 #ccd0d4;
							padding: 10px 8px;
						}
						.tv-table-recent th.col-time { width: 18%; }
						.tv-table-recent th.col-url { width: 28%; }
						.tv-table-recent th.col-ref { width: 28%; }
						.tv-table-recent th.col-ua { width: 20%; }
						.tv-table-recent th.col-hits { width: 6%; }

						@media screen and (max-width: 600px) {
							.elonix-admin-wrap .tv-analytics-panel-header {
								flex-direction: column;
								align-items: flex-start !important;
								gap: 12px;
							}
							.elonix-admin-wrap .tv-analytics-panel-header div {
								width: 100%;
								display: flex;
								gap: 8px;
							}
							.elonix-admin-wrap .tv-analytics-panel-header div a {
								flex: 1;
								text-align: center;
							}
						}
						</style>
						<div class="tv-analytics-panel-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccd0d4; padding-bottom: 15px; margin-bottom: 20px;">
							<h3 style="margin: 0; font-size: 16px; font-weight: 600;"><?php esc_html_e( '404 Logging Statistics & Broken Link Tracking', 'elonix' ); ?></h3>
							<div>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=elonix-404&action=export_csv' ), 'tv_404_export_csv_nonce', 'nonce' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Export CSV Logs', 'elonix' ); ?></a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=elonix-404&action=clear_logs' ), 'tv_404_clear_logs_nonce', 'nonce' ) ); ?>" class="button button-secondary tv-action-confirm" data-confirm="<?php esc_attr_e( 'Are you sure you want to clear all analytics logs?', 'elonix' ); ?>" style="color: #d63638; border-color: #d63638;"><?php esc_html_e( 'Clear Logs', 'elonix' ); ?></a>
							</div>
						</div>

						<div class="tv-reports-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
							
							<!-- Top 404 URLs -->
							<div class="report-box" style="border: 1px solid #ccd0d4; border-radius: 4px; padding: 16px; background: #fafafa;">
								<h4 style="margin-top: 0; margin-bottom: 12px; font-weight: 600; display: flex; align-items: center;">
									<span class="dashicons dashicons-chart-bar" style="margin-right: 5px; color: #3b82f6;"></span>
									<?php esc_html_e( 'Top 404 Error URLs', 'elonix' ); ?>
								</h4>
								<div class="tv-table-container">
									<table class="wp-list-table widefat striped tv-table-top-urls" style="box-shadow: none; border: none;">
										<thead>
											<tr>
												<th class="col-url"><?php esc_html_e( 'URL Endpoint', 'elonix' ); ?></th>
												<th class="col-hits"><?php esc_html_e( 'Hits', 'elonix' ); ?></th>
												<th class="col-last-seen"><?php esc_html_e( 'Last Seen', 'elonix' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$top_urls = $this->analytics->get_top_urls( 5 );
											if ( ! empty( $top_urls ) ) :
												foreach ( $top_urls as $log ) :
													?>
													<tr>
														<td><code><?php echo esc_html( $log->url ); ?></code></td>
														<td><strong><?php echo esc_html( $log->total_hits ); ?></strong></td>
														<td><span class="description"><?php echo esc_html( $log->last_seen ); ?></span></td>
													</tr>
													<?php
												endforeach;
											else :
												?>
												<tr><td colspan="3"><?php esc_html_e( 'No log data logged yet.', 'elonix' ); ?></td></tr>
											<?php endif; ?>
										</tbody>
									</table>
								</div>
							</div>

							<!-- Broken Link Reports (Internal Referrers) -->
							<div class="report-box" style="border: 1px solid #ccd0d4; border-radius: 4px; padding: 16px; background: #fafafa;">
								<h4 style="margin-top: 0; margin-bottom: 12px; font-weight: 600; display: flex; align-items: center;">
									<span class="dashicons dashicons-admin-links" style="margin-right: 5px; color: #ef4444;"></span>
									<?php esc_html_e( 'Broken Links Report (Internal Referrers)', 'elonix' ); ?>
								</h4>
								<div class="tv-table-container">
									<table class="wp-list-table widefat striped tv-table-broken-links" style="box-shadow: none; border: none;">
										<thead>
											<tr>
												<th class="col-dest"><?php esc_html_e( 'Destination Link', 'elonix' ); ?></th>
												<th class="col-src"><?php esc_html_e( 'Internal Source Page', 'elonix' ); ?></th>
												<th class="col-hits"><?php esc_html_e( 'Hits', 'elonix' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$broken_links = $this->analytics->get_broken_links_report( 5 );
											if ( ! empty( $broken_links ) ) :
												foreach ( $broken_links as $log ) :
													?>
													<tr>
														<td><code><?php echo esc_html( $log->url ); ?></code></td>
														<td><a href="<?php echo esc_url( $log->referrer ); ?>" target="_blank"><?php echo esc_html( preg_replace( '/^https?:\/\/[^\/]+/i', '', $log->referrer ) ); ?></a></td>
														<td><strong><?php echo esc_html( $log->total_hits ); ?></strong></td>
													</tr>
													<?php
												endforeach;
											else :
												?>
												<tr><td colspan="3"><?php esc_html_e( 'No internal broken links detected. Great job!', 'elonix' ); ?></td></tr>
											<?php endif; ?>
										</tbody>
									</table>
								</div>
							</div>

							<!-- Referrer Sources -->
							<div class="report-box" style="border: 1px solid #ccd0d4; border-radius: 4px; padding: 16px; background: #fafafa;">
								<h4 style="margin-top: 0; margin-bottom: 12px; font-weight: 600; display: flex; align-items: center;">
									<span class="dashicons dashicons-location-alt" style="margin-right: 5px; color: #10b981;"></span>
									<?php esc_html_e( 'Top Referrer Sources', 'elonix' ); ?>
								</h4>
								<div class="tv-table-container">
									<table class="wp-list-table widefat striped tv-table-referrers" style="box-shadow: none; border: none;">
										<thead>
											<tr>
												<th class="col-ref"><?php esc_html_e( 'Referrer Domain / URL', 'elonix' ); ?></th>
												<th class="col-hits"><?php esc_html_e( 'Hits Count', 'elonix' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$referrers = $this->analytics->get_referrer_sources( 5 );
											if ( ! empty( $referrers ) ) :
												foreach ( $referrers as $log ) :
													?>
													<tr>
														<td><a href="<?php echo esc_url( $log->referrer ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $log->referrer ); ?></a></td>
														<td><strong><?php echo esc_html( $log->total_hits ); ?></strong></td>
													</tr>
													<?php
												endforeach;
											else :
												?>
												<tr><td colspan="2"><?php esc_html_e( 'No external referrer sources logged.', 'elonix' ); ?></td></tr>
											<?php endif; ?>
										</tbody>
									</table>
								</div>
							</div>

							<!-- Search Terms -->
							<div class="report-box" style="border: 1px solid #ccd0d4; border-radius: 4px; padding: 16px; background: #fafafa;">
								<h4 style="margin-top: 0; margin-bottom: 12px; font-weight: 600; display: flex; align-items: center;">
									<span class="dashicons dashicons-search" style="margin-right: 5px; color: #8b5cf6;"></span>
									<?php esc_html_e( 'Search Keywords (Deduced)', 'elonix' ); ?>
								</h4>
								<div class="tv-table-container">
									<table class="wp-list-table widefat striped tv-table-keywords" style="box-shadow: none; border: none;">
										<thead>
											<tr>
												<th class="col-term"><?php esc_html_e( 'Search Term', 'elonix' ); ?></th>
												<th class="col-hits"><?php esc_html_e( 'Hits Count', 'elonix' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$search_terms = $this->analytics->get_search_terms( 5 );
											if ( ! empty( $search_terms ) ) :
												foreach ( $search_terms as $log ) :
													?>
													<tr>
														<td><code><?php echo esc_html( $log->keyword ); ?></code></td>
														<td><strong><?php echo esc_html( $log->count ); ?></strong></td>
													</tr>
													<?php
												endforeach;
											else :
												?>
												<tr><td colspan="2"><?php esc_html_e( 'No search parameters found in referrers query strings.', 'elonix' ); ?></td></tr>
											<?php endif; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>

						<!-- Recent 404 Errors Table -->
						<div class="report-box" style="border: 1px solid #ccd0d4; border-radius: 4px; padding: 16px; background: #fafafa; margin-top: 20px;">
							<h4 style="margin-top: 0; margin-bottom: 12px; font-weight: 600; display: flex; align-items: center;">
								<span class="dashicons dashicons-list-view" style="margin-right: 5px; color: #6b7280;"></span>
								<?php esc_html_e( 'Recent 404 Errors Log (Max 100)', 'elonix' ); ?>
							</h4>
							<div class="tv-recent-logs-container">
								<table class="wp-list-table widefat striped tv-table-recent" style="box-shadow: none; border: none;">
									<thead>
										<tr>
											<th class="col-time"><?php esc_html_e( 'Timestamp', 'elonix' ); ?></th>
											<th class="col-url"><?php esc_html_e( 'Requested URL', 'elonix' ); ?></th>
											<th class="col-ref"><?php esc_html_e( 'Referrer', 'elonix' ); ?></th>
											<th class="col-ua"><?php esc_html_e( 'User Agent', 'elonix' ); ?></th>
											<th class="col-hits"><?php esc_html_e( 'Hits', 'elonix' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$recent_logs = $this->analytics->get_recent_logs( 100 );
										if ( ! empty( $recent_logs ) ) :
											foreach ( $recent_logs as $log ) :
												?>
												<tr>
													<td><?php echo esc_html( $log->updated_at ); ?></td>
													<td><code><?php echo esc_html( $log->url ); ?></code></td>
													<td>
														<?php if ( ! empty( $log->referrer ) ) : ?>
															<a href="<?php echo esc_url( $log->referrer ); ?>" target="_blank" rel="noopener" style="word-break: break-all;"><?php echo esc_html( $log->referrer ); ?></a>
														<?php else : ?>
															<span class="description"><?php esc_html_e( 'Direct Visit', 'elonix' ); ?></span>
														<?php endif; ?>
													</td>
													<td><span class="description" style="font-size: 11px;"><?php echo esc_html( $log->user_agent ); ?></span></td>
													<td><strong><?php echo esc_html( $log->hits ); ?></strong></td>
												</tr>
												<?php
											endforeach;
										else :
											?>
											<tr><td colspan="5"><?php esc_html_e( 'No logs present in the database table.', 'elonix' ); ?></td></tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php endif; ?>

				</div>

				<?php if ( 'analytics' !== $active_tab ) : ?>
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'elonix' ); ?>" />
					</p>
				<?php endif; ?>
			</form>
		</div>
		<?php
	}
}
