<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Search_Info extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'search-info';
	}

	public function get_title() {
		return esc_html__( 'Search Info', 'elonix' );
	}

	public function get_group() {
		return 'elonix-search';
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
					"query" => esc_html__( "Search Query", "elonix" ),
					"count" => esc_html__( "Result Count", "elonix" ),
				],
				"default" => "query",
			]
		);
	}
	public function render() {
		
		$settings = $this->get_settings();
		$info_type = isset( $settings["info_type"] ) ? $settings["info_type"] : "query";
		
		$value = "";
		switch ( $info_type ) {
			case "query":
				$value = $this->get_dynamic_data()->get_search_query();
				break;
			case "count":
				$value = $this->get_dynamic_data()->get_search_count();
				break;
		}
		$this->render_text( $value );
	}
}
