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
 * Class Elonix_Target_Rules
 * Renders target rules and enqueues rules assets.
 */
class Elonix_Target_Rules {

	/**
	 * Single class instance.
	 *
	 * @var Elonix_Target_Rules|null
	 */
	private static $_instance = null;

	/**
	 * Get class instance.
	 *
	 * @return Elonix_Target_Rules Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_elonix_get_posts_by_query', array( $this, 'ajax_get_posts_by_query' ) );
	}

	/**
	 * AJAX handler to search posts, pages, categories, and tags.
	 */
	public function ajax_get_posts_by_query() {
		check_ajax_referer( 'es-get-posts-by-query', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( esc_html__( 'Unauthorized.', 'elonix' ) );
		}

		$search_string = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
		$rule          = isset( $_POST['rule'] ) ? sanitize_text_field( wp_unslash( $_POST['rule'] ) ) : '';
		$results       = array();

		$target_post_type = '';
		$target_taxonomy  = '';

		if ( ! empty( $rule ) ) {
			if ( preg_match( '/^tax\|specific\|([a-zA-Z0-9_\-]+)$/', $rule, $matches ) ) {
				$target_taxonomy = $matches[1];
			} elseif ( preg_match( '/^([a-zA-Z0-9_\-]+)\|specific$/', $rule, $matches ) ) {
				$target_post_type = $matches[1];
			}
		}

		// 1. Query post types
		if ( ! empty( $target_post_type ) || ( empty( $target_post_type ) && empty( $target_taxonomy ) ) ) {
			$post_types = array();
			if ( ! empty( $target_post_type ) ) {
				if ( post_type_exists( $target_post_type ) ) {
					$post_types[ $target_post_type ] = get_post_type_object( $target_post_type );
				}
			} else {
				$post_types    = get_post_types( array( 'public' => true ), 'objects' );
				$exclude_types = array( 'es_header', 'es_footer', 'attachment' );
				foreach ( $exclude_types as $ex ) {
					unset( $post_types[ $ex ] );
				}
			}

			foreach ( $post_types as $slug => $pt_obj ) {
				$posts_data = array();
				add_filter( 'posts_search', array( $this, 'search_only_titles' ), 10, 2 );

				$query = new WP_Query(
					array(
						's'              => $search_string,
						'post_type'      => $slug,
						'posts_per_page' => 30,
						'post_status'    => 'publish',
					)
				);

				remove_filter( 'posts_search', array( $this, 'search_only_titles' ), 10 );

				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						$title = get_the_title();
						if ( $query->post->post_parent ) {
							$title .= ' (' . get_the_title( $query->post->post_parent ) . ')';
						}
						$posts_data[] = array(
							'id'   => 'post-' . get_the_ID(),
							'text' => $title,
						);
					}
				}
				wp_reset_postdata();

				if ( ! empty( $posts_data ) ) {
					$results[] = array(
						'text'     => $pt_obj->labels->name,
						'children' => $posts_data,
					);
				}
			}
		}

		// 2. Query taxonomies
		if ( ! empty( $target_taxonomy ) || ( empty( $target_post_type ) && empty( $target_taxonomy ) ) ) {
			$taxonomies = array();
			if ( ! empty( $target_taxonomy ) ) {
				if ( taxonomy_exists( $target_taxonomy ) ) {
					$taxonomies[ $target_taxonomy ] = get_taxonomy( $target_taxonomy );
				}
			} else {
				$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
			}

			foreach ( $taxonomies as $slug => $tax_obj ) {
				if ( 'post_format' === $slug ) {
					continue;
				}
				$terms = get_terms(
					array(
						'taxonomy'   => $slug,
						'orderby'    => 'count',
						'hide_empty' => false,
						'name__like' => $search_string,
						'number'     => 30,
					)
				);

				$tax_data = array();
				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						$tax_data[] = array(
							'id'   => 'tax-' . $term->term_id,
							/* translators: %1$s: string */
							'text' => sprintf( esc_html__( '%1$s Archive - %2$s', 'elonix' ), $term->name, $tax_obj->labels->singular_name ),
						);
						$tax_data[] = array(
							'id'   => 'tax-' . $term->term_id . '-single-' . $slug,
							/* translators: %1$s: string */
							'text' => sprintf( esc_html__( 'All Singulars from %1$s (%2$s)', 'elonix' ), $term->name, $tax_obj->labels->singular_name ),
						);
					}
				}

				if ( ! empty( $tax_data ) ) {
					$results[] = array(
						'text'     => $tax_obj->labels->name,
						'children' => $tax_data,
					);
				}
			}
		}

		wp_send_json( $results );
	}

	/**
	 * Helper to restrict search query to titles only.
	 */
	public function search_only_titles( $search, $wp_query ) {
		global $wpdb;
		if ( ! empty( $search ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
			$q              = $wp_query->query_vars;
			$n              = ! empty( $q['exact'] ) ? '' : '%';
			$search_clauses = array();

			foreach ( (array) $q['search_terms'] as $term ) {
				$search_clauses[] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
			}

			if ( ! is_user_logged_in() ) {
				$search_clauses[] = "{$wpdb->posts}.post_password = ''";
			}

			$search = ' AND ' . implode( ' AND ', $search_clauses );
		}
		return $search;
	}

	/**
	 * Retrieve all available target locations selections grouped.
	 */
	public static function get_location_selections() {
		$selections = array(
			'basic'   => array(
				'label' => esc_html__( 'Basic', 'elonix' ),
				'value' => array(
					'basic-global'    => esc_html__( 'Entire Website', 'elonix' ),
					'basic-singulars' => esc_html__( 'All Singulars', 'elonix' ),
					'basic-archives'  => esc_html__( 'All Archives', 'elonix' ),
				),
			),
			'special' => array(
				'label' => esc_html__( 'Special', 'elonix' ),
				'value' => array(
					'special-front'  => esc_html__( 'Front Page', 'elonix' ),
					'special-blog'   => esc_html__( 'Blog Page', 'elonix' ),
					'special-search' => esc_html__( 'Search Page', 'elonix' ),
					'special-404'    => esc_html__( '404 Page', 'elonix' ),
				),
			),
			'pages'   => array(
				'label' => esc_html__( 'Pages', 'elonix' ),
				'value' => array(
					'page|all'      => esc_html__( 'All Pages', 'elonix' ),
					'page|specific' => esc_html__( 'Specific Pages', 'elonix' ),
				),
			),
			'posts'   => array(
				'label' => esc_html__( 'Posts', 'elonix' ),
				'value' => array(
					'post|all'              => esc_html__( 'All Posts', 'elonix' ),
					'post|specific'         => esc_html__( 'Specific Posts', 'elonix' ),
					'tax|all|category'      => esc_html__( 'All Categories', 'elonix' ),
					'tax|specific|category' => esc_html__( 'Specific Category Archive', 'elonix' ),
					'tax|all|post_tag'      => esc_html__( 'All Tags', 'elonix' ),
					'tax|specific|post_tag' => esc_html__( 'Specific Tag Archive', 'elonix' ),
				),
			),
		);

		// Auto Detect public Custom Post Types
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $post_types as $slug => $pt ) {
			if ( in_array( $slug, array( 'post', 'page', 'attachment', 'es_header', 'es_footer' ), true ) ) {
				continue;
			}
			$opt_values = array(
				/* translators: %s: Post type plural name. */
				$slug . '|all'      => sprintf( esc_html__( 'All %s', 'elonix' ), $pt->labels->name ),
				/* translators: %s: Post type plural name. */
				$slug . '|specific' => sprintf( esc_html__( 'Specific %s', 'elonix' ), $pt->labels->name ),
			);
			if ( ! empty( $pt->has_archive ) ) {
				/* translators: %s: Post type plural name. */
				$opt_values[ $slug . '|all|archive' ] = sprintf( esc_html__( 'All %s Archives', 'elonix' ), $pt->labels->name );
			}

			$selections[ $slug ] = array(
				'label' => $pt->labels->name,
				'value' => $opt_values,
			);
		}

		// Auto Detect public Taxonomies
		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		foreach ( $taxonomies as $slug => $tax ) {
			if ( in_array( $slug, array( 'category', 'post_tag', 'post_format' ), true ) ) {
				continue;
			}
			$selections[ $slug ] = array(
				'label' => $tax->labels->name,
				'value' => array(
					/* translators: %s: Taxonomy plural name. */
					'tax|all|' . $slug      => sprintf( esc_html__( 'All %s Archives', 'elonix' ), $tax->labels->name ),
					/* translators: %s: Taxonomy singular name. */
					'tax|specific|' . $slug => sprintf( esc_html__( 'Specific %s Archive', 'elonix' ), $tax->labels->singular_name ),
				),
			);
		}

		return apply_filters( 'elonix_target_locations_selections', $selections );
	}

	/**
	 * Retrieve all user roles selection list.
	 */
	public static function get_user_selections() {
		$selections = array(
			'basic' => array(
				'label' => esc_html__( 'Basic', 'elonix' ),
				'value' => array(
					'all'        => esc_html__( 'All Users', 'elonix' ),
					'logged-in'  => esc_html__( 'Logged In Users', 'elonix' ),
					'logged-out' => esc_html__( 'Logged Out Users', 'elonix' ),
				),
			),
			'roles' => array(
				'label' => esc_html__( 'User Roles', 'elonix' ),
				'value' => array(),
			),
		);

		$roles = get_editable_roles();
		foreach ( $roles as $slug => $data ) {
			$selections['roles']['value'][ $slug ] = $data['name'];
		}

		return apply_filters( 'elonix_user_roles_selections', $selections );
	}

	/**
	 * Get readable label by key.
	 */
	public static function get_location_by_key( $key ) {
		$selections = self::get_location_selections();
		foreach ( $selections as $group ) {
			if ( isset( $group['value'][ $key ] ) ) {
				return $group['value'][ $key ];
			}
		}

		if ( strpos( $key, 'post-' ) !== false ) {
			$post_id = (int) str_replace( 'post-', '', $key );
			return get_the_title( $post_id );
		}

		if ( strpos( $key, 'tax-' ) !== false ) {
			$tax_parts = explode( '-', $key );
			$tax_id    = (int) $tax_parts[1];
			$term      = get_term( $tax_id );
			if ( ! is_wp_error( $term ) && $term ) {
				$tax_obj   = get_taxonomy( $term->taxonomy );
				$tax_label = $tax_obj ? $tax_obj->labels->singular_name : ucfirst( $term->taxonomy );
				if ( isset( $tax_parts[2] ) && 'single' === $tax_parts[2] ) {
					/* translators: %1$s: string */
					return sprintf( esc_html__( 'All Singulars from %1$s (%2$s)', 'elonix' ), $term->name, $tax_label );
				}
				/* translators: %1$s: string */
				return sprintf( esc_html__( '%1$s Archive - %2$s', 'elonix' ), $term->name, $tax_label );
			}
		}

		return $key;
	}

	/**
	 * Get readable user label by key.
	 */
	public static function get_user_by_key( $key ) {
		$selections = self::get_user_selections();
		if ( isset( $selections['basic']['value'][ $key ] ) ) {
			return $selections['basic']['value'][ $key ];
		}
		if ( isset( $selections['roles']['value'][ $key ] ) ) {
			return $selections['roles']['value'][ $key ];
		}
		return $key;
	}

	/**
	 * Enqueue Select2 and dynamic meta field assets.
	 */
	public static function enqueue_assets() {
		if ( wp_script_is( 'elementor-select2', 'registered' ) ) {
			wp_enqueue_script( 'elementor-select2' );
			wp_enqueue_style( 'elementor-select2' );
		} else {
			wp_enqueue_script( 'elonix-select2', ELONIX_ACC_URL . 'assets/js/vendor/select2.min.js', array( 'jquery' ), '4.0.13', true );
			wp_enqueue_style( 'elonix-select2', ELONIX_ACC_URL . 'assets/css/vendor/select2.min.css', array(), '4.0.13' );
		}
	}

	/**
	 * Normalize old and new target rules meta formats into unified row-based array.
	 */
	public static function normalize_rules( $raw_rules ) {
		$normalized = array();

		if ( ! is_array( $raw_rules ) ) {
			return $normalized;
		}

		// Check if it's the old/current format: array( 'rule' => array(...), 'specific' => array(...) )
		if ( isset( $raw_rules['rule'] ) && is_array( $raw_rules['rule'] ) ) {
			$rules     = $raw_rules['rule'];
			$specifics = isset( $raw_rules['specific'] ) && is_array( $raw_rules['specific'] ) ? $raw_rules['specific'] : array();

			foreach ( $rules as $idx => $rule ) {
				if ( empty( $rule ) ) {
					continue;
				}

				// Map old 'specifics' rule back to the correct specific type rule
				$row = array(
					'rule' => ( 'specifics' === $rule ) ? 'page|specific' : $rule,
				);

				if ( 'specifics' === $rule || strpos( $rule, 'specific' ) !== false ) {
					$row['specific'] = $specifics;
				}

				$normalized[] = $row;
			}
			return $normalized;
		}

		// Otherwise, it should be the new format: array( array( 'rule' => ..., 'specific' => ... ) )
		foreach ( $raw_rules as $row ) {
			if ( ! is_array( $row ) || empty( $row['rule'] ) ) {
				continue;
			}
			$normalized[] = array(
				'rule'     => sanitize_text_field( $row['rule'] ),
				'specific' => isset( $row['specific'] ) && is_array( $row['specific'] ) ? array_map( 'sanitize_text_field', $row['specific'] ) : array(),
			);
		}

		return $normalized;
	}

	/**
	 * Renders dynamic locations assignment table.
	 */
	public static function render_location_rule_fields( $name, $value = array() ) {
		$selections = self::get_location_selections();
		$normalized = self::normalize_rules( $value );

		if ( empty( $normalized ) ) {
			$normalized = array(
				array(
					'rule'     => '',
					'specific' => array(),
				),
			);
		}

		$rule_type = ( strpos( $name, 'exclude' ) !== false ) ? 'exclude' : 'include';
		$add_label = ( 'exclude' === $rule_type ) ? esc_html__( 'Add Exclusion Rule', 'elonix' ) : esc_html__( 'Add Display Rule', 'elonix' );

		?>
		<div class="es-target-rules-wrapper" data-type="<?php echo esc_attr( $rule_type ); ?>" data-name="<?php echo esc_attr( $name ); ?>">
			<div class="es-rules-rows-container">
				<?php
				foreach ( $normalized as $index => $row_val ) :
					$rule_val = isset( $row_val['rule'] ) ? $row_val['rule'] : '';
					?>
					<div class="es-rule-row-item" data-index="<?php echo (int) $index; ?>" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:10px;">
						<div class="es-select-rule-wrapper">
							<select name="<?php echo esc_attr( $name ); ?>[<?php echo (int) $index; ?>][rule]" class="es-rule-condition-select" style="min-width: 220px; height:36px; border-radius:6px;">
								<option value=""><?php esc_html_e( '-- Select Location --', 'elonix' ); ?></option>
								<?php foreach ( $selections as $grp_slug => $grp ) : ?>
									<optgroup label="<?php echo esc_attr( $grp['label'] ); ?>">
										<?php foreach ( $grp['value'] as $k => $v ) : ?>
											<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $rule_val, $k ); ?>><?php echo esc_html( $v ); ?></option>
										<?php endforeach; ?>
									</optgroup>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="es-specific-search-wrapper" style="<?php echo ( strpos( $rule_val, 'specific' ) !== false ) ? 'display:block;' : 'display:none;'; ?> min-width: 260px;">
							<select name="<?php echo esc_attr( $name ); ?>[<?php echo (int) $index; ?>][specific][]" class="es-select2-ajax-search" multiple="multiple" style="width: 100%;">
								<?php
								if ( strpos( $rule_val, 'specific' ) !== false && isset( $row_val['specific'] ) && is_array( $row_val['specific'] ) ) {
									foreach ( $row_val['specific'] as $spec_val ) {
										echo '<option value="' . esc_attr( $spec_val ) . '" selected="selected">' . esc_html( self::get_location_by_key( $spec_val ) ) . '</option>';
									}
								}
								?>
							</select>
						</div>

						<span class="es-delete-rule-row dashicons dashicons-dismiss" style="cursor:pointer; color:#ef4444; font-size:20px; margin-top:2px;"></span>
					</div>
				<?php endforeach; ?>
			</div>
			
			<div style="margin-top:12px; display:flex; gap:10px;">
				<button type="button" class="button es-add-rule-row-btn" style="background:#ffffff; color:#334155; border-color:#cbd5e1;"><?php echo esc_html( $add_label ); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders dynamic user role assignment field.
	 */
	public static function render_user_rule_fields( $name, $value = array() ) {
		$selections = self::get_user_selections();

		if ( ! is_array( $value ) || empty( $value ) ) {
			$value = array( '' );
		}
		?>
		<div class="es-user-rules-wrapper" data-name="<?php echo esc_attr( $name ); ?>">
			<div class="es-user-rows-container">
				<?php foreach ( $value as $index => $role_val ) : ?>
					<div class="es-user-row-item" data-index="<?php echo (int) $index; ?>" style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
						<select name="<?php echo esc_attr( $name ); ?>[<?php echo (int) $index; ?>]" style="min-width: 220px; height:36px; border-radius:6px;">
							<option value=""><?php esc_html_e( '-- Select User Audience --', 'elonix' ); ?></option>
							<?php foreach ( $selections as $grp_slug => $grp ) : ?>
								<optgroup label="<?php echo esc_attr( $grp['label'] ); ?>">
									<?php foreach ( $grp['value'] as $k => $v ) : ?>
										<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $role_val, $k ); ?>><?php echo esc_html( $v ); ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php endforeach; ?>
						</select>
						<span class="es-delete-user-row dashicons dashicons-dismiss" style="cursor:pointer; color:#ef4444; font-size:20px;"></span>
					</div>
				<?php endforeach; ?>
			</div>

			<div style="margin-top:12px;">
				<button type="button" class="button es-add-user-row-btn" style="background:#ffffff; color:#334155; border-color:#cbd5e1;"><?php esc_html_e( 'Add User Audience Rule', 'elonix' ); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Formats rules array for saving into metadata.
	 */
	public static function format_rule_meta_value( $post_data, $name ) {
		$value = array();

		if ( isset( $post_data[ $name ] ) && is_array( $post_data[ $name ] ) ) {
			foreach ( $post_data[ $name ] as $idx => $row ) {
				if ( empty( $row['rule'] ) ) {
					continue;
				}

				$formatted_row = array(
					'rule' => sanitize_text_field( $row['rule'] ),
				);

				if ( isset( $row['specific'] ) && is_array( $row['specific'] ) ) {
					$formatted_row['specific'] = array_map( 'sanitize_text_field', $row['specific'] );
				} else {
					$formatted_row['specific'] = array();
				}

				$value[] = $formatted_row;
			}
		}

		return $value;
	}
}
