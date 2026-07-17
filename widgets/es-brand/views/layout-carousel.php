<?php
/**
 * Carousel Layout for Brand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

// Build Swiper settings payload
$carousel_settings = [];
foreach ( $settings as $key => $value ) {
	// Include all swiper specific controls and the column gap layout setting
	if ( strpos( $key, 'slides_per_view' ) === 0 || 
	     strpos( $key, 'slides_per_group' ) === 0 || 
	     strpos( $key, 'column_gap' ) === 0 ) {
		$carousel_settings[ $key ] = $value;
	}
}

// Map root level non-responsive settings
$carousel_settings['loop']               = 'yes' === $settings['loop'];
$carousel_settings['autoplay']           = 'yes' === $settings['autoplay'];
$carousel_settings['autoplaySpeed']      = $settings['autoplaySpeed'];
$carousel_settings['pause_on_hover']     = 'yes' === $settings['pause_on_hover'];
$carousel_settings['speed']              = $settings['speed'];
$carousel_settings['navigation']         = 'yes' === $settings['navigation'];
$carousel_settings['pagination']         = 'yes' === $settings['pagination'];
$carousel_settings['grab_cursor']        = 'yes' === $settings['grab_cursor'];
$carousel_settings['keyboard']           = 'yes' === $settings['keyboard'];
$carousel_settings['mousewheel']         = 'yes' === $settings['mousewheel'];
$carousel_settings['continuous_marquee'] = 'yes' === $settings['continuous_marquee'];
$carousel_settings['reverse_direction']  = 'yes' === $settings['reverse_direction'];

$data_settings = wp_json_encode( $carousel_settings );

?>
<div class="es-brand__carousel" data-settings="<?php echo esc_attr( $data_settings ); ?>">
	<div class="es-swiper-container">
		<div class="swiper-wrapper">
			<?php
			if ( $settings['items'] ) {
				foreach ( $settings['items'] as $index => $item ) {
					$item_key = 'item_' . $index;
					
					$is_link = ! empty( $item['brand_url']['url'] );
					if ( $is_link ) {
						$this->add_link_attributes( $item_key, $item['brand_url'] );
					}
					
					echo '<div class="swiper-slide">';
					include __DIR__ . '/' . esc_attr( $settings['skin'] ) . '.php';
					echo '</div>';
				}
			}
			?>
		</div>
		
		<?php if ( 'yes' === $settings['pagination'] && 'yes' !== $settings['continuous_marquee'] ) : ?>
			<div class="swiper-pagination"></div>
		<?php endif; ?>

		<?php if ( 'yes' === $settings['navigation'] && 'yes' !== $settings['continuous_marquee'] ) : ?>
			<div class="es-swiper-button-prev">
				<?php \Elementor\Icons_Manager::render_icon( [ 'value' => 'fas fa-chevron-left', 'library' => 'fa-solid' ], [ 'aria-hidden' => 'true' ] ); ?>
			</div>
			<div class="es-swiper-button-next">
				<?php \Elementor\Icons_Manager::render_icon( [ 'value' => 'fas fa-chevron-right', 'library' => 'fa-solid' ], [ 'aria-hidden' => 'true' ] ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
