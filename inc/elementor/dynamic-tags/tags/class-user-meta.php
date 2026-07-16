<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_User_Meta extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'user-meta';
	}

	public function get_title() {
		return esc_html__( 'User Meta', 'elonix' );
	}

	public function get_group() {
		return 'elonix-user';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control( "meta_key", [ "label" => esc_html__( "Meta Key", "elonix" ), "type" => \Elementor\Controls_Manager::TEXT ] );
	}

	public function render() {
		$settings = $this->get_settings();
		$meta_key = isset( $settings["meta_key"] ) ? $settings["meta_key"] : "";
		if ( empty( $meta_key ) ) { return; }
		$user_id = get_current_user_id();
		if ( ! $user_id ) { return; }
		$value = get_user_meta( $user_id, $meta_key, true );
		if ( is_array( $value ) ) { $value = implode( ", ", $value ); }
		$this->render_text( $value );
	}
}
