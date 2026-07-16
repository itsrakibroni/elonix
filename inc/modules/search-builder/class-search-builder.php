<?php
/**
 * Elonix Search Builder Module Orchestrator
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.Files.FileName -- Search Builder keeps Archive Builder file naming to preserve shared module architecture.
/**
 * Elonix Search Builder Module Orchestrator.
 */
class Elonix_Search_Builder {

	/**
	 * Class instance holder (Singleton).
	 *
	 * @var Elonix_Search_Builder|null
	 */
	private static $instance = null;

	/**
	 * CPT Configurer.
	 *
	 * @var Elonix_Search_CPT
	 */
	public $cpt;

	/**
	 * Frontend Renderer.
	 *
	 * @var Elonix_Search_Renderer
	 */
	public $renderer;

	/**
	 * Admin settings & duplicate handler.
	 *
	 * @var Elonix_Search_Admin
	 */
	public $admin;

	/**
	 * Preview & mockup loops generator.
	 *
	 * @var Elonix_Search_Preview
	 */
	public $preview;

	/**
	 * Get class instance.
	 *
	 * @return Elonix_Search_Builder Instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
	}

	/**
	 * Bootstrap subclasses.
	 */
	public function init() {
		// Exit early if Elementor is not active.
		if ( ! did_action( 'elementor/loaded' ) && ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		$this->cpt      = new Elonix_Search_CPT();
		$this->renderer = new Elonix_Search_Renderer();
		$this->preview  = new Elonix_Search_Preview();

		if ( is_admin() ) {
			$this->admin = new Elonix_Search_Admin();
		}
	}
}
// phpcs:enable WordPress.Files.FileName
