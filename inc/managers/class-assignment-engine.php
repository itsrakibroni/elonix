<?php
/**
 * Elonix – Toolkit for Elementor Unified Template Assignment Engine
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Assignment_Engine {

	private static $instance = null;
	private $cached_matches = array();

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_elonix_assignment_search', array( $this, 'ajax_search' ) );
		add_action( 'wp_ajax_elonix_assignment_save', array( $this, 'ajax_save' ) );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function enqueue_assets() {
		// Enqueue Select2
		if ( wp_script_is( 'elementor-select2', 'registered' ) ) {
			wp_enqueue_script( 'elementor-select2' );
			wp_enqueue_style( 'elementor-select2' );
		} else {
			wp_enqueue_script( 'elonix-select2', ELONIX_ACC_URL . 'assets/js/vendor/select2.min.js', array( 'jquery' ), '4.0.13', true );
			wp_enqueue_style( 'elonix-select2', ELONIX_ACC_URL . 'assets/css/vendor/select2.min.css', array(), '4.0.13' );
		}

		wp_enqueue_style( 'elonix-assignment-engine', ELONIX_ACC_URL . 'assets/admin/css/assignment-engine.css', array(), ELONIX_VERSION );
		wp_enqueue_script( 'elonix-assignment-engine', ELONIX_ACC_URL . 'assets/admin/js/assignment-engine.js', array( 'jquery', 'wp-util' ), ELONIX_VERSION, true );
		
		$rule_options = array();
		if ( class_exists( 'Elonix_Target_Rules' ) ) {
			$rule_options = Elonix_Target_Rules::get_location_selections();
		}

		wp_localize_script( 'elonix-assignment-engine', 'esAssignmentEngine', array(
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
			'admin_url'    => admin_url(),
			'nonce'        => wp_create_nonce( 'es_assignment_nonce' ),
			'search_nonce' => wp_create_nonce( 'es-get-posts-by-query' ),
			'rule_options' => $rule_options,
			'strings'      => array(
				'saving'       => esc_html__( 'Saving...', 'elonix' ),
				'saved'        => esc_html__( 'Saved!', 'elonix' ),
				'error'        => esc_html__( 'Error occurred', 'elonix' ),
				'select_rule'  => esc_html__( 'Select Condition...', 'elonix' ),
				'search_sub'   => esc_html__( 'Search Specific...', 'elonix' ),
				'no_results'   => esc_html__( 'No results found', 'elonix' ),
				'add_rule'     => esc_html__( 'Add Rule', 'elonix' ),
				'cancel'       => esc_html__( 'Cancel', 'elonix' ),
			)
		) );
	}



	// -------------------------------------------------------------------------
	// PUBLIC API METHODS
	// -------------------------------------------------------------------------

	/**
	 * Assign conditions to a template.
	 *
	 * @param int $post_id
	 * @param array $include
	 * @param array $exclude
	 * @param int $priority
	 * @param bool $active
	 */
	public function assign_template( $post_id, $include = array(), $exclude = array(), $priority = 10, $active = true ) {
		update_post_meta( $post_id, '_es_target_include_locations', $include );
		update_post_meta( $post_id, '_es_target_exclude_locations', $exclude );
		update_post_meta( $post_id, '_es_priority', intval( $priority ) );
		update_post_meta( $post_id, '_es_assignment_active', $active ? 'yes' : 'no' );

		$this->clear_cache();
	}

	/**
	 * Remove all assignments from a template.
	 */
	public function remove_assignment( $post_id ) {
		delete_post_meta( $post_id, '_es_target_include_locations' );
		delete_post_meta( $post_id, '_es_target_exclude_locations' );
		update_post_meta( $post_id, '_es_assignment_active', 'no' );

		$this->clear_cache();
	}

	/**
	 * Get assignment data for a template.
	 */
	public function get_assignment( $post_id ) {
		$include = get_post_meta( $post_id, '_es_target_include_locations', true );
		$exclude = get_post_meta( $post_id, '_es_target_exclude_locations', true );
		$priority = get_post_meta( $post_id, '_es_priority', true );
		$active = get_post_meta( $post_id, '_es_assignment_active', true );

		// Legacy Migration Support
		if ( empty( $active ) && ( ! empty( $include ) || ! empty( $exclude ) ) ) {
			$active = 'yes';
			update_post_meta( $post_id, '_es_assignment_active', 'yes' );
		}

		return array(
			'include'  => $this->normalize_rules( $include ),
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- False positive. Internal Assignment Engine configuration key; not a WP_Query argument.
			'exclude'  => $this->normalize_rules( $exclude ),
			'priority' => ( '' === $priority ) ? 10 : intval( $priority ),
			'active'   => ( 'no' === $active ) ? false : true,
		);
	}

	/**
	 * Centralized Normalization Layer.
	 * Converts various meta storage formats into a flat array of evaluation strings.
	 */
	private function normalize_rules( $raw_rules ) {
		$normalized = array();

		if ( empty( $raw_rules ) || ! is_array( $raw_rules ) ) {
			return $normalized;
		}

		// Handle legacy HFE format: array( 'rule' => array(...), 'specific' => array(...) )
		if ( isset( $raw_rules['rule'] ) && is_array( $raw_rules['rule'] ) ) {
			$base_rules = $raw_rules['rule'];
			$specifics  = isset( $raw_rules['specific'] ) && is_array( $raw_rules['specific'] ) ? $raw_rules['specific'] : array();
			
			foreach ( $base_rules as $base_rule ) {
				if ( empty( $base_rule ) ) continue;
				if ( 'specifics' === $base_rule || strpos( (string) $base_rule, 'specific' ) !== false ) {
					foreach ( $specifics as $specific_id ) {
						if ( is_string( $specific_id ) || is_numeric( $specific_id ) ) {
							$normalized[] = (string) $specific_id;
						}
					}
				} else {
					$normalized[] = (string) $base_rule;
				}
			}
			return array_unique( $normalized );
		}

		// Handle array of items (Drawer flat strings OR Builder nested arrays)
		foreach ( $raw_rules as $rule ) {
			if ( is_string( $rule ) ) {
				$normalized[] = $rule;
			} elseif ( is_array( $rule ) && isset( $rule['rule'] ) ) {
				$base_rule = (string) $rule['rule'];
				if ( strpos( $base_rule, 'specific' ) !== false && ! empty( $rule['specific'] ) && is_array( $rule['specific'] ) ) {
					foreach ( $rule['specific'] as $specific_id ) {
						if ( is_string( $specific_id ) || is_numeric( $specific_id ) ) {
							$normalized[] = (string) $specific_id;
						}
					}
				} else {
					$normalized[] = $base_rule;
				}
			}
		}

		return array_unique( $normalized );
	}

	/**
	 * Detect conflicts before saving.
	 */
	public function detect_conflicts( $post_type, $include, $exclude, $current_post_id ) {
		$conflicts = array();
		
		$args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Required to prevent the current template from matching itself.
			'post__not_in'   => array( $current_post_id )
		);
		$templates = get_posts( $args );

		foreach ( $templates as $tpl_id ) {
			$data = $this->get_assignment( $tpl_id );
			if ( ! $data['active'] ) continue;

			// Simple strict overlap check
			$overlap = array_intersect( $include, $data['include'] );
			if ( ! empty( $overlap ) ) {
				$conflicts[] = array(
					'id'       => $tpl_id,
					'title'    => get_the_title( $tpl_id ),
					'overlap'  => $overlap,
					'priority' => $data['priority']
				);
			}
		}

		return $conflicts;
	}

	/**
	 * Get the highest priority template matching the current request.
	 */
	public function get_matching_template( $post_type ) {
		if ( isset( $this->cached_matches[ $post_type ] ) ) {
			return $this->cached_matches[ $post_type ];
		}

		// Edit Mode Protections
		if ( class_exists( '\Elementor\Plugin' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				if ( is_singular( $post_type ) ) {
					return 0; // The builder itself will render its own canvas
				}
			}
		}
		
		if ( is_singular( array( 'elonix_header', 'elonix_footer', 'elonix_single', 'elonix_archive', 'es_search_template', 'es_404_template' ) ) ) {
			return 0; // Disable recursive embedding when editing any template
		}

		$args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$templates = get_posts( $args );
		if ( empty( $templates ) ) {
			$this->cached_matches[ $post_type ] = 0;
			return 0;
		}

		$candidates = array();

		foreach ( $templates as $tpl_id ) {
			$data = $this->get_assignment( $tpl_id );
			if ( ! $data['active'] ) continue;

			if ( $this->check_conditions( $data['include'], $data['exclude'] ) ) {
				$candidates[] = array(
					'id'       => $tpl_id,
					'priority' => $data['priority'],
				);
			}
		}

		if ( empty( $candidates ) ) {
			$this->cached_matches[ $post_type ] = 0;
			return 0;
		}

		usort( $candidates, function ( $a, $b ) {
			if ( $a['priority'] === $b['priority'] ) {
				return $b['id'] - $a['id']; // Newest wins
			}
			return $b['priority'] - $a['priority']; // Highest priority wins
		} );

		$winner = $candidates[0]['id'];
		$this->cached_matches[ $post_type ] = $winner;

		return $winner;
	}

	/**
	 * Validates if the current page matches the include/exclude rules.
	 */
	private function check_conditions( $include, $exclude ) {
		$is_included = false;
		
		if ( empty( $include ) ) {
			return false;
		}

		foreach ( $include as $rule ) {
			if ( $this->evaluate_rule( $rule ) ) {
				$is_included = true;
				break;
			}
		}

		if ( ! $is_included ) {
			return false;
		}

		if ( ! empty( $exclude ) ) {
			foreach ( $exclude as $rule ) {
				if ( $this->evaluate_rule( $rule ) ) {
					return false; // Excluded
				}
			}
		}

		return true;
	}

	/**
	 * Evaluates a single rule against the current query.
	 */
	private function evaluate_rule( $rule ) {
		if ( ! is_string( $rule ) ) {
			if ( class_exists( 'Elonix_Settings' ) && \Elonix_Settings::is_assignment_debug_enabled() ) {

			}
			return false;
		}

		$rule    = trim( $rule );
		$post_id = get_queried_object_id();

		// Basic Rules
		if ( 'entire_site' === $rule || 'basic-global' === $rule ) return true;
		if ( 'basic-singulars' === $rule && is_singular() ) return true;
		if ( 'basic-archives' === $rule && is_archive() ) return true;

		// Special Rules
		if ( 'special-front' === $rule && is_front_page() ) return true;
		if ( 'special-blog' === $rule && is_home() ) return true;
		if ( 'special-search' === $rule && is_search() ) return true;
		if ( 'special-404' === $rule && is_404() ) return true;

		// Pages & Posts
		if ( 'page|all' === $rule && is_page() ) return true;
		if ( 'post|all' === $rule && is_singular( 'post' ) ) return true;

		// Specific Taxonomy Archives (e.g. tax|all|category)
		if ( preg_match( '/^tax\|all\|([a-zA-Z0-9_\-]+)$/', $rule, $matches ) ) {
			if ( is_tax( $matches[1] ) || is_category() || is_tag() ) {
				return true;
			}
		}

		// Specific Post Types (e.g. product|all)
		if ( preg_match( '/^([a-zA-Z0-9_\-]+)\|all$/', $rule, $matches ) ) {
			if ( is_singular( $matches[1] ) ) {
				return true;
			}
		}
		
		// Specific Post Type Archive (e.g. product|all|archive)
		if ( preg_match( '/^([a-zA-Z0-9_\-]+)\|all\|archive$/', $rule, $matches ) ) {
			if ( is_post_type_archive( $matches[1] ) ) {
				return true;
			}
		}

		// Specific Single ID
		if ( strpos( $rule, 'post-' ) === 0 ) {
			$id = (int) str_replace( 'post-', '', $rule );
			if ( is_singular() && $post_id === $id ) return true;
		}

		// Specific Term Archive
		if ( strpos( $rule, 'tax-' ) === 0 && strpos( $rule, '-single-' ) === false ) {
			$id = (int) str_replace( 'tax-', '', $rule );
			if ( is_category( $id ) || is_tag( $id ) || is_tax( '', $id ) ) return true;
		}

		// Specific Term Singular Support (All Singulars from Category X)
		if ( strpos( $rule, 'tax-' ) === 0 && strpos( $rule, '-single-' ) !== false ) {
			$parts   = explode( '-single-', $rule );
			$term_id = (int) str_replace( 'tax-', '', $parts[0] );
			$tax_slug = $parts[1];
			if ( is_singular() && has_term( $term_id, $tax_slug, $post_id ) ) return true;
		}

		return false;
	}

	private function clear_cache() {
		$this->cached_matches = array();
	}

	// -------------------------------------------------------------------------
	// AJAX Handlers
	// -------------------------------------------------------------------------



	public function ajax_save() {
		check_ajax_referer( 'es_assignment_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'elonix' ) ) );
		}

		$post_id   = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';

		if ( ! $post_id || ! $post_type ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid request data.', 'elonix' ) ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'elonix' ) ) );
		}

		$include  = isset( $_POST['include'] ) && is_array( $_POST['include'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['include'] ) ) : array();
		$exclude  = isset( $_POST['exclude'] ) && is_array( $_POST['exclude'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['exclude'] ) ) : array();
		$priority = isset( $_POST['priority'] ) ? intval( $_POST['priority'] ) : 10;
		$active   = isset( $_POST['active'] ) && filter_var( wp_unslash( $_POST['active'] ), FILTER_VALIDATE_BOOLEAN );
		$force    = isset( $_POST['force'] ) ? intval( $_POST['force'] ) : 0;

		if ( $active && ! $force ) {
			$conflicts = $this->detect_conflicts( $post_type, $include, $exclude, $post_id );
			
			if ( ! empty( $conflicts ) ) {
				wp_send_json_error( array( 'conflicts' => $conflicts ) );
			}
		}

		$this->assign_template( $post_id, $include, $exclude, $priority, $active );

		wp_send_json_success( array( 'message' => esc_html__( 'Conditions saved successfully.', 'elonix' ) ) );
	}

	public function ajax_search() {
		// Import logic from Elonix_Target_Rules::ajax_get_posts_by_query
		// This handles the autocomplete for specific posts/terms.
	}
}
