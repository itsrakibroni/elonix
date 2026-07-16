<?php
/**
 * Elonix – Toolkit for Elementor Post Comments Renderer
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Toolkit_Post_Comments_Renderer {

	public static $current_state = array();

	public static function render_comments_area( $settings, $is_editor = false ) {
		require_once __DIR__ . '/class-query.php';
		require_once __DIR__ . '/class-pagination.php';

		$query          = new Elonix_Toolkit_Post_Comments_Query( get_the_ID() );
		$comments       = $query->fetch_comments();
		$total_comments = $query->get_total_comments();
		$has_comments   = $query->has_comments();

		if ( $is_editor ) {
			$comments       = self::mock_comments_array( $comments, get_the_ID() );
			$total_comments = count( $comments );
			$has_comments   = true;
		}

		$elementor_setting = isset( $settings['comments_per_page'] ) ? $settings['comments_per_page'] : '';
		$per_page          = Elonix_Toolkit_Post_Comments_Pagination::resolve_comments_per_page( $elementor_setting );
		$pagination        = null;
		$current_page      = 1;
		$total_pages       = 1;
		$pagination_links  = '';

		if ( $per_page > 0 ) {
			$pagination       = new Elonix_Toolkit_Post_Comments_Pagination( $comments, $per_page );
			$current_page     = $pagination->get_current_page();
			$total_pages      = $pagination->get_total_pages();
			$pagination_links = $pagination->get_pagination_links();
		}

		self::$current_state = compact(
			'settings',
			'query',
			'comments',
			'total_comments',
			'has_comments',
			'per_page',
			'pagination',
			'current_page',
			'total_pages',
			'pagination_links',
			'is_editor'
		);

		$avatar_action = function () use ( $settings ) {
			echo '<input type="hidden" name="tv_avatar_size" value="' . esc_attr( $settings['avatar_size']['size'] ?? 60 ) . '">';
		};
		add_action( 'comment_form', $avatar_action );

		ob_start();
		require __DIR__ . '/comments.php';
		$comments_html = ob_get_clean();

		remove_action( 'comment_form', $avatar_action );
		self::$current_state = array();

		// Base modern CSS injection
		$css = "<style>
		.tv-comment-list { list-style: none; padding: 0; margin: 0; }
		.tv-comment-card { display: flex; flex-wrap: nowrap; gap: 20px; transition: all 0.3s ease; margin-bottom: 25px; }
		.tv-comment-avatar { flex-shrink: 0; display: inline-block; position: relative; }
		.tv-comment-avatar img.avatar { position: static !important; left: auto !important; right: auto !important; top: auto !important; bottom: auto !important; margin: 0 !important; display: block; box-sizing: border-box; object-fit: cover; max-width: 100%; }
		.tv-comment-body { flex: 1; min-width: 0; }
		.tv-comment-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
		.tv-comment-author-info { display: flex; flex-direction: column; }
		.tv-comment-author { font-weight: 600; margin-bottom: 2px; }
		.tv-comment-meta a { font-size: 0.9em; opacity: 0.7; text-decoration: none; }
		.tv-comment-reply a { display: inline-block; transition: all 0.3s ease; text-decoration: none; }
		.tv-comment-content p { margin-bottom: 15px; }
		.tv-comment-content p:last-child { margin-bottom: 0; }
		.tv-comment-respond { margin-top: 40px; }
		.tv-comment-form { display: flex; flex-direction: column; gap: 15px; }
		.tv-comment-form p { margin: 0; }
		.tv-comment-form input[type='text'], .tv-comment-form input[type='email'], .tv-comment-form input[type='url'], .tv-comment-form textarea { width: 100%; box-sizing: border-box; transition: all 0.3s ease; }
		@media (max-width: 767px) {
			.tv-comment-card { flex-direction: column; gap: 15px; }
			.tv-comment-header { flex-direction: column; gap: 10px; }
		}
		</style>";

		$anim_type     = $settings['anim_type'] ?? 'fade';
		$anim_duration = $settings['anim_duration'] ?? '0.4s';
		$scroll_after  = $settings['scroll_after_ajax'] ?? 'smooth';
		$html          = str_replace( 'class="tv-comments-area"', 'class="tv-comments-area" data-anim-type="' . esc_attr( $anim_type ) . '" data-anim-duration="' . esc_attr( $anim_duration ) . '" data-scroll-after="' . esc_attr( $scroll_after ) . '"', $comments_html );

		return $css . $html;
	}



	public static function custom_comment_callback( $comment, $args, $depth ) {
		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
		?>
		<<?php echo esc_html( $tag ); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent', $comment ); ?>>
			<div id="div-comment-<?php comment_ID(); ?>" class="tv-comment-card">
				<?php if ( 0 != $args['avatar_size'] ) : ?>
					<div class="tv-comment-avatar">
						<?php echo get_avatar( $comment, $args['avatar_size'] ); ?>
					</div>
				<?php endif; ?>
				<div class="tv-comment-body">
					<div class="tv-comment-header">
						<div class="tv-comment-author-info">
							<div class="tv-comment-author"><?php printf( '<span class="fn">%s</span>', get_comment_author_link( $comment ) ); ?></div>
							<div class="tv-comment-meta">
								<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
									<time datetime="<?php comment_time( 'c' ); ?>">
										<?php
										/* translators: %1$s: Comment date, %2$s: Comment time */
										printf( esc_html__( '%1$s at %2$s', 'elonix' ), esc_html( get_comment_date( '', $comment ) ), esc_html( get_comment_time() ) );
										?>
									</time>
								</a>
								<?php edit_comment_link( esc_html__( 'Edit', 'elonix' ), ' <span class="tv-edit-link">', '</span>' ); ?>
							</div>
						</div>
						<div class="tv-comment-reply">
							<?php
							comment_reply_link(
								array_merge(
									$args,
									array(
										'add_below' => 'div-comment',
										'depth'     => $depth,
										'max_depth' => $args['max_depth'],
										'before'    => '',
										'after'     => '',
									)
								)
							);
							?>
						</div>
					</div>
					<div class="tv-comment-content">
						<?php if ( '0' == $comment->comment_approved ) : ?>
							<em class="tv-comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'elonix' ); ?></em><br />
						<?php endif; ?>
						<?php comment_text(); ?>
					</div>
				</div>
			</div>
		<?php
	}

	public static function mock_comments_array( $comments, $post_id ) {
		if ( ! empty( $comments ) ) {
			return $comments;
		}
		return array(
			new \WP_Comment(
				(object) array(
					'comment_ID'           => 1,
					'comment_post_ID'      => $post_id,
					'comment_author'       => 'Elonix User',
					'comment_author_email' => 'user@example.com',
					'comment_author_url'   => '',
					'comment_author_IP'    => '127.0.0.1',
					'comment_date'         => current_time( 'mysql' ),
					'comment_date_gmt'     => current_time( 'mysql', 1 ),
					'comment_content'      => 'This is a beautifully designed mock comment for Elonix – Toolkit for Elementor. It allows you to perfectly visualize spacing, typography, and premium comment cards live inside the Elementor editor.',
					'comment_karma'        => 0,
					'comment_approved'     => '1',
					'comment_agent'        => '',
					'comment_type'         => 'comment',
					'comment_parent'       => 0,
					'user_id'              => 0,
				)
			),
		);
	}
}
