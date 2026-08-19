<?php
/**
 * Elonix – Toolkit for Elementor Elementor Base Widget Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class Elonix_Widget_Base extends \Elementor\Widget_Base {

	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );
		add_action( 'elementor/element/parse_css', array( $this, 'parse_custom_css' ), 10, 2 );
	}

	public function parse_custom_css( $post_css, $element ) {
		if ( $element->get_name() !== $this->get_name() ) {
			return;
		}

		$settings = $element->get_settings();
		if ( empty( $settings['custom_css'] ) ) {
			return;
		}

		$css = $settings['custom_css'];

		// Elementor Dynamic_CSS objects don't use get_stylesheet()
		if ( $post_css instanceof \Elementor\Core\DynamicTags\Dynamic_CSS ) {
			return;
		}

		$css = str_replace( 'selector', $post_css->get_element_unique_selector( $element ), $css );

		$post_css->get_stylesheet()->add_raw_css( $css );
	}

	public function get_es_widget_category() {
		return 'elonix-widgets';
	}

	/**
	 * Retrieve the categories list the widget belongs to.
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		if ( class_exists( 'Elonix_Toolkit_Widget_Registry' ) ) {
			$widget_data = Elonix_Toolkit_Widget_Registry::instance()->get_widget( $this->get_name() );
			if ( $widget_data && ! empty( $widget_data['category'] ) ) {
				return array( $widget_data['category'] );
			}
		}
		return array( $this->get_es_widget_category() );
	}

	/**
	 * Retrieve default keywords list for searching the widget.
	 *
	 * @return array Search keywords.
	 */
	public function get_es_widget_keywords() {
		return array( 'elonix', 'toolkit' );
	}

	/**
	 * Retrieve search keywords for the widget.
	 *
	 * @return array Search keywords.
	 */
	public function get_keywords() {
		$default_keywords = array( 'elonix', 'Elonix', 'ESKIT', 'toolkit' );
		$custom_keywords  = $this->get_es_widget_keywords();
		return array_unique( array_merge( $default_keywords, $custom_keywords ) );
	}

	/**
	 * Retrieve default icon slug.
	 *
	 * @return string Default icon slug.
	 */
	public function get_es_widget_icon() {
		return 'eicon-code';
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @return string Icon class/slug.
	 */
	public function get_icon() {
		$categories     = $this->get_categories();
		$is_es_category = false;
		foreach ( $categories as $category ) {
			if ( strpos( $category, 'elonix-' ) === 0 ) {
				$is_es_category = true;
				break;
			}
		}

		if ( $is_es_category ) {
			return $this->get_es_widget_icon() . ' elonix-widget-icon';
		}
		return $this->get_es_widget_icon();
	}

	/**
	 * Retrieve the list of style dependencies the widget requires.
	 * Override in child classes for conditional, widget-specific style enqueues.
	 *
	 * @return array Handle dependencies.
	 */
	public function get_style_depends() {
		return array();
	}

	/**
	 * Retrieve the list of script dependencies the widget requires.
	 * Override in child classes for conditional, widget-specific script enqueues.
	 *
	 * @return array Handle dependencies.
	 */
	public function get_script_depends() {
		return array();
	}

	/**
	 * Helper method to register common style controls.
	 * Prepared for future shared settings (Typography, Color, Background, Border, etc.).
	 */
	protected function register_common_controls() {
		// Prepared for future shared Typography, Color, and Background controls
	}

	/**
	 * Helper method to register visibility toggle controls.
	 * Prepared for future visibility conditions / dynamic user roles.
	 */
	protected function register_visibility_controls() {
		// Prepared for future visibility settings
	}

	/**
	 * Helper method to register animation controls.
	 * Prepared for future motion effects and advanced element animations.
	 */
	protected function register_animation_controls() {
		// Prepared for future Motion Effects and enter animations
	}

	/**
	 * Helper method to register responsive controls.
	 * Prepared for future devices-specific viewport overrides.
	 */
	protected function register_responsive_controls() {
		// Prepared for future responsive breakpoints / layout toggles
	}

	/**
	 * Helper method to register custom CSS developer controls.
	 * Prepared for future layout custom style injections.
	 */
	protected function register_custom_css_controls() {
		// Prepared for future custom CSS overrides
	}

	/**
	 * Compatibility hook for dynamic tags integration.
	 */
	protected function prepare_dynamic_tags_compatibility() {
		// Prepared for future custom Elementor Dynamic Tags integration
	}

	/**
	 * Compatibility hook for WooCommerce integrations.
	 */
	protected function prepare_woocommerce_compatibility() {
		// Prepared for future WooCommerce widget elements support
	}

	/**
	 * Render heading output safely.
	 *
	 * @param array  $settings   Widget settings array.
	 * @param string $setting_key Setting name key.
	 * @param string $class       CSS class parameter.
	 */
	protected function render_heading( $settings, $setting_key, $class = '' ) {
		if ( empty( $settings[ $setting_key ] ) ) {
			return;
		}

		$tag          = ! empty( $settings[ $setting_key . '_tag' ] ) ? $settings[ $setting_key . '_tag' ] : 'h2';
		$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'span' );

		if ( ! in_array( $tag, $allowed_tags, true ) ) {
			$tag = 'h2';
		}

		$class_attr = ! empty( $class ) ? ' class="' . esc_attr( $class ) . '"' : '';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $tag is esc_attr()'d and whitelisted above, $class_attr is esc_attr()'d.
		echo '<' . esc_attr( $tag ) . $class_attr . '>';
		echo wp_kses_post( $settings[ $setting_key ] );
		echo '</' . esc_attr( $tag ) . '>';
	}

	/**
	 * Render button/link output safely.
	 *
	 * @param array  $settings   Widget settings array.
	 * @param string $setting_key Setting name key.
	 * @param string $class       CSS class parameter.
	 */
	protected function render_button( $settings, $setting_key, $class = '' ) {
		$button_text = ! empty( $settings[ $setting_key . '_text' ] ) ? $settings[ $setting_key . '_text' ] : '';
		$button_link = ! empty( $settings[ $setting_key . '_link' ] ) ? $settings[ $setting_key . '_link' ] : array();

		if ( empty( $button_text ) || empty( $button_link['url'] ) ) {
			return;
		}

		$class_attr = ! empty( $class ) ? ' class="' . esc_attr( $class ) . '"' : '';
		$target     = ! empty( $button_link['is_external'] ) ? ' target="_blank"' : '';
		$nofollow   = ! empty( $button_link['nofollow'] ) ? ' rel="nofollow"' : '';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- url is esc_url()'d; class_attr is esc_attr()'d; target/nofollow are fixed static strings.
		echo '<a href="' . esc_url( $button_link['url'] ) . '"' . $class_attr . $target . $nofollow . '>';
		echo esc_html( $button_text );
		echo '</a>';
	}

	/**
	 * Render responsive image output safely.
	 *
	 * @param array  $settings   Widget settings array.
	 * @param string $setting_key Setting name key.
	 * @param string $size        Image dimension slug (default: 'full').
	 * @param string $class       CSS class parameter.
	 */
	protected function render_image( $settings, $setting_key, $size = 'full', $class = '' ) {
		if ( empty( $settings[ $setting_key ]['id'] ) && empty( $settings[ $setting_key ]['url'] ) ) {
			return;
		}

		$image = $settings[ $setting_key ];

		if ( ! empty( $image['id'] ) ) {
			$attr = array();
			if ( ! empty( $class ) ) {
				$attr['class'] = $class;
			}
			echo wp_get_attachment_image( $image['id'], $size, false, $attr );
		} elseif ( ! empty( $image['url'] ) ) {
			$class_attr = ! empty( $class ) ? ' class="' . esc_attr( $class ) . '"' : '';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- url is esc_url()'d; class_attr is esc_attr()'d.
			echo '<img src="' . esc_url( $image['url'] ) . '" alt=""' . $class_attr . ' />';
		}
	}

	/**
	 * Render icons output safely (SVG / fonts).
	 *
	 * @param array  $settings   Widget settings array.
	 * @param string $setting_key Setting name key.
	 * @param string $class       CSS class parameter.
	 */
	protected function render_icon( $settings, $setting_key, $class = '' ) {
		if ( empty( $settings[ $setting_key ] ) ) {
			return;
		}

		$icon       = $settings[ $setting_key ];
		$class_attr = ! empty( $class ) ? ' class="' . esc_attr( $class ) . '"' : '';

		if ( is_array( $icon ) && ! empty( $icon['value'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- class_attr is esc_attr()'d; icon rendered via Elementor's trusted Icons_Manager below.
			echo '<span' . $class_attr . '>';
			\Elementor\Icons_Manager::render_icon( $icon, array( 'aria-hidden' => 'true' ) );
			echo '</span>';
		} elseif ( is_string( $icon ) ) {
			echo '<i class="' . esc_attr( $icon ) . ( ! empty( $class ) ? ' ' . esc_attr( $class ) : '' ) . '" aria-hidden="true"></i>';
		}
	}
}
