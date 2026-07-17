<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

if ( empty( $item['button_text'] ) && empty( $item['button_icon']['value'] ) ) {
	return;
}

$has_icon = ! empty( $item['button_icon']['value'] );
$btn_tag  = ! empty( $item['button_link']['url'] ) ? 'a' : 'div';
$btn_attr = '';

if ( 'a' === $btn_tag ) {
	$this->add_link_attributes( 'button_link_' . $item['_id'], $item['button_link'] );
	$btn_attr = $this->get_render_attribute_string( 'button_link_' . $item['_id'] );
}
?>
<<?php echo esc_attr( $btn_tag ); ?> class="es-fc-button" <?php echo $btn_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe attribute string from Elementor. ?>>
	<?php if ( $has_icon && 'before' === $item['button_icon_position'] ) : ?>
		<span class="es-fc-button-icon es-fc-button-icon-before">
			<?php \Elementor\Icons_Manager::render_icon( $item['button_icon'], array( 'aria-hidden' => 'true' ) ); ?>
		</span>
	<?php endif; ?>

	<?php if ( ! empty( $item['button_text'] ) ) : ?>
		<span class="es-fc-button-text"><?php echo esc_html( $item['button_text'] ); ?></span>
	<?php endif; ?>

	<?php if ( $has_icon && 'after' === $item['button_icon_position'] ) : ?>
		<span class="es-fc-button-icon es-fc-button-icon-after">
			<?php \Elementor\Icons_Manager::render_icon( $item['button_icon'], array( 'aria-hidden' => 'true' ) ); ?>
		</span>
	<?php endif; ?>
</<?php echo esc_attr( $btn_tag ); ?>>
