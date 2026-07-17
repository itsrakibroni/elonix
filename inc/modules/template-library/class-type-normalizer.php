<?php
namespace Elonix_Toolkit\Modules\Template_Library;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Type_Normalizer {
	/**
	 * Normalizes a template type string to a canonical format.
	 *
	 * @param string $raw_type
	 * @return string Canonical template type
	 */
	public static function normalize_template_type( $raw_type ) {
		$type = strtolower( trim( sanitize_text_field( $raw_type ) ) );

		// Strip 'es_' prefix if present
		$type = preg_replace( '/^es_/', '', $type );

		// Map common aliases to canonical values
		$map = array(
			'headers'          => 'header',
			'footers'          => 'footer',
			'singles'          => 'single',
			'archives'         => 'archive',
			'search_template'  => 'search',
			'404_template'     => 'error-404',
			'error_404'        => 'error-404',
			'404'              => 'error-404',
			'popups'           => 'popup',
			'loops'            => 'loop',
		);

		if ( isset( $map[ $type ] ) ) {
			return $map[ $type ];
		}

		return $type;
	}
}
