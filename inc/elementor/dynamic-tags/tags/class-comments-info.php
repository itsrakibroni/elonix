<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Comments_Info extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'comments-info';
	}

	public function get_title() {
		return esc_html__( 'Comments Info', 'elonix' );
	}

	public function get_group() {
		return 'elonix-comments';
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
					"count" => esc_html__( "Comment Count", "elonix" ),
					"open" => esc_html__( "Are Comments Open?", "elonix" ),
					"last_date" => esc_html__( "Last Comment Date", "elonix" ),
				],
				"default" => "count",
			]
		);
	}
	public function render() {
		
		$settings = $this->get_settings();
		$info_type = isset( $settings["info_type"] ) ? $settings["info_type"] : "count";
		
		$post = $this->get_post();
		if ( ! $post ) { return; }
		
		$value = "";
		switch ( $info_type ) {
			case "count":
				$value = $post->comment_count;
				break;
			case "open":
				$value = comments_open( $post->ID ) ? esc_html__( "Yes", "elonix" ) : esc_html__( "No", "elonix" );
				break;
			case "last_date":
				$comments = get_comments( [ "post_id" => $post->ID, "status" => "approve", "number" => 1 ] );
				if ( ! empty( $comments ) ) {
					$value = get_comment_date( "", $comments[0]->comment_ID );
				}
				break;
		}
		$this->render_text( $value );
	}
}
