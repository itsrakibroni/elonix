<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Acf_Text extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'acf-text';
	}

	public function get_title() {
		return esc_html__( 'ACF Field (Text)', 'elonix' );
	}

	public function get_group() {
		return 'elonix-acf';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		
		$this->add_control(
			"field_name",
			[
				"label" => esc_html__( "Field Name", "elonix" ),
				"type" => \Elementor\Controls_Manager::TEXT,
				"description" => esc_html__( "Enter the ACF field name (e.g. sub_title)", "elonix" ),
			]
		);
	}
	public function render() {
		
		if ( ! function_exists( "get_field" ) ) { return; }
		$settings = $this->get_settings();
		$field_name = isset( $settings["field_name"] ) ? $settings["field_name"] : "";
		if ( empty( $field_name ) ) { return; }
		
		$value = get_field( $field_name );
		if ( is_array( $value ) ) {
			$value = implode( ", ", $value );
		}
		$this->render_text( $value );
	}
}
