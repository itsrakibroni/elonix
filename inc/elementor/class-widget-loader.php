<?php
/**
 * Elonix – Toolkit for Elementor Elementor Widget Registration Loader
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Widget_Loader {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Load Widget Base Class
		require_once ELONIX_ACC_PATH . 'inc/elementor/class-widget-base.php';
		require_once ELONIX_ACC_PATH . 'inc/elementor/class-social-base-widget.php';

		// Initialize the widget registry system
		require_once ELONIX_ACC_PATH . 'inc/elementor/class-widget-registry.php';
		Elonix_Toolkit_Widget_Registry::instance();
	}

	/**
	 * Retrieve enabled widgets from Widget Manager.
	 *
	 * @return array List of enabled widget slugs.
	 */
	public function get_enabled_widgets() {
		if ( class_exists( 'Elonix_Toolkit_Widget_Manager' ) ) {
			return Elonix_Toolkit_Widget_Manager::get_enabled_widgets();
		}

		// Fallback to direct option retrieval if Widget Manager is unavailable
		$saved = get_option( 'elonix_widgets', array() );
		if ( ! is_array( $saved ) ) {
			return array();
		}

		$enabled = array();
		foreach ( $saved as $slug => $status ) {
			if ( $status ) {
				$enabled[] = $slug;
			}
		}

		return $enabled;
	}

	/**
	 * Check if a widget is in the registry and its file exists.
	 *
	 * @param string $widget_slug Widget slug/ID.
	 * @return bool True if registered and file exists, false otherwise.
	 */
	public function widget_exists( $widget_slug ) {
		// Validate widget slug ID
		if ( ! preg_match( '/^[a-z0-9\-_]+$/i', $widget_slug ) ) {
			return false;
		}

		$registry = Elonix_Toolkit_Widget_Registry::instance()->get_widgets();
		if ( ! isset( $registry[ $widget_slug ] ) ) {
			return false;
		}

		$base_path = defined( 'ELONIX_ACC_PATH' ) ? ELONIX_ACC_PATH : dirname( __DIR__, 2 ) . '/';
		$file_path = $base_path . $registry[ $widget_slug ]['path'];

		return file_exists( $file_path );
	}

	/**
	 * Locate and safely load widget class files.
	 *
	 * @return array Slugs of successfully loaded widgets.
	 */
	public function load_widget_files() {
		$enabled  = $this->get_enabled_widgets();
		$loaded   = array();
		$registry = Elonix_Toolkit_Widget_Registry::instance()->get_widgets();

		foreach ( $enabled as $slug ) {
			// Validation & check
			if ( $this->widget_exists( $slug ) ) {
				$base_path = defined( 'ELONIX_ACC_PATH' ) ? ELONIX_ACC_PATH : dirname( __DIR__, 2 ) . '/';
				$file_path = $base_path . $registry[ $slug ]['path'];

				require_once $file_path;
				$loaded[] = $slug;
			} else {
				// Missing File Protection: Prevent fatal error and log warning for future diagnostics
				if ( isset( $registry[ $slug ] ) ) {
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

					}
				}
			}
		}

		return $loaded;
	}

	/**
	 * Register loaded widgets with Elementor.
	 *
	 * @param object $widgets_manager Elementor widgets manager instance.
	 */
	public function register_widgets( $widgets_manager ) {
		$loaded_widgets = $this->load_widget_files();
		$registry       = Elonix_Toolkit_Widget_Registry::instance()->get_widgets();

		foreach ( $loaded_widgets as $slug ) {
			$registry_entry = $registry[ $slug ];
			$class_name     = isset( $registry_entry['class'] ) ? $registry_entry['class'] : 'Elonix_Toolkit_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $slug ) ) ) . '_Widget';

			if ( class_exists( $class_name ) ) {
				$widgets_manager->register( new $class_name() );
			} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

			}
		}
	}

	/**
	 * Retrieve the predefined widget registry.
	 *
	 * @return array The central registry array.
	 */
	public function get_registry() {
		return Elonix_Toolkit_Widget_Registry::instance()->get_widgets();
	}
}
