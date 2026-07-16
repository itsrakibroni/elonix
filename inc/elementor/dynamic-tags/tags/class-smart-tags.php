<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Smart_Tags extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'smart-tags';
	}

	public function get_title() {
		return esc_html__( 'Smart Tags', 'elonix' );
	}

	public function get_group() {
		return 'elonix-site';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control( "type", [ "label" => esc_html__( "Type", "elonix" ), "type" => \Elementor\Controls_Manager::SELECT, "options" => [ "logged_in" => "Is Logged In?", "is_home" => "Is Homepage?", "is_single" => "Is Single?", "is_archive" => "Is Archive?", "is_search" => "Is Search?" ], "default" => "logged_in" ] );
	}

	public function render() {
		$settings = $this->get_settings();
		$type = isset( $settings["type"] ) ? $settings["type"] : "logged_in";
		$value = "";
		switch ( $type ) {
			case "logged_in": $value = is_user_logged_in() ? "Yes" : "No"; break;
			case "is_home": $value = is_front_page() || is_home() ? "Yes" : "No"; break;
			case "is_single": $value = is_single() ? "Yes" : "No"; break;
			case "is_archive": $value = is_archive() ? "Yes" : "No"; break;
			case "is_search": $value = is_search() ? "Yes" : "No"; break;
		}
		$this->render_text( $value );
	}
}
