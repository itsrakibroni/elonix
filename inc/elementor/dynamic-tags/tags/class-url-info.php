<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Url_Info extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'url-info';
	}

	public function get_title() {
		return esc_html__( 'URL Info', 'elonix' );
	}

	public function get_group() {
		return 'elonix-url';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
	}

	protected function register_controls() {

		$this->add_control(
			'info_type',
			array(
				'label'   => esc_html__( 'Information', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'current'  => esc_html__( 'Current URL', 'elonix' ),
					'referrer' => esc_html__( 'Referrer URL', 'elonix' ),
					'previous' => esc_html__( 'Previous URL', 'elonix' ),
					'login'    => esc_html__( 'Login URL', 'elonix' ),
					'logout'   => esc_html__( 'Logout URL', 'elonix' ),
				),
				'default' => 'current',
			)
		);
	}
	public function render() {

		$settings  = $this->get_settings();
		$info_type = isset( $settings['info_type'] ) ? $settings['info_type'] : 'current';

		$value = '';
		switch ( $info_type ) {
			case 'current':
				$value = home_url( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' );
				break;
			case 'referrer':
				$value = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
				break;
			case 'previous':
				$value = 'javascript:history.back()';
				break;
			case 'login':
				$value = wp_login_url();
				break;
			case 'logout':
				$value = wp_logout_url();
				break;
		}
		$this->render_url( $value );
	}
}
