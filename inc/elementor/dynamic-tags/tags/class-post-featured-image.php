<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Post_Featured_Image extends Elonix_Dynamic_Data_Tag {

	public function get_name() {
		return 'post-featured-image';
	}

	public function get_title() {
		return esc_html__( 'Featured Image', 'elonix' );
	}

	public function get_group() {
		return 'elonix-post';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
	}

	protected function register_controls() {
		
		$this->add_control(
			"info_type",
			[
				"label" => esc_html__( "Information", "elonix" ),
				"type" => \Elementor\Controls_Manager::SELECT,
				"options" => [
					"url" => esc_html__( "Image Data (URL/ID)", "elonix" ),
					"alt" => esc_html__( "Alt Text", "elonix" ),
					"caption" => esc_html__( "Caption", "elonix" ),
					"description" => esc_html__( "Description", "elonix" ),
				],
				"default" => "url",
			]
		);
	}
	public function get_value( array $options = [] ) {
		
		$post = $this->get_post();
		if ( ! $post ) { return []; }
		$image_id = get_post_thumbnail_id( $post->ID );
		if ( ! $image_id ) { return []; }
		return [
			"id" => $image_id,
			"url" => wp_get_attachment_image_url( $image_id, "full" ),
		];
		
	}
}
