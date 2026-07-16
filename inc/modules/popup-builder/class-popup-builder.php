<?php
/**
 * Elonix Popup Builder Module Orchestrator
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Popup_Builder {

	/**
	 * Class instance holder (Singleton).
	 *
	 * @var Elonix_Toolkit_Popup_Builder|null
	 */
	private static $_instance = null;

	/**
	 * CPT Register instance.
	 *
	 * @var Elonix_Toolkit_Popup_CPT
	 */
	public $cpt;

	/**
	 * Renderer instance.
	 *
	 * @var Elonix_Toolkit_Popup_Renderer
	 */
	public $renderer;

	/**
	 * Admin settings and metabox instance.
	 *
	 * @var Elonix_Toolkit_Popup_Admin
	 */
	public $admin;

	/**
	 * AJAX endpoints handler.
	 *
	 * @var Elonix_Toolkit_Popup_AJAX
	 */
	public $ajax;

	/**
	 * Get class instance.
	 *
	 * @return Elonix_Toolkit_Popup_Builder Instance.
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
	 * Initialize module elements.
	 */
	public function init() {
		// Verify Elementor is loaded before bootstrapping
		if ( ! did_action( 'elementor/loaded' ) && ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		// Require Sub-modules
		require_once ELONIX_ACC_PATH . 'inc/modules/popup-builder/class-popup-cpt.php';
		require_once ELONIX_ACC_PATH . 'inc/modules/popup-builder/class-popup-renderer.php';
		require_once ELONIX_ACC_PATH . 'inc/modules/popup-builder/class-popup-ajax.php';

		$this->cpt      = new Elonix_Toolkit_Popup_CPT();
		$this->renderer = new Elonix_Toolkit_Popup_Renderer();
		$this->ajax     = new Elonix_Toolkit_Popup_AJAX();

		if ( is_admin() ) {
			require_once ELONIX_ACC_PATH . 'inc/modules/popup-builder/class-popup-admin.php';
			$this->admin = new Elonix_Toolkit_Popup_Admin();
		}
	}
}
