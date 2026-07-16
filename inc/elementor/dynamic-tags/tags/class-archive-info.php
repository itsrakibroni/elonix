<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Archive_Info extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'archive-info';
	}

	public function get_title() {
		return esc_html__( 'Archive Info', 'elonix' );
	}

	public function get_group() {
		return 'elonix-archive';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		
		$this->add_control(
			"info_type",
			[
				"label" => esc_html__( "Information", "elonix" ),
				"type" => \Elementor\Controls_Manager::SELECT,
				"options" => [
					"title" => esc_html__( "Archive Title", "elonix" ),
					"description" => esc_html__( "Archive Description", "elonix" ),
				],
				"default" => "title",
			]
		);
	}
	public function render() {
		
		$settings = $this->get_settings();
		$info_type = isset( $settings["info_type"] ) ? $settings["info_type"] : "title";
		
		$value = "";
		switch ( $info_type ) {
			case "title":
				$value = $this->get_dynamic_data()->get_current_archive_title();
				break;
			case "description":
				$value = $this->get_dynamic_data()->get_current_archive_description();
				break;
		}
		
		$this->render_text( $value );
	}
}
