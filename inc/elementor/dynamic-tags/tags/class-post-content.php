<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Post_Content extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'post-content';
	}

	public function get_title() {
		return esc_html__( 'Post Content', 'elonix' );
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
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress Core hook
		$this->render_text( apply_filters( "the_content", $post->post_content ) );
	}
}
