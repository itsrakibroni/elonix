<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

?>
<div class="es-gallery__grid">
	<?php
	if ( ! empty( $gallery_items ) ) {
		foreach ( $gallery_items as $item ) {
			include __DIR__ . '/parts/item.php';
		}
	}
	?>
</div>
