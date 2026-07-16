<?php
namespace Elonix_Toolkit\Modules\Screen_Loader\Engines;

use Elonix_Toolkit\Modules\Screen_Loader\Interfaces\Loader_Engine_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Hybrid_Engine implements Loader_Engine_Interface {
	private $id;

	public function __construct( $id ) {
		$this->id = $id;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_name() {
		return ucwords( str_replace( '-', ' ', $this->id ) );
	}

	public function get_markup( $settings ) {
		// Custom image URL from settings, or fallback
		$image_url = ! empty( $settings['custom_image'] ) ? esc_url( $settings['custom_image'] ) : '';
		
		if ( ! $image_url ) {
			// Fallback to text if no image
			return '<div class="tv-hybrid-loader tv-loader-' . esc_attr( $this->id ) . '" aria-hidden="true"><span>Loading...</span></div>';
		}

		$markup = sprintf(
			'<div class="tv-hybrid-loader tv-loader-%s" aria-hidden="true"><img src="%s" alt="%s"></div>',
			esc_attr( $this->id ),
			esc_url( $image_url ),
			esc_attr__( 'Loading...', 'elonix' )
		);

		return $markup;
	}
}
