<?php
/**
 * Elonix – Toolkit for Elementor Module Manager Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Module_Manager {

	/**
	 * Holds registered modules list.
	 *
	 * @var array
	 */
	private static $modules = array();

	/**
	 * Initialize the manager and register default modules.
	 */
	public static function init() {
		self::register_default_modules();
	}

	/**
	 * Register default modules.
	 */
	private static function register_default_modules() {
		self::register_module(
			'header_builder',
			array(
				'title'       => esc_html__( 'Header Builder', 'elonix' ),
				'description' => esc_html__( 'Build custom Elementor headers and assign them globally or to specific pages.', 'elonix' ),
			)
		);

		self::register_module(
			'footer_builder',
			array(
				'title'       => esc_html__( 'Footer Builder', 'elonix' ),
				'description' => esc_html__( 'Build custom Elementor footers and assign them globally or to specific pages.', 'elonix' ),
			)
		);

		self::register_module(
			'popup_builder',
			array(
				'title'       => esc_html__( 'Popup Builder', 'elonix' ),
				'description' => esc_html__( 'Design beautiful, conversion-focused popups and modals with trigger actions.', 'elonix' ),
			)
		);

		self::register_module(
			'archive_builder',
			array(
				'title'       => esc_html__( 'Archive Builder', 'elonix' ),
				'description' => esc_html__( 'Build custom templates for post type archives and search results.', 'elonix' ),
			)
		);

		self::register_module(
			'single_builder',
			array(
				'title'       => esc_html__( 'Single Builder', 'elonix' ),
				'description' => esc_html__( 'Build custom layouts for single posts, pages, and custom post types.', 'elonix' ),
			)
		);

		self::register_module(
			'dynamic_tags',
			array(
				'title'       => esc_html__( 'Dynamic Tags', 'elonix' ),
				'description' => esc_html__( 'Add dynamic content tags (post details, author info, ACF data) inside Elementor.', 'elonix' ),
			)
		);

		self::register_module(
			'advanced_404_builder',
			array(
				'title'       => esc_html__( 'Advanced 404 Builder', 'elonix' ),
				'description' => esc_html__( 'Design highly customizable 404 error page templates in Elementor with redirections, logging, and SEO controls.', 'elonix' ),
			)
		);

		self::register_module(
			'search_builder',
			array(
				'title'       => esc_html__( 'Search Builder', 'elonix' ),
				'description' => esc_html__( 'Build custom templates for WordPress search results.', 'elonix' ),
			)
		);

		self::register_module(
			'screen_loader',
			array(
				'title'       => esc_html__( 'Screen Loader', 'elonix' ),
				'description' => esc_html__( 'Global website loading screen with premium animations and styles.', 'elonix' ),
			)
		);


		self::register_module(
			'template_library',
			array(
				'title'       => esc_html__( 'Template Library', 'elonix' ),
				'description' => esc_html__( 'Access a library of pre-designed sections and templates to kickstart pages.', 'elonix' ),
			)
		);

		self::register_module(
			'custom_post_types',
			array(
				'title'       => esc_html__( 'Custom Post Types', 'elonix' ),
				'description' => esc_html__( 'Easily register custom post types and taxonomies with basic configurations.', 'elonix' ),
			)
		);

		self::register_module(
			'custom_icons',
			array(
				'title'       => esc_html__( 'Custom Icons', 'elonix' ),
				'description' => esc_html__( 'Upload custom SVG and font icon packs to extend the Elementor icon library.', 'elonix' ),
			)
		);
	}

	/**
	 * Register a module dynamically.
	 *
	 * @param string $slug Module slug/ID.
	 * @param array  $args Module configuration arguments.
	 */
	public static function register_module( $slug, $args ) {
		self::$modules[ $slug ] = wp_parse_args(
			$args,
			array(
				'title'       => '',
				'description' => '',
			)
		);
	}

	/**
	 * Get all registered modules.
	 *
	 * @return array List of registered modules.
	 */
	public static function get_registered_modules() {
		return self::$modules;
	}

	/**
	 * Get saved module statuses from database option.
	 *
	 * @return array Key-value pair of module slug and status (boolean).
	 */
	public static function get_saved_statuses() {
		$saved = get_option( 'elonix_modules', array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return $saved;
	}

	/**
	 * Check if a module is enabled.
	 * Default is true if status is not explicitly set to false.
	 *
	 * @param string $slug Module slug.
	 * @return bool True if enabled, false otherwise.
	 */
	public static function is_module_enabled( $slug ) {
		$statuses = self::get_saved_statuses();
		if ( isset( $statuses[ $slug ] ) ) {
			return (bool) $statuses[ $slug ];
		}
		// Default to enabled if not set
		return true;
	}

	/**
	 * Get list of enabled module slugs.
	 *
	 * @return array Enabled module slugs.
	 */
	public static function get_enabled_modules() {
		$enabled = array();
		foreach ( array_keys( self::$modules ) as $slug ) {
			if ( self::is_module_enabled( $slug ) ) {
				$enabled[] = $slug;
			}
		}
		return $enabled;
	}

	/**
	 * Save module status.
	 *
	 * @param string $slug   Module slug.
	 * @param bool   $status Enabled (true) or disabled (false).
	 */
	public static function save_module_status( $slug, $status ) {
		$statuses          = self::get_saved_statuses();
		$statuses[ $slug ] = (bool) $status;
		update_option( 'elonix_modules', $statuses );
	}

	/**
	 * Bulk save module statuses.
	 *
	 * @param array $statuses Array of module slug to boolean statuses.
	 */
	public static function save_statuses( $statuses ) {
		if ( ! is_array( $statuses ) ) {
			return;
		}
		$clean_statuses = array();
		foreach ( self::$modules as $slug => $args ) {
			$clean_statuses[ $slug ] = isset( $statuses[ $slug ] ) && (bool) $statuses[ $slug ];
		}
		update_option( 'elonix_modules', $clean_statuses );
	}
}
