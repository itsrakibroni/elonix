<?php
namespace Elonix_Toolkit\Modules\Screen_Loader\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

interface Loader_Engine_Interface {
	/**
	 * Get engine ID.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Get engine human-readable name.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get engine HTML markup.
	 *
	 * @param array $settings Module settings.
	 * @return string
	 */
	public function get_markup( $settings );
}
