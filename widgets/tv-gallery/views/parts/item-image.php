<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

?>
<div class="tv-gallery__image">
	<?php
	if ( ! empty( $item['image']['id'] ) ) {
		echo wp_get_attachment_image( $item['image']['id'], 'full', false, [
			'alt'     => $item['alt'],
			'loading' => 'lazy',
		] );
	} elseif ( ! empty( $item['image']['url'] ) ) {
		?>
		<img src="<?php echo esc_url( $item['image']['url'] ); ?>" alt="<?php echo esc_attr( $item['alt'] ); ?>" loading="lazy">
		<?php
	}
	?>
</div>
