<?php
/**
 * Elonix – Toolkit for Elementor Toggle Widget Template
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

$items         = $settings['toggle_items'];
$default_index = absint( $settings['default_active_index'] );
$default_index = max( 0, min( $default_index, count( $items ) - 1 ) );
$aria_label    = ! empty( $settings['aria_label'] ) ? $settings['aria_label'] : esc_html__( 'Toggle Navigation', 'elonix' );

$toggle_type  = ! empty( $settings['toggle_type'] ) ? $settings['toggle_type'] : 'pills';
$toggle_style = ! empty( $settings['toggle_style'] ) ? $settings['toggle_style'] : 'pills';

$wrapper_classes = array(
	'tv-toggle',
	'tv-toggle--type-' . esc_attr( $toggle_type ),
	'tv-toggle--style-' . esc_attr( $toggle_style ),
);
?>
<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
	<div class="tv-toggle__wrapper">
		<ul class="tv-toggle__list" role="tablist" aria-label="<?php echo esc_attr( $aria_label ); ?>">
			
			<li class="tv-toggle__indicator" aria-hidden="true" role="presentation"></li>

			<?php
			foreach ( $items as $index => $item ) :
				$is_active       = ( $index === $default_index );
				$active_class    = $is_active ? ' tv-toggle__button--active' : '';
				$target_selector = esc_attr( $item['target_selector'] );
				?>
				<li class="tv-toggle__item" role="presentation">
					<button class="tv-toggle__button<?php echo esc_attr( $active_class ); ?>" data-tv-target="<?php echo esc_attr( $target_selector ); ?>" data-tv-index="<?php echo esc_attr( $index ); ?>" role="tab" tabindex="<?php echo $is_active ? '0' : '-1'; ?>" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
						<?php if ( ! empty( $item['icon']['value'] ) && 'yes' === $settings['show_icons'] ) : ?>
							<span class="tv-toggle__icon">
								<?php \Elementor\Icons_Manager::render_icon( $item['icon'], array( 'aria-hidden' => 'true' ) ); ?>
							</span>
						<?php endif; ?>
						
						<?php if ( ! empty( $item['label'] ) ) : ?>
							<span class="tv-toggle__label"><?php echo esc_html( $item['label'] ); ?></span>
						<?php endif; ?>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
