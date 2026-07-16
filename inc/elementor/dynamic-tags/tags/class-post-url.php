<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Post_Url extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'post-url';
	}

	public function get_title() {
		return esc_html__( 'Post URL', 'elonix' );
	}

	public function get_group() {
		return 'elonix-post';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
	}

	public function render() {
		$this->render_url( get_permalink() );
	}
}
