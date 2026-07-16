<?php
/**
 * Elonix – Toolkit for Elementor Post Comments Query
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Toolkit_Post_Comments_Query {

	private $post_id;
	private $comments = null;
	private $total_comments = 0;

	public function __construct( $post_id ) {
		$this->post_id = $post_id;
	}

	public function fetch_comments() {
		if ( null !== $this->comments ) {
			return $this->comments;
		}

		$order = get_option( 'comment_order' );
		if ( ! in_array( strtolower( $order ), array( 'asc', 'desc' ), true ) ) {
			$order = 'asc';
		}

		$args = array(
			'post_id' => $this->post_id,
			'status'  => 'approve',
			'order'   => strtoupper( $order ),
		);

		$this->comments = get_comments( $args );
		$this->total_comments = count( $this->comments );

		return $this->comments;
	}

	public function get_total_comments() {
		if ( null === $this->comments ) {
			$this->fetch_comments();
		}
		return $this->total_comments;
	}

	public function has_comments() {
		return $this->get_total_comments() > 0;
	}

}
