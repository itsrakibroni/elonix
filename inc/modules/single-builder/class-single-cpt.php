<?php
/**
 * Elonix Single Builder Custom Post Type Configuration
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Single_CPT {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ), 10 );
		add_action( 'init', array( $this, 'add_elementor_support' ), 20 );
	}

	/**
	 * Register CPT elonix_single.
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Single Templates', 'post type general name', 'elonix' ),
			'singular_name'      => _x( 'Single Template', 'post type singular name', 'elonix' ),
			'menu_name'          => _x( 'Single Builder', 'admin menu', 'elonix' ),
			'name_admin_bar'     => _x( 'Single Template', 'add new on admin bar', 'elonix' ),
			'add_new'            => _x( 'Add New', 'single', 'elonix' ),
			'add_new_item'       => __( 'Add New Single Template', 'elonix' ),
			'new_item'           => __( 'New Single Template', 'elonix' ),
			'edit_item'          => __( 'Edit Single Template', 'elonix' ),
			'view_item'          => __( 'View Single Template', 'elonix' ),
			'all_items'          => __( 'All Single Templates', 'elonix' ),
			'search_items'       => __( 'Search Single Templates', 'elonix' ),
			'not_found'          => __( 'No single templates found.', 'elonix' ),
			'not_found_in_trash' => __( 'No single templates found in Trash.', 'elonix' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true, // Needed for Elementor preview iframe compatibility
			'show_ui'            => true,
			'show_in_menu'       => 'elonix', // Nest inside main Elonix menu
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'elonix_single' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor' ),
		);

		register_post_type( 'elonix_single', $args );
	}

	/**
	 * Ensure Elementor CPT support option registers elonix_single templates automatically.
	 */
	public function add_elementor_support() {
		$cpts = get_option( 'elementor_cpt_support' );
		if ( ! is_array( $cpts ) ) {
			$cpts = array( 'post', 'page' );
		}
		if ( ! in_array( 'elonix_single', $cpts, true ) ) {
			$cpts[] = 'elonix_single';
			update_option( 'elementor_cpt_support', $cpts );
		}
	}
}
