<?php
/**
 * Carousel Layout for Testimonial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

	$carousel_settings = [
	'loop'          => $settings['loop'] ?? 'yes',
	'autoplay'      => $settings['autoplay'] ?? 'yes',
	'autoplaySpeed' => $settings['autoplaySpeed'] ?? 5000,
	'speed'         => $settings['speed'] ?? 500,
	'pauseOnHover'  => $settings['pause_on_hover'] ?? 'yes',
	'navigation'    => $settings['navigation'] ?? 'yes',
	'pagination'    => $settings['pagination'] ?? 'yes',
	'grabCursor'    => $settings['grab_cursor'] ?? 'yes',
	'mousewheel'    => $settings['mousewheel'] ?? 'no',
	'keyboard'      => $settings['keyboard'] ?? 'yes',
];

// Forward raw responsive settings directly to the JS Core Framework
foreach ( $settings as $key => $value ) {
	if ( strpos( $key, 'slides_per_view' ) === 0 || 
	     strpos( $key, 'slides_per_group' ) === 0 || 
	     strpos( $key, 'column_gap' ) === 0 ) {
		$carousel_settings[ $key ] = $value;
	}
}
?>
<div class="es-testimonial__carousel" data-settings="<?php echo esc_attr( wp_json_encode( $carousel_settings ) ); ?>">
	<div class="swiper-container swiper">
		<div class="swiper-wrapper">
			<?php
			if ( $settings['items'] ) {
				foreach ( $settings['items'] as $index => $item ) {
					$item_key = 'item_' . $index;
					
					// Setup wrapper links if needed
					$is_link = ! empty( $item['link']['url'] );
					if ( $is_link ) {
						$this->add_link_attributes( $item_key, $item['link'] );
					}
					
					echo '<div class="swiper-slide">';
					include __DIR__ . '/' . esc_attr( $settings['skin'] ) . '.php';
					echo '</div>';
				}
			}
			?>
		</div>
		<?php if ( 'yes' === $settings['pagination'] ) : ?>
			<div class="swiper-pagination"></div>
		<?php endif; ?>
	</div>
	<?php if ( 'yes' === $settings['navigation'] ) : ?>
		<div class="es-swiper-button-prev"><i class="eicon-chevron-left" aria-hidden="true"></i></div>
		<div class="es-swiper-button-next"><i class="eicon-chevron-right" aria-hidden="true"></i></div>
	<?php endif; ?>
</div>
