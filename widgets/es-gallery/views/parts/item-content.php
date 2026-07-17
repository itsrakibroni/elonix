<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

$has_title    = 'yes' === $settings['show_title'] && ! empty( $item['title'] );
$has_subtitle = 'yes' === $settings['show_subtitle'] && ! empty( $item['subtitle'] );
$has_desc     = 'yes' === $settings['show_description'] && ! empty( $item['description'] );
$has_cat      = 'yes' === $settings['show_category'] && ! empty( $item['category'] );

if ( ! $has_title && ! $has_subtitle && ! $has_desc && ! $has_cat ) {
	return;
}
?>
<figcaption class="es-gallery__content">
	<div class="es-gallery__content-inner">
		<?php if ( $has_cat ) : ?>
			<span class="es-gallery__category"><?php echo esc_html( $item['category'] ); ?></span>
		<?php endif; ?>

		<?php if ( $has_subtitle ) : ?>
			<h5 class="es-gallery__subtitle"><?php echo wp_kses_post( $item['subtitle'] ); ?></h5>
		<?php endif; ?>

		<?php if ( $has_title ) : ?>
			<h3 class="es-gallery__title"><?php echo wp_kses_post( $item['title'] ); ?></h3>
		<?php endif; ?>

		<?php if ( $has_desc ) : ?>
			<div class="es-gallery__description"><?php echo wp_kses_post( $item['description'] ); ?></div>
		<?php endif; ?>
	</div>
</figcaption>
