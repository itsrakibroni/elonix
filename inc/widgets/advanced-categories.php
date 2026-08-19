<?php
/**
 * Elonix Advanced Categories Widget
 *
 * @package Elonix_Toolkit
 * @author  Elonix
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Custom Walker Class (Controls the HTML structure safely for Lists)
if ( ! class_exists( 'Elonix_Category_Walker' ) ) {
	class Elonix_Category_Walker extends Walker_Category {

		public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
			$cat_name = esc_attr( $category->name );
			$link     = '<a href="' . esc_url( get_term_link( $category ) ) . '" ';

			if ( $args['use_desc_for_title'] && ! empty( $category->description ) ) {
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress Core hook
				$link .= 'title="' . esc_attr( wp_strip_all_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';
			}

			$link .= '>';
			$link .= esc_html( $cat_name );

			// Display Post Count
			if ( ! empty( $args['show_count'] ) ) {
				$link .= ' <span class="count">(' . intval( $category->count ) . ')</span>';
			}

			// Add Arrow Icon
			$link .= ' <i class="fas fa-arrow-right"></i>';
			$link .= '</a>';

			// Logic for Active/Current Class
			$current_object_id = get_queried_object_id();
			$classes           = 'cat-item cat-item-' . $category->term_id;

			if ( $current_object_id === $category->term_id ) {
				$classes .= ' current-cat current-menu-item';
			}

			if ( ! empty( $args['has_children'] ) ) {
				$classes .= ' cat-parent';
			}

			$output .= "<li class='" . esc_attr( $classes ) . "'>";
			$output .= $link;
		}
	}
}

// 2. Main Widget Class
if ( ! class_exists( 'Elonix_Advanced_Categories' ) ) {
	class Elonix_Advanced_Categories extends WP_Widget {

		public function __construct() {
			$widget_ops = array(
				'classname'   => 'elonix_advanced_categories',
				'description' => esc_html__( 'Display categories or tags with advanced styles (List or Cloud).', 'elonix' ),
			);
			parent::__construct( 'elonix_advanced_categories', esc_html__( 'Elonix: Advanced Categories', 'elonix' ), $widget_ops );
		}

		public function widget( $args, $instance ) {
			// Defaults
			$title         = ! empty( $instance['title'] ) ? $instance['title'] : '';
			$taxonomy      = ! empty( $instance['taxonomy'] ) ? $instance['taxonomy'] : 'category';
			$display_style = ! empty( $instance['display_style'] ) ? $instance['display_style'] : 'list';
			$orderby       = ! empty( $instance['orderby'] ) ? $instance['orderby'] : 'name';
			$order         = ! empty( $instance['order'] ) ? $instance['order'] : 'ASC';
			$limit         = ! empty( $instance['limit'] ) ? intval( $instance['limit'] ) : 0;
			$show_count    = ! empty( $instance['show_count'] ) ? true : false;
			$hierarchical  = ! empty( $instance['hierarchical'] ) ? true : false;
			$hide_empty    = ! empty( $instance['hide_empty'] ) ? true : false;

			/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
			$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

			// Output Widget Wrapper
			echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- standard WP_Widget wrapper markup, supplied by the theme's register_sidebar(), not user input.

			if ( $title ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- before/after_title are standard WP_Widget theme wrapper markup; $title is esc_html()'d.
			}

			echo '<div class="widget-box">';

			// --- RENDER LOGIC ---
			if ( 'cloud' === $display_style ) {
				// === TAG CLOUD STYLE ===
				?>
				<div class="sidebar-tag-list">
					<?php
					$cloud_args = array(
						'taxonomy'   => $taxonomy,
						'echo'       => false,
						'orderby'    => $orderby,
						'order'      => $order,
						'number'     => ( $limit > 0 ) ? $limit : 0,
						'show_count' => $show_count,
						'format'     => 'flat', // Required for standard div structure
						'separator'  => "\n",
					);

					$tag_cloud_html = wp_tag_cloud( $cloud_args );

					// Allowed HTML for wp_tag_cloud (Standard WP security practice)
					echo wp_kses(
						$tag_cloud_html,
						array(
							'a'    => array(
								'href'       => array(),
								'class'      => array(),
								'style'      => array(), // Inline styles are standard for tag clouds (font-size)
								'aria-label' => array(),
								'title'      => array(),
							),
							'span' => array(
								'class' => array(),
							),
							'div'  => array(
								'class' => array(),
							),
						)
					);
					?>
				</div>
				<?php
			} else {
				// === LIST STYLE (Default) ===
				?>
				<div class="sidebar-service-list">
					<ul class="<?php echo $hierarchical ? esc_attr( 'children-enabled' ) : ''; ?>">
						<?php
						$cat_args = array(
							'taxonomy'     => $taxonomy,
							'orderby'      => $orderby,
							'order'        => $order,
							'show_count'   => $show_count,
							'hierarchical' => $hierarchical,
							'hide_empty'   => $hide_empty,
							'title_li'     => '',
							'walker'       => new Elonix_Category_Walker(),
						);

						if ( $limit > 0 ) {
							$cat_args['number'] = $limit;
						}

						wp_list_categories( $cat_args );
						?>
					</ul>
				</div>
				<?php
			}

			echo '</div>'; // End .widget-box

			echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- standard WP_Widget wrapper markup, supplied by the theme's register_sidebar(), not user input.
		}

		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			// Sanitize Inputs (Crucial for ThemeForest)
			$instance['title']         = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
			$instance['taxonomy']      = isset( $new_instance['taxonomy'] ) ? sanitize_key( $new_instance['taxonomy'] ) : 'category';
			$instance['display_style'] = isset( $new_instance['display_style'] ) ? sanitize_key( $new_instance['display_style'] ) : 'list';
			$instance['orderby']       = isset( $new_instance['orderby'] ) ? sanitize_key( $new_instance['orderby'] ) : 'name';
			$instance['order']         = isset( $new_instance['order'] ) ? sanitize_key( $new_instance['order'] ) : 'ASC';
			$instance['limit']         = isset( $new_instance['limit'] ) ? intval( $new_instance['limit'] ) : 0;

			// Checkboxes
			$instance['show_count']   = ! empty( $new_instance['show_count'] ) ? 1 : 0;
			$instance['hierarchical'] = ! empty( $new_instance['hierarchical'] ) ? 1 : 0;
			$instance['hide_empty']   = ! empty( $new_instance['hide_empty'] ) ? 1 : 0;

			return $instance;
		}

		public function form( $instance ) {
			$defaults = array(
				'title'         => esc_html__( 'Categories', 'elonix' ),
				'taxonomy'      => 'category',
				'display_style' => 'list',
				'orderby'       => 'name',
				'order'         => 'ASC',
				'limit'         => '',
				'show_count'    => 0,
				'hierarchical'  => 0,
				'hide_empty'    => 1,
			);
			$instance = wp_parse_args( (array) $instance, $defaults );

			// Get Taxonomies
			$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
			?>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'elonix' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>"><?php esc_html_e( 'Select Source (Taxonomy):', 'elonix' ); ?></label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'taxonomy' ) ); ?>">
					<?php foreach ( $taxonomies as $tax ) : ?>
						<option value="<?php echo esc_attr( $tax->name ); ?>" <?php selected( $instance['taxonomy'], $tax->name ); ?>>
							<?php echo esc_html( $tax->label ) . ' (' . esc_html( $tax->name ) . ')'; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>" style="font-weight:bold; color: #d63638;"><?php esc_html_e( 'Display Style:', 'elonix' ); ?></label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_style' ) ); ?>">
					<option value="list" <?php selected( $instance['display_style'], 'list' ); ?>><?php esc_html_e( 'List View (Default for Categories)', 'elonix' ); ?></option>
					<option value="cloud" <?php selected( $instance['display_style'], 'cloud' ); ?>><?php esc_html_e( 'Tag Cloud (Best for Tags)', 'elonix' ); ?></option>
				</select>
				<small><?php esc_html_e( 'Select "Tag Cloud" to use the sidebar tag design.', 'elonix' ); ?></small>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php esc_html_e( 'Order By:', 'elonix' ); ?></label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
					<option value="name" <?php selected( $instance['orderby'], 'name' ); ?>><?php esc_html_e( 'Name (A-Z)', 'elonix' ); ?></option>
					<option value="count" <?php selected( $instance['orderby'], 'count' ); ?>><?php esc_html_e( 'Post Count', 'elonix' ); ?></option>
					<option value="id" <?php selected( $instance['orderby'], 'id' ); ?>><?php esc_html_e( 'ID (Created Date)', 'elonix' ); ?></option>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"><?php esc_html_e( 'Order Direction:', 'elonix' ); ?></label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
					<option value="ASC" <?php selected( $instance['order'], 'ASC' ); ?>><?php esc_html_e( 'Ascending', 'elonix' ); ?></option>
					<option value="DESC" <?php selected( $instance['order'], 'DESC' ); ?>><?php esc_html_e( 'Descending', 'elonix' ); ?></option>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Number of items:', 'elonix' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number" min="0" step="1" value="<?php echo esc_attr( $instance['limit'] ); ?>" />
			</p>

			<p>
				<input class="checkbox" type="checkbox" <?php checked( $instance['show_count'], 1 ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>" />
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"><?php esc_html_e( 'Show Counts', 'elonix' ); ?></label>
				<br/>

				<input class="checkbox" type="checkbox" <?php checked( $instance['hierarchical'], 1 ); ?> id="<?php echo esc_attr( $this->get_field_id( 'hierarchical' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hierarchical' ) ); ?>" />
				<label for="<?php echo esc_attr( $this->get_field_id( 'hierarchical' ) ); ?>"><?php esc_html_e( 'Show Hierarchy (List only)', 'elonix' ); ?></label>
				<br/>

				<input class="checkbox" type="checkbox" <?php checked( $instance['hide_empty'], 1 ); ?> id="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_empty' ) ); ?>" />
				<label for="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>"><?php esc_html_e( 'Hide Empty', 'elonix' ); ?></label>
			</p>

			<?php
		}
	}
}

// Register Widget
function elonix_register_advanced_categories() {
	register_widget( 'Elonix_Advanced_Categories' );
}
add_action( 'widgets_init', 'elonix_register_advanced_categories' );
