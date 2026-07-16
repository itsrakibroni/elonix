<?php
/**
 * Elonix – Toolkit for Elementor Diagnostics & System Info Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_System_Info {

	/**
	 * Retrieve WordPress Environment Information.
	 *
	 * @return array WP environment data.
	 */
	public static function get_wordpress_info() {
		global $wp_version;

		return array(
			'version'    => $wp_version,
			'site_url'   => site_url(),
			'home_url'   => home_url(),
			'multisite'  => is_multisite() ? esc_html__( 'Yes', 'elonix' ) : esc_html__( 'No', 'elonix' ),
			'debug_mode' => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? esc_html__( 'Enabled', 'elonix' ) : esc_html__( 'Disabled', 'elonix' ),
			'locale'     => get_locale(),
		);
	}

	/**
	 * Retrieve Server Environment Information.
	 *
	 * @return array Server environment data.
	 */
	public static function get_server_info() {
		return array(
			'php_version'        => phpversion(),
			'memory_limit'       => ini_get( 'memory_limit' ),
			'max_execution_time' => ini_get( 'max_execution_time' ) . 's',
			'upload_max_size'    => ini_get( 'upload_max_filesize' ),
			'post_max_size'      => ini_get( 'post_max_size' ),
			'max_input_vars'     => ini_get( 'max_input_vars' ),
			'server_software'    => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : esc_html__( 'Unknown', 'elonix' ),
		);
	}

	/**
	 * Retrieve Plugin & Theme Environment Information.
	 *
	 * @return array Plugin and theme environment data.
	 */
	public static function get_plugin_info() {
		$theme = wp_get_theme();

		return array(
			'toolkit_version'   => ELONIX_VERSION,
			'elementor_version' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : esc_html__( 'Not Installed', 'elonix' ),
			'elementor_pro'     => defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : esc_html__( 'Not Installed', 'elonix' ),
			'theme_name'        => $theme->get( 'Name' ),
			'theme_version'     => $theme->get( 'Version' ),
		);
	}

	/**
	 * Retrieve Database Information.
	 *
	 * @return array Database info.
	 */
	public static function get_database_info() {
		global $wpdb;

		return array(
			'db_version'   => $wpdb->db_version(),
			'prefix'       => $wpdb->prefix,
			'wp_mem_limit' => defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : esc_html__( 'Not Defined', 'elonix' ),
		);
	}

	/**
	 * Retrieve Active Plugins list.
	 *
	 * @return array Active plugins data.
	 */
	public static function get_active_plugins() {
		$active_plugin_files = get_option( 'active_plugins', array() );
		$plugins             = array();

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();

		foreach ( $active_plugin_files as $file ) {
			if ( isset( $all_plugins[ $file ] ) ) {
				$plugins[] = array(
					'name'    => $all_plugins[ $file ]['Name'],
					'version' => $all_plugins[ $file ]['Version'],
					'status'  => esc_html__( 'Active', 'elonix' ),
				);
			}
		}

		return $plugins;
	}

	/**
	 * Generate a formatted plain-text system diagnostics report.
	 *
	 * @return string Plain-text diagnostics report.
	 */
	public static function generate_report() {
		$wp      = self::get_wordpress_info();
		$server  = self::get_server_info();
		$plugins = self::get_plugin_info();
		$db      = self::get_database_info();
		$active  = self::get_active_plugins();

		$report  = "=== Elonix – Toolkit for Elementor Diagnostics ===\n";
		$report .= 'Generated: ' . gmdate( 'Y-m-d H:i:s' ) . " UTC\n\n";

		$report .= "-- WordPress Environment --\n";
		$report .= 'WordPress Version: ' . $wp['version'] . "\n";
		$report .= 'Site URL:          ' . $wp['site_url'] . "\n";
		$report .= 'Home URL:          ' . $wp['home_url'] . "\n";
		$report .= 'Multisite:         ' . $wp['multisite'] . "\n";
		$report .= 'Debug Mode:        ' . $wp['debug_mode'] . "\n";
		$report .= 'Language:          ' . $wp['locale'] . "\n\n";

		$report .= "-- Server Environment --\n";
		$report .= 'PHP Version:        ' . $server['php_version'] . "\n";
		$report .= 'PHP Memory Limit:   ' . $server['memory_limit'] . "\n";
		$report .= 'Max Execution Time: ' . $server['max_execution_time'] . "\n";
		$report .= 'Upload Max Size:    ' . $server['upload_max_size'] . "\n";
		$report .= 'Post Max Size:      ' . $server['post_max_size'] . "\n";
		$report .= 'Max Input Vars:     ' . $server['max_input_vars'] . "\n";
		$report .= 'Server Software:    ' . $server['server_software'] . "\n\n";

		$report .= "-- Plugin Environment --\n";
		$report .= 'Elonix Version: ' . $plugins['toolkit_version'] . "\n";
		$report .= 'Elementor Version:  ' . $plugins['elementor_version'] . "\n";
		$report .= 'Elementor Pro:      ' . $plugins['elementor_pro'] . "\n";
		$report .= 'Active Theme:       ' . $plugins['theme_name'] . ' (v' . $plugins['theme_version'] . ")\n\n";

		$report .= "-- Database Information --\n";
		$report .= 'Database Version:   ' . $db['db_version'] . "\n";
		$report .= 'Database Prefix:    ' . $db['prefix'] . "\n";
		$report .= 'WP Memory Limit:    ' . $db['wp_mem_limit'] . "\n\n";

		$report .= "-- Active Plugins --\n";
		foreach ( $active as $item ) {
			$report .= '- ' . $item['name'] . ' (v' . $item['version'] . ")\n";
		}

		return $report;
	}
}
