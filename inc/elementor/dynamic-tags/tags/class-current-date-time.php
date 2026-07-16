<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Current_Date_Time extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'current-date-time';
	}

	public function get_title() {
		return esc_html__( 'Current Date Time', 'elonix' );
	}

	public function get_group() {
		return 'elonix-site';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control( "type", [ "label" => esc_html__( "Type", "elonix" ), "type" => \Elementor\Controls_Manager::SELECT, "options" => [ "date" => "Current Date", "time" => "Current Time", "year" => "Current Year", "month" => "Current Month", "day" => "Current Day", "timestamp" => "Timestamp" ], "default" => "date" ] );
	}

	public function render() {
		$settings = $this->get_settings();
		$type = isset( $settings["type"] ) ? $settings["type"] : "date";
		$value = "";
		switch ( $type ) {
			case "date": $value = date_i18n( get_option( "date_format" ) ); break;
			case "time": $value = date_i18n( get_option( "time_format" ) ); break;
			case "year": $value = date_i18n( "Y" ); break;
			case "month": $value = date_i18n( "F" ); break;
			case "day": $value = date_i18n( "d" ); break;
			case "timestamp": $value = current_time( "timestamp" ); break;
		}
		$this->render_text( $value );
	}
}
