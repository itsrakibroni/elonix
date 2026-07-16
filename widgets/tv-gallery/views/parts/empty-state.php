<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals
?>
<div class="tv-gallery__empty">
	<p><?php esc_html_e( 'No gallery items found.', 'elonix' ); ?></p>
</div>
