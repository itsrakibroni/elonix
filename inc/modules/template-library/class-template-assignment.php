<?php
namespace Elonix_Toolkit\Modules\Template_Library;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assignment {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Assigns an Elementor library template to a Elonix – Toolkit for Elementor Builder.
	 * 
	 * @param int $elementor_post_id
	 * @param string $type
	 * @param bool $make_active
	 * @return int|\WP_Error
	 */
	public function create_builder_assignment( $elementor_post_id, $type, $make_active = false ) {
		$type = Type_Normalizer::normalize_template_type( $type );
		$post_type_map = array(
			'header'  => 'tv_header',
			'footer'  => 'tv_footer',
			'archive' => 'tv_archive',
			'single'  => 'tv_single',
			'search'  => 'tv_search_template',
			'popup'   => 'tv_popup',
		);

		if ( ! isset( $post_type_map[ $type ] ) ) {
			return new \WP_Error( 'unsupported_type', esc_html__( 'Template type cannot be assigned to a builder.', 'elonix' ) );
		}

		$target_cpt = $post_type_map[ $type ];
		
		if ( ! post_type_exists( $target_cpt ) ) {
			return new \WP_Error( 'cpt_missing', esc_html__( 'Target builder module is not enabled.', 'elonix' ) );
		}

		$source_post = get_post( $elementor_post_id );
		if ( ! $source_post ) {
			return new \WP_Error( 'source_missing', esc_html__( 'Imported Elementor template not found.', 'elonix' ) );
		}

		$builder_post_id = wp_insert_post( array(
			'post_title'  => $source_post->post_title,
			'post_status' => 'publish',
			'post_type'   => $target_cpt,
		) );

		if ( is_wp_error( $builder_post_id ) ) {
			return $builder_post_id;
		}

		// Copy Elementor meta keys seamlessly without duplication of logic
		$elementor_metas = array( 
			'_elementor_data', 
			'_elementor_edit_mode', 
			'_elementor_template_type', 
			'_elementor_version', 
			'_elementor_pro_version', 
			'_wp_elementor_css', 
			'_elementor_page_settings' 
		);

		foreach ( $elementor_metas as $meta_key ) {
			$meta_value = get_post_meta( $elementor_post_id, $meta_key, true );
			if ( ! empty( $meta_value ) ) {
				update_post_meta( $builder_post_id, $meta_key, wp_slash( $meta_value ) );
			}
		}

		if ( $make_active ) {
			if ( class_exists( 'Elonix_Assignment_Engine' ) ) {
				$default_rule = 'entire_site';
				if ( 'archive' === $type ) {
					$default_rule = 'basic-archives';
				} elseif ( 'single' === $type ) {
					$default_rule = 'basic-singulars';
				} elseif ( 'search' === $type ) {
					$default_rule = 'special-search';
				} elseif ( 'popup' === $type ) {
					$default_rule = 'entire_site'; // TBD
				}
				
				\Elonix_Assignment_Engine::instance()->assign_template(
					$builder_post_id,
					array( $default_rule ), // Include
					array(), // Exclude
					10, // Priority
					true // Active
				);
			} else {
				update_post_meta( $builder_post_id, '_tv_target_include_locations', array( 'entire_site' ) );
			}
		}

		// Log History using User Meta, not Custom Table
		$user_id = get_current_user_id();
		$history = get_user_meta( $user_id, 'tv_imported_templates', true );
		if ( ! is_array( $history ) ) {
			$history = array();
		}

		$history[] = array(
			'id'              => $elementor_post_id,
			'builder_post_id' => $builder_post_id,
			'type'            => $type,
			'timestamp'       => time(),
		);

		update_user_meta( $user_id, 'tv_imported_templates', $history );

		return $builder_post_id;
	}
}
