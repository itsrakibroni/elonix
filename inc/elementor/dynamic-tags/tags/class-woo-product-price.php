<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Woo_Product_Price extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'woo-product-price';
	}

	public function get_title() {
		return esc_html__( 'Product Price', 'elonix' );
	}

	public function get_group() {
		return 'elonix-woo';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		
		$this->add_control(
			"info_type",
			[
				"label" => esc_html__( "Format", "elonix" ),
				"type" => \Elementor\Controls_Manager::SELECT,
				"options" => [
					"price" => esc_html__( "Default Price HTML", "elonix" ),
					"regular" => esc_html__( "Regular Price", "elonix" ),
					"sale" => esc_html__( "Sale Price", "elonix" ),
				],
				"default" => "price",
			]
		);
	}
	public function render() {
		
		if ( ! class_exists( "WooCommerce" ) ) { return; }
		$settings = $this->get_settings();
		$info_type = isset( $settings["info_type"] ) ? $settings["info_type"] : "price";
		
		$product = $this->get_dynamic_data()->get_current_product();
		if ( ! $product ) { return; }
		
		$value = "";
		switch ( $info_type ) {
			case "price":
				$value = $product->get_price_html();
				break;
			case "regular":
				$value = wc_price( $product->get_regular_price() );
				break;
			case "sale":
				$value = wc_price( $product->get_sale_price() );
				break;
		}
		$this->render_text( $value );
	}
}
