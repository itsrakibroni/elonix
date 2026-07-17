<?php
/**
 * Style One Item Template for Testimonial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

$wrapper_tag = $is_link ? 'a' : 'div';
?>
<<?php echo esc_html( $wrapper_tag ); ?> <?php echo $is_link ? $this->get_render_attribute_string( $item_key ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="es-testimonial__item elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>">
	
	<figure class="es-testimonial__figure">
		
		<div class="es-testimonial__header">
			<?php if ( 'yes' === $settings['show_quote_icon'] ) : ?>
				<div class="es-testimonial__quote-icon">
					<?php
					$quote_icon = $item['quote_icon_override']['value'] ? $item['quote_icon_override'] : $settings['global_quote_icon'];
					if ( ! empty( $quote_icon['value'] ) ) {
						\Elementor\Icons_Manager::render_icon( $quote_icon, array( 'aria-hidden' => 'true' ) );
					}
					?>
				</div>
			<?php endif; ?>

			<?php if ( 'yes' === $settings['show_company_logo'] && ! empty( $item['company_logo']['url'] ) ) : ?>
				<div class="es-testimonial__company-logo">
					<img src="<?php echo esc_url( $item['company_logo']['url'] ); ?>" alt="<?php echo esc_attr( $item['company_name'] ); ?>">
				</div>
			<?php endif; ?>
		</div>

		<blockquote class="es-testimonial__content">
			<?php echo wp_kses_post( $item['testimonial'] ); ?>
		</blockquote>
		
		<figcaption class="es-testimonial__footer">
			
			<div class="es-testimonial__author">
				<?php if ( 'yes' === $settings['show_avatar'] && ! empty( $item['avatar']['url'] ) ) : ?>
					<div class="es-testimonial__avatar">
						<img src="<?php echo esc_url( $item['avatar']['url'] ); ?>" alt="<?php echo esc_attr( $item['client_name'] ); ?>">
					</div>
				<?php endif; ?>

				<div class="es-testimonial__meta">
					<?php if ( 'yes' === $settings['show_client_name'] && ! empty( $item['client_name'] ) ) : ?>
						<h4 class="es-testimonial__client-name"><?php echo esc_html( $item['client_name'] ); ?></h4>
					<?php endif; ?>

					<div class="es-testimonial__designation-wrapper">
						<?php if ( 'yes' === $settings['show_designation'] && ! empty( $item['designation'] ) ) : ?>
							<span class="es-testimonial__designation"><?php echo esc_html( $item['designation'] ); ?></span>
						<?php endif; ?>

						<?php if ( 'yes' === $settings['show_company_name'] && ! empty( $item['company_name'] ) ) : ?>
							<?php if ( 'yes' === $settings['show_designation'] && ! empty( $item['designation'] ) ) : ?>
								<span class="es-testimonial__separator">&mdash;</span>
							<?php endif; ?>
							<span class="es-testimonial__company"><?php echo esc_html( $item['company_name'] ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php if ( 'yes' === $settings['show_rating'] && ! empty( $item['rating'] ) ) : ?>
				<div class="es-testimonial__rating">
					<div class="es-testimonial__rating-icon">
						<?php \Elementor\Icons_Manager::render_icon( $settings['rating_icon'], array( 'aria-hidden' => 'true' ) ); ?>
					</div>
					<span class="es-testimonial__rating-text"><?php echo esc_html( number_format( (float) $item['rating'], 1 ) ); ?></span>
				</div>
			<?php endif; ?>

		</figcaption>
	</figure>

</<?php echo esc_html( $wrapper_tag ); ?>>
