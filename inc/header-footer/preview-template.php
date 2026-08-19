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
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>

	<?php if ( $show_debug ) : ?>
		<!-- Temporary Debug Report -->
		<div class="es-preview-debug-bar">
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
	$is_header = ( 'elonix_header' === $template_type );
	if ( $is_header ) {
		echo '<header class="es-site-header">';
	}

	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor's own rendering API (get_builder_content); escaping is handled internally by Elementor per-widget.

	if ( $is_header ) {
		echo '</header>';
	}

	wp_footer();
	?>
</body>
</html>
