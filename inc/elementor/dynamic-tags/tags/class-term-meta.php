<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Term_Meta extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'term-meta';
	}

	public function get_title() {
		return esc_html__( 'Term Meta', 'elonix' );
	}

	public function get_group() {
		return 'elonix-term';
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
		$term = $this->get_dynamic_data()->get_current_term();
		if ( ! $term ) { return; }
		$value = get_term_meta( $term->term_id, $meta_key, true );
		if ( is_array( $value ) ) { $value = implode( ", ", $value ); }
		$this->render_text( $value );
	}
}
