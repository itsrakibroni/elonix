<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

$filter_items_source = isset( $all_items ) ? $all_items : $gallery_items;

if ( empty( $filter_items_source ) ) {
	return;
}

$categories = [];
foreach ( $filter_items_source as $item ) {
	if ( ! empty( $item['category'] ) ) {
		$categories[ sanitize_title( $item['category'] ) ] = $item['category'];
	}
}

if ( empty( $categories ) ) {
	return;
}
?>
<nav class="tv-gallery__filter-bar" aria-label="<?php esc_attr_e( 'Gallery Filter', 'elonix' ); ?>">
	<button type="button" class="tv-gallery__filter-btn tv-active" data-filter="*" aria-pressed="true">
		<?php echo esc_html( $settings['filter_all_label'] ); ?>
	</button>
	<?php foreach ( $categories as $slug => $name ) : ?>
		<button type="button" class="tv-gallery__filter-btn" data-filter="<?php echo esc_attr( $slug ); ?>" aria-pressed="false">
			<?php echo esc_html( $name ); ?>
		</button>
	<?php endforeach; ?>
</nav>
