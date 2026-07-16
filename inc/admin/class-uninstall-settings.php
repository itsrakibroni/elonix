<?php
/**
 * Elonix – Toolkit for Elementor Uninstall Settings Handler
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Uninstall_Settings {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Hook to enqueue deletion modal script on the plugins admin page.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_delete_modal_script' ) );

		// AJAX handler to save the data deletion preference before uninstalling.
		add_action( 'wp_ajax_elonix_set_uninstall_pref', array( $this, 'set_uninstall_pref' ) );
	}

	/**
	 * Enqueue deletion modal script on the plugins admin page.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_delete_modal_script( $hook ) {
		if ( 'plugins.php' !== $hook ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'plugins' !== $screen->id ) {
			return;
		}

		wp_enqueue_style( 'elonix-notifications-css' );
		wp_enqueue_script( 'elonix-notifications-js' );

		wp_enqueue_script(
			'elonix-delete-modal',
			ELONIX_ACC_URL . 'inc/admin/delete-modal.js',
			array( 'jquery', 'updates' ),
			ELONIX_VERSION,
			true
		);

		wp_localize_script(
			'elonix-delete-modal',
			'elonixDeleteOpts',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'elonix_delete_nonce' ),
				'plugin'   => 'elonix/elonix.php',
				'slug'     => 'elonix',
			)
		);
	}

	/**
	 * AJAX handler to save the data deletion preference before uninstalling.
	 */
	public function set_uninstall_pref() {
		check_ajax_referer( 'elonix_delete_nonce', 'nonce' );

		if ( ! current_user_can( 'delete_plugins' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'elonix' ) ), 403 );
		}

		$remove_data = isset( $_POST['remove_data'] ) && 'true' === $_POST['remove_data'];

		$settings = get_option( 'elonix_settings', array() );
		if ( ! isset( $settings['uninstall'] ) ) {
			$settings['uninstall'] = array();
		}
		$settings['uninstall']['remove_data_on_uninstall'] = $remove_data ? '1' : '0';
		update_option( 'elonix_settings', $settings );

		wp_send_json_success();
	}
}
