<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

?>
<div class="es-gallery__badge">
	<span class="es-gallery__badge-text"><?php echo esc_html( $item['badge'] ); ?></span>
</div>
