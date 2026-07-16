<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Author_Profile_Picture extends Elonix_Dynamic_Data_Tag {

	public function get_name() {
		return 'author-profile-picture';
	}

	public function get_title() {
		return esc_html__( 'Author Profile Picture', 'elonix' );
	}

	public function get_group() {
		return 'elonix-author';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
	}

	public function get_value( array $options = [] ) {
		
		$author = $this->get_author();
		if ( ! $author ) { return []; }
		$avatar_url = get_avatar_url( $author->ID );
		return [
			"id" => "",
			"url" => $avatar_url,
		];
	}
}
