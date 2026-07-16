<?php
/**
 * Elonix – Toolkit for Elementor Elementor Widget Category System
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Widget_Category {

	/**
	 * Class instance holder.
	 *
	 * @var Elonix_Toolkit_Widget_Category|null
	 */
	private static $_instance = null;

	/**
	 * Predefined widget categories.
	 *
	 * @var array
	 */
	private $categories = array();

	/**
	 * Initialize categories lazily.
	 */
	private function init_categories() {
		if ( empty( $this->categories ) ) {
			// 1. Define all possible Elonix categories with metadata
			$all_categories = array(
				'elonix-widgets'       => array(
					'title' => esc_html__( 'Elonix – Toolkit for Elementor', 'elonix' ),
					'icon'  => 'eicon-star',
				),
				'elonix-theme-builder' => array(
					'title' => esc_html__( 'Elonix Theme Builder', 'elonix' ),
					'icon'  => 'eicon-hammer',
				),
				'elonix-archive'       => array(
					'title' => esc_html__( 'Elonix Archive', 'elonix' ),
					'icon'  => 'eicon-archive',
				),
				'elonix-woocommerce'   => array(
					'title' => esc_html__( 'Elonix WooCommerce', 'elonix' ),
					'icon'  => 'eicon-woocommerce',
				),
			);

			// 2. Scan registered widgets to find which categories are actually populated
			$active_categories = array();
			if ( class_exists( 'Elonix_Toolkit_Widget_Registry' ) ) {
				$registered_widgets = Elonix_Toolkit_Widget_Registry::instance()->get_widgets();
				foreach ( $registered_widgets as $widget ) {
					if ( ! empty( $widget['category'] ) ) {
						$active_categories[ $widget['category'] ] = true;
					}
				}
			}

			// 3. Register only the categories that contain widgets
			foreach ( $all_categories as $slug => $args ) {
				if ( isset( $active_categories[ $slug ] ) ) {
					$this->categories[ $slug ] = $args;
				}
			}
		}
	}

	/**
	 * Get class instance.
	 *
	 * @return Elonix_Toolkit_Widget_Category Instance.
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
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_categories_hook' ) );
	}

	/**
	 * Reusable category registration helper method.
	 *
	 * @param object $elements_manager Elementor elements manager instance.
	 * @param string $slug             Category slug.
	 * @param array  $args             Category parameters.
	 */
	public function register_category( $elements_manager, $slug, $args ) {
		// Safeguard checking that Elementor's category registration API exists
		if ( ! method_exists( $elements_manager, 'add_category' ) ) {
			return;
		}

		// Prevent duplicate registration check if get_categories is supported
		if ( method_exists( $elements_manager, 'get_categories' ) ) {
			$registered = $elements_manager->get_categories();
			if ( isset( $registered[ $slug ] ) ) {
				return;
			}
		}

		$elements_manager->add_category( $slug, $args );
	}

	/**
	 * Callback handler to register all predefined categories.
	 *
	 * @param object $elements_manager Elementor elements manager instance.
	 */
	public function register_categories_hook( $elements_manager ) {
		$this->init_categories();
		foreach ( $this->categories as $slug => $args ) {
			$this->register_category( $elements_manager, $slug, $args );
		}
	}

	/**
	 * Retrieve primary category slug.
	 *
	 * @return string Slug.
	 */
	public function get_category_slug() {
		return 'elonix-widgets';
	}

	/**
	 * Retrieve primary category title.
	 *
	 * @return string Title.
	 */
	public function get_category_title() {
		return esc_html__( 'Elonix – Toolkit for Elementor', 'elonix' );
	}

	/**
	 * Extensible method to register categories dynamically before action execution.
	 *
	 * @param string $slug Category slug.
	 * @param array  $args Category arguments.
	 */
	public function add_category( $slug, $args ) {
		$this->init_categories();
		if ( ! isset( $this->categories[ $slug ] ) ) {
			$this->categories[ $slug ] = wp_parse_args(
				$args,
				array(
					'title' => '',
					'icon'  => 'eicon-star',
				)
			);
		}
	}
}
