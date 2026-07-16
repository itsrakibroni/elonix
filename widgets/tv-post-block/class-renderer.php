<?php
/**
 * Elonix Post Block Renderer
 *
 * Standalone rendering engine for Post Block cards. Contains zero Elementor
 * inheritance — it is a plain PHP utility class.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * ARCHITECTURE NOTE
 * ─────────────────────────────────────────────────────────────────────────────
 * This class exists so that rendering logic can be shared between:
 *   1. Elonix_Toolkit_Post_Block_Widget   (Elementor widget)
 *   2. Elonix_Toolkit_Search_Results_Widget (Elementor widget)
 *   3. Elonix_Toolkit_Post_Block_AJAX      (AJAX handler — plugins_loaded)
 *   4. Elonix_Toolkit_Search_Results_AJAX_Handler (AJAX handler — plugins_loaded)
 *
 * Widgets are only loaded AFTER Elementor fires elementor/widgets/register.
 * AJAX handlers fire at plugins_loaded, BEFORE Elementor is ready.
 *
 * Loading Elonix_Widget_Base (or any Elementor widget) at plugins_loaded
 * causes a fatal: Class "Elementor\Widget_Base" not found.
 *
 * This class has NO parent class — it is safe to load at any point in the
 * WordPress boot sequence.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Shared card renderer for the Post Block and Search Results widgets.
 *
 * All methods are static. No instantiation required.
 */
class Elonix_Toolkit_Post_Block_Renderer {

	// ──────────────────────────────────────────────────────────────────────────
	// Public API
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Render a single post card.
	 *
	 * Identical HTML to the original render_single_post() that lived inside
	 * Elonix_Toolkit_Post_Block_Widget. Widget delegates here; AJAX
	 * handlers call this directly.
	 *
	 * @param array  $item       Formatted post data from Query Helper.
	 * @param array  $settings   Widget / AJAX settings.
	 * @param string $layout     Layout key (style_1 … style_5).
	 * @param int    $post_index Zero-based index of the current post card.
	 */
	public static function render_single_post( $item, $settings, $layout, $post_index = 0 ) {
		$card_classes   = array( 'tv-post-block-card' );
		$show_featured  = isset( $settings['show_featured'] ) ? $settings['show_featured'] : 'no';
		$is_featured    = ( 'yes' === $show_featured && 0 === $post_index );
		$card_classes[] = $is_featured ? 'tv-featured-card' : 'tv-standard-card';

		if ( 'style_3' === $layout ) {
			if ( 0 === $post_index ) {
				$card_classes[] = 'tv-grid-primary';
			} else {
				$card_classes[] = 'tv-grid-secondary';
			}
		}

		?>
		<article class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>">
			<?php
			if ( class_exists( 'Elonix_Toolkit_Edit_Overlay' ) ) {
				Elonix_Toolkit_Edit_Overlay::render( $item['id'] );
			}
			?>
			<?php if ( ! empty( $item['thumbnail'] ) ) : ?>
				<div class="tv-post-block-thumbnail">
					<!-- Redundant tab stops fixed by removing keyboard focus and hiding from screen readers. Screen readers read title or read more button. -->
					<a href="<?php echo esc_url( $item['url'] ); ?>" tabindex="-1" aria-hidden="true">
						<?php echo $item['thumbnail']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<?php self::render_smart_badges( $item, $settings ); ?>
				</div>
			<?php endif; ?>

			<div class="tv-post-block-content">
				<?php
				// Reorder layout elements using visual sortable builder settings
				$element_order = ! empty( $settings['post_element_order'] ) ? $settings['post_element_order'] : array();
				if ( ! empty( $element_order ) ) {
					$rendered_elements = array();
					foreach ( $element_order as $block ) {
						// Skip disabled items in the Visual Builder
						if ( isset( $block['show_element'] ) && 'yes' !== $block['show_element'] ) {
							continue;
						}
						$el_type = ! empty( $block['element_type'] ) ? $block['element_type'] : '';
						if ( empty( $el_type ) || in_array( $el_type, $rendered_elements, true ) ) {
							continue;
						}
						$rendered_elements[] = $el_type;
						self::render_element_block( $el_type, $item, $settings, $block );
					}
				}
				?>
			</div>
		</article>
		<?php
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Internal helpers (protected static — accessible to subclasses if any)
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Maps sortable builder elements to layout block templates.
	 *
	 * @param string $type     Element type key.
	 * @param array  $item     Post data array.
	 * @param array  $settings Widget settings.
	 * @param array  $block    Repeater block settings.
	 */
	protected static function render_element_block( $type, $item, $settings, $block = array() ) {
		switch ( $type ) {
			case 'badge':
				$badge_type = ! empty( $block['badge_type'] ) ? $block['badge_type'] : ( ! empty( $settings['badge_type'] ) ? $settings['badge_type'] : 'category' );
				$badge_text = '';

				if ( 'category' === $badge_type && ! empty( $item['primary_category'] ) ) {
					$badge_text = $item['primary_category'];
				} elseif ( 'post_type' === $badge_type ) {
					$post_obj   = get_post( $item['id'] );
					$badge_text = $post_obj ? get_post_type_object( $post_obj->post_type )->labels->singular_name : 'Post';
				} elseif ( 'custom' === $badge_type ) {
					$badge_text = ! empty( $block['badge_text'] ) ? $block['badge_text'] : ( ! empty( $settings['badge_text'] ) ? $settings['badge_text'] : '' );
				}

				if ( ! empty( $badge_text ) ) {
					printf(
						'<div class="tv-post-block-badge-wrapper"><span class="tv-post-block-badge">%s%s</span></div>',
						self::get_icon_markup( $settings, 'badge_icon' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						esc_html( $badge_text )
					);
				}
				break;

			case 'title':
				$title_tag = ! empty( $block['title_tag'] ) ? $block['title_tag'] : ( ! empty( $settings['title_tag'] ) ? $settings['title_tag'] : 'h3' );
				$title_val = $item['title'];
				// If block-specific title limits are configured, format raw_title
				if ( ( isset( $block['title_word_limit'] ) && 0 !== intval( $block['title_word_limit'] ) ) || ( isset( $block['title_char_limit'] ) && 0 !== intval( $block['title_char_limit'] ) ) ) {
					$title_words  = isset( $block['title_word_limit'] ) ? intval( $block['title_word_limit'] ) : 0;
					$title_chars  = isset( $block['title_char_limit'] ) ? intval( $block['title_char_limit'] ) : 0;
					$title_suffix = isset( $block['title_suffix'] ) ? sanitize_text_field( $block['title_suffix'] ) : '...';
					$title_val    = Elonix_Toolkit_Post_Block_Query_Helper::limit_text_content(
						! empty( $item['raw_title'] ) ? $item['raw_title'] : $item['title'],
						$title_words,
						$title_chars,
						$title_suffix,
						false,
						false
					);
				}
				printf(
					'<%1$s class="tv-post-block-title"><a href="%2$s">%3$s</a></%1$s>',
					esc_attr( $title_tag ),
					esc_url( $item['url'] ),
					esc_html( $title_val )
				);
				break;

			case 'meta':
				$meta_order = ! empty( $settings['meta_elements_order'] ) ? $settings['meta_elements_order'] : array();
				if ( ! empty( $meta_order ) ) {
					echo '<div class="tv-post-block-meta">';
					foreach ( $meta_order as $meta_block ) {
						if ( isset( $meta_block['show_meta_item'] ) && 'yes' !== $meta_block['show_meta_item'] ) {
							continue;
						}
						self::render_meta_item( $meta_block['meta_type'], $item, $settings, $meta_block );
					}
					echo '</div>';
				}
				break;

			case 'excerpt':
				$excerpt_val = $item['excerpt'];
				// If block-specific excerpt limits are configured, format raw_excerpt
				if ( isset( $block['excerpt_word_limit'] ) || isset( $block['excerpt_char_limit'] ) ) {
					$excerpt_words      = isset( $block['excerpt_word_limit'] ) ? intval( $block['excerpt_word_limit'] ) : 15;
					$excerpt_chars      = isset( $block['excerpt_char_limit'] ) ? intval( $block['excerpt_char_limit'] ) : 0;
					$excerpt_strip_html = ! isset( $block['excerpt_strip_html'] ) || 'yes' === $block['excerpt_strip_html'];
					$excerpt_strip_sc   = ! isset( $block['excerpt_strip_shortcodes'] ) || 'yes' === $block['excerpt_strip_shortcodes'];
					$excerpt_suffix     = isset( $block['excerpt_suffix'] ) ? sanitize_text_field( $block['excerpt_suffix'] ) : '...';
					$excerpt_val        = Elonix_Toolkit_Post_Block_Query_Helper::limit_text_content(
						! empty( $item['raw_excerpt'] ) ? $item['raw_excerpt'] : $item['excerpt'],
						$excerpt_words,
						$excerpt_chars,
						$excerpt_suffix,
						$excerpt_strip_html,
						$excerpt_strip_sc
					);
				}
				if ( ! empty( $excerpt_val ) ) {
					printf(
						'<div class="tv-post-block-excerpt">%s</div>',
						esc_html( $excerpt_val )
					);
				}
				break;

			case 'read_more':
				$btn_text      = ! empty( $block['read_more_text'] ) ? $block['read_more_text'] : ( ! empty( $settings['read_more_text'] ) ? $settings['read_more_text'] : esc_html__( 'Read More', 'elonix' ) );
				$icon_position = ! empty( $settings['read_more_icon_position'] ) ? $settings['read_more_icon_position'] : 'before';
				$icon_markup   = self::get_icon_markup( $settings, 'read_more_icon' );
				$text_markup   = esc_html( $btn_text );
				?>
				<div class="tv-post-block-readmore">
					<a href="<?php echo esc_url( $item['url'] ); ?>" class="tv-readmore-btn tv-icon-pos-<?php echo esc_attr( $icon_position ); ?>">
						<?php echo 'after' === $icon_position ? $text_markup . $icon_markup : $icon_markup . $text_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>
				<?php
				break;

			case 'author':
				$class_suffix = '';
				if ( ! empty( $block['_id'] ) ) {
					$class_suffix .= ' elementor-repeater-item-' . $block['_id'];
				}
				echo '<div class="tv-post-block-meta-single' . esc_attr( $class_suffix ) . '">';
				self::render_meta_item( 'author', $item, $settings, $block );
				echo '</div>';
				break;

			case 'date':
				$class_suffix = '';
				if ( ! empty( $block['_id'] ) ) {
					$class_suffix .= ' elementor-repeater-item-' . $block['_id'];
				}
				echo '<div class="tv-post-block-meta-single' . esc_attr( $class_suffix ) . '">';
				self::render_meta_item( 'date', $item, $settings, $block );
				echo '</div>';
				break;

			case 'comments':
				$class_suffix = '';
				if ( ! empty( $block['_id'] ) ) {
					$class_suffix .= ' elementor-repeater-item-' . $block['_id'];
				}
				echo '<div class="tv-post-block-meta-single' . esc_attr( $class_suffix ) . '">';
				self::render_meta_item( 'comments', $item, $settings, $block );
				echo '</div>';
				break;

			case 'reading_time':
				$class_suffix = '';
				if ( ! empty( $block['_id'] ) ) {
					$class_suffix .= ' elementor-repeater-item-' . $block['_id'];
				}
				echo '<div class="tv-post-block-meta-single' . esc_attr( $class_suffix ) . '">';
				self::render_meta_item( 'reading_time', $item, $settings, $block );
				echo '</div>';
				break;

			case 'share':
				$icon_position = ! empty( $block['icon_position'] ) ? $block['icon_position'] : 'before';
				$class_suffix  = ( 'after' === $icon_position ) ? ' tv-icon-pos-after' : '';
				if ( ! empty( $block['_id'] ) ) {
					$class_suffix .= ' elementor-repeater-item-' . $block['_id'];
				}

				$icon_markup  = self::get_icon_markup( $settings, 'share_icon' );
				$label_markup = '<span class="tv-share-label-text">' . esc_html__( 'Share:', 'elonix' ) . '</span>';
				?>
				<div class="tv-post-block-share<?php echo esc_attr( $class_suffix ); ?>">
					<span class="tv-share-label">
						<?php echo 'after' === $icon_position ? $label_markup . $icon_markup : $icon_markup . $label_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $item['url'] ) ); ?>" target="_blank" rel="noopener" class="tv-share-btn fb" aria-label="<?php esc_attr_e( 'Share on Facebook', 'elonix' ); ?>">F</a>
					<a href="<?php echo esc_url( 'https://twitter.com/intent/tweet?url=' . rawurlencode( $item['url'] ) ); ?>" target="_blank" rel="noopener" class="tv-share-btn tw" aria-label="<?php esc_attr_e( 'Share on Twitter', 'elonix' ); ?>">T</a>
					<a href="<?php echo esc_url( 'https://api.whatsapp.com/send?text=' . rawurlencode( $item['url'] ) ); ?>" target="_blank" rel="noopener" class="tv-share-btn wa" aria-label="<?php esc_attr_e( 'Share on WhatsApp', 'elonix' ); ?>">W</a>
				</div>
				<?php
				break;
		}
	}

	/**
	 * Renders individual meta line items.
	 *
	 * @param string $type     Meta type key.
	 * @param array  $item     Post data array.
	 * @param array  $settings Widget settings.
	 * @param array  $block    Repeater block settings.
	 */
	protected static function render_meta_item( $type, $item, $settings = array(), $block = array() ) {
		$icon_position = ! empty( $block['icon_position'] ) ? $block['icon_position'] : 'before';
		$class_suffix  = ( 'after' === $icon_position ) ? ' tv-icon-pos-after' : '';
		if ( ! empty( $block['_id'] ) ) {
			$class_suffix .= ' elementor-repeater-item-' . $block['_id'];
		}

		switch ( $type ) {
			case 'author':
				$icon_markup = self::get_icon_markup( $settings, 'meta_author_icon' );
				$text_markup = sprintf( '<a href="%s">%s</a>', esc_url( $item['author_url'] ), esc_html( $item['author_name'] ) );
				printf(
					'<span class="tv-meta-item tv-meta-author%s">%s</span>',
					esc_attr( $class_suffix ),
					'after' === $icon_position ? $text_markup . $icon_markup : $icon_markup . $text_markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
				break;

			case 'date':
				$icon_markup = self::get_icon_markup( $settings, 'meta_date_icon' );
				$text_markup = esc_html( $item['date'] );
				printf(
					'<span class="tv-meta-item tv-meta-date%s">%s</span>',
					esc_attr( $class_suffix ),
					'after' === $icon_position ? $text_markup . $icon_markup : $icon_markup . $text_markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
				break;

			case 'modified_date':
				$icon_markup = self::get_icon_markup( $settings, 'meta_date_icon' );
				$text_markup = esc_html( $item['updated_date'] );
				printf(
					'<span class="tv-meta-item tv-meta-modified-date%s">%s</span>',
					esc_attr( $class_suffix ),
					'after' === $icon_position ? $text_markup . $icon_markup : $icon_markup . $text_markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
				break;

			case 'comments':
				$icon_markup = self::get_icon_markup( $settings, 'meta_comments_icon' );
				$text_markup = sprintf(
					'<a href="%s#comments">%s</a>',
					esc_url( $item['url'] ),
					/* translators: %d: Number of comments */
					sprintf( esc_html( _n( '%d Comment', '%d Comments', $item['comments'], 'elonix' ) ), intval( $item['comments'] ) )
				);
				printf(
					'<span class="tv-meta-item tv-meta-comments%s">%s</span>',
					esc_attr( $class_suffix ),
					'after' === $icon_position ? $text_markup . $icon_markup : $icon_markup . $text_markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
				break;

			case 'reading_time':
				$icon_markup = self::get_icon_markup( $settings, 'meta_reading_time_icon' );
				/* translators: %s: Estimated reading time in minutes. */
				$text_markup = sprintf( esc_html__( '%s min read', 'elonix' ), intval( $item['reading_time'] ) );
				printf(
					'<span class="tv-meta-item tv-meta-reading%s">%s</span>',
					esc_attr( $class_suffix ),
					'after' === $icon_position ? $text_markup . $icon_markup : $icon_markup . $text_markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
				break;

			case 'views':
				$icon_markup = self::get_icon_markup( $settings, 'meta_views_icon' );
				/* translators: %s: Number of post views. */
				$text_markup = sprintf( esc_html__( '%s views', 'elonix' ), number_format_i18n( $item['views'] ) );
				printf(
					'<span class="tv-meta-item tv-meta-views%s">%s</span>',
					esc_attr( $class_suffix ),
					'after' === $icon_position ? $text_markup . $icon_markup : $icon_markup . $text_markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
				break;
		}
	}

	/**
	 * Renders dynamic smart badge labels (New, Popular, Trending, Sponsored).
	 *
	 * @param array $item     Post data array.
	 * @param array $settings Widget settings.
	 */
	protected static function render_smart_badges( $item, $settings ) {
		$badge_list = array();

		$show_new       = isset( $settings['show_new_badge'] ) ? $settings['show_new_badge'] : 'no';
		$show_popular   = isset( $settings['show_popular_badge'] ) ? $settings['show_popular_badge'] : 'no';
		$show_trending  = isset( $settings['show_trending_badge'] ) ? $settings['show_trending_badge'] : 'no';
		$show_sponsored = isset( $settings['show_sponsored_label'] ) ? $settings['show_sponsored_label'] : 'no';

		if ( 'yes' === $show_new && ! empty( $item['is_new'] ) ) {
			$badge_list[] = '<span class="tv-sbadge tv-sbadge-new">' . esc_html__( 'New', 'elonix' ) . '</span>';
		}
		if ( 'yes' === $show_popular && ! empty( $item['is_popular'] ) ) {
			$badge_list[] = '<span class="tv-sbadge tv-sbadge-popular">' . esc_html__( 'Popular', 'elonix' ) . '</span>';
		}
		if ( 'yes' === $show_trending && ! empty( $item['is_trending'] ) ) {
			$badge_list[] = '<span class="tv-sbadge tv-sbadge-trending">' . esc_html__( 'Trending', 'elonix' ) . '</span>';
		}
		if ( 'yes' === $show_sponsored && ! empty( $item['is_sponsored'] ) ) {
			$badge_list[] = '<span class="tv-sbadge tv-sbadge-sponsored">' . esc_html__( 'Sponsored', 'elonix' ) . '</span>';
		}

		if ( ! empty( $badge_list ) ) {
			printf(
				'<div class="tv-post-block-smart-badges">%s</div>',
				implode( '', $badge_list ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
		}
	}

	/**
	 * Render a standard Elementor icon.
	 *
	 * Public alias used by Elonix_Toolkit_Post_Block_Widget::get_icon_markup().
	 *
	 * @param array  $settings Widget settings.
	 * @param string $icon_key Setting key that holds the icon array.
	 * @return string Icon HTML markup or empty string.
	 */
	public static function render_icon_markup( $settings, $icon_key ) {
		return self::get_icon_markup( $settings, $icon_key );
	}

	/**
	 * Internal icon rendering helper.
	 *
	 * SAFE to call from AJAX: Icons_Manager::render_icon() is a WordPress
	 * function that does NOT require Elementor to be fully booted.
	 * It is loaded by Elementor's early bootstrap (elementor/loaded) and is
	 * available as soon as the Elementor plugin file is included.
	 *
	 * When called from a non-Elementor context (e.g. front-end AJAX) where
	 * Icons_Manager may not exist, it gracefully returns an empty string.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $icon_key Setting key that holds the icon array.
	 * @return string Icon HTML markup or empty string.
	 */
	protected static function get_icon_markup( $settings, $icon_key ) {
		if ( isset( $settings[ $icon_key ] ) && ! empty( $settings[ $icon_key ]['value'] )
			&& class_exists( '\Elementor\Icons_Manager' )
		) {
			ob_start();
			echo '<span class="tv-post-block-icon">';
			\Elementor\Icons_Manager::render_icon( $settings[ $icon_key ], array( 'aria-hidden' => 'true' ) );
			echo '</span>';
			return ob_get_clean();
		}
		return '';
	}
}
