<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Woo_Product_Rating extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'woo-product-rating';
	}

	public function get_title() {
		return esc_html__( 'Product Rating', 'elonix' );
	}

	public function get_group() {
		return 'elonix-woo';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	public function render() {
		
		if ( ! class_exists( "WooCommerce" ) ) { return; }
		$product = $this->get_dynamic_data()->get_current_product();
		if ( ! $product ) { return; }
		$this->render_text( $product->get_average_rating() );
	}
}
