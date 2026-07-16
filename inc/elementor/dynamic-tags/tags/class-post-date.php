<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Post_Date extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'post-date';
	}

	public function get_title() {
		return esc_html__( 'Post Date', 'elonix' );
	}

	public function get_group() {
		return 'elonix-post';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		
		$this->add_control(
			"type",
			[
				"label" => esc_html__( "Type", "elonix" ),
				"type" => \Elementor\Controls_Manager::SELECT,
				"options" => [
					"published" => esc_html__( "Published", "elonix" ),
					"modified" => esc_html__( "Modified", "elonix" ),
				],
				"default" => "published",
			]
		);
		$this->add_control(
			"format",
			[
				"label" => esc_html__( "Format", "elonix" ),
				"type" => \Elementor\Controls_Manager::SELECT,
				"options" => [
					"default" => esc_html__( "Default", "elonix" ),
					"custom" => esc_html__( "Custom", "elonix" ),
				],
				"default" => "default",
			]
		);
		$this->add_control(
			"custom_format",
			[
				"label" => esc_html__( "Custom Format", "elonix" ),
				"type" => \Elementor\Controls_Manager::TEXT,
				"default" => get_option( "date_format" ),
				"condition" => [
					"format" => "custom",
				],
			]
		);
	}
	public function render() {
		
		$settings = $this->get_settings();
		$format = isset( $settings["format"] ) ? $settings["format"] : get_option( "date_format" );
		if ( "custom" === $format && isset( $settings["custom_format"] ) ) {
			$format = $settings["custom_format"];
		}
		
		$type = isset( $settings["type"] ) ? $settings["type"] : "published";
		if ( "modified" === $type ) {
			$date = get_the_modified_date( $format );
		} else {
			$date = get_the_date( $format );
		}
		$this->render_text( $date );
	}
}
