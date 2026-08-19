<?php
/**
 * Plugin Name: Elonix – Toolkit for Elementor
 * Plugin URI:  https://wordpress.org/plugins/elonix/
 * Description: A lightweight and powerful Elementor addon with 30+ widgets, Header & Footer Builder, Popup Builder, Archive Builder, and more.
 * Version: 1.0.0
 * Author: itsrakibroni
 * Author URI: https://profiles.wordpress.org/itsrakibroni/
 * Text Domain: elonix
 * Domain Path: /languages/
 * Requires PHP: 8.2
 * Requires at least: 6.0
 * Tested up to: 7.0
 * Requires Plugins: elementor
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'ELONIX_VERSION', '1.0.0' );
define( 'ELONIX_ACC_PATH', plugin_dir_path( __FILE__ ) );
define( 'ELONIX_ACC_URL', plugin_dir_url( __FILE__ ) );

/**
 * Absolute path to the main plugin file.
 * Required by Elonix_Toolkit_Dependency_Manager for deactivation calls.
 */
if ( ! defined( 'ELONIX_PLUGIN_FILE' ) ) {
	define( 'ELONIX_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'ELONIX_FRAMEWORK_VAR' ) ) {
	define( 'ELONIX_FRAMEWORK_VAR', 'elonix_settings' );
}
if ( ! defined( 'ELONIX_WIDGET_BADGE' ) ) {
	define( 'ELONIX_WIDGET_BADGE', 'ESKIT' );
}

// -------------------------------------------------------------------------
// Step 1: Load the Dependency Manager FIRST — before everything else.
// This must run before the autoloader so no Elementor-dependent class is
// loaded when Elementor itself is not present.
// -------------------------------------------------------------------------
require_once ELONIX_ACC_PATH . 'inc/class-dependency-manager.php';

$elonix_dependency_manager = Elonix_Toolkit_Dependency_Manager::instance();
$elonix_dependency_manager->register_hooks();

// -------------------------------------------------------------------------
// Step 2: Load Autoloader.
// -------------------------------------------------------------------------
require_once ELONIX_ACC_PATH . 'inc/class-autoloader.php';
Elonix_Toolkit\Autoloader::register();

// -------------------------------------------------------------------------
// Step 3: Gate ALL remaining plugin initialization behind the dependency.
// Nothing below this line runs unless Elementor is installed AND active.
//
// IMPORTANT: register_activation_hook() and register_deactivation_hook() are
// registered here, BEFORE the dependency gate, so WordPress always knows
// about them even when Elementor is absent.
// -------------------------------------------------------------------------
register_activation_hook( __FILE__, 'elonix_activate' );
register_deactivation_hook( __FILE__, 'elonix_deactivate' );

if ( ! $elonix_dependency_manager->is_dependency_met() ) {
	// Dependency not met. Only the dependency manager runs (notices, auto-deactivation).
	return;
}
// -------------------------------------------------------------------------
// Load Elonix Core Bootstrap
// -------------------------------------------------------------------------
require_once ELONIX_ACC_PATH . 'inc/core/class-core-bootstrap.php';

require_once ELONIX_ACC_PATH . 'inc/admin-settings.php';
// require_once ELONIX_ACC_PATH . 'inc/helpers/class-breadcrumb-helper.php';
// require_once ELONIX_ACC_PATH . 'inc/helpers/class-title-helper.php';
// require_once ELONIX_ACC_PATH . 'inc/helpers/class-button-helper.php';
// require_once ELONIX_ACC_PATH . 'inc/helpers/class-accordion-helper.php';
// require_once ELONIX_ACC_PATH . 'inc/helpers/class-nav-walker.php';

// Load Post Edit Overlay globally
require_once ELONIX_ACC_PATH . 'inc/helpers/class-edit-overlay.php';

// Load widget manager globally
// require_once ELONIX_ACC_PATH . 'inc/managers/class-widget-manager.php';
add_action( 'init', array( 'Elonix_Toolkit_Widget_Manager', 'init' ), 5 );

// Load module manager globally on init to prevent early translation execution
// require_once ELONIX_ACC_PATH . 'inc/managers/class-module-manager.php';
add_action( 'init', array( 'Elonix_Toolkit_Module_Manager', 'init' ), 2 );

// Load Assignment Engine
require_once ELONIX_ACC_PATH . 'inc/managers/class-assignment-engine.php';
Elonix_Assignment_Engine::instance();
if ( is_admin() ) {
	require_once ELONIX_ACC_PATH . 'inc/managers/class-assignment-admin.php';
	Elonix_Assignment_Admin::instance();
	Elonix_Admin_Row_Actions::instance();
}

// Load Header & Footer Builder conditionally
$elonix_header_enabled = Elonix_Toolkit_Module_Manager::is_module_enabled( 'header_builder' );
$elonix_footer_enabled = Elonix_Toolkit_Module_Manager::is_module_enabled( 'footer_builder' );
if ( $elonix_header_enabled || $elonix_footer_enabled ) {
	// require_once ELONIX_ACC_PATH . 'inc/header-footer/class-header-footer-builder.php';
	Elonix_Header_Footer_Builder::instance();
}

// Load Advanced 404 Builder conditionally
if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'advanced_404_builder' ) ) {
	// require_once ELONIX_ACC_PATH . 'inc/modules/404-builder/class-404-builder.php';
	Elonix_Toolkit_404_Builder::instance();
}

// Load Popup Builder conditionally
if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'popup_builder' ) ) {
	// require_once ELONIX_ACC_PATH . 'inc/modules/popup-builder/class-popup-builder.php';
	Elonix_Toolkit_Popup_Builder::instance();
}

// Load Archive Builder conditionally
if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'archive_builder' ) ) {
	// require_once ELONIX_ACC_PATH . 'inc/modules/archive-builder/class-archive-builder.php';
	Elonix_Toolkit_Archive_Builder::instance();
}

// Load Single Builder conditionally
if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'single_builder' ) ) {
	require_once ELONIX_ACC_PATH . 'inc/modules/single-builder/class-single-builder.php';
	Elonix_Toolkit_Single_Builder::instance();
}

// Load Search Builder conditionally
if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'search_builder' ) ) {
	Elonix_Search_Builder::instance();
}

// Load Template Library conditionally
if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'template_library' ) ) {
	\Elonix_Toolkit\Modules\Template_Library\Module::instance();
}

// Load Screen Loader Module conditionally
if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'screen_loader' ) ) {
	\Elonix_Toolkit\Modules\Screen_Loader\Module::instance();
}

// Load Target Rules globally (Required for all Builders)
require_once ELONIX_ACC_PATH . 'inc/managers/class-target-rules.php';
Elonix_Target_Rules::instance();

// Load settings framework globally
// require_once ELONIX_ACC_PATH . 'inc/framework/class-settings-framework.php';

// Load assets manager globally
// require_once ELONIX_ACC_PATH . 'inc/assets/class-assets-manager.php';
new Elonix_Toolkit_Assets_Manager();

// Load Search AJAX Handler globally
add_action( 'plugins_loaded', 'elonix_init_search_ajax', 25 );
function elonix_init_search_ajax() {
	$ajax_path = ELONIX_ACC_PATH . 'widgets/es-search/class-search-ajax.php';
	if ( file_exists( $ajax_path ) ) {
		require_once $ajax_path;
		if ( class_exists( 'Elonix_Toolkit_Search_AJAX_Handler' ) ) {
			new Elonix_Toolkit_Search_AJAX_Handler();
		}
	}
}

// Load Search Results AJAX Handler globally
add_action( 'plugins_loaded', 'elonix_init_search_results_ajax', 25 );
function elonix_init_search_results_ajax() {
	$ajax_path = ELONIX_ACC_PATH . 'widgets/es-search-results/class-ajax-handler.php';
	if ( file_exists( $ajax_path ) ) {
		require_once $ajax_path;
		if ( class_exists( 'Elonix_Toolkit_Search_Results_AJAX_Handler' ) ) {
			Elonix_Toolkit_Search_Results_AJAX_Handler::register_hooks();
		}
	}
}

// Load Post List AJAX Handler globally
add_action( 'plugins_loaded', 'elonix_init_post_list_ajax', 26 );
function elonix_init_post_list_ajax() {
	$ajax_path = ELONIX_ACC_PATH . 'widgets/es-post-list/handler-ajax.php';
	if ( file_exists( $ajax_path ) ) {
		require_once $ajax_path;
	}
}

// Load Post Block AJAX Handler globally
add_action( 'plugins_loaded', 'elonix_init_post_block_ajax', 27 );
function elonix_init_post_block_ajax() {
	$ajax_path = ELONIX_ACC_PATH . 'widgets/es-post-block/handler-ajax.php';
	if ( file_exists( $ajax_path ) ) {
		require_once $ajax_path;
	}
}

// Load Gallery AJAX Handler globally
add_action( 'plugins_loaded', 'elonix_init_gallery_ajax', 28 );
function elonix_init_gallery_ajax() {
	$ajax_path = ELONIX_ACC_PATH . 'widgets/es-gallery/handler-ajax.php';
	if ( file_exists( $ajax_path ) ) {
		require_once $ajax_path;
	}
}

// Load Post Comments AJAX Handler globally
add_action( 'plugins_loaded', 'elonix_init_post_comments_ajax', 28 );
function elonix_init_post_comments_ajax() {
	$ajax_path = ELONIX_ACC_PATH . 'widgets/es-post-comments/class-ajax-handler.php';
	if ( file_exists( $ajax_path ) ) {
		require_once $ajax_path;
	}
}




if ( is_admin() ) {
	// require_once ELONIX_ACC_PATH . 'inc/admin/class-notices-manager.php';
	// require_once ELONIX_ACC_PATH . 'inc/admin/class-admin-menu.php';
	// require_once ELONIX_ACC_PATH . 'inc/admin/class-dashboard-page.php';
	// require_once ELONIX_ACC_PATH . 'inc/admin/class-widgets-page.php';
	// require_once ELONIX_ACC_PATH . 'inc/admin/class-modules-page.php';
	// require_once ELONIX_ACC_PATH . 'inc/admin/class-settings-page.php';
	// require_once ELONIX_ACC_PATH . 'inc/admin/class-system-info-page.php';
	// require_once ELONIX_ACC_PATH . 'inc/import-export/class-import-export.php';
	// require_once ELONIX_ACC_PATH . 'inc/admin/class-uninstall-settings.php';
	new Elonix_Admin_Notices_Manager();
	new Elonix_Toolkit_Admin_Menu();
	new Elonix_Toolkit_Dashboard_Page();
	new Elonix_Toolkit_Widgets_Page();
	new Elonix_Toolkit_Modules_Page();
	new Elonix_Toolkit_Settings_Page();
	new Elonix_Toolkit_System_Info_Page();
	new Elonix_Toolkit_Import_Export();
	new Elonix_Toolkit_Uninstall_Settings();
}


// function elonix_load_textdomain() {
// 	load_plugin_textdomain( 'elonix', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
// }
// add_action( 'init', 'elonix_load_textdomain', 1 );


// Select page for link
function elonix_get_page_as_list() {
	$args = wp_parse_args(
		array(
			'post_type'   => 'page',
			'numberposts' => -1,
		)
	);

	$posts        = get_posts( $args );
	$post_options = array( esc_html__( '--Select Page--', 'elonix' ) => '' );

	if ( $posts ) {
		foreach ( $posts as $post ) {
			$post_options[ $post->post_title ] = $post->ID;
		}
	}
	$flipped = array_flip( $post_options );
	return $flipped;
}


/**
 * Post category list
 */
function elonix_get_post_cat_list() {
	// Get the current category ID
	$post_category_id = get_queried_object_id();

	// Define arguments to fetch child categories of the current category
	$args = array(
		'taxonomy'   => 'category',
		'parent'     => $post_category_id,
		'hide_empty' => false, // Change to true if you want to hide empty categories
	);

	// Get the child categories
	$terms = get_terms( $args );

	// Initialize the options array
	$cat_options = array(
		esc_html__( '-- Select Category --', 'elonix' ) => '',
	);

	// Loop through terms and populate options array
	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
			$cat_options[ $term->name ] = $term->term_id;
		}
	}

	return $cat_options;
}

// Project Category Select
function elonix_get_project_cat_list() {
	$terms       = get_terms( 'project_cat' );
	$cat_options = array( '' => '' );

	if ( $terms ) {
		foreach ( $terms as $term ) {
			$cat_options[ $term->name ] = $term->name;
		}
	}
	return $cat_options;
}

function elonix_disable_gutenberg_widget_editor() {
	remove_theme_support( 'widgets-block-editor' );
}
add_action( 'after_setup_theme', 'elonix_disable_gutenberg_widget_editor' );


// Load Elementor core integration globally
require_once ELONIX_ACC_PATH . 'inc/elementor/class-elementor-core.php';
Elonix_Toolkit_Elementor_Core::instance();

// Widget Options
require_once ELONIX_ACC_PATH . 'inc/widgets.php';
require_once ELONIX_ACC_PATH . 'inc/theme-social.php';
if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'custom_icons' ) ) {
	require_once ELONIX_ACC_PATH . 'inc/icons.php';
}


add_filter( 'script_loader_tag', 'elonix_clean_script_tag' );
function elonix_clean_script_tag( $input ) {
	$input = str_replace( array( 'type="text/javascript"', "type='text/javascript'" ), '', $input );
	return $input;
}




/**
 * Get the existing menus in array format
 *
 * @return array
 */
function elonix_get_menu_array() {
	$menus      = wp_get_nav_menus();
	$menu_array = array();
	foreach ( $menus as $menu ) {
		$menu_array[ $menu->slug ] = $menu->name;
	}
	return $menu_array;
}


// Pass placeholder to Comments Form
add_filter( 'comment_form_default_fields', 'elonix_comment_placeholders' );
function elonix_comment_placeholders( $fields ) {

	$name_place  = Elonix_Settings::get( 'form_name_place' ) ?? '';
	$email_place = Elonix_Settings::get( 'form_email_place' ) ?? '';
	$web_place   = Elonix_Settings::get( 'form_web_place' ) ?? '';

	$fields['author'] = str_replace(
		'<input',
		'<input placeholder="' . $name_place . '"',
		$fields['author']
	);
	$fields['email']  = str_replace(
		'<input',
		'<input placeholder="' . $email_place . '"',
		$fields['email']
	);
	$fields['url']    = str_replace(
		'<input',
		'<input placeholder="' . $web_place . '"',
		$fields['url']
	);
	return $fields;
}

/* Add Placehoder in comment Form Field (Comment) */
add_filter( 'comment_form_defaults', 'elonix_textarea_placeholder' );

function elonix_textarea_placeholder( $fields ) {
	$comment_place = Elonix_Settings::get( 'form_comment_ph' ) ?? '';

	$fields['comment_field'] = str_replace(
		'<textarea',
		'<textarea placeholder="' . $comment_place . '"',
		$fields['comment_field']
	);

	return $fields;
}


// Advanced search functionality
if ( ! function_exists( 'elonix_advanced_search_query' ) ) :
	function elonix_advanced_search_query( $query ) {
		if ( $query->is_search() ) {
			// category terms search.
			// Read-only search-query filter; value is sanitize_text_field()'d and only used inside a WP_Query tax_query array (parameterised), never output.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, see note above.
			if ( isset( $_GET['category'] ) && ! empty( $_GET['category'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, see note above.
				$category = isset( $_GET['category'] ) ? sanitize_text_field( wp_unslash( $_GET['category'] ) ) : '';
				$query->set(
					'tax_query',
					array(
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'slug',
							'terms'    => array( $category ),
						),
					)
				);
			} else {
				if ( is_admin() || ! $query->is_main_query() ) {
					return;
				}
				// Make sure this isn't the WooCommerce product search form
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only routing check, compared against a fixed string.
				if ( isset( $_GET['post_type'] ) && ( $_GET['post_type'] == 'product' ) ) {
					return;
				}
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only routing check, compared against a fixed string.
				if ( isset( $_GET['post_type'] ) && ( $_GET['post_type'] == 'service' ) ) {
					return;
				}
				$in_search_post_types = get_post_types( array( 'exclude_from_search' => false ) );
				// The post types you're removing (example: 'product', 'custom post types' and 'page')
				$post_types_to_remove = array( 'product', 'page', 'service' ); // Add here your custom posts name instead of custompost1, custompost2
				foreach ( $post_types_to_remove as $post_type_to_remove ) {
					if ( is_array( $in_search_post_types ) && in_array(
						$post_type_to_remove,
						$in_search_post_types
					) ) {
						unset( $in_search_post_types[ $post_type_to_remove ] );
						$query->set( 'post_type', $in_search_post_types );
					}
				}
			}
		}
		return $query;
	}
endif;
add_action( 'pre_get_posts', 'elonix_advanced_search_query' );

// Elementor Post Types Auto Checked - now handled in activation hook
// Note: elonix_enable_elementor_for_all_post_types removed - update_option now only runs on activation

/**
 * Plugin Activation Hook Callback
 *
 * Runs the dependency check first. If Elementor is missing or inactive,
 * the dependency manager deactivates this plugin and calls wp_die().
 * Only proceeds with CPT registration and option setup if Elementor is present.
 */
function elonix_activate() {
	// --- Dependency gate (Case 3) ---
	// Ensure plugin.php helpers are available during activation.
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	// Instantiate and run the dependency activation check.
	// This will deactivate the plugin and call wp_die() if Elementor is absent.
	Elonix_Toolkit_Dependency_Manager::instance()->handle_activation();

	// --- From here, Elementor is confirmed installed and active ---

	// Call CPT/taxonomy registration functions to register them before flushing


	// Register builder CPTs
	if ( class_exists( 'Elonix_Header_Footer_Builder' ) ) {
		Elonix_Header_Footer_Builder::instance()->register_builder_post_types();
	}

	// Disable Elementor's Default Colors and Default Fonts
	update_option( 'elementor_disable_color_schemes', 'yes' );
	update_option( 'elementor_disable_typography_schemes', 'yes' );

	// Enable Elementor for all post types including builder CPTs
	if ( did_action( 'elementor/init' ) ) {
		$cpt_support = get_option( 'elementor_cpt_support', array( 'page', 'post' ) );
		if ( ! is_array( $cpt_support ) ) {
			$cpt_support = array( 'page', 'post' );
		}
		$builder_cpts = array( 'elonix_header', 'elonix_footer', 'elonix_popup', 'elonix_archive', 'es_search_template' );
		if ( array_diff( $builder_cpts, $cpt_support ) ) {
			$post_types = get_post_types( array( 'public' => true ), 'names' );
			update_option( 'elementor_cpt_support', array_values( array_unique( array_merge( $cpt_support, $post_types, $builder_cpts ) ) ) );
		}
	}

	flush_rewrite_rules();
}


/**
 * Plugin Deactivation Hook Callback
 */
function elonix_deactivate() {
	flush_rewrite_rules();
}

$elonix_opt_name = ELONIX_FRAMEWORK_VAR;
