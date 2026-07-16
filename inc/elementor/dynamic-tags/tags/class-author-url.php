<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Author_Url extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'author-url';
	}

	public function get_title() {
		return esc_html__( 'Author URL', 'elonix' );
	}

	public function get_group() {
		return 'elonix-author';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
	}

	protected function register_controls() {
		
		$this->add_control(
			"info_type",
			[
				"label" => esc_html__( "URL Type", "elonix" ),
				"type" => \Elementor\Controls_Manager::SELECT,
				"options" => [
					"archive" => esc_html__( "Author Archive", "elonix" ),
					"website" => esc_html__( "Author Website", "elonix" ),
				],
				"default" => "archive",
			]
		);
	}
	public function render() {
		
		$settings = $this->get_settings();
		$info_type = isset( $settings["info_type"] ) ? $settings["info_type"] : "archive";
		$author = $this->get_author();
		if ( ! $author ) { return; }
		
		$value = "";
		switch ( $info_type ) {
			case "archive":
				$value = get_author_posts_url( $author->ID );
				break;
			case "website":
				$value = $author->user_url;
				break;
		}
		
		$this->render_url( $value );
	}
}
