<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Tag_Post_Info extends Elonix_Dynamic_Tag {

	public function get_name() {
		return 'post-info';
	}

	public function get_title() {
		return esc_html__( 'Post Info', 'elonix' );
	}

	public function get_group() {
		return 'elonix-post';
	}

	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	protected function register_controls() {

		$this->add_control(
			'info_type',
			array(
				'label'   => esc_html__( 'Information', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'id'                 => esc_html__( 'ID', 'elonix' ),
					'type'               => esc_html__( 'Post Type', 'elonix' ),
					'status'             => esc_html__( 'Status', 'elonix' ),
					'comment_count'      => esc_html__( 'Comment Count', 'elonix' ),
					'word_count'         => esc_html__( 'Word Count', 'elonix' ),
					'reading_time'       => esc_html__( 'Reading Time', 'elonix' ),
					'format'             => esc_html__( 'Post Format', 'elonix' ),
					'sticky'             => esc_html__( 'Is Sticky?', 'elonix' ),
					'password_protected' => esc_html__( 'Is Password Protected?', 'elonix' ),
				),
				'default' => 'id',
			)
		);
	}
	public function render() {

		$settings  = $this->get_settings();
		$info_type = isset( $settings['info_type'] ) ? $settings['info_type'] : 'id';
		$post      = $this->get_post();
		if ( ! $post ) {
			return; }

		$value = '';
		switch ( $info_type ) {
			case 'id':
				$value = $post->ID;
				break;
			case 'type':
				$value = $post->post_type;
				break;
			case 'status':
				$value = $post->post_status;
				break;
			case 'comment_count':
				$value = $post->comment_count;
				break;
			case 'word_count':
				$value = str_word_count( wp_strip_all_tags( $post->post_content ) );
				break;
			case 'reading_time':
				$words   = str_word_count( wp_strip_all_tags( $post->post_content ) );
				$minutes = ceil( $words / 200 );
				/* translators: %s: Number of minutes */
				$value   = sprintf( _n( '%s min', '%s mins', $minutes, 'elonix' ), $minutes );
				break;
			case 'format':
				$value = get_post_format( $post->ID ) ?: 'standard';
				break;
			case 'sticky':
				$value = is_sticky( $post->ID ) ? esc_html__( 'Yes', 'elonix' ) : esc_html__( 'No', 'elonix' );
				break;
			case 'password_protected':
				$value = post_password_required( $post->ID ) ? esc_html__( 'Yes', 'elonix' ) : esc_html__( 'No', 'elonix' );
				break;
		}
		$this->render_text( $value );
	}
}
