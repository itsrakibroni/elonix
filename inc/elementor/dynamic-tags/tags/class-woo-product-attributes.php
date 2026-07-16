<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Woo_Product_Attributes extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'woo-product-attributes';
	}

	public function get_title() {
		return esc_html__( 'Product Attributes', 'elonix' );
	}

	public function get_group() {
		return 'elonix-woo';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control( "attribute", [ "label" => esc_html__( "Attribute Slug", "elonix" ), "type" => \Elementor\Controls_Manager::TEXT, "description" => "E.g. pa_color or pa_size" ] );
	}

	public function render() {
		$product = $this->get_dynamic_data()->get_current_product();
		if ( ! $product ) { return; }
		$settings = $this->get_settings();
		$attr_name = isset( $settings["attribute"] ) ? $settings["attribute"] : "";
		if ( ! $attr_name ) { return; }
		$value = $product->get_attribute( $attr_name );
		$this->render_text( $value );
	}
}
