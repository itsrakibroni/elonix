<?php
/**
 * Elonix – Toolkit for Elementor Widget Manager Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Widget_Manager {

	/**
	 * Initialize the manager and register/load widget registry.
	 */
	public static function init() {
		require_once ELONIX_ACC_PATH . 'inc/elementor/class-widget-registry.php';
		Elonix_Toolkit_Widget_Registry::instance();
	}

	/**
	 * Register a widget dynamically in the registry.
	 *
	 * @param string $slug Widget slug/ID.
	 * @param array  $args Widget configuration arguments (title, description, etc.).
	 */
	public static function register_widget( $slug, $args ) {
		require_once ELONIX_ACC_PATH . 'inc/elementor/class-widget-registry.php';
		Elonix_Toolkit_Widget_Registry::instance()->register( $slug, $args );
	}

	/**
	 * Get all registered widgets.
	 *
	 * @return array List of registered widgets.
	 */
	public static function get_registered_widgets() {
		require_once ELONIX_ACC_PATH . 'inc/elementor/class-widget-registry.php';
		return Elonix_Toolkit_Widget_Registry::instance()->get_widgets();
	}

	/**
	 * Get saved widget statuses from the database option.
	 *
	 * @return array Key-value pair of widget slug and status (boolean).
	 */
	public static function get_saved_statuses() {
		$saved = get_option( 'elonix_widgets', array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return $saved;
	}

	/**
	 * Check if a widget is enabled.
	 * Default is true if status is not explicitly set to false.
	 *
	 * @param string $slug Widget slug.
	 * @return bool True if enabled, false otherwise.
	 */
	public static function is_widget_enabled( $slug ) {
		$statuses = self::get_saved_statuses();
		if ( isset( $statuses[ $slug ] ) ) {
			return (bool) $statuses[ $slug ];
		}
		// Default to enabled if not set
		return true;
	}

	/**
	 * Get list of enabled widget slugs.
	 *
	 * @return array Enabled widget slugs.
	 */
	public static function get_enabled_widgets() {
		$enabled    = array();
		$registered = self::get_registered_widgets();
		foreach ( array_keys( $registered ) as $slug ) {
			if ( self::is_widget_enabled( $slug ) ) {
				$enabled[] = $slug;
			}
		}
		return $enabled;
	}

	/**
	 * Save widget status.
	 *
	 * @param string $slug   Widget slug.
	 * @param bool   $status Enabled (true) or disabled (false).
	 */
	public static function save_widget_status( $slug, $status ) {
		$statuses          = self::get_saved_statuses();
		$statuses[ $slug ] = (bool) $status;
		update_option( 'elonix_widgets', $statuses );
	}

	/**
	 * Bulk save widget statuses.
	 *
	 * @param array $statuses Array of widget slug to boolean statuses.
	 */
	public static function save_statuses( $statuses ) {
		if ( ! is_array( $statuses ) ) {
			return;
		}
		$clean_statuses = array();
		$registered     = self::get_registered_widgets();
		foreach ( $registered as $slug => $args ) {
			$clean_statuses[ $slug ] = isset( $statuses[ $slug ] ) && (bool) $statuses[ $slug ];
		}
		update_option( 'elonix_widgets', $clean_statuses );
	}
}
