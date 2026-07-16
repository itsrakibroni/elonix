<?php
/**
 * Elonix Search Builder Template Canvas
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

$template_id = get_query_var( 'tv_matched_search_id' );

if ( ! $template_id ) {
	$fallback_template = get_query_template( 'search' );
	if ( $fallback_template && file_exists( $fallback_template ) ) {
		include $fallback_template;
	}
	return;
}

$show_header = get_post_meta( $template_id, '_tv_search_show_header', true ) !== 'no';
$show_footer = get_post_meta( $template_id, '_tv_search_show_footer', true ) !== 'no';

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
	.tv-search-canvas-content {
		width: 100% !important;
		max-width: 100% !important;
		margin: 0 !important;
		padding: 0 !important;
	}
	.tv-search-parent-fluid {
		width: 100% !important;
		max-width: 100% !important;
		margin-left: 0 !important;
		margin-right: 0 !important;
		padding-left: 0 !important;
		padding-right: 0 !important;
		float: none !important;
		flex: none !important;
	}
	div:has(> .tv-search-canvas-content),
	section:has(> .tv-search-canvas-content),
	article:has(> .tv-search-canvas-content),
	.site-content:has(.tv-search-canvas-content),
	.content-area:has(.tv-search-canvas-content),
	.container:has(.tv-search-canvas-content),
	#content:has(.tv-search-canvas-content) {
		width: 100% !important;
		max-width: 100% !important;
		margin-left: 0 !important;
		margin-right: 0 !important;
		padding-left: 0 !important;
		padding-right: 0 !important;
		float: none !important;
	}
</style>

<main id="tv-search-primary" class="tv-search-canvas-content tv-builder-container elementor-template-full-width" role="main" aria-label="<?php esc_attr_e( 'Search Results Content', 'elonix' ); ?>">
	<?php
	echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
</main>

<script type="text/javascript">
	(function() {
		function makeParentsFluid() {
			var canvas = document.getElementById('tv-search-primary');
			if (canvas) {
				var parent = canvas.parentElement;
				while (parent && parent.tagName !== 'BODY' && parent.tagName !== 'HTML') {
					if (parent.id === 'page' || parent.classList.contains('site') || parent.classList.contains('page-wrapper')) {
						break;
					}
					parent.classList.add('tv-search-parent-fluid');
					parent = parent.parentElement;
				}
			}
		}
		makeParentsFluid();
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
