<?php
/**
 * Elonix – Toolkit for Elementor Dynamic Data Tag Base Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class Elonix_Dynamic_Data_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

	/**
	 * Get group.
	 *
	 * @return array
	 */
	public function get_group() {
		return array( 'elonix-post' );
	}

	/**
	 * Get categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
	}

	/**
	 * Get centralized Dynamic Data Engine.
	 *
	 * @return \Elonix_Dynamic_Data
	 */
	protected function get_dynamic_data() {
		return \Elonix_Dynamic_Data::instance();
	}

	/**
	 * Get post from context.
	 *
	 * @return \WP_Post|null
	 */
	protected function get_post() {
		return $this->get_dynamic_data()->get_current_post();
	}
}
