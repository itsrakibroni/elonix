<?php
namespace Elonix_Toolkit\Modules\Template_Library;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Importer {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Imports a local template into the Elementor Library.
	 *
	 * @param array $template_meta
	 * @return int|\WP_Error
	 */
	public function import_local_template( $template_meta ) {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return new \WP_Error( 'elementor_missing', esc_html__( 'Elementor is required to import templates.', 'elonix' ) );
		}

		$local_path = isset( $template_meta['local_path'] ) ? $template_meta['local_path'] : '';
		if ( empty( $local_path ) ) {
			return new \WP_Error( 'missing_path', esc_html__( 'Template path is undefined.', 'elonix' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$json_file = wp_normalize_path( $local_path . 'template.json' );

		if ( ! $wp_filesystem->exists( $json_file ) || ! $wp_filesystem->is_readable( $json_file ) ) {
			return new \WP_Error( 'missing_json', esc_html__( 'Template JSON file is missing or unreadable.', 'elonix' ) );
		}

		$json_content = $wp_filesystem->get_contents( $json_file );
		if ( empty( $json_content ) ) {
			return new \WP_Error( 'empty_json', esc_html__( 'Template JSON is empty.', 'elonix' ) );
		}

		$parsed_json = json_decode( $json_content, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new \WP_Error( 'invalid_json', esc_html__( 'Template JSON is malformed.', 'elonix' ) );
		}

		$canonical_type = Type_Normalizer::normalize_template_type( isset( $template_meta['type'] ) ? $template_meta['type'] : 'page' );

		// Bypass Elementor Core's strict document type validation by wrapping the template as a 'page'
		$parsed_json['type'] = 'page';
		$parsed_json['title'] = isset( $template_meta['title'] ) ? sanitize_text_field( $template_meta['title'] ) : 'Imported Template';
		
		$temp_file = wp_normalize_path( get_temp_dir() . 'tv_import_' . time() . '.json' );
		$wp_filesystem->put_contents( $temp_file, wp_json_encode( $parsed_json ) );

		$source = \Elementor\Plugin::$instance->templates_manager->get_source( 'local' );
		$name = sanitize_file_name( $template_meta['slug'] . '.json' );
		$result = $source->import_template( $name, $temp_file );

		$wp_filesystem->delete( $temp_file );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( is_array( $result ) && ! empty( $result ) ) {
			$post_id = $result[0]['template_id'];
			
			// Enforce document type if it's a specific Theme Builder template type
			$allowed_theme_builder_types = array( 'header', 'footer', 'single', 'archive', 'search', 'error-404', 'popup', 'section', 'page', 'loop' );
			
			if ( in_array( $canonical_type, $allowed_theme_builder_types, true ) ) {
				update_post_meta( $post_id, '_elementor_template_type', $canonical_type );
			}

			\Elementor\Plugin::$instance->files_manager->clear_cache();
			return $post_id;
		}

		return new \WP_Error( 'import_failed', esc_html__( 'Elementor import failed to return a valid template ID.', 'elonix' ) );
	}
}
