<?php
namespace Elonix_Toolkit\Modules\Template_Library;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Kit_Manifest {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Parses and strictly validates a kit.json file.
	 *
	 * @param string $file_path
	 * @param string $slug
	 * @return array|\WP_Error
	 */
	public function parse_and_validate( $file_path, $slug ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		if ( ! $wp_filesystem->exists( $file_path ) ) {
			return new \WP_Error( 'file_not_found', esc_html__( 'Kit manifest file not found.', 'elonix' ) );
		}

		$content = $wp_filesystem->get_contents( $file_path );
		if ( empty( $content ) ) {
			return new \WP_Error( 'empty_file', esc_html__( 'Kit manifest file is empty.', 'elonix' ) );
		}

		$data = json_decode( $content, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new \WP_Error( 'invalid_json', esc_html__( 'Invalid JSON in kit manifest.', 'elonix' ) );
		}

		return $this->validate_schema( $data, $slug );
	}

	/**
	 * Strictly validates the schema to prevent malicious or malformed data.
	 *
	 * @param array $data
	 * @param string $slug
	 * @return array|\WP_Error
	 */
	private function validate_schema( $data, $slug ) {
		if ( ! is_array( $data ) ) {
			return new \WP_Error( 'invalid_schema', esc_html__( 'Manifest must be a JSON object.', 'elonix' ) );
		}

		// Required fields
		$required_keys = array( 'id', 'title' );
		foreach ( $required_keys as $key ) {
			if ( empty( $data[ $key ] ) ) {
				/* translators: %s: Required JSON key name. */
				return new \WP_Error( 'missing_required_key', sprintf( esc_html__( 'Missing required key: %s', 'elonix' ), $key ) );
			}
		}

		$valid_data = array(
			'id'                  => sanitize_text_field( $data['id'] ),
			'slug'                => sanitize_title( $slug ),
			'title'               => sanitize_text_field( $data['title'] ),
			'description'         => isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '',
			'author'              => isset( $data['author'] ) ? sanitize_text_field( $data['author'] ) : '',
			'version'             => isset( $data['version'] ) ? sanitize_text_field( $data['version'] ) : '1.0.0',
			'thumbnail'           => isset( $data['thumbnail'] ) ? esc_url_raw( $data['thumbnail'] ) : '',
			'preview'             => isset( $data['preview'] ) ? esc_url_raw( $data['preview'] ) : '',
			'tags'                => array(),
			'category'            => array(),
			'required_plugins'    => array(),
			'recommended_modules' => array(),
			'templates'           => array(), // List of components in the kit e.g., ['header' => 'header/manifest.json']
			'global_styles'       => isset( $data['global_styles'] ) ? true : false,
			'theme_options'       => isset( $data['theme_options'] ) ? true : false,
		);

		// Array sanitization
		if ( isset( $data['tags'] ) && is_array( $data['tags'] ) ) {
			$valid_data['tags'] = array_map( 'sanitize_text_field', $data['tags'] );
		}

		if ( isset( $data['category'] ) && is_array( $data['category'] ) ) {
			$valid_data['category'] = array_map( 'sanitize_text_field', $data['category'] );
		}

		if ( isset( $data['required_plugins'] ) && is_array( $data['required_plugins'] ) ) {
			$valid_data['required_plugins'] = array_map( 'sanitize_text_field', $data['required_plugins'] );
		}

		if ( isset( $data['recommended_modules'] ) && is_array( $data['recommended_modules'] ) ) {
			$valid_data['recommended_modules'] = array_map( 'sanitize_text_field', $data['recommended_modules'] );
		}

		if ( isset( $data['templates'] ) && is_array( $data['templates'] ) ) {
			foreach ( $data['templates'] as $tpl_type => $tpl_path ) {
				$valid_data['templates'][ sanitize_text_field( $tpl_type ) ] = sanitize_text_field( $tpl_path );
			}
		}

		return $valid_data;
	}
}
