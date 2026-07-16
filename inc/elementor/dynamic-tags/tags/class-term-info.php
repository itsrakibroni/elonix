<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Term_Info extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'term-info';
	}

	public function get_title() {
		return esc_html__( 'Term Info', 'elonix' );
	}

	public function get_group() {
		return 'elonix-term';
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
					"name" => esc_html__( "Name", "elonix" ),
					"description" => esc_html__( "Description", "elonix" ),
					"id" => esc_html__( "ID", "elonix" ),
					"parent" => esc_html__( "Parent Term", "elonix" ),
					"count" => esc_html__( "Count", "elonix" ),
				],
				"default" => "name",
			]
		);
	}
	public function render() {
		
		$settings = $this->get_settings();
		$info_type = isset( $settings["info_type"] ) ? $settings["info_type"] : "name";
		
		$term = $this->get_dynamic_data()->get_current_term();
		
		if ( ! $term || ! isset( $term->term_id ) ) { return; }
		
		$value = "";
		switch ( $info_type ) {
			case "name":
				$value = $term->name;
				break;
			case "description":
				$value = $term->description;
				break;
			case "id":
				$value = $term->term_id;
				break;
			case "parent":
				$value = $term->parent ? get_term( $term->parent )->name : "";
				break;
			case "count":
				$value = $term->count;
				break;
		}
		
		$this->render_text( $value );
	}
}
