<?php
/**
 * Elonix – Toolkit for Elementor Assets Manager Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Assets_Manager {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_frontend_assets' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_assets' ) );
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'register_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_all_widget_assets' ), 15 );
	}

	/**
	 * Get central asset version (plugin version).
	 *
	 * @return string Asset version.
	 */
	public static function get_asset_version() {
		return ELONIX_VERSION . '.' . time();
	}

	/**
	 * Get file suffix based on SCRIPT_DEBUG.
	 *
	 * @return string File suffix (.min or empty).
	 */
	public static function get_asset_suffix() {
		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	}

	/**
	 * Register admin assets.
	 *
	 * @param string $hook Current admin screen hook.
	 */
	public function register_admin_assets( $hook ) {
		$suffix  = self::get_asset_suffix();
		$version = self::get_asset_version();

		// Register core admin styling (pointing to dashboard.css which contains design tokens)
		wp_register_style(
			'elonix-admin-css',
			ELONIX_ACC_URL . 'assets/admin/css/dashboard.css',
			array(),
			$version
		);

		wp_register_script(
			'elonix-admin-js',
			ELONIX_ACC_URL . 'assets/admin/js/admin.js',
			array( 'jquery' ),
			$version,
			true
		);

		wp_register_style(
			'elonix-notifications-css',
			ELONIX_ACC_URL . 'assets/admin/css/tv-notifications.css',
			array(),
			$version
		);

		wp_register_script(
			'elonix-notifications-js',
			ELONIX_ACC_URL . 'assets/admin/js/tv-notifications.js',
			array( 'jquery' ),
			$version,
			true
		);

		// Enqueue the common admin style and script on Elonix pages
		if ( strpos( $hook, 'elonix' ) !== false || strpos( $hook, 'post' ) !== false || strpos( $hook, 'edit' ) !== false ) {
			wp_enqueue_style( 'elonix-notifications-css' );
			wp_enqueue_script( 'elonix-notifications-js' );
			wp_enqueue_style( 'elonix-admin-css' );
			wp_enqueue_script( 'elonix-admin-js' );

			// Localize variables for AJAX saving and diagnostic copy features
			wp_localize_script(
				'elonix-admin-js',
				'elonixAdminOpts',
				array(
					'ajax_url'      => admin_url( 'admin-ajax.php' ),
					'widgets_nonce' => wp_create_nonce( 'elonix_widgets_ajax_nonce' ),
					'modules_nonce' => wp_create_nonce( 'elonix_modules_ajax_nonce' ),
					'i18n'          => array(
						'copied'        => esc_html__( 'Copied!', 'elonix' ),
						'copy_error'    => esc_html__( 'Error copying, please copy manually.', 'elonix' ),
						'update_failed' => esc_html__( 'Failed to update status. Please try again.', 'elonix' ),
						'saving'        => esc_html__( 'Saving...', 'elonix' ),
						'success'       => esc_html__( 'Saved successfully!', 'elonix' ),
					),
				)
			);
		}
	}

	/**
	 * Register frontend assets (registered only, not enqueued).
	 */
	public function register_frontend_assets() {
		$suffix  = self::get_asset_suffix();
		$version = self::get_asset_version();

		// Register Reset CSS
		$reset_path = 'assets/css/core/tv-reset.css';
		if ( file_exists( ELONIX_ACC_PATH . $reset_path ) ) {
			wp_register_style(
				'elonix-reset',
				ELONIX_ACC_URL . $reset_path,
				array(),
				$version
			);
		}

		// Register core frontend styles/scripts only if they exist on the filesystem
		$css_path = "assets/frontend/css/frontend{$suffix}.css";
		if ( file_exists( ELONIX_ACC_PATH . $css_path ) ) {
			wp_register_style(
				'elonix-frontend-css',
				ELONIX_ACC_URL . $css_path,
				array(),
				$version
			);
		}

		$core_js_path = "assets/js/tv-core.js";
		if ( file_exists( ELONIX_ACC_PATH . $core_js_path ) ) {
			wp_register_script(
				'elonix-core-js',
				ELONIX_ACC_URL . $core_js_path,
				array( 'jquery' ),
				$version,
				true
			);
		}

		// Marquee Engine
		$marquee_engine_path = "assets/js/marquee.min.js";
		if ( file_exists( ELONIX_ACC_PATH . $marquee_engine_path ) ) {
			wp_register_script(
				'elonix-marquee-engine',
				ELONIX_ACC_URL . $marquee_engine_path,
				array( 'jquery' ),
				$version,
				true
			);
		}

		$js_path = "assets/frontend/js/frontend{$suffix}.js";
		if ( file_exists( ELONIX_ACC_PATH . $js_path ) ) {
			wp_register_script(
				'elonix-frontend-js',
				ELONIX_ACC_URL . $js_path,
				array( 'jquery', 'elonix-core-js' ),
				$version,
				true
			);
		}

		// Isotope
		$isotope_path = "assets/js/isotope.pkgd.min.js";
		if ( file_exists( ELONIX_ACC_PATH . $isotope_path ) ) {
			wp_register_script( 'isotope', ELONIX_ACC_URL . $isotope_path, array( 'jquery' ), $version, true );
		}

		// Third-party Interactive Libraries
		$libraries = array();

		foreach ( $libraries as $handle => $data ) {
			if ( file_exists( ELONIX_ACC_PATH . $data['path'] ) ) {
				wp_register_script( $handle, ELONIX_ACC_URL . $data['path'], $data['deps'], $version, true );
			}
		}

		// Dynamic registrations for registered widgets from centralized folders
		$widgets = array();
		if ( class_exists( 'Elonix_Toolkit_Widget_Manager' ) ) {
			$widgets = Elonix_Toolkit_Widget_Manager::get_registered_widgets();
		}
		foreach ( $widgets as $slug => $args ) {
			$style_rel  = "assets/css/{$slug}.css";
			$script_rel = "assets/js/{$slug}.js";

			// Fallback: If tv- prefix is present but files are named without prefix, look for those files instead.
			if ( 0 === strpos( $slug, 'tv-' ) ) {
				$fallback_slug = substr( $slug, 3 );
				if ( ! file_exists( ELONIX_ACC_PATH . $style_rel ) && file_exists( ELONIX_ACC_PATH . "assets/css/{$fallback_slug}.css" ) ) {
					$style_rel = "assets/css/{$fallback_slug}.css";
				}
				if ( ! file_exists( ELONIX_ACC_PATH . $script_rel ) && file_exists( ELONIX_ACC_PATH . "assets/js/{$fallback_slug}.js" ) ) {
					$script_rel = "assets/js/{$fallback_slug}.js";
				}
			}

			if ( file_exists( ELONIX_ACC_PATH . $style_rel ) ) {
				wp_register_style(
					"elonix-widget-{$slug}",
					ELONIX_ACC_URL . $style_rel,
					array(),
					$version
				);
			}

			if ( file_exists( ELONIX_ACC_PATH . $script_rel ) ) {
				wp_register_script(
					"elonix-widget-{$slug}",
					ELONIX_ACC_URL . $script_rel,
					array( 'jquery', 'elementor-frontend' ),
					$version,
					true
				);

				if ( 'tv-post-comments' === $slug ) {
					wp_localize_script( 'elonix-widget-tv-post-comments', 'tv_post_comments_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				}
			}
		}

		// Placeholder registrations for modules
		$modules = array( 'header_builder', 'footer_builder', 'popup_builder', 'search_builder' );
		foreach ( $modules as $module ) {
			$style_rel  = "assets/frontend/css/modules/{$module}{$suffix}.css";
			$script_rel = "assets/frontend/js/modules/{$module}{$suffix}.js";

			if ( 'popup_builder' === $module ) {
				$style_rel  = 'assets/css/popup-builder-frontend.css';
				$script_rel = 'assets/js/popup-builder-frontend.js';
			}

			// Only register if files exist
			if ( file_exists( ELONIX_ACC_PATH . $style_rel ) ) {
				wp_register_style(
					"elonix-module-{$module}",
					ELONIX_ACC_URL . $style_rel,
					array(),
					$version
				);
			} else {
				// Register empty placeholder to prevent errors
				wp_register_style(
					"elonix-module-{$module}",
					false,
					array(),
					$version
				);
			}

			if ( file_exists( ELONIX_ACC_PATH . $script_rel ) ) {
				wp_register_script(
					"elonix-module-{$module}",
					ELONIX_ACC_URL . $script_rel,
					array( 'jquery' ),
					$version,
					true
				);
			} else {
				// Register empty placeholder to prevent errors
				wp_register_script(
					"elonix-module-{$module}",
					false,
					array( 'jquery' ),
					$version,
					true
				);
			}
		}
	}

	/**
	 * Register Elementor editor assets.
	 */
	public function register_editor_assets() {
		$suffix  = self::get_asset_suffix();
		$version = self::get_asset_version();

		// Register Elementor editor styling (pointing to the actual file editor-style.css)
		$css_path = 'assets/css/editor-style.css';
		if ( file_exists( ELONIX_ACC_PATH . $css_path ) ) {
			wp_register_style(
				'elonix-editor-css',
				ELONIX_ACC_URL . $css_path,
				array(),
				$version
			);
		}

		$js_path = "assets/editor/js/editor{$suffix}.js";
		if ( file_exists( ELONIX_ACC_PATH . $js_path ) ) {
			wp_register_script(
				'elonix-editor-js',
				ELONIX_ACC_URL . $js_path,
				array( 'jquery' ),
				$version,
				true
			);
		}
	}

	/**
	 * Enqueue admin assets.
	 */
	public static function enqueue_admin_assets() {
		wp_enqueue_style( 'elonix-admin-css' );
		wp_enqueue_script( 'elonix-admin-js' );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public static function enqueue_frontend_assets() {
		if ( wp_style_is( 'elonix-reset', 'registered' ) ) {
			wp_enqueue_style( 'elonix-reset' );
		}
		if ( wp_style_is( 'elonix-frontend-css', 'registered' ) ) {
			wp_enqueue_style( 'elonix-frontend-css' );
		}
		if ( wp_script_is( 'elonix-frontend-js', 'registered' ) ) {
			wp_enqueue_script( 'elonix-frontend-js' );
		}
	}

	/**
	 * Automatically enqueue core styles and scripts, leaving widget-specific assets to load conditionally.
	 */
	public function enqueue_all_widget_assets() {
		// Enqueue core frontend assets
		self::enqueue_frontend_assets();

		// Widget-specific assets are enqueued conditionally via Elementor's get_script_depends / get_style_depends.
		// This prevents loading unused widget CSS/JS globally, optimizing performance.
	}

	/**
	 * Enqueue editor assets.
	 */
	public static function enqueue_editor_assets() {
		if ( wp_style_is( 'elonix-editor-css', 'registered' ) ) {
			wp_enqueue_style( 'elonix-editor-css' );
		}
		if ( wp_script_is( 'elonix-editor-js', 'registered' ) ) {
			wp_enqueue_script( 'elonix-editor-js' );
		}
	}

	/**
	 * Enqueue assets for a specific widget.
	 *
	 * @param string $widget_id Widget slug/ID.
	 */
	public static function enqueue_widget_assets( $widget_id ) {
		// Enqueue the widget specific style if registered
		if ( wp_style_is( "elonix-widget-{$widget_id}", 'registered' ) ) {
			wp_enqueue_style( "elonix-widget-{$widget_id}" );
		}

		// Enqueue the widget specific script if registered
		if ( wp_script_is( "elonix-widget-{$widget_id}", 'registered' ) ) {
			wp_enqueue_script( "elonix-widget-{$widget_id}" );
		}
	}

	/**
	 * Enqueue assets for a specific module.
	 *
	 * @param string $module_id Module slug/ID.
	 */
	public static function enqueue_module_assets( $module_id ) {
		// Enqueue module specific style if registered
		if ( wp_style_is( "elonix-module-{$module_id}", 'registered' ) ) {
			wp_enqueue_style( "elonix-module-{$module_id}" );
		}

		// Enqueue module specific script if registered
		if ( wp_script_is( "elonix-module-{$module_id}", 'registered' ) ) {
			wp_enqueue_script( "elonix-module-{$module_id}" );
		}
	}
}
