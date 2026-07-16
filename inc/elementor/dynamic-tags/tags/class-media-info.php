<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Media_Info extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'media-info';
	}

	public function get_title() {
		return esc_html__( 'Media Info', 'elonix' );
	}

	public function get_group() {
		return 'elonix-media';
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
					"url" => esc_html__( "Attachment URL", "elonix" ),
					"caption" => esc_html__( "Caption", "elonix" ),
					"alt" => esc_html__( "Alt Text", "elonix" ),
					"description" => esc_html__( "Description", "elonix" ),
				],
				"default" => "url",
			]
		);
	}
	public function render() {
		
		$settings = $this->get_settings();
		$info_type = isset( $settings["info_type"] ) ? $settings["info_type"] : "url";
		
		$post = $this->get_post();
		if ( ! $post || "attachment" !== $post->post_type ) { return; }
		
		$value = "";
		switch ( $info_type ) {
			case "url":
				$value = wp_get_attachment_url( $post->ID );
				break;
			case "caption":
				$value = $post->post_excerpt;
				break;
			case "alt":
				$value = get_post_meta( $post->ID, "_wp_attachment_image_alt", true );
				break;
			case "description":
				$value = $post->post_content;
				break;
		}
		
		if ( "url" === $info_type ) {
			$this->render_url( $value );
		} else {
			$this->render_text( $value );
		}
	}
}
