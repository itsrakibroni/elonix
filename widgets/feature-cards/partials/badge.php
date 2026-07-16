<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

if ( empty( $item['badge_text'] ) ) {
	return;
}
?>
<div class="tv-fc-badge">
	<span class="tv-fc-badge-text"><?php echo esc_html( $item['badge_text'] ); ?></span>
</div>
