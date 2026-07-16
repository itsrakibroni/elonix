<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Acf_Image extends Elonix_Dynamic_Data_Tag {

	public function get_name() {
		return 'acf-image';
	}

	public function get_title() {
		return esc_html__( 'ACF Field (Image)', 'elonix' );
	}

	public function get_group() {
		return 'elonix-acf';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
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
	public function get_value( array $options = [] ) {
		
		if ( ! function_exists( "get_field" ) ) { return []; }
		$settings = $this->get_settings();
		$field_name = isset( $settings["field_name"] ) ? $settings["field_name"] : "";
		if ( empty( $field_name ) ) { return []; }
		
		$value = get_field( $field_name );
		if ( ! $value ) { return []; }
		
		if ( is_array( $value ) && isset( $value["id"] ) ) {
			return [
				"id" => $value["id"],
				"url" => $value["url"],
			];
		} elseif ( is_numeric( $value ) ) {
			return [
				"id" => $value,
				"url" => wp_get_attachment_image_url( $value, "full" ),
			];
		} elseif ( is_string( $value ) ) {
			return [
				"id" => "",
				"url" => $value,
			];
		}
		return [];
	}
}
