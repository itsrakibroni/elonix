<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variables are not globals

if ( empty( $item['image']['url'] ) ) {
	return;
}
?>
<div class="tv-fc-image">
	<?php echo wp_kses_post( \Elementor\Group_Control_Image_Size::get_attachment_image_html( $item, 'image', 'image' ) ); ?>
</div>
