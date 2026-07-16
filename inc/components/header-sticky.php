<?php
/**
 * Theme Header Sticky Component (Fully Dynamic)
 *
 * @package ElonixToolkit
 * @since 1.0.0
 * @version 3.0.0
 * @author Creative RakibRoni
 *
 * Usage: tv_headerSticky_controls( $this, 'my_headersticky', __( 'Header Sticky Settings', 'elonix' ));
 * Render: tv_render_headerSticky( $settings, 'my_headersticky' );
 */

namespace Elementor;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Header Sticky Controls
 */
if ( ! function_exists( 'tv_headerSticky_controls' ) ) :
	function tv_headerSticky_controls( $widget, $prefix = 'sticky', $section_label = 'Header Sticky Settings' ) {

		// Header Sticky Section
		$widget->start_controls_section(
			$prefix . '_section',
			array(
				'label' => esc_html( $section_label ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		// Enable Sticky Header
		$widget->add_control(
			$prefix . '_enable',
			array(
				'label'        => esc_html__( 'Enable Sticky Header', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		// Logo Image
		$widget->add_control(
			$prefix . '_logo',
			array(
				'label'     => esc_html__( 'Sticky Logo', 'elonix' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		// Menu Selection
		$menus        = wp_get_nav_menus();
		$menu_options = array();
		foreach ( $menus as $menu ) {
			$menu_options[ $menu->term_id ] = $menu->name;
		}

		$widget->add_control(
			$prefix . '_menu',
			array(
				'label'     => esc_html__( 'Select Menu', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $menu_options,
				'default'   => ! empty( $menu_options ) ? array_keys( $menu_options )[0] : '',
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		// Container Width
		$widget->add_control(
			$prefix . '_container_width',
			array(
				'label'     => esc_html__( 'Container Width', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'container'       => esc_html__( 'Container', 'elonix' ),
					'container-fluid' => esc_html__( 'Container Fluid', 'elonix' ),
				),
				'default'   => 'container',
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		// Show Mobile Toggle
		$widget->add_control(
			$prefix . '_show_mobile_toggle',
			array(
				'label'        => esc_html__( 'Show Mobile Toggle', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		// Sticky Background Color
		$widget->add_control(
			$prefix . '_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sticky-header' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		$widget->end_controls_section();

		// ============ STYLE TAB ============

		// Sticky Header Style Section
		$widget->start_controls_section(
			$prefix . '_style_section',
			array(
				'label'     => esc_html__( 'Sticky Header Style', 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					$prefix . '_enable' => 'yes',
				),
			)
		);

		// Background
		$widget->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => $prefix . '_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .sticky-header',
			)
		);

		// Box Shadow
		$widget->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => $prefix . '_box_shadow',
				'selector' => '{{WRAPPER}} .sticky-header',
			)
		);

		// Border
		$widget->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => $prefix . '_border',
				'selector' => '{{WRAPPER}} .sticky-header',
			)
		);

		// Padding
		$widget->add_responsive_control(
			$prefix . '_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .sticky-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Margin
		$widget->add_responsive_control(
			$prefix . '_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .sticky-header' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Logo Style Section
		$widget->add_control(
			$prefix . '_logo_style_section',
			array(
				'label'     => esc_html__( 'Logo Style', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Logo Width
		$widget->add_responsive_control(
			$prefix . '_logo_width',
			array(
				'label'      => esc_html__( 'Logo Width', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 50,
						'max' => 500,
					),
					'%'  => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .sticky-header .stickyheader-logo img' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Logo Padding
		$widget->add_responsive_control(
			$prefix . '_logo_padding',
			array(
				'label'      => esc_html__( 'Logo Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .sticky-header .stickyheader-logo' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Menu Style Section
		$widget->add_control(
			$prefix . '_menu_style_section',
			array(
				'label'     => esc_html__( 'Menu Style', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Menu Tabs
		$widget->start_controls_tabs( $prefix . '_menu_tabs' );

		// Normal Tab
		$widget->start_controls_tab(
			$prefix . '_menu_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		// Menu Color
		$widget->add_control(
			$prefix . '_menu_color',
			array(
				'label'     => esc_html__( 'Menu Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sticky-header .main-menu ul.navigation > li > a' => 'color: {{VALUE}};',
				),
			)
		);

		// Menu Background
		$widget->add_control(
			$prefix . '_menu_bg',
			array(
				'label'     => esc_html__( 'Menu Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sticky-header .main-menu ul.navigation > li > a' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_tab();

		// Hover Tab
		$widget->start_controls_tab(
			$prefix . '_menu_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		// Menu Hover Color
		$widget->add_control(
			$prefix . '_menu_hover_color',
			array(
				'label'     => esc_html__( 'Menu Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sticky-header .main-menu ul.navigation > li > a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .sticky-header .main-menu ul.navigation > li.current > a' => 'color: {{VALUE}};',
				),
			)
		);

		// Menu Hover Background
		$widget->add_control(
			$prefix . '_menu_hover_bg',
			array(
				'label'     => esc_html__( 'Menu Hover Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sticky-header .main-menu ul.navigation > li > a:hover' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .sticky-header .main-menu ul.navigation > li.current > a' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_tab();
		$widget->end_controls_tabs();

		// Menu Typography
		$widget->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => $prefix . '_menu_typography',
				'label'     => esc_html__( 'Menu Typography', 'elonix' ),
				'selector'  => '{{WRAPPER}} .sticky-header .main-menu ul.navigation > li > a',
				'separator' => 'before',
			)
		);

		// Menu Spacing
		$widget->add_responsive_control(
			$prefix . '_menu_spacing',
			array(
				'label'      => esc_html__( 'Menu Item Spacing', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .sticky-header .main-menu ul.navigation > li' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Menu Padding
		$widget->add_responsive_control(
			$prefix . '_menu_padding',
			array(
				'label'      => esc_html__( 'Menu Item Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .sticky-header .main-menu ul.navigation > li > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Mobile Toggle Style Section
		$widget->add_control(
			$prefix . '_toggle_style_section',
			array(
				'label'     => esc_html__( 'Mobile Toggle Style', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Toggle Tabs
		$widget->start_controls_tabs( $prefix . '_toggle_tabs' );

		// Normal Tab
		$widget->start_controls_tab(
			$prefix . '_toggle_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		// Toggle Line Color
		$widget->add_control(
			$prefix . '_toggle_color',
			array(
				'label'     => esc_html__( 'Toggle Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sticky-header .menu-toggle .line' => 'background-color: {{VALUE}};',
				),
			)
		);

		// Toggle Background
		$widget->add_control(
			$prefix . '_toggle_bg',
			array(
				'label'     => esc_html__( 'Toggle Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sticky-header .menu-toggle' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_tab();

		// Hover Tab
		$widget->start_controls_tab(
			$prefix . '_toggle_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		// Toggle Hover Color
		$widget->add_control(
			$prefix . '_toggle_hover_color',
			array(
				'label'     => esc_html__( 'Toggle Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sticky-header .menu-toggle:hover .line' => 'background-color: {{VALUE}};',
				),
			)
		);

		// Toggle Hover Background
		$widget->add_control(
			$prefix . '_toggle_hover_bg',
			array(
				'label'     => esc_html__( 'Toggle Hover Background', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sticky-header .menu-toggle:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$widget->end_controls_tab();
		$widget->end_controls_tabs();

		// Toggle Size
		$widget->add_responsive_control(
			$prefix . '_toggle_size',
			array(
				'label'      => esc_html__( 'Toggle Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 20,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .sticky-header .menu-toggle' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		// Toggle Padding
		$widget->add_responsive_control(
			$prefix . '_toggle_padding',
			array(
				'label'      => esc_html__( 'Toggle Padding', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .sticky-header .menu-toggle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Toggle Border Radius
		$widget->add_responsive_control(
			$prefix . '_toggle_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .sticky-header .menu-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$widget->end_controls_section();
	}
endif;

/**
 * Render Header Sticky HTML
 */
if ( ! function_exists( 'tv_render_headerSticky' ) ) :
	function tv_render_headerSticky( $settings, $prefix = 'sticky' ) {

		// Check if sticky header is enabled
		if ( empty( $settings[ $prefix . '_enable' ] ) || $settings[ $prefix . '_enable' ] !== 'yes' ) {
			return;
		}

		// Get settings
		$sticky_logo     = ! empty( $settings[ $prefix . '_logo' ]['url'] ) ? $settings[ $prefix . '_logo' ]['url'] : '';
		$sticky_menu     = ! empty( $settings[ $prefix . '_menu' ] ) ? $settings[ $prefix . '_menu' ] : '';
		$container_class = ! empty( $settings[ $prefix . '_container_width' ] ) ? $settings[ $prefix . '_container_width' ] : 'container';
		$show_mobile     = ! empty( $settings[ $prefix . '_show_mobile_toggle' ] ) && $settings[ $prefix . '_show_mobile_toggle' ] === 'yes';

		?>
		<div class="sticky-header">
			<div class="<?php echo esc_attr( $container_class ); ?>">
				<!-- Main Menu Area -->
				<div class="menu-area">
					<div class="row align-items-center justify-content-between">
						<div class="col-auto logo">
							<div class="stickyheader-logo">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
									<?php if ( ! empty( $sticky_logo ) ) : ?>
										<img src="<?php echo esc_url( $sticky_logo ); ?>" alt="<?php bloginfo( 'name' ); ?>">
									<?php else : ?>
										<h2><?php bloginfo( 'name' ); ?></h2>
									<?php endif; ?>
								</a>
							</div>
						</div>
						<div class="col-auto nav-menu">
							<nav class="main-menu d-none d-lg-inline-block">
								<ul class="navigation clearfix">
									<?php
									$primary_nav_arg = array(
										'menu'           => $sticky_menu,
										'theme_location' => 'primary',
										'container'      => false,
										'menu_class'     => '',
										'depth'          => 3,
										'walker'         => class_exists( 'Elonix_Bootstrap_Navwalker' ) ? new \Elonix_Bootstrap_Navwalker() : '',
										'fallback_cb'    => class_exists( 'Elonix_Bootstrap_Navwalker' ) ? 'Elonix_Bootstrap_Navwalker::fallback' : '',
										'items_wrap'     => '%3$s',
									);

									if ( ! empty( $menu ) || has_nav_menu( 'primary' ) ) {
										wp_nav_menu( $primary_nav_arg );
									}
									?>
								</ul>
							</nav>
							<?php if ( $show_mobile ) : ?>
								<div class="navbar-right d-inline-flex d-lg-none">
									<button class="menu-toggle sidebar-btn" type="button">
										<span class="line"></span>
										<span class="line"></span>
										<span class="line"></span>
									</button>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
endif;
