<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Woo_Product_Image extends Elonix_Dynamic_Data_Tag {

	public function get_name() {
		return 'woo-product-image';
	}

	public function get_title() {
		return esc_html__( 'Product Image', 'elonix' );
	}

	public function get_group() {
		return 'elonix-woo';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
	}

	public function get_value( array $options = [] ) {
		
		if ( ! class_exists( "WooCommerce" ) ) { return []; }
		$product = $this->get_dynamic_data()->get_current_product();
		if ( ! $product ) { return []; }
		
		$image_id = $product->get_image_id();
		if ( ! $image_id ) { return []; }
		
		return [
			"id" => $image_id,
			"url" => wp_get_attachment_image_url( $image_id, "full" ),
		];
	}
}
