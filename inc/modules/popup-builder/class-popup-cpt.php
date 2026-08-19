<?php
/**
 * Elonix Popup Builder Custom Post Type Configuration
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Popup_CPT {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ), 10 );
		add_action( 'init', array( $this, 'add_elementor_support' ), 20 );
	}

	/**
	 * Register elonix_popup Custom Post Type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Popup Templates', 'post type general name', 'elonix' ),
			'singular_name'      => _x( 'Popup Template', 'post type singular name', 'elonix' ),
			'menu_name'          => _x( 'Popup Builder', 'admin menu', 'elonix' ),
			'name_admin_bar'     => _x( 'Popup Template', 'add new on admin bar', 'elonix' ),
			'add_new'            => _x( 'Add New', 'popup', 'elonix' ),
			'add_new_item'       => __( 'Add New Popup Template', 'elonix' ),
			'new_item'           => __( 'New Popup Template', 'elonix' ),
			'edit_item'          => __( 'Edit Popup Template', 'elonix' ),
			'view_item'          => __( 'View Popup Template', 'elonix' ),
			'all_items'          => __( 'All Popups', 'elonix' ),
			'search_items'       => __( 'Search Popup Templates', 'elonix' ),
			'not_found'          => __( 'No popup templates found.', 'elonix' ),
			'not_found_in_trash' => __( 'No popup templates found in Trash.', 'elonix' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true, // Needed for Elementor preview iframe compatibility
			'show_ui'            => true,
			'show_in_menu'       => 'elonix', // Nest inside main Elonix menu
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'elonix_popup' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'elementor' ), // Supports Title & Elementor
		);

		register_post_type( 'elonix_popup', $args );
	}

	/**
	 * Ensure Elementor CPT support option registers elonix_popup templates automatically.
	 */
	public function add_elementor_support() {
		$cpts = get_option( 'elementor_cpt_support' );
		if ( ! is_array( $cpts ) ) {
			$cpts = array( 'post', 'page' );
		}
		if ( ! in_array( 'elonix_popup', $cpts, true ) ) {
			$cpts[] = 'elonix_popup';
			update_option( 'elementor_cpt_support', $cpts );
		}
	}
}
