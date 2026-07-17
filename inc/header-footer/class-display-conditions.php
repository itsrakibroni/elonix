<?php
/**
 * Elonix Display Conditions and Target Rules Engine
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Class Elonix_Display_Conditions
 * Replaces the previous conditions engine. Registers CPT metaboxes, enqueues rules JS, and resolves matching rules.
 */
class Elonix_Display_Conditions {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_layout_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_layout_meta_fields' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_metabox_assets' ) );
		add_action( 'admin_footer', array( $this, 'render_js_templates_and_scripts' ) );
	}

	/**
	 * Enqueue asset styles and scripts.
	 */
	public function enqueue_metabox_assets( $hook ) {
		global $post;
		$layout_types = array( 'es_header', 'es_footer', 'es_single', 'es_archive', 'es_search_template' );
		if ( ! $post || ! in_array( $post->post_type, $layout_types, true ) ) {
			return;
		}

		Elonix_Target_Rules::enqueue_assets();

		wp_enqueue_script(
			'elonix-display-conditions',
			ELONIX_ACC_URL . 'assets/js/display-conditions.js',
			array( 'jquery', 'select2', 'wp-util' ),
			'1.0.0',
			true
		);

		
		wp_enqueue_style(
			'elonix-display-conditions',
			ELONIX_ACC_URL . 'assets/admin/css/display-conditions.css',
			array(),
			ELONIX_VERSION
		);
wp_localize_script(
			'elonix-display-conditions',
			'esDisplayConditionsL10n',
			array(
				'placeholder' => esc_html__( 'Search posts, pages, categories, tags...', 'elonix' ),
				'nonce'       => esc_js( wp_create_nonce( 'es-get-posts-by-query' ) ),
			)
		);
	}

	/**
	 * Register the metabox.
	 */
	public function add_layout_meta_boxes() {
		$layout_types = array( 'es_header', 'es_footer', 'es_single', 'es_archive', 'es_search_template' );
		foreach ( $layout_types as $pt ) {
			if ( post_type_exists( $pt ) ) {
				add_meta_box(
					'es_layout_assignments_box',
					esc_html__( 'Display Conditions', 'elonix' ),
					array( $this, 'render_layout_metabox' ),
					$pt,
					'normal',
					'high'
				);
			}
		}
	}

	/**
	 * Renders Display rules settings metabox.
	 */
	public function render_layout_metabox( $post ) {
		wp_nonce_field( 'es_layout_meta_nonce', 'es_layout_meta_nonce_field' );

		$include_locations = get_post_meta( $post->ID, '_es_target_include_locations', true );
		$exclude_locations = get_post_meta( $post->ID, '_es_target_exclude_locations', true );
		$user_roles        = get_post_meta( $post->ID, '_es_target_user_roles', true );

		$priority = get_post_meta( $post->ID, '_es_priority', true );
		if ( '' === $priority ) {
			$priority = 0;
		}
		?>
		<!-- METABOX CSS MOVED EXTERNALLY -->

		<div class="es-metabox-wrapper">
			<!-- Include Rules -->
			<div class="es-metabox-row">
				<div class="es-metabox-label">
					<label><?php esc_html_e( 'Display On', 'elonix' ); ?></label>
					<p><?php esc_html_e( 'Specify page locations where this layout template should be displayed.', 'elonix' ); ?></p>
				</div>
				<div class="es-metabox-field">
					<?php Elonix_Target_Rules::render_location_rule_fields( '_es_target_include_locations', $include_locations ); ?>
				</div>
			</div>

			<!-- Exclude Rules -->
			<div class="es-metabox-row">
				<div class="es-metabox-label">
					<label><?php esc_html_e( 'Do Not Display On', 'elonix' ); ?></label>
					<p><?php esc_html_e( 'Specify page locations where this layout template should NOT be displayed.', 'elonix' ); ?></p>
				</div>
				<div class="es-metabox-field">
					<?php Elonix_Target_Rules::render_location_rule_fields( '_es_target_exclude_locations', $exclude_locations ); ?>
				</div>
			</div>

			<!-- User Roles Rules -->
			<div class="es-metabox-row">
				<div class="es-metabox-label">
					<label><?php esc_html_e( 'User Roles', 'elonix' ); ?></label>
					<p><?php esc_html_e( 'Limit this layout visibility to targeted visitor audiences or user roles.', 'elonix' ); ?></p>
				</div>
				<div class="es-metabox-field">
					<?php Elonix_Target_Rules::render_user_rule_fields( '_es_target_user_roles', $user_roles ); ?>
				</div>
			</div>

			<!-- Priority Rule -->
			<div class="es-metabox-row">
				<div class="es-metabox-label">
					<label><?php esc_html_e( 'Priority', 'elonix' ); ?></label>
					<p><?php esc_html_e( 'Sort index for layouts. Higher priority templates override lower priority templates in case of conflict.', 'elonix' ); ?></p>
				</div>
				<div class="es-metabox-field">
					<input type="number" name="_es_priority" value="<?php echo esc_attr( $priority ); ?>" min="0" step="1">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save Layout metadata.
	 */
	public function save_layout_meta_fields( $post_id ) {
		if ( ! isset( $_POST['es_layout_meta_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['es_layout_meta_nonce_field'] ) ), 'es_layout_meta_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		// Save Priority
		if ( isset( $_POST['_es_priority'] ) ) {
			update_post_meta( $post_id, '_es_priority', intval( $_POST['_es_priority'] ) );
		}

		// Save locations inclusions
		$include_locs = Elonix_Target_Rules::format_rule_meta_value( $_POST, '_es_target_include_locations' );
		update_post_meta( $post_id, '_es_target_include_locations', $include_locs );

		// Save locations exclusions
		$exclude_locs = Elonix_Target_Rules::format_rule_meta_value( $_POST, '_es_target_exclude_locations' );
		update_post_meta( $post_id, '_es_target_exclude_locations', $exclude_locs );

		// Save user roles target
		if ( isset( $_POST['_es_target_user_roles'] ) && is_array( $_POST['_es_target_user_roles'] ) ) {
			$roles = array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['_es_target_user_roles'] ) ) );
			update_post_meta( $post_id, '_es_target_user_roles', array_values( $roles ) );
		} else {
			update_post_meta( $post_id, '_es_target_user_roles', array() );
		}
	}

	public function evaluate_conditions( $template_id ) {
		// 1. Evaluate User Roles
		$user_roles = get_post_meta( $template_id, '_es_target_user_roles', true );
		if ( is_array( $user_roles ) && ! empty( $user_roles ) ) {
			$role_match = false;
			foreach ( $user_roles as $role ) {
				if ( empty( $role ) || 'all' === $role ) {
					$role_match = true;
				} elseif ( 'logged-in' === $role && is_user_logged_in() ) {
					$role_match = true;
				} elseif ( 'logged-out' === $role && ! is_user_logged_in() ) {
					$role_match = true;
				} elseif ( is_user_logged_in() ) {
					$user = wp_get_current_user();
					if ( in_array( $role, (array) $user->roles, true ) ) {
						$role_match = true;
					}
				}
			}
			if ( ! $role_match ) {
				return false;
			}
		}

		$post_id = ( ! is_404() && ! is_search() && ! is_archive() && ! is_home() ) ? get_the_ID() : false;

		// 2. Evaluate Inclusions
		$include_on = get_post_meta( $template_id, '_es_target_include_locations', true );
		$is_display = $this->parse_layout_display_condition( $post_id, $include_on );

		if ( ! $is_display ) {
			return false;
		}

		// 3. Evaluate Exclusions
		$exclude_on = get_post_meta( $template_id, '_es_target_exclude_locations', true );
		$is_exclude = $this->parse_layout_display_condition( $post_id, $exclude_on );

		if ( $is_exclude ) {
			return false;
		}

		return true;
	}

	/**
	 * Matching condition evaluator logic derived from HFE.
	 */
	public function parse_layout_display_condition( $post_id, $rules ) {
		$display    = false;
		$normalized = Elonix_Target_Rules::normalize_rules( $rules );

		if ( empty( $normalized ) ) {
			return false;
		}

		$current_post_type = get_post_type( $post_id );

		foreach ( $normalized as $row ) {
			$rule      = $row['rule'];
			$specifics = isset( $row['specific'] ) ? $row['specific'] : array();

			switch ( $rule ) {
				case 'basic-global':
					$display = true;
					break;

				case 'basic-singulars':
					if ( is_singular() ) {
						$display = true;
					}
					break;

				case 'basic-archives':
					if ( is_archive() || is_search() ) {
						$display = true;
					}
					break;

				case 'special-front':
					if ( is_front_page() ) {
						$display = true;
					}
					break;

				case 'special-blog':
					if ( is_home() ) {
						$display = true;
					}
					break;

				case 'special-search':
					if ( is_search() ) {
						$display = true;
					}
					break;

				case 'special-404':
					if ( is_404() ) {
						$display = true;
					}
					break;

				case 'page|all':
					if ( is_page() ) {
						$display = true;
					}
					break;

				case 'post|all':
					if ( is_singular( 'post' ) ) {
						$display = true;
					}
					break;

				// Specific targets (Posts, Pages, CPTs)
				case 'page|specific':
				case 'post|specific':
					if ( ! empty( $specifics ) ) {
						foreach ( $specifics as $spec_val ) {
							if ( strpos( $spec_val, 'post-' ) !== false ) {
								$spec_id = (int) str_replace( 'post-', '', $spec_val );
								if ( $spec_id === $post_id ) {
									$display = true;
									break 2;
								}
							}
						}
					}
					break;

				// Default or auto-detected Taxonomies All Archive
				default:
					// Check for CPT singular all: {cpt_slug}|all
					if ( preg_match( '/^([a-zA-Z0-9_\-]+)\|all$/', $rule, $matches ) ) {
						$cpt_slug = $matches[1];
						if ( is_singular( $cpt_slug ) ) {
							$display = true;
						}
					}
					// Check for CPT singular specific: {cpt_slug}|specific
					elseif ( preg_match( '/^([a-zA-Z0-9_\-]+)\|specific$/', $rule, $matches ) ) {
						$cpt_slug = $matches[1];
						if ( is_singular( $cpt_slug ) && ! empty( $specifics ) ) {
							foreach ( $specifics as $spec_val ) {
								if ( strpos( $spec_val, 'post-' ) !== false ) {
									$spec_id = (int) str_replace( 'post-', '', $spec_val );
									if ( $spec_id === $post_id ) {
										$display = true;
										break 2;
									}
								}
							}
						}
					}
					// Check for CPT archive all: {cpt_slug}|all|archive
					elseif ( preg_match( '/^([a-zA-Z0-9_\-]+)\|all\|archive$/', $rule, $matches ) ) {
						$cpt_slug = $matches[1];
						if ( is_post_type_archive( $cpt_slug ) ) {
							$display = true;
						}
					}
					// Check for Taxonomy all archives: tax|all|{tax_slug}
					elseif ( preg_match( '/^tax\|all\|([a-zA-Z0-9_\-]+)$/', $rule, $matches ) ) {
						$tax_slug = $matches[1];
						if ( is_tax( $tax_slug ) || ( 'category' === $tax_slug && is_category() ) || ( 'post_tag' === $tax_slug && is_tag() ) ) {
							$display = true;
						}
					}
					// Check for Taxonomy specific archive: tax|specific|{tax_slug}
					elseif ( preg_match( '/^tax\|specific\|([a-zA-Z0-9_\-]+)$/', $rule, $matches ) ) {
						$tax_slug = $matches[1];
						if ( ! empty( $specifics ) ) {
							$queried_id = get_queried_object_id();
							foreach ( $specifics as $spec_val ) {
								// Match term archive
								if ( strpos( $spec_val, 'tax-' ) !== false && strpos( $spec_val, '-single-' ) === false ) {
									$term_id = (int) str_replace( 'tax-', '', $spec_val );
									if ( $term_id === $queried_id && ( is_tax( $tax_slug ) || ( 'category' === $tax_slug && is_category() ) || ( 'post_tag' === $tax_slug && is_tag() ) ) ) {
										$display = true;
										break 2;
									}
								}
								// Match singular belonging to taxonomy term
								elseif ( strpos( $spec_val, 'tax-' ) !== false && strpos( $spec_val, '-single-' ) !== false ) {
									$parts   = explode( '-single-', $spec_val );
									$term_id = (int) str_replace( 'tax-', '', $parts[0] );
									if ( is_singular() && has_term( $term_id, $tax_slug, $post_id ) ) {
										$display = true;
										break 2;
									}
								}
							}
						}
					}
					break;
			}

			if ( $display ) {
				break;
			}
		}

		return $display;
	}

	/**
	 * Find winning active layout template by priority and ID.
	 */
	public function get_active_template_id( $post_type ) {
		static $cached_ids = array();

		if ( isset( $cached_ids[ $post_type ] ) ) {
			return $cached_ids[ $post_type ];
		}

		// Disable matches recursively during editing
		if ( is_singular( array( 'es_header', 'es_footer' ) ) ) {
			return 0;
		}

		if ( 'es_header' === $post_type && ! Elonix_Toolkit_Module_Manager::is_module_enabled( 'header_builder' ) ) {
			return 0;
		}
		if ( 'es_footer' === $post_type && ! Elonix_Toolkit_Module_Manager::is_module_enabled( 'footer_builder' ) ) {
			return 0;
		}

		$args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$templates = get_posts( $args );

		if ( empty( $templates ) ) {
			$cached_ids[ $post_type ] = 0;
			return 0;
		}

		$candidates = array();

		foreach ( $templates as $tpl_id ) {
			if ( $this->evaluate_conditions( $tpl_id ) ) {
				$priority     = get_post_meta( $tpl_id, '_es_priority', true );
				$candidates[] = array(
					'id'       => $tpl_id,
					'priority' => ( '' === $priority ) ? 0 : intval( $priority ),
				);
			}
		}

		if ( empty( $candidates ) ) {
			$cached_ids[ $post_type ] = 0;
			return 0;
		}

		// Sort: Highest priority wins. If identical, newest template (larger ID) wins.
		usort(
			$candidates,
			function ( $a, $b ) {
				if ( $a['priority'] === $b['priority'] ) {
					return $b['id'] - $a['id'];
				}
				return $b['priority'] - $a['priority'];
			}
		);

		$winner                   = $candidates[0]['id'];
		$cached_ids[ $post_type ] = $winner;

		return $winner;
	}

	/**
	 * Outputs JS script template and javascript handler code for metabox display management.
	 */
	public function render_js_templates_and_scripts() {
		global $post;
		$layout_types = array( 'es_header', 'es_footer' );
		if ( ! $post || ! in_array( $post->post_type, $layout_types, true ) ) {
			return;
		}

		$selections      = Elonix_Target_Rules::get_location_selections();
		$user_selections = Elonix_Target_Rules::get_user_selections();
		?>
		<!-- TARGET RULE JS ROW TEMPLATES -->
		<script type="text/html" id="tmpl-es-location-row-template">
			<div class="es-rule-row-item" data-index="{{data.index}}" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:10px;">
				<div class="es-select-rule-wrapper">
					<select name="{{data.name}}[{{data.index}}][rule]" class="es-rule-condition-select" style="min-width: 220px; height:36px; border-radius:6px;">
						<option value=""><?php esc_html_e( '-- Select Location --', 'elonix' ); ?></option>
						<?php foreach ( $selections as $grp_slug => $grp ) : ?>
							<optgroup label="<?php echo esc_attr( $grp['label'] ); ?>">
								<?php foreach ( $grp['value'] as $k => $v ) : ?>
									<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $v ); ?></option>
								<?php endforeach; ?>
							</optgroup>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="es-specific-search-wrapper" style="display:none; min-width: 260px;">
					<select name="{{data.name}}[{{data.index}}][specific][]" class="es-select2-ajax-search" multiple="multiple" style="width: 100%;">
					</select>
				</div>
				<span class="es-delete-rule-row dashicons dashicons-dismiss" style="cursor:pointer; color:#ef4444; font-size:20px; margin-top:2px;"></span>
			</div>
		</script>

		<script type="text/html" id="tmpl-es-user-row-template">
			<div class="es-user-row-item" data-index="{{data.index}}" style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
				<select name="{{data.name}}[{{data.index}}]" style="min-width: 220px; height:36px; border-radius:6px;">
					<option value=""><?php esc_html_e( '-- Select User Audience --', 'elonix' ); ?></option>
					<?php foreach ( $user_selections as $grp_slug => $grp ) : ?>
						<optgroup label="<?php echo esc_attr( $grp['label'] ); ?>">
							<?php foreach ( $grp['value'] as $k => $v ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $v ); ?></option>
							<?php endforeach; ?>
						</optgroup>
					<?php endforeach; ?>
				</select>
				<span class="es-delete-user-row dashicons dashicons-dismiss" style="cursor:pointer; color:#ef4444; font-size:20px;"></span>
			</div>
		</script>
		<!-- DISPLAY CONDITIONS SCRIPTS ENQUEUED EXTERNALLY -->
		<?php
	}
}

// Instantiate target rules helper to register AJAX queries
Elonix_Target_Rules::instance();
