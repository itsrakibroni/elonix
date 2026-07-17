<?php
/**
 * Elonix – Toolkit for Elementor Post Comments AJAX Handler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Toolkit_Post_Comments_Ajax {
	public function __construct() {
		add_action( 'wp_ajax_es_submit_comment', array( $this, 'ajax_submit_comment' ) );
		add_action( 'wp_ajax_nopriv_es_submit_comment', array( $this, 'ajax_submit_comment' ) );
	}

	public function ajax_submit_comment() {
		check_ajax_referer( 'elonix_comment_nonce', 'nonce' );

		// Native WP validation, spam protection, and insertion
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$comment = wp_handle_comment_submission( wp_unslash( $_POST ) );

		if ( is_wp_error( $comment ) ) {
			wp_send_json_error( $comment->get_error_message() );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$avatar_size = isset( $_POST['es_avatar_size'] ) ? intval( wp_unslash( $_POST['es_avatar_size'] ) ) : 60;

		$GLOBALS['comment'] = $comment;
		$GLOBALS['post']    = get_post( $comment->comment_post_ID );

		require_once __DIR__ . '/class-renderer.php';
		ob_start();
		Elonix_Toolkit_Post_Comments_Renderer::custom_comment_callback(
			$comment,
			array(
				'avatar_size'  => $avatar_size,
				'style'        => 'ol',
				'has_children' => false,
				'max_depth'    => get_option( 'thread_comments_depth' ),
			),
			1
		);
		$html = ob_get_clean();

		$status  = wp_get_comment_status( $comment->comment_ID );
		$message = esc_html__( 'Comment submitted successfully.', 'elonix' );
		if ( 'unapproved' === $status || '0' === $status ) {
			$message = esc_html__( 'Your comment is awaiting moderation.', 'elonix' );
		}

		wp_send_json_success(
			array(
				'message' => $message,
				'html'    => $html,
				'status'  => $status,
				'parent'  => $comment->comment_parent,
			)
		);
	}
}

new Elonix_Toolkit_Post_Comments_Ajax();
