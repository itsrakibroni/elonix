<?php
/**
 * Grid Layout for Testimonial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

?>
<div class="es-testimonial__grid">
	<?php
	if ( $settings['items'] ) {
		foreach ( $settings['items'] as $index => $item ) {
			$item_key = 'item_' . $index;
			
			// Setup wrapper links if needed
			$is_link = ! empty( $item['link']['url'] );
			if ( $is_link ) {
				$this->add_link_attributes( $item_key, $item['link'] );
			}
			
			include __DIR__ . '/' . esc_attr( $settings['skin'] ) . '.php';
		}
	}
	?>
</div>
