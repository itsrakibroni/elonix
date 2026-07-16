<?php
/**
 * Elonix Archive Builder Module Orchestrator
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Archive_Builder {

	/**
	 * Class instance holder (Singleton).
	 *
	 * @var Elonix_Toolkit_Archive_Builder|null
	 */
	private static $_instance = null;

	/**
	 * CPT Configurer.
	 *
	 * @var Elonix_Toolkit_Archive_CPT
	 */
	public $cpt;

	/**
	 * Frontend Renderer.
	 *
	 * @var Elonix_Toolkit_Archive_Renderer
	 */
	public $renderer;

	/**
	 * Admin settings & duplicate handler.
	 *
	 * @var Elonix_Toolkit_Archive_Admin
	 */
	public $admin;

	/**
	 * Preview & mockup loops generator.
	 *
	 * @var Elonix_Toolkit_Archive_Preview
	 */
	public $preview;

	/**
	 * Get class instance.
	 *
	 * @return Elonix_Toolkit_Archive_Builder Instance.
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
		add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
	}

	/**
	 * Bootstrap and require sub-modules.
	 */
	public function init() {
		// Exit early if Elementor is not present
		if ( ! did_action( 'elementor/loaded' ) && ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		// Require Sub-modules
		require_once ELONIX_ACC_PATH . 'inc/modules/archive-builder/class-archive-cpt.php';
		require_once ELONIX_ACC_PATH . 'inc/modules/archive-builder/class-archive-renderer.php';
		require_once ELONIX_ACC_PATH . 'inc/modules/archive-builder/class-archive-preview.php';

		$this->cpt      = new Elonix_Toolkit_Archive_CPT();
		$this->renderer = new Elonix_Toolkit_Archive_Renderer();
		$this->preview  = new Elonix_Toolkit_Archive_Preview();

		if ( is_admin() ) {
			require_once ELONIX_ACC_PATH . 'inc/modules/archive-builder/class-archive-admin.php';
			$this->admin = new Elonix_Toolkit_Archive_Admin();
		}
	}
}
