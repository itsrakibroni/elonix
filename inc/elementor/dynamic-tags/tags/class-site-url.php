<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Site_Url extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'site-url';
	}

	public function get_title() {
		return esc_html__( 'Site URL', 'elonix' );
	}

	public function get_group() {
		return 'elonix-site';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
	}

	public function render() {
		$this->render_url( home_url( "/" ) );
	}
}
