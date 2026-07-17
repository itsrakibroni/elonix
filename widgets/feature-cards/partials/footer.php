<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

if ( empty( $item['footer_label'] ) ) {
	return;
}
?>
<div class="es-fc-footer">
	<span class="es-fc-footer-label"><?php echo esc_html( $item['footer_label'] ); ?></span>
</div>
