<?php
/**
 * Elonix – Toolkit for Elementor Dynamic Tags Manager
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Dynamic_Tags_Manager {

	/**
	 * Instance.
	 */
	private static $_instance = null;

	/**
	 * Get instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Initialization
	}

	/**
	 * Register Tags.
	 *
	 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager
	 */
	public function register_tags( $dynamic_tags_manager ) {
		$this->register_groups( $dynamic_tags_manager );
		$this->include_files();
		$this->register_tag_classes( $dynamic_tags_manager );
		
		// Allow third-party developers to register tags
		do_action( 'elonix/register_dynamic_tags', $dynamic_tags_manager );
	}

	/**
	 * Register tag groups (categories).
	 *
	 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager
	 */
	private function register_groups( $dynamic_tags_manager ) {
		$groups = array(
			'elonix-post'     => array( 'title' => esc_html__( 'Elonix Post', 'elonix' ) ),
			'elonix-author'   => array( 'title' => esc_html__( 'Elonix Author', 'elonix' ) ),
			'elonix-site'     => array( 'title' => esc_html__( 'Elonix Site', 'elonix' ) ),
			'elonix-term'     => array( 'title' => esc_html__( 'Elonix Term', 'elonix' ) ),
			'elonix-search'   => array( 'title' => esc_html__( 'Elonix Search', 'elonix' ) ),
			'elonix-archive'  => array( 'title' => esc_html__( 'Elonix Archive', 'elonix' ) ),
			'elonix-user'     => array( 'title' => esc_html__( 'Elonix User', 'elonix' ) ),
			'elonix-comments' => array( 'title' => esc_html__( 'Elonix Comments', 'elonix' ) ),
			'elonix-media'    => array( 'title' => esc_html__( 'Elonix Media', 'elonix' ) ),
			'elonix-url'      => array( 'title' => esc_html__( 'Elonix URL', 'elonix' ) ),
			'elonix-woo'      => array( 'title' => esc_html__( 'Elonix WooCommerce', 'elonix' ) ),
			'elonix-acf'      => array( 'title' => esc_html__( 'Elonix ACF', 'elonix' ) ),
			'elonix-meta'     => array( 'title' => esc_html__( 'Elonix Meta', 'elonix' ) ),
		);

		foreach ( $groups as $group_id => $group_args ) {
			$dynamic_tags_manager->register_group( $group_id, $group_args );
		}
	}

	/**
	 * Include tag files.
	 */
	private function include_files() {
		// Centralized Data Engine
		require_once ELONIX_ACC_PATH . 'inc/managers/class-dynamic-data.php';

		// Base Class
		require_once ELONIX_ACC_PATH . 'inc/elementor/dynamic-tags/class-base-tag.php';
		require_once ELONIX_ACC_PATH . 'inc/elementor/dynamic-tags/class-data-tag.php';

		// Categories
		$files = array(
			'post-title',
			'post-excerpt',
			'post-content',
			'post-url',
			'post-slug',
			'post-featured-image',
			'post-date',
			'post-info',
			'author-info',
			'author-url',
			'author-profile-picture',
			'site-title',
			'site-tagline',
			'site-url',
			'site-logo',
			'site-date-time',
			'term-info',
			'term-url',
			'search-info',
			'archive-info',
			'archive-url',
			'user-info',
			'user-profile-picture',
			'comments-info',
			'media-info',
			'url-info',
			'post-meta',
			'acf-text',
			'acf-image',
			'acf-gallery',
			'acf-url',
			'woo-product-price',
			'woo-product-image',
			'woo-product-rating',
			'woo-product-sku',
						'woo-product-stock',
			'post-navigation',
			'post-navigation-url',
			'current-date-time',
			'request-info',
			'smart-tags',
			'author-meta',
			'term-meta',
			'user-meta',
			'woo-product-title',
			'woo-product-url',
			'woo-product-attributes',
			'woo-product-terms',
		);

		foreach ( $files as $file ) {
			$path = ELONIX_ACC_PATH . 'inc/elementor/dynamic-tags/tags/class-' . $file . '.php';
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}
	}

	/**
	 * Register tag classes.
	 *
	 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags_manager
	 */
	private function register_tag_classes( $dynamic_tags_manager ) {
		$tags = array(
			'Elonix_Dynamic_Tag_Post_Title',
			'Elonix_Dynamic_Tag_Post_Excerpt',
			'Elonix_Dynamic_Tag_Post_Content',
			'Elonix_Dynamic_Tag_Post_Url',
			'Elonix_Dynamic_Tag_Post_Slug',
			'Elonix_Dynamic_Tag_Post_Featured_Image',
			'Elonix_Dynamic_Tag_Post_Date',
			'Elonix_Dynamic_Tag_Post_Info',
			'Elonix_Dynamic_Tag_Author_Info',
			'Elonix_Dynamic_Tag_Author_Url',
			'Elonix_Dynamic_Tag_Author_Profile_Picture',
			'Elonix_Dynamic_Tag_Site_Title',
			'Elonix_Dynamic_Tag_Site_Tagline',
			'Elonix_Dynamic_Tag_Site_Url',
			'Elonix_Dynamic_Tag_Site_Logo',
			'Elonix_Dynamic_Tag_Site_Date_Time',
			'Elonix_Dynamic_Tag_Term_Info',
			'Elonix_Dynamic_Tag_Term_Url',
			'Elonix_Dynamic_Tag_Search_Info',
			'Elonix_Dynamic_Tag_Archive_Info',
			'Elonix_Dynamic_Tag_Archive_Url',
			'Elonix_Dynamic_Tag_User_Info',
			'Elonix_Dynamic_Tag_User_Profile_Picture',
			'Elonix_Dynamic_Tag_Comments_Info',
			'Elonix_Dynamic_Tag_Media_Info',
			'Elonix_Dynamic_Tag_Url_Info',
			'Elonix_Dynamic_Tag_Post_Meta',
			'Elonix_Dynamic_Tag_Acf_Text',
			'Elonix_Dynamic_Tag_Acf_Image',
			'Elonix_Dynamic_Tag_Acf_Gallery',
			'Elonix_Dynamic_Tag_Acf_Url',
			'Elonix_Dynamic_Tag_Woo_Product_Price',
			'Elonix_Dynamic_Tag_Woo_Product_Image',
			'Elonix_Dynamic_Tag_Woo_Product_Rating',
			'Elonix_Dynamic_Tag_Woo_Product_Sku',
						'Elonix_Dynamic_Tag_Woo_Product_Stock',
			'Elonix_Dynamic_Tag_Post_Navigation',
			'Elonix_Dynamic_Tag_Post_Navigation_Url',
			'Elonix_Dynamic_Tag_Current_Date_Time',
			'Elonix_Dynamic_Tag_Request_Info',
			'Elonix_Dynamic_Tag_Smart_Tags',
			'Elonix_Dynamic_Tag_Author_Meta',
			'Elonix_Dynamic_Tag_Term_Meta',
			'Elonix_Dynamic_Tag_User_Meta',
			'Elonix_Dynamic_Tag_Woo_Product_Title',
			'Elonix_Dynamic_Tag_Woo_Product_Url',
			'Elonix_Dynamic_Tag_Woo_Product_Attributes',
			'Elonix_Dynamic_Tag_Woo_Product_Terms',
		);

		foreach ( $tags as $tag_class ) {
			if ( class_exists( $tag_class ) ) {
				$dynamic_tags_manager->register( new $tag_class() );
			}
		}
	}
}
