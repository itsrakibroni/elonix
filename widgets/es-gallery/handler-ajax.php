<?php
/**
 * Elonix Gallery AJAX Handler
 *
 * Handles AJAX requests for Load More operations securely.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Gallery_AJAX {

	public function __construct() {
		add_action( 'wp_ajax_es_gallery_load_more', array( $this, 'ajax_load_more' ) );
		add_action( 'wp_ajax_nopriv_es_gallery_load_more', array( $this, 'ajax_load_more' ) );
	}

	public function ajax_load_more() {
		check_ajax_referer( 'es_gallery_ajax_nonce', 'nonce' );

		$post_id       = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$widget_id     = isset( $_POST['widget_id'] ) ? sanitize_text_field( wp_unslash( $_POST['widget_id'] ) ) : '';
		$offset        = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
		$limit         = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 3;
		$active_filter = isset( $_POST['active_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['active_filter'] ) ) : '*';

		if ( ! $post_id || empty( $widget_id ) ) {
			wp_send_json_error( 'Missing parameters.' );
		}

		$document = \Elementor\Plugin::$instance->documents->get( $post_id );
		if ( ! $document ) {
			wp_send_json_error( 'Invalid post document.' );
		}

		$elements    = $document->get_elements_data();
		$widget_data = $this->find_element_recursive( $elements, $widget_id );

		if ( ! $widget_data ) {
			wp_send_json_error( 'Widget not found.' );
		}

		$widget = \Elementor\Plugin::$instance->elements_manager->create_element_instance( $widget_data );
		if ( ! $widget ) {
			wp_send_json_error( 'Widget instantiation failed.' );
		}

		$settings = $widget->get_settings_for_display();

		// Fetch items matching the filter, then apply offset and limit.
		$gallery_items = $widget->normalize_data( $settings, $offset, $limit, $active_filter );

		// Check if there's more after this fetch to determine has_more.
		$next_offset        = $offset + count( $gallery_items );
		$all_filter_items   = $widget->normalize_data( $settings, 0, null, $active_filter );
		$total_filter_items = count( $all_filter_items );
		$remaining          = $total_filter_items - $next_offset;
		$has_more           = $remaining > 0;

		$html = '';
		if ( ! empty( $gallery_items ) ) {
			ob_start();
			foreach ( $gallery_items as $item ) {
				include __DIR__ . '/views/parts/item.php';
			}
			$html = ob_get_clean();
		}

		wp_send_json_success(
			array(
				'html'           => $html,
				'next_offset'    => $next_offset,
				'remaining'      => max( 0, $remaining ),
				'has_more'       => $has_more,
				'current_filter' => $active_filter,
			)
		);
	}

	private function find_element_recursive( $elements, $id ) {
		foreach ( $elements as $element ) {
			if ( $id === $element['id'] ) {
				return $element;
			}
			if ( ! empty( $element['elements'] ) ) {
				$found = $this->find_element_recursive( $element['elements'], $id );
				if ( $found ) {
					return $found;
				}
			}
		}
		return false;
	}
}

new Elonix_Toolkit_Gallery_AJAX();
