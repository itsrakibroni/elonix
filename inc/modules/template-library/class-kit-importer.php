<?php
namespace Elonix_Toolkit\Modules\Template_Library;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Kit_Importer {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Import a single component from a Kit.
	 *
	 * @param array $kit_manifest
	 * @param string $component_key (e.g., 'header', 'home')
	 * @return int|\WP_Error
	 */
	public function import_component( $kit_manifest, $component_key ) {
		if ( empty( $kit_manifest['templates'][ $component_key ] ) ) {
			return new \WP_Error( 'missing_component', esc_html__( 'Component not found in kit manifest.', 'elonix' ) );
		}

		$relative_path = $kit_manifest['templates'][ $component_key ];
		$slug = $kit_manifest['slug'];
		$base_dir = wp_normalize_path( ELONIX_ACC_PATH . 'templates/kits/' . $slug . '/' );
		$manifest_path = wp_normalize_path( $base_dir . $relative_path );

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		if ( ! $wp_filesystem->exists( $manifest_path ) ) {
			return new \WP_Error( 'file_not_found', esc_html__( 'Template manifest file not found in kit.', 'elonix' ) );
		}

		// Kit Importer directly reads the template.json to avoid the standard library structure assumption
		$template_dir = wp_normalize_path( dirname( $manifest_path ) );
		$json_file = wp_normalize_path( $template_dir . '/template.json' );

		if ( ! $wp_filesystem->exists( $json_file ) ) {
			return new \WP_Error( 'missing_json', esc_html__( 'Template JSON missing in kit.', 'elonix' ) );
		}

		$json_content = $wp_filesystem->get_contents( $json_file );
		if ( empty( $json_content ) ) {
			return new \WP_Error( 'empty_json', esc_html__( 'Template JSON is empty.', 'elonix' ) );
		}

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return new \WP_Error( 'elementor_missing', esc_html__( 'Elementor is required.', 'elonix' ) );
		}

		$import_args = array(
			'fileData' => base64_encode( $json_content ),
			'fileName' => sanitize_file_name( $slug . '-' . $component_key . '.json' ),
		);

		$result = \Elementor\Plugin::$instance->templates_manager->import_template( $import_args );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( is_array( $result ) && ! empty( $result ) ) {
			$post_id = $result[0]['template_id'];
			
			$allowed_types = array( 'header', 'footer', 'single', 'archive', 'search', 'error-404', 'popup', 'section', 'page' );
			$type = 'page';
			foreach ( $allowed_types as $allowed ) {
				if ( strpos( $relative_path, $allowed ) !== false ) {
					$type = $allowed;
					break;
				}
			}
			
			if ( in_array( $type, $allowed_types, true ) ) {
				update_post_meta( $post_id, '_elementor_template_type', $type );
			}

			\Elementor\Plugin::$instance->files_manager->clear_cache();
			return $post_id;
		}

		return new \WP_Error( 'import_failed', esc_html__( 'Elementor import failed.', 'elonix' ) );
	}

	/**
	 * Imports Elementor Global Styles using official Elementor APIs.
	 *
	 * @param array $kit_manifest
	 * @return bool|\WP_Error
	 */
	public function import_global_styles( $kit_manifest ) {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return new \WP_Error( 'elementor_missing', esc_html__( 'Elementor is required to import global styles.', 'elonix' ) );
		}

		$slug = $kit_manifest['slug'];
		$styles_path = wp_normalize_path( ELONIX_ACC_PATH . 'templates/kits/' . $slug . '/global-styles.json' );

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		if ( ! $wp_filesystem->exists( $styles_path ) ) {
			return new \WP_Error( 'file_not_found', esc_html__( 'Global styles file not found in kit.', 'elonix' ) );
		}

		$content = $wp_filesystem->get_contents( $styles_path );
		$new_styles = json_decode( $content, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $new_styles ) ) {
			return new \WP_Error( 'invalid_json', esc_html__( 'Invalid global styles JSON.', 'elonix' ) );
		}

		// Elementor API: Get Active Kit
		$active_kit_id = \Elementor\Plugin::$instance->kits_manager->get_active_id();
		if ( ! $active_kit_id ) {
			return new \WP_Error( 'kit_missing', esc_html__( 'No active Elementor kit found.', 'elonix' ) );
		}

		$current_settings = get_post_meta( $active_kit_id, '_elementor_page_settings', true );
		if ( ! is_array( $current_settings ) ) {
			$current_settings = array();
		}

		// Safely merge new styles (Typography, Colors, Spacing, Buttons, Forms, Variables, etc.)
		// Array merge will overwrite matching keys.
		$merged_settings = array_merge( $current_settings, $new_styles );

		update_post_meta( $active_kit_id, '_elementor_page_settings', $merged_settings );

		// Flush CSS cache
		\Elementor\Plugin::$instance->files_manager->clear_cache();

		return true;
	}

	/**
	 * Log the import progress.
	 * 
	 * @param string $kit_slug
	 * @param string $component_key
	 * @param int $assigned_id
	 */
	public function log_import( $kit_slug, $component_key, $assigned_id ) {
		$user_id = get_current_user_id();
		$history = get_user_meta( $user_id, 'tv_imported_kits', true );
		if ( ! is_array( $history ) ) {
			$history = array();
		}

		if ( ! isset( $history[ $kit_slug ] ) ) {
			$history[ $kit_slug ] = array(
				'timestamp'  => time(),
				'components' => array()
			);
		}

		$history[ $kit_slug ]['components'][] = array(
			'key'         => $component_key,
			'assigned_id' => $assigned_id,
			'time'        => time(),
		);

		update_user_meta( $user_id, 'tv_imported_kits', $history );
	}
}
