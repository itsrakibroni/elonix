<?php
/**
 * Elonix Shared Settings Helper
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Settings {

	/**
	 * Helper to check if Developer Mode is enabled.
	 * 
	 * @return bool True if developer mode is enabled.
	 */
	public static function is_developer_mode() {
		if ( defined( 'ELONIX_DEVELOPER_MODE' ) && ELONIX_DEVELOPER_MODE ) {
			return true;
		}
		
		$settings = get_option( 'elonix_settings', array() );
		return isset( $settings['advanced']['developer_mode'] ) && '1' === (string) $settings['advanced']['developer_mode'];
	}

	/**
	 * Helper to check if Debug Mode is enabled.
	 */
	public static function is_debug_mode() {
		$settings = get_option( 'elonix_settings', array() );
		return isset( $settings['debug']['debug_mode'] ) && '1' === (string) $settings['debug']['debug_mode'];
	}

	/**
	 * Helper to check if Dynamic Inspector is enabled.
	 */
	public static function is_dynamic_inspector_enabled() {
		if ( ! self::is_debug_mode() ) {
			return false;
		}
		$settings = get_option( 'elonix_settings', array() );
		return isset( $settings['debug']['dynamic_inspector'] ) && '1' === (string) $settings['debug']['dynamic_inspector'];
	}

	/**
	 * Helper to check if Widget Diagnostics is enabled.
	 */
	public static function is_widget_diagnostics_enabled() {
		if ( ! self::is_debug_mode() ) {
			return false;
		}
		$settings = get_option( 'elonix_settings', array() );
		return isset( $settings['debug']['widget_diagnostics'] ) && '1' === (string) $settings['debug']['widget_diagnostics'];
	}

	/**
	 * Helper to check if Render Information debug is enabled.
	 */
	public static function is_render_debug_enabled() {
		if ( ! self::is_debug_mode() ) {
			return false;
		}
		$settings = get_option( 'elonix_settings', array() );
		return isset( $settings['debug']['render_information'] ) && '1' === (string) $settings['debug']['render_information'];
	}

	/**
	 * Helper to check if Template Information debug is enabled.
	 */
	public static function is_template_debug_enabled() {
		if ( ! self::is_debug_mode() ) {
			return false;
		}
		$settings = get_option( 'elonix_settings', array() );
		return isset( $settings['debug']['template_information'] ) && '1' === (string) $settings['debug']['template_information'];
	}

	/**
	 * Helper to check if Assignment Debug is enabled.
	 */
	public static function is_assignment_debug_enabled() {
		if ( ! self::is_debug_mode() ) {
			return false;
		}
		$settings = get_option( 'elonix_settings', array() );
		return isset( $settings['debug']['assignment_debug'] ) && '1' === (string) $settings['debug']['assignment_debug'];
	}

	/**
	 * Helper to check if Performance Overlay is enabled.
	 */
	public static function is_performance_overlay_enabled() {
		if ( ! self::is_debug_mode() ) {
			return false;
		}
		$settings = get_option( 'elonix_settings', array() );
		return isset( $settings['debug']['performance_overlay'] ) && '1' === (string) $settings['debug']['performance_overlay'];
	}

	/**
	 * Helper to check if Query Information debug is enabled.
	 */
	public static function is_query_debug_enabled() {
		if ( ! self::is_debug_mode() ) {
			return false;
		}
		$settings = get_option( 'elonix_settings', array() );
		return isset( $settings['debug']['query_information'] ) && '1' === (string) $settings['debug']['query_information'];
	}

	/**
	 * Get a setting value from the multidimensional Toolkit Settings array.
	 *
	 * @param string $key     The setting key.
	 * @param mixed  $default Default value if not found.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$settings = get_option( 'elonix_settings', array() );

		if ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}

		if ( is_array( $settings ) ) {
			foreach ( $settings as $section => $fields ) {
				if ( is_array( $fields ) && isset( $fields[ $key ] ) ) {
					return $fields[ $key ];
				}
			}
		}

		return $default;
	}

}
