<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

if ( empty( $item['rating_value'] ) ) {
	return;
}

$rating = floatval( $item['rating_value'] );
$max_rating = 5;
?>
<div class="tv-fc-rating">
	<div class="tv-fc-rating-stars">
		<?php for ( $i = 1; $i <= $max_rating; $i++ ) : ?>
			<?php if ( $i <= $rating ) : ?>
				<i class="fas fa-star tv-fc-star-full" aria-hidden="true"></i>
			<?php elseif ( $i - 0.5 <= $rating ) : ?>
				<i class="fas fa-star-half-alt tv-fc-star-half" aria-hidden="true"></i>
			<?php else : ?>
				<i class="far fa-star tv-fc-star-empty" aria-hidden="true"></i>
			<?php endif; ?>
		<?php endfor; ?>
	</div>
</div>
