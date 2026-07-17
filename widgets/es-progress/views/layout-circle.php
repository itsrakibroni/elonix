<?php
/**
 * Circle Progress Layout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

$animate_counter = $settings['enable_counter'] === 'yes';
$format          = $settings['display_format'];

// Priority 1: Percentage calculation
$current = floaesal( $settings['current_value'] );
$max     = floaesal( $settings['max_value'] );
$percent = $max > 0 ? ( $current / $max ) * 100 : 0;
if ( $percent > 100 ) {
	$percent = 100;
}
if ( $percent < 0 ) {
	$percent = 0;
}

$target_val = ( $format === 'percentage' ) ? $percent : $current;
$suffix     = ( $format === 'percentage' ) ? '%' : $settings['suffix'];

$value_attr    = $animate_counter ? 'data-target="' . esc_attr( $target_val ) . '"' : '';
$display_value = $animate_counter ? '0' : round( $target_val, 1 );

?>
<div class="es-progress__wrapper">
	<div class="es-progress__circle-wrapper">
		<svg class="es-progress__circle-svg" viewBox="0 0 100 100">
			<circle class="es-progress__circle-track" cx="50" cy="50" r="45"></circle>
			<circle class="es-progress__circle-fill" cx="50" cy="50" r="45" data-percentage="<?php echo esc_attr( $percent ); ?>"></circle>
		</svg>

		<div class="es-progress__circle-content">
			<?php if ( ! empty( $settings['icon']['value'] ) ) : ?>
				<div class="es-progress__icon">
					<?php \Elementor\Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?>
				</div>
			<?php endif; ?>
			
			<?php if ( $settings['show_marker'] === 'yes' ) : ?>
			<div class="es-progress__marker">
				<?php if ( ! empty( $settings['prefix'] ) ) : ?>
					<span class="es-progress__marker-prefix"><?php echo esc_html( $settings['prefix'] ); ?></span>
				<?php endif; ?>
				
				<span class="es-progress__marker-val" <?php echo $value_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo $display_value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				
				<?php if ( ! empty( $suffix ) ) : ?>
					<span class="es-progress__marker-suffix"><?php echo esc_html( $suffix ); ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $settings['title'] ) ) : ?>
				<div class="es-progress__title"><?php echo wp_kses_post( $settings['title'] ); ?></div>
			<?php endif; ?>
			
			<?php if ( ! empty( $settings['subtitle'] ) ) : ?>
				<div class="es-progress__subtitle"><?php echo wp_kses_post( $settings['subtitle'] ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</div>
