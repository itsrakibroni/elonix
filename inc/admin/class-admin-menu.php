<?php
/**
 * Elonix – Toolkit for Elementor Admin Menu Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Admin_Menu {

	/**
	 * Register the admin menu and submenu pages.
	 */
	public function register_admin_menu() {
		$icon_url = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPCEtLSBHZW5lcmF0b3I6IGh0dHBzOi8vZXpnaWYuY29tL3BuZy10by1zdmcgLS0+CjxzdmcgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCI+CjxwYXRoIGQ9Ik0wIDAgQzUuOTMgMi44MyAxMS42OSA3LjIgMTUgMTMgQzE1LjI3IDE2LjI2IDE1LjE0IDE5LjMxIDE0Ljk0IDIyLjU3IEMxNS4wNCAyOC45MSAxOC4xNyAzMi4xMiAyMi40OCAzNi41NCBDMjYuMjQgNDAuMTIgMzAuMDUgNDMuNjMgMzQgNDcgQzQxLjgzIDQzLjQyIDQ5LjI1IDM2LjExIDU0IDI5IEM1NC44MyAyNi4yIDU0Ljg3IDIzLjcxIDU0LjY5IDIwLjgxIEM1NC40MyAxNS43NyA1NC42MiAxMi45NyA1Ny44MyA5LjExIEM2MSA1Ljc4IDY0LjM0IDIuNzggNjggMCBDNjguNjYgMCA2OS4zMiAwIDcwIDAgQzcyLjI5IDExLjc3IDc0Ljg4IDI2LjU4IDY4LjU3IDM3LjQ3IEM2NC4yNyA0My41IDU5LjEzIDQ4LjY4IDUzLjc1IDUzLjc1IEM1Mi45OCA1NC40OCA1Mi4yMSA1NS4yMSA1MS40MSA1NS45NyBDNDcuODUgNTkuMjggNDQuMjIgNjIuNTMgNDAuNDggNjUuNjQgQzM5LjkxIDY2LjEyIDM5LjMzIDY2LjYxIDM4LjczIDY3LjEgQzM3IDY4IDM3IDY4IDM0LjkyIDY3Ljc2IEMzMi4xNSA2Ni42NiAzMC41OCA2NC45IDI4LjUyIDYyLjc3IEMyNy42NiA2MS45MSAyNi44MSA2MS4wNCAyNS45MyA2MC4xNCBDMjUuMDUgNTkuMjMgMjQuMTYgNTguMzIgMjMuMjUgNTcuMzggQzE1LjMyIDQ5LjI0IDE1LjMyIDQ5LjI0IDYuODggNDEuNjUgQzYuMjYgNDEuMTEgNS42NCA0MC41NiA1IDQwIEM0LjExIDM5LjI4IDMuMjIgMzguNTYgMi4zIDM3LjgxIEMtMC42NyAzNC4xOCAtMC42NCAzMS4zNCAtMC40OSAyNi44NiBDLTAuNDggMjYuMDkgLTAuNDcgMjUuMzIgLTAuNDcgMjQuNTIgQy0wLjQ0IDIyLjA4IC0wLjM4IDE5LjYzIC0wLjMxIDE3LjE5IEMtMC4yOSAxNS41MiAtMC4yNiAxMy44NiAtMC4yNCAxMi4yIEMtMC4xOSA4LjEzIC0wLjEgNC4wNyAwIDAgWiAiIGZpbGw9IiNGNEY0RjUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDUsMTEpIi8+CjxwYXRoIGQ9Ik0wIDAgQzIzLjEgMCA0Ni4yIDAgNzAgMCBDNzAgMC42NiA3MCAxLjMyIDcwIDIgQzY4LjE0IDMuODMgNjYuMTQgNS40NSA2NC4xMyA3LjEyIEM2Mi4yNSA4Ljc4IDYwLjg2IDEwLjQyIDU5LjMxIDEyLjM4IEM1Ni4zMyAxNS40OCA1My40NyAxNS40NCA0OS4yOSAxNS42NiBDNDcuNTMgMTUuNzcgNDcuNTMgMTUuNzcgNDUuNzMgMTUuODcgQzQ0LjgzIDE1LjkyIDQzLjkzIDE1Ljk2IDQzIDE2IEM0My4wNSAxNy4xOCA0My4wNSAxNy4xOCA0My4xMSAxOC4zOCBDNDMuMjYgMjEuOTYgNDMuMzUgMjUuNTQgNDMuNDQgMjkuMTIgQzQzLjUgMzAuMzYgNDMuNTUgMzEuNiA0My42MiAzMi44OCBDNDMuNzggNDEuODYgNDMuNzggNDEuODYgNDEuNDggNDUuMjEgQzM3LjE4IDQ5IDM3LjE4IDQ5IDM0IDQ5IEMzNCA0OC4zNCAzNCA0Ny42OCAzNCA0NyBDMzMuMDEgNDYuNjcgMzIuMDIgNDYuMzQgMzEgNDYgQzMxIDQ1LjM0IDMxIDQ0LjY4IDMxIDQ0IEMyOS41MiA0My41MSAyOS41MiA0My41MSAyOCA0MyBDMjggMzMuNzYgMjggMjQuNTIgMjggMTUgQzIzLjM4IDE1IDE4Ljc2IDE1IDE0IDE1IEMxMC41MyAxMi42MyA4LjY4IDExLjE0IDYuMTIgOC4wNiBDNS41MiA3LjQgNC45MiA2Ljc0IDQuMjkgNi4wNiBDMCAxLjMyIDAgMS4zMiAwIDAgWiAiIGZpbGw9IiMwNUM1ODAiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDUsMSkiLz4KPC9zdmc+Cg==';

		add_menu_page(
			esc_html__( 'Elonix', 'elonix' ),
			esc_html__( 'Elonix', 'elonix' ),
			'manage_options',
			'elonix',
			array( $this, 'render_dashboard_page' ),
			$icon_url,
			59 // Below Elementor menu (which is positioned at 58.5)
		);

		add_submenu_page(
			'elonix',
			esc_html__( 'Dashboard', 'elonix' ),
			esc_html__( 'Dashboard', 'elonix' ),
			'manage_options',
			'elonix',
			array( $this, 'render_dashboard_page' )
		);

		add_submenu_page(
			'elonix',
			esc_html__( 'Widgets', 'elonix' ),
			esc_html__( 'Widgets', 'elonix' ),
			'manage_options',
			'elonix-widgets',
			array( $this, 'render_widgets_page' )
		);

		add_submenu_page(
			'elonix',
			esc_html__( 'Modules', 'elonix' ),
			esc_html__( 'Modules', 'elonix' ),
			'manage_options',
			'elonix-modules',
			array( $this, 'render_modules_page' )
		);

		if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'header_builder' ) || Elonix_Toolkit_Module_Manager::is_module_enabled( 'footer_builder' ) ) {
			add_submenu_page(
				'elonix',
				esc_html__( 'Header & Footer Builder', 'elonix' ),
				esc_html__( 'Header & Footer Builder', 'elonix' ),
				'manage_options',
				'elonix-header-footer',
				array( $this, 'render_header_footer_page' )
			);
		}

		if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'template_library' ) ) {
			add_submenu_page(
				'elonix',
				esc_html__( 'Templates', 'elonix' ),
				esc_html__( 'Templates', 'elonix' ),
				'manage_options',
				'elonix-templates',
				array( $this, 'render_templates_page' )
			);
		}

		if ( Elonix_Toolkit_Module_Manager::is_module_enabled( 'screen_loader' ) ) {
			add_submenu_page(
				'elonix',
				esc_html__( 'Screen Loader', 'elonix' ),
				esc_html__( 'Screen Loader', 'elonix' ),
				'manage_options',
				'elonix-screen-loader',
				array( $this, 'render_screen_loader_page' )
			);
		}

		add_submenu_page(
			'elonix',
			esc_html__( 'Settings', 'elonix' ),
			esc_html__( 'Settings', 'elonix' ),
			'manage_options',
			'elonix-settings',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'elonix',
			esc_html__( 'System Info', 'elonix' ),
			esc_html__( 'System Info', 'elonix' ),
			'manage_options',
			'elonix-system-info',
			array( $this, 'render_system_info_page' )
		);
	}

	/**
	 * Render Dashboard Page.
	 */
	public function render_dashboard_page() {
		Elonix_Toolkit_Dashboard_Page::render();
	}

	/**
	 * Render Widgets Page.
	 */
	public function render_widgets_page() {
		Elonix_Toolkit_Widgets_Page::render();
	}

	/**
	 * Render Modules Page.
	 */
	public function render_modules_page() {
		Elonix_Toolkit_Modules_Page::render();
	}

	/**
	 * Reorder submenu items dynamically.
	 */
	public function reorder_admin_menu() {
		global $submenu;

		if ( ! isset( $submenu['elonix'] ) ) {
			return;
		}

		$order = array(
			'elonix'               => 1,
			'elonix-header-footer' => 2,
			'edit.php?post_type=es_archive_template' => 3,
			'edit.php?post_type=es_single_template'  => 4,
			'edit.php?post_type=es_search_template'  => 5,
			'elonix-404'           => 6,
			'elonix-templates'     => 7,
			'edit.php?post_type=es_popup'      => 8,
			'elonix-widgets'       => 9,
			'elonix-modules'       => 10,
			'elonix-screen-loader' => 11,
			'elonix-settings'      => 98,
			'elonix-system-info'   => 99,
		);

		$new_submenu = array();
		$unmatched   = array();

		foreach ( $submenu['elonix'] as $key => $item ) {
			$slug = $item[2];
			if ( isset( $order[ $slug ] ) ) {
				$new_submenu[ $order[ $slug ] ] = $item;
			} else {
				$unmatched[] = $item;
			}
		}

		ksort( $new_submenu );

		$final_submenu = array();
		$index         = 20;

		foreach ( $new_submenu as $pos => $item ) {
			if ( $pos >= 98 && ! empty( $unmatched ) ) {
				foreach ( $unmatched as $u_item ) {
					$final_submenu[ $index++ ] = $u_item;
				}
				$unmatched = array();
			}
			$final_submenu[ $pos ] = $item;
		}

		foreach ( $unmatched as $u_item ) {
			$final_submenu[ $index++ ] = $u_item;
		}

		$submenu['elonix'] = $final_submenu;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'reorder_admin_menu' ), 999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Enqueue styles for Templates page.
	 *
	 * @param string $hook Screen hook.
	 */
	public function enqueue_styles( $hook ) {
		if ( 'elonix_page_elonix-templates' !== $hook ) {
			return;
		}

		if ( wp_style_is( 'elonix-admin-css', 'registered' ) ) {
			wp_enqueue_style( 'elonix-admin-css' );
		} else {
			wp_enqueue_style(
				'elonix-admin-css',
				ELONIX_ACC_URL . 'assets/admin/css/dashboard.css',
				array(),
				ELONIX_VERSION
			);
		}
	}

	/**
	 * Render Templates Page.
	 */
	public function render_templates_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$is_library_enabled = true;
		if ( class_exists( 'Elonix_Toolkit_Module_Manager' ) ) {
			$is_library_enabled = Elonix_Toolkit_Module_Manager::is_module_enabled( 'template_library' );
		}

		if ( $is_library_enabled && class_exists( '\Elonix_Toolkit\Modules\Template_Library\Admin' ) ) {
			\Elonix_Toolkit\Modules\Template_Library\Admin::instance()->render();
		} else {
			echo '<div class="wrap"><h1>Template Library is disabled.</h1></div>';
		}
	}

	/**
	 * Render Header & Footer Page.
	 */
	public function render_header_footer_page() {
		if ( class_exists( 'Elonix_Header_Footer_Builder' ) ) {
			Elonix_Header_Footer_Builder::instance()->admin_ui->render_settings_page();
		}
	}



	/**
	 * Render Screen Loader Page.
	 */
	public function render_screen_loader_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( class_exists( '\Elonix_Toolkit\Modules\Screen_Loader\Admin_Page' ) ) {
			\Elonix_Toolkit\Modules\Screen_Loader\Admin_Page::instance()->render();
		} else {
			echo '<div class="wrap"><h1>Screen Loader module is disabled.</h1><p>Enable it from Elonix – Toolkit for Elementor &rarr; Modules.</p></div>';
		}
	}

	/**
	 * Render Settings Page.
	 */
	public function render_settings_page() {
		Elonix_Toolkit_Settings_Page::render();
	}

	/**
	 * Render System Info Page.
	 */
	public function render_system_info_page() {
		Elonix_Toolkit_System_Info_Page::render();
	}
}
