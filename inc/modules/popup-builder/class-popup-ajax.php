<?php
/**
 * Elonix Popup Builder Custom AJAX Actions Handler
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Popup_AJAX {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Register Duplicate AJAX trigger
		add_action( 'wp_ajax_elonix_duplicate_popup', array( $this, 'duplicate_popup_template' ) );

		// Add quick duplicate links inside post rows in list table
	}

	/**
	 * Inject dynamic "Duplicate" link inside row actions of elonix_popup.
	 */
	public function inject_duplicate_link( $actions, $post ) {
		if ( 'elonix_popup' === $post->post_type ) {
			$nonce                   = wp_create_nonce( 'es_duplicate_popup_' . $post->ID );
			$url                     = admin_url( 'admin-ajax.php?action=elonix_duplicate_popup&post_id=' . $post->ID . '&nonce=' . $nonce );
			$actions['es_duplicate'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( $url ),
				esc_attr__( 'Duplicate this template', 'elonix' ),
				esc_html__( 'Duplicate', 'elonix' )
			);
		}
		return $actions;
	}

	/**
	 * Handle Duplicate Template request.
	 */
	public function duplicate_popup_template() {
		$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
		$nonce   = isset( $_GET['nonce'] ) ? sanitize_key( $_GET['nonce'] ) : '';

		if ( ! $post_id || ! wp_verify_nonce( $nonce, 'es_duplicate_popup_' . $post_id ) ) {
			wp_die( esc_html__( 'Security validation failed.', 'elonix' ) );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to execute this action.', 'elonix' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post || 'elonix_popup' !== $post->post_type ) {
			wp_die( esc_html__( 'Original template not found.', 'elonix' ) );
		}

		// Create duplicate post object
		$new_post_args = array(
			'post_title'  => $post->post_title . esc_html__( ' (Copy)', 'elonix' ),
			'post_status' => 'draft', // Force as draft first
			'post_type'   => 'elonix_popup',
			'post_author' => get_current_user_id(),
		);

		$new_post_id = wp_insert_post( $new_post_args );

		if ( is_wp_error( $new_post_id ) ) {
			wp_die( esc_html__( 'Unable to insert duplicate template.', 'elonix' ) );
		}

		// Replicate metabox settings
		$meta_keys = array(
			'_es_popup_type',
			'_es_popup_trigger_type',
			'_es_popup_trigger_value',
			'_es_popup_target_rule',
			'_es_popup_target_ids',
			'_es_popup_devices',
			'_es_popup_frequency',
			'_es_popup_cookie_expiry',
			'_elementor_data',
			'_elementor_template_type',
			'_elementor_edit_mode',
		);

		foreach ( $meta_keys as $key ) {
			$val = get_post_meta( $post_id, $key, true );
			if ( ! empty( $val ) || '0' === $val ) {
				update_post_meta( $new_post_id, $key, $val );
			}
		}

		// Redirect back to templates list
		wp_safe_redirect( admin_url( 'edit.php?post_type=elonix_popup' ) );
		exit;
	}
}
