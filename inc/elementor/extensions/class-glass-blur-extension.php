<?php
/**
 * Elonix Glass Blur Extension
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Glass_Blur_Extension extends Elonix_Base_Extension {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_name() {
		return 'glass_blur';
	}

	public function register_controls( $element, $args ) {
		$element->start_controls_section(
			'elonix_glass_blur_section',
			[
				'label' => esc_html__( 'Elonix Glass Blur', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'es_glass_blur_enable',
			[
				'label'        => esc_html__( 'Enable Glass Blur', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			]
		);

		$element->add_responsive_control(
			'es_glass_blur_amount',
			[
				'label'      => esc_html__( 'Blur', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 15,
					'unit' => 'px',
				],
				'condition'  => [
					'es_glass_blur_enable' => 'yes',
				],
				'selectors'  => [
					'{{WRAPPER}}.es-glass-blur-element' => '-webkit-backdrop-filter: blur({{SIZE}}{{UNIT}}); backdrop-filter: blur({{SIZE}}{{UNIT}});',
				],
			]
		);

		// FUTURE EXPANSION ARCHITECTURE PREPARATION
		// Below is space reserved for future native controls to be appended
		// natively following the same selectors and conditions.
		//
		// - Background Opacity
		// - Background Color
		// - Border
		// - Border Radius
		// - Backdrop Saturation
		// - Backdrop Brightness
		// - Glass Shadow
		// - Noise Overlay
		// - Frosted Glass Presets

		$element->end_controls_section();
	}

	public function before_render( $element ) {
		$settings = $element->get_settings_for_display();

		if ( empty( $settings['es_glass_blur_enable'] ) || 'yes' !== $settings['es_glass_blur_enable'] ) {
			return;
		}

		// Inject class for CSS targeting
		$element->add_render_attribute( '_wrapper', 'class', 'es-glass-blur-element' );
	}
}
