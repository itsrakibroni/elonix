<?php
/**
 * Elonix Shortcode System for Header & Footer Builder
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Shortcodes {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Register shortcodes on init
		add_action( 'init', array( $this, 'register_shortcodes' ) );

		// Enable shortcode processing in widgets and block widgets
		add_filter( 'widget_text', 'do_shortcode' );
		add_filter( 'widget_block_content', 'do_shortcode' );
	}

	/**
	 * Register Shortcodes for Header and Footer.
	 */
	public function register_shortcodes() {
		if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'header_builder' ) ) {
			add_shortcode( 'elonix_header', array( $this, 'es_header_shortcode_handler' ) );
		}
		if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'footer_builder' ) ) {
			add_shortcode( 'elonix_footer', array( $this, 'es_footer_shortcode_handler' ) );
		}
	}

	/**
	 * Shortcode handler for Header templates.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered Elementor content.
	 */
	public function es_header_shortcode_handler( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'elonix_header'
		);

		return self::render_template( $atts['id'], 'elonix_header' );
	}

	/**
	 * Shortcode handler for Footer templates.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered Elementor content.
	 */
	public function es_footer_shortcode_handler( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'elonix_footer'
		);

		return self::render_template( $atts['id'], 'elonix_footer' );
	}

	/**
	 * Safe Elementor layout template rendering engine.
	 *
	 * @param int    $template_id   Template Post ID.
	 * @param string $expected_type Expected template type ('elonix_header' or 'elonix_footer').
	 * @return string Rendered content.
	 */
	public static function render_template( $template_id, $expected_type = '' ) {
		$template_id = intval( $template_id );
		if ( ! $template_id ) {
			return '';
		}

		// Security: Validate post existence
		$post = get_post( $template_id );
		if ( ! $post ) {
			return '';
		}

		// Security: Validate correct post types
		$allowed_post_types = array( 'elonix_header', 'elonix_footer' );
		if ( ! in_array( $post->post_type, $allowed_post_types, true ) ) {
			return '';
		}

		// Security: Validate expected type match
		if ( ! empty( $expected_type ) && $post->post_type !== $expected_type ) {
			return '';
		}

		// Security: Prevent rendering of drafts or trashed templates
		if ( 'publish' !== $post->post_status ) {
			return '';
		}

		// Elementor check
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return '';
		}

		// Recursion Prevention
		static $rendered_shortcode_templates = array();
		if ( in_array( $template_id, $rendered_shortcode_templates, true ) ) {
			return ''; // Prevent infinite loops
		}
		$rendered_shortcode_templates[] = $template_id;

		// Enqueue template CSS/assets so it renders exactly as designed in Elementor
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
				\Elementor\Plugin::$instance->frontend->enqueue_styles();
			}
			$css_file = new \Elementor\Core\Files\CSS\Post( $template_id );
			$css_file->enqueue();
		}

		// Enqueue module specific assets for header builder compatibility
		if ( 'elonix_header' === $post->post_type ) {
			if ( class_exists( 'Elonix_Toolkit_Assets_Manager' ) ) {
				Elonix_Toolkit_Assets_Manager::enqueue_module_assets( 'header_builder' );
			}
		}

		// Fetch the builder content
		$content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id );

		if ( 'elonix_header' === $post->post_type ) {
			$content = '<header class="es-site-header">' . $content . '</header>';
		}

		// Remove from stack after render
		$key = array_search( $template_id, $rendered_shortcode_templates, true );
		if ( false !== $key ) {
			unset( $rendered_shortcode_templates[ $key ] );
		}

		return $content;
	}

	/**
	 * Helper function to recursively replace shortcode ID references in metadata/arrays.
	 *
	 * @param mixed $array  Input array or string.
	 * @param int   $old_id Original template ID.
	 * @param int   $new_id Duplicated/Imported template ID.
	 * @return mixed Replaced array/string.
	 */
	public static function array_replace_shortcode_ids( $array, $old_id, $new_id ) {
		if ( ! is_array( $array ) ) {
			return $array;
		}
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				$array[ $key ] = self::array_replace_shortcode_ids( $value, $old_id, $new_id );
			} elseif ( is_string( $value ) ) {
				$value = str_replace( '[elonix_header id="' . $old_id . '"]', '[elonix_header id="' . $new_id . '"]', $value );
				$value = str_replace( '[elonix_footer id="' . $old_id . '"]', '[elonix_footer id="' . $new_id . '"]', $value );
				// Also support escaped quotes if in JSON
				$value         = str_replace( '[elonix_header id=\\"' . $old_id . '\\"]', '[elonix_header id=\\"' . $new_id . '\\"]', $value );
				$value         = str_replace( '[elonix_footer id=\\"' . $old_id . '\\"]', '[elonix_footer id=\\"' . $new_id . '\\"]', $value );
				$array[ $key ] = $value;
			}
		}
		return $array;
	}

	/**
	 * Update shortcode references when a template is duplicated.
	 *
	 * @param int $post_id     Original layout ID.
	 * @param int $new_post_id Duplicated layout ID.
	 */
	public static function update_shortcode_references_on_duplicate( $post_id, $new_post_id ) {
		$post = get_post( $new_post_id );
		if ( ! $post ) {
			return;
		}

		// Update post content
		$content = $post->post_content;
		$content = str_replace( '[elonix_header id="' . $post_id . '"]', '[elonix_header id="' . $new_post_id . '"]', $content );
		$content = str_replace( '[elonix_footer id="' . $post_id . '"]', '[elonix_footer id="' . $new_post_id . '"]', $content );
		wp_update_post(
			array(
				'ID'           => $new_post_id,
				'post_content' => $content,
			)
		);

		// Update post metadata (e.g., _elementor_data)
		$meta = get_post_meta( $new_post_id );
		foreach ( $meta as $key => $values ) {
			foreach ( $values as $value ) {
				$val = maybe_unserialize( $value );
				// Skip modifying Elementor keys to preserve their content exactly without str_replace
				if ( strpos( $key, '_elementor_' ) !== 0 ) {
					if ( is_string( $val ) ) {
						$val = str_replace( '[elonix_header id="' . $post_id . '"]', '[elonix_header id="' . $new_post_id . '"]', $val );
						$val = str_replace( '[elonix_footer id="' . $post_id . '"]', '[elonix_footer id="' . $new_post_id . '"]', $val );
						$val = str_replace( '[elonix_header id=\\"' . $post_id . '\\"]', '[elonix_header id=\\"' . $new_post_id . '\\"]', $val );
						$val = str_replace( '[elonix_footer id=\\"' . $post_id . '\\"]', '[elonix_footer id=\\"' . $new_post_id . '\\"]', $val );
					} elseif ( is_array( $val ) ) {
						$val = self::array_replace_shortcode_ids( $val, $post_id, $new_post_id );
					}
				}
				$val = wp_slash( $val );
				update_post_meta( $new_post_id, $key, $val );
			}
		}
	}

	/**
	 * Update shortcode references when a template is imported.
	 *
	 * @param int $original_id     Original layout ID.
	 * @param int $imported_post_id Imported layout ID.
	 */
	public static function update_shortcode_references_on_import( $original_id, $imported_post_id ) {
		if ( ! $original_id || ! $imported_post_id ) {
			return;
		}
		self::update_shortcode_references_on_duplicate( $original_id, $imported_post_id );
	}
}

/**
 * Global helper function to render custom Header layouts in templates.
 *
 * @param int $template_id Header template ID.
 */
if ( ! function_exists( 'elonix_render_header' ) ) {
	function elonix_render_header( $template_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_template() uses Elementor's own trusted rendering API internally.
		echo Elonix_Shortcodes::render_template( $template_id, 'elonix_header' );
	}
}

/**
 * Global helper function to render custom Footer layouts in templates.
 *
 * @param int $template_id Footer template ID.
 */
if ( ! function_exists( 'elonix_render_footer' ) ) {
	function elonix_render_footer( $template_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_template() uses Elementor's own trusted rendering API internally.
		echo Elonix_Shortcodes::render_template( $template_id, 'elonix_footer' );
	}
}
