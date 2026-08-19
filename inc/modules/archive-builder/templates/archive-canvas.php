<?php
/**
 * Elonix Archive Builder Template Canvas
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'elonix-canvas', ELONIX_ACC_URL . 'assets/css/canvas.css', array(), ELONIX_VERSION );
	wp_enqueue_script( 'elonix-canvas', ELONIX_ACC_URL . 'assets/js/canvas.js', array(), ELONIX_VERSION, true );

	$es_archive_canvas_css = '
		.es-archive-canvas-content {
			width: 100% !important;
			max-width: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
		}
		/* Break out of any width-constrained parent containers dynamically */
		.es-archive-parent-fluid {
			width: 100% !important;
			max-width: 100% !important;
			margin-left: 0 !important;
			margin-right: 0 !important;
			padding-left: 0 !important;
			padding-right: 0 !important;
		}
	';
	wp_add_inline_style( 'elonix-canvas', $es_archive_canvas_css );
} );

$template_id = get_query_var( 'es_matched_archive_id' );

// Safety fallback
if ( ! $template_id ) {
	$fallback_template = get_query_template( 'index' );
	if ( $fallback_template && file_exists( $fallback_template ) ) {
		include $fallback_template;
	}
	return;
}

$show_header = get_post_meta( $template_id, '_es_archive_show_header', true ) !== 'no';
$show_footer = get_post_meta( $template_id, '_es_archive_show_footer', true ) !== 'no';

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
?>

<main id="es-archive-primary" class="es-archive-canvas-content es-builder-container elementor-template-full-width" role="main" aria-label="<?php esc_attr_e( 'Archive Content', 'elonix' ); ?>">
	<?php
	// Print the Elementor custom layout template content
	echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor's own rendering API; escaping is handled internally by Elementor per-widget.
	?>
</main>

<?php
if ( $show_footer ) {
	get_footer();
} else {
	wp_footer();
	?>
	</body>
	</html>
	<?php
}
