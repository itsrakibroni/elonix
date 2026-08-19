<?php
/**
 * Elonix – Toolkit for Elementor Advanced Icon Box Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Icon_Box_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'es-icon-box';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Icon Box', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_es_widget_icon() {
		return 'eicon-icon-box';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_es_widget_keywords() {
		return array( 'icon', 'box', 'card', 'feature', 'service', 'info' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-es-icon-box' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// Content Section - General
		$this->start_controls_section(
			'section_general',
			array(
				'label' => esc_html__( 'Icon Box Content', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'selected_icon',
			array(
				'label'            => esc_html__( 'Icon Selector', 'elonix' ),
				'type'             => \Elementor\Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => array(
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => esc_html__( 'Title Text', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Feature Title', 'elonix' ),
				'dynamic'     => array(
					'active' => true,
				),
				'placeholder' => esc_html__( 'Enter icon box title', 'elonix' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => esc_html__( 'Title Tag', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
				),
			)
		);

		$this->add_control(
			'description',
			array(
				'label'       => esc_html__( 'Description Text', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'Provide detailed information about this specific service, feature, or benefit.', 'elonix' ),
				'dynamic'     => array(
					'active' => true,
				),
				'placeholder' => esc_html__( 'Enter icon box description text', 'elonix' ),
				'rows'        => 4,
			)
		);

		$this->end_controls_section();

		// Content Section - Layout
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout Settings', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'layout_type',
			array(
				'label'   => esc_html__( 'Layout Style', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'top',
				'options' => array(
					'top'     => esc_html__( 'Top Icon (Centered)', 'elonix' ),
					'left'    => esc_html__( 'Left Icon', 'elonix' ),
					'right'   => esc_html__( 'Right Icon', 'elonix' ),
					'stacked' => esc_html__( 'Stacked Layout', 'elonix' ),
					'inline'  => esc_html__( 'Inline Layout', 'elonix' ),
				),
			)
		);

		$this->end_controls_section();

		// Button Section Integration - Reuse existing Button Component logic
		if ( function_exists( 'Elonix_Toolkit_Compat\es_button_controls' ) ) {
			\Elonix_Toolkit_Compat\es_button_controls( $this, 'btn', esc_html__( 'Button Settings', 'elonix' ) );
		}

		// Style Section - Icon Style
		$this->start_controls_section(
			'section_icon_style',
			array(
				'label' => esc_html__( 'Icon Style', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'     => esc_html__( 'Icon Size', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 150,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-icon-box-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elonix-icon-box-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Icon States Normal & Hover Tabs
		$this->start_controls_tabs( 'tabs_icon_style' );

		// Normal State
		$this->start_controls_tab(
			'tab_icon_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-icon-box-icon i'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .elonix-icon-box-icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-icon-box-icon' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_icon_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'icon_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-advanced-icon-box:hover .elonix-icon-box-icon i'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .elonix-advanced-icon-box:hover .elonix-icon-box-icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_hover_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-advanced-icon-box:hover .elonix-icon-box-icon' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Borders & Padding
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'      => 'icon_border',
				'selector'  => '{{WRAPPER}} .elonix-icon-box-icon',
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'icon_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-icon-box-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-icon-box-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Section - Title Style
		$this->start_controls_section(
			'section_title_style',
			array(
				'label' => esc_html__( 'Title', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-icon-box-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .elonix-icon-box-title',
			)
		);

		$this->add_responsive_control(
			'title_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-icon-box-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Section - Description Style
		$this->start_controls_section(
			'section_desc_style',
			array(
				'label' => esc_html__( 'Description', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'desc_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-icon-box-desc' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'desc_typography',
				'selector' => '{{WRAPPER}} .elonix-icon-box-desc',
			)
		);

		$this->add_responsive_control(
			'desc_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-icon-box-desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Section - Card Style
		$this->start_controls_section(
			'section_card_style',
			array(
				'label' => esc_html__( 'Card Container', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-advanced-icon-box' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .elonix-advanced-icon-box',
			)
		);

		$this->add_responsive_control(
			'card_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-advanced-icon-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => esc_html__( 'Padding', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-advanced-icon-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-advanced-icon-box' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Tabs for normal/hover card shadows
		$this->start_controls_tabs( 'tabs_card_shadow' );

		// Normal
		$this->start_controls_tab(
			'tab_card_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .elonix-advanced-icon-box',
			)
		);

		$this->end_controls_tab();

		// Hover
		$this->start_controls_tab(
			'tab_card_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_hover_box_shadow',
				'selector' => '{{WRAPPER}} .elonix-advanced-icon-box:hover',
			)
		);

		$this->add_control(
			'hover_effect',
			array(
				'label'   => esc_html__( 'Hover Effect', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'             => esc_html__( 'None', 'elonix' ),
					'lift'             => esc_html__( 'Lift (translate Y)', 'elonix' ),
					'scale'            => esc_html__( 'Scale Up', 'elonix' ),
					'rotate'           => esc_html__( 'Rotate', 'elonix' ),
					'glow'             => esc_html__( 'Glow shadow', 'elonix' ),
					'border-highlight' => esc_html__( 'Border Highlight', 'elonix' ),
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Render widget output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Add Class Attributes
		$this->add_render_attribute( 'card', 'class', 'elonix-advanced-icon-box' );
		$this->add_render_attribute( 'card', 'class', 'elonix-layout-' . $settings['layout_type'] );

		if ( ! empty( $settings['hover_effect'] ) && 'none' !== $settings['hover_effect'] ) {
			$this->add_render_attribute( 'card', 'class', 'elonix-hover-' . $settings['hover_effect'] );
		}

		// Title tag verification
		$title_tag    = ! empty( $settings['title_tag'] ) ? $settings['title_tag'] : 'h3';
		$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span' );

		if ( ! in_array( $title_tag, $allowed_tags, true ) ) {
			$title_tag = 'h3';
		}

		// Inline Editing Setup
		if ( ! empty( $settings['title'] ) ) {
			$this->add_render_attribute( 'title', 'class', 'elonix-icon-box-title' );
			$this->add_inline_editing_attributes( 'title', 'none' );
		}

		if ( ! empty( $settings['description'] ) ) {
			$this->add_render_attribute( 'description', 'class', 'elonix-icon-box-desc' );
			$this->add_inline_editing_attributes( 'description', 'basic' );
		}
		?>
		<div <?php $this->print_render_attribute_string( 'card' ); ?>>
			
			<?php if ( 'inline' === $settings['layout_type'] ) : ?>
				<div class="elonix-icon-box-header">
					<?php if ( ! empty( $settings['selected_icon']['value'] ) ) : ?>
						<div class="elonix-icon-box-icon">
							<?php \Elementor\Icons_Manager::render_icon( $settings['selected_icon'], array( 'aria-hidden' => 'true' ) ); ?>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $settings['title'] ) ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> <?php $this->print_render_attribute_string( 'title' ); ?>>
							<?php echo wp_kses_post( $settings['title'] ); ?>
						</<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
				</div>
				<div class="elonix-icon-box-content">
					<?php if ( ! empty( $settings['description'] ) ) : ?>
						<p <?php $this->print_render_attribute_string( 'description' ); ?>>
							<?php echo wp_kses_post( $settings['description'] ); ?>
						</p>
					<?php endif; ?>

					<?php if ( function_exists( 'Elonix_Toolkit_Compat\es_render_button' ) ) : ?>
						<div class="elonix-icon-box-button">
							<?php \Elonix_Toolkit_Compat\es_render_button( $settings, 'btn' ); ?>
						</div>
					<?php endif; ?>
				</div>

			<?php else : ?>

				<?php if ( ! empty( $settings['selected_icon']['value'] ) ) : ?>
					<div class="elonix-icon-box-icon">
						<?php \Elementor\Icons_Manager::render_icon( $settings['selected_icon'], array( 'aria-hidden' => 'true' ) ); ?>
					</div>
				<?php endif; ?>

				<div class="elonix-icon-box-content">
					<?php if ( ! empty( $settings['title'] ) ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> <?php $this->print_render_attribute_string( 'title' ); ?>>
							<?php echo wp_kses_post( $settings['title'] ); ?>
						</<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>

					<?php if ( ! empty( $settings['description'] ) ) : ?>
						<p <?php $this->print_render_attribute_string( 'description' ); ?>>
							<?php echo wp_kses_post( $settings['description'] ); ?>
						</p>
					<?php endif; ?>

					<?php if ( function_exists( 'Elonix_Toolkit_Compat\es_render_button' ) ) : ?>
						<div class="elonix-icon-box-button">
							<?php \Elonix_Toolkit_Compat\es_render_button( $settings, 'btn' ); ?>
						</div>
					<?php endif; ?>
				</div>

			<?php endif; ?>

		</div>
		<?php
	}
}
