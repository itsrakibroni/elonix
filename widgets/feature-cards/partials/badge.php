<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

if ( empty( $item['badge_text'] ) ) {
	return;
}
?>
<div class="es-fc-badge">
	<span class="es-fc-badge-text"><?php echo esc_html( $item['badge_text'] ); ?></span>
</div>
