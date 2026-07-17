<?php
/**
 * Elonix Advanced 404 Builder Main Orchestrator
 *
 * Status: PRODUCTION READY | FROZEN | THEMEFOREST READY
 * Last Updated: June 23, 2026
 *
 * Freeze Policy:
 * - NO New Features, Controls, Layouts, Effects, or Responsive Logic.
 * - ALLOWED: Bug Fixes, Security Fixes, WordPress Compatibility Updates, Elementor Compatibility Updates, and Performance Optimizations.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_404_Builder {

	/**
	 * Singleton class instance.
	 *
	 * @var Elonix_Toolkit_404_Builder|null
	 */
	private static $_instance = null;

	/**
	 * Analytics helper instance.
	 *
	 * @var Elonix_Toolkit_404_Analytics
	 */
	public $analytics;

	/**
	 * Router logic handler.
	 *
	 * @var Elonix_Toolkit_404_Router
	 */
	public $router;

	/**
	 * Custom canvas renderer engine.
	 *
	 * @var Elonix_Toolkit_404_Renderer
	 */
	public $renderer;

	/**
	 * Admin settings and metrics panel controller.
	 *
	 * @var Elonix_Toolkit_404_Admin
	 */
	public $admin;

	/**
	 * Get class instance.
	 *
	 * @return Elonix_Toolkit_404_Builder Instance.
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
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ), 25 );
	}

	/**
	 * Initialize module elements.
	 */
	public function init() {
		// Verify Elementor is loaded before bootstrapping
		if ( ! did_action( 'elementor/loaded' ) && ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		// Require sub-modules
		require_once ELONIX_ACC_PATH . 'inc/modules/404-builder/class-404-analytics.php';
		require_once ELONIX_ACC_PATH . 'inc/modules/404-builder/class-404-router.php';
		require_once ELONIX_ACC_PATH . 'inc/modules/404-builder/class-404-renderer.php';

		$this->analytics = new Elonix_Toolkit_404_Analytics();
		$this->router    = new Elonix_Toolkit_404_Router( $this->analytics );
		$this->renderer  = new Elonix_Toolkit_404_Renderer();

		if ( is_admin() ) {
			require_once ELONIX_ACC_PATH . 'inc/modules/404-builder/class-404-admin.php';
			$this->admin = new Elonix_Toolkit_404_Admin( $this->analytics );
		}

		// Ensure custom database table is built
		$this->maybe_install_table();
	}

	/**
	 * Run dynamic log database migrations if schema is not registered.
	 */
	private function maybe_install_table() {
		$db_version = Elonix_Settings::get( 'es_404_db_version' ) ?? '0' ;
		if ( version_compare( $db_version, '1.0.0', '<' ) ) {
			global $wpdb;
			$table_name      = $wpdb->prefix . 'es_404_logs';
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				url varchar(2083) NOT NULL,
				referrer varchar(2083) DEFAULT NULL,
				user_agent varchar(512) DEFAULT NULL,
				ip_hash varchar(32) NOT NULL,
				hits int(11) UNSIGNED NOT NULL DEFAULT 1,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY url (url(255)),
				KEY updated_at (updated_at)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			update_option( 'es_404_db_version', '1.0.0' );
		}
	}
}
