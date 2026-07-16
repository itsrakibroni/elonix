<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Site_Title extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'site-title';
	}

	public function get_title() {
		return esc_html__( 'Site Title', 'elonix' );
	}

	public function get_group() {
		return 'elonix-site';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	public function render() {
		$this->render_text( get_bloginfo( "name" ) );
	}
}
