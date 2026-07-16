<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Acf_Gallery extends Elonix_Dynamic_Data_Tag {

	public function get_name() {
		return 'acf-gallery';
	}

	public function get_title() {
		return esc_html__( 'ACF Field (Gallery)', 'elonix' );
	}

	public function get_group() {
		return 'elonix-acf';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY );
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
		if ( ! is_array( $value ) ) { return []; }
		
		$gallery = [];
		foreach ( $value as $image ) {
			if ( is_array( $image ) && isset( $image["id"] ) ) {
				$gallery[] = [
					"id" => $image["id"],
					"url" => $image["url"],
				];
			} elseif ( is_numeric( $image ) ) {
				$gallery[] = [
					"id" => $image,
					"url" => wp_get_attachment_image_url( $image, "full" ),
				];
			}
		}
		return $gallery;
	}
}
