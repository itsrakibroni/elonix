<?php
namespace Elonix_Toolkit\Modules\Template_Library;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Discovery {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Scans the local templates directory for valid manifests.
	 * 
	 * @return array
	 */
	public function get_local_templates() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$templates = array();
		$base_dir = wp_normalize_path( ELONIX_ACC_PATH . 'templates/' );
		
		if ( ! $wp_filesystem->exists( $base_dir ) || ! $wp_filesystem->is_dir( $base_dir ) ) {
			return $templates;
		}

		$types = array( 'pages', 'sections', 'headers', 'footers', 'archive', 'single', 'search', '404', 'popup' );
		$ignore_files = array( '.ds_store', 'thumbs.db', 'desktop.ini', '.git', '.gitkeep', 'node_modules', 'vendor' );
		$processed_ids = array();
		
		foreach ( $types as $type ) {
			$type_dir = wp_normalize_path( $base_dir . $type . '/' );
			
			if ( ! $wp_filesystem->exists( $type_dir ) || ! $wp_filesystem->is_dir( $type_dir ) ) {
				continue;
			}
			
			$dir_list = $wp_filesystem->dirlist( $type_dir );
			if ( empty( $dir_list ) ) {
				continue;
			}

			foreach ( $dir_list as $slug => $info ) {
				if ( 'd' !== $info['type'] ) {
					continue;
				}
				
				if ( in_array( strtolower( $slug ), $ignore_files, true ) || 0 === strpos( $slug, '.' ) ) {
					continue;
				}

				$template_dir = wp_normalize_path( $type_dir . $slug . '/' );
				$manifest_path = wp_normalize_path( $template_dir . 'manifest.json' );
				
				if ( $wp_filesystem->exists( $manifest_path ) ) {
					$manifest_data = Manifest::instance()->parse_and_validate( $manifest_path, $type, $slug );
					
					if ( ! is_wp_error( $manifest_data ) && is_array( $manifest_data ) ) {
						// Check for duplicate IDs
						$id = $manifest_data['id'];
						if ( ! isset( $processed_ids[ $id ] ) ) {
							$processed_ids[ $id ] = true;
							$templates[] = $manifest_data;
						}
					}
				}
			}
		}

		return $templates;
	}

	/**
	 * Scans the local kits directory for valid kit.json manifests.
	 * 
	 * @return array
	 */
	public function get_local_kits() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$kits = array();
		$base_dir = wp_normalize_path( ELONIX_ACC_PATH . 'templates/kits/' );
		
		if ( ! $wp_filesystem->exists( $base_dir ) || ! $wp_filesystem->is_dir( $base_dir ) ) {
			return $kits;
		}

		$ignore_files = array( '.ds_store', 'thumbs.db', 'desktop.ini', '.git', '.gitkeep', 'node_modules', 'vendor' );
		$processed_ids = array();
		
		$dir_list = $wp_filesystem->dirlist( $base_dir );
		if ( empty( $dir_list ) ) {
			return $kits;
		}

		foreach ( $dir_list as $slug => $info ) {
			if ( 'd' !== $info['type'] ) {
				continue;
			}
			
			if ( in_array( strtolower( $slug ), $ignore_files, true ) || 0 === strpos( $slug, '.' ) ) {
				continue;
			}

			$kit_dir = wp_normalize_path( $base_dir . $slug . '/' );
			$manifest_path = wp_normalize_path( $kit_dir . 'kit.json' );
			
			if ( $wp_filesystem->exists( $manifest_path ) ) {
				// Require the kit manifest parser if not loaded
				if ( ! class_exists( __NAMESPACE__ . '\Kit_Manifest' ) ) {
					require_once ELONIX_ACC_PATH . 'inc/modules/template-library/class-kit-manifest.php';
				}

				$manifest_data = Kit_Manifest::instance()->parse_and_validate( $manifest_path, $slug );
				
				if ( ! is_wp_error( $manifest_data ) && is_array( $manifest_data ) ) {
					// Check for duplicate IDs
					$id = $manifest_data['id'];
					if ( ! isset( $processed_ids[ $id ] ) ) {
						$processed_ids[ $id ] = true;
						$kits[] = $manifest_data;
					}
				}
			}
		}

		return $kits;
	}
}
