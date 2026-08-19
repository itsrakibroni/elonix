<?php
/**
 * Elonix Header & Footer Builder Main Orchestrator
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Header_Footer_Builder {

	/**
	 * Single class instance.
	 *
	 * @var Elonix_Header_Footer_Builder|null
	 */
	private static $_instance = null;

	/**
	 * Display conditions manager instance.
	 *
	 * @var Elonix_Display_Conditions
	 */
	public $display_conditions;

	/**
	 * Rendering engine instance.
	 *
	 * @var Elonix_Rendering_Engine
	 */
	public $rendering_engine;

	/**
	 * Admin UI manager instance.
	 *
	 * @var Elonix_Admin_UI
	 */
	public $admin_ui;

	/**
	 * Shortcodes instance.
	 *
	 * @var Elonix_Shortcodes
	 */
	public $shortcodes;

	/**
	 * Get class instance.
	 *
	 * @return Elonix_Header_Footer_Builder Instance.
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
	private function __construct() {
		// Initialize on plugins_loaded to check if Elementor and module manager are loaded
		add_action( 'plugins_loaded', array( $this, 'init' ), 30 );
	}

	/**
	 * Initialize the components.
	 */
	public function init() {
		// Verify Elementor is active
		if ( ! did_action( 'elementor/loaded' ) && ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		$header_active = false;
		$footer_active = false;

		if ( class_exists( 'Elonix_Toolkit_Module_Manager' ) ) {
			$header_active = Elonix_Toolkit_Module_Manager::is_module_enabled( 'header_builder' );
			$footer_active = Elonix_Toolkit_Module_Manager::is_module_enabled( 'footer_builder' );
		}

		// Only boot up if at least one builder module is active
		if ( ! $header_active && ! $footer_active ) {
			return;
		}

		// Register Post Types
		add_action( 'init', array( $this, 'register_builder_post_types' ) );

		// Load Sub-modules
		require_once ELONIX_ACC_PATH . 'inc/header-footer/class-display-conditions.php';
		require_once ELONIX_ACC_PATH . 'inc/header-footer/class-rendering-engine.php';
		require_once ELONIX_ACC_PATH . 'inc/header-footer/class-admin-ui.php';
		require_once ELONIX_ACC_PATH . 'inc/header-footer/class-shortcodes.php';

		$this->display_conditions = new Elonix_Display_Conditions();
		$this->rendering_engine   = new Elonix_Rendering_Engine( $this->display_conditions );
		$this->shortcodes         = new Elonix_Shortcodes();
		$this->admin_ui           = new Elonix_Admin_UI( $this->display_conditions );

		// Template Preview Hooks: hijack query and override template loading
		add_action( 'pre_get_posts', array( $this, 'hijack_preview_query' ) );
		add_filter( 'template_include', array( $this, 'load_preview_template' ), 99 );

		// Preview Page Admin Bar Disabler
		add_filter( 'show_admin_bar', array( $this, 'disable_preview_admin_bar' ) );
		add_action( 'wp', array( $this, 'remove_preview_admin_bar_actions' ) );
		add_filter( 'body_class', array( $this, 'remove_preview_admin_bar_body_classes' ), 999999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_preview_admin_bar_assets' ), 9999999 );
		add_action( 'wp_print_styles', array( $this, 'dequeue_preview_admin_bar_assets' ), 9999999 );

		// Security: prevent direct front-end single template access for non-admins
		add_action( 'template_redirect', array( $this, 'restrict_template_frontend_view' ) );
	}

	/**
	 * Register Custom Post Types conditionally.
	 * show_in_menu is set to false to support a single, clean admin menu.
	 */
	public function register_builder_post_types() {
		$header_active = Elonix_Toolkit_Module_Manager::is_module_enabled( 'header_builder' );
		$footer_active = Elonix_Toolkit_Module_Manager::is_module_enabled( 'footer_builder' );

		// Register Header CPT
		if ( $header_active ) {
			$header_labels = array(
				'name'               => esc_html__( 'Headers', 'elonix' ),
				'singular_name'      => esc_html__( 'Header', 'elonix' ),
				'add_new'            => esc_html__( 'Add New Header', 'elonix' ),
				'add_new_item'       => esc_html__( 'Add New Header Layout', 'elonix' ),
				'edit_item'          => esc_html__( 'Edit Header Layout', 'elonix' ),
				'new_item'           => esc_html__( 'New Header Layout', 'elonix' ),
				'all_items'          => esc_html__( 'All Headers', 'elonix' ),
				'view_item'          => esc_html__( 'View Header Layout', 'elonix' ),
				'search_items'       => esc_html__( 'Search Header Layouts', 'elonix' ),
				'not_found'          => esc_html__( 'No Header Layouts Found', 'elonix' ),
				'not_found_in_trash' => esc_html__( 'No Header Layouts Found in Trash', 'elonix' ),
			);

			register_post_type(
				'elonix_header',
				array(
					'labels'              => $header_labels,
					'public'              => true,
					'show_ui'             => true,
					'show_in_menu'        => false, // Managed inside consolidated menu
					'show_in_nav_menus'   => false,
					'exclude_from_search' => true,
					'publicly_queryable'  => true, // Required for Elementor editor preview loading
					'has_archive'         => false,
					'hierarchical'        => false,
					'supports'            => array( 'title', 'editor', 'elementor' ),
					'show_in_rest'        => true,
					'menu_icon'           => 'dashicons-editor-insertmore',
					'capabilities'        => array(
						'edit_post'              => 'manage_options',
						'read_post'              => 'read',
						'delete_post'            => 'manage_options',
						'edit_posts'             => 'manage_options',
						'edit_others_posts'      => 'manage_options',
						'publish_posts'          => 'manage_options',
						'read_private_posts'     => 'manage_options',
						'delete_posts'           => 'manage_options',
						'delete_others_posts'    => 'manage_options',
						'delete_private_posts'   => 'manage_options',
						'delete_published_posts' => 'manage_options',
						'create_posts'           => 'manage_options',
					),
				)
			);
		}

		// Register Footer CPT
		if ( $footer_active ) {
			$footer_labels = array(
				'name'               => esc_html__( 'Footers', 'elonix' ),
				'singular_name'      => esc_html__( 'Footer', 'elonix' ),
				'add_new'            => esc_html__( 'Add New Footer', 'elonix' ),
				'add_new_item'       => esc_html__( 'Add New Footer Layout', 'elonix' ),
				'edit_item'          => esc_html__( 'Edit Footer Layout', 'elonix' ),
				'new_item'           => esc_html__( 'New Footer Layout', 'elonix' ),
				'all_items'          => esc_html__( 'All Footers', 'elonix' ),
				'view_item'          => esc_html__( 'View Footer Layout', 'elonix' ),
				'search_items'       => esc_html__( 'Search Footer Layouts', 'elonix' ),
				'not_found'          => esc_html__( 'No Footer Layouts Found', 'elonix' ),
				'not_found_in_trash' => esc_html__( 'No Footer Layouts Found in Trash', 'elonix' ),
			);

			register_post_type(
				'elonix_footer',
				array(
					'labels'              => $footer_labels,
					'public'              => true,
					'show_ui'             => true,
					'show_in_menu'        => false,
					'show_in_nav_menus'   => false,
					'exclude_from_search' => true,
					'publicly_queryable'  => true,
					'has_archive'         => false,
					'hierarchical'        => false,
					'supports'            => array( 'title', 'editor', 'elementor' ),
					'show_in_rest'        => true,
					'menu_icon'           => 'dashicons-editor-insertmore',
					'capabilities'        => array(
						'edit_post'              => 'manage_options',
						'read_post'              => 'read',
						'delete_post'            => 'manage_options',
						'edit_posts'             => 'manage_options',
						'edit_others_posts'      => 'manage_options',
						'publish_posts'          => 'manage_options',
						'read_private_posts'     => 'manage_options',
						'delete_posts'           => 'manage_options',
						'delete_others_posts'    => 'manage_options',
						'delete_private_posts'   => 'manage_options',
						'delete_published_posts' => 'manage_options',
						'create_posts'           => 'manage_options',
					),
				)
			);
		}
	}

	/**
	 * Prevent direct access to single builder template pages.
	 */
	public function restrict_template_frontend_view() {
		// Do not redirect if we are viewing the template preview endpoint
		if ( isset( $_GET['es_preview'] ) && $this->is_preview_request_authorized( intval( wp_unslash( $_GET['es_preview'] ) ) ) ) {
			return;
		}
		$layout_types = array( 'elonix_header', 'elonix_footer' );
		if ( is_singular( $layout_types ) ) {
			// Allow administrators or users with post editing capabilities to view (for Elementor editing & previewing)
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_safe_redirect( home_url( '/' ) );
				exit;
			}
		}
	}

	public function hijack_preview_query( $query ) {
		if ( ! is_admin() && $query->is_main_query() && isset( $_GET['es_preview'] ) ) {
			$template_id = intval( wp_unslash( $_GET['es_preview'] ) );
			if ( $template_id && $this->is_preview_request_authorized( $template_id ) ) {
				$post = get_post( $template_id );
				if ( $post && in_array( $post->post_type, array( 'elonix_header', 'elonix_footer' ), true ) ) {
					$module = ( 'elonix_header' === $post->post_type ) ? 'header_builder' : 'footer_builder';
					if ( ! Elonix_Toolkit_Module_Manager::is_module_enabled( $module ) ) {
						return;
					}
					// Reset query variables to query only the specific template, preventing homepage conflicts
					$query->query_vars = array(
						'post_type'   => $post->post_type,
						'p'           => $template_id,
						'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
					);
					// Set correct singular flags
					$query->is_single   = true;
					$query->is_singular = true;
					$query->is_home     = false;
					$query->is_archive  = false;
					$query->is_category = false;
					$query->is_tag      = false;
					$query->is_tax      = false;
					$query->is_page     = false;
				}
			}
		}
	}

	/**
	 * Verify that the current request is allowed to preview a given (possibly unpublished)
	 * header/footer template: a valid nonce plus edit rights on that specific post.
	 * Centralised here so hijack_preview_query() and load_preview_template() stay in sync.
	 *
	 * @param int $template_id Post ID being requested for preview.
	 * @return bool
	 */
	private function is_preview_request_authorized( $template_id ) {
		if ( ! isset( $_GET['_wpnonce'] ) ) {
			return false;
		}
		$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'elonix_header_footer_preview' ) ) {
			return false;
		}
		return current_user_can( 'edit_post', $template_id );
	}

	/**
	 * Override the loaded template on preview requests to load our dedicated preview-template.php.
	 *
	 * @param string $template Path to the template file.
	 * @return string Path to the preview template file.
	 */
	public function load_preview_template( $template ) {
		if ( isset( $_GET['es_preview'] ) ) {
			$template_id = intval( wp_unslash( $_GET['es_preview'] ) );
			if ( $template_id && $this->is_preview_request_authorized( $template_id ) ) {
				$post = get_post( $template_id );
				if ( $post && in_array( $post->post_type, array( 'elonix_header', 'elonix_footer' ), true ) ) {
					$module = ( 'elonix_header' === $post->post_type ) ? 'header_builder' : 'footer_builder';
					if ( ! Elonix_Toolkit_Module_Manager::is_module_enabled( $module ) ) {
						return $template;
					}
					$preview_template = ELONIX_ACC_PATH . 'inc/header-footer/preview-template.php';
					if ( file_exists( $preview_template ) ) {
						return $preview_template;
					}
				}
			}
		}
		return $template;
	}

	/**
	 * Disable the admin bar for template preview requests.
	 *
	 * @param bool $show Whether to show the admin bar.
	 * @return bool False if it is a template preview request, original value otherwise.
	 */
	public function disable_preview_admin_bar( $show ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- cosmetic only (hides admin bar chrome); presence check, no data read or output.
		if ( isset( $_GET['es_preview'] ) ) {
			return false;
		}
		return $show;
	}

	/**
	 * Remove native WordPress admin bar render actions during template preview.
	 */
	public function remove_preview_admin_bar_actions() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- cosmetic only (hides admin bar chrome); presence check, no data read or output.
		if ( isset( $_GET['es_preview'] ) ) {
			remove_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
			remove_action( 'wp_head', 'wp_admin_bar_header' );
			remove_action( 'wp_body_open', 'wp_admin_bar_render', 0 );
		}
	}

	/**
	 * Dequeue WordPress admin bar styles and scripts to prevent any visual leaks.
	 */
	public function dequeue_preview_admin_bar_assets() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- cosmetic only (dequeues admin-bar assets); presence check, no data read or output.
		if ( isset( $_GET['es_preview'] ) ) {
			wp_dequeue_style( 'admin-bar' );
			wp_dequeue_script( 'admin-bar' );

			// Dequeue plugin/theme toolbar assets or admin helper scripts enqueued on frontend previews
			wp_dequeue_style( 'cdp-css-global' );
			wp_dequeue_style( 'cdp-css' );
			wp_dequeue_style( 'cdp-css-user' );
			wp_dequeue_style( 'cdp-tooltips-css' );
			wp_dequeue_style( 'cdp-css-select' );

			wp_dequeue_script( 'cdp-js-global' );
			wp_dequeue_script( 'cdp' );
			wp_dequeue_script( 'cdp-tooltips' );
			wp_dequeue_script( 'cdp-modal' );
			wp_dequeue_script( 'cdp-js-user' );
			wp_dequeue_script( 'cdp-js-select' );

			wp_dequeue_script( 'jquery-ui-core' );
			wp_dequeue_script( 'jquery-ui-mouse' );
			wp_dequeue_script( 'jquery-ui-draggable' );
			wp_dequeue_script( 'jquery-ui-droppable' );
			wp_dequeue_script( 'jquery-ui-sortable' );
		}
	}

	/**
	 * Remove admin bar related CSS classes from the body tag list on preview page requests.
	 *
	 * @param array $classes Body classes.
	 * @return array Cleaned body classes.
	 */
	public function remove_preview_admin_bar_body_classes( $classes ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- cosmetic only (body class list); presence check, no data read or output.
		if ( isset( $_GET['es_preview'] ) ) {
			$classes = array_diff( $classes, array( 'admin-bar', 'wp-toolbar' ) );
		}
		return $classes;
	}
}
