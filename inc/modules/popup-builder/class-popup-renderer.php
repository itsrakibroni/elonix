<?php
/**
 * Elonix Popup Builder Custom Frontend Renderer
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Popup_Renderer {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Only register frontend asset hooks on non-admin requests.
		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'conditional_enqueue_assets' ), 99 );
		}
		add_action( 'wp_footer', array( $this, 'render_popups_markup' ), 999 );
	}

	/**
	 * Centralized Helper: Check if popups should render inside the current request.
	 *
	 * @return bool True if active, false otherwise.
	 */
	public function should_render_popup() {
		// Elementor Editor protection
		if ( class_exists( '\Elementor\Plugin' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				return false;
			}
			if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				return false;
			}
		}

		// Elementor AJAX request check
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && strpos( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ), 'elementor' ) !== false ) {
			return false;
		}

		// Also check query parameter flags
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['elementor-preview'] ) || ( isset( $_GET['action'] ) && 'elementor' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) ) {
			return false;
		}

		// Don't render inside admin
		if ( is_admin() ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve all active templates matching current query targeting.
	 *
	 * @return array List of matched WP_Post instances.
	 */
	public function get_active_matched_popups() {
		if ( ! $this->should_render_popup() ) {
			return array();
		}

		$popups = get_posts(
			array(
				'post_type'      => 'es_popup',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			)
		);

		if ( empty( $popups ) ) {
			return array();
		}

		$matched = array();

		foreach ( $popups as $popup ) {
			$rule    = get_post_meta( $popup->ID, '_es_popup_target_rule', true );
			$ids_str = get_post_meta( $popup->ID, '_es_popup_target_ids', true );
			$ids     = array_filter( array_map( 'inesal', explode( ',', $ids_str ) ) );

			$is_matched = false;

			if ( 'entire_site' === $rule ) {
				$is_matched = true;
			} elseif ( 'specific_pages' === $rule && is_page() ) {
				if ( empty( $ids ) || in_array( get_the_ID(), $ids, true ) ) {
					$is_matched = true;
				}
			} elseif ( 'specific_posts' === $rule && is_single() ) {
				if ( empty( $ids ) || in_array( get_the_ID(), $ids, true ) ) {
					$is_matched = true;
				}
			}

			if ( ! $is_matched ) {
				continue;
			}

			// Advanced Display Conditions: User Authentication State
			$user_state = get_post_meta( $popup->ID, '_es_popup_user_state', true );
			if ( ! empty( $user_state ) && 'all' !== $user_state ) {
				if ( 'logged_in' === $user_state && ! is_user_logged_in() ) {
					continue;
				}
				if ( 'logged_out' === $user_state && is_user_logged_in() ) {
					continue;
				}
			}

			// Advanced Display Conditions: User Roles
			$user_roles = get_post_meta( $popup->ID, '_es_popup_user_roles', true );
			if ( is_array( $user_roles ) && ! empty( $user_roles ) ) {
				if ( ! is_user_logged_in() ) {
					continue;
				}
				$current_user = wp_get_current_user();
				$matched_role = false;
				foreach ( $user_roles as $role ) {
					if ( in_array( $role, $current_user->roles, true ) ) {
						$matched_role = true;
						break;
					}
				}
				if ( ! $matched_role ) {
					continue;
				}
			}

			// Advanced Display Conditions: Specific Page Conditions
			$page_conds = get_post_meta( $popup->ID, '_es_popup_page_conditions', true );
			if ( is_array( $page_conds ) && ! empty( $page_conds ) ) {
				$page_match = false;
				if ( in_array( 'front_page', $page_conds, true ) && is_front_page() ) {
					$page_match = true;
				}
				if ( in_array( 'blog_page', $page_conds, true ) && is_home() ) {
					$page_match = true;
				}
				if ( in_array( 'search_results', $page_conds, true ) && is_search() ) {
					$page_match = true;
				}
				if ( in_array( '404_page', $page_conds, true ) && is_404() ) {
					$page_match = true;
				}
				if ( in_array( 'archive_pages', $page_conds, true ) && is_archive() ) {
					$page_match = true;
				}
				if ( in_array( 'single_posts', $page_conds, true ) && is_single() ) {
					$page_match = true;
				}
				if ( in_array( 'woo_products', $page_conds, true ) && class_exists( 'WooCommerce' ) && function_exists( 'is_product' ) && is_product() ) {
					$page_match = true;
				}

				if ( ! $page_match ) {
					continue;
				}
			}

			$matched[] = $popup;
		}

		return $matched;
	}

	/**
	 * Enqueue assets conditionally to optimize performance.
	 */
	public function conditional_enqueue_assets() {
		$matched = $this->get_active_matched_popups();
		if ( empty( $matched ) ) {
			return;
		}

		// Enqueue module styles
		wp_enqueue_style(
			'es-popup-builder-frontend',
			ELONIX_ACC_URL . 'assets/css/popup-builder-frontend.css',
			array(),
			'1.0.0'
		);

		// Enqueue module scripts (No jQuery dependency)
		wp_enqueue_script(
			'es-popup-builder-frontend',
			ELONIX_ACC_URL . 'assets/js/popup-builder-frontend.js',
			array(),
			'1.0.0',
			true
		);

		// Enqueue Elementor CSS post files dynamically
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
				\Elementor\Plugin::$instance->frontend->enqueue_styles();
			}
			foreach ( $matched as $popup ) {
				$css_file = new \Elementor\Core\Files\CSS\Post( $popup->ID );
				$css_file->enqueue();
			}
		}
	}

	/**
	 * Render popup container elements inside the page footer.
	 */
	public function render_popups_markup() {
		$matched = $this->get_active_matched_popups();
		if ( empty( $matched ) ) {
			return;
		}

		foreach ( $matched as $popup ) {
			$type             = get_post_meta( $popup->ID, '_es_popup_type', true );
			$trigger          = get_post_meta( $popup->ID, '_es_popup_trigger_type', true );
			$trigger_val      = get_post_meta( $popup->ID, '_es_popup_trigger_value', true );
			$scroll_val       = get_post_meta( $popup->ID, '_es_popup_scroll_value', true );
			$exit_sensitivity = get_post_meta( $popup->ID, '_es_popup_exit_intent_sensitivity', true );
			$priority         = get_post_meta( $popup->ID, '_es_popup_priority', true );
			$frequency        = get_post_meta( $popup->ID, '_es_popup_frequency', true );
			$cookie_expiry    = get_post_meta( $popup->ID, '_es_popup_cookie_expiry', true );
			$devices          = get_post_meta( $popup->ID, '_es_popup_devices', true );

			if ( empty( $type ) ) {
				$type = 'modal';
			}
			if ( empty( $trigger ) ) {
				$trigger = 'page_load';
			}
			if ( empty( $frequency ) ) {
				$frequency = 'show_once';
			}
			if ( empty( $exit_sensitivity ) ) {
				$exit_sensitivity = 20;
			}
			if ( empty( $priority ) ) {
				$priority = 10;
			}
			if ( ! is_array( $devices ) ) {
				$devices = array( 'desktop', 'tablet', 'mobile' );
			}
			?>
			<div id="es-popup-<?php echo esc_attr( $popup->ID ); ?>" 
				class="es-popup-wrapper es-popup-type-<?php echo esc_attr( $type ); ?>" 
				data-popup-id="<?php echo esc_attr( $popup->ID ); ?>"
				data-trigger="<?php echo esc_attr( $trigger ); ?>"
				data-trigger-val="<?php echo esc_attr( $trigger_val ); ?>"
				data-scroll-val="<?php echo esc_attr( $scroll_val ); ?>"
				data-sensitivity="<?php echo esc_attr( $exit_sensitivity ); ?>"
				data-priority="<?php echo esc_attr( $priority ); ?>"
				data-frequency="<?php echo esc_attr( $frequency ); ?>"
				data-cookie-expiry="<?php echo esc_attr( $cookie_expiry ); ?>"
				data-devices="<?php echo esc_attr( implode( ',', $devices ) ); ?>"
				role="dialog" 
				aria-modal="true" 
				aria-hidden="true" 
				style="display: none;">
				
				<div class="es-popup-overlay"></div>
				<div class="es-popup-container">
					<button class="es-popup-close-btn" aria-label="<?php esc_attr_e( 'Close Popup', 'elonix' ); ?>">&times;</button>
					<div class="es-popup-content">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $popup->ID );
						?>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
