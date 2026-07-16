<?php
/**
 * Elonix Dynamic Style Conditions
 * 
 * Passive logic engine to determine if widget data requirements are met
 * DURING FRONTEND RENDERING ONLY.
 * 
 * NEVER use this class during register_controls().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Style_Conditions {

	/**
	 * Check if a top-level setting has a value.
	 */
	public static function has_value( array $settings, $key ) {
		return ! empty( $settings[ $key ] );
	}

	/**
	 * Check if layout mode matches.
	 */
	public static function layout_is( array $settings, $layout ) {
		return isset( $settings['layout_mode'] ) && $settings['layout_mode'] === $layout;
	}

	/**
	 * Check if a switcher is enabled.
	 */
	public static function switcher_enabled( array $settings, $key ) {
		return isset( $settings[ $key ] ) && 'yes' === $settings[ $key ];
	}

	/**
	 * Check if any item in a repeater has a value for a specific key.
	 */
	public static function has_repeater_value( array $settings, $repeater_id, $key ) {
		if ( empty( $settings[ $repeater_id ] ) || ! is_array( $settings[ $repeater_id ] ) ) {
			return false;
		}

		foreach ( $settings[ $repeater_id ] as $item ) {
			if ( isset( $item[ $key ] ) ) {
				$val = $item[ $key ];
				if ( is_array( $val ) ) {
					// Check Media/Image Array
					if ( isset( $val['url'] ) && ! empty( $val['url'] ) ) {
						return true;
					}
					// Check Icon Array
					if ( isset( $val['value'] ) && ! empty( $val['value'] ) ) {
						return true;
					}
				} else {
					// Check standard text/string
					if ( ! empty( $val ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	public static function has_media( array $settings, $target = '' ) {
		if ( empty( $target ) ) {
			return self::has_value( $settings, 'image' ) || self::has_repeater_value( $settings, 'cards', 'image' );
		}
		return self::has_repeater_value( $settings, $target, 'image' );
	}

	public static function has_icon( array $settings, $target = '' ) {
		if ( empty( $target ) ) {
			return self::has_value( $settings, 'icon' ) || self::has_repeater_value( $settings, 'cards', 'icon' );
		}
		return self::has_repeater_value( $settings, $target, 'icon' );
	}
}
