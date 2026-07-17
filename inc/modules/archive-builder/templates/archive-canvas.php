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
<style type="text/css">
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
		float: none !important;
		flex: none !important;
	}
	/* Browser fallback using CSS :has selector */
	div:has(> .es-archive-canvas-content),
	section:has(> .es-archive-canvas-content),
	article:has(> .es-archive-canvas-content),
	.site-content:has(.es-archive-canvas-content),
	.content-area:has(.es-archive-canvas-content),
	.container:has(.es-archive-canvas-content),
	#content:has(.es-archive-canvas-content) {
		width: 100% !important;
		max-width: 100% !important;
		margin-left: 0 !important;
		margin-right: 0 !important;
		padding-left: 0 !important;
		padding-right: 0 !important;
		float: none !important;
	}
</style>

<main id="es-archive-primary" class="es-archive-canvas-content es-builder-container elementor-template-full-width" role="main" aria-label="<?php esc_attr_e( 'Archive Content', 'elonix' ); ?>">
	<?php
	// Print the Elementor custom layout template content
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id );
	?>
</main>

<script type="text/javascript">
	(function() {
		function makeParentsFluid() {
			var canvas = document.getElementById('es-archive-primary');
			if (canvas) {
				var parent = canvas.parentElement;
				while (parent && parent.tagName !== 'BODY' && parent.tagName !== 'HTML') {
					if (parent.id === 'page' || parent.classList.contains('site') || parent.classList.contains('page-wrapper')) {
						break;
					}
					parent.classList.add('es-archive-parent-fluid');
					parent = parent.parentElement;
				}
			}
		}
		// Run immediately
		makeParentsFluid();
		// Also run on DOMContentLoaded for complete safety
		document.addEventListener('DOMContentLoaded', makeParentsFluid);
	})();
</script>

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
