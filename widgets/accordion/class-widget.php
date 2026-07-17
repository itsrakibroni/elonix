<?php
/**
 * Elonix – Toolkit for Elementor Accordion Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Accordion_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'es-accordion';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Accordion', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_es_widget_icon() {
		return 'eicon-accordion';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_es_widget_keywords() {
		return array( 'accordion', 'faq', 'toggle', 'eskit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-es-accordion' );
	}

	/**
	 * Retrieve widget script dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_script_depends() {
		return array( 'elonix-widget-es-accordion' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// Register content fields dynamically from the Reusable Helper Engine
		Elonix_Accordion_Helper::register_accordion_content_controls( $this );

		// Register style controls dynamically from the Reusable Helper Engine
		Elonix_Accordion_Helper::register_accordion_style_controls( $this );
	}

	/**
	 * Render accordion widget output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		Elonix_Accordion_Helper::render_accordion_html( $this, $settings );
	}
}
