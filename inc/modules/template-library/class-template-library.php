<?php
namespace Elonix_Toolkit\Modules\Template_Library;

use Elonix_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Module {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init();
	}

	private function init() {
		require_once ELONIX_ACC_PATH . 'inc/modules/template-library/class-type-normalizer.php';
		REST::instance();
		Cache::instance();

		if ( is_admin() ) {
			Admin::instance();
		}

		// Inject into Elementor's editor if needed
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ) );
		
		// Load assignment orchestrator
		require_once ELONIX_ACC_PATH . 'inc/modules/template-library/class-template-assignment.php';

		// Load Developer Tools (if developer mode is enabled via setting or constant)
		$is_dev_mode = class_exists( 'Elonix_Settings' ) && Elonix_Settings::is_developer_mode();

		if ( $is_dev_mode ) {
			require_once ELONIX_ACC_PATH . 'inc/modules/template-library/class-developer-tools.php';
			require_once ELONIX_ACC_PATH . 'inc/modules/template-library/class-package-generator.php';
			Developer_Tools::instance();
			Package_Generator::instance();
		}
	}

	public function enqueue_editor_scripts() {
		// Future: load modal UI inside Elementor editor
	}
}
