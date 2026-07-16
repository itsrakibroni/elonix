<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Post_Title extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'post-title';
	}

	public function get_title() {
		return esc_html__( 'Post Title', 'elonix' );
	}

	public function get_group() {
		return 'elonix-post';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	public function render() {
		$this->render_text( get_the_title() );
	}
}
