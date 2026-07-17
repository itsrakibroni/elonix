<?php
/**
 * Elonix – Toolkit for Elementor Settings Framework Class
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Settings_Framework {

	/**
	 * Registered sections.
	 *
	 * @var array
	 */
	private static $sections = array();

	/**
	 * Registered fields grouped by section.
	 *
	 * @var array
	 */
	private static $fields = array();

	/**
	 * Register a setting tab/section.
	 *
	 * @param string $id    Section ID.
	 * @param string $title Section title.
	 */
	public static function add_section( $id, $title ) {
		self::$sections[ $id ] = $title;
	}

	/**
	 * Get registered sections.
	 *
	 * @return array Sections list.
	 */
	public static function get_sections() {
		return self::$sections;
	}

	/**
	 * Register a settings field.
	 *
	 * @param string $section_id Section ID (Tab ID).
	 * @param array  $args       Field configuration arguments.
	 */
	public static function add_field( $section_id, $args ) {
		if ( ! isset( self::$fields[ $section_id ] ) ) {
			self::$fields[ $section_id ] = array();
		}

		self::$fields[ $section_id ][ $args['id'] ] = wp_parse_args(
			$args,
			array(
				'id'          => '',
				'type'        => 'text',
				'label'       => '',
				'description' => '',
				'default'     => '',
				'options'     => array(),
			)
		);
	}

	/**
	 * Initialize Settings API registrations.
	 */
	public static function register_settings() {
		register_setting(
			'elonix_settings_group',
			'elonix_settings',
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
				'default'           => array(),
			)
		);

		foreach ( self::$sections as $section_id => $title ) {
			// Register a section for this tab
			add_settings_section(
				'elonix_section_' . $section_id,
				'', // Section title (rendered manually as tabs)
				__return_empty_string(),
				'elonix_settings_' . $section_id
			);

			// Register fields for this tab
			if ( isset( self::$fields[ $section_id ] ) ) {
				foreach ( self::$fields[ $section_id ] as $field_id => $field ) {
					add_settings_field(
						$field_id,
						$field['label'],
						array( __CLASS__, 'render_field_callback' ),
						'elonix_settings_' . $section_id,
						'elonix_section_' . $section_id,
						array(
							'section_id' => $section_id,
							'field'      => $field,
						)
					);
				}
			}
		}
	}

	/**
	 * Callback to render fields.
	 *
	 * @param array $args Callback arguments.
	 */
	public static function render_field_callback( $args ) {
		$section_id = $args['section_id'];
		$field      = $args['field'];
		$field_id   = $field['id'];
		$type       = $field['type'];

		// Retrieve current values
		$settings = get_option( 'elonix_settings', array() );
		$value    = isset( $settings[ $section_id ][ $field_id ] ) ? $settings[ $section_id ][ $field_id ] : $field['default'];

		// Field name format: elonix_settings[section_id][field_id]
		$name = sprintf( 'elonix_settings[%s][%s]', esc_attr( $section_id ), esc_attr( $field_id ) );

		switch ( $type ) {
			case 'switch':
				self::render_switch( $name, $value, $field );
				break;
			case 'text':
				self::render_text( $name, $value, $field );
				break;
			case 'number':
				self::render_number( $name, $value, $field );
				break;
			case 'select':
				self::render_select( $name, $value, $field );
				break;
			case 'checkbox':
				self::render_checkbox( $name, $value, $field );
				break;
			case 'color':
				self::render_color( $name, $value, $field );
				break;
			case 'slider':
				self::render_slider( $name, $value, $field );
				break;
			case 'image':
				self::render_image( $name, $value, $field );
				break;
			default:
				echo '<p>' . esc_html__( 'Invalid field type.', 'elonix' ) . '</p>';
				break;
		}
	}

	/**
	 * Render switch toggle field.
	 *
	 * @param string $name  Input name attribute.
	 * @param string $value Input value.
	 * @param array  $field Field configuration.
	 */
	public static function render_switch( $name, $value, $field ) {
		$is_developer_mode_override = ( 'developer_mode' === $field['id'] && defined( 'ELONIX_DEVELOPER_MODE' ) && ELONIX_DEVELOPER_MODE );

		if ( $is_developer_mode_override ) {
			$checked = 'checked="checked" disabled="disabled"';
		} else {
			$checked = checked( $value, '1', false );
		}

		echo '<label class="elonix-switch">';
		echo '<input type="checkbox" name="' . esc_attr( $name ) . '" value="1" ' . $checked . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span class="slider round"></span>';
		echo '</label>';

		if ( $is_developer_mode_override ) {
			echo '<p style="color:#d63638;"><strong><span class="dashicons dashicons-lock"></span> ' . esc_html__( 'Enabled by Constant (read-only): wp-config.php overrides plugin settings.', 'elonix' ) . '</strong></p>';
		} elseif ( ! empty( $field['description'] ) ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}
	}

	/**
	 * Render standard text field.
	 *
	 * @param string $name  Input name attribute.
	 * @param string $value Input value.
	 * @param array  $field Field configuration.
	 */
	public static function render_text( $name, $value, $field ) {
		echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
		if ( ! empty( $field['description'] ) ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}
	}

	/**
	 * Render number input field.
	 *
	 * @param string $name  Input name attribute.
	 * @param string $value Input value.
	 * @param array  $field Field configuration.
	 */
	public static function render_number( $name, $value, $field ) {
		echo '<input type="number" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="small-text">';
		if ( ! empty( $field['description'] ) ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}
	}

	/**
	 * Render select dropdown.
	 *
	 * @param string $name  Input name attribute.
	 * @param string $value Input value.
	 * @param array  $field Field configuration.
	 */
	public static function render_select( $name, $value, $field ) {
		echo '<select name="' . esc_attr( $name ) . '">';
		foreach ( $field['options'] as $val => $label ) {
			echo '<option value="' . esc_attr( $val ) . '" ' . selected( $value, $val, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
		if ( ! empty( $field['description'] ) ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}
	}

	/**
	 * Render standard checkbox.
	 *
	 * @param string $name  Input name attribute.
	 * @param string $value Input value.
	 * @param array  $field Field configuration.
	 */
	public static function render_checkbox( $name, $value, $field ) {
		$checked    = checked( $value, '1', false );
		$label_text = isset( $field['label_text'] ) ? $field['label_text'] : '';
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr( $name ) . '" value="1" ' . $checked . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ' ' . esc_html( $label_text );
		echo '</label>';
		if ( ! empty( $field['description'] ) ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}
	}

	/**
	 * Render color picker.
	 *
	 * @param string $name  Input name attribute.
	 * @param string $value Input value.
	 * @param array  $field Field configuration.
	 */
	public static function render_color( $name, $value, $field ) {
		echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="elonix-color-picker" data-default-color="' . esc_attr( $field['default'] ) . '">';
		if ( ! empty( $field['description'] ) ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}
	}

	/**
	 * Render slider with unit.
	 *
	 * @param string $name  Input name attribute.
	 * @param string $value Input value.
	 * @param array  $field Field configuration.
	 */
	public static function render_slider( $name, $value, $field ) {
		$min  = isset( $field['min'] ) ? $field['min'] : 0;
		$max  = isset( $field['max'] ) ? $field['max'] : 100;
		$step = isset( $field['step'] ) ? $field['step'] : 1;
		$unit = isset( $field['unit'] ) ? $field['unit'] : '';

		// Parse value to strip unit for range input
		$numeric_val = preg_replace( '/[^0-9.]/', '', $value );
		if ( $numeric_val === '' ) {
			$numeric_val = $min;
		}

		echo '<div style="display:flex; align-items:center; gap: 15px;">';
		echo '<input type="range" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '" class="elonix-slider" value="' . esc_attr( $numeric_val ) . '" style="width: 250px;">';
		echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="elonix-slider-input small-text" readonly>';
		if ( $unit ) {
			echo '<span class="slider-unit" style="display:none;" data-unit="' . esc_attr( $unit ) . '"></span>';
		}
		echo '</div>';

		if ( ! empty( $field['description'] ) ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}
	}

	/**
	 * Render image uploader.
	 *
	 * @param string $name  Input name attribute.
	 * @param string $value Input value.
	 * @param array  $field Field configuration.
	 */
	public static function render_image( $name, $value, $field ) {
		$preview_style = $value ? 'display: block;' : 'display: none;';
		echo '<div class="elonix-image-uploader">';
		echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="elonix-image-url">';
		echo '<div class="elonix-image-preview" style="max-width: 150px; margin-bottom: 10px; ' . esc_attr( $preview_style ) . '">';
		echo '<img src="' . esc_url( $value ) . '" style="max-width: 100%; height: auto; border: 1px solid #ccc; padding: 5px; background: #f0f0f0;">';
		echo '</div>';
		echo '<button type="button" class="button elonix-upload-button">' . esc_html__( 'Upload Image', 'elonix' ) . '</button> ';
		echo '<button type="button" class="button elonix-reset-button" style="' . esc_attr( $preview_style ) . '">' . esc_html__( 'Remove', 'elonix' ) . '</button>';
		echo '</div>';

		if ( ! empty( $field['description'] ) ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}
	}

	/**
	 * Sanitize settings array.
	 *
	 * @param array $input Unsanitized inputs.
	 * @return array Sanitized outputs.
	 */
	public static function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		// Retrieve existing settings from the database to preserve untabbed or unregistered keys.
		$old_settings = get_option( 'elonix_settings', array() );
		if ( ! is_array( $old_settings ) ) {
			$old_settings = array();
		}

		$output = $old_settings;

		// Determine which tab was submitted (if submitted via the main settings page options.php)
		$active_tab = isset( $input['es_active_tab'] ) ? sanitize_text_field( $input['es_active_tab'] ) : '';

		// Remove the helper key so it doesn't get saved forever
		unset( $output['es_active_tab'] );

		foreach ( self::$fields as $section_id => $fields ) {
			if ( ! isset( $output[ $section_id ] ) ) {
				$output[ $section_id ] = array();
			}

			// If the request is from options.php, ONLY sanitize the active tab!
			// If it's NOT from options.php ($active_tab is empty, e.g. from 404 builder),
			// then we don't wipe anything, we just accept whatever is in $input for the framework fields.
			if ( ! empty( $active_tab ) && $section_id !== $active_tab ) {
				continue;
			}

			foreach ( $fields as $field_id => $field ) {
				// Read from input. If not set, it's empty (e.g. unchecked checkbox)
				$val = isset( $input[ $section_id ][ $field_id ] ) ? $input[ $section_id ][ $field_id ] : '';

				switch ( $field['type'] ) {
					case 'switch':
					case 'checkbox':
						$output[ $section_id ][ $field_id ] = '1' === $val ? '1' : '0';
						break;
					case 'number':
						$output[ $section_id ][ $field_id ] = sanitize_text_field( $val );
						break;
					case 'select':
						$output[ $section_id ][ $field_id ] = isset( $field['options'][ $val ] ) ? sanitize_text_field( $val ) : $field['default'];
						break;
					case 'color':
						$output[ $section_id ][ $field_id ] = sanitize_hex_color( $val ) ? sanitize_hex_color( $val ) : '';
						break;
					case 'slider':
						$output[ $section_id ][ $field_id ] = sanitize_text_field( $val );
						break;
					case 'image':
						$output[ $section_id ][ $field_id ] = esc_url_raw( $val );
						break;
					case 'text':
					default:
						$output[ $section_id ][ $field_id ] = sanitize_text_field( $val );
						break;
				}
			}
		}

		// Merge any flat keys passed in $input that are not in self::$fields (like es_404_*)
		foreach ( $input as $k => $v ) {
			if ( ! isset( self::$fields[ $k ] ) && $k !== 'es_active_tab' ) {
				$output[ $k ] = $v;
			}
		}

		return $output;
	}
}
