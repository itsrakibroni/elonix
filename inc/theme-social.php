<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output social links list.
 *
 * @package Elonix
 * @since 1.0.0
 */

if ( ! function_exists( 'elonix_social_link' ) ) :

	function elonix_social_link() {

		// Target attribute.
		$target = Elonix_Settings::get( 'elonix_social_target' ) ?? '_blank';

		// Social networks map: option_key => icon_class.
		$social_links = array(
			'facebook_url'   => 'fab fa-facebook-f',
			'twitter_url'    => 'fab fa-x-twitter',
			'instagram_url'  => 'fab fa-instagram',
			'linkedin_url'   => 'fa-brands fa-linkedin-in',
			'youtube_url'    => 'fa-brands fa-youtube',
			'pinterest_url'  => 'fa-brands fa-pinterest-p',
			'tiktok_url'     => 'fa-brands fa-tiktok',
			'github_url'     => 'fa-brands fa-github',
			'behance_url'    => 'fa-brands fa-behance',
			'dribbble_url'   => 'fa-brands fa-dribbble',
			'whatsapp_url'   => 'fa-brands fa-whatsapp',
			'telegram_url'   => 'fa-brands fa-telegram-plane',
		);

		foreach ( $social_links as $option_key => $icon_class ) {

			$url = Elonix_Settings::get( $option_key ) ?? '';

			if ( empty( $url ) ) {
				continue;
			}
			?>
			<li>
				<a class="d-block <?php echo esc_attr( str_replace( '_url', '', $option_key ) ); ?>"
				   target="<?php echo esc_attr( $target ); ?>"
				   href="<?php echo esc_url( $url ); ?>">
					<i class="<?php echo esc_attr( $icon_class ); ?>"></i>
				</a>
			</li>
			<?php
		}
	}

endif;
