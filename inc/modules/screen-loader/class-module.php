<?php
namespace Elonix_Toolkit\Modules\Screen_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module {
	/**
	 * @var Module|null
	 */
	private static $instance = null;

	/**
	 * @var Settings
	 */
	private $settings_provider;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->settings_provider = new Settings();
		if ( is_admin() ) {
			Admin_Page::instance();
		}
		add_action( 'wp', array( $this, 'init' ) );
	}

	public function init() {
		$settings = $this->settings_provider->get_settings();



		if ( empty( $settings['enable'] ) && empty( $settings['screen_loader_enable'] ) && ! apply_filters( 'elonix/screen_loader/force_enable', false ) ) {
			// Module disabled.
			return;
		}

		// 2. Context Analyzer
		$analyzer = new Context_Analyzer();
		if ( ! $analyzer->should_render() ) {
			return;
		}

		// 3. Engine Registry & Lazy Loading
		$registry = Registry::instance();
		$engine_type = ! empty( $settings['engine'] ) ? $settings['engine'] : 'pure-css';
		
		$engine = $registry->get_engine( $engine_type );
		if ( ! $engine ) {
			// Fallback to default if not found
			$engine = $registry->get_engine( 'pure-css' );
		}

		if ( ! $engine ) {
			return; // Safety net
		}

		// 4. Register Assets
		$assets = new Assets( $settings );
		$assets->register_hooks();

		// 5. Register Renderer
		$renderer = new Renderer( $settings, $engine );
		$renderer->register_hooks();
	}
}
