<?php
/**
 * Elonix Search Results render helper.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Shared card and chrome renderer.
 */
class Elonix_Toolkit_Search_Results_Render_Helper {

	/**
	 * Render stats header.
	 *
	 * @param array $data     Query data.
	 * @param array $settings Widget settings.
	 */
	public static function render_stats( $data, $settings ) {
		if ( empty( $settings['show_stats'] ) || 'yes' !== $settings['show_stats'] ) {
			return;
		}

		$keyword          = ! empty( $data['keyword'] ) ? $data['keyword'] : '';
		$show_total       = ! isset( $settings['show_stat_total'] ) || 'yes' === $settings['show_stat_total'];
		$show_keyword     = ! isset( $settings['show_stat_keyword'] ) || 'yes' === $settings['show_stat_keyword'];
		$show_time        = ! isset( $settings['show_stat_time'] ) || 'yes' === $settings['show_stat_time'];
		$show_current     = ! isset( $settings['show_stat_current'] ) || 'yes' === $settings['show_stat_current'];
		$show_total_pages = ! isset( $settings['show_stat_total_pages'] ) || 'yes' === $settings['show_stat_total_pages'];
		?>
		<header class="es-search-results-stats">
			<div>
				<?php if ( $show_total ) : ?>
					<strong>
						<?php
						printf(
							/* translators: %d: Number of found search results. */
							esc_html( _n( '%d Result Found', '%d Results Found', intval( $data['found_posts'] ), 'elonix' ) ),
							intval( $data['found_posts'] )
						);
						?>
					</strong>
				<?php endif; ?>
				<?php if ( $show_keyword && '' !== $keyword ) : ?>
					<span><?php esc_html_e( 'Showing results for', 'elonix' ); ?> <em>"<?php echo esc_html( $keyword ); ?>"</em></span>
				<?php endif; ?>
			</div>
			<ul aria-label="<?php esc_attr_e( 'Search statistics', 'elonix' ); ?>">
				<?php if ( $show_current || $show_total_pages ) : ?>
					<li>
						<?php
						if ( $show_current && $show_total_pages ) {
							/* translators: 1: Current page number, 2: Total number of pages. */
							printf( esc_html__( 'Page %1$d of %2$d', 'elonix' ), intval( $data['paged'] ), esc_html( max( 1, intval( $data['max_pages'] ) ) ) );
						} elseif ( $show_current ) {
							/* translators: %d: Current page number. */
							printf( esc_html__( 'Page %d', 'elonix' ), intval( $data['paged'] ) );
						} else {
							/* translators: %d: Total number of pages. */
							printf( esc_html__( '%d Pages', 'elonix' ), esc_html( max( 1, intval( $data['max_pages'] ) ) ) );
						}
						?>
					</li>
				<?php endif; ?>
				<?php if ( $show_time ) : ?>
					<li>
						<?php
						/* translators: %s: Query elapsed time in seconds. */
						printf( esc_html__( '%s sec', 'elonix' ), esc_html( number_format_i18n( $data['elapsed'], 3 ) ) );
						?>
					</li>
				<?php endif; ?>
			</ul>
		</header>
		<?php
	}

	/**
	 * Render filters sidebar.
	 *
	 * @param array $settings Widget settings.
	 */
	public static function render_filters( $settings ) {
		if ( empty( $settings['show_filter_sidebar'] ) || 'yes' !== $settings['show_filter_sidebar'] ) {
			return;
		}
		?>
		<aside class="es-search-results-sidebar" aria-label="<?php esc_attr_e( 'Search result filters', 'elonix' ); ?>">
			<button type="button" class="es-search-results-filter-toggle" aria-expanded="false">
				<?php esc_html_e( 'Filters', 'elonix' ); ?>
			</button>
			<div class="es-search-results-filter-panel">
				<label>
					<span><?php esc_html_e( 'Post Type', 'elonix' ); ?></span>
					<select class="es-search-results-filter" data-filter="post_type">
						<option value=""><?php esc_html_e( 'All', 'elonix' ); ?></option>
						<?php foreach ( Elonix_Toolkit_Search_Results_Query_Helper::get_post_type_options() as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					<span><?php esc_html_e( 'Date', 'elonix' ); ?></span>
					<select class="es-search-results-filter" data-filter="date_filter">
						<option value="all"><?php esc_html_e( 'Any time', 'elonix' ); ?></option>
						<option value="day"><?php esc_html_e( 'Past day', 'elonix' ); ?></option>
						<option value="week"><?php esc_html_e( 'Past week', 'elonix' ); ?></option>
						<option value="month"><?php esc_html_e( 'Past month', 'elonix' ); ?></option>
						<option value="year"><?php esc_html_e( 'Past year', 'elonix' ); ?></option>
					</select>
				</label>
			</div>
		</aside>
		<?php
	}

	/**
	 * Render a result card.
	 *
	 * @param array $item     Post data.
	 * @param array $settings Widget settings.
	 * @param int   $index    Result index.
	 */
	public static function render_card( $item, $settings, $index = 0 ) {
		$layout  = ! empty( $settings['layout'] ) ? sanitize_key( $settings['layout'] ) : 'classic_grid';
		$classes = array( 'es-search-results-card', 'es-search-results-card-' . $layout );

		if ( 0 === $index ) {
			$classes[] = 'is-first-result';
		}
		?>
		<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php
			if ( class_exists( 'Elonix_Toolkit_Edit_Overlay' ) ) {
				Elonix_Toolkit_Edit_Overlay::render( $item['id'] );
			}
			?>
			<?php
			$element_order = self::get_element_order( $settings );
			$content_open  = false;

			foreach ( $element_order as $block ) {
				if ( isset( $block['show_element'] ) && 'yes' !== $block['show_element'] ) {
					continue;
				}

				$type = ! empty( $block['element_type'] ) ? sanitize_key( $block['element_type'] ) : '';
				if ( '' === $type ) {
					continue;
				}

				if ( 'featured_image' === $type ) {
					self::render_element_block( $type, $item, $settings, $block );
					continue;
				}

				if ( ! $content_open ) {
					echo '<div class="es-search-results-card-content">';
					$content_open = true;
				}
				self::render_element_block( $type, $item, $settings, $block );
			}

			if ( $content_open ) {
				echo '</div>';
			}
			?>
		</article>
		<?php
	}

	/**
	 * Resolve sortable element order with legacy switch fallbacks.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	private static function get_element_order( $settings ) {
		$order = ! empty( $settings['search_result_element_order'] ) && is_array( $settings['search_result_element_order'] ) ? $settings['search_result_element_order'] : array();
		if ( empty( $order ) ) {
			$order = array(
				array(
					'element_type' => 'featured_image',
					'show_element' => self::legacy_enabled( $settings, 'show_image' ),
				),
				array(
					'element_type' => 'category',
					'show_element' => self::legacy_enabled( $settings, 'show_category' ),
				),
				array(
					'element_type' => 'title',
					'show_element' => self::legacy_enabled( $settings, 'show_title' ),
				),
				array(
					'element_type' => 'meta',
					'show_element' => self::legacy_enabled( $settings, 'show_meta' ),
				),
				array(
					'element_type' => 'excerpt',
					'show_element' => self::legacy_enabled( $settings, 'show_excerpt' ),
				),
				array(
					'element_type' => 'tags',
					'show_element' => self::legacy_enabled( $settings, 'show_tags' ),
				),
				array(
					'element_type' => 'button',
					'show_element' => self::legacy_enabled( $settings, 'show_button' ),
				),
			);
		}

		$seen = array();
		$out  = array();
		foreach ( $order as $block ) {
			$type = ! empty( $block['element_type'] ) ? sanitize_key( $block['element_type'] ) : '';
			if ( '' === $type || isset( $seen[ $type ] ) ) {
				continue;
			}
			$seen[ $type ] = true;
			$out[]         = $block;
		}

		return $out;
	}

	/**
	 * Convert legacy switches to builder switch values.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $key      Setting key.
	 * @return string
	 */
	private static function legacy_enabled( $settings, $key ) {
		return ! isset( $settings[ $key ] ) || 'yes' === $settings[ $key ] ? 'yes' : 'no';
	}

	/**
	 * Render one sortable element.
	 *
	 * @param string $type     Element type.
	 * @param array  $item     Post data.
	 * @param array  $settings Widget settings.
	 * @param array  $block    Builder row.
	 */
	private static function render_element_block( $type, $item, $settings, $block ) {
		$title_tag = self::sanitize_title_tag( isset( $settings['title_tag'] ) ? $settings['title_tag'] : 'h3' );

		switch ( $type ) {
			case 'featured_image':
				if ( empty( $settings['show_image'] ) || 'yes' !== $settings['show_image'] || empty( $item['thumbnail'] ) ) {
					return;
				}
				?>
				<a class="es-search-results-thumb" href="<?php echo esc_url( $item['url'] ); ?>" tabindex="-1" aria-hidden="true">
					<?php echo $item['thumbnail']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress image markup. ?>
				</a>
				<?php
				break;

			case 'category':
				if ( empty( $settings['show_category'] ) || 'yes' !== $settings['show_category'] || empty( $item['categories'] ) ) {
					return;
				}
				?>
				<div class="es-search-results-terms">
					<?php foreach ( array_slice( $item['categories'], 0, 2 ) as $term ) : ?>
						<a href="<?php echo esc_url( $term['url'] ); ?>"><?php echo esc_html( $term['name'] ); ?></a>
					<?php endforeach; ?>
				</div>
				<?php
				break;

			case 'badge':
				$badge_text = ! empty( $block['badge_text'] ) ? $block['badge_text'] : esc_html__( 'Featured', 'elonix' );
				echo '<span class="es-search-results-badge">' . esc_html( $badge_text ) . '</span>';
				break;

			case 'post_type':
				echo '<span class="es-search-results-post-type">' . esc_html( $item['post_type_name'] ) . '</span>';
				break;

			case 'author':
			case 'date':
			case 'reading_time':
			case 'comments':
				echo '<div class="es-search-results-meta-single">';
				self::render_meta_item( $type, $item );
				echo '</div>';
				break;

			case 'title':
				if ( empty( $settings['show_title'] ) || 'yes' !== $settings['show_title'] ) {
					return;
				}
				?>
				<<?php echo esc_attr( $title_tag ); ?> class="es-search-results-title">
					<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo wp_kses( $item['highlighted'], array( 'mark' => array( 'class' => true ) ) ); ?></a>
				</<?php echo esc_attr( $title_tag ); ?>>
				<?php
				break;

			case 'excerpt':
				if ( empty( $settings['show_excerpt'] ) || 'yes' !== $settings['show_excerpt'] || empty( $item['excerpt'] ) ) {
					return;
				}
				echo '<p class="es-search-results-excerpt">' . esc_html( $item['excerpt'] ) . '</p>';
				break;

			case 'meta':
				if ( empty( $settings['show_meta'] ) || 'yes' !== $settings['show_meta'] ) {
					return;
				}
				self::render_meta( $item, $settings );
				break;

			case 'tags':
				if ( empty( $settings['show_tags'] ) || 'yes' !== $settings['show_tags'] || empty( $item['tags'] ) ) {
					return;
				}
				?>
				<div class="es-search-results-tags">
					<?php foreach ( array_slice( $item['tags'], 0, 3 ) as $term ) : ?>
						<a href="<?php echo esc_url( $term['url'] ); ?>">#<?php echo esc_html( $term['name'] ); ?></a>
					<?php endforeach; ?>
				</div>
				<?php
				break;

			case 'button':
				if ( empty( $settings['show_button'] ) || 'yes' !== $settings['show_button'] ) {
					return;
				}
				?>
				<a class="es-search-results-button" href="<?php echo esc_url( $item['url'] ); ?>">
					<?php
					echo esc_html( ! empty( $settings['button_text'] ) ? $settings['button_text'] : esc_html__( 'Read More', 'elonix' ) );
					?>
				</a>
				<?php
				break;

			case 'rating':
				if ( empty( $item['rating'] ) ) {
					return;
				}
				printf(
					'<span class="es-search-results-rating" aria-label="%1$s">%2$s <strong>%3$s</strong></span>',
					esc_attr__( 'Post rating', 'elonix' ),
					esc_html__( 'Rating', 'elonix' ),
					esc_html( number_format_i18n( (float) $item['rating'], 1 ) )
				);
				break;
		}
	}

	/**
	 * Render meta line.
	 *
	 * @param array $item     Post data.
	 * @param array $settings Widget settings.
	 */
	private static function render_meta( $item, $settings ) {
		$meta = ! empty( $settings['meta_items'] ) && is_array( $settings['meta_items'] ) ? $settings['meta_items'] : array( 'author', 'date', 'reading_time' );
		?>
		<div class="es-search-results-meta">
			<?php if ( in_array( 'post_type', $meta, true ) ) : ?>
				<span><?php echo esc_html( $item['post_type_name'] ); ?></span>
			<?php endif; ?>
			<?php if ( in_array( 'author', $meta, true ) ) : ?>
				<span><a href="<?php echo esc_url( $item['author_url'] ); ?>"><?php echo esc_html( $item['author_name'] ); ?></a></span>
			<?php endif; ?>
			<?php if ( in_array( 'date', $meta, true ) ) : ?>
				<span><?php echo esc_html( $item['date'] ); ?></span>
			<?php endif; ?>
			<?php if ( in_array( 'reading_time', $meta, true ) ) : ?>
				<span>
					<?php
					/* translators: %d: Estimated reading time in minutes. */
					printf( esc_html__( '%d min read', 'elonix' ), intval( $item['reading_time'] ) );
					?>
				</span>
			<?php endif; ?>
			<?php if ( in_array( 'comments', $meta, true ) ) : ?>
				<span>
					<?php
					printf(
						/* translators: %d: Number of post comments. */
						esc_html( _n( '%d Comment', '%d Comments', intval( $item['comments'] ), 'elonix' ) ),
						intval( $item['comments'] )
					);
					?>
				</span>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render one standalone meta item.
	 *
	 * @param string $type Meta type.
	 * @param array  $item Post data.
	 */
	private static function render_meta_item( $type, $item ) {
		switch ( $type ) {
			case 'author':
				printf(
					'<span class="es-search-results-meta-item es-search-results-meta-author"><a href="%1$s">%2$s</a></span>',
					esc_url( $item['author_url'] ),
					esc_html( $item['author_name'] )
				);
				break;

			case 'date':
				echo '<span class="es-search-results-meta-item es-search-results-meta-date">' . esc_html( $item['date'] ) . '</span>';
				break;

			case 'reading_time':
				printf(
					'<span class="es-search-results-meta-item es-search-results-meta-reading">%s</span>',
					sprintf(
						/* translators: %d: Estimated reading time in minutes. */
						esc_html__( '%d min read', 'elonix' ),
						intval( $item['reading_time'] )
					)
				);
				break;

			case 'comments':
				printf(
					'<span class="es-search-results-meta-item es-search-results-meta-comments"><a href="%1$s#comments">%2$s</a></span>',
					esc_url( $item['url'] ),
					sprintf(
						/* translators: %d: Number of post comments. */
						esc_html( _n( '%d Comment', '%d Comments', intval( $item['comments'] ), 'elonix' ) ),
						intval( $item['comments'] )
					)
				);
				break;
		}
	}

	/**
	 * Render pagination controls.
	 *
	 * @param array $data     Query data.
	 * @param array $settings Widget settings.
	 */
	public static function render_pagination( $data, $settings ) {
		$type      = ! empty( $settings['pagination_type'] ) ? sanitize_key( $settings['pagination_type'] ) : 'none';
		$max_pages = max( 1, intval( $data['max_pages'] ) );
		$paged     = max( 1, intval( $data['paged'] ) );

		if ( 'none' === $type || $max_pages <= 1 ) {
			return;
		}

		?>
		<nav class="es-search-results-pagination es-search-results-pagination-<?php echo esc_attr( $type ); ?>" aria-label="<?php esc_attr_e( 'Search results pagination', 'elonix' ); ?>">
			<?php if ( 'load_more' === $type || 'infinite_scroll' === $type ) : ?>
				<button type="button" class="es-search-results-load-more" data-next-page="<?php echo esc_attr( $paged + 1 ); ?>" <?php disabled( $paged >= $max_pages ); ?>>
					<span><?php echo esc_html( ! empty( $settings['load_more_text'] ) ? $settings['load_more_text'] : esc_html__( 'Load More', 'elonix' ) ); ?></span>
				</button>
			<?php elseif ( 'numeric' === $type || 'prev_next' === $type ) : ?>
				<?php if ( $paged > 1 ) : ?>
					<a class="es-search-results-page-link" href="<?php echo esc_url( get_pagenum_link( $paged - 1 ) ); ?>"><?php esc_html_e( 'Prev', 'elonix' ); ?></a>
				<?php endif; ?>
				<?php if ( 'numeric' === $type ) : ?>
					<?php for ( $i = 1; $i <= $max_pages; $i++ ) : ?>
						<a class="es-search-results-page-link <?php echo $i === $paged ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_pagenum_link( $i ) ); ?>" <?php echo $i === $paged ? 'aria-current="page"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed static string literal, no dynamic content. ?>>
							<?php echo esc_html( $i ); ?>
						</a>
					<?php endfor; ?>
				<?php endif; ?>
				<?php if ( $paged < $max_pages ) : ?>
					<a class="es-search-results-page-link" href="<?php echo esc_url( get_pagenum_link( $paged + 1 ) ); ?>"><?php esc_html_e( 'Next', 'elonix' ); ?></a>
				<?php endif; ?>
			<?php endif; ?>
		</nav>
		<?php
	}

	/**
	 * Sanitize title tag.
	 *
	 * @param string $tag Tag.
	 * @return string
	 */
	private static function sanitize_title_tag( $tag ) {
		return in_array( $tag, array( 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ), true ) ? $tag : 'h3';
	}
}
