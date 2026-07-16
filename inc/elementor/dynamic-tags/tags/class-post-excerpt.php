<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Post_Excerpt extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'post-excerpt';
	}

	public function get_title() {
		return esc_html__( 'Post Excerpt', 'elonix' );
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
		if ( has_excerpt( $post->ID ) ) {
			$this->render_text( get_the_excerpt( $post ) );
		} else {
			$this->render_text( wp_trim_words( $post->post_content, 20 ) );
		}
	}
}
