<?php
namespace Elonix_Toolkit\Modules\Template_Library;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			'elonix/v1',
			'/library/manifest',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_manifest' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'elonix/v1',
			'/library/import',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'import_template' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			'elonix/v1',
			'/library/assign',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'assign_template' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'elementor_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'type' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			'elonix/v1',
			'/library/conflicts',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'check_conflicts' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'type' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			'elonix/v1',
			'/library/kits',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_kits' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		register_rest_route(
			'elonix/v1',
			'/library/kits/import',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'import_kit_component' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'slug' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'component' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			'elonix/v1',
			'/library/kits/styles',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'import_kit_styles' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'slug' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	public function check_permission() {
		return current_user_can( 'edit_posts' );
	}

	public function get_manifest( \WP_REST_Request $request ) {
		$catalog = Cache::instance()->get_catalog();
		
		$user_id = get_current_user_id();
		$history = get_user_meta( $user_id, 'tv_imported_templates', true );
		if ( ! is_array( $history ) ) {
			$history = array();
		}
		
		// Map history for fast lookup
		$imported_ids = array();
		foreach ( $history as $record ) {
			if ( isset( $record['id'] ) ) {
				$imported_ids[ $record['id'] ] = true;
			}
		}
		
		foreach ( $catalog as &$item ) {
			if ( isset( $imported_ids[ $item['id'] ] ) ) {
				$item['import_status'] = 'Imported';
			} else {
				$item['import_status'] = 'Not Imported';
			}
		}
		
		return rest_ensure_response( $catalog );
	}

	public function import_template( \WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		$catalog = Cache::instance()->get_catalog();
		
		$template_meta = null;
		foreach ( $catalog as $item ) {
			if ( $item['id'] === $id ) {
				$template_meta = $item;
				break;
			}
		}

		if ( ! $template_meta ) {
			return new \WP_Error( 'not_found', esc_html__( 'Template not found in catalog.', 'elonix' ), array( 'status' => 404 ) );
		}

		// Dependency check strictly blocks import if core plugins are missing
		if ( ! empty( $template_meta['required_plugins'] ) ) {
			$missing = array();
			foreach ( $template_meta['required_plugins'] as $plugin ) {
				if ( 'woocommerce' === $plugin && ! class_exists( 'WooCommerce' ) ) {
					$missing[] = 'WooCommerce';
				}
				if ( 'elementor' === $plugin && ! class_exists( '\Elementor\Plugin' ) ) {
					$missing[] = 'Elementor';
				}
				if ( 'acf' === $plugin && ! class_exists( 'ACF' ) ) {
					$missing[] = 'Advanced Custom Fields';
				}
			}
			if ( ! empty( $missing ) ) {
				/* translators: %s: Comma-separated list of missing plugin names. */
				return new \WP_Error( 'missing_dependencies', sprintf( esc_html__( 'Missing required plugins: %s', 'elonix' ), implode( ', ', $missing ) ), array( 'status' => 422 ) );
			}
		}

		$result = Importer::instance()->import_local_template( $template_meta );
		if ( is_wp_error( $result ) ) {
			// Wrap it securely, don't expose local paths from importer explicitly.
			return new \WP_Error( 'import_error', $result->get_error_message(), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array(
			'success'     => true,
			'template_id' => $result,
			'message'     => esc_html__( 'Template imported successfully.', 'elonix' )
		) );
	}

	public function assign_template( \WP_REST_Request $request ) {
		$elementor_id = $request->get_param( 'elementor_id' );
		$type         = $request->get_param( 'type' );

		require_once ELONIX_ACC_PATH . 'inc/modules/template-library/class-template-assignment.php';

		$result = Assignment::instance()->create_builder_assignment( $elementor_id, $type, true );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error( 'assignment_failed', $result->get_error_message(), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array(
			'success'         => true,
			'builder_post_id' => $result,
			'edit_url'        => admin_url( 'post.php?post=' . $result . '&action=elementor' ),
			'message'         => esc_html__( 'Template assigned to Builder successfully.', 'elonix' )
		) );
	}

	public function check_conflicts( \WP_REST_Request $request ) {
		$type = $request->get_param( 'type' );
		$conflicts = array();
		
		if ( 'header' === $type || 'footer' === $type ) {
			$post_type = 'tv_' . $type;
			$query = new \WP_Query( array(
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required to find conflicting active headers or footers.
				'meta_query'     => array(
					array(
						'key'     => '_tv_target_include_locations',
						'compare' => 'EXISTS',
					)
				),
				'posts_per_page' => 1
			) );
			
			if ( $query->have_posts() ) {
				$conflicts[] = 'existing_active_' . $type;
			}
		}
		return rest_ensure_response( array(
			'success'   => true,
			'conflicts' => $conflicts
		) );
	}
	
	public function get_kits( \WP_REST_Request $request ) {
		$catalog = Cache::instance()->get_kit_catalog();
		return rest_ensure_response( $catalog );
	}

	public function import_kit_component( \WP_REST_Request $request ) {
		$slug      = $request->get_param( 'slug' );
		$component = $request->get_param( 'component' );
		$catalog   = Cache::instance()->get_kit_catalog();
		
		$kit_manifest = null;
		foreach ( $catalog as $kit ) {
			if ( $kit['slug'] === $slug ) {
				$kit_manifest = $kit;
				break;
			}
		}

		if ( ! $kit_manifest ) {
			return new \WP_Error( 'not_found', esc_html__( 'Kit not found.', 'elonix' ), array( 'status' => 404 ) );
		}

		require_once ELONIX_ACC_PATH . 'inc/modules/template-library/class-kit-importer.php';
		
		$result = Kit_Importer::instance()->import_component( $kit_manifest, $component );
		
		if ( is_wp_error( $result ) ) {
			return new \WP_Error( 'import_error', $result->get_error_message(), array( 'status' => 500 ) );
		}

		Kit_Importer::instance()->log_import( $slug, $component, $result );

		return rest_ensure_response( array(
			'success'      => true,
			'elementor_id' => $result,
			'message'      => esc_html__( 'Component imported successfully.', 'elonix' )
		) );
	}

	public function import_kit_styles( \WP_REST_Request $request ) {
		$slug    = $request->get_param( 'slug' );
		$catalog = Cache::instance()->get_kit_catalog();
		
		$kit_manifest = null;
		foreach ( $catalog as $kit ) {
			if ( $kit['slug'] === $slug ) {
				$kit_manifest = $kit;
				break;
			}
		}

		if ( ! $kit_manifest ) {
			return new \WP_Error( 'not_found', esc_html__( 'Kit not found.', 'elonix' ), array( 'status' => 404 ) );
		}

		require_once ELONIX_ACC_PATH . 'inc/modules/template-library/class-kit-importer.php';
		
		$result = Kit_Importer::instance()->import_global_styles( $kit_manifest );
		
		if ( is_wp_error( $result ) ) {
			return new \WP_Error( 'import_error', $result->get_error_message(), array( 'status' => 500 ) );
		}

		Kit_Importer::instance()->log_import( $slug, 'global_styles', 0 );

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Global Styles imported successfully.', 'elonix' )
		) );
	}
}
