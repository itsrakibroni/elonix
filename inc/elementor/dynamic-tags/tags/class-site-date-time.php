<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Site_Date_Time extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'site-date-time';
	}

	public function get_title() {
		return esc_html__( 'Site Date/Time Info', 'elonix' );
	}

	public function get_group() {
		return 'elonix-site';
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
					"admin_email" => esc_html__( "Admin Email", "elonix" ),
					"language" => esc_html__( "Language", "elonix" ),
					"timezone" => esc_html__( "Timezone", "elonix" ),
					"date_format" => esc_html__( "Date Format", "elonix" ),
					"time_format" => esc_html__( "Time Format", "elonix" ),
				],
				"default" => "admin_email",
			]
		);
	}
	public function render() {
		
		$settings = $this->get_settings();
		$info_type = isset( $settings["info_type"] ) ? $settings["info_type"] : "admin_email";
		
		$value = "";
		switch ( $info_type ) {
			case "admin_email":
				$value = get_option( "admin_email" );
				break;
			case "language":
				$value = get_bloginfo( "language" );
				break;
			case "timezone":
				$value = wp_timezone_string();
				break;
			case "date_format":
				$value = get_option( "date_format" );
				break;
			case "time_format":
				$value = get_option( "time_format" );
				break;
		}
		$this->render_text( $value );
	}
}
