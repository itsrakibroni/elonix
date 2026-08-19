<?php
/**
 * Elonix – Toolkit for Elementor Post Comments Pagination
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Toolkit_Post_Comments_Pagination {

	private $comments;
	private $per_page;
	private $threaded;

	public static function resolve_comments_per_page( $elementor_setting ) {
		if ( ! empty( $elementor_setting ) && (int) $elementor_setting > 0 ) {
			return (int) $elementor_setting;
		}

		return 0;
	}

	public function __construct( $comments, $per_page ) {
		$this->comments = $comments;
		$this->per_page = (int) $per_page;
		$this->threaded = (bool) get_option( 'thread_comments' );
	}

	public function get_current_page() {
		// Respect our custom es_cpage parameter first to avoid redirect_canonical issues
		// Read-only pagination (mirrors WP core's own 'cpage' comment-pagination query var); intval-cast, never output.
		if ( isset( $_GET['es_cpage'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pagination, see note above.
			$page = (int) wp_unslash( $_GET['es_cpage'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- (int) cast is sufficient sanitization.
		} elseif ( isset( $_GET['cpage'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pagination, see note above.
			$page = (int) wp_unslash( $_GET['cpage'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- (int) cast is sufficient sanitization.
		} else {
			$page = get_query_var( 'cpage' );
		}

		if ( ! $page ) {
			$page = 1;
		}

		return max( 1, (int) $page );
	}

	public function get_total_pages() {
		if ( empty( $this->comments ) || $this->per_page <= 0 ) {
			return 1;
		}

		if ( $this->threaded ) {
			$count = 0;
			foreach ( $this->comments as $comment ) {
				if ( 0 == $comment->comment_parent ) {
					++$count;
				}
			}
		} else {
			$count = count( $this->comments );
		}

		return (int) ceil( $count / $this->per_page );
	}

	public function get_pagination_links() {
		$total_pages = $this->get_total_pages();
		if ( $total_pages <= 1 ) {
			return '';
		}

		$current_page = $this->get_current_page();

		$args = array(
			'base'      => add_query_arg( 'es_cpage', '%#%' ),
			'format'    => '',
			'total'     => $total_pages,
			'current'   => $current_page,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'add_args'  => false,
		);

		return paginate_links( $args );
	}
}
