<?php
/**
 * Grid Layout for Brand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

?>
<div class="es-brand__grid">
	<?php
	if ( $settings['items'] ) {
		foreach ( $settings['items'] as $index => $item ) {
			$item_key = 'item_' . $index;
			
			$is_link = ! empty( $item['brand_url']['url'] );
			if ( $is_link ) {
				$this->add_link_attributes( $item_key, $item['brand_url'] );
			}
			
			include __DIR__ . '/' . esc_attr( $settings['skin'] ) . '.php';
		}
	}
	?>
</div>
