<?php
/**
 * Elonix – Toolkit for Elementor Import / Export Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Import_Export {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'handle_export_trigger' ) );
		add_action( 'admin_init', array( $this, 'handle_import_submission' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Enqueue stylesheet conditionally.
	 *
	 * @param string $hook Screen hook.
	 */
	public function enqueue_styles( $hook ) {
		if ( 'elonix_page_elonix-settings' !== $hook ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
		if ( 'import_export' !== $tab ) {
			return;
		}

		wp_enqueue_style(
			'elonix-import-export-css',
			ELONIX_ACC_URL . 'assets/admin/css/import-export.css',
			array(),
			ELONIX_VERSION
		);
	}

	/**
	 * Intercept GET request to export settings as JSON file.
	 */
	public function handle_export_trigger() {
		if ( ! isset( $_GET['action'] ) || 'elonix_export_settings' !== $_GET['action'] ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'elonix_export_settings' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'elonix' ), 403 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'elonix' ), 403 );
		}

		$this->export_settings();
	}

	/**
	 * Intercept POST request to import JSON settings file.
	 */
	public function handle_import_submission() {
		if ( ! isset( $_POST['elonix_import_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['elonix_import_settings_nonce'] ) ), 'elonix_import_settings' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error( 'elonix_import_export', 'denied', esc_html__( 'Permission denied.', 'elonix' ), 'error' );
			return;
		}

		if ( empty( $_FILES['import_file']['tmp_name'] ) ) {
			add_settings_error( 'elonix_import_export', 'no_file', esc_html__( 'Please select a settings JSON file to upload.', 'elonix' ), 'error' );
			return;
		}

		$file_path = sanitize_text_field( wp_unslash( $_FILES['import_file']['tmp_name'] ) );
		$file_name = isset( $_FILES['import_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['import_file']['name'] ) ) : '';

		// Validate file MIME type using WordPress core function
		$allowed_mime_types = apply_filters( 'elonix_import_allowed_mime_types', array( 'json' ) );
		$wp_filetype        = wp_check_filetype_and_ext( $file_path, $file_name );
		if ( empty( $wp_filetype['type'] ) || ! in_array( $wp_filetype['ext'], $allowed_mime_types, true ) ) {
			add_settings_error( 'elonix_import_export', 'invalid_mime', esc_html__( 'Invalid file type detected. Only JSON files are allowed.', 'elonix' ), 'error' );
			return;
		}

		// Validate file extension (additional security layer)
		$ext = pathinfo( $file_name, PATHINFO_EXTENSION );
		if ( 'json' !== strtolower( $ext ) ) {
			add_settings_error( 'elonix_import_export', 'invalid_file_type', esc_html__( 'Invalid file format. Please upload a valid .json file.', 'elonix' ), 'error' );
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$file_content = file_get_contents( $file_path );
		$import_data  = json_decode( $file_content, true );

		// Validate JSON encoding
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			add_settings_error( 'elonix_import_export', 'invalid_json', esc_html__( 'Invalid JSON file. The file content could not be decoded.', 'elonix' ), 'error' );
			return;
		}

		// Validate structural compatibility
		$validation = $this->validate_import_file( $import_data );
		if ( is_wp_error( $validation ) ) {
			add_settings_error( 'elonix_import_export', 'validation_failed', $validation->get_error_message(), 'error' );
			return;
		}

		// Perform restoration
		$this->import_settings( $import_data );
		add_settings_error( 'elonix_import_export', 'import_success', esc_html__( 'Settings, widgets, and modules imported successfully.', 'elonix' ), 'success' );
	}

	/**
	 * Generate settings database array payload.
	 *
	 * @return array System settings payload.
	 */
	public function generate_export_data() {
		$settings = get_option( 'elonix_settings', array() );
		$widgets  = get_option( 'elonix_widgets', array() );
		$modules  = get_option( 'elonix_modules', array() );

		return array(
			'plugin_version' => ELONIX_VERSION,
			'export_date'    => gmdate( 'Y-m-d' ),
			'settings'       => $settings,
			'widgets'        => $widgets,
			'modules'        => $modules,
		);
	}

	/**
	 * Send download headers and output settings JSON.
	 */
	public function export_settings() {
		// Prevent headers already sent errors
		if ( headers_sent( $filename, $linenum ) ) {
			wp_die( esc_html__( 'Unable to export: headers already sent.', 'elonix' ), 500 );
		}

		$data     = $this->generate_export_data();
		$filename = 'elonix-settings-' . gmdate( 'Y-m-d' ) . '.json';

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Pragma: public' );

		echo wp_json_encode( $data, JSON_PRETTY_PRINT );
		exit;
	}

	/**
	 * Validate import file structure and version.
	 *
	 * @param array $data JSON data array.
	 * @return bool|WP_Error True if valid, WP_Error otherwise.
	 */
	public function validate_import_file( $data ) {
		if ( ! is_array( $data ) || ! isset( $data['plugin_version'] ) ) {
			return new WP_Error( 'structure_invalid', esc_html__( 'Invalid file structure. The uploaded file is not a valid Elonix settings export.', 'elonix' ) );
		}

		// Check version mismatch (prevent backwards compatibility issue if importing from a newer version)
		if ( version_compare( $data['plugin_version'], ELONIX_VERSION, '>' ) ) {
			/* translators: %s: string */
			return new WP_Error( 'version_mismatch', sprintf( esc_html__( 'Version mismatch. The export file comes from a newer version (v%s) of the plugin. Please update the plugin before importing.', 'elonix' ), $data['plugin_version'] ) );
		}

		if ( ! isset( $data['settings'] ) || ! isset( $data['widgets'] ) || ! isset( $data['modules'] ) ) {
			return new WP_Error( 'missing_keys', esc_html__( 'Missing required settings configurations in the upload package.', 'elonix' ) );
		}

		return true;
	}

	/**
	 * Import configurations into database options.
	 *
	 * @param array $data JSON payload.
	 */
	public function import_settings( $data ) {
		// Import settings
		require_once ELONIX_ACC_PATH . 'inc/framework/class-settings-framework.php';
		$sanitized_settings = Elonix_Toolkit_Settings_Framework::sanitize_settings( $data['settings'] );
		update_option( 'elonix_settings', $sanitized_settings );

		// Import widgets
		require_once ELONIX_ACC_PATH . 'inc/managers/class-widget-manager.php';
		Elonix_Toolkit_Widget_Manager::save_statuses( $data['widgets'] );

		// Import modules
		require_once ELONIX_ACC_PATH . 'inc/managers/class-module-manager.php';
		Elonix_Toolkit_Module_Manager::save_statuses( $data['modules'] );
	}

	/**
	 * Render HTML layout card panels for Import / Export page view.
	 */
	public static function render_page() {
		$export_url = add_query_arg(
			array(
				'action'   => 'elonix_export_settings',
				'_wpnonce' => wp_create_nonce( 'elonix_export_settings' ),
			)
		);

		?>
		<div class="elonix-import-export-grid">
			
			<!-- Export Panel Card -->
			<div class="elonix-ie-card">
				<h2><?php esc_html_e( 'Export Settings', 'elonix' ); ?></h2>
				<p><?php esc_html_e( 'Download all settings, active widgets status, and module configurations as a JSON file. You can restore these configurations on any other site running the Elonix – Toolkit for Elementor plugin.', 'elonix' ); ?></p>
				<div class="card-footer">
					<a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary button-large">
						<span class="dashicons dashicons-download" style="vertical-align: middle; margin-top: -3px; margin-right: 4px;"></span>
						<?php esc_html_e( 'Export Settings', 'elonix' ); ?>
					</a>
				</div>
			</div>

			<!-- Import Panel Card -->
			<div class="elonix-ie-card">
				<h2><?php esc_html_e( 'Import Settings', 'elonix' ); ?></h2>
				<p><?php esc_html_e( 'Upload a configurations JSON file generated by this plugin to restore settings, widgets, and modules status. Warning: This will overwrite your current settings.', 'elonix' ); ?></p>
				
				<form method="post" enctype="multipart/form-data" action="">
					<?php wp_nonce_field( 'elonix_import_settings', 'elonix_import_settings_nonce' ); ?>
					
					<div class="elonix-file-upload-wrapper">
						<div class="elonix-file-input">
							<span class="dashicons dashicons-upload"></span>
							<input type="file" name="import_file" accept=".json" required>
						</div>
					</div>

					<div class="card-footer">
						<button type="submit" name="elonix_import_submit" class="button button-primary button-large">
							<span class="dashicons dashicons-upload" style="vertical-align: middle; margin-top: -3px; margin-right: 4px;"></span>
							<?php esc_html_e( 'Import Settings', 'elonix' ); ?>
						</button>
					</div>
				</form>
			</div>

		</div>
		<?php
	}
}
