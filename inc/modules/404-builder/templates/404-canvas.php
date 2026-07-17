<?php
/**
 * Elonix – Toolkit for Elementor Advanced 404 Builder Template Canvas
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

// Canvas Loader check using the central helper
$es_404_builder   = class_exists( 'Elonix_Toolkit_404_Builder' ) ? Elonix_Toolkit_404_Builder::instance() : null;
$es_should_render = $es_404_builder && $es_404_builder->router && method_exists( $es_404_builder->router, 'should_render_404_template' ) && $es_404_builder->router->should_render_404_template();

if ( ! $es_should_render ) {
	// If loaded outside the router rendering logic, fall back to index theme layout
	$fallback_template = get_query_template( 'index' );
	if ( $fallback_template && file_exists( $fallback_template ) ) {
		include $fallback_template;
	}
	return;
}

$show_header = ( 'yes' === ( Elonix_Settings::get( 'es_404_show_header' ) ?? 'yes' ) );
$show_footer = ( 'yes' === ( Elonix_Settings::get( 'es_404_show_footer' ) ?? 'yes' ) );

if ( $show_header ) {
	get_header();
} else {
	?>
	<!doctype html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="profile" href="https://gmpg.org/xfn/11">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<?php
}

// Custom head script blocks injection
$custom_header_code = Elonix_Settings::get( 'es_404_custom_header_code' ) ?? '';
if ( ! empty( $custom_header_code ) ) {
	echo wp_kses_post( $custom_header_code );
}

// Render the selected Elementor document content
$template_id = Elonix_Settings::get( 'es_404_selected_page_id' );
if ( $template_id ) {
	echo '<main class="es-404-page-content es-builder-container" role="main">';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id );
	echo '</main>';
}

// Custom foot script blocks injection
$custom_footer_code = Elonix_Settings::get( 'es_404_custom_footer_code' ) ?? '';
if ( ! empty( $custom_footer_code ) ) {
	echo wp_kses_post( $custom_footer_code );
}

if ( $show_footer ) {
	get_footer();
} else {
	wp_footer();
	?>
	</body>
	</html>
	<?php
}
