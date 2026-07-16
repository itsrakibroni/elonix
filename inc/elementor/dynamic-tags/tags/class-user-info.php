<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_User_Info extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'user-info';
	}

	public function get_title() {
		return esc_html__( 'User Info', 'elonix' );
	}

	public function get_group() {
		return 'elonix-user';
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
					"display_name" => esc_html__( "Display Name", "elonix" ),
					"first_name" => esc_html__( "First Name", "elonix" ),
					"last_name" => esc_html__( "Last Name", "elonix" ),
					"email" => esc_html__( "Email", "elonix" ),
					"role" => esc_html__( "Role", "elonix" ),
					"meta" => esc_html__( "Custom Meta", "elonix" ),
				],
				"default" => "display_name",
			]
		);
		$this->add_control(
			"meta_key",
			[
				"label" => esc_html__( "Meta Key", "elonix" ),
				"type" => \Elementor\Controls_Manager::TEXT,
				"condition" => [
					"info_type" => "meta",
				],
			]
		);
	}
	public function render() {
		
		$settings = $this->get_settings();
		$info_type = isset( $settings["info_type"] ) ? $settings["info_type"] : "display_name";
		
		$user = $this->get_dynamic_data()->get_current_user();
		if ( ! $user || ! $user->exists() ) { return; }
		
		$value = "";
		switch ( $info_type ) {
			case "display_name":
				$value = $user->display_name;
				break;
			case "first_name":
				$value = $user->first_name;
				break;
			case "last_name":
				$value = $user->last_name;
				break;
			case "email":
				$value = $user->user_email;
				break;
			case "role":
				$roles = $user->roles;
				$value = ! empty( $roles ) ? $roles[0] : "";
				break;
			case "meta":
				$meta_key = isset( $settings["meta_key"] ) ? $settings["meta_key"] : "";
				if ( ! empty( $meta_key ) ) {
					$value = get_user_meta( $user->ID, $meta_key, true );
				}
				break;
		}
		$this->render_text( $value );
	}
}
