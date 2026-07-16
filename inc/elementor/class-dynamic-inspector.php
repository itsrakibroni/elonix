<?php
/**
 * Elonix Dynamic Data Inspector (Developer Mode)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Inspector {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		// Register hooks always; conditional checks are deferred to runtime methods
		// to ensure user capability checks and session data are fully loaded.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'elementor/frontend/before_render', [ $this, 'before_render' ] );
		add_action( 'wp_footer', [ $this, 'render_global_overlay' ], 9999 );
		
		// Prevent Elementor from caching widgets when the inspector is active
		// This avoids poisoning the cache with developer metadata for guests
		add_filter( 'elementor/frontend/element/should_cache', [ $this, 'disable_element_cache' ] );
	}

	public function disable_element_cache( $should_cache ) {
		if ( $this->is_active() ) {
			return false;
		}
		return $should_cache;
	}

	/**
	 * Centralized visibility and activation checks.
	 * Returns true ONLY if the current request meets strict developer criteria.
	 *
	 * @return bool
	 */
	private function is_active() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! class_exists( 'Elonix_Settings' ) ) {
			return false;
		}

		if ( ! \Elonix_Settings::is_dynamic_inspector_enabled() ) {
			return false;
		}

		return true;
	}

	public function enqueue_assets() {
		if ( ! $this->is_active() ) {
			return;
		}

		if ( ! \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			// Optional: only load in preview or everywhere for devs. We load everywhere for dev mode.
		}

		$css = "
		#tv-dev-inspector-overlay {
			position: fixed;
			bottom: 20px;
			left: 20px;
			background: rgba(0,0,0,0.85);
			color: #0f0;
			font-family: monospace;
			font-size: 11px;
			padding: 10px;
			border-radius: 4px;
			z-index: 999999;
			opacity: 0;
			visibility: hidden;
			transition: opacity 0.2s ease, visibility 0.2s ease;
			pointer-events: none;
			white-space: pre;
			text-align: left;
			line-height: 1.4;
			box-shadow: 0 4px 10px rgba(0,0,0,0.5);
			min-width: 250px;
		}
		#tv-dev-inspector-overlay.tv-inspector-visible {
			opacity: 1;
			visibility: visible;
		}
		";
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Dummy inline style handle. No physical asset exists.
		wp_register_style( 'elonix-dev-inspector', false );
		wp_enqueue_style( 'elonix-dev-inspector' );
		wp_add_inline_style( 'elonix-dev-inspector', $css );

		$js = "
		document.addEventListener('DOMContentLoaded', function() {
			var overlay = document.getElementById('tv-dev-inspector-overlay');
			if (!overlay) return;

			document.body.addEventListener('mouseover', function(e) {
				var target = e.target.closest ? e.target.closest('[data-tv-inspector]') : null;
				if (target) {
					overlay.textContent = target.getAttribute('data-tv-inspector');
					overlay.classList.add('tv-inspector-visible');
				} else {
					overlay.classList.remove('tv-inspector-visible');
				}
			});
			
			document.body.addEventListener('mouseleave', function(e) {
				overlay.classList.remove('tv-inspector-visible');
			});
		});
		";
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion, WordPress.WP.EnqueuedResourceParameters.NotInFooter -- Dummy inline script handle. Version is irrelevant because no file is loaded.
		wp_register_script( 'elonix-dev-inspector', false, [], false, true );
		wp_enqueue_script( 'elonix-dev-inspector' );
		wp_add_inline_script( 'elonix-dev-inspector', $js );
	}

	public function render_global_overlay() {
		if ( ! $this->is_active() ) {
			return;
		}
		echo '<div id="tv-dev-inspector-overlay"></div>';
	}

	public function before_render( $widget ) {
		if ( ! $this->is_active() ) {
			return;
		}

		if ( ! class_exists( 'Elonix_Dynamic_Data' ) ) {
			require_once ELONIX_ACC_PATH . 'inc/managers/class-dynamic-data.php';
		}
		
		$data = \Elonix_Dynamic_Data::instance();
		$post = $data->get_current_post();
		$author = $data->get_current_author();
		$term = $data->get_current_term();
		$product = $data->get_current_product();
		
		$info = "Elonix Dynamic Inspector\n";
		$info .= str_repeat("-", 30) . "\n";
		$info .= "Widget: " . $widget->get_name() . "\n";
		$info .= "Context:\n";
		$info .= "  Post ID: " . ( $post ? $post->ID . " (" . $post->post_type . ")" : "N/A" ) . "\n";
		$info .= "  Author: " . ( $author ? $author->display_name . " (ID: " . $author->ID . ")" : "N/A" ) . "\n";
		$info .= "  Term: " . ( $term ? $term->name . " (ID: " . $term->term_id . ")" : "N/A" ) . "\n";
		$info .= "  Product: " . ( $product ? "Yes (ID: " . $product->get_id() . ")" : "No" ) . "\n";
		$info .= "  Is Archive: " . ( is_archive() ? "Yes" : "No" ) . "\n";
		$info .= "  Is Search: " . ( is_search() ? "Yes" : "No" ) . "\n";

		$widget->add_render_attribute( '_wrapper', 'data-tv-inspector', $info );
	}
}
