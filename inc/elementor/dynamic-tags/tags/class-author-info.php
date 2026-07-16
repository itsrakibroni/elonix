<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Author_Info extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'author-info';
	}

	public function get_title() {
		return esc_html__( 'Author Info', 'elonix' );
	}

	public function get_group() {
		return 'elonix-author';
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
					"display_name" => esc_html__( "Display Name", "elonix" ),
					"first_name" => esc_html__( "First Name", "elonix" ),
					"last_name" => esc_html__( "Last Name", "elonix" ),
					"bio" => esc_html__( "Bio", "elonix" ),
					"email" => esc_html__( "Email", "elonix" ),
					"id" => esc_html__( "ID", "elonix" ),
				],
				"default" => "display_name",
			]
		);
	}
	public function render() {
		
		$settings = $this->get_settings();
		$info_type = isset( $settings["info_type"] ) ? $settings["info_type"] : "display_name";
		$author = $this->get_author();
		if ( ! $author ) { return; }
		
		$value = "";
		switch ( $info_type ) {
			case "display_name":
				$value = $author->display_name;
				break;
			case "first_name":
				$value = $author->first_name;
				break;
			case "last_name":
				$value = $author->last_name;
				break;
			case "bio":
				$value = $author->description;
				break;
			case "email":
				$value = $author->user_email;
				break;
			case "id":
				$value = $author->ID;
				break;
		}
		
		$this->render_text( $value );
	}
}
