<?php
/**
 * Linear Progress Layout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

$animate_counter = $settings['enable_counter'] === 'yes';
$format          = $settings['display_format'];

// Priority 1: Percentage calculation
$current = floatval( $settings['current_value'] );
$max     = floatval( $settings['max_value'] );
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
<div class="tv-progress__wrapper">
	
	<?php if ( ! empty( $settings['title'] ) || ! empty( $settings['subtitle'] ) ) : ?>
	<div class="tv-progress__header">
		<?php if ( ! empty( $settings['title'] ) ) : ?>
		<div class="tv-progress__title">
			<?php if ( ! empty( $settings['icon']['value'] ) ) : ?>
				<span class="tv-progress__title-icon">
					<?php \Elementor\Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?>
				</span>
			<?php endif; ?>
			<?php echo wp_kses_post( $settings['title'] ); ?>
		</div>
		<?php endif; ?>
		<?php if ( ! empty( $settings['subtitle'] ) ) : ?>
		<div class="tv-progress__subtitle">
			<?php echo wp_kses_post( $settings['subtitle'] ); ?>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<div class="tv-progress__track">
		<?php
		if ( $settings['show_target'] === 'yes' ) :
			$target_line_pos = floatval( $settings['target_value'] );
			if ( $target_line_pos > 100 ) {
				$target_line_pos = 100;
			}
			?>
			<div class="tv-progress__target" style="left: <?php echo esc_attr( $target_line_pos ); ?>%;"></div>
		<?php endif; ?>
		
		<div class="tv-progress__fill"></div>
		
		<?php if ( $settings['show_marker'] === 'yes' ) : ?>
			<div class="tv-progress__marker">
				<?php if ( ! empty( $settings['prefix'] ) ) : ?>
					<span class="tv-progress__marker-prefix"><?php echo esc_html( $settings['prefix'] ); ?></span>
				<?php endif; ?>
				
				<span class="tv-progress__marker-val" <?php echo $value_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo $display_value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				
				<?php if ( ! empty( $suffix ) ) : ?>
					<span class="tv-progress__marker-suffix"><?php echo esc_html( $suffix ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
