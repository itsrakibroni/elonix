<?php
namespace Elonix_Toolkit\Modules\Screen_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Settings {
	/**
	 * Get module settings.
	 *
	 * @return array
	 */
	public function get_settings() {
		$global_options = get_option( ELONIX_FRAMEWORK_VAR, array() );
		$toolkit_options = get_option( 'elonix_settings', array() );
		$toolkit_sl      = isset( $toolkit_options['screen_loader'] ) ? $toolkit_options['screen_loader'] : array();

		// Defaults for locked architecture
		$defaults = array(
			'screen_loader_enable'       => false,
			'screen_loader_engine'       => 'pure-css',
			'screen_loader_style'        => 'default',
			'screen_loader_type'         => '', // Legacy support
			'screen_loader_color'        => '#000000',
			'screen_loader_color_alt'    => '#cccccc',
			'screen_loader_bg'           => '#ffffff',
			'screen_loader_opacity'      => '1',
			'screen_loader_size'         => '150px',
			'screen_loader_animation'    => 'fade', // fade, slide-up, etc.
			'screen_loader_speed'        => '0.5s',
			'screen_loader_timeout'      => 5000,
			'screen_loader_once'         => false,
			'screen_loader_zindex'       => 999999,
			'screen_loader_custom_image' => '',
			'screen_loader_enable_escape'=> true,
			'screen_loader_custom_class' => '',
		);

		$settings = array();
		foreach ( $defaults as $key => $default ) {
			$short_key = str_replace( 'screen_loader_', '', $key );

			if ( isset( $toolkit_sl[ $short_key ] ) && '' !== $toolkit_sl[ $short_key ] ) {
				$settings[ $short_key ] = $toolkit_sl[ $short_key ];
			} elseif ( isset( $global_options[ $key ] ) && '' !== $global_options[ $key ] ) {
				$settings[ $short_key ] = $global_options[ $key ];
			} else {
				$settings[ $short_key ] = $default;
			}
		}

		// Backward Compatibility Migration (In-Memory)
		if ( ! empty( $settings['type'] ) && empty( $toolkit_sl['engine'] ) && empty( $global_options['screen_loader_engine'] ) ) {
			$legacy_type = $settings['type'];
			$is_pure_css = ! in_array( $legacy_type, array( 'svg', 'logo', 'image' ), true );
			
			if ( $is_pure_css ) {
				$settings['engine'] = 'pure-css';
				$settings['style']  = $legacy_type;
			} else {
				$settings['engine'] = $legacy_type;
				$settings['style']  = 'default';
			}
		}

		return $settings;
	}
}
