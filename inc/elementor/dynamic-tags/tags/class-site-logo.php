<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Site_Logo extends Elonix_Dynamic_Data_Tag {

	public function get_name() {
		return 'site-logo';
	}

	public function get_title() {
		return esc_html__( 'Site Logo', 'elonix' );
	}

	public function get_group() {
		return 'elonix-site';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
	}

	public function get_value( array $options = [] ) {
		
		$custom_logo_id = get_theme_mod( "custom_logo" );
		if ( ! $custom_logo_id ) { return []; }
		return [
			"id" => $custom_logo_id,
			"url" => wp_get_attachment_image_url( $custom_logo_id, "full" ),
		];
	}
}
