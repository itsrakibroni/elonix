<?php
/**
 * Elonix – Toolkit for Elementor Settings Page
 *
 * Handles module toggling configuration for performance optimization.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register settings page under WordPress Settings menu.
 */
function elonix_add_settings_menu() {
	add_options_page(
		esc_html__( 'Elonix – Toolkit for Elementor Settings', 'elonix' ),
		esc_html__( 'Elonix – Toolkit for Elementor', 'elonix' ),
		'manage_options',
		'elonix',
		'elonix_render_settings_page'
	);
}
add_action( 'admin_menu', 'elonix_add_settings_menu' );

/**
 * Register Settings, Section, and Fields.
 */
function elonix_register_settings() {
	register_setting(
		'elonix_settings_group',
		'elonix_modules',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'elonix_sanitize_modules',
			'default'           => array(
				'header_footer' => 1,
				'sections'      => 1,
				'marquee'       => 1,
				'cpts'          => 1,
			),
		)
	);

	add_settings_section(
		'elonix_modules_section',
		esc_html__( 'Module Management', 'elonix' ),
		'elonix_modules_section_callback',
		'elonix'
	);

	add_settings_field(
		'elonix_modules_field',
		esc_html__( 'Enable Modules', 'elonix' ),
		'elonix_modules_field_callback',
		'elonix',
		'elonix_modules_section'
	);

}
add_action( 'admin_init', 'elonix_register_settings' );

/**
 * Section description callback.
 */
function elonix_modules_section_callback() {
	echo '<p>' . esc_html__( 'Enable or disable specific module groups of the Elonix – Toolkit for Elementor. Disabled modules will not load their PHP classes, widgets, or related scripts and styles, optimizing your site performance.', 'elonix' ) . '</p>';
}

/**
 * Renders module checkboxes.
 */
function elonix_modules_field_callback() {
	$options = get_option( 'elonix_modules' );

	// Fallback to default if options are not set or not an array
	if ( ! is_array( $options ) ) {
		$options = array(
			'header_footer' => 1,
			'sections'      => 1,
			'marquee'       => 1,
			'cpts'          => 1,
		);
	}

	$modules = array(
		'header_footer' => esc_html__( 'Header & Footer Builder', 'elonix' ),
		'sections'      => esc_html__( 'Section / Page-Part Widgets', 'elonix' ),
		'marquee'       => esc_html__( 'Marquee Widgets', 'elonix' ),
		'cpts'          => esc_html__( 'Custom Post Types (Services/Projects/Team/Gallery)', 'elonix' ),
	);

	foreach ( $modules as $key => $label ) {
		$checked = isset( $options[ $key ] ) && $options[ $key ] ? 'checked' : '';
		echo '<div style="margin-bottom: 10px;">';
		echo '<label>';
		echo '<input type="checkbox" name="elonix_modules[' . esc_attr( $key ) . ']" value="1" ' . esc_attr( $checked ) . ' /> ';
		echo '<strong>' . esc_html( $label ) . '</strong>';
		echo '</label>';
		echo '</div>';
	}
}

/**
 * Settings Sanitization & Save Capability Handler.
 *
 * @param array $input Unsanitized option array.
 * @return array Sanitized output.
 */
function elonix_sanitize_modules( $input ) {
	// Security check for save operations
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to perform this action.', 'elonix' ) );
	}

	if ( ! is_array( $input ) ) {
		$input = array();
	}

	$legacy_keys = array( 'header_footer', 'sections', 'marquee', 'cpts' );
	$modern_keys = array();

	if ( class_exists( 'Elonix_Toolkit_Module_Manager' ) ) {
		$modern_keys = array_keys( Elonix_Toolkit_Module_Manager::get_registered_modules() );
	}

	// Detect if input contains legacy or modern keys to determine context
	$has_legacy_in_input = false;
	foreach ( $legacy_keys as $key ) {
		if ( isset( $input[ $key ] ) ) {
			$has_legacy_in_input = true;
			break;
		}
	}

	$has_modern_in_input = false;
	foreach ( $modern_keys as $key ) {
		if ( isset( $input[ $key ] ) ) {
			$has_modern_in_input = true;
			break;
		}
	}

	// Fetch existing option values from the database
	$existing = get_option( 'elonix_modules' );
	if ( ! is_array( $existing ) ) {
		$existing = array();
	}

	$output = array();

	// Sanitize legacy keys
	foreach ( $legacy_keys as $key ) {
		if ( isset( $input[ $key ] ) ) {
			$output[ $key ] = $input[ $key ] ? 1 : 0;
		} else {
			// If input contains other legacy keys, this missing one was unchecked on legacy settings page
			if ( $has_legacy_in_input ) {
				$output[ $key ] = 0;
			} else {
				// Otherwise, preserve the existing value
				$output[ $key ] = isset( $existing[ $key ] ) ? ( $existing[ $key ] ? 1 : 0 ) : 1;
			}
		}
	}

	// Sanitize modern keys
	foreach ( $modern_keys as $key ) {
		if ( isset( $input[ $key ] ) ) {
			$output[ $key ] = (bool) $input[ $key ];
		} else {
			// If input contains other modern keys, this missing one was unchecked/disabled
			if ( $has_modern_in_input ) {
				$output[ $key ] = false;
			} else {
				// Otherwise, preserve the existing value
				$output[ $key ] = isset( $existing[ $key ] ) ? (bool) $existing[ $key ] : true;
			}
		}
	}

	return $output;
}

/**
 * Render Settings Page HTML.
 */
function elonix_render_settings_page() {
	// Security check for page rendering
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to perform this action.', 'elonix' ) );
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'elonix_settings_group' );
			do_settings_sections( 'elonix' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
