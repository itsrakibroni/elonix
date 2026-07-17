<?php
/**
 * Elonix – Toolkit for Elementor Nav Menu Walker Class
 *
 * Custom WordPress Nav Menu Walker with dropdown support, dynamic badges,
 * active state triggers, and extensible Mega Menu hook points.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Nav_Menu_Walker extends Walker_Nav_Menu {

	/**
	 * Submenu Indicator Icon HTML.
	 *
	 * @var string
	 */
	protected $indicator_icon = '';

	/**
	 * Custom Badge Config.
	 *
	 * @var array
	 */
	protected $badge_config = array();

	/**
	 * Constructor to pass custom indicators.
	 *
	 * @param string $indicator_icon Indicator HTML wrapper.
	 * @param array  $badge_config   Custom badge configs.
	 */
	public function __construct( $indicator_icon = '', $badge_config = array() ) {
		$this->indicator_icon = $indicator_icon;
		$this->badge_config   = $badge_config;
	}

	/**
	 * Start level (render submenu wrapper block).
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of page. Used for padding.
	 * @param array  $args   An array of arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent  = str_repeat( "\t", $depth );
		$classes = array( 'es-dropdown', 'es-submenu-panel' );

		/**
		 * Filter submenu classes.
		 */
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress Core hook.
		$class_names = join( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$output .= "\n$indent<ul$class_names>\n";
	}

	/**
	 * Start element (render menu item li/a link triggers).
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments.
	 * @param int    $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$indent  = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		if ( in_array( 'menu-item-has-children', $classes, true ) ) {
			$classes[] = 'es-dropdown-has';
		}

		/**
		 * Filter CSS classes.
		 */
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress Core hook
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress Core hook.
		$item_id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$item_id = $item_id ? ' id="' . esc_attr( $item_id ) . '"' : '';

		$output .= $indent . '<li' . $item_id . $class_names . '>';

		// Build element attribute link dictionary
		$atts           = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
		$atts['href']   = ! empty( $item->url ) ? $item->url : '';

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress Core hook.
		$atts       = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );
		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
			}
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress Core hook
		$title = apply_filters( 'the_title', $item->title, $item->ID );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress Core hook
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		// Scan for badge settings from menu item meta, with backward compatibility fallback for CSS class badges
		$badge_html     = '';
		$badge_enabled  = isset( $item->es_badge_enabled ) ? $item->es_badge_enabled : '';
		$badge_type     = isset( $item->es_badge_type ) ? $item->es_badge_type : 'none';
		$badge_text     = isset( $item->es_badge_text ) ? $item->es_badge_text : '';
		$badge_color    = isset( $item->es_badge_color ) ? $item->es_badge_color : '';
		$badge_bg       = isset( $item->es_badge_bg ) ? $item->es_badge_bg : '';
		$badge_position = isset( $item->es_badge_position ) ? $item->es_badge_position : 'top-right';
		$badge_offset_x = isset( $item->es_badge_offset_x ) ? $item->es_badge_offset_x : '';
		$badge_offset_y = isset( $item->es_badge_offset_y ) ? $item->es_badge_offset_y : '';

		// Backward compatibility: check if class-based badge exists
		if ( 'yes' !== $badge_enabled || 'none' === $badge_type ) {
			foreach ( $classes as $class ) {
				if ( strpos( $class, 'es-badge-' ) === 0 ) {
					$badge_name    = str_replace( 'es-badge-', '', $class );
					$badge_key     = strtolower( $badge_name );
					$badge_enabled = 'yes';
					$badge_type    = in_array( $badge_key, array( 'new', 'hot', 'sale' ), true ) ? $badge_key : 'custom';
					$badge_text    = isset( $this->badge_config[ $badge_key ] ) && ! empty( $this->badge_config[ $badge_key ] ) ? $this->badge_config[ $badge_key ] : $badge_name;
					break;
				}
			}
		}

		if ( 'yes' === $badge_enabled && 'none' !== $badge_type ) {
			if ( empty( $badge_text ) ) {
				if ( 'new' === $badge_type ) {
					$badge_text = esc_html__( 'New', 'elonix' );
				} elseif ( 'hot' === $badge_type ) {
					$badge_text = esc_html__( 'Hot', 'elonix' );
				} elseif ( 'sale' === $badge_type ) {
					$badge_text = esc_html__( 'Sale', 'elonix' );
				} else {
					$badge_text = esc_html__( 'Custom', 'elonix' );
				}
			}

			$inline_styles = array();
			if ( ! empty( $badge_color ) ) {
				$inline_styles[] = 'color: ' . esc_attr( $badge_color ) . ';';
			}
			if ( ! empty( $badge_bg ) ) {
				$inline_styles[] = 'background-color: ' . esc_attr( $badge_bg ) . ';';
			}

			$badge_classes = array( 'es-menu-badge', 'es-badge-' . esc_attr( $badge_type ) );

			// Position styling presets
			if ( 'top-right' === $badge_position ) {
				$inline_styles[] = 'position: absolute; top: 0; right: 0; transform: translateY(-50%) !important;';
				$badge_classes[] = 'es-badge-pos-top-right';
			} elseif ( 'top-left' === $badge_position ) {
				$inline_styles[] = 'position: absolute; top: 0; left: 0; transform: translateY(-50%) !important;';
				$badge_classes[] = 'es-badge-pos-top-left';
			} elseif ( 'bottom-right' === $badge_position ) {
				$inline_styles[] = 'position: absolute; bottom: 0; right: 0; transform: translateY(50%) !important;';
				$badge_classes[] = 'es-badge-pos-bottom-right';
			} elseif ( 'bottom-left' === $badge_position ) {
				$inline_styles[] = 'position: absolute; bottom: 0; left: 0; transform: translateY(50%) !important;';
				$badge_classes[] = 'es-badge-pos-bottom-left';
			} else {
				$inline_styles[] = 'position: relative;';
				$badge_classes[] = 'es-badge-pos-custom';
				if ( ! empty( $badge_offset_x ) ) {
					$inline_styles[] = 'margin-left: ' . esc_attr( $badge_offset_x ) . ';';
				}
				if ( ! empty( $badge_offset_y ) ) {
					$inline_styles[] = 'transform: translateY(' . esc_attr( $badge_offset_y ) . ');';
				}
			}

			$style_attr = ! empty( $inline_styles ) ? ' style="' . implode( ' ', $inline_styles ) . '"' : '';
			$badge_html = ' <span class="' . esc_attr( implode( ' ', $badge_classes ) ) . '"' . $style_attr . '>' . esc_html( $badge_text ) . '</span>';
		}

		// Submenu carets indicators injection
		$submenu_indicator = '';
		if ( in_array( 'menu-item-has-children', $classes, true ) ) {
			if ( ! empty( $this->indicator_icon ) ) {
				$submenu_indicator = ' ' . $this->indicator_icon;
			} else {
				$submenu_indicator = ' <i class="fas fa-chevron-down es-submenu-indicator" aria-hidden="true"></i>';
			}
		}

		$item_output  = isset( $args->before ) ? $args->before : '';
		$item_output .= '<a' . $attributes . '>';
		$item_output .= ( isset( $args->link_before ) ? $args->link_before : '' ) . $title . $badge_html . $submenu_indicator . ( isset( $args->link_after ) ? $args->link_after : '' );
		$item_output .= '</a>';
		$item_output .= isset( $args->after ) ? $args->after : '';

		/**
		 * Elonix Mega Menu Hook Placeholder:
		 * Intercept standard dropdown renderer to inject customized layout templates.
		 */
		$mega_menu_html = apply_filters( 'elonix_mega_menu_content', '', $item, $depth, $args );
		if ( ! empty( $mega_menu_html ) ) {
			$item_output .= '<div class="es-megamenu-panel">' . $mega_menu_html . '</div>';
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress Core hook
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}
