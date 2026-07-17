<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

if ( empty( $item['number_text'] ) ) {
	return;
}
?>
<div class="es-fc-number">
	<span class="es-fc-number-text"><?php echo esc_html( $item['number_text'] ); ?></span>
</div>
