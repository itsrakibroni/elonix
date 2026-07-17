<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

if ( empty( $item ) ) {
	return;
}

$item_classes = [
	'es-gallery__item',
	'es-gallery__item--style-1', // Default future style routing
];

$category_slug = ! empty( $item['category'] ) ? sanitize_title( $item['category'] ) : '';
?>
<figure class="<?php echo implode( ' ', array_map( 'esc_attr', $item_classes ) ); ?>" data-category="<?php echo esc_attr( $category_slug ); ?>">
	
	<?php include __DIR__ . '/item-image.php'; ?>
	<?php include __DIR__ . '/item-overlay.php'; ?>
	
	<?php 
	if ( 'yes' === $settings['show_badge'] && ! empty( $item['badge'] ) ) {
		include __DIR__ . '/item-badge.php'; 
	}
	?>

	<?php include __DIR__ . '/item-content.php'; ?>
	
	<?php 
	if ( ! empty( $item['link']['url'] ) || ( 'yes' === $settings['show_lightbox'] && ! empty( $item['lightbox'] ) ) ) {
		$link_url = ! empty( $item['link']['url'] ) ? $item['link']['url'] : $item['lightbox'];
		$link_classes = [ 'es-gallery__link-overlay' ];
		$is_lightbox = empty( $item['link']['url'] ) && 'yes' === $settings['show_lightbox'];
		
		$lightbox_attr = '';
		if ( $is_lightbox ) {
			$link_classes[] = 'es-gallery__lightbox';
			$current_widget_id = isset( $widget ) ? $widget->get_id() : $this->get_id();
			$lightbox_attr = ' data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="' . esc_attr( $current_widget_id ) . '"';
		}
		?>
		<a href="<?php echo esc_url( $link_url ); ?>" class="<?php echo implode( ' ', array_map( 'esc_attr', $link_classes ) ); ?>" aria-label="<?php echo esc_attr( $item['title'] ); ?>"<?php echo $lightbox_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></a>
		<?php
	}
	?>
</figure>
