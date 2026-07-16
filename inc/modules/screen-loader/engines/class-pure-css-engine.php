<?php
namespace Elonix_Toolkit\Modules\Screen_Loader\Engines;

use Elonix_Toolkit\Modules\Screen_Loader\Interfaces\Loader_Engine_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Pure_CSS_Engine implements Loader_Engine_Interface {
	/**
	 * @var string
	 */
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
		$style = ! empty( $settings['style'] ) ? $settings['style'] : 'default';

		if ( 'default' === $style ) {
			return '
			<div class="tv-screen-loader tv-screen-loader--default">
				<button class="tv-screen-loader__close" type="button" aria-label="' . esc_attr__( 'Close Loader', 'elonix' ) . '"></button>
				<div class="tv-screen-loader__spinner">
					<span class="tv-screen-loader__ring"></span>
				</div>
			</div>';
		}

		// Example markup for pure CSS loaders based on their ID.
		// The CSS itself is injected by class-assets.php based on the selected type.
		$markup = sprintf( '<div class="tv-css-loader tv-loader-%s" aria-hidden="true"></div>', esc_attr( $style ) );
		return $markup;
	}
}
