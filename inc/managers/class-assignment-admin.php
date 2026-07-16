<?php
/**
 * Elonix – Toolkit for Elementor Unified Template Assignment Admin UI
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Assignment_Admin {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$post_types = array( 'tv_header', 'tv_footer', 'tv_single', 'tv_archive', 'tv_search_template' );
		
		foreach ( $post_types as $pt ) {
			add_filter( "manage_{$pt}_posts_columns", array( $this, 'register_columns' ) );
			add_action( "manage_{$pt}_posts_custom_column", array( $this, 'render_columns' ), 10, 2 );
		}
	}

	public function register_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $title ) {
			if ( 'date' === $key ) {
				$new_columns['tv_status']     = esc_html__( 'Status', 'elonix' );
				$new_columns['tv_assignment'] = esc_html__( 'Assigned To', 'elonix' );
				$new_columns['tv_priority']   = esc_html__( 'Priority', 'elonix' );
			}
			$new_columns[ $key ] = $title;
		}
		return $new_columns;
	}

	public function render_columns( $column, $post_id ) {
		$engine = Elonix_Assignment_Engine::instance();
		$data   = $engine->get_assignment( $post_id );

		switch ( $column ) {
			case 'tv_status':
				if ( $data['active'] ) {
					echo '<span class="tv-status-badge tv-status-active">' . esc_html__( 'Active', 'elonix' ) . '</span>';
				} else {
					echo '<span class="tv-status-badge tv-status-inactive">' . esc_html__( 'Inactive', 'elonix' ) . '</span>';
				}
				break;

			case 'tv_assignment':
				if ( empty( $data['include'] ) ) {
					echo '<em>' . esc_html__( 'None', 'elonix' ) . '</em>';
				} else {
					$limit = 3;
					$count = 0;
					foreach ( $data['include'] as $rule ) {
						if ( $count >= $limit ) {
							echo '<span class="tv-assignment-more">+' . ( count( $data['include'] ) - $limit ) . ' ' . esc_html__( 'more', 'elonix' ) . '</span>';
							break;
						}
						echo '<span class="tv-assignment-tag">' . esc_html( $rule ) . '</span>';
						$count++;
					}
					if ( ! empty( $data['exclude'] ) ) {
						echo '<br><small style="color:#d63638;">' . esc_html__( 'With exclusions', 'elonix' ) . '</small>';
					}
				}
				break;

			case 'tv_priority':
				echo '<strong>' . esc_html( $data['priority'] ) . '</strong>';
				break;
		}
	}

	public function custom_row_actions( $actions, $post ) {
		$supported_types = array( 'tv_header', 'tv_footer', 'tv_single', 'tv_archive', 'tv_search_template' );
		if ( ! in_array( $post->post_type, $supported_types, true ) ) {
			return $actions;
		}

		$assign_btn = sprintf(
			'<a href="#" class="tv-open-assignment-drawer" data-id="%d" data-type="%s" style="color:#2271b1; font-weight:bold;">%s</a>',
			$post->ID,
			$post->post_type,
			esc_html__( 'Assign Template', 'elonix' )
		);

		$new_actions = array();
		foreach ( $actions as $key => $action ) {
			$new_actions[ $key ] = $action;
			if ( 'edit' === $key ) {
				$new_actions['tv_assign'] = $assign_btn;
			}
		}

		// Ensure it is injected if edit is missing
		if ( ! isset( $new_actions['tv_assign'] ) ) {
			$new_actions['tv_assign'] = $assign_btn;
		}

		return $new_actions;
	}
}
