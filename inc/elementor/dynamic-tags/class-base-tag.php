<?php
/**
 * Elonix – Toolkit for Elementor Dynamic Tag Base Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class Elonix_Dynamic_Tag extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get group.
	 *
	 * @return array
	 */
	public function get_group() {
		return array( 'elonix-post' );
	}

	/**
	 * Get categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	/**
	 * Get centralized Dynamic Data Engine.
	 *
	 * @return \Elonix_Dynamic_Data
	 */
	protected function get_dynamic_data() {
		return \Elonix_Dynamic_Data::instance();
	}

	/**
	 * Get post from context.
	 *
	 * @return \WP_Post|null
	 */
	protected function get_post() {
		return $this->get_dynamic_data()->get_current_post();
	}

	/**
	 * Get author from context.
	 *
	 * @return \WP_User|null
	 */
	protected function get_author() {
		return $this->get_dynamic_data()->get_current_author();
	}

	protected function register_advanced_section() {
		parent::register_advanced_section();

		$this->start_controls_section(
			'elonix_formatting',
			[
				'label' => esc_html__( 'Formatting', 'elonix' ),
			]
		);

		$this->add_control(
			'tv_prefix',
			[
				'label' => esc_html__( 'Prefix', 'elonix' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'tv_suffix',
			[
				'label' => esc_html__( 'Suffix', 'elonix' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'tv_default_value',
			[
				'label' => esc_html__( 'Default Value', 'elonix' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Used if value is truly empty (different from fallback).', 'elonix' ),
			]
		);

		$this->add_control(
			'tv_case',
			[
				'label' => esc_html__( 'Text Case', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__( 'Default', 'elonix' ),
					'uppercase' => esc_html__( 'Uppercase', 'elonix' ),
					'lowercase' => esc_html__( 'Lowercase', 'elonix' ),
					'capitalize' => esc_html__( 'Capitalize', 'elonix' ),
				],
				'default' => '',
			]
		);

		$this->add_control(
			'tv_trim',
			[
				'label' => esc_html__( 'Trim Whitespace', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'tv_strip_html',
			[
				'label' => esc_html__( 'Strip HTML', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'tv_strip_shortcodes',
			[
				'label' => esc_html__( 'Strip Shortcodes', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'tv_auto_p',
			[
				'label' => esc_html__( 'Auto Paragraph (wpautop)', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'tv_url_encode',
			[
				'label' => esc_html__( 'URL Formatting', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__( 'Default', 'elonix' ),
					'encode' => esc_html__( 'URL Encode', 'elonix' ),
					'decode' => esc_html__( 'URL Decode', 'elonix' ),
				],
				'default' => '',
			]
		);

		$this->add_control(
			'tv_json_encode',
			[
				'label' => esc_html__( 'JSON Encode', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'tv_number_format',
			[
				'label' => esc_html__( 'Number Format', 'elonix' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'description' => esc_html__( 'Format as a number with thousands separator.', 'elonix' ),
			]
		);

		$this->add_control(
			'tv_limit',
			[
				'label' => esc_html__( 'Character Limit', 'elonix' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Process formatting for any string value.
	 *
	 * @param string $value
	 * @return string
	 */
	protected function apply_formatting( $value ) {
		$settings = $this->get_settings();

		if ( empty( $value ) && '0' !== $value ) {
			if ( ! empty( $settings['tv_default_value'] ) ) {
				$value = $settings['tv_default_value'];
			} else {
				return '';
			}
		}

		if ( ! empty( $settings['tv_strip_html'] ) && 'yes' === $settings['tv_strip_html'] ) {
			$value = wp_strip_all_tags( $value );
		}

		if ( ! empty( $settings['tv_strip_shortcodes'] ) && 'yes' === $settings['tv_strip_shortcodes'] ) {
			$value = strip_shortcodes( $value );
		}

		if ( ! empty( $settings['tv_trim'] ) && 'yes' === $settings['tv_trim'] ) {
			$value = trim( $value );
		}

		if ( ! empty( $settings['tv_case'] ) ) {
			switch ( $settings['tv_case'] ) {
				case 'uppercase':
					$value = strtoupper( $value );
					break;
				case 'lowercase':
					$value = strtolower( $value );
					break;
				case 'capitalize':
					$value = ucwords( strtolower( $value ) );
					break;
			}
		}

		if ( ! empty( $settings['tv_number_format'] ) && 'yes' === $settings['tv_number_format'] ) {
			if ( is_numeric( $value ) ) {
				$value = number_format_i18n( (float) $value );
			}
		}

		if ( ! empty( $settings['tv_limit'] ) ) {
			$limit = absint( $settings['tv_limit'] );
			if ( mb_strlen( $value ) > $limit ) {
				$value = mb_substr( $value, 0, $limit ) . '&hellip;';
			}
		}

		if ( ! empty( $settings['tv_url_encode'] ) ) {
			if ( 'encode' === $settings['tv_url_encode'] ) {
				$value = urlencode( $value );
			} elseif ( 'decode' === $settings['tv_url_encode'] ) {
				$value = urldecode( $value );
			}
		}

		if ( ! empty( $settings['tv_json_encode'] ) && 'yes' === $settings['tv_json_encode'] ) {
			$value = wp_json_encode( $value );
		}

		if ( ! empty( $settings['tv_auto_p'] ) && 'yes' === $settings['tv_auto_p'] ) {
			$value = wpautop( $value );
		}

		if ( ! empty( $settings['tv_prefix'] ) ) {
			$value = $settings['tv_prefix'] . $value;
		}

		if ( ! empty( $settings['tv_suffix'] ) ) {
			$value .= $settings['tv_suffix'];
		}

		return apply_filters( 'elonix/dynamic_tags/apply_formatting', $value, $settings, $this );
	}

	/**
	 * Render text value safely.
	 *
	 * @param string $value
	 */
	protected function render_text( $value ) {
		$value = $this->apply_formatting( $value );
		if ( ! empty( $value ) || '0' === $value ) {
			echo wp_kses_post( $value );
		}
	}

	/**
	 * Render URL value safely.
	 *
	 * @param string $value
	 */
	protected function render_url( $value ) {
		$value = $this->apply_formatting( $value );
		if ( ! empty( $value ) || '0' === $value ) {
			echo esc_url( $value );
		}
	}

	/**
	 * Render value safely (alias for render_text for backwards compatibility).
	 *
	 * @param string $value
	 */
	protected function render_value( $value ) {
		$this->render_text( $value );
	}
}
