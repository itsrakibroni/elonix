<?php
/**
 * Elonix – Toolkit for Elementor Advanced Button Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Button_Widget extends Elonix_Widget_Base {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'tv-button';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Button', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_tv_widget_icon() {
		return 'eicon-button';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_tv_widget_keywords() {
		return array( 'button', 'link', 'cta', 'action' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-tv-button' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// Content Section - Button Settings
		$this->start_controls_section(
			'section_button',
			array(
				'label' => esc_html__( 'Button Settings', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		// Register content fields from the Reusable Engine
		Elonix_Button_Helper::register_button_content_controls( $this );

		// Alignment Control
		$this->add_responsive_control(
			'align',
			array(
				'label'     => esc_html__( 'Alignment', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array(
						'title' => esc_html__( 'Left', 'elonix' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => esc_html__( 'Center', 'elonix' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'   => array(
						'title' => esc_html__( 'Right', 'elonix' ),
						'icon'  => 'eicon-text-align-right',
					),
					'justify' => array(
						'title' => esc_html__( 'Justified', 'elonix' ),
						'icon'  => 'eicon-text-align-justify',
					),
				),
				'default'   => 'left',
				'selectors' => array(
					'{{WRAPPER}} .elonix-advanced-button-wrapper' => 'text-align: {{VALUE}};',
				),
				'separator' => 'before',
			)
		);

		$this->end_controls_section();

		// Register style sections from the Reusable Engine dynamically
		Elonix_Button_Helper::register_button_style_controls( $this );
	}

	/**
	 * Render button widget output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="elonix-advanced-button-wrapper">
			<?php Elonix_Button_Helper::render_button_html( $this, $settings ); ?>
		</div>
		<?php
	}
}
