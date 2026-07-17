<?php
/**
 * Elonix – Toolkit for Elementor Advanced Image Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Image_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'es-image';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Image', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_es_widget_icon() {
		return 'eicon-image';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_es_widget_keywords() {
		return array( 'image', 'photo', 'media', 'gallery' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-es-image' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// Content Section - Image
		$this->start_controls_section(
			'section_image',
			array(
				'label' => esc_html__( 'Image', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'image',
			array(
				'label'   => esc_html__( 'Choose Image', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => array(
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'image',
				'default' => 'large',
			)
		);

		$this->add_control(
			'alt_override',
			array(
				'label'       => esc_html__( 'Alt Text Override', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter custom alt text', 'elonix' ),
				'dynamic'     => array(
					'active' => true,
				),
			)
		);

		$this->add_control(
			'caption_type',
			array(
				'label'   => esc_html__( 'Caption', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'       => esc_html__( 'None', 'elonix' ),
					'attachment' => esc_html__( 'Attachment Caption', 'elonix' ),
					'custom'     => esc_html__( 'Custom Caption', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'custom_caption',
			array(
				'label'       => esc_html__( 'Custom Caption', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter custom caption text', 'elonix' ),
				'dynamic'     => array(
					'active' => true,
				),
				'condition'   => array(
					'caption_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'link_type',
			array(
				'label'   => esc_html__( 'Link Type', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'   => esc_html__( 'None', 'elonix' ),
					'file'   => esc_html__( 'Media File', 'elonix' ),
					'custom' => esc_html__( 'Custom URL', 'elonix' ),
				),
			)
		);

		$this->add_control(
			'link',
			array(
				'label'       => esc_html__( 'Link URL', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
				'dynamic'     => array(
					'active' => true,
				),
				'condition'   => array(
					'link_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'custom_rel',
			array(
				'label'       => esc_html__( 'Custom Rel', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. sponsor, nofollow', 'elonix' ),
				'condition'   => array(
					'link_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'lazy_load',
			array(
				'label'        => esc_html__( 'Lazy Loading', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'elonix' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'elonix' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'elonix' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .elonix-advanced-image-wrapper' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		// Style Section - Image
		$this->start_controls_section(
			'section_image_style',
			array(
				'label' => esc_html__( 'Image Styles', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'width',
			array(
				'label'      => esc_html__( 'Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 1000,
					),
					'%'  => array(
						'min' => 1,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-advanced-image-wrapper img' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'max_width',
			array(
				'label'      => esc_html__( 'Max Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 1000,
					),
					'%'  => array(
						'min' => 1,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-advanced-image-wrapper img' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'height',
			array(
				'label'      => esc_html__( 'Height', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-advanced-image-wrapper img' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'object_fit',
			array(
				'label'     => esc_html__( 'Object Fit', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'fill',
				'options'   => array(
					'fill'    => esc_html__( 'Fill', 'elonix' ),
					'cover'   => esc_html__( 'Cover', 'elonix' ),
					'contain' => esc_html__( 'Contain', 'elonix' ),
					'none'    => esc_html__( 'None', 'elonix' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-advanced-image-wrapper img' => 'object-fit: {{VALUE}};',
				),
			)
		);

		// Style Tabs for Normal & Hover Opacity / Filter States
		$this->start_controls_tabs( 'tabs_image_states' );

		// Normal State
		$this->start_controls_tab(
			'tab_image_normal',
			array(
				'label' => esc_html__( 'Normal', 'elonix' ),
			)
		);

		$this->add_control(
			'opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0.1,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-advanced-image-wrapper img' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .elonix-advanced-image-wrapper img',
			)
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_image_hover',
			array(
				'label' => esc_html__( 'Hover', 'elonix' ),
			)
		);

		$this->add_control(
			'hover_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0.1,
						'step' => 0.01,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elonix-advanced-image-wrapper img:hover' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'hover_css_filters',
				'selector' => '{{WRAPPER}} .elonix-advanced-image-wrapper img:hover',
			)
		);

		$this->add_control(
			'hover_effect',
			array(
				'label'   => esc_html__( 'Hover Zoom/Transition Effect', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'      => esc_html__( 'None', 'elonix' ),
					'zoom-in'   => esc_html__( 'Zoom In', 'elonix' ),
					'zoom-out'  => esc_html__( 'Zoom Out', 'elonix' ),
					'scale'     => esc_html__( 'Scale Up', 'elonix' ),
					'rotate'    => esc_html__( 'Rotate', 'elonix' ),
					'grayscale' => esc_html__( 'Grayscale', 'elonix' ),
					'blur'      => esc_html__( 'Blur', 'elonix' ),
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Borders & Radii Controls
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'      => 'border',
				'selector'  => '{{WRAPPER}} .elonix-advanced-image-wrapper img',
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-advanced-image-wrapper img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .elonix-advanced-image-wrapper img',
			)
		);

		$this->end_controls_section();

		// Style Section - Caption
		$this->start_controls_section(
			'section_caption_style',
			array(
				'label'     => esc_html__( 'Caption Styles', 'elonix' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'caption_type!' => 'none',
				),
			)
		);

		$this->add_control(
			'caption_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elonix-image-caption' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'caption_typography',
				'selector' => '{{WRAPPER}} .elonix-image-caption',
			)
		);

		$this->add_responsive_control(
			'caption_margin',
			array(
				'label'      => esc_html__( 'Margin', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .elonix-image-caption' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['image']['url'] ) && empty( $settings['image']['id'] ) ) {
			return;
		}

		$image_id  = ! empty( $settings['image']['id'] ) ? $settings['image']['id'] : 0;
		$image_url = ! empty( $settings['image']['url'] ) ? $settings['image']['url'] : '';

		// Add Wrapper Classes
		$this->add_render_attribute( 'wrapper', 'class', 'elonix-advanced-image-wrapper' );
		if ( ! empty( $settings['hover_effect'] ) && 'none' !== $settings['hover_effect'] ) {
			$this->add_render_attribute( 'wrapper', 'class', 'elonix-effect-' . $settings['hover_effect'] );
		}

		// Configure Image Tag Alt, Title and loading Attributes
		$alt = '';
		if ( ! empty( $settings['alt_override'] ) ) {
			$alt = $settings['alt_override'];
		} elseif ( $image_id ) {
			$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		}

		$title = $image_id ? get_the_title( $image_id ) : '';

		$attr = array(
			'alt'   => esc_attr( $alt ),
			'title' => esc_attr( $title ),
		);

		if ( ! empty( $settings['lazy_load'] ) && 'yes' === $settings['lazy_load'] ) {
			$attr['loading'] = 'lazy';
		} else {
			$attr['loading'] = 'eager';
		}

		// Resolve Link Options
		$link_url = '';
		if ( 'file' === $settings['link_type'] ) {
			$link_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : $image_url;
		} elseif ( 'custom' === $settings['link_type'] && ! empty( $settings['link']['url'] ) ) {
			$link_url = $settings['link']['url'];
		}

		if ( ! empty( $link_url ) ) {
			$this->add_render_attribute( 'link', 'href', esc_url( $link_url ) );

			if ( 'custom' === $settings['link_type'] ) {
				if ( ! empty( $settings['link']['is_external'] ) ) {
					$this->add_render_attribute( 'link', 'target', '_blank' );
				}
				if ( ! empty( $settings['link']['nofollow'] ) ) {
					$this->add_render_attribute( 'link', 'rel', 'nofollow' );
				}
				if ( ! empty( $settings['custom_rel'] ) ) {
					$this->add_render_attribute( 'link', 'rel', esc_attr( $settings['custom_rel'] ) );
				}
			}
		}

		// Resolve Caption Options
		$caption = '';
		if ( 'attachment' === $settings['caption_type'] && $image_id ) {
			$attachment = get_post( $image_id );
			if ( $attachment && ! empty( $attachment->post_excerpt ) ) {
				$caption = $attachment->post_excerpt;
			}
		} elseif ( 'custom' === $settings['caption_type'] && ! empty( $settings['custom_caption'] ) ) {
			$caption = $settings['custom_caption'];
		}
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php if ( ! empty( $link_url ) ) : ?>
				<a <?php $this->print_render_attribute_string( 'link' ); ?>>
			<?php endif; ?>

			<?php
			if ( $image_id ) {
				$size = \Elementor\Group_Control_Image_Size::get_attachment_image_html( $settings, 'image' );
				// If customized size was selected, print output via elementor size resolver
				if ( ! empty( $size ) ) {
					// We pass our attributes to get_attachment_image_html
					echo wp_kses_post( \Elementor\Group_Control_Image_Size::get_attachment_image_html( $settings, 'image', $attr ) );
				} else {
					echo wp_get_attachment_image( $image_id, 'large', false, $attr );
				}
			} else {
				echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $title ) . '" loading="' . esc_attr( $attr['loading'] ) . '" />';
			}
			?>

			<?php if ( ! empty( $link_url ) ) : ?>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $caption ) ) : ?>
				<div class="elonix-image-caption">
					<?php echo esc_html( $caption ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
