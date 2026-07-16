<?php
/**
 * Multi Segment Progress Layout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

$items = $settings['items'];

// Calculate total max value for segments
$total_value = 0;
foreach ( $items as $item ) {
	$total_value += (float) $item['current_value'];
}
if ( $total_value === 0 ) {
	$total_value = 100;
}

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
		foreach ( $items as $index => $item ) :
			$percent     = ( (float) $item['current_value'] / $total_value ) * 100;
			$color_style = ! empty( $item['item_color'] ) ? 'background-color: ' . esc_attr( $item['item_color'] ) . ';' : '';
			?>
		<div class="tv-progress__segment elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>" 
			style="--tv-segment-value: <?php echo esc_attr( $percent ); ?>%; <?php echo $color_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
			title="<?php echo esc_attr( $item['title'] . ': ' . $item['current_value'] ); ?>">
		</div>
		<?php endforeach; ?>
	</div>
	
	<div class="tv-progress__legend">
		<?php
		foreach ( $items as $index => $item ) :
			$color_style = ! empty( $item['item_color'] ) ? 'background-color: ' . esc_attr( $item['item_color'] ) . ';' : '';
			?>
		<div class="tv-progress__legend-item">
			<span class="tv-progress__legend-color" style="<?php echo $color_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"></span>
			<span class="tv-progress__legend-title"><?php echo esc_html( $item['title'] ); ?></span>
			<span class="tv-progress__legend-value">(<?php echo esc_html( $item['current_value'] ); ?>)</span>
		</div>
		<?php endforeach; ?>
	</div>
</div>

<style>
/* Temp legend inline styles until compiled */
.tv-progress__legend {
	display: flex;
	flex-wrap: wrap;
	gap: 15px;
	margin-top: 15px;
}
.tv-progress__legend-item {
	display: flex;
	align-items: center;
	gap: 5px;
	font-size: 14px;
}
.tv-progress__legend-color {
	width: 12px;
	height: 12px;
	border-radius: 50%;
	display: inline-block;
	background: var(--tv-progress-fill-color);
}
</style>
