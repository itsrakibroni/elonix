<?php
/**
 * Elonix Search Builder Custom Post Type Configuration
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.Files.FileName -- Search Builder keeps Archive Builder file naming to preserve shared module architecture.
/**
 * Elonix Search Builder Custom Post Type Configuration.
 */
class Elonix_Search_CPT {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ), 10 );
		add_action( 'init', array( $this, 'add_elementor_support' ), 20 );
	}

	/**
	 * Register Search Builder template CPT.
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Search Templates', 'post type general name', 'elonix' ),
			'singular_name'      => _x( 'Search Template', 'post type singular name', 'elonix' ),
			'menu_name'          => _x( 'Search Builder', 'admin menu', 'elonix' ),
			'name_admin_bar'     => _x( 'Search Template', 'add new on admin bar', 'elonix' ),
			'add_new'            => _x( 'Add New', 'search template', 'elonix' ),
			'add_new_item'       => __( 'Add New Search Template', 'elonix' ),
			'new_item'           => __( 'New Search Template', 'elonix' ),
			'edit_item'          => __( 'Edit Search Template', 'elonix' ),
			'view_item'          => __( 'View Search Template', 'elonix' ),
			'all_items'          => __( 'All Search Templates', 'elonix' ),
			'search_items'       => __( 'Search Search Templates', 'elonix' ),
			'not_found'          => __( 'No search templates found.', 'elonix' ),
			'not_found_in_trash' => __( 'No search templates found in Trash.', 'elonix' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false, // Explicit "Search Builder" submenu is registered centrally in class-admin-menu.php instead.
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'es_search_template' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor' ),
		);

		register_post_type( 'es_search_template', $args );
	}

	/**
	 * Ensure Elementor can edit Search Builder templates.
	 */
	public function add_elementor_support() {
		$cpts = get_option( 'elementor_cpt_support' );
		if ( ! is_array( $cpts ) ) {
			$cpts = array( 'post', 'page' );
		}
		if ( ! in_array( 'es_search_template', $cpts, true ) ) {
			$cpts[] = 'es_search_template';
			update_option( 'elementor_cpt_support', $cpts );
		}
	}
}
// phpcs:enable WordPress.Files.FileName
