<?php
namespace Elonix_Toolkit\Modules\Template_Library;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Manifest {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Parses and validates a manifest file.
	 *
	 * @param string $path Absolute path to manifest.json
	 * @param string $type The folder type (e.g., pages)
	 * @param string $slug The template slug
	 * @return array|\WP_Error Returns parsed array if valid, WP_Error otherwise.
	 */
	public function parse_and_validate( $path, $type, $slug ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$path = wp_normalize_path( $path );

		if ( ! $wp_filesystem->exists( $path ) || ! $wp_filesystem->is_readable( $path ) ) {
			return new \WP_Error( 'unreadable_manifest', esc_html__( 'Manifest file is unreadable.', 'elonix' ) );
		}

		$content = $wp_filesystem->get_contents( $path );
		if ( empty( $content ) ) {
			return new \WP_Error( 'empty_manifest', esc_html__( 'Manifest file is empty.', 'elonix' ) );
		}

		$data = json_decode( $content, true );
		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $data ) ) {
			return new \WP_Error( 'invalid_json', esc_html__( 'Manifest JSON is malformed.', 'elonix' ) );
		}

		// Validate required fields
		$required = array( 'id', 'title', 'version', 'type' );
		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				/* translators: %s: Required JSON field name. */
				return new \WP_Error( 'missing_field', sprintf( esc_html__( 'Manifest is missing required field: %s', 'elonix' ), $field ) );
			}
		}

		// Normalize basic fields
		$data['id'] = sanitize_text_field( $data['id'] );
		$data['slug'] = sanitize_title( $slug );
		$data['title'] = sanitize_text_field( $data['title'] );
		$data['type'] = sanitize_text_field( $data['type'] );
		$data['description'] = isset( $data['description'] ) ? sanitize_text_field( $data['description'] ) : '';
		
		$data['tags'] = isset( $data['tags'] ) && is_array( $data['tags'] ) ? array_map( 'sanitize_text_field', $data['tags'] ) : array();
		$data['category'] = isset( $data['category'] ) && is_array( $data['category'] ) ? array_map( 'sanitize_text_field', $data['category'] ) : array();
		$data['required_plugins'] = isset( $data['required_plugins'] ) && is_array( $data['required_plugins'] ) ? array_map( 'sanitize_text_field', $data['required_plugins'] ) : array();

		$base_url = ELONIX_ACC_URL . 'templates/' . $type . '/' . $slug . '/';
		$base_path = ELONIX_ACC_PATH . 'templates/' . $type . '/' . $slug . '/';
		
		$extensions = array('webp', 'jpg', 'jpeg', 'png');
		
		$data['thumbnail'] = '';
		foreach ( $extensions as $ext ) {
			if ( $wp_filesystem->exists( wp_normalize_path( $base_path . 'thumbnail.' . $ext ) ) ) {
				$data['thumbnail'] = $base_url . 'thumbnail.' . $ext;
				break;
			}
		}

		$data['preview'] = '';
		foreach ( $extensions as $ext ) {
			if ( $wp_filesystem->exists( wp_normalize_path( $base_path . 'preview.' . $ext ) ) ) {
				$data['preview'] = $base_url . 'preview.' . $ext;
				break;
			}
		}

		$data['source'] = 'local';
		$data['local_path'] = wp_normalize_path( $base_path );

		return $data;
	}
}
