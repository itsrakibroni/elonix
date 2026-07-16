<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Woo_Product_Url extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'woo-product-url';
	}

	public function get_title() {
		return esc_html__( 'Product URL', 'elonix' );
	}

	public function get_group() {
		return 'elonix-woo';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
	}

	public function render() {
		$product = $this->get_dynamic_data()->get_current_product();
		if ( ! $product ) { return; }
		$this->render_url( $product->get_permalink() );
	}
}
