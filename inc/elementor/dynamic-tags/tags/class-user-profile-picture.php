<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_User_Profile_Picture extends Elonix_Dynamic_Data_Tag {

	public function get_name() {
		return 'user-profile-picture';
	}

	public function get_title() {
		return esc_html__( 'User Profile Picture', 'elonix' );
	}

	public function get_group() {
		return 'elonix-user';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
	}

	public function get_value( array $options = [] ) {
		
		$user = $this->get_dynamic_data()->get_current_user();
		if ( ! $user || ! $user->exists() ) { return []; }
		$avatar_url = get_avatar_url( $user->ID );
		return [
			"id" => "",
			"url" => $avatar_url,
		];
	}
}
