<?php
/**
 * Step Progress Layout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

$items       = $settings['items'];
$total_items = count( $items );
if ( $total_items === 0 ) {
	return;
}

$current_step = (int) $settings['current_value']; // Treat current_value as current step index

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
	<div class="tv-progress__steps">
		<?php
		foreach ( $items as $index => $item ) :
			$step_num   = $index + 1;
			$is_active  = $step_num <= $current_step;
			$is_current = $step_num === $current_step;

			$classes = array( 'tv-progress__step', 'elementor-repeater-item-' . esc_attr( $item['_id'] ) );
			if ( $is_active ) {
				$classes[] = 'is-active';
			}
			if ( $is_current ) {
				$classes[] = 'is-current';
			}

			$color_style = ! empty( $item['item_color'] ) ? '--tv-step-active-color: ' . esc_attr( $item['item_color'] ) . ';' : '';
			?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo $color_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
			<div class="tv-progress__step-indicator">
				<?php if ( ! empty( $item['icon']['value'] ) ) : ?>
					<?php \Elementor\Icons_Manager::render_icon( $item['icon'], array( 'aria-hidden' => 'true' ) ); ?>
				<?php else : ?>
					<?php echo esc_html( $step_num ); ?>
				<?php endif; ?>
			</div>
			<div class="tv-progress__step-content">
				<h5 class="tv-progress__step-title"><?php echo esc_html( $item['title'] ); ?></h5>
			</div>
			
			<?php if ( $step_num < $total_items ) : ?>
				<div class="tv-progress__step-connector"></div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>
</div>

<style>
/* Temp step inline styles until compiled */
.tv-progress__steps {
	display: flex;
	justify-content: space-between;
	position: relative;
	width: 100%;
}
.tv-progress__step {
	display: flex;
	flex-direction: column;
	align-items: center;
	position: relative;
	flex: 1;
	text-align: center;
}
.tv-progress__step-indicator {
	width: 40px;
	height: 40px;
	border-radius: 50%;
	background: var(--tv-progress-track-color, #eee);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 2;
	transition: all 0.3s ease;
}
.tv-progress__step.is-active .tv-progress__step-indicator {
	background: var(--tv-step-active-color, var(--tv-progress-fill-color));
	color: #fff;
}
.tv-progress__step-connector {
	position: absolute;
	top: 20px;
	left: 50%;
	width: 100%;
	height: 4px;
	background: var(--tv-progress-track-color, #eee);
	z-index: 1;
}
.tv-progress__step.is-active .tv-progress__step-connector {
	background: var(--tv-step-active-color, var(--tv-progress-fill-color));
}
.tv-progress__step:last-child .tv-progress__step-connector {
	display: none;
}
.tv-progress__step-content {
	margin-top: 10px;
}
.tv-progress__step-title {
	font-size: 14px;
	margin: 0;
}
</style>
