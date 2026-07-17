<?php
/**
 * Elonix Style Manager
 * 
 * Reusable Elementor UI helper for consistent styling across all Elonix – Toolkit for Elementor widgets.
 * Exclusively provides static methods to register standard Elementor controls.
 * 
 * NEVER inspects widget instance data.
 * NEVER intercepts the Elementor lifecycle.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Style_Manager {

	public static function get_typography_preset( $prefix, $selector ) {
		return [
			'name'     => $prefix . '_typography',
			'selector' => $selector,
		];
	}

	public static function register_card_style( \Elementor\Widget_Base $widget, $section_id, $prefix, $selector, $wrapper = '{{WRAPPER}} .elonix-feature-cards', $condition = [] ) {
		$widget->start_controls_section( $section_id, [ 
			'label'     => esc_html__( 'Card', 'elonix' ), 
			'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
			'condition' => $condition
		] );

		$widget->start_controls_tabs( $section_id . '_tabs' );
		
		$widget->start_controls_tab( $section_id . '_normal', [ 'label' => esc_html__( 'Normal', 'elonix' ) ] );
		$widget->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => $prefix . '_bg', 'selector' => $selector, 'fields_options' => [ 'background' => [ 'label' => 'Background' ] ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => $prefix . '_border', 'selector' => $selector ] );
		$widget->add_responsive_control( $prefix . '_border_radius', [ 'label' => esc_html__( 'Border Radius', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => [ 'px', '%', 'em' ], 'selectors' => [ $wrapper => '--es-fc-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => $prefix . '_box_shadow', 'selector' => $selector ] );
		$widget->add_control( $prefix . '_opacity', [ 'label' => esc_html__( 'Opacity', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'max' => 1, 'min' => 0.10, 'step' => 0.01 ] ], 'selectors' => [ $selector => 'opacity: {{SIZE}};' ] ] );
		$widget->add_control( $prefix . '_transition', [ 'label' => esc_html__( 'Transition Duration (s)', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'max' => 3, 'step' => 0.1 ] ], 'selectors' => [ $wrapper => '--es-fc-transition: all {{SIZE}}s ease-in-out;' ] ] );
		$widget->end_controls_tab();

		$widget->start_controls_tab( $section_id . '_hover', [ 'label' => esc_html__( 'Hover', 'elonix' ) ] );
		$widget->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => $prefix . '_bg_hover', 'selector' => $selector . ':hover' ] );
		$widget->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => $prefix . '_border_hover', 'selector' => $selector . ':hover' ] );
		$widget->add_responsive_control( $prefix . '_border_radius_hover', [ 'label' => esc_html__( 'Border Radius', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => [ 'px', '%', 'em' ], 'selectors' => [ $selector . ':hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => $prefix . '_box_shadow_hover', 'selector' => $selector . ':hover' ] );
		$widget->end_controls_tab();

		$widget->start_controls_tab( $section_id . '_active', [ 'label' => esc_html__( 'Active', 'elonix' ) ] );
		$widget->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => $prefix . '_bg_active', 'selector' => $selector . ':active' ] );
		$widget->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => $prefix . '_box_shadow_active', 'selector' => $selector . ':active' ] );
		$widget->end_controls_tab();

		$widget->end_controls_tabs();
		$widget->end_controls_section();
	}

	public static function register_title_style( \Elementor\Widget_Base $widget, $section_id, $prefix, $selector, $hover_selector, $condition = [] ) {
		$widget->start_controls_section( $section_id, [ 
			'label'     => esc_html__( 'Title', 'elonix' ), 
			'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
			'condition' => $condition
		] );

		$widget->start_controls_tabs( $section_id . '_tabs' );

		$widget->start_controls_tab( $section_id . '_normal', [ 'label' => esc_html__( 'Normal', 'elonix' ) ] );
		$widget->add_group_control( \Elementor\Group_Control_Typography::get_type(), self::get_typography_preset( $prefix, $selector ) );
		$widget->add_control( $prefix . '_color', [ 'label' => esc_html__( 'Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ $selector => 'color: {{VALUE}};' ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Text_Shadow::get_type(), [ 'name' => $prefix . '_text_shadow', 'selector' => $selector ] );
		$widget->add_responsive_control( $prefix . '_margin', [ 'label' => esc_html__( 'Margin', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => [ 'px', '%', 'em' ], 'selectors' => [ $selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$widget->end_controls_tab();

		$widget->start_controls_tab( $section_id . '_hover', [ 'label' => esc_html__( 'Hover', 'elonix' ) ] );
		$widget->add_control( $prefix . '_color_hover', [ 'label' => esc_html__( 'Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ $hover_selector => 'color: {{VALUE}};' ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Text_Shadow::get_type(), [ 'name' => $prefix . '_text_shadow_hover', 'selector' => $hover_selector ] );
		$widget->end_controls_tab();
		
		$widget->start_controls_tab( $section_id . '_active', [ 'label' => esc_html__( 'Active', 'elonix' ) ] );
		$widget->add_control( $prefix . '_color_active', [ 'label' => esc_html__( 'Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ str_replace(':hover', ':active', $hover_selector) => 'color: {{VALUE}};' ] ] );
		$widget->end_controls_tab();

		$widget->end_controls_tabs();
		$widget->end_controls_section();
	}
	
	public static function register_icon_style( \Elementor\Widget_Base $widget, $section_id, $prefix, $selector, $hover_selector, $wrapper = '{{WRAPPER}} .elonix-feature-cards', $condition = [] ) {
		$widget->start_controls_section( $section_id, [ 
			'label'     => esc_html__( 'Icon', 'elonix' ), 
			'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
			'condition' => $condition
		] );

		$widget->start_controls_tabs( $section_id . '_tabs' );

		$widget->start_controls_tab( $section_id . '_normal', [ 'label' => esc_html__( 'Normal', 'elonix' ) ] );
		$widget->add_responsive_control( $prefix . '_size', [ 'label' => esc_html__( 'Size', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => 10, 'max' => 200 ] ], 'selectors' => [ $wrapper => '--es-fc-icon-size: {{SIZE}}{{UNIT}};' ] ] );
		$widget->add_control( $prefix . '_color', [ 'label' => esc_html__( 'Color / Fill', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ $selector => 'color: {{VALUE}}; fill: {{VALUE}};' ] ] );
		$widget->add_control( $prefix . '_stroke', [ 'label' => esc_html__( 'SVG Stroke', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ $selector . ' svg' => 'stroke: {{VALUE}};' ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => $prefix . '_bg', 'selector' => $selector ] );
		$widget->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => $prefix . '_border', 'selector' => $selector ] );
		$widget->add_responsive_control( $prefix . '_radius', [ 'label' => esc_html__( 'Border Radius', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => [ 'px', '%', 'em' ], 'selectors' => [ $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$widget->add_responsive_control( $prefix . '_padding', [ 'label' => esc_html__( 'Padding', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => [ 'px', '%', 'em' ], 'selectors' => [ $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => $prefix . '_box_shadow', 'selector' => $selector ] );
		$widget->end_controls_tab();

		$widget->start_controls_tab( $section_id . '_hover', [ 'label' => esc_html__( 'Hover', 'elonix' ) ] );
		$widget->add_control( $prefix . '_color_hover', [ 'label' => esc_html__( 'Color / Fill', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ $hover_selector => 'color: {{VALUE}}; fill: {{VALUE}};' ] ] );
		$widget->add_control( $prefix . '_stroke_hover', [ 'label' => esc_html__( 'SVG Stroke', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ $hover_selector . ' svg' => 'stroke: {{VALUE}};' ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => $prefix . '_bg_hover', 'selector' => $hover_selector ] );
		$widget->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => $prefix . '_border_hover', 'selector' => $hover_selector ] );
		$widget->add_responsive_control( $prefix . '_rotate_hover', [ 'label' => esc_html__( 'Rotate', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => -360, 'max' => 360 ] ], 'selectors' => [ $hover_selector => 'transform: rotate({{SIZE}}deg);' ] ] );
		$widget->end_controls_tab();
		
		$widget->start_controls_tab( $section_id . '_active', [ 'label' => esc_html__( 'Active', 'elonix' ) ] );
		$widget->add_control( $prefix . '_color_active', [ 'label' => esc_html__( 'Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ str_replace(':hover', ':active', $hover_selector) => 'color: {{VALUE}};' ] ] );
		$widget->end_controls_tab();

		$widget->end_controls_tabs();
		$widget->end_controls_section();
	}

	public static function register_badge_style( \Elementor\Widget_Base $widget, $section_id, $prefix, $selector, $hover_selector, $condition = [] ) {
		$widget->start_controls_section( $section_id, [ 
			'label'     => esc_html__( 'Badge', 'elonix' ), 
			'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
			'condition' => $condition
		] );

		$widget->start_controls_tabs( $section_id . '_tabs' );
		
		$widget->start_controls_tab( $section_id . '_normal', [ 'label' => esc_html__( 'Normal', 'elonix' ) ] );
		$widget->add_group_control( \Elementor\Group_Control_Typography::get_type(), self::get_typography_preset( $prefix, $selector ) );
		$widget->add_control( $prefix . '_color', [ 'label' => esc_html__( 'Text Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ $selector => 'color: {{VALUE}};' ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => $prefix . '_bg', 'selector' => $selector ] );
		$widget->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => $prefix . '_border', 'selector' => $selector ] );
		$widget->add_responsive_control( $prefix . '_radius', [ 'label' => esc_html__( 'Border Radius', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => [ 'px', '%', 'em' ], 'selectors' => [ $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$widget->add_responsive_control( $prefix . '_padding', [ 'label' => esc_html__( 'Padding', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => [ 'px', '%', 'em' ], 'selectors' => [ $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => $prefix . '_box_shadow', 'selector' => $selector ] );
		$widget->end_controls_tab();

		$widget->start_controls_tab( $section_id . '_hover', [ 'label' => esc_html__( 'Hover', 'elonix' ) ] );
		$widget->add_control( $prefix . '_color_hover', [ 'label' => esc_html__( 'Text Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ $hover_selector => 'color: {{VALUE}};' ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => $prefix . '_bg_hover', 'selector' => $hover_selector ] );
		$widget->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => $prefix . '_border_hover', 'selector' => $hover_selector ] );
		$widget->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [ 'name' => $prefix . '_box_shadow_hover', 'selector' => $hover_selector ] );
		$widget->end_controls_tab();

		$widget->end_controls_tabs();
		$widget->end_controls_section();
	}

	public static function register_button_style( \Elementor\Widget_Base $widget, $section_id, $prefix, $selector, $condition = [] ) {
		$widget->start_controls_section( $section_id, [ 
			'label'     => esc_html__( 'Button', 'elonix' ), 
			'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
			'condition' => $condition
		] );

		$widget->start_controls_tabs( $section_id . '_tabs' );
		
		$widget->start_controls_tab( $section_id . '_normal', [ 'label' => esc_html__( 'Normal', 'elonix' ) ] );
		$widget->add_group_control( \Elementor\Group_Control_Typography::get_type(), self::get_typography_preset( $prefix, $selector ) );
		$widget->add_control( $prefix . '_color', [ 'label' => esc_html__( 'Text Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ $selector => 'color: {{VALUE}};' ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => $prefix . '_bg', 'selector' => $selector ] );
		$widget->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => $prefix . '_border', 'selector' => $selector ] );
		$widget->add_responsive_control( $prefix . '_radius', [ 'label' => esc_html__( 'Border Radius', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => [ 'px', '%', 'em' ], 'selectors' => [ $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		$widget->add_responsive_control( $prefix . '_padding', [ 'label' => esc_html__( 'Padding', 'elonix' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => [ 'px', '%', 'em' ], 'selectors' => [ $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ] ] );
		
		// Icon specific controls for button
		$widget->add_responsive_control( $prefix . '_icon_size', [ 'label' => esc_html__( 'Icon Size', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => 5, 'max' => 100 ] ], 'selectors' => [ $selector . '-icon i' => 'font-size: {{SIZE}}{{UNIT}};' ] ] );
		$widget->add_responsive_control( $prefix . '_icon_gap', [ 'label' => esc_html__( 'Icon Gap', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => 0, 'max' => 50 ] ], 'selectors' => [ $selector . '-icon-before' => 'margin-right: {{SIZE}}{{UNIT}};', $selector . '-icon-after' => 'margin-left: {{SIZE}}{{UNIT}};' ] ] );
		$widget->end_controls_tab();

		$widget->start_controls_tab( $section_id . '_hover', [ 'label' => esc_html__( 'Hover', 'elonix' ) ] );
		$widget->add_control( $prefix . '_color_hover', [ 'label' => esc_html__( 'Text Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ $selector . ':hover' => 'color: {{VALUE}};' ] ] );
		$widget->add_group_control( \Elementor\Group_Control_Background::get_type(), [ 'name' => $prefix . '_bg_hover', 'selector' => $selector . ':hover' ] );
		$widget->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => $prefix . '_border_hover', 'selector' => $selector . ':hover' ] );
		$widget->add_control( $prefix . '_transition', [ 'label' => esc_html__( 'Transition', 'elonix' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'max' => 3, 'step' => 0.1 ] ], 'selectors' => [ $selector => 'transition: all {{SIZE}}s ease-in-out;' ] ] );
		$widget->end_controls_tab();
		
		$widget->start_controls_tab( $section_id . '_active', [ 'label' => esc_html__( 'Active', 'elonix' ) ] );
		$widget->add_control( $prefix . '_color_active', [ 'label' => esc_html__( 'Text Color', 'elonix' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => [ $selector . ':active' => 'color: {{VALUE}};' ] ] );
		$widget->end_controls_tab();

		$widget->end_controls_tabs();
		$widget->end_controls_section();
	}
}
