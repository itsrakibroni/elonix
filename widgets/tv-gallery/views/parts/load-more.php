<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

$remaining_items = count( $all_items ) - count( $gallery_items );
if ( $remaining_items <= 0 ) {
	return; // Hide button if all items are already visible
}
?>
<div class="tv-gallery__load-more-wrapper" style="display: flex;">
	<button type="button" class="tv-gallery__load-more-btn" data-loading-text="<?php echo esc_attr( $settings['loading_text'] ); ?>" data-default-text="<?php echo esc_attr( $settings['button_text'] ); ?>">
		<span class="tv-gallery__load-more-text"><?php echo esc_html( $settings['button_text'] ); ?></span>
		<span class="tv-gallery__spinner" style="display: none;"></span>
	</button>
</div>
