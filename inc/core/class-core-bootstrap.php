<?php
/**
 * Elonix Core Bootstrap
 * 
 * Initializes foundational core services for the Elonix Framework.
 * Ensures strict load order and eliminates legacy autoloader map dependencies.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Core_Bootstrap {

	/**
	 * Main initialization entry point.
	 */
	public static function init() {
		self::load_dependencies();
	}

	/**
	 * Explicitly load all strictly-required core architecture files.
	 * Loaded BEFORE Elementor registers widgets.
	 */
	private static function load_dependencies() {
		$core_dir = ELONIX_ACC_PATH . 'inc/core/';

		// Load Style Manager Core Architecture
		require_once $core_dir . 'style-manager/class-dynamic-style-conditions.php';
		require_once $core_dir . 'style-manager/class-style-manager.php';

		// Future core modules (e.g. Extension Injector, Global Cache) can be required here.
	}
}

// Bootstrap immediately when loaded
Elonix_Core_Bootstrap::init();
