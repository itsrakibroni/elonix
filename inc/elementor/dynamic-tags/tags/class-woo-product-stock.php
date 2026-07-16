<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Woo_Product_Stock extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'woo-product-stock';
	}

	public function get_title() {
		return esc_html__( 'Product Stock', 'elonix' );
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
				"label" => esc_html__( "Information", "elonix" ),
				"type" => \Elementor\Controls_Manager::SELECT,
				"options" => [
					"quantity" => esc_html__( "Stock Quantity", "elonix" ),
					"status" => esc_html__( "Stock Status", "elonix" ),
				],
				"default" => "quantity",
			]
		);
	}
	public function render() {
		
		if ( ! class_exists( "WooCommerce" ) ) { return; }
		$product = $this->get_dynamic_data()->get_current_product();
		if ( ! $product ) { return; }
		
		$settings = $this->get_settings();
		$info_type = isset( $settings["info_type"] ) ? $settings["info_type"] : "quantity";
		
		if ( "status" === $info_type ) {
			$this->render_text( $product->is_in_stock() ? __( "In Stock", "elonix" ) : __( "Out of Stock", "elonix" ) );
		} else {
			$this->render_text( $product->get_stock_quantity() );
		}
	}
}
