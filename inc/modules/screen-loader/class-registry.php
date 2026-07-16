<?php
namespace Elonix_Toolkit\Modules\Screen_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Registry {
	/**
	 * @var array
	 */
	private $engines = array();

	/**
	 * @var Registry|null
	 */
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->register_default_engines();
		/**
		 * Allows third-party developers to register custom loader engines.
		 */
		do_action( 'elonix/screen_loader/register_engines', $this );
	}

	private function register_default_engines() {
		$this->register( 'pure-css', 'Elonix_Toolkit\Modules\Screen_Loader\Engines\Pure_CSS_Engine' );
		$this->register( 'svg', 'Elonix_Toolkit\Modules\Screen_Loader\Engines\SVG_Engine' );
		$this->register( 'logo', 'Elonix_Toolkit\Modules\Screen_Loader\Engines\Hybrid_Engine' );
		$this->register( 'image', 'Elonix_Toolkit\Modules\Screen_Loader\Engines\Hybrid_Engine' );
	}

	/**
	 * Register an engine.
	 *
	 * @param string $id Engine ID.
	 * @param string $class_name Class name (must implement Loader_Engine_Interface).
	 */
	public function register( $id, $class_name ) {
		$this->engines[ $id ] = $class_name;
	}

	/**
	 * Get an instance of the active engine.
	 * Lazy loads the class.
	 *
	 * @param string $id Engine ID.
	 * @return Interfaces\Loader_Engine_Interface|false
	 */
	public function get_engine( $id ) {
		if ( ! isset( $this->engines[ $id ] ) ) {
			return false;
		}

		$class_name = $this->engines[ $id ];

		if ( class_exists( $class_name ) ) {
			$engine = new $class_name( $id );
			if ( $engine instanceof Interfaces\Loader_Engine_Interface ) {
				return $engine;
			}
		}

		return false;
	}

	/**
	 * Get all registered engine IDs.
	 *
	 * @return array
	 */
	public function get_registered_engines() {
		return array_keys( $this->engines );
	}
}
