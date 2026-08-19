<?php
namespace Elonix_Toolkit;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elonix – Toolkit for Elementor Autoloader Class
 *
 * Manages PSR-4 namespace resolution mapping to physical files
 * and maintains backward compatibility via global class aliases.
 */
class Autoloader {

	/**
	 * Map namespaced classes directly to legacy class names and file paths.
	 *
	 * @var array
	 */
	private static $class_map = array(
		'Elonix_Toolkit\Managers\Widget_Manager'       => array( 'Elonix_Toolkit_Widget_Manager', 'inc/managers/class-widget-manager.php' ),
		'Elonix_Toolkit\Managers\Module_Manager'       => array( 'Elonix_Toolkit_Module_Manager', 'inc/managers/class-module-manager.php' ),
		'Elonix_Toolkit\Assets\Assets_Manager'         => array( 'Elonix_Toolkit_Assets_Manager', 'inc/assets/class-assets-manager.php' ),

		// Elementor Widgets
		'Elonix_Toolkit\Widgets\Advanced_Heading\Widget' => array( 'Elonix_Toolkit_Heading_Widget', 'widgets/advanced-heading/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Advanced_Button\Widget' => array( 'Elonix_Toolkit_Button_Widget', 'widgets/advanced-button/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Accordion\Widget'      => array( 'Elonix_Toolkit_Accordion_Widget', 'widgets/accordion/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Advanced_Image\Widget' => array( 'Elonix_Toolkit_Image_Widget', 'widgets/advanced-image/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Advanced_Icon_Box\Widget' => array( 'Elonix_Toolkit_Icon_Box_Widget', 'widgets/advanced-icon-box/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Feature_Cards\Widget'  => array( 'Elonix_Toolkit_Feature_Cards_Widget', 'widgets/feature-cards/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Advanced_Breadcrumb\Widget' => array( 'Elonix_Toolkit_Breadcrumb_Widget', 'widgets/advanced-breadcrumb/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Advanced_Post_Title\Widget' => array( 'Elonix_Toolkit_Post_Title_Widget', 'widgets/advanced-post-title/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Nav_Menu\Widget'       => array( 'Elonix_Toolkit_Nav_Menu_Widget', 'widgets/nav-menu/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Social_Icons\Widget'   => array( 'Elonix_Toolkit_Social_Icons_Widget', 'widgets/social-icons/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Error_Code\Widget'     => array( 'Elonix_Toolkit_Error_Code_Widget', 'widgets/error-code/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Search\Widget'         => array( 'Elonix_Toolkit_Search_Widget', 'widgets/es-search/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Search_Results\Widget' => array( 'Elonix_Toolkit_Search_Results_Widget', 'widgets/es-search-results/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Categories\Widget'     => array( 'Elonix_Toolkit_Categories_Widget', 'widgets/es-categories/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Post_List\Widget'      => array( 'Elonix_Toolkit_Post_List_Widget', 'widgets/es-post-list/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Tag_Cloud\Widget'      => array( 'Elonix_Toolkit_Tag_Cloud_Widget', 'widgets/es-tag-cloud/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Post_Block\Widget'     => array( 'Elonix_Toolkit_Post_Block_Widget', 'widgets/es-post-block/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Post_Block\Renderer'   => array( 'Elonix_Toolkit_Post_Block_Renderer', 'widgets/es-post-block/class-renderer.php' ),
		'Elonix_Toolkit\Widgets\Back_To_Top\Widget'    => array( 'Elonix_Toolkit_Back_To_Top_Widget', 'widgets/es-back-to-top/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Smart_Contact_Actions\Widget' => array( 'Elonix_Toolkit_Smart_Contact_Actions_Widget', 'widgets/es-smart-contact-actions/class-widget.php' ),
		'Elonix_Toolkit\Widgets\Marquee\Widget' => array( 'Elonix_Toolkit_Marquee_Widget', 'widgets/marquee/class-widget.php' ),

		// Modules
		'Elonix_Toolkit\Modules\404_Builder\Builder'   => array( 'Elonix_Toolkit_404_Builder', 'inc/modules/404-builder/class-404-builder.php' ),
		'Elonix_Toolkit\Modules\404_Builder\Admin'     => array( 'Elonix_Toolkit_404_Admin', 'inc/modules/404-builder/class-404-admin.php' ),
		'Elonix_Toolkit\Modules\404_Builder\Analytics' => array( 'Elonix_Toolkit_404_Analytics', 'inc/modules/404-builder/class-404-analytics.php' ),
		'Elonix_Toolkit\Modules\404_Builder\Renderer'  => array( 'Elonix_Toolkit_404_Renderer', 'inc/modules/404-builder/class-404-renderer.php' ),
		'Elonix_Toolkit\Modules\404_Builder\Router'    => array( 'Elonix_Toolkit_404_Router', 'inc/modules/404-builder/class-404-router.php' ),

		'Elonix_Toolkit\Modules\Archive_Builder\Builder' => array( 'Elonix_Toolkit_Archive_Builder', 'inc/modules/archive-builder/class-archive-builder.php' ),
		'Elonix_Toolkit\Modules\Archive_Builder\Admin' => array( 'Elonix_Toolkit_Archive_Admin', 'inc/modules/archive-builder/class-archive-admin.php' ),
		'Elonix_Toolkit\Modules\Archive_Builder\CPT'   => array( 'Elonix_Toolkit_Archive_CPT', 'inc/modules/archive-builder/class-archive-cpt.php' ),
		'Elonix_Toolkit\Modules\Archive_Builder\Preview' => array( 'Elonix_Toolkit_Archive_Preview', 'inc/modules/archive-builder/class-archive-preview.php' ),
		'Elonix_Toolkit\Modules\Archive_Builder\Renderer' => array( 'Elonix_Toolkit_Archive_Renderer', 'inc/modules/archive-builder/class-archive-renderer.php' ),

		'Elonix_Toolkit\Modules\Popup_Builder\Builder' => array( 'Elonix_Toolkit_Popup_Builder', 'inc/modules/popup-builder/class-popup-builder.php' ),
		'Elonix_Toolkit\Modules\Popup_Builder\Admin'   => array( 'Elonix_Toolkit_Popup_Admin', 'inc/modules/popup-builder/class-popup-admin.php' ),
		'Elonix_Toolkit\Modules\Popup_Builder\AJAX'    => array( 'Elonix_Toolkit_Popup_AJAX', 'inc/modules/popup-builder/class-popup-ajax.php' ),
		'Elonix_Toolkit\Modules\Popup_Builder\CPT'     => array( 'Elonix_Toolkit_Popup_CPT', 'inc/modules/popup-builder/class-popup-cpt.php' ),
		'Elonix_Toolkit\Modules\Popup_Builder\Renderer' => array( 'Elonix_Toolkit_Popup_Renderer', 'inc/modules/popup-builder/class-popup-renderer.php' ),

		'Elonix_Toolkit\Modules\Search_Builder\Builder' => array( 'Elonix_Search_Builder', 'inc/modules/search-builder/class-search-builder.php' ),
		'Elonix_Toolkit\Modules\Search_Builder\Admin'   => array( 'Elonix_Search_Admin', 'inc/modules/search-builder/class-search-admin.php' ),
		'Elonix_Toolkit\Modules\Search_Builder\CPT'     => array( 'Elonix_Search_CPT', 'inc/modules/search-builder/class-search-cpt.php' ),
		'Elonix_Toolkit\Modules\Search_Builder\Preview' => array( 'Elonix_Search_Preview', 'inc/modules/search-builder/class-search-preview.php' ),
		'Elonix_Toolkit\Modules\Search_Builder\Renderer' => array( 'Elonix_Search_Renderer', 'inc/modules/search-builder/class-search-renderer.php' ),

		// Template Library
		'Elonix_Toolkit\Modules\Template_Library\Module'    => array( 'Elonix_Toolkit_Template_Library', 'inc/modules/template-library/class-template-library.php' ),
		'Elonix_Toolkit\Modules\Template_Library\Discovery' => array( 'Elonix_Toolkit_Template_Discovery', 'inc/modules/template-library/class-template-discovery.php' ),
		'Elonix_Toolkit\Modules\Template_Library\Manifest'  => array( 'Elonix_Toolkit_Template_Manifest', 'inc/modules/template-library/class-template-manifest.php' ),
		'Elonix_Toolkit\Modules\Template_Library\Importer'  => array( 'Elonix_Toolkit_Template_Importer', 'inc/modules/template-library/class-template-importer.php' ),
		'Elonix_Toolkit\Modules\Template_Library\Admin'     => array( 'Elonix_Toolkit_Template_Admin', 'inc/modules/template-library/class-template-admin.php' ),
		'Elonix_Toolkit\Modules\Template_Library\REST'      => array( 'Elonix_Toolkit_Template_REST', 'inc/modules/template-library/class-template-rest.php' ),
		'Elonix_Toolkit\Modules\Template_Library\Cache'     => array( 'Elonix_Toolkit_Template_Cache', 'inc/modules/template-library/class-template-cache.php' ),

		// Header Footer Builder
		'Elonix_Toolkit\HeaderFooter\Builder'          => array( 'Elonix_Header_Footer_Builder', 'inc/header-footer/class-header-footer-builder.php' ),
		'Elonix_Toolkit\HeaderFooter\Admin_UI'         => array( 'Elonix_Admin_UI', 'inc/header-footer/class-admin-ui.php' ),
		'Elonix_Toolkit\HeaderFooter\Display_Conditions' => array( 'Elonix_Display_Conditions', 'inc/header-footer/class-display-conditions.php' ),
		'Elonix_Toolkit\HeaderFooter\Rendering_Engine' => array( 'Elonix_Rendering_Engine', 'inc/header-footer/class-rendering-engine.php' ),
		'Elonix_Toolkit\HeaderFooter\Shortcodes'       => array( 'Elonix_Shortcodes', 'inc/header-footer/class-shortcodes.php' ),

		// Helpers
		'Elonix_Toolkit\Helpers\Title_Helper'          => array( 'Elonix_Title_Helper', 'inc/helpers/class-title-helper.php' ),
		'Elonix_Toolkit\Helpers\Breadcrumb_Helper'     => array( 'Elonix_Breadcrumb_Helper', 'inc/helpers/class-breadcrumb-helper.php' ),
		'Elonix_Toolkit\Helpers\Button_Helper'         => array( 'Elonix_Button_Helper', 'inc/helpers/class-button-helper.php' ),
		'Elonix_Toolkit\Helpers\Accordion_Helper'      => array( 'Elonix_Accordion_Helper', 'inc/helpers/class-accordion-helper.php' ),
		'Elonix_Toolkit\Helpers\Nav_Menu_Walker'       => array( 'Elonix_Nav_Menu_Walker', 'inc/helpers/class-nav-walker.php' ),
		'Elonix_Toolkit\Helpers\Query_Context'         => array( 'Elonix_Query_Context', 'inc/helpers/class-query-context.php' ),
		'Elonix_Toolkit\Helpers\Settings'              => array( 'Elonix_Settings', 'inc/helpers/class-settings.php' ),

		// Admin
		'Elonix_Toolkit\Admin\Admin_Menu'              => array( 'Elonix_Toolkit_Admin_Menu', 'inc/admin/class-admin-menu.php' ),
		'Elonix_Toolkit\Admin\Dashboard_Page'          => array( 'Elonix_Toolkit_Dashboard_Page', 'inc/admin/class-dashboard-page.php' ),
		'Elonix_Toolkit\Admin\Modules_Page'            => array( 'Elonix_Toolkit_Modules_Page', 'inc/admin/class-modules-page.php' ),
		'Elonix_Toolkit\Admin\Notices_Manager'         => array( 'Elonix_Admin_Notices_Manager', 'inc/admin/class-notices-manager.php' ),
		'Elonix_Toolkit\Admin\Settings_Page'           => array( 'Elonix_Toolkit_Settings_Page', 'inc/admin/class-settings-page.php' ),
		'Elonix_Toolkit\Admin\System_Info_Page'        => array( 'Elonix_Toolkit_System_Info_Page', 'inc/admin/class-system-info-page.php' ),
		'Elonix_Toolkit\Admin\Uninstall_Settings'      => array( 'Elonix_Toolkit_Uninstall_Settings', 'inc/admin/class-uninstall-settings.php' ),
		'Elonix_Toolkit\Admin\Widgets_Page'            => array( 'Elonix_Toolkit_Widgets_Page', 'inc/admin/class-widgets-page.php' ),

		// Other framework and integration classes
		'Elonix_Toolkit\ImportExport\Import_Export'    => array( 'Elonix_Toolkit_Import_Export', 'inc/import-export/class-import-export.php' ),
		'Elonix_Toolkit\Diagnostics\System_Info'       => array( 'Elonix_Toolkit_System_Info', 'inc/diagnostics/class-system-info.php' ),
		'Elonix_Toolkit\Framework\Settings_Framework'  => array( 'Elonix_Toolkit_Settings_Framework', 'inc/framework/class-settings-framework.php' ),
		'Elonix_Toolkit\Elementor\Elementor_Core'      => array( 'Elonix_Toolkit_Elementor_Core', 'inc/elementor/class-elementor-core.php' ),
		'Elonix_Toolkit\Elementor\Widget_Base'         => array( 'Elonix_Widget_Base', 'inc/elementor/class-widget-base.php' ),
		'Elonix_Toolkit\Elementor\Widget_Category'     => array( 'Elonix_Toolkit_Widget_Category', 'inc/elementor/class-widget-category.php' ),
		'Elonix_Toolkit\Elementor\Widget_Loader'       => array( 'Elonix_Toolkit_Widget_Loader', 'inc/elementor/class-widget-loader.php' ),
		'Elonix_Toolkit\Elementor\Widget_Registry'     => array( 'Elonix_Toolkit_Widget_Registry', 'inc/elementor/class-widget-registry.php' ),
	);

	/**
	 * Map legacy global class names directly to their relative paths.
	 *
	 * @var array
	 */
	private static $legacy_map = array(
		'Elonix_Toolkit_Widget_Manager'               => 'inc/managers/class-widget-manager.php',
		'Elonix_Toolkit_Module_Manager'               => 'inc/managers/class-module-manager.php',
		'Elonix_Toolkit_Assets_Manager'               => 'inc/assets/class-assets-manager.php',
		'Elonix_Toolkit_Heading_Widget'               => 'widgets/advanced-heading/class-widget.php',
		'Elonix_Toolkit_Button_Widget'                => 'widgets/advanced-button/class-widget.php',
		'Elonix_Toolkit_Accordion_Widget'             => 'widgets/accordion/class-widget.php',
		'Elonix_Toolkit_Image_Widget'                 => 'widgets/advanced-image/class-widget.php',
		'Elonix_Toolkit_Icon_Box_Widget'              => 'widgets/advanced-icon-box/class-widget.php',
		'Elonix_Toolkit_Feature_Cards_Widget'         => 'widgets/feature-cards/class-widget.php',
		'Elonix_Toolkit_Breadcrumb_Widget'            => 'widgets/advanced-breadcrumb/class-widget.php',
		'Elonix_Toolkit_Post_Title_Widget'            => 'widgets/advanced-post-title/class-widget.php',
		'Elonix_Toolkit_Nav_Menu_Widget'              => 'widgets/nav-menu/class-widget.php',
		'Elonix_Toolkit_Social_Icons_Widget'          => 'widgets/social-icons/class-widget.php',
		'Elonix_Toolkit_Error_Code_Widget'            => 'widgets/error-code/class-widget.php',
		'Elonix_Toolkit_Search_Widget'                => 'widgets/es-search/class-widget.php',
		'Elonix_Toolkit_Search_Results_Widget'        => 'widgets/es-search-results/class-widget.php',
		'Elonix_Toolkit_Categories_Widget'            => 'widgets/es-categories/class-widget.php',
		'Elonix_Toolkit_Post_List_Widget'             => 'widgets/es-post-list/class-widget.php',
		'Elonix_Toolkit_Tag_Cloud_Widget'             => 'widgets/es-tag-cloud/class-widget.php',
		'Elonix_Toolkit_Post_Block_Widget'            => 'widgets/es-post-block/class-widget.php',
		'Elonix_Toolkit_Post_Block_Renderer'          => 'widgets/es-post-block/class-renderer.php',
		'Elonix_Toolkit_Back_To_Top_Widget'           => 'widgets/es-back-to-top/class-widget.php',
		'Elonix_Toolkit_Smart_Contact_Actions_Widget' => 'widgets/es-smart-contact-actions/class-widget.php',
		'Elonix_Toolkit_Marquee_Widget'               => 'widgets/marquee/class-widget.php',
		'Elonix_Toolkit_404_Builder'                  => 'inc/modules/404-builder/class-404-builder.php',
		'Elonix_Toolkit_404_Admin'                    => 'inc/modules/404-builder/class-404-admin.php',
		'Elonix_Toolkit_404_Analytics'                => 'inc/modules/404-builder/class-404-analytics.php',
		'Elonix_Toolkit_404_Renderer'                 => 'inc/modules/404-builder/class-404-renderer.php',
		'Elonix_Toolkit_404_Router'                   => 'inc/modules/404-builder/class-404-router.php',
		'Elonix_Toolkit_Archive_Builder'              => 'inc/modules/archive-builder/class-archive-builder.php',
		'Elonix_Toolkit_Archive_Admin'                => 'inc/modules/archive-builder/class-archive-admin.php',
		'Elonix_Toolkit_Archive_CPT'                  => 'inc/modules/archive-builder/class-archive-cpt.php',
		'Elonix_Toolkit_Archive_Preview'              => 'inc/modules/archive-builder/class-archive-preview.php',
		'Elonix_Toolkit_Archive_Renderer'             => 'inc/modules/archive-builder/class-archive-renderer.php',
		'Elonix_Toolkit_Popup_Builder'                => 'inc/modules/popup-builder/class-popup-builder.php',
		'Elonix_Toolkit_Popup_Admin'                  => 'inc/modules/popup-builder/class-popup-admin.php',
		'Elonix_Toolkit_Popup_AJAX'                   => 'inc/modules/popup-builder/class-popup-ajax.php',
		'Elonix_Toolkit_Popup_CPT'                    => 'inc/modules/popup-builder/class-popup-cpt.php',
		'Elonix_Toolkit_Popup_Renderer'               => 'inc/modules/popup-builder/class-popup-renderer.php',

		'Elonix_Search_Builder'                       => 'inc/modules/search-builder/class-search-builder.php',
		'Elonix_Search_Admin'                         => 'inc/modules/search-builder/class-search-admin.php',
		'Elonix_Search_CPT'                           => 'inc/modules/search-builder/class-search-cpt.php',
		'Elonix_Search_Preview'                       => 'inc/modules/search-builder/class-search-preview.php',
		'Elonix_Search_Renderer'                      => 'inc/modules/search-builder/class-search-renderer.php',
		'Elonix_Toolkit_Template_Library'             => 'inc/modules/template-library/class-template-library.php',
		'Elonix_Toolkit_Template_Discovery'           => 'inc/modules/template-library/class-template-discovery.php',
		'Elonix_Toolkit_Template_Manifest'            => 'inc/modules/template-library/class-template-manifest.php',
		'Elonix_Toolkit_Template_Importer'            => 'inc/modules/template-library/class-template-importer.php',
		'Elonix_Toolkit_Template_Admin'               => 'inc/modules/template-library/class-template-admin.php',
		'Elonix_Toolkit_Template_REST'                => 'inc/modules/template-library/class-template-rest.php',
		'Elonix_Toolkit_Template_Cache'               => 'inc/modules/template-library/class-template-cache.php',
		'Elonix_Header_Footer_Builder'                => 'inc/header-footer/class-header-footer-builder.php',
		'Elonix_Admin_UI'                             => 'inc/header-footer/class-admin-ui.php',
		'Elonix_Display_Conditions'                   => 'inc/header-footer/class-display-conditions.php',
		'Elonix_Rendering_Engine'                     => 'inc/header-footer/class-rendering-engine.php',
		'Elonix_Shortcodes'                           => 'inc/header-footer/class-shortcodes.php',
		'Elonix_Title_Helper'                         => 'inc/helpers/class-title-helper.php',
		'Elonix_Breadcrumb_Helper'                    => 'inc/helpers/class-breadcrumb-helper.php',
		'Elonix_Button_Helper'                        => 'inc/helpers/class-button-helper.php',
		'Elonix_Accordion_Helper'                     => 'inc/helpers/class-accordion-helper.php',
		'Elonix_Nav_Menu_Walker'                      => 'inc/helpers/class-nav-walker.php',
		'Elonix_Query_Context'                        => 'inc/helpers/class-query-context.php',
		'Elonix_Settings'                             => 'inc/helpers/class-settings.php',
		'Elonix_Toolkit_Admin_Menu'                   => 'inc/admin/class-admin-menu.php',
		'Elonix_Admin_Row_Actions'                    => 'inc/admin/class-admin-row-actions.php',
		'Elonix_Toolkit_Dashboard_Page'               => 'inc/admin/class-dashboard-page.php',
		'Elonix_Toolkit_Modules_Page'                 => 'inc/admin/class-modules-page.php',
		'Elonix_Admin_Notices_Manager'                => 'inc/admin/class-notices-manager.php',
		'Elonix_Toolkit_Settings_Page'                => 'inc/admin/class-settings-page.php',
		'Elonix_Toolkit_System_Info_Page'             => 'inc/admin/class-system-info-page.php',
		'Elonix_Toolkit_Uninstall_Settings'           => 'inc/admin/class-uninstall-settings.php',
		'Elonix_Toolkit_Widgets_Page'                 => 'inc/admin/class-widgets-page.php',
		'Elonix_Toolkit_Import_Export'                => 'inc/import-export/class-import-export.php',
		'Elonix_Toolkit_System_Info'                  => 'inc/diagnostics/class-system-info.php',
		'Elonix_Toolkit_Settings_Framework'           => 'inc/framework/class-settings-framework.php',
		'Elonix_Toolkit_Elementor_Core'               => 'inc/elementor/class-elementor-core.php',
		'Elonix_Widget_Base'                          => 'inc/elementor/class-widget-base.php',
		'Elonix_Toolkit_Widget_Category'              => 'inc/elementor/class-widget-category.php',
		'Elonix_Toolkit_Widget_Loader'                => 'inc/elementor/class-widget-loader.php',
		'Elonix_Toolkit_Widget_Registry'              => 'inc/elementor/class-widget-registry.php',
		'Elonix_Toolkit_Widget_Registry_Helper'           => 'inc/elementor/class-widget-registry.php',

		// Legacy WordPress widgets (WP legacy loader)
		'Elonix_Category_Walker'                      => 'inc/widgets/advanced-categories.php',
		'Elonix_Advanced_Categories'                  => 'inc/widgets/advanced-categories.php',
		'Elonix_Service_Help_Widget'                  => 'inc/widgets/contact-help.php',
		'Elonix_Newsletter_Widget'                    => 'inc/widgets/newsletter.php',
		'elonix_posts_thumbs'                         => 'inc/widgets/recent-posts.php',
		'Elonix_Service_Download_Widget'              => 'inc/widgets/service-download.php',
	);

	/**
	 * Register the spl_autoload_register handler.
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload handler callback.
	 *
	 * @param string $class Class name.
	 */
	public static function autoload( $class ) {
		// 1. Resolve namespaced class if defined in class_map
		if ( isset( self::$class_map[ $class ] ) ) {
			list( $legacy_class, $relative_path ) = self::$class_map[ $class ];
			$file_path                            = ELONIX_ACC_PATH . $relative_path;
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
				if ( ! class_exists( $class, false ) && class_exists( $legacy_class, false ) ) {
					class_alias( $legacy_class, $class );
				}
			}
			return;
		}

		// 2. Resolve legacy class if defined in legacy_map
		if ( isset( self::$legacy_map[ $class ] ) ) {
			$relative_path = self::$legacy_map[ $class ];
			$file_path     = ELONIX_ACC_PATH . $relative_path;
			if ( file_exists( $file_path ) ) {
				require_once $file_path;

				// Automatically alias the equivalent namespaced class if applicable
				$namespaced_class = array_search( $class, array_column( self::$class_map, 0 ) );
				if ( $namespaced_class === false ) {
					// Fallback search keys
					foreach ( self::$class_map as $ns => $details ) {
						if ( $details[0] === $class ) {
							$namespaced_class = $ns;
							break;
						}
					}
				}
				if ( $namespaced_class && ! class_exists( $namespaced_class, false ) ) {
					class_alias( $class, $namespaced_class );
				}
			}
			return;
		}

		// 3. PSR-4-like fallback resolution for other namespaced classes under Elonix_Toolkit namespace
		$prefix     = 'Elonix_Toolkit\\';
		$prefix_len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $prefix_len ) === 0 ) {
			$relative_class = substr( $class, $prefix_len );
			$parts          = explode( '\\', $relative_class );

			if ( empty( $parts ) ) {
				return;
			}

			// Subdirectory mapping table
			$directory_map = array(
				'Widgets'      => 'widgets/',
				'Managers'     => 'inc/managers/',
				'Modules'      => 'inc/modules/',
				'Helpers'      => 'inc/helpers/',
				'Admin'        => 'inc/admin/',
				'Assets'       => 'inc/assets/',
				'Elementor'    => 'inc/elementor/',
				'Framework'    => 'inc/framework/',
				'HeaderFooter' => 'inc/header-footer/',
				'ImportExport' => 'inc/import-export/',
				'Diagnostics'  => 'inc/diagnostics/',
				'Importer'     => 'inc/',
			);

			// Special directory map overrides for widgets folder names
			$widget_folder_map = array(
				'post-block' => 'es-post-block',
				'categories' => 'es-categories',
				'post-list'  => 'es-post-list',
				'search'     => 'es-search',
				'search-results' => 'es-search-results',
				'tag-cloud'  => 'es-tag-cloud',
			);

			$root_part = $parts[0];
			if ( isset( $directory_map[ $root_part ] ) ) {
				$base_dir = $directory_map[ $root_part ];
				array_shift( $parts ); // Remove the root part

				// Convert namespaces to lower-case hyphenated paths
				$sub_dirs = array();
				while ( count( $parts ) > 1 ) {
					$dir_name = strtolower( str_replace( '_', '-', array_shift( $parts ) ) );
					if ( $root_part === 'Widgets' && isset( $widget_folder_map[ $dir_name ] ) ) {
						$dir_name = $widget_folder_map[ $dir_name ];
					}
					$sub_dirs[] = $dir_name;
				}

				$file_class = strtolower( str_replace( '_', '-', array_shift( $parts ) ) );
				$filename   = 'class-' . $file_class . '.php';

				$sub_path  = ! empty( $sub_dirs ) ? implode( '/', $sub_dirs ) . '/' : '';
				$file_path = ELONIX_ACC_PATH . $base_dir . $sub_path . $filename;

				if ( file_exists( $file_path ) ) {
					require_once $file_path;
				}
			}
		}
	}
}
