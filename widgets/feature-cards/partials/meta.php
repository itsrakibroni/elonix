<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

if ( empty( $item['title'] ) && empty( $item['subtitle'] ) && empty( $item['description'] ) ) {
	return;
}
?>
<div class="es-fc-meta">
	<?php if ( ! empty( $item['subtitle'] ) ) : ?>
		<div class="es-fc-subtitle"><?php echo wp_kses_post( $item['subtitle'] ); ?></div>
	<?php endif; ?>
	
	<?php if ( ! empty( $item['title'] ) ) : ?>
		<<?php echo esc_attr( $settings['title_tag'] ); ?> class="es-fc-title">
			<?php echo wp_kses_post( $item['title'] ); ?>
		</<?php echo esc_attr( $settings['title_tag'] ); ?>>
	<?php endif; ?>
	
	<?php if ( ! empty( $item['description'] ) ) : ?>
		<div class="es-fc-description"><?php echo wp_kses_post( $item['description'] ); ?></div>
	<?php endif; ?>
</div>
