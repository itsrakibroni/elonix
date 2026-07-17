<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Custom Comments Template for Elonix – Toolkit for Elementor
 */

if ( post_password_required() ) {
	return;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local template variable.
$state = Elonix_Toolkit_Post_Comments_Renderer::$current_state;
if ( empty( $state ) ) {
	return;
}

$settings         = $state['settings'];
$comments         = $state['comments'];
$total_comments   = $state['total_comments'];
$has_comments     = $state['has_comments'];
$per_page         = $state['per_page'];
$current_page     = $state['current_page'];
$total_pages      = $state['total_pages'];
$pagination_links = $state['pagination_links'];

?>

<div id="comments" class="es-comments-area">
	
	<?php if ( $has_comments ) : ?>
		
		<?php if ( 'yes' === $settings['show_comment_count'] ) : ?>
			<h3 class="es-comments-title">
				<?php
				if ( 1 === $total_comments ) {
					printf( esc_html__( '1 Comment', 'elonix' ) );
				} else {
					/* translators: %1$s: Number of comments */
					printf( esc_html( _nx( '%1$s Comment', '%1$s Comments', $total_comments, 'comments title', 'elonix' ) ), esc_html( number_format_i18n( $total_comments ) ) );
				}
				?>
			</h3>
		<?php endif; ?>

		<ol class="es-comment-list">
			<?php
			$list_args = array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => isset( $settings['avatar_size']['size'] ) ? $settings['avatar_size']['size'] : 60,
				'callback'    => array( 'Elonix_Toolkit_Post_Comments_Renderer', 'custom_comment_callback' ),
			);

			if ( $per_page > 0 ) {
				$list_args['per_page'] = $per_page;
				$list_args['page']     = $current_page;
			}

			// Passing $comments array explicitly tells wp_list_comments to bypass the global $wp_query->comments
			wp_list_comments( $list_args, $comments );
			?>
		</ol>

		<?php if ( 'yes' === $settings['show_pagination'] && ! empty( $pagination_links ) ) : ?>
			<div class="es-comment-navigation">
				<?php echo wp_kses_post( $pagination_links ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! comments_open() ) : ?>
			<p class="es-no-comments"><?php echo esc_html( $settings['empty_comments_message'] ); ?></p>
		<?php endif; ?>

	<?php endif; ?>

	<?php
	if ( 'yes' === $settings['show_comment_form'] && comments_open() ) :
		$args = array(
			'title_reply'       => $settings['reply_button_text'],
			'label_submit'      => $settings['submit_button_text'],
			'cancel_reply_link' => $settings['cancel_reply_text'],
			'class_container'   => 'es-comment-respond',
			'class_form'        => 'es-comment-form',
			'class_submit'      => 'es-submit-btn',
		);
		comment_form( $args );
		// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	endif;
	?>

</div>
