<?php
/**
 * Marquee Widget - Marquee Engine Layout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

$pause_button = ! empty( $settings['pause_button'] ) && 'yes' === $settings['pause_button'];

// Build Marquee settings payload
$carousel_settings = [];
foreach ( $settings as $key => $value ) {
	if ( strpos( $key, 'item_gap' ) === 0 || strpos( $key, 'animation_speed' ) === 0 ) {
		$carousel_settings[ $key ] = $value;
	}
}

// Map root level non-responsive settings
$carousel_settings['layout_mode']          = ! empty( $settings['layout_mode'] ) ? $settings['layout_mode'] : 'marquee';
$carousel_settings['direction']            = ! empty( $settings['direction'] ) ? $settings['direction'] : 'left';
$carousel_settings['duplicated']           = 'yes' === $settings['duplicated'];
$carousel_settings['duplicateCount']       = ! empty( $settings['duplicateCount'] ) ? absint( $settings['duplicateCount'] ) : 1;
$carousel_settings['pause_on_hover']       = 'yes' === $settings['pause_on_hover'];
$carousel_settings['pause_on_cycle']       = 'yes' === $settings['pause_on_cycle'];
$carousel_settings['delay_before_start']   = ! empty( $settings['delay_before_start'] ) ? absint( $settings['delay_before_start'] ) : 0;
$carousel_settings['start_visible']        = 'yes' === $settings['start_visible'];
$carousel_settings['allow_css3_support']   = 'yes' === $settings['allow_css3_support'];

$data_settings = wp_json_encode( $carousel_settings );
?>
<div class="tv-marquee__wrapper tv-marquee__carousel" data-settings="<?php echo esc_attr( $data_settings ); ?>">
	<div class="tv-marquee__track">
		<?php
		if ( ! empty( $settings['items'] ) ) {
			foreach ( $settings['items'] as $index => $item ) {
				$item_key = 'item_' . $index;
				
				$this->add_render_attribute( $item_key, 'class', 'tv-marquee__item elementor-repeater-item-' . esc_attr( $item['_id'] ) );
				
				$this->add_render_attribute( $item_key . '_text', 'class', 'tv-marquee__text' );
				$this->add_render_attribute( $item_key . '_icon', 'class', 'tv-marquee__icon' );
				$this->add_render_attribute( $item_key . '_title', 'class', 'tv-marquee__title' );

				$display_type = ! empty( $item['item_display_type'] ) ? $item['item_display_type'] : 'icon';
				$has_icon     = ! empty( $item['item_icon']['value'] );
				$has_image    = ! empty( $item['item_image']['id'] ) || ! empty( $item['item_image']['url'] );
				
				// Optional link wrapper
				$is_link = ! empty( $item['item_link']['url'] );
				$tag = $is_link ? 'a' : 'div';
				if ( $is_link ) {
					$this->add_link_attributes( $item_key, $item['item_link'] );
				}
				?>
				<<?php echo esc_attr( $tag ); ?> <?php $this->print_render_attribute_string( $item_key ); ?>>
					<div class="tv-marquee__content">
						<?php if ( 'image' === $display_type && $has_image ) : ?>
							<figure class="tv-marquee__image">
								<?php $this->render_item_image( $item ); ?>
							</figure>
						<?php elseif ( 'icon' === $display_type && $has_icon ) : ?>
							<span <?php $this->print_render_attribute_string( $item_key . '_icon' ); ?>>
								<?php \Elementor\Icons_Manager::render_icon( $item['item_icon'], [ 'aria-hidden' => 'true' ] ); ?>
							</span>
						<?php endif; ?>
						
						<div <?php $this->print_render_attribute_string( $item_key . '_text' ); ?>>
							<?php if ( ! empty( $item['item_text'] ) ) : ?>
								<span <?php $this->print_render_attribute_string( $item_key . '_title' ); ?>><?php echo wp_kses_post( $item['item_text'] ); ?></span>
							<?php endif; ?>
							
							<?php if ( ! empty( $item['item_description'] ) ) : ?>
								<span class="tv-marquee__desc"><?php echo wp_kses_post( $item['item_description'] ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</<?php echo esc_attr( $tag ); ?>>
				<?php
			}
		}
		?>
	</div>
	
	<?php if ( $pause_button ) : ?>
		<button class="tv-marquee__pause-btn" aria-label="<?php esc_attr_e( 'Pause/Play animation', 'elonix' ); ?>" aria-pressed="false">
			<span class="tv-marquee__pause-icon" aria-hidden="true">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
			</span>
		</button>
	<?php endif; ?>
</div>
