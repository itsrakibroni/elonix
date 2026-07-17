<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elonix – Toolkit for Elementor Uninstall
 *
 * This file is called when the plugin is deleted from the WordPress admin dashboard.
 *
 * @package Elonix_Toolkit
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function elonix_uninstall_logic() {
	// Only run the deletion logic if the user has explicitly opted to remove data on uninstall.
	$settings = get_option( 'elonix_settings', array() );
	if ( empty( $settings['uninstall']['remove_data_on_uninstall'] ) ) {
		return;
	}

	// 1. Delete all posts of custom post types registered by this plugin
	$post_types = array( 'es_header', 'es_footer', 'es_popup', 'es_archive' );
	foreach ( $post_types as $post_type ) {
		$posts = get_posts(
			array(
				'post_type'   => $post_type,
				'numberposts' => -1,
				'post_status' => 'any',
			)
		);

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				// Bypass trash and force delete, including post meta
				wp_delete_post( $post->ID, true );
			}
		}
	}

	// 2. Delete terms associated with custom taxonomies registered by this plugin
	$taxonomies = array();

	foreach ( $taxonomies as $taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				wp_delete_term( $term->term_id, $taxonomy );
			}
		}
	}

	// 3. Delete post meta for all deleted posts (cleanup any orphaned meta)
	delete_metadata( 'post', null, '_elonix_settings', '', true );
	delete_metadata( 'post', null, '_es_display_conditions', '', true );
	delete_metadata( 'post', null, '_elementor_controls_usage', '', true );
	delete_metadata( 'post', null, '_elementor_controls_classes', '', true );
	delete_metadata( 'post', null, '_elementor_template_type', '', true );

	// 4. Delete options created and used by the plugin
	$options_to_delete = array(
		'elonix_widgets',
		'elonix_modules',
		'elonix_settings',
		'elonix_remove_data_on_uninstall',
	);

	foreach ( $options_to_delete as $option ) {
		delete_option( $option );
	}

	// 5. Delete transients
	delete_transient( 'elonix_transients' );
}

if ( is_multisite() ) {
	$elonix_blogs = get_sites( array( 'fields' => 'ids' ) );
	if ( $elonix_blogs ) {
		foreach ( $elonix_blogs as $blog_id ) {
			switch_to_blog( $blog_id );
			elonix_uninstall_logic();
			restore_current_blog();
		}
	}
} else {
	elonix_uninstall_logic();
}
