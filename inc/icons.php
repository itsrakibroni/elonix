<?php
/**
 * Custom Icon Library for Elementor - Theme Icons (Boxicons & FontAwesome)
 * Main Tab: Theme All Icons with Sub-tabs
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Register Custom Icon Library with Elementor
 */
class Elonix_Custom_Icons {

	/**
	 * Instance of this class
	 */
	private static $instance = null;

	/**
	 * Plugin path
	 */
	private $plugin_path;

	/**
	 * Plugin URL
	 */
	private $plugin_url;

	/**
	 * Get instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		// Set plugin path and URL
		$this->plugin_path = ELONIX_ACC_PATH;
		$this->plugin_url  = ELONIX_ACC_URL;

		// Register custom icons with Elementor
		add_filter( 'elementor/icons_manager/additional_tabs', array( $this, 'register_custom_icons' ) );

		// Enqueue styles for frontend, editor, and preview
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 999 );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ) );
	}

	/**
	 * Register custom icon library with Elementor
	 *
	 * @param array $tabs Existing icon tabs
	 * @return array Modified icon tabs
	 */
	public function register_custom_icons( $tabs ) {
		// Generate JSON files only if they don't exist or are outdated.
		$this->process_boxicons_files();

		// Helper function to get file URL with version query string.
		$versioned_url = function ( $file_path ) {
			$file_real_path = $this->plugin_path . str_replace( $this->plugin_url, '', $file_path );
			return file_exists( $file_real_path ) ? $file_path . '?ver=' . filemtime( $file_real_path ) : $file_path;
		};

		// Register Sub-tab: BoxIcon Basic Icons
		$tabs['theme-icons-basic'] = array(
			'name'          => 'theme-icons-basic',
			'label'         => esc_html__( 'BoxIcon - Basic', 'elonix' ),
			'url'           => '',
			'enqueue'       => array( $this->plugin_url . 'assets/icons/boxicons/boxicons.min.css' ),
			'prefix'        => '',
			'displayPrefix' => '',
			'labelIcon'     => 'bx bx-cube-alt',
			'ver'           => '1.0.0',
			'fetchJson'     => $versioned_url( $this->plugin_url . 'assets/icons/boxicons/boxicons-processed.json' ),
			'native'        => false,
		);

		// Register Sub-tab: BoxIcon Brands Icons
		$tabs['theme-icons-brands'] = array(
			'name'          => 'theme-icons-brands',
			'label'         => esc_html__( 'BoxIcon - Brands', 'elonix' ),
			'url'           => '',
			'enqueue'       => array( $this->plugin_url . 'assets/icons/boxicons/boxicons-brands.min.css' ),
			'prefix'        => '',
			'displayPrefix' => '',
			'labelIcon'     => 'bx bxs-flag-alt',
			'ver'           => '1.0.0',
			'fetchJson'     => $versioned_url( $this->plugin_url . 'assets/icons/boxicons/boxicons-brands-processed.json' ),
			'native'        => false,
		);

		return $tabs;
	}

	/**
	 * Process Boxicons files only
	 */
	private function process_boxicons_files() {
		// Process Boxicons Basic
		$this->process_boxicons( 'boxicons.json', 'boxicons-processed.json', 'bx' );

		// Process Boxicons Brands
		$this->process_boxicons( 'boxicons-brands.json', 'boxicons-brands-processed.json', 'bxl' );
	}

	/**
	 * Process Boxicons JSON files
	 *
	 * @param string $source_file Source JSON filename
	 * @param string $output_file Output processed JSON filename
	 * @param string $prefix Icon prefix (bx or bxl)
	 * @return array Processed icons array
	 */
	private function process_boxicons( $source_file, $output_file, $prefix ) {
		$json_file           = $this->plugin_path . 'assets/icons/boxicons/' . $source_file;
		$processed_json_file = $this->plugin_path . 'assets/icons/boxicons/' . $output_file;

		// If the processed file exists and is newer than the source, do nothing.
		if ( file_exists( $processed_json_file ) && filemtime( $processed_json_file ) > filemtime( $json_file ) ) {
			return $this->get_processed_icons( $processed_json_file );
		}

		if ( ! file_exists( $json_file ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

			}
			return array();
		}

		// Read original JSON.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$json_content = file_get_contents( $json_file );
		$icons_data   = json_decode( $json_content, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return array();
		}

		// Process icons for Elementor format.
		$processed_icons = array();

		foreach ( $icons_data as $icon_name => $unicode ) {
			// Skip variable-selector and other non-icon entries
			if ( strpos( $icon_name, 'variable-selector' ) !== false ) {
				continue;
			}

			// Add appropriate prefix
			$processed_icons[] = $prefix . ' ' . $icon_name;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		// writing to plugin dir removed for production compliance

		return $processed_icons;
	}

	/**
	 * Get icons from already processed JSON file
	 *
	 * @param string $file_path Path to processed JSON file
	 * @return array Icons array
	 */
	private function get_processed_icons( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			return array();
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$json_content = file_get_contents( $file_path );
		$data         = json_decode( $json_content, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return array();
		}

		// Return icons array or an empty array.
		return isset( $data['icons'] ) ? $data['icons'] : array();
	}


	public function enqueue_styles() {
		// These styles are for the live site.
		// The editor will enqueue them via the 'enqueue' key in the tab registration.
		if ( ! wp_style_is( 'elonix-boxicons', 'enqueued' ) ) {
			wp_enqueue_style( 'elonix-boxicons', $this->plugin_url . 'assets/icons/boxicons/boxicons.min.css', array(), ELONIX_VERSION );
		}
		if ( ! wp_style_is( 'elonix-boxicons-brands', 'enqueued' ) ) {
			wp_enqueue_style( 'elonix-boxicons-brands', $this->plugin_url . 'assets/icons/boxicons/boxicons-brands.min.css', array(), ELONIX_VERSION );
		}
	}

	public function enqueue_editor_styles() {
		// Enqueue styles for the editor, as they are also needed there.
		if ( ! wp_style_is( 'elonix-boxicons-editor', 'enqueued' ) ) {
			wp_enqueue_style( 'elonix-boxicons-editor', $this->plugin_url . 'assets/icons/boxicons/boxicons.min.css', array(), ELONIX_VERSION );
		}
		if ( ! wp_style_is( 'elonix-boxicons-brands-editor', 'enqueued' ) ) {
			wp_enqueue_style( 'elonix-boxicons-brands-editor', $this->plugin_url . 'assets/icons/boxicons/boxicons-brands.min.css', array(), ELONIX_VERSION );
		}
		if ( ! wp_style_is( 'elonix-editor-style', 'enqueued' ) ) {
			wp_enqueue_style( 'elonix-editor-style', $this->plugin_url . 'assets/css/editor-style.css', array(), ELONIX_VERSION );
		}
	}
}

// Initialize the custom icons
Elonix_Custom_Icons::get_instance();
