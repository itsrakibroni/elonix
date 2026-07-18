<?php
/**
 * Elonix – Toolkit for Elementor Elementor Error Code Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Error_Code_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'es-error-code';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Error Code', 'elonix' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_es_widget_icon() {
		return 'eicon-number-field';
	}

	/**
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @return array Widget keywords.
	 */
	public function get_es_widget_keywords() {
		return array( 'error', 'code', 'number', '404', '410', 'status' );
	}

	/**
	 * Detect current status/error code dynamically.
	 *
	 * @return int Status code.
	 */
	protected function get_current_error_code() {
		// Mock inside Elementor Editor / Preview modes
		if ( class_exists( '\Elementor\Plugin' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				$send_410 = ( 'yes' === ( Elonix_Settings::get( 'es_404_send_410_header' ) ?? 'no' )  ) || ( 'yes' === ( Elonix_Settings::get( 'es_404_seo_410_header' ) ?? 'no' )  );
				if ( $send_410 ) {
					return 410;
				}
				$custom_status = intval( Elonix_Settings::get( 'es_404_custom_status_code' ) ?? 404  );
				return $custom_status ? $custom_status : 404;
			}
		}

		// Detect standard HTTP Status Header
		$response_code = http_response_code();
		if ( $response_code && in_array( $response_code, array( 400, 401, 403, 404, 410, 429, 500, 502, 503, 504 ), true ) ) {
			return $response_code;
		}

		// Fallback to active query
		if ( is_404() ) {
			$send_410 = ( 'yes' === ( Elonix_Settings::get( 'es_404_send_410_header' ) ?? 'no' )  ) || ( 'yes' === ( Elonix_Settings::get( 'es_404_seo_410_header' ) ?? 'no' )  );
			if ( $send_410 ) {
				return 410;
			}
			$custom_status = intval( Elonix_Settings::get( 'es_404_custom_status_code' ) ?? 404  );
			return $custom_status ? $custom_status : 404;
		}

		return 404; // Final default fallback
	}

	/**
	 * Register the widget controls.
	 */
	protected function register_controls() {

		// Content Section
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'Configuration', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'effect_watermark',
			array(
				'label'        => esc_html__( 'Enable Watermark Style', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'effect_outline',
			array(
				'label'        => esc_html__( 'Outline Text Effect', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'effect_glass',
			array(
				'label'        => esc_html__( 'Glassmorphism Effect', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'effect_floating',
			array(
				'label'        => esc_html__( 'Floating Animation', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'effect_pulse',
			array(
				'label'        => esc_html__( 'Pulse Animation', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->end_controls_section();

		// Style Section
		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Typography & Styling', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'code_typography',
				'selector' => '{{WRAPPER}} .es-error-code',
			)
		);

		$this->add_control(
			'code_color',
			array(
				'label'     => esc_html__( 'Text Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1f2937',
				'selectors' => array(
					'{{WRAPPER}} .es-error-code' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'effect_outline!' => 'yes',
				),
			)
		);

		// Gradient Support
		$this->add_control(
			'enable_gradient',
			array(
				'label'        => esc_html__( 'Text Gradient Color', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'elonix' ),
				'label_off'    => esc_html__( 'Off', 'elonix' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'effect_outline!' => 'yes',
				),
			)
		);

		$this->add_control(
			'gradient_color_a',
			array(
				'label'     => esc_html__( 'Gradient Color A', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#3b82f6',
				'condition' => array(
					'enable_gradient' => 'yes',
					'effect_outline!' => 'yes',
				),
			)
		);

		$this->add_control(
			'gradient_color_b',
			array(
				'label'     => esc_html__( 'Gradient Color B', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ef4444',
				'condition' => array(
					'enable_gradient' => 'yes',
					'effect_outline!' => 'yes',
				),
			)
		);

		$this->add_control(
			'gradient_angle',
			array(
				'label'      => esc_html__( 'Gradient Angle', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 360,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 135,
				),
				'condition'  => array(
					'enable_gradient' => 'yes',
					'effect_outline!' => 'yes',
				),
			)
		);

		// Text Stroke (Outline control when outline is active/not active)
		$this->add_control(
			'stroke_size',
			array(
				'label'      => esc_html__( 'Stroke Width', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 10,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 2,
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-error-code' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}}; text-stroke-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'stroke_color',
			array(
				'label'     => esc_html__( 'Stroke Color', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ef4444',
				'selectors' => array(
					'{{WRAPPER}} .es-error-code' => '-webkit-text-stroke-color: {{VALUE}}; text-stroke-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'code_shadow',
				'selector' => '{{WRAPPER}} .es-error-code',
			)
		);

		$this->add_control(
			'code_opacity',
			array(
				'label'      => esc_html__( 'Opacity', 'elonix' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0.1,
						'max'  => 1.0,
						'step' => 0.05,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 1,
				),
				'selectors'  => array(
					'{{WRAPPER}} .es-error-code' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$code     = $this->get_current_error_code();

		// Construct CSS animation classes dynamically
		$classes = array( 'es-error-code' );

		if ( 'yes' === $settings['effect_outline'] ) {
			$classes[] = 'effect-outline';
		}
		if ( 'yes' === $settings['effect_glass'] ) {
			$classes[] = 'effect-glass';
		}
		if ( 'yes' === $settings['effect_floating'] ) {
			$classes[] = 'effect-floating';
		}
		if ( 'yes' === $settings['effect_pulse'] ) {
			$classes[] = 'effect-pulse';
		}

		$wrapper_classes = array( 'es-error-code-wrapper' );
		if ( 'yes' === $settings['effect_watermark'] ) {
			$wrapper_classes[] = 'effect-watermark';
		}

		// Inject Inline Gradient styles if configured
		$gradient_style = '';
		if ( 'yes' === $settings['enable_gradient'] && 'yes' !== $settings['effect_outline'] ) {
			$angle          = ! empty( $settings['gradient_angle']['size'] ) ? intval( $settings['gradient_angle']['size'] ) : 135;
			$colora         = ! empty( $settings['gradient_color_a'] ) ? $settings['gradient_color_a'] : '#3b82f6';
			$colorb         = ! empty( $settings['gradient_color_b'] ) ? $settings['gradient_color_b'] : '#ef4444';
			$gradient_style = sprintf(
				'background: linear-gradient(%ddeg, %s 0%%, %s 100%%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; text-fill-color: transparent;',
				$angle,
				esc_attr( $colora ),
				esc_attr( $colorb )
			);
		}

		// Watermark static style overrides
		$wrapper_style = '';
		if ( 'yes' === $settings['effect_watermark'] ) {
			$wrapper_style = 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: -1; opacity: 0.15; pointer-events: none; width: auto; max-width: 100%; text-align: center;';
		}

		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" style="<?php echo esc_attr( $wrapper_style ); ?>">
			<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-hidden="true" style="<?php echo esc_attr( $gradient_style ); ?>">
				<?php echo esc_html( $code ); ?>
			</div>
			<span class="screen-reader-text">
				<?php
				/* translators: %d: Error code */
				echo esc_html( sprintf( __( 'Error code: %d', 'elonix' ), $code ) );
				?>
			</span>
		</div>
		<?php
	}
}
