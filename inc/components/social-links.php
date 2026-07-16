<?php
/**
 * Theme Social Links Component (Fully Dynamic)
 *
 * @package ElonixToolkit
 * @since 1.0.0
 * @version 3.0.0
 * @author Creative RakibRoni
 *
 * Usage:
 * tv_socialLinks_controls( $this, 'my_social', __( 'Social Links Settings', 'elonix' ), [
 *     'condition' => [ 'header_style' => 'style1' ]
 * ]);
 *
 * Render:
 * tv_render_socialLinks( $settings, 'my_social' );
 */

namespace Elementor;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Social Links Controls
 */
if ( ! function_exists( 'tv_socialLinks_controls' ) ) :
	function tv_socialLinks_controls( $widget, $prefix = 'social', $section_label = 'Social Links Settings', $args = array() ) {

		// Default arguments
		$default_args = array(
			'condition' => array(),
		);

		// Merge user arguments
		$args = wp_parse_args( $args, $default_args );

		// ==================== Content Section ====================
		$widget->start_controls_section(
			$prefix . '_content_section',
			array(
				'label'     => esc_html( $section_label ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => $args['condition'],
			)
		);

		$widget->add_control(
			$prefix . '_show_social_links',
			array(
				'label'        => esc_html__( 'Show Social Links', 'elonix' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$widget->add_control(
			$prefix . '_source',
			array(
				'label'     => esc_html__( 'Social Links Source', 'elonix' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'theme_function',
				'options'   => array(
					'theme_function' => esc_html__( 'Theme Function', 'elonix' ),
					'custom'         => esc_html__( 'Custom Links', 'elonix' ),
				),
				'condition' => array(
					$prefix . '_show_social_links' => 'yes',
				),
			)
		);

		// Custom Social Links Repeater
		$social_repeater = new Repeater();

		$social_repeater->add_control(
			'social_icon',
			array(
				'label'   => esc_html__( 'Icon', 'elonix' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fab fa-facebook-f',
					'library' => 'fab',
				),
			)
		);

		$social_repeater->add_control(
			'social_link',
			array(
				'label'       => esc_html__( 'Link', 'elonix' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
				'default'     => array(
					'url' => '#',
				),
			)
		);

		$widget->add_control(
			$prefix . '_links_list',
			array(
				'label'       => esc_html__( 'Social Links', 'elonix' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $social_repeater->get_controls(),
				'default'     => array(
					array(
						'social_icon' => array(
							'value'   => 'fab fa-facebook-f',
							'library' => 'fab',
						),
						'social_link' => array(
							'url' => '#',
						),
					),
					array(
						'social_icon' => array(
							'value'   => 'fab fa-twitter',
							'library' => 'fab',
						),
						'social_link' => array(
							'url' => '#',
						),
					),
					array(
						'social_icon' => array(
							'value'   => 'fab fa-instagram',
							'library' => 'fab',
						),
						'social_link' => array(
							'url' => '#',
						),
					),
				),
				'title_field' => '{{{ social_icon.value }}}',
				'condition'   => array(
					$prefix . '_show_social_links' => 'yes',
					$prefix . '_source'            => 'custom',
				),
			)
		);

		$widget->end_controls_section();

		// ==================== Style Section ====================
		$widget->start_controls_section(
			$prefix . '_style_section',
			array(
				'label'     => esc_html( $section_label, 'elonix' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => $args['condition'],
				array(
					$prefix . '_show_social_links' => 'yes',
				),

			)
		);

		// Social Links Style
		$widget->add_control(
			$prefix . '_links_heading',
			array(
				'label'     => esc_html__( 'Social Links', 'elonix' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$widget->add_control(
			$prefix . '_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .social-icon-one li a' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_control(
			$prefix . '_icon_hover_color',
			array(
				'label'     => esc_html__( 'Icon Hover Color', 'elonix' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .social-icon-one li a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$widget->add_responsive_control(
			$prefix . '_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .social-icon-one li a' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$widget->add_responsive_control(
			$prefix . '_icon_spacing',
			array(
				'label'      => esc_html__( 'Icon Spacing', 'elonix' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .social-icon-one li:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$widget->end_controls_section();
	}
endif;

/**
 * Render Social Links HTML
 */
if ( ! function_exists( 'tv_render_socialLinks' ) ) :
	function tv_render_socialLinks( $settings, $prefix = 'social' ) {
		$show_key   = $prefix . '_show_social_links';
		$source_key = $prefix . '_source';
		$links_key  = $prefix . '_links_list';

		if ( empty( $settings[ $show_key ] ) || $settings[ $show_key ] !== 'yes' ) {
			return;
		}
		?>
		<?php if ( $settings[ $source_key ] === 'theme_function' && function_exists( 'elonix_social_link' ) ) : ?>
			<?php elonix_social_link(); ?>
		<?php elseif ( $settings[ $source_key ] === 'custom' && ! empty( $settings[ $links_key ] ) ) : ?>
			<?php
			foreach ( $settings[ $links_key ] as $social ) :
				$social_url      = ! empty( $social['social_link']['url'] ) ? $social['social_link']['url'] : '#';
				$social_target   = ! empty( $social['social_link']['is_external'] ) ? ' target="_blank"' : '';
				$social_nofollow = ! empty( $social['social_link']['nofollow'] ) ? ' rel="nofollow"' : '';
				?>
				<li>
					<a href="<?php echo esc_url( $social_url ); ?>"<?php echo $social_target . $social_nofollow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php Icons_Manager::render_icon( $social['social_icon'], array( 'aria-hidden' => 'true' ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php
	}
endif;
