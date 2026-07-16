<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Post_Meta extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'post-meta';
	}

	public function get_title() {
		return esc_html__( 'Post Custom Meta', 'elonix' );
	}

	public function get_group() {
		return 'elonix-meta';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		
		$this->add_control(
			"meta_key",
			[
				"label" => esc_html__( "Meta Key", "elonix" ),
				"type" => \Elementor\Controls_Manager::TEXT,
			]
		);
	}
	public function render() {
		
		$settings = $this->get_settings();
		$meta_key = isset( $settings["meta_key"] ) ? $settings["meta_key"] : "";
		if ( empty( $meta_key ) ) { return; }
		
		$post = $this->get_post();
		if ( ! $post ) { return; }
		
		$value = get_post_meta( $post->ID, $meta_key, true );
		if ( is_array( $value ) ) {
			$value = implode( ", ", $value );
		}
		$this->render_text( $value );
	}
}
