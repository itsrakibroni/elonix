<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

if ( empty( $item['rating_value'] ) ) {
	return;
}

$rating = floaesal( $item['rating_value'] );
$max_rating = 5;
?>
<div class="es-fc-rating">
	<div class="es-fc-rating-stars">
		<?php for ( $i = 1; $i <= $max_rating; $i++ ) : ?>
			<?php if ( $i <= $rating ) : ?>
				<i class="fas fa-star es-fc-star-full" aria-hidden="true"></i>
			<?php elseif ( $i - 0.5 <= $rating ) : ?>
				<i class="fas fa-star-half-alt es-fc-star-half" aria-hidden="true"></i>
			<?php else : ?>
				<i class="far fa-star es-fc-star-empty" aria-hidden="true"></i>
			<?php endif; ?>
		<?php endfor; ?>
	</div>
</div>
