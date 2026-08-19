<?php
/**
 * Elonix Rendering Engine for Header & Footer Builder
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Rendering_Engine {

	/**
	 * Display conditions instance.
	 *
	 * @var Elonix_Display_Conditions
	 */
	private $display_conditions;

	/**
	 * Track rendered templates to prevent double rendering.
	 *
	 * @var array
	 */
	private static $rendered_templates = array();

	/**
	 * Constructor.
	 *
	 * @param Elonix_Display_Conditions $display_conditions Display conditions instance.
	 */
	public function __construct( $display_conditions ) {
		$this->display_conditions = $display_conditions;

		// Elementor Theme Locations
		add_action( 'elementor/theme/register_locations', array( $this, 'register_locations' ) );
		add_action( 'elementor/theme/do_location', array( $this, 'do_location' ), 10, 2 );

		// Only register frontend hooks on non-admin requests.
		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_template_styles' ), 500 );
		}

		// Hook into themes on setup
		add_action( 'wp', array( $this, 'setup_theme_compatibilities' ), 20 );

		// Block theme template part hook
		add_filter( 'render_block', array( $this, 'render_block_compatibility' ), 10, 2 );

		// Force Elementor canvas template when previewing/editing templates on the frontend
		add_filter( 'single_template', array( $this, 'override_editor_canvas' ), 999 );
	}

	/**
	 * Register Elementor theme locations.
	 */
	public function register_locations( $theme_manager ) {
		$theme_manager->register_location( 'header' );
		$theme_manager->register_location( 'footer' );
	}

	/**
	 * Handler for Elementor's theme locations callback.
	 */
	public function do_location( $location, $theme_manager ) {
		$builder         = class_exists( 'Elonix_Toolkit_404_Builder' ) ? Elonix_Toolkit_404_Builder::instance() : null;
		$is_404_template = $builder && $builder->router && method_exists( $builder->router, 'should_render_404_template' ) && $builder->router->should_render_404_template();

		if ( 'header' === $location ) {
			if ( $is_404_template && 'no' === ( Elonix_Settings::get( 'es_404_show_header' ) ?? 'yes' ) ) {
				return true;
			}
			$header_id = \Elonix_Assignment_Engine::instance()->get_matching_template( 'elonix_header' );
			if ( $header_id ) {
				$this->render_elementor_content( $header_id );
				return true;
			}
		} elseif ( 'footer' === $location ) {
			if ( $is_404_template && 'no' === ( Elonix_Settings::get( 'es_404_show_footer' ) ?? 'yes' ) ) {
				return true;
			}
			$footer_id = \Elonix_Assignment_Engine::instance()->get_matching_template( 'elonix_footer' );
			if ( $footer_id ) {
				$this->render_elementor_content( $footer_id );
				return true;
			}
		}
		return false;
	}

	/**
	 * Enqueue CSS files of the custom header and footer templates in the head.
	 */
	public function enqueue_template_styles() {
		if ( ! class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			return;
		}

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
			\Elementor\Plugin::$instance->frontend->enqueue_styles();
		}

		// Support for Layout Template Preview Changes mode
		// Cosmetic only: enqueues Elementor's per-post CSS file for smoother preview loading. Actual
		// content access is separately gated by Elonix_Toolkit_Header_Footer_Builder::is_preview_request_authorized().
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- cosmetic CSS enqueue only, see note above.
		if ( isset( $_GET['es_preview'] ) ) {
			$preview_id = intval( wp_unslash( $_GET['es_preview'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- cosmetic CSS enqueue only, see note above.
			if ( $preview_id ) {
				$css_file = new \Elementor\Core\Files\CSS\Post( $preview_id );
				$css_file->enqueue();

				// Enqueue widget styles pre-emptively in head to prevent FOUC
				$this->enqueue_template_widget_styles( $preview_id );

				// Base layout CSS for the standalone preview page (see preview-template.php).
				wp_register_style( 'elonix-preview-page', false, array(), ELONIX_VERSION );
				wp_enqueue_style( 'elonix-preview-page' );
				wp_add_inline_style(
					'elonix-preview-page',
					'html, body { margin: 0 !important; padding: 0 !important; width: 100% !important; height: 100% !important; background: #f1f5f9 !important; }
					.es-site-header { width: 100% !important; }
					.es-preview-debug-bar { background: #1e293b; color: #f1f5f9; padding: 10px 20px; font-family: Consolas, Monaco, monospace; font-size: 12px; border-bottom: 2px solid #ef4444; display: flex; gap: 20px; flex-wrap: wrap; z-index: 999999; position: relative; }
					.es-preview-debug-bar span { font-weight: bold; }
					.es-preview-debug-bar .success { color: #22c55e; }
					.es-preview-debug-bar .failure { color: #ef4444; }'
				);

				$post_type = get_post_type( $preview_id );
				if ( 'elonix_header' === $post_type && class_exists( 'Elonix_Toolkit_Assets_Manager' ) ) {
					Elonix_Toolkit_Assets_Manager::enqueue_module_assets( 'header_builder' );
				}
				return;
			}
		}

		// Enqueue Header CSS
		$header_id = $this->display_conditions->get_active_template_id( 'elonix_header' );
		if ( $header_id ) {
			$css_file = new \Elementor\Core\Files\CSS\Post( $header_id );
			$css_file->enqueue();

			// Enqueue widget styles pre-emptively in head to prevent FOUC
			$this->enqueue_template_widget_styles( $header_id );

			// Enqueue module specific assets for header builder compatibility
			if ( class_exists( 'Elonix_Toolkit_Assets_Manager' ) ) {
				Elonix_Toolkit_Assets_Manager::enqueue_module_assets( 'header_builder' );
			}
		}

		// Enqueue Footer CSS
		$footer_id = $this->display_conditions->get_active_template_id( 'elonix_footer' );
		if ( $footer_id ) {
			$css_file = new \Elementor\Core\Files\CSS\Post( $footer_id );
			$css_file->enqueue();

			// Enqueue widget styles pre-emptively in head to prevent FOUC
			$this->enqueue_template_widget_styles( $footer_id );
		}
	}

	/**
	 * Pre-emptively enqueue widget styles for a given template ID to prevent FOUC.
	 *
	 * @param int $template_id Template Post ID.
	 */
	private function enqueue_template_widget_styles( $template_id ) {
		if ( ! $template_id ) {
			return;
		}

		$elementor_data = get_post_meta( $template_id, '_elementor_data', true );
		if ( empty( $elementor_data ) ) {
			return;
		}

		if ( is_string( $elementor_data ) ) {
			$elements = json_decode( $elementor_data, true );
		} else {
			$elements = $elementor_data;
		}

		if ( is_array( $elements ) ) {
			$this->enqueue_widget_styles_from_elements( $elements );
		}
	}

	/**
	 * Recursively scans Elementor elements array and enqueues Elonix widget styles.
	 *
	 * @param array $elements Elementor elements data.
	 */
	private function enqueue_widget_styles_from_elements( $elements ) {
		if ( ! is_array( $elements ) ) {
			return;
		}

		foreach ( $elements as $element ) {
			if ( isset( $element['elType'] ) ) {
				if ( 'widget' === $element['elType'] && isset( $element['widgetType'] ) ) {
					$widget_type = $element['widgetType'];
					if ( 0 === strpos( $widget_type, 'es-' ) ) {
						$handle = "elonix-widget-{$widget_type}";
						if ( wp_style_is( $handle, 'registered' ) ) {
							wp_enqueue_style( $handle );
						}
					}
				}
			}

			if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$this->enqueue_widget_styles_from_elements( $element['elements'] );
			}
		}
	}


	/**
	/**
	 * Render Elementor post content for display.
	 */
	public function render_elementor_content( $template_id ) {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		if ( in_array( $template_id, self::$rendered_templates, true ) ) {
			return; // Prevent recursion and double rendering
		}

		self::$rendered_templates[] = $template_id;

		$post_type = get_post_type( $template_id );
		$is_header = ( 'elonix_header' === $post_type );

		if ( $is_header ) {
			echo '<header class="es-site-header">';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor's own rendering API; escaping is handled internally by Elementor per-widget.
		echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id );

		if ( $is_header ) {
			echo '</header>';
		}
	}

	/**
	 * Render custom header contents.
	 */
	public function render_custom_header() {
		$header_id = $this->display_conditions->get_active_template_id( 'elonix_header' );
		if ( $header_id ) {
			$this->render_elementor_content( $header_id );
		}
	}

	/**
	 * Render custom footer contents.
	 */
	public function render_custom_footer() {
		$footer_id = $this->display_conditions->get_active_template_id( 'elonix_footer' );
		if ( $footer_id ) {
			$this->render_elementor_content( $footer_id );
		}
	}

	/**
	 * Setup hooks to override active theme headers and footers.
	 */
	public function setup_theme_compatibilities() {
		$builder         = class_exists( 'Elonix_Toolkit_404_Builder' ) ? Elonix_Toolkit_404_Builder::instance() : null;
		$is_404_template = $builder && $builder->router && method_exists( $builder->router, 'should_render_404_template' ) && $builder->router->should_render_404_template();

		$show_header = ! ( $is_404_template && 'no' === ( Elonix_Settings::get( 'es_404_show_header' ) ?? 'yes' ) );
		$show_footer = ! ( $is_404_template && 'no' === ( Elonix_Settings::get( 'es_404_show_footer' ) ?? 'yes' ) );

		$header_id = $this->display_conditions->get_active_template_id( 'elonix_header' );
		$footer_id = $this->display_conditions->get_active_template_id( 'elonix_footer' );

		// If no custom templates, fall back to theme defaults
		if ( ! $header_id && ! $footer_id ) {
			return;
		}

		// 1. Astra Theme
		if ( class_exists( 'Astra_Theme_Header' ) || defined( 'ASTRA_THEME_SETTINGS' ) ) {
			if ( $header_id ) {
				remove_action( 'astra_header', 'astra_header_markup' );
				if ( $show_header ) {
					add_action( 'astra_header', array( $this, 'render_custom_header' ) );
				}
			}
			if ( $footer_id ) {
				remove_action( 'astra_footer', 'astra_footer_markup' );
				if ( $show_footer ) {
					add_action( 'astra_footer', array( $this, 'render_custom_footer' ) );
				}
			}
		}

		// 2. GeneratePress Theme
		if ( defined( 'GENERATE_VERSION' ) ) {
			if ( $header_id ) {
				remove_action( 'generate_header', 'generate_construct_header' );
				if ( $show_header ) {
					add_action( 'generate_header', array( $this, 'render_custom_header' ) );
				}
			}
			if ( $footer_id ) {
				remove_action( 'generate_footer', 'generate_construct_footer' );
				if ( $show_footer ) {
					add_action( 'generate_footer', array( $this, 'render_custom_footer' ) );
				}
			}
		}

		// 3. Kadence Theme
		if ( class_exists( 'Kadence\Theme' ) || defined( 'KADENCE_VERSION' ) ) {
			if ( $header_id ) {
				remove_action( 'kadence_header', 'kadence_header_markup' );
				if ( $show_header ) {
					add_action( 'kadence_header', array( $this, 'render_custom_header' ) );
				}
			}
			if ( $footer_id ) {
				remove_action( 'kadence_footer', 'kadence_footer_markup' );
				if ( $show_footer ) {
					add_action( 'kadence_footer', array( $this, 'render_custom_footer' ) );
				}
			}
		}

		// 4. OceanWP Theme
		if ( class_exists( 'OCEANWP_Theme_Class' ) || defined( 'OCEANWP_THEME_VERSION' ) ) {
			if ( $header_id ) {
				remove_action( 'ocean_header', 'ocean_header_template' );
				if ( $show_header ) {
					add_action( 'ocean_header', array( $this, 'render_custom_header' ) );
				}
			}
			if ( $footer_id ) {
				remove_action( 'ocean_footer', 'ocean_footer_template' );
				if ( $show_footer ) {
					add_action( 'ocean_footer', array( $this, 'render_custom_footer' ) );
				}
			}
		}

		// 5. Elonix Themes (Custom themes)
		if ( $header_id ) {
			remove_action( 'elonix_header', 'elonix_default_header' );
			if ( $show_header ) {
				add_action( 'elonix_header', array( $this, 'render_custom_header' ) );
			}
		}
		if ( $footer_id ) {
			remove_action( 'elonix_footer', 'elonix_default_footer' );
			if ( $show_footer ) {
				add_action( 'elonix_footer', array( $this, 'render_custom_footer' ) );
			}
		}
	}

	/**
	 * Intercept Block template-parts in Block Themes (FSE)
	 */
	public function render_block_compatibility( $block_content, $block ) {
		if ( ! isset( $block['blockName'] ) ) {
			return $block_content;
		}

		$builder         = class_exists( 'Elonix_Toolkit_404_Builder' ) ? Elonix_Toolkit_404_Builder::instance() : null;
		$is_404_template = $builder && $builder->router && method_exists( $builder->router, 'should_render_404_template' ) && $builder->router->should_render_404_template();

		if ( 'core/template-part' === $block['blockName'] && isset( $block['attrs']['slug'] ) ) {
			$slug = $block['attrs']['slug'];

			if ( 'header' === $slug ) {
				if ( $is_404_template && 'no' === ( Elonix_Settings::get( 'es_404_show_header' ) ?? 'yes' ) ) {
					return '';
				}
				$header_id = $this->display_conditions->get_active_template_id( 'elonix_header' );
				if ( $header_id ) {
					ob_start();
					$this->render_elementor_content( $header_id );
					return ob_get_clean();
				}
			}

			if ( 'footer' === $slug ) {
				if ( $is_404_template && 'no' === ( Elonix_Settings::get( 'es_404_show_footer' ) ?? 'yes' ) ) {
					return '';
				}
				$footer_id = $this->display_conditions->get_active_template_id( 'elonix_footer' );
				if ( $footer_id ) {
					ob_start();
					$this->render_elementor_content( $footer_id );
					return ob_get_clean();
				}
			}
		}

		return $block_content;
	}

	/**
	 * Force Elementor canvas template when previewing/editing templates on the frontend.
	 * This prevents any "Sorry, content area was not found" errors by bypassing the theme's singular layout.
	 */
	public function override_editor_canvas( $template ) {
		$layout_types = array( 'elonix_header', 'elonix_footer' );
		if ( is_singular( $layout_types ) && defined( 'ELEMENTOR_PATH' ) ) {
			$canvas = ELEMENTOR_PATH . 'modules/page-templates/templates/canvas.php';
			if ( file_exists( $canvas ) ) {
				return $canvas;
			}
		}
		return $template;
	}

	/**
	 * Callback to manually trigger template rendering for a location.
	 */
	public function do_location_direct( $location ) {
		$builder         = class_exists( 'Elonix_Toolkit_404_Builder' ) ? Elonix_Toolkit_404_Builder::instance() : null;
		$is_404_template = $builder && $builder->router && method_exists( $builder->router, 'should_render_404_template' ) && $builder->router->should_render_404_template();

		if ( 'header' === $location ) {
			if ( $is_404_template && 'no' === ( Elonix_Settings::get( 'es_404_show_header' ) ?? 'yes' ) ) {
				return true;
			}
			$header_id = $this->display_conditions->get_active_template_id( 'elonix_header' );
			if ( $header_id ) {
				$this->render_elementor_content( $header_id );
				return true;
			}
		} elseif ( 'footer' === $location ) {
			if ( $is_404_template && 'no' === ( Elonix_Settings::get( 'es_404_show_footer' ) ?? 'yes' ) ) {
				return true;
			}
			$footer_id = $this->display_conditions->get_active_template_id( 'elonix_footer' );
			if ( $footer_id ) {
				$this->render_elementor_content( $footer_id );
				return true;
			}
		}
		return false;
	}
}

if ( ! function_exists( 'elementor_theme_do_location' ) ) {
	/**
	 * Callback to simulate/override Elementor Pro's theme locations.
	 *
	 * @param string $location Location name.
	 * @return bool True if location rendered, false otherwise.
	 */
	function elementor_theme_do_location( $location ) {
		if ( class_exists( 'Elonix_Header_Footer_Builder' ) ) {
			$builder = Elonix_Header_Footer_Builder::instance();
			if ( $builder && isset( $builder->rendering_engine ) ) {
				return $builder->rendering_engine->do_location_direct( $location );
			}
		}
		return false;
	}
}
