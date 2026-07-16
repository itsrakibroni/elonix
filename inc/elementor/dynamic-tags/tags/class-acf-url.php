<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Acf_Url extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'acf-url';
	}

	public function get_title() {
		return esc_html__( 'ACF Field (URL)', 'elonix' );
	}

	public function get_group() {
		return 'elonix-acf';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
	}

	protected function register_controls() {
		
		$this->add_control(
			"field_name",
			[
				"label" => esc_html__( "Field Name", "elonix" ),
				"type" => \Elementor\Controls_Manager::TEXT,
			]
		);
	}
	public function render() {
		
		if ( ! function_exists( "get_field" ) ) { return; }
		$settings = $this->get_settings();
		$field_name = isset( $settings["field_name"] ) ? $settings["field_name"] : "";
		if ( empty( $field_name ) ) { return; }
		
		$value = get_field( $field_name );
		if ( is_array( $value ) && isset( $value["url"] ) ) {
			$value = $value["url"]; // ACF Link field
		}
		$this->render_url( $value );
	}
}
