<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Woo_Product_Terms extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'woo-product-terms';
	}

	public function get_title() {
		return esc_html__( 'Product Terms', 'elonix' );
	}

	public function get_group() {
		return 'elonix-woo';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control( "taxonomy", [ "label" => esc_html__( "Taxonomy", "elonix" ), "type" => \Elementor\Controls_Manager::SELECT, "options" => [ "product_cat" => "Product Categories", "product_tag" => "Product Tags", "pwb-brand" => "Brands (Perfect WooCommerce Brands)", "yith_product_brand" => "Brands (YITH)" ], "default" => "product_cat" ] );
		$this->add_control( "separator", [ "label" => esc_html__( "Separator", "elonix" ), "type" => \Elementor\Controls_Manager::TEXT, "default" => ", " ] );
	}

	public function render() {
		$product = $this->get_dynamic_data()->get_current_product();
		if ( ! $product ) { return; }
		$settings = $this->get_settings();
		$taxonomy = isset( $settings["taxonomy"] ) ? $settings["taxonomy"] : "product_cat";
		$separator = isset( $settings["separator"] ) ? $settings["separator"] : ", ";
		$terms = wc_get_product_terms( $product->get_id(), $taxonomy, array( "fields" => "names" ) );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$this->render_text( implode( $separator, $terms ) );
		}
	}
}
