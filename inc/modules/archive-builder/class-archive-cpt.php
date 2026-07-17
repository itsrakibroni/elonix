<?php
/**
 * Elonix Archive Builder Custom Post Type Configuration
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Archive_CPT {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ), 10 );
		add_action( 'init', array( $this, 'add_elementor_support' ), 20 );
	}

	/**
	 * Register CPT es_archive.
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Archive Templates', 'post type general name', 'elonix' ),
			'singular_name'      => _x( 'Archive Template', 'post type singular name', 'elonix' ),
			'menu_name'          => _x( 'Archive Builder', 'admin menu', 'elonix' ),
			'name_admin_bar'     => _x( 'Archive Template', 'add new on admin bar', 'elonix' ),
			'add_new'            => _x( 'Add New', 'archive', 'elonix' ),
			'add_new_item'       => __( 'Add New Archive Template', 'elonix' ),
			'new_item'           => __( 'New Archive Template', 'elonix' ),
			'edit_item'          => __( 'Edit Archive Template', 'elonix' ),
			'view_item'          => __( 'View Archive Template', 'elonix' ),
			'all_items'          => __( 'All Archive Templates', 'elonix' ),
			'search_items'       => __( 'Search Archive Templates', 'elonix' ),
			'not_found'          => __( 'No archive templates found.', 'elonix' ),
			'not_found_in_trash' => __( 'No archive templates found in Trash.', 'elonix' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true, // Needed for Elementor preview iframe compatibility
			'show_ui'            => true,
			'show_in_menu'       => 'elonix', // Nest inside main Elonix menu
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'es_archive' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor' ),
		);

		register_post_type( 'es_archive', $args );
	}

	/**
	 * Ensure Elementor CPT support option registers es_archive templates automatically.
	 */
	public function add_elementor_support() {
		$cpts = get_option( 'elementor_cpt_support' );
		if ( ! is_array( $cpts ) ) {
			$cpts = array( 'post', 'page' );
		}
		if ( ! in_array( 'es_archive', $cpts, true ) ) {
			$cpts[] = 'es_archive';
			update_option( 'elementor_cpt_support', $cpts );
		}
	}
}
