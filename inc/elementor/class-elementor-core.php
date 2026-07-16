<?php
/**
 * Elonix – Toolkit for Elementor Elementor Integration Core Loader
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Elementor_Core {

	/**
	 * Class instance holder.
	 *
	 * @var Elonix_Toolkit_Elementor_Core|null
	 */
	private static $_instance = null;

	/**
	 * Get class instance.
	 *
	 * @return Elonix_Toolkit_Elementor_Core Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Hook with high priority to run after Elementor load
		add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
	}

	/**
	 * Initialize the loader checks and hooks.
	 */
	public function init() {
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Requirement checks passed. Register core hooks.
		add_action( 'elementor/init', array( $this, 'init_elementor' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/controls/register', array( $this, 'register_controls' ) );
		if ( class_exists( 'Elonix_Toolkit_Module_Manager' ) && Elonix_Toolkit_Module_Manager::is_module_enabled( 'dynamic_tags' ) ) {
			add_action( 'elementor/dynamic_tags/register', array( $this, 'register_dynamic_tags' ) );
		}

		// Enqueue scripts & styles for editor and frontend
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'enqueue_frontend_assets' ) );

		// Load Elementor Widget Category System
		require_once ELONIX_ACC_PATH . 'inc/elementor/class-widget-category.php';
		Elonix_Toolkit_Widget_Category::instance();
	}

	/**
	 * Callback for elementor/init hook.
	 */
	public function init_elementor() {
		// Initialize the Central Extension Injector
		require_once ELONIX_ACC_PATH . 'inc/elementor/extensions/class-base-extension.php';
		require_once ELONIX_ACC_PATH . 'inc/elementor/extensions/class-extension-injector.php';
		Elonix_Extension_Injector::instance();

		// Load Extensions
		$this->register_extensions();

		if ( class_exists( 'Elonix_Toolkit_Module_Manager' ) && Elonix_Toolkit_Module_Manager::is_module_enabled( 'dynamic_tags' ) ) {
			require_once ELONIX_ACC_PATH . 'inc/elementor/class-dynamic-visibility.php';
			Elonix_Dynamic_Visibility::instance();

			require_once ELONIX_ACC_PATH . 'inc/elementor/class-dynamic-inspector.php';
			Elonix_Dynamic_Inspector::instance();
		}
	}

	/**
	 * Perform WordPress, PHP, and Elementor version/activity validation checks.
	 *
	 * @return bool True if requirements pass, false otherwise.
	 */
	public function check_requirements() {
		global $wp_version;

		// Verify minimum WordPress version (>= 5.9)
		if ( version_compare( $wp_version, '5.9', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_wp_version' ) );
			return false;
		}

		// Verify minimum PHP version (>= 7.4)
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_php_version' ) );
			return false;
		}

		// Verify Elementor is installed
		if ( ! $this->is_elementor_installed() ) {
			add_action( 'admin_notices', array( $this, 'notice_elementor_not_installed' ) );
			return false;
		}

		// Verify Elementor is active
		if ( ! $this->is_elementor_active() ) {
			add_action( 'admin_notices', array( $this, 'notice_elementor_not_active' ) );
			return false;
		}

		// Verify Elementor version meets minimum requirements (>= 3.20)
		if ( version_compare( $this->get_elementor_version(), '3.20', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_elementor_version' ) );
			return false;
		}

		return true;
	}

	/**
	 * Check if Elementor plugin files exist.
	 *
	 * @return bool True if files exist.
	 */
	public function is_elementor_installed() {
		$file_path = WP_PLUGIN_DIR . '/elementor/elementor.php';
		return file_exists( $file_path );
	}

	/**
	 * Check if Elementor has completed loading.
	 *
	 * @return bool True if Elementor class exists or actions fired.
	 */
	public function is_elementor_active() {
		return did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' );
	}

	/**
	 * Retrieve installed Elementor version.
	 *
	 * @return string Elementor version string.
	 */
	public function get_elementor_version() {
		return defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '0.0.0';
	}

	/**
	 * Display warning notice if WordPress version is unsupported.
	 */
	public function notice_wp_version() {
		$message = sprintf(
			/* translators: %s: Required WordPress version */
			esc_html__( 'Elonix – Toolkit for Elementor requires WordPress version %s or greater to run. Please update your WordPress installation.', 'elonix' ),
			'5.9'
		);
		$this->render_admin_notice( $message );
	}

	/**
	 * Display warning notice if PHP version is unsupported.
	 */
	public function notice_php_version() {
		$message = sprintf(
			/* translators: %s: Required PHP version */
			esc_html__( 'Elonix – Toolkit for Elementor requires PHP version %s or greater to run. Please contact your hosting provider to upgrade PHP.', 'elonix' ),
			'7.4'
		);
		$this->render_admin_notice( $message );
	}

	/**
	 * Display notice if Elementor is not installed.
	 */
	public function notice_elementor_not_installed() {
		$message = sprintf(
			/* translators: %s: Elementor plugin text */
			esc_html__( 'Elonix – Toolkit for Elementor requires %s to be installed and active. Please install Elementor first.', 'elonix' ),
			'<strong>' . esc_html__( 'Elementor', 'elonix' ) . '</strong>'
		);
		$this->render_admin_notice( $message );
	}

	/**
	 * Display notice if Elementor is installed but not activated.
	 */
	public function notice_elementor_not_active() {
		$message = sprintf(
			/* translators: %s: Elementor plugin text */
			esc_html__( 'Elonix – Toolkit for Elementor requires %s to be activated. Please activate Elementor.', 'elonix' ),
			'<strong>' . esc_html__( 'Elementor', 'elonix' ) . '</strong>'
		);
		$this->render_admin_notice( $message );
	}

	/**
	 * Display notice if active Elementor version is lower than minimum requirement.
	 */
	public function notice_elementor_version() {
		$message = sprintf(
			/* translators: 1: Required Elementor version, 2: Current Elementor version */
			esc_html__( 'Elonix – Toolkit for Elementor requires Elementor version %1$s or greater. Your current version is %2$s. Please update Elementor.', 'elonix' ),
			'3.20',
			$this->get_elementor_version()
		);
		$this->render_admin_notice( $message );
	}

	/**
	 * Render standard WordPress admin notice HTML.
	 *
	 * @param string $message Notice message.
	 */
	private function render_admin_notice( $message ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		?>
		<div class="notice notice-warning is-dismissible">
			<p><?php echo wp_kses_post( $message ); ?></p>
		</div>
		<?php
	}

	/**
	 * Register custom Elementor widgets.
	 *
	 * @param object $widgets_manager Elementor widgets manager instance.
	 */
	public function register_widgets( $widgets_manager ) {
		// Load the central Widget Loader and delegate registration
		require_once ELONIX_ACC_PATH . 'inc/elementor/class-widget-loader.php';
		$widget_loader = new Elonix_Toolkit_Widget_Loader();
		$widget_loader->register_widgets( $widgets_manager );
	}

	/**
	 * Register custom Elementor dynamic tags.
	 *
	 * @param object $dynamic_tags_manager Elementor dynamic tags manager instance.
	 */
	public function register_dynamic_tags( $dynamic_tags_manager ) {
		// Load the Dynamic Tags Manager and delegate registration
		require_once ELONIX_ACC_PATH . 'inc/elementor/dynamic-tags/class-manager.php';
		$manager = Elonix_Toolkit_Dynamic_Tags_Manager::instance();
		$manager->register_tags( $dynamic_tags_manager );
	}

	/**
	 * Register custom Elementor controls.
	 *
	 * @param object $controls_manager Elementor controls manager instance.
	 */
	public function register_controls( $controls_manager ) {
		// Prepared for future custom Elementor Controls registration
	}

	/**
	 * Register custom Elementor extensions.
	 */
	public function register_extensions() {
		// Register Sticky
		require_once ELONIX_ACC_PATH . 'inc/elementor/extensions/class-sticky-element.php';
		Elonix_Sticky_Extension::instance();

		// Register Glass Blur
		require_once ELONIX_ACC_PATH . 'inc/elementor/extensions/class-glass-blur-extension.php';
		Elonix_Glass_Blur_Extension::instance();
	}

	/**
	 * Enqueue editor-related scripts and styles.
	 */
	public function enqueue_editor_assets() {
		if ( class_exists( 'Elonix_Toolkit_Assets_Manager' ) ) {
			Elonix_Toolkit_Assets_Manager::enqueue_editor_assets();
		}

		$badge_text = defined( 'ELONIX_WIDGET_BADGE' ) ? ELONIX_WIDGET_BADGE : 'ESKIT';
		$custom_css = "
			.elementor-panel .elementor-element .icon {
				position: relative;
			}
			.elonix-widget-icon {
			}
			.elonix-widget-icon:after {
				content: '" . esc_js( $badge_text ) . "';
				position: absolute;
				top: 3px;
				right: 3px;
				color: #556068;
				font-size: 9px;
				border: 1px solid #dedede;
				font-weight: 400;
				padding: 1px 2px;
				line-height: 10px;
				display: inline-block;
				border-radius: 2px;
				font-family: 'Open Sans', Roboto, Helvetica, Arial, sans-serif;
			}
			.elementor-element-wrapper:hover .elonix-widget-icon:after {
				color: #6c5ce7;
				border-color: #6c5ce7;
			}
			.elementor-editor-dark-mode .elonix-widget-icon:after {
				color: #e0e1e3;
				border: 1px solid #dedede45;
			}
			.elementor-editor-dark-mode .elementor-element-wrapper:hover .elonix-widget-icon:after {
				color: #a29bfe;
				border-color: #a29bfe;
			}
		";
		wp_add_inline_style( 'elementor-editor', $custom_css );
	}

	/**
	 * Register/Enqueue frontend assets after Elementor registers its own.
	 */
	public function enqueue_frontend_assets() {
		// Prepared for future Elementor Frontend asset loading
	}
}
