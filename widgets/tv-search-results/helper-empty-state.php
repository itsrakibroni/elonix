<?php
/**
 * Elonix Search Results empty state helper.
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Renders premium no-results recovery UI.
 */
class Elonix_Toolkit_Search_Results_Empty_State_Helper {

	/**
	 * Render empty-state UI.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $keyword  Current search keyword.
	 */
	public static function render( $settings, $keyword ) {
		$title        = ! empty( $settings['empty_title'] ) ? $settings['empty_title'] : esc_html__( 'Nothing Found', 'elonix' );
		$desc         = ! empty( $settings['empty_description'] ) ? $settings['empty_description'] : esc_html__( "Sorry, we couldn't find anything matching your search.", 'elonix' );
		$button_text  = ! empty( $settings['empty_search_button_text'] ) ? $settings['empty_search_button_text'] : esc_html__( 'Search Again', 'elonix' );
		$show_home    = ! empty( $settings['show_home_button'] ) && 'yes' === $settings['show_home_button'];
		$show_support = ! empty( $settings['show_support_button'] ) && 'yes' === $settings['show_support_button'];
		?>
		<section class="tv-search-results-empty" aria-labelledby="tv-search-results-empty-title">
			<div class="tv-search-results-empty-visual" aria-hidden="true">
				<?php if ( ! empty( $settings['empty_lottie_url']['url'] ) ) : ?>
					<lottie-player class="tv-search-results-empty-lottie" src="<?php echo esc_url( $settings['empty_lottie_url']['url'] ); ?>" background="transparent" speed="1" loop autoplay></lottie-player>
				<?php else : ?>
					<span class="tv-search-results-empty-icon">
						<svg viewBox="0 0 64 64" focusable="false" role="img">
							<path d="M28 6a21 21 0 0 0-8.7 40.1l-5.5 5.5a4 4 0 0 0 5.7 5.7l5.9-5.9A21 21 0 1 0 28 6Zm0 8a13 13 0 1 1 0 26 13 13 0 0 1 0-26Zm0 7a3 3 0 0 1 3 3v5a3 3 0 0 1-6 0v-5a3 3 0 0 1 3-3Zm0 13.5a3.2 3.2 0 1 1 0 6.4 3.2 3.2 0 0 1 0-6.4Z"></path>
						</svg>
					</span>
				<?php endif; ?>
			</div>
			<div class="tv-search-results-empty-content">
				<h2 id="tv-search-results-empty-title" class="tv-search-results-empty-title"><?php echo esc_html( $title ); ?></h2>
				<p class="tv-search-results-empty-desc"><?php echo esc_html( $desc ); ?></p>

				<form class="tv-search-results-empty-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label class="screen-reader-text" for="tv-search-results-empty-field"><?php esc_html_e( 'Search for', 'elonix' ); ?></label>
					<input id="tv-search-results-empty-field" type="search" name="s" value="<?php echo esc_attr( $keyword ); ?>" placeholder="<?php esc_attr_e( 'Try another keyword', 'elonix' ); ?>">
					<button type="submit"><?php echo esc_html( $button_text ); ?></button>
				</form>

				<?php self::render_suggestions( $settings, $keyword ); ?>

				<div class="tv-search-results-empty-actions">
					<?php if ( $show_home ) : ?>
						<a class="tv-search-results-home" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return Home', 'elonix' ); ?></a>
					<?php endif; ?>
					<?php if ( $show_support && ! empty( $settings['support_url']['url'] ) ) : ?>
						<a class="tv-search-results-support" href="<?php echo esc_url( $settings['support_url']['url'] ); ?>"><?php esc_html_e( 'Contact Support', 'elonix' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * Render suggestions and browse links.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $keyword  Current keyword.
	 */
	private static function render_suggestions( $settings, $keyword ) {
		$show_suggestions = ! empty( $settings['show_suggestions'] ) && 'yes' === $settings['show_suggestions'];
		if ( ! $show_suggestions ) {
			return;
		}

		$suggestions = class_exists( 'Elonix_Toolkit_Search_Results_Query_Helper' ) ? Elonix_Toolkit_Search_Results_Query_Helper::get_keyword_suggestions( $keyword ) : array();
		$categories  = get_categories(
			array(
				'number'     => 6,
				'hide_empty' => true,
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);
		$tags        = get_tags(
			array(
				'number'     => 6,
				'hide_empty' => true,
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);
		$recent      = get_posts(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => 4,
				'ignore_sticky_posts' => true,
			)
		);
		$trending    = get_posts(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => 4,
				'orderby'             => 'comment_count',
				'order'               => 'DESC',
				'ignore_sticky_posts' => true,
			)
		);
		?>
		<div class="tv-search-results-suggestions" aria-label="<?php esc_attr_e( 'Search suggestions', 'elonix' ); ?>">
			<?php if ( ( ! isset( $settings['show_popular_searches'] ) || 'yes' === $settings['show_popular_searches'] ) && ! empty( $suggestions ) ) : ?>
				<div class="tv-search-results-suggestion-group">
					<h3><?php esc_html_e( 'Popular Searches', 'elonix' ); ?></h3>
					<ul>
						<?php foreach ( array_slice( $suggestions, 0, 6 ) as $suggestion ) : ?>
							<li><a href="<?php echo esc_url( add_query_arg( 's', rawurlencode( $suggestion ), home_url( '/' ) ) ); ?>"><?php echo esc_html( $suggestion ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( ( ! isset( $settings['show_suggested_categories'] ) || 'yes' === $settings['show_suggested_categories'] ) && ! empty( $categories ) ) : ?>
				<div class="tv-search-results-suggestion-group">
					<h3><?php esc_html_e( 'Popular Categories', 'elonix' ); ?></h3>
					<ul>
						<?php foreach ( $categories as $category ) : ?>
							<li><a href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( ( ! isset( $settings['show_suggested_tags'] ) || 'yes' === $settings['show_suggested_tags'] ) && ! empty( $tags ) ) : ?>
				<div class="tv-search-results-suggestion-group">
					<h3><?php esc_html_e( 'Suggested Tags', 'elonix' ); ?></h3>
					<ul>
						<?php foreach ( $tags as $tag ) : ?>
							<li><a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>"><?php echo esc_html( $tag->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( ( ! isset( $settings['show_recent_posts'] ) || 'yes' === $settings['show_recent_posts'] ) && ! empty( $recent ) ) : ?>
				<div class="tv-search-results-suggestion-group">
					<h3><?php esc_html_e( 'Recent Posts', 'elonix' ); ?></h3>
					<ul>
						<?php foreach ( $recent as $post ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( ( ! isset( $settings['show_trending_posts'] ) || 'yes' === $settings['show_trending_posts'] ) && ! empty( $trending ) ) : ?>
				<div class="tv-search-results-suggestion-group">
					<h3><?php esc_html_e( 'Trending Posts', 'elonix' ); ?></h3>
					<ul>
						<?php foreach ( $trending as $post ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
