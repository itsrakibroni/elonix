<?php
/**
 * Dedicated preview template for Elonix Header & Footer layouts.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variable.
$template_id         = get_the_ID();
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variable.
$template_type       = get_post_type( $template_id );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variable.
$content             = '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variable.
$loaded_successfully = 'no';

if ( class_exists( '\Elementor\Plugin' ) ) {
	// Add Elementor template canvas body class
	\Elementor\Plugin::$instance->frontend->add_body_class( 'elementor-template-canvas' );
	\Elementor\Plugin::$instance->frontend->add_body_class( 'elementor-page-' . $template_id );

	// Get builder content
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable used only within this preview template.
	$content = \Elementor\Plugin::$instance->frontend->get_builder_content( $template_id, true );
	if ( ! empty( $content ) ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable used only within this preview template.
		$loaded_successfully = 'yes';
	}
}

// Guarantee elementor-template-canvas class is output in body tag
add_filter(
	'body_class',
	function ( $classes ) {
		if ( ! in_array( 'elementor-template-canvas', $classes, true ) ) {
			$classes[] = 'elementor-template-canvas';
		}
		return $classes;
	}
);

// Check if Template Information debug mode is enabled
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variable.
$show_debug = ( class_exists( 'Elonix_Settings' ) && \Elonix_Settings::is_template_debug_enabled() );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
	<style>
		html, body {
			margin: 0 !important;
			padding: 0 !important;
			width: 100% !important;
			height: 100% !important;
			background: #f1f5f9 !important;
		}
		.tv-site-header {
			width: 100% !important;
		}
		<?php if ( $show_debug ) : ?>
		.tv-preview-debug-bar {
			background: #1e293b;
			color: #f1f5f9;
			padding: 10px 20px;
			font-family: Consolas, Monaco, monospace;
			font-size: 12px;
			border-bottom: 2px solid #ef4444;
			display: flex;
			gap: 20px;
			flex-wrap: wrap;
			z-index: 999999;
			position: relative;
		}
		.tv-preview-debug-bar span {
			font-weight: bold;
		}
		.tv-preview-debug-bar .success {
			color: #22c55e;
		}
		.tv-preview-debug-bar .failure {
			color: #ef4444;
		}
		<?php endif; ?>
	</style>
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>

	<?php if ( $show_debug ) : ?>
		<!-- Temporary Debug Report -->
		<div class="tv-preview-debug-bar">
			<div><span>Template ID:</span> <?php echo (int) $template_id; ?></div>
			<div><span>Template Type:</span> <?php echo esc_html( $template_type ); ?></div>
			<div><span>Rendering Method:</span> Elementor frontend get_builder_content()</div>
			<div><span>Elementor Content Loaded:</span> 
				<?php if ( 'yes' === $loaded_successfully ) : ?>
					<span class="success">YES</span>
				<?php else : ?>
					<span class="failure">NO</span>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php
	// Output content exactly as Elementor outputs it
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variable.
	$is_header = ( 'tv_header' === $template_type );
	if ( $is_header ) {
		echo '<header class="tv-site-header">';
	}

	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	if ( $is_header ) {
		echo '</header>';
	}

	wp_footer();
	?>
</body>
</html>
