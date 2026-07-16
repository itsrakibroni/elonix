<?php
/**
 * Elonix Extension Injector
 *
 * Centralizes Elementor hook management for all extensions.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Extension_Injector {

	private static $_instance = null;
	private $extensions = [];

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		// Control Hooks
		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/element/section/section_advanced/after_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/element/column/section_advanced/after_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_controls' ], 10, 2 );

		// Render Hooks
		add_action( 'elementor/frontend/before_render', [ $this, 'before_render' ], 10, 1 );

		// Visibility Filters
		add_filter( 'elementor/frontend/widget/should_render', [ $this, 'should_render' ], 10, 2 );
		add_filter( 'elementor/frontend/section/should_render', [ $this, 'should_render' ], 10, 2 );
		add_filter( 'elementor/frontend/column/should_render', [ $this, 'should_render' ], 10, 2 );
		add_filter( 'elementor/frontend/container/should_render', [ $this, 'should_render' ], 10, 2 );

		// Assets Hook
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function register_extension( Elonix_Base_Extension $extension ) {
		$this->extensions[ $extension->get_name() ] = $extension;
	}

	public function register_controls( $element, $args ) {
		foreach ( $this->extensions as $extension ) {
			$extension->register_controls( $element, $args );
		}
	}

	public function before_render( $element ) {
		foreach ( $this->extensions as $extension ) {
			$extension->before_render( $element );
		}
	}

	public function should_render( $should_render, $element ) {
		foreach ( $this->extensions as $extension ) {
			$should_render = $extension->should_render( $should_render, $element );
		}
		return $should_render;
	}

	public function enqueue_assets() {
		foreach ( $this->extensions as $extension ) {
			$extension->enqueue_assets();
		}
	}
}
