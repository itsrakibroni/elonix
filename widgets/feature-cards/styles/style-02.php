<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="es-fc-card es-fc-style-02 <?php echo esc_attr( $item['custom_class'] ); ?>">
	<?php
	if ( $is_link_wrapper ) {
		echo '<a ' . $wrapper_link_attr . ' class="es-fc-card-link-wrapper">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor safe attribute string.
	}
	?>
	<div class="es-fc-card-inner">
		<?php require __DIR__ . '/../partials/badge.php'; ?>
		<div class="es-fc-card-body">
			<div class="es-fc-card-media-wrap">
				<?php
				if ( 'icon' === $item['media_type'] ) {
					include __DIR__ . '/../partials/icon.php';
				} elseif ( 'image' === $item['media_type'] ) {
					include __DIR__ . '/../partials/image.php';
				}
				require __DIR__ . '/../partials/number.php';
				?>
			</div>
			<div class="es-fc-card-content-wrap">
				<?php
				require __DIR__ . '/../partials/rating.php';
				require __DIR__ . '/../partials/meta.php';
				?>
			</div>
		</div>
		<div class="es-fc-card-bottom">
			<?php
			require __DIR__ . '/../partials/footer.php';
			require __DIR__ . '/../partials/button.php';
			?>
		</div>
	</div>
	<?php
	if ( $is_link_wrapper ) {
		echo '</a>';
	}
	?>
</div>
