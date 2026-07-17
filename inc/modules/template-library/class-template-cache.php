<?php
namespace Elonix_Toolkit\Modules\Template_Library;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cache {
	private static $instance = null;
	const TRANSIENT_KEY = '_es_template_catalog';
	const KIT_TRANSIENT_KEY = '_es_kit_catalog';
	const HASH_KEY = '_es_template_dir_hash';

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'upgrader_process_complete', array( $this, 'clear_cache' ) );
		// Clear cache if version changed
		$cached_version = get_option( 'es_template_library_version' );
		if ( $cached_version !== ELONIX_VERSION ) {
			$this->clear_cache();
			update_option( 'es_template_library_version', ELONIX_VERSION );
		}
	}

	public function get_catalog() {
		$current_hash = $this->get_directory_hash();
		$stored_hash = get_option( self::HASH_KEY );

		if ( $current_hash !== $stored_hash ) {
			$this->clear_cache();
			update_option( self::HASH_KEY, $current_hash );
		}

		$catalog = get_transient( self::TRANSIENT_KEY );
		
		if ( false === $catalog ) {
			$catalog = Discovery::instance()->get_local_templates();
			set_transient( self::TRANSIENT_KEY, $catalog, DAY_IN_SECONDS );
		}
		
		return $catalog;
	}

	public function get_kit_catalog() {
		$current_hash = $this->get_directory_hash();
		$stored_hash = get_option( self::HASH_KEY );

		if ( $current_hash !== $stored_hash ) {
			$this->clear_cache();
			update_option( self::HASH_KEY, $current_hash );
		}

		$kits = get_transient( self::KIT_TRANSIENT_KEY );
		
		if ( false === $kits ) {
			$kits = Discovery::instance()->get_local_kits();
			set_transient( self::KIT_TRANSIENT_KEY, $kits, DAY_IN_SECONDS );
		}
		
		return $kits;
	}

	public function clear_cache() {
		delete_transient( self::TRANSIENT_KEY );
		delete_transient( self::KIT_TRANSIENT_KEY );
	}

	private function get_directory_hash() {
		$hash = '';
		$base_dir = wp_normalize_path( ELONIX_ACC_PATH . 'templates/' );
		if ( file_exists( $base_dir ) && is_readable( $base_dir ) ) {
			$hash .= filemtime( $base_dir );
			$types = array( 'pages', 'sections', 'headers', 'footers', 'archive', 'single', 'search', '404', 'popup', 'kits' );
			foreach ( $types as $type ) {
				$type_dir = wp_normalize_path( $base_dir . $type . '/' );
				if ( file_exists( $type_dir ) && is_readable( $type_dir ) ) {
					$hash .= filemtime( $type_dir );
				}
			}
		}
		return md5( $hash );
	}
}
