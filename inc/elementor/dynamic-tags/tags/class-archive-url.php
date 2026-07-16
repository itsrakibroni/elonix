<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Archive_Url extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'archive-url';
	}

	public function get_title() {
		return esc_html__( 'Archive URL', 'elonix' );
	}

	public function get_group() {
		return 'elonix-archive';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
	}

	public function render() {
		$value = $this->get_dynamic_data()->get_current_archive_url();
		$this->render_url( $value );
	}
}
