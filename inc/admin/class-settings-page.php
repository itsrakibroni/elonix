<?php
/**
 * Elonix – Toolkit for Elementor Settings Page Renderer
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings_fields' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Enqueue stylesheet.
	 *
	 * @param string $hook Screen hook.
	 */
	public function enqueue_styles( $hook ) {
		if ( 'elonix_page_elonix-settings' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'elonix-settings-page-css',
			ELONIX_ACC_URL . 'assets/admin/css/settings-page.css',
			array(),
			ELONIX_VERSION
		);

		// Enqueue native UI components
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_media();
	}

	/**
	 * Register settings sections and fields via the framework.
	 */
	public function register_settings_fields() {
		// Register sections (tabs)
		Elonix_Toolkit_Settings_Framework::add_section( 'general', esc_html__( 'General', 'elonix' ) );
		Elonix_Toolkit_Settings_Framework::add_section( 'performance', esc_html__( 'Performance', 'elonix' ) );
		Elonix_Toolkit_Settings_Framework::add_section( 'advanced', esc_html__( 'Advanced', 'elonix' ) );
		Elonix_Toolkit_Settings_Framework::add_section( 'debug', esc_html__( 'Debug', 'elonix' ) );
		Elonix_Toolkit_Settings_Framework::add_section( 'uninstall', esc_html__( 'Uninstall', 'elonix' ) );
		Elonix_Toolkit_Settings_Framework::add_section( 'import_export', esc_html__( 'Import / Export', 'elonix' ) );
		Elonix_Toolkit_Settings_Framework::add_section( 'social_links', esc_html__( 'Social Links', 'elonix' ) );

		$socials = array(
			'facebook_url'  => 'Facebook',
			'twitter_url'   => 'X / Twitter',
			'instagram_url' => 'Instagram',
			'linkedin_url'  => 'LinkedIn',
			'youtube_url'   => 'YouTube',
			'pinterest_url' => 'Pinterest',
			'tiktok_url'    => 'TikTok',
			'github_url'    => 'GitHub',
			'behance_url'   => 'Behance',
			'dribbble_url'  => 'Dribbble',
			'whatsapp_url'  => 'WhatsApp',
			'telegram_url'  => 'Telegram',
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'social_links',
			array(
				'id'      => 'elonix_social_target',
				'type'    => 'select',
				'label'   => esc_html__( 'Open Links In', 'elonix' ),
				'default' => '_blank',
				'options' => array(
					'_blank' => esc_html__( 'New Tab', 'elonix' ),
					'_self'  => esc_html__( 'Same Window', 'elonix' ),
				),
			)
		);

		foreach ( $socials as $id => $label ) {
			Elonix_Toolkit_Settings_Framework::add_field(
				'social_links',
				array(
					'id'    => $id,
					'type'  => 'text',
					'label' => esc_html( $label ),
				)
			);
		}

		// General Fields
		Elonix_Toolkit_Settings_Framework::add_field(
			'general',
			array(
				'id'          => 'assets_opt',
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Plugin Assets Optimization', 'elonix' ),
				'description' => esc_html__( 'Optimize JS/CSS files for faster page load times.', 'elonix' ),
				'default'     => '0',
			)
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'general',
			array(
				'id'          => 'admin_notices',
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Admin Notices', 'elonix' ),
				'description' => esc_html__( 'Allow important admin notifications on the dashboard.', 'elonix' ),
				'default'     => '1',
			)
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'general',
			array(
				'id'          => 'usage_tracking',
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Usage Tracking', 'elonix' ),
				'description' => esc_html__( 'Share non-sensitive diagnostic data to help us improve the plugin.', 'elonix' ),
				'default'     => '0',
			)
		);

		// Performance Fields
		Elonix_Toolkit_Settings_Framework::add_field(
			'performance',
			array(
				'id'          => 'assets_conditional',
				'type'        => 'switch',
				'label'       => esc_html__( 'Load Assets Conditionally', 'elonix' ),
				'description' => esc_html__( 'Only load widget scripts and styles on pages where they are used.', 'elonix' ),
				'default'     => '0',
			)
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'performance',
			array(
				'id'          => 'assets_minify',
				'type'        => 'switch',
				'label'       => esc_html__( 'Minify Assets', 'elonix' ),
				'description' => esc_html__( 'Load minified styles and scripts.', 'elonix' ),
				'default'     => '0',
			)
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'advanced',
			array(
				'id'          => 'developer_mode',
				'type'        => 'switch',
				'label'       => esc_html__( 'Developer Mode', 'elonix' ),
				'description' => esc_html__( 'Enable developer tools including: Add to Library, Export Package, Package Validation, and Developer Utilities.', 'elonix' ),
				'default'     => '0',
			)
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'advanced',
			array(
				'id'          => 'safe_mode',
				'type'        => 'switch',
				'label'       => esc_html__( 'Safe Mode', 'elonix' ),
				'description' => esc_html__( 'Disable third-party conflicts in Elementor editor.', 'elonix' ),
				'default'     => '0',
			)
		);

		// Debug Fields
		Elonix_Toolkit_Settings_Framework::add_field(
			'debug',
			array(
				'id'          => 'debug_mode',
				'type'        => 'switch',
				'label'       => esc_html__( 'Debug Mode', 'elonix' ),
				'description' => esc_html__( 'Enable log outputs and script debugging controls.', 'elonix' ),
				'default'     => '0',
			)
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'debug',
			array(
				'id'          => 'dynamic_inspector',
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Dynamic Inspector', 'elonix' ),
				'description' => esc_html__( 'Enable Dynamic Inspector overlay on frontend.', 'elonix' ),
				'default'     => '0',
			)
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'debug',
			array(
				'id'          => 'widget_diagnostics',
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Widget Diagnostics', 'elonix' ),
				'description' => esc_html__( 'Enable Widget Diagnostics.', 'elonix' ),
				'default'     => '0',
			)
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'debug',
			array(
				'id'          => 'render_information',
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Render Information', 'elonix' ),
				'description' => esc_html__( 'Enable Render Information.', 'elonix' ),
				'default'     => '0',
			)
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'debug',
			array(
				'id'          => 'template_information',
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Template Information', 'elonix' ),
				'description' => esc_html__( 'Enable Template Information.', 'elonix' ),
				'default'     => '0',
			)
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'debug',
			array(
				'id'          => 'assignment_debug',
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Assignment Debug', 'elonix' ),
				'description' => esc_html__( 'Enable Assignment Debug.', 'elonix' ),
				'default'     => '0',
			)
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'debug',
			array(
				'id'          => 'performance_overlay',
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Performance Overlay', 'elonix' ),
				'description' => esc_html__( 'Enable Performance Overlay.', 'elonix' ),
				'default'     => '0',
			)
		);

		Elonix_Toolkit_Settings_Framework::add_field(
			'debug',
			array(
				'id'          => 'query_information',
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Query Information', 'elonix' ),
				'description' => esc_html__( 'Enable Query Information.', 'elonix' ),
				'default'     => '0',
			)
		);

		// Uninstall Fields
		Elonix_Toolkit_Settings_Framework::add_field(
			'uninstall',
			array(
				'id'          => 'remove_data_on_uninstall',
				'type'        => 'switch',
				'label'       => esc_html__( 'Remove Data On Uninstall', 'elonix' ),
				'description' => esc_html__( 'Permanently wipe out custom post types, taxonomies, and options on deletion.', 'elonix' ),
				'default'     => '0',
			)
		);

		// Hook framework to Settings API
		Elonix_Toolkit_Settings_Framework::register_settings();
	}

	/**
	 * Render Settings Page tabbed layout.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$sections = Elonix_Toolkit_Settings_Framework::get_sections();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab display, sanitized and whitelist-checked against $sections below.
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';

		if ( ! array_key_exists( $active_tab, $sections ) ) {
			$active_tab = 'general';
		}

		?>
		<div class="wrap elonix-settings-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Elonix – Toolkit for Elementor Settings', 'elonix' ); ?></h1>
			<hr class="wp-header-end">

			<!-- Tabs Navigation -->
			<h2 class="nav-tab-wrapper elonix-settings-tabs">
				<?php
				foreach ( $sections as $id => $title ) :
					$active_class = ( $active_tab === $id ) ? 'nav-tab-active' : '';
					$tab_url      = add_query_arg( 'tab', $id );
					?>
					<a href="<?php echo esc_url( $tab_url ); ?>" class="nav-tab <?php echo esc_attr( $active_class ); ?>">
						<?php echo esc_html( $title ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<?php settings_errors( 'elonix_import_export' ); ?>

			<?php if ( 'import_export' === $active_tab ) : ?>
				<?php Elonix_Toolkit_Import_Export::render_page(); ?>
			<?php else : ?>
				<form method="post" action="options.php">
					<?php
					// Output security fields for Settings API
					settings_fields( 'elonix_settings_group' );
					?>
					<input type="hidden" name="elonix_settings[es_active_tab]" value="<?php echo esc_attr( $active_tab ); ?>" />
					<?php
					echo '<div class="elonix-settings-section-card">';

					// Render the active tab section
					do_settings_sections( 'elonix_settings_' . $active_tab );

					echo '</div>';

					submit_button( esc_html__( 'Save Settings', 'elonix' ) );
					?>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}
}
