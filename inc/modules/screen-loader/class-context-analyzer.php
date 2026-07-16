<?php
namespace Elonix_Toolkit\Modules\Screen_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Context_Analyzer {
	/**
	 * Determine if the loader should run in the current context.
	 *
	 * @return bool
	 */
	public function should_render() {
		// Do not render in WP Admin
		if ( is_admin() ) {
			return false;
		}

		// Do not render in WP Customizer
		if ( is_customize_preview() ) {
			return false;
		}

		// Do not render during AJAX requests
		if ( wp_doing_ajax() ) {
			return false;
		}

		// Do not render during REST API requests
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		// Do not render during JSON requests
		if ( wp_is_json_request() ) {
			return false;
		}

		// Do not render in Elementor Editor or Preview mode
		if ( class_exists( '\Elementor\Plugin' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				return false;
			}
		}

		/**
		 * Allow third-party plugins/themes to override context.
		 */
		return apply_filters( 'elonix/screen_loader/should_render', true );
	}
}
