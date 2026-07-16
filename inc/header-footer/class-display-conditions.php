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
		$layout_types = array( 'tv_header', 'tv_footer', 'tv_single', 'tv_archive', 'tv_search_template' );
		if ( ! $post || ! in_array( $post->post_type, $layout_types, true ) ) {
			return;
		}

		Elonix_Target_Rules::enqueue_assets();
	}

	/**
	 * Register the metabox.
	 */
	public function add_layout_meta_boxes() {
		$layout_types = array( 'tv_header', 'tv_footer', 'tv_single', 'tv_archive', 'tv_search_template' );
		foreach ( $layout_types as $pt ) {
			if ( post_type_exists( $pt ) ) {
				add_meta_box(
					'tv_layout_assignments_box',
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
		wp_nonce_field( 'tv_layout_meta_nonce', 'tv_layout_meta_nonce_field' );

		$include_locations = get_post_meta( $post->ID, '_tv_target_include_locations', true );
		$exclude_locations = get_post_meta( $post->ID, '_tv_target_exclude_locations', true );
		$user_roles        = get_post_meta( $post->ID, '_tv_target_user_roles', true );

		$priority = get_post_meta( $post->ID, '_tv_priority', true );
		if ( '' === $priority ) {
			$priority = 0;
		}
		?>
		<style>
			.tv-metabox-wrapper {
				padding: 10px 0;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			}
			.tv-metabox-row {
				display: flex;
				border-bottom: 1px solid #f1f5f9;
				padding: 18px 0;
			}
			.tv-metabox-row:last-child {
				border-bottom: none;
			}
			.tv-metabox-label {
				width: 220px;
				font-weight: 600;
				color: #0f172a;
				font-size: 13.5px;
				padding-right: 20px;
			}
			.tv-metabox-field {
				flex: 1;
			}
			.tv-metabox-label p {
				font-weight: 400;
				color: #64748b;
				margin: 5px 0 0 0;
				font-size: 12px;
				line-height: 1.4;
			}
			.tv-metabox-field input[type="number"] {
				height: 36px;
				border-radius: 6px;
				border: 1px solid #cbd5e1;
				padding: 0 10px;
				width: 120px;
			}
			.tv-metabox-field .select2-container--default .select2-selection--single,
			.tv-metabox-field .select2-container--default .select2-selection--multiple {
				border: 1px solid #cbd5e1;
				border-radius: 6px;
				min-height: 36px;
				display: flex;
				align-items: center;
			}
			.tv-metabox-field .select2-container--default .select2-selection--single .select2-selection__rendered {
				line-height: 36px;
				color: #334155;
				padding-left: 10px;
			}
			.tv-metabox-field .select2-container--default .select2-selection--single .select2-selection__arrow {
				height: 34px;
			}
			.tv-metabox-field .select2-container--default .select2-selection--multiple .select2-selection__rendered {
				padding: 2px 5px;
			}
			.tv-metabox-field .select2-container--default .select2-selection--multiple .select2-selection__choice {
				background-color: #f1f5f9;
				border: 1px solid #e2e8f0;
				border-radius: 4px;
				padding: 2px 8px;
				color: #334155;
				font-size: 12px;
				margin-top: 3px;
			}
			.tv-metabox-field select {
				height: 36px;
				border-radius: 6px;
				border: 1px solid #cbd5e1;
				color: #334155;
				padding: 0 8px;
				min-width: 220px;
			}
		</style>

		<div class="tv-metabox-wrapper">
			<!-- Include Rules -->
			<div class="tv-metabox-row">
				<div class="tv-metabox-label">
					<label><?php esc_html_e( 'Display On', 'elonix' ); ?></label>
					<p><?php esc_html_e( 'Specify page locations where this layout template should be displayed.', 'elonix' ); ?></p>
				</div>
				<div class="tv-metabox-field">
					<?php Elonix_Target_Rules::render_location_rule_fields( '_tv_target_include_locations', $include_locations ); ?>
				</div>
			</div>

			<!-- Exclude Rules -->
			<div class="tv-metabox-row">
				<div class="tv-metabox-label">
					<label><?php esc_html_e( 'Do Not Display On', 'elonix' ); ?></label>
					<p><?php esc_html_e( 'Specify page locations where this layout template should NOT be displayed.', 'elonix' ); ?></p>
				</div>
				<div class="tv-metabox-field">
					<?php Elonix_Target_Rules::render_location_rule_fields( '_tv_target_exclude_locations', $exclude_locations ); ?>
				</div>
			</div>

			<!-- User Roles Rules -->
			<div class="tv-metabox-row">
				<div class="tv-metabox-label">
					<label><?php esc_html_e( 'User Roles', 'elonix' ); ?></label>
					<p><?php esc_html_e( 'Limit this layout visibility to targeted visitor audiences or user roles.', 'elonix' ); ?></p>
				</div>
				<div class="tv-metabox-field">
					<?php Elonix_Target_Rules::render_user_rule_fields( '_tv_target_user_roles', $user_roles ); ?>
				</div>
			</div>

			<!-- Priority Rule -->
			<div class="tv-metabox-row">
				<div class="tv-metabox-label">
					<label><?php esc_html_e( 'Priority', 'elonix' ); ?></label>
					<p><?php esc_html_e( 'Sort index for layouts. Higher priority templates override lower priority templates in case of conflict.', 'elonix' ); ?></p>
				</div>
				<div class="tv-metabox-field">
					<input type="number" name="_tv_priority" value="<?php echo esc_attr( $priority ); ?>" min="0" step="1">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save Layout metadata.
	 */
	public function save_layout_meta_fields( $post_id ) {
		if ( ! isset( $_POST['tv_layout_meta_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tv_layout_meta_nonce_field'] ) ), 'tv_layout_meta_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		// Save Priority
		if ( isset( $_POST['_tv_priority'] ) ) {
			update_post_meta( $post_id, '_tv_priority', intval( $_POST['_tv_priority'] ) );
		}

		// Save locations inclusions
		$include_locs = Elonix_Target_Rules::format_rule_meta_value( $_POST, '_tv_target_include_locations' );
		update_post_meta( $post_id, '_tv_target_include_locations', $include_locs );

		// Save locations exclusions
		$exclude_locs = Elonix_Target_Rules::format_rule_meta_value( $_POST, '_tv_target_exclude_locations' );
		update_post_meta( $post_id, '_tv_target_exclude_locations', $exclude_locs );

		// Save user roles target
		if ( isset( $_POST['_tv_target_user_roles'] ) && is_array( $_POST['_tv_target_user_roles'] ) ) {
			$roles = array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['_tv_target_user_roles'] ) ) );
			update_post_meta( $post_id, '_tv_target_user_roles', array_values( $roles ) );
		} else {
			update_post_meta( $post_id, '_tv_target_user_roles', array() );
		}
	}

	public function evaluate_conditions( $template_id ) {
		// 1. Evaluate User Roles
		$user_roles = get_post_meta( $template_id, '_tv_target_user_roles', true );
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
		$include_on = get_post_meta( $template_id, '_tv_target_include_locations', true );
		$is_display = $this->parse_layout_display_condition( $post_id, $include_on );

		if ( ! $is_display ) {
			return false;
		}

		// 3. Evaluate Exclusions
		$exclude_on = get_post_meta( $template_id, '_tv_target_exclude_locations', true );
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
		if ( is_singular( array( 'tv_header', 'tv_footer' ) ) ) {
			return 0;
		}

		if ( 'tv_header' === $post_type && ! Elonix_Toolkit_Module_Manager::is_module_enabled( 'header_builder' ) ) {
			return 0;
		}
		if ( 'tv_footer' === $post_type && ! Elonix_Toolkit_Module_Manager::is_module_enabled( 'footer_builder' ) ) {
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
				$priority     = get_post_meta( $tpl_id, '_tv_priority', true );
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
		$layout_types = array( 'tv_header', 'tv_footer' );
		if ( ! $post || ! in_array( $post->post_type, $layout_types, true ) ) {
			return;
		}

		$selections      = Elonix_Target_Rules::get_location_selections();
		$user_selections = Elonix_Target_Rules::get_user_selections();
		?>
		<!-- TARGET RULE JS ROW TEMPLATES -->
		<script type="text/html" id="tmpl-tv-location-row-template">
			<div class="tv-rule-row-item" data-index="{{data.index}}" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:10px;">
				<div class="tv-select-rule-wrapper">
					<select name="{{data.name}}[{{data.index}}][rule]" class="tv-rule-condition-select" style="min-width: 220px; height:36px; border-radius:6px;">
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
				<div class="tv-specific-search-wrapper" style="display:none; min-width: 260px;">
					<select name="{{data.name}}[{{data.index}}][specific][]" class="tv-select2-ajax-search" multiple="multiple" style="width: 100%;">
					</select>
				</div>
				<span class="tv-delete-rule-row dashicons dashicons-dismiss" style="cursor:pointer; color:#ef4444; font-size:20px; margin-top:2px;"></span>
			</div>
		</script>

		<script type="text/html" id="tmpl-tv-user-row-template">
			<div class="tv-user-row-item" data-index="{{data.index}}" style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
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
				<span class="tv-delete-user-row dashicons dashicons-dismiss" style="cursor:pointer; color:#ef4444; font-size:20px;"></span>
			</div>
		</script>

		<script>
			jQuery(document).ready(function($) {
				// Setup AJAX Select2 search function
				function initSelect2Ajax(element) {
					var $select = $(element);
					$select.select2({
						placeholder: '<?php esc_html_e( 'Search posts, pages, categories, tags...', 'elonix' ); ?>',
						minimumInputLength: 2,
						ajax: {
							url: ajaxurl,
							dataType: 'json',
							delay: 250,
							method: 'POST',
							data: function(params) {
								var $row = $select.closest('.tv-rule-row-item');
								var ruleVal = $row.find('.tv-rule-condition-select').val();
								return {
									q: params.term,
									rule: ruleVal,
									action: 'elonix_get_posts_by_query',
									nonce: '<?php echo esc_js( wp_create_nonce( 'tv-get-posts-by-query' ) ); ?>'
								};
							},
							processResults: function(data) {
								return {
									results: data
								};
							},
							cache: true
						}
					});
				}

				// Initialize Select2 on existing fields
				$('.tv-select2-ajax-search').each(function() {
					initSelect2Ajax(this);
				});

				// Toggle specific select box visibility and handle select2 activation/clearing
				$(document).on('change', '.tv-rule-condition-select', function() {
					var ruleVal = $(this).val();
					var $rowItem = $(this).closest('.tv-rule-row-item');
					var $searchWrapper = $rowItem.find('.tv-specific-search-wrapper');
					var $select2 = $searchWrapper.find('.tv-select2-ajax-search');

					// Clear any existing selection
					$select2.val(null).trigger('change');

					if (ruleVal && ruleVal.indexOf('specific') !== -1) {
						$searchWrapper.show();
						initSelect2Ajax($select2);
					} else {
						$searchWrapper.hide();
					}
				});

				// Add location rule row
				$('.tv-add-rule-row-btn').on('click', function(e) {
					e.preventDefault();
					var $wrapper = $(this).closest('.tv-target-rules-wrapper');
					var name = $wrapper.data('name');
					var $container = $wrapper.find('.tv-rules-rows-container');
					
					// Find next index
					var nextIdx = 0;
					$container.find('.tv-rule-row-item').each(function() {
						var idx = parseInt($(this).attr('data-index'));
						if (idx >= nextIdx) {
							nextIdx = idx + 1;
						}
					});

					var tmpl = wp.template('tv-location-row-template');
					var html = tmpl({ index: nextIdx, name: name });
					$container.append(html);
				});

				// Remove location rule row
				$(document).on('click', '.tv-delete-rule-row', function() {
					var $wrapper = $(this).closest('.tv-target-rules-wrapper');
					var $container = $wrapper.find('.tv-rules-rows-container');
					var rowCount = $container.find('.tv-rule-row-item').length;

					if (rowCount > 1) {
						$(this).closest('.tv-rule-row-item').remove();
					} else {
						var $row = $(this).closest('.tv-rule-row-item');
						$row.find('.tv-rule-condition-select').val('').trigger('change');
						$row.find('.tv-select2-ajax-search').val(null).trigger('change');
					}
				});

				// Add user role rule row
				$('.tv-add-user-row-btn').on('click', function(e) {
					e.preventDefault();
					var $wrapper = $(this).closest('.tv-user-rules-wrapper');
					var name = $wrapper.data('name');
					var $container = $wrapper.find('.tv-user-rows-container');

					var nextIdx = 0;
					$container.find('.tv-user-row-item').each(function() {
						var idx = parseInt($(this).attr('data-index'));
						if (idx >= nextIdx) {
							nextIdx = idx + 1;
						}
					});

					var tmpl = wp.template('tv-user-row-template');
					var html = tmpl({ index: nextIdx, name: name });
					$container.append(html);
				});

				// Remove user role rule row
				$(document).on('click', '.tv-delete-user-row', function() {
					var $wrapper = $(this).closest('.tv-user-rules-wrapper');
					var $container = $wrapper.find('.tv-user-rows-container');
					var rowCount = $container.find('.tv-user-row-item').length;

					if (rowCount > 1) {
						$(this).closest('.tv-user-row-item').remove();
					} else {
						$(this).closest('.tv-user-row-item').find('select').val('');
					}
				});
			});
		</script>
		<?php
	}
}

// Instantiate target rules helper to register AJAX queries
Elonix_Target_Rules::instance();
