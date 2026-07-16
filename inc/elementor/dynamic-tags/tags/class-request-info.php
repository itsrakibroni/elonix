<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Request_Info extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'request-info';
	}

	public function get_title() {
		return esc_html__( 'Request Info', 'elonix' );
	}

	public function get_group() {
		return 'elonix-site';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control(
			'type',
			array(
				'label'   => esc_html__( 'Type', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'page_number' => 'Page Number',
					'language'    => 'Language',
					'user_agent'  => 'User Agent',
					'query_var'   => 'Query Variable',
				),
				'default' => 'page_number',
			)
		);
		$this->add_control(
			'query_var_name',
			array(
				'label'     => esc_html__( 'Query Var Name', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'type' => 'query_var' ),
			)
		);
	}

	public function render() {
		$settings = $this->get_settings();
		$type     = isset( $settings['type'] ) ? $settings['type'] : 'page_number';
		$value    = '';
		switch ( $type ) {
			case 'page_number':
				$value = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				break;
			case 'language':
				$value = get_locale();
				break;
			case 'user_agent':
				$value = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
				break;
			case 'query_var':
				$var_name = isset( $settings['query_var_name'] ) ? $settings['query_var_name'] : '';
				$value    = get_query_var( $var_name );
				// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only frontend query variable used by Elementor Dynamic Tag. No state-changing action is performed.
				if ( ! $value && isset( $_GET[ $var_name ] ) ) {
					$value = sanitize_text_field( wp_unslash( $_GET[ $var_name ] ) );
				}
				// phpcs:enable WordPress.Security.NonceVerification.Recommended
				break;
		}
		$this->render_text( $value );
	}
}
