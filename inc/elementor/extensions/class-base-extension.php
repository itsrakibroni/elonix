<?php
/**
 * Elonix Base Extension
 *
 * Base class for all Elonix Elementor Extensions.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Elonix_Base_Extension {

	public function __construct() {
		Elonix_Extension_Injector::instance()->register_extension( $this );
	}

	/**
	 * Get Extension Name
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Register Controls
	 *
	 * @param \Elementor\Element_Base $element The Elementor element instance.
	 * @param array $args Additional arguments.
	 */
	abstract public function register_controls( $element, $args );

	/**
	 * Before Render
	 *
	 * @param \Elementor\Element_Base $element The Elementor element instance.
	 */
	public function before_render( $element ) {}

	/**
	 * Should Render
	 *
	 * @param bool $should_render Whether the element should render.
	 * @param \Elementor\Element_Base $element The Elementor element instance.
	 * @return bool
	 */
	public function should_render( $should_render, $element ) {
		return $should_render;
	}

	/**
	 * Enqueue Assets
	 */
	public function enqueue_assets() {}

	/**
	 * Shared Utility: Check if Editor Mode
	 */
	protected function is_edit_mode() {
		if ( class_exists( '\Elementor\Plugin' ) ) {
			return \Elementor\Plugin::$instance->editor->is_edit_mode();
		}
		return false;
	}

	/**
	 * Shared Utility: Check if Preview Mode
	 */
	protected function is_preview_mode() {
		if ( class_exists( '\Elementor\Plugin' ) ) {
			return \Elementor\Plugin::$instance->preview->is_preview_mode();
		}
		return false;
	}

	/**
	 * Shared Utility: Apply Preview State Selector
	 *
	 * @param string $base_class The base class of the element.
	 * @param string $state_class The class representing the state to preview.
	 * @return string The modified selector for editor preview.
	 */
	protected function apply_preview_state( $base_class, $state_class ) {
		// In the editor, we need to force the sticky state for preview purposes.
		return '{{WRAPPER}}.' . $base_class . '.' . $state_class . ', {{WRAPPER}}.elementor-element.elementor-element-edit-mode .' . $base_class;
	}
}
