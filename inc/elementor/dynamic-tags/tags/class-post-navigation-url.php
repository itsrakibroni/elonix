<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Post_Navigation_Url extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'post-navigation-url';
	}

	public function get_title() {
		return esc_html__( 'Post Navigation URL', 'elonix' );
	}

	public function get_group() {
		return 'elonix-post';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control( "nav_type", [ "label" => esc_html__( "Type", "elonix" ), "type" => \Elementor\Controls_Manager::SELECT, "options" => [ "next" => "Next Post", "previous" => "Previous Post" ], "default" => "next" ] );
	}

	public function render() {
		$settings = $this->get_settings();
		$nav_type = isset( $settings["nav_type"] ) ? $settings["nav_type"] : "next";
		$post = get_adjacent_post( false, "", "previous" === $nav_type );
		if ( $post ) {
			$this->render_url( get_permalink( $post ) );
		}
	}
}
