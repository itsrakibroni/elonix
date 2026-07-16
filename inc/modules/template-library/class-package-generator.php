<?php
namespace Elonix_Toolkit\Modules\Template_Library;

use Elonix_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Package_Generator {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ( class_exists( 'Elonix_Settings' ) && Elonix_Settings::is_developer_mode() ) {
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}
	}

	public function register_routes() {
		register_rest_route(
			'elonix/v1',
			'/developer/package/library',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_to_library' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'elonix/v1',
			'/developer/package/export',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'export_package' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'elonix/v1',
			'/developer/package/delete',
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_package' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	// --- Filesystem Hardening --- //

	private function write_file( $path, $content ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		if ( function_exists( 'WP_Filesystem' ) && WP_Filesystem() ) {
			global $wp_filesystem;
			if ( $wp_filesystem->put_contents( $path, $content ) ) {
				return true;
			}
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$result = @file_put_contents( $path, $content );
		return $result !== false;
	}

	private function create_dir( $path ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		if ( function_exists( 'WP_Filesystem' ) && WP_Filesystem() ) {
			global $wp_filesystem;
			if ( ! $wp_filesystem->exists( $path ) ) {
				if ( $wp_filesystem->mkdir( $path ) ) {
					return true;
				}
			} else {
				return true;
			}
		}
		// Safe fallback
		if ( ! file_exists( $path ) ) {
			return @wp_mkdir_p( $path );
		}
		return true;
	}

	private function delete_dir( $path ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		if ( function_exists( 'WP_Filesystem' ) && WP_Filesystem() ) {
			global $wp_filesystem;
			if ( $wp_filesystem->delete( $path, true ) ) {
				return true;
			}
		}
		// Safe fallback
		if ( file_exists( $path ) && is_dir( $path ) ) {
			$files = array_diff( scandir( $path ), array( '.', '..' ) );
			foreach ( $files as $file ) {
				$curr_path = $path . '/' . $file;
				// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				is_dir( $curr_path ) ? $this->delete_dir( $curr_path ) : @unlink( $curr_path );
			}
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
			return @rmdir( $path );
		}
		return false;
	}

	private function copy_file( $src, $dest ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		if ( function_exists( 'WP_Filesystem' ) && WP_Filesystem() ) {
			global $wp_filesystem;
			if ( $wp_filesystem->copy( $src, $dest ) ) {
				return true;
			}
		}
		// Safe fallback
		return @copy( $src, $dest );
	}

	private function file_exists_safe( $path ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		if ( function_exists( 'WP_Filesystem' ) && WP_Filesystem() ) {
			global $wp_filesystem;
			return $wp_filesystem->exists( $path );
		}
		return file_exists( $path );
	}

	// --- Elementor Export & Data --- //

	private function get_elementor_export( $post_id ) {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return new \WP_Error( 'elementor_missing', 'Elementor is required.' );
		}

		$document = \Elementor\Plugin::$instance->documents->get( $post_id );
		if ( ! $document ) {
			return new \WP_Error( 'document_missing', 'Elementor document not found.' );
		}

		$export_data = $document->get_export_data();
		return wp_json_encode( $export_data );
	}

	private function detect_dependencies( $post_id ) {
		$deps = array( 'elementor' );

		$content = get_post_meta( $post_id, '_elementor_data', true );
		if ( ! empty( $content ) ) {
			if ( strpos( $content, 'elonix' ) !== false ) {
				$deps[] = 'elonix';
			}
			if ( strpos( $content, 'woocommerce' ) !== false ) {
				$deps[] = 'woocommerce';
			}
			if ( strpos( $content, 'acf' ) !== false ) {
				$deps[] = 'acf';
			}
			if ( strpos( $content, 'contact-form-7' ) !== false ) {
				$deps[] = 'contact-form-7';
			}
		}

		return array_unique( $deps );
	}

	public function get_template_folder_type( $type ) {
		$normalized = Type_Normalizer::normalize_template_type( $type );

		$map = array(
			'header'    => 'headers',
			'footer'    => 'footers',
			'single'    => 'single',
			'archive'   => 'archive',
			'search'    => 'search',
			'popup'     => 'popup',
			'error-404' => '404',
			'page'      => 'pages',
			'section'   => 'sections',
			'loop'      => 'loop',
		);
		return isset( $map[ $normalized ] ) ? $map[ $normalized ] : 'pages';
	}

	public function get_elementor_type( $type ) {
		// Canonical format is already Elementor compatible!
		return Type_Normalizer::normalize_template_type( $type );
	}

	private function generate_manifest( $post_id, $args ) {
		$post = get_post( $post_id );

		$dependencies = $this->detect_dependencies( $post_id );
		$categories   = array();
		if ( ! empty( $args['category'] ) ) {
			$categories = array_map( 'trim', explode( ',', $args['category'] ) );
		}

		$tags = array();
		if ( ! empty( $args['tags'] ) ) {
			$tags = array_map( 'trim', explode( ',', $args['tags'] ) );
		}

		$manifest = array(
			'id'               => md5( $args['slug'] . $post_id ),
			'slug'             => sanitize_title( $args['slug'] ),
			'title'            => sanitize_text_field( $args['title'] ),
			'description'      => isset( $args['description'] ) ? sanitize_textarea_field( $args['description'] ) : '',
			'author'           => isset( $args['author'] ) ? sanitize_text_field( $args['author'] ) : 'Elonix',
			'version'          => isset( $args['version'] ) ? sanitize_text_field( $args['version'] ) : '1.0.0',
			'type'             => $this->get_elementor_type( $post->post_type ),
			'category'         => $categories,
			'tags'             => $tags,
			'required_plugins' => $dependencies,
			'status'           => isset( $args['status'] ) ? sanitize_text_field( $args['status'] ) : 'Published',
		);

		return wp_json_encode( $manifest, JSON_PRETTY_PRINT );
	}

	private function generate_readme( $args, $dependencies, $builder_type ) {
		$content  = '=== ' . $args['title'] . " ===\n";
		$content .= 'Version: ' . ( isset( $args['version'] ) ? $args['version'] : '1.0.0' ) . "\n";
		$content .= 'Author: ' . ( isset( $args['author'] ) ? $args['author'] : 'Elonix' ) . "\n\n";
		$content .= "== Description ==\n";
		$content .= isset( $args['description'] ) && ! empty( $args['description'] ) ? $args['description'] : 'A premium template for Elonix – Toolkit for Elementor.';
		$content .= "\n\n== Requirements ==\n";
		$content .= "This template requires the following plugins/modules:\n";
		foreach ( $dependencies as $dep ) {
			$content .= '- ' . ucfirst( str_replace( '-', ' ', $dep ) ) . "\n";
		}
		$content .= "\n== Supported Builders ==\n";
		$content .= 'This template is designed for: ' . ucfirst( $builder_type ) . " Builder.\n";
		$content .= "\n== Installation ==\n";
		$content .= "1. Go to Elonix -> Template Library in your WordPress Admin.\n";
		$content .= "2. Click 'Import' and select this package.\n";
		$content .= "3. Assign it to your desired locations.\n";
		return $content;
	}

	// --- Library Integration --- //

	private function process_package_image( $attachment_id, $target_dir, $base_name ) {
		if ( ! $attachment_id ) {
			return false;
		}
		$file = get_attached_file( $attachment_id );
		if ( ! $file || ! file_exists( $file ) ) {
			return false;
		}

		$ext  = pathinfo( $file, PATHINFO_EXTENSION );
		$mime = wp_get_image_mime( $file );

		if ( function_exists( 'imagewebp' ) && in_array( $mime, array( 'image/jpeg', 'image/png' ) ) ) {
			$image = null;
			if ( $mime === 'image/jpeg' ) {
				$image = @imagecreatefromjpeg( $file );
			} elseif ( $mime === 'image/png' ) {
				$image = @imagecreatefrompng( $file );
				if ( $image ) {
					imagepalettetotruecolor( $image );
					imagealphablending( $image, true );
					imagesavealpha( $image, true );
				}
			}

			if ( $image ) {
				$webp_path = $target_dir . $base_name . '.webp';
				if ( @imagewebp( $image, $webp_path, 80 ) ) {
					imagedestroy( $image );
					return true;
				}
				imagedestroy( $image );
			}
		}

		// Fallback: Just copy original
		$final_ext = strtolower( $ext ) === 'jpg' ? 'jpeg' : strtolower( $ext );
		$this->copy_file( $file, $target_dir . $base_name . '.' . $final_ext );
		return true;
	}

	public function add_to_library( \WP_REST_Request $request ) {
		$post_id = intval( $request->get_param( 'post_id' ) );
		$nonce   = $request->get_header( 'x_wp_nonce' );

		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new \WP_Error( 'invalid_nonce', 'Invalid nonce.', array( 'status' => 403 ) );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new \WP_Error( 'not_found', 'Post not found.', array( 'status' => 404 ) );
		}

		$slug = sanitize_title( $request->get_param( 'slug' ) );
		if ( empty( $slug ) ) {
			return new \WP_Error( 'missing_slug', 'Slug is required.', array( 'status' => 400 ) );
		}

		$type_folder        = $this->get_template_folder_type( $post->post_type );
		$base_templates_dir = wp_normalize_path( ELONIX_ACC_PATH . 'templates/' );
		$target_dir         = wp_normalize_path( $base_templates_dir . $type_folder . '/' . $slug . '/' );

		// Validation: Writable directory
		if ( ! wp_is_writable( ELONIX_ACC_PATH ) ) {
			return new \WP_Error( 'not_writable', 'Plugin directory is not writable. Please check filesystem permissions.', array( 'status' => 500 ) );
		}

		// Validation: Slug Conflict Resolution
		$conflict_action = $request->get_param( 'conflict_action' );

		if ( $this->file_exists_safe( $target_dir ) ) {
			if ( $conflict_action === 'overwrite' ) {
				$this->delete_dir( $target_dir );
			} elseif ( $conflict_action === 'duplicate' ) {
				$original_slug = $slug;
				$counter       = 2;
				while ( $this->file_exists_safe( wp_normalize_path( $base_templates_dir . $type_folder . '/' . $slug . '/' ) ) ) {
					$slug = $original_slug . '-' . $counter;
					++$counter;
				}
				$target_dir = wp_normalize_path( $base_templates_dir . $type_folder . '/' . $slug . '/' );
			} else {
				return new \WP_Error( 'slug_exists', 'Package with this slug already exists.', array( 'status' => 409 ) );
			}
		}

		if ( ! $this->file_exists_safe( $target_dir ) ) {
			if ( ! $this->create_dir( $target_dir ) ) {
				return new \WP_Error( 'dir_failed', 'Failed to create directory structure.', array( 'status' => 500 ) );
			}
		}

		// 1. Export Elementor JSON
		$json = $this->get_elementor_export( $post_id );
		if ( is_wp_error( $json ) ) {
			$this->delete_dir( $target_dir ); // Cleanup on failure
			return $json;
		}

		// Validation: Elementor JSON
		$decoded = json_decode( $json, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$this->delete_dir( $target_dir );
			return new \WP_Error( 'invalid_json', 'Generated Elementor JSON is invalid.', array( 'status' => 500 ) );
		}

		if ( ! $this->write_file( $target_dir . 'template.json', $json ) ) {
			$this->delete_dir( $target_dir );
			return new \WP_Error( 'write_failed', 'Failed to write template.json', array( 'status' => 500 ) );
		}

		// 2. Generate Manifest
		// Ensure slug is correct in manifest if duplicated
		$args         = $request->get_params();
		$args['slug'] = $slug;

		$manifest_json = $this->generate_manifest( $post_id, $args );
		if ( ! $this->write_file( $target_dir . 'manifest.json', $manifest_json ) ) {
			$this->delete_dir( $target_dir );
			return new \WP_Error( 'write_failed', 'Failed to write manifest.json', array( 'status' => 500 ) );
		}

		// 3. Process Uploaded Images or Fallback to Placeholder
		$thumb_id   = intval( $request->get_param( 'thumbnail_id' ) );
		$preview_id = intval( $request->get_param( 'preview_id' ) );

		$this->process_package_image( $thumb_id, $target_dir, 'thumbnail' );
		$this->process_package_image( $preview_id, $target_dir, 'preview' );

		// Fallback Placeholder logic
		$placeholder = ELONIX_ACC_PATH . 'assets/admin/images/placeholder.webp';
		if ( $this->file_exists_safe( $placeholder ) ) {
			if ( empty( glob( $target_dir . 'thumbnail.*' ) ) ) {
				$this->copy_file( $placeholder, $target_dir . 'thumbnail.webp' );
			}
			if ( empty( glob( $target_dir . 'preview.*' ) ) ) {
				$this->copy_file( $placeholder, $target_dir . 'preview.webp' );
			}
		}

		// Save canonical slug for future exports
		update_post_meta( $post_id, '_tv_package_slug', $slug );

		// 4. Clear Cache & Refresh Library
		Cache::instance()->clear_cache();

		$msg = 'Successfully packaged and added to Local Library.';
		if ( empty( $thumb_id ) || empty( $preview_id ) ) {
			$msg .= ' Warning: No preview/thumbnail image selected. Placeholders were used.';
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => $msg,
				'slug'    => $slug,
			)
		);
	}

	public function export_package( \WP_REST_Request $request ) {
		$post_id = intval( $request->get_param( 'post_id' ) );
		$nonce   = $request->get_header( 'x_wp_nonce' );

		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new \WP_Error( 'invalid_nonce', 'Invalid nonce.', array( 'status' => 403 ) );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new \WP_Error( 'not_found', 'Post not found.', array( 'status' => 404 ) );
		}

		if ( ! class_exists( 'ZipArchive' ) ) {
			return new \WP_Error( 'no_ziparchive', 'ZipArchive class is not available on this server.', array( 'status' => 500 ) );
		}

		// Locate Package Directory
		$slug = get_post_meta( $post_id, '_tv_package_slug', true );
		if ( empty( $slug ) ) {
			$slug = $post->post_name; // Fallback
		}

		$type_folder        = $this->get_template_folder_type( $post->post_type );
		$base_templates_dir = wp_normalize_path( ELONIX_ACC_PATH . 'templates/' );
		$target_dir         = wp_normalize_path( $base_templates_dir . $type_folder . '/' . $slug . '/' );

		// Issue #1 Fix: Package Content Validation
		if ( ! $this->file_exists_safe( $target_dir ) || ! $this->file_exists_safe( $target_dir . 'manifest.json' ) || ! $this->file_exists_safe( $target_dir . 'template.json' ) ) {
			return new \WP_Error( 'not_in_library', 'Validation Failed: This template has not been properly added to the Local Library. Please Add to Library first.', array( 'status' => 400 ) );
		}

		$thumb_files   = glob( $target_dir . 'thumbnail.*' );
		$preview_files = glob( $target_dir . 'preview.*' );

		$thumb_path   = ! empty( $thumb_files ) ? $thumb_files[0] : '';
		$preview_path = ! empty( $preview_files ) ? $preview_files[0] : '';

		if ( empty( $thumb_path ) || empty( $preview_path ) || ! $this->file_exists_safe( $thumb_path ) || ! $this->file_exists_safe( $preview_path ) ) {
			return new \WP_Error( 'missing_assets', 'Validation Failed: Thumbnail or preview image is missing from the local package directory. Please ensure images exist before exporting.', array( 'status' => 400 ) );
		}

		// Read existing manifest rather than generating a new one to preserve accurate metadata
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$manifest_content = @file_get_contents( $target_dir . 'manifest.json' );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$template_content = @file_get_contents( $target_dir . 'template.json' );

		if ( ! $manifest_content || ! $template_content ) {
			return new \WP_Error( 'read_failed', 'Failed to read package contents.', array( 'status' => 500 ) );
		}

		$manifest_data = json_decode( $manifest_content, true );

		$args = array(
			'slug'        => $slug,
			'title'       => isset( $manifest_data['title'] ) ? $manifest_data['title'] : $post->post_title,
			'description' => isset( $manifest_data['description'] ) ? $manifest_data['description'] : '',
			'author'      => isset( $manifest_data['author'] ) ? $manifest_data['author'] : 'Elonix',
			'version'     => isset( $manifest_data['version'] ) ? $manifest_data['version'] : '1.0.0',
		);

		$dependencies = isset( $manifest_data['required_plugins'] ) ? $manifest_data['required_plugins'] : $this->detect_dependencies( $post_id );
		$builder_type = $this->get_elementor_type( $post->post_type );

		$readme  = $this->generate_readme( $args, $dependencies, $builder_type );
		$license = "GPLv2 or later\n\nThis template is distributed under the GNU General Public License.";

		// Create ZIP
		$upload_dir = wp_upload_dir();
		$zip_dir    = wp_normalize_path( $upload_dir['basedir'] . '/elonix-exports/' );
		if ( ! $this->file_exists_safe( $zip_dir ) ) {
			$this->create_dir( $zip_dir );
		}

		// Temp File Cleanup: Delete older zip exports to save space
		$this->cleanup_old_exports( $zip_dir );

		$zip_name = $slug . '-package-' . time() . '.zip';
		$zip_path = $zip_dir . $zip_name;

		$zip = new \ZipArchive();
		if ( $zip->open( $zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) === true ) {
			$zip->addFromString( 'template.json', $template_content );
			$zip->addFromString( 'manifest.json', $manifest_content );
			$zip->addFromString( 'README.txt', $readme );
			$zip->addFromString( 'license.txt', $license );

			// Bundle images correctly using actual basenames
			$zip->addFile( $thumb_path, basename( $thumb_path ) );
			$zip->addFile( $preview_path, basename( $preview_path ) );

			$zip->close();
		} else {
			return new \WP_Error( 'zip_failed', 'Failed to create ZIP archive.', array( 'status' => 500 ) );
		}

		$download_url = $upload_dir['baseurl'] . '/elonix-exports/' . $zip_name;

		return rest_ensure_response(
			array(
				'success'      => true,
				'message'      => 'Package created successfully.',
				'download_url' => $download_url,
			)
		);
	}

	private function cleanup_old_exports( $zip_dir ) {
		// Clean zips older than 1 hour to prevent server bloat
		if ( is_dir( $zip_dir ) ) {
			$files = glob( $zip_dir . '*.zip' );
			$now   = time();
			if ( $files ) {
				foreach ( $files as $file ) {
					if ( is_file( $file ) && ( $now - filemtime( $file ) >= 3600 ) ) {
						// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
						@unlink( $file );
					}
				}
			}
		}
	}

	public function delete_package( \WP_REST_Request $request ) {
		$slug  = sanitize_title( $request->get_param( 'slug' ) );
		$type  = sanitize_text_field( $request->get_param( 'type' ) );
		$nonce = $request->get_header( 'x_wp_nonce' );

		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new \WP_Error( 'invalid_nonce', 'Invalid nonce.', array( 'status' => 403 ) );
		}

		if ( empty( $slug ) || empty( $type ) ) {
			return new \WP_Error( 'missing_params', 'Slug and type are required.', array( 'status' => 400 ) );
		}

		$type_folder        = $this->get_template_folder_type( $type );
		$base_templates_dir = wp_normalize_path( ELONIX_ACC_PATH . 'templates/' );
		$target_dir         = wp_normalize_path( $base_templates_dir . $type_folder . '/' . $slug . '/' );

		if ( ! $this->file_exists_safe( $target_dir ) ) {
			return new \WP_Error( 'not_found', 'Package not found in Local Template Library.', array( 'status' => 404 ) );
		}

		if ( $this->delete_dir( $target_dir ) ) {
			// Clear cache to reflect deletion
			if ( class_exists( 'Elonix_Toolkit\Modules\Template_Library\Cache' ) ) {
				Cache::instance()->clear_cache();
			}

			return rest_ensure_response(
				array(
					'success' => true,
					'message' => 'Package deleted successfully.',
				)
			);
		}

		return new \WP_Error( 'delete_failed', 'Failed to delete package directory. Check permissions.', array( 'status' => 500 ) );
	}
}
