<?php
/**
 * Style One Item Template for Brand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

$wrapper_tag = $is_link ? 'a' : 'div';
?>
<<?php echo esc_html( $wrapper_tag ); ?> <?php echo $is_link ? $this->get_render_attribute_string( $item_key ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="es-brand__item elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>">
	<figure class="es-brand__figure">
		<div class="es-brand__logo">
			<?php if ( ! empty( $item['brand_logo']['url'] ) ) : ?>
				<?php
				$alt = ! empty( $item['brand_name'] ) ? $item['brand_name'] : 'Brand Logo';
				?>
				<img src="<?php echo esc_url( $item['brand_logo']['url'] ); ?>" alt="<?php echo esc_attr( $alt ); ?>">
			<?php endif; ?>
		</div>
		<?php if ( 'yes' === $settings['show_brand_name'] && ! empty( $item['brand_name'] ) ) : ?>
			<figcaption class="es-brand__title"><?php echo esc_html( $item['brand_name'] ); ?></figcaption>
		<?php endif; ?>

		<?php if ( 'yes' === $settings['show_tooltip'] && ! empty( $item['brand_name'] ) ) : ?>
			<div class="es-brand__tooltip"><?php echo esc_html( $item['brand_name'] ); ?></div>
		<?php endif; ?>
	</figure>
</<?php echo esc_html( $wrapper_tag ); ?>>
