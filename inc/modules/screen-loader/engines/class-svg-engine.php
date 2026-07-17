<?php
namespace Elonix_Toolkit\Modules\Screen_Loader\Engines;

use Elonix_Toolkit\Modules\Screen_Loader\Interfaces\Loader_Engine_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SVG_Engine implements Loader_Engine_Interface {
	private $id;

	public function __construct( $id ) {
		$this->id = $id;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_name() {
		return 'SVG Loader';
	}

	public function get_markup( $settings ) {
		$custom_image = ! empty( $settings['custom_image'] ) ? $settings['custom_image'] : '';

		if ( $custom_image ) {
			// Ensure it's a URL and has SVG extension
			$ext = pathinfo( wp_parse_url( $custom_image, PHP_URL_PATH ), PATHINFO_EXTENSION );
			if ( 'svg' === $ext ) {
				$transient_key = 'es_sl_svg_' . md5( $custom_image . ( defined( 'ELONIX_VERSION' ) ? ELONIX_VERSION : '1.0' ) );
				$cached_svg    = get_transient( $transient_key );

				if ( false !== $cached_svg ) {
					return $cached_svg;
				}

				$response = wp_safe_remote_get( esc_url_raw( $custom_image ) );
				if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
					$svg_content = wp_remote_retrieve_body( $response );

					// Sanitize inline SVG
					$allowed_tags = array(
						'svg'     => array(
							'xmlns'        => true,
							'viewbox'      => true,
							'width'        => true,
							'height'       => true,
							'class'        => true,
							'id'           => true,
							'style'        => true,
							'fill'         => true,
							'stroke'       => true,
							'stroke-width' => true,
						),
						'path'    => array(
							'd'      => true,
							'fill'   => true,
							'stroke' => true,
							'class'  => true,
							'id'     => true,
							'style'  => true,
						),
						'g'       => array(
							'fill'   => true,
							'stroke' => true,
							'class'  => true,
							'id'     => true,
							'style'  => true,
						),
						'circle'  => array(
							'cx'     => true,
							'cy'     => true,
							'r'      => true,
							'fill'   => true,
							'stroke' => true,
							'class'  => true,
							'id'     => true,
							'style'  => true,
						),
						'rect'    => array(
							'x'      => true,
							'y'      => true,
							'width'  => true,
							'height' => true,
							'rx'     => true,
							'ry'     => true,
							'fill'   => true,
							'stroke' => true,
							'class'  => true,
							'id'     => true,
							'style'  => true,
						),
						'polygon' => array(
							'points' => true,
							'fill'   => true,
							'stroke' => true,
							'class'  => true,
							'id'     => true,
							'style'  => true,
						),
						'line'    => array(
							'x1'     => true,
							'y1'     => true,
							'x2'     => true,
							'y2'     => true,
							'stroke' => true,
							'class'  => true,
							'id'     => true,
							'style'  => true,
						),
					);

					$clean_svg = wp_kses( $svg_content, $allowed_tags );
					if ( trim( $clean_svg ) !== '' ) {
						$markup = '<div class="es-svg-loader" aria-hidden="true">' . $clean_svg . '</div>';
						set_transient( $transient_key, $markup, WEEK_IN_SECONDS );
						return $markup;
					}
				}
			}
		}

		// Fallback to default inline SVG loader
		return '<div class="es-svg-loader" aria-hidden="true"><svg class="es-circular-svg" viewBox="25 25 50 50"><circle class="es-path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/></svg></div>';
	}
}
