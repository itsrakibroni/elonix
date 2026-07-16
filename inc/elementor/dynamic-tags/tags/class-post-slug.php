<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Post_Slug extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'post-slug';
	}

	public function get_title() {
		return esc_html__( 'Post Slug', 'elonix' );
	}

	public function get_group() {
		return 'elonix-post';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	public function render() {
		
		$post = $this->get_post();
		if ( ! $post ) { return; }
		$this->render_text( $post->post_name );
	}
}
