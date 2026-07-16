<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Author_Meta extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'author-meta';
	}

	public function get_title() {
		return esc_html__( 'Author Meta', 'elonix' );
	}

	public function get_group() {
		return 'elonix-author';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control( "meta_key", [ "label" => esc_html__( "Meta Key", "elonix" ), "type" => \Elementor\Controls_Manager::TEXT ] );
	}

	public function render() {
		$settings = $this->get_settings();
		$meta_key = isset( $settings["meta_key"] ) ? $settings["meta_key"] : "";
		if ( empty( $meta_key ) ) { return; }
		$author = $this->get_author();
		if ( ! $author ) { return; }
		$value = get_user_meta( $author->ID, $meta_key, true );
		if ( is_array( $value ) ) { $value = implode( ", ", $value ); }
		$this->render_text( $value );
	}
}
