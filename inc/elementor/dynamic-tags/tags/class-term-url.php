<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Term_Url extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'term-url';
	}

	public function get_title() {
		return esc_html__( 'Term URL', 'elonix' );
	}

	public function get_group() {
		return 'elonix-term';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
	}

	public function render() {
		$term = $this->get_dynamic_data()->get_current_term();
		if ( ! $term || ! isset( $term->term_id ) ) { return; }
		
		$value = get_term_link( $term );
		$this->render_url( $value );
	}
}
