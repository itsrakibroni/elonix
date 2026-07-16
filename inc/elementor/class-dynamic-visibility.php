<?php
/**
 * Elonix Dynamic Visibility Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elonix_Dynamic_Visibility extends Elonix_Base_Extension {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_name() {
		return 'visibility';
	}

	public function register_controls( $element, $args ) {
		$element->start_controls_section(
			'elonix_visibility_section',
			array(
				'label' => esc_html__( 'Elonix Visibility', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			)
		);

		$element->add_control(
			'tv_visibility_enable',
			array(
				'label'        => esc_html__( 'Enable Dynamic Visibility', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'condition_type',
			array(
				'label'   => esc_html__( 'Condition', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					''             => esc_html__( '- Select Condition -', 'elonix' ),
					'logged_in'    => esc_html__( 'User is Logged In', 'elonix' ),
					'logged_out'   => esc_html__( 'User is Logged Out', 'elonix' ),
					'user_role'    => esc_html__( 'User Role', 'elonix' ),
					'post_type'    => esc_html__( 'Post Type', 'elonix' ),
					'is_archive'   => esc_html__( 'Is Archive', 'elonix' ),
					'is_single'    => esc_html__( 'Is Single', 'elonix' ),
					'is_search'    => esc_html__( 'Is Search', 'elonix' ),
					'custom_field' => esc_html__( 'Custom Field', 'elonix' ),
				),
				'default' => '',
			)
		);

		$repeater->add_control(
			'condition_value',
			array(
				'label'     => esc_html__( 'Value', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array(
					'condition_type' => array( 'user_role', 'post_type', 'custom_field' ),
				),
			)
		);

		$element->add_control(
			'tv_visibility_conditions',
			array(
				'label'       => esc_html__( 'Conditions', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'condition'   => array(
					'tv_visibility_enable' => 'yes',
				),
				'title_field' => '{{{ condition_type }}}',
			)
		);

		$element->add_control(
			'tv_visibility_relation',
			array(
				'label'     => esc_html__( 'Relation', 'elonix' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => array(
					'AND' => esc_html__( 'Show if ALL are met (AND)', 'elonix' ),
					'OR'  => esc_html__( 'Show if ANY is met (OR)', 'elonix' ),
				),
				'default'   => 'AND',
				'condition' => array(
					'tv_visibility_enable' => 'yes',
				),
			)
		);

		$element->end_controls_section();
	}

	public function should_render( $should_render, $element ) {
		if ( ! $should_render ) {
			return $should_render;
		}

		$settings = $element->get_settings_for_display();

		if ( empty( $settings['tv_visibility_enable'] ) || 'yes' !== $settings['tv_visibility_enable'] ) {
			return $should_render;
		}

		$conditions = isset( $settings['tv_visibility_conditions'] ) ? $settings['tv_visibility_conditions'] : array();
		if ( empty( $conditions ) ) {
			return $should_render;
		}

		$relation = isset( $settings['tv_visibility_relation'] ) ? $settings['tv_visibility_relation'] : 'AND';
		$results  = array();

		$debug_log = '---- EVALUATING WIDGET ' . $element->get_name() . " ----\n";

		foreach ( $conditions as $condition ) {
			$type   = isset( $condition['condition_type'] ) ? $condition['condition_type'] : '';
			$value  = isset( $condition['condition_value'] ) ? $condition['condition_value'] : '';
			$is_met = false;

			switch ( $type ) {
				case 'logged_in':
					$is_met = is_user_logged_in();
					break;
				case 'logged_out':
					$is_met = ! is_user_logged_in();
					break;
				case 'user_role':
					if ( is_user_logged_in() ) {
						$user   = wp_get_current_user();
						$is_met = in_array( $value, (array) $user->roles );
					}
					break;
				case 'post_type':
					$is_met = ( get_post_type() === $value );
					break;
				case 'is_archive':
					$is_met = is_archive();
					break;
				case 'is_single':
					$is_met = is_single();
					break;
				case 'is_search':
					$is_met = is_search();
					break;
				case 'custom_field':
					$meta   = get_post_meta( get_the_ID(), $value, true );
					$is_met = ! empty( $meta );
					break;
			}

			// Third party condition hook
			$is_met = apply_filters( 'elonix/visibility/evaluate_condition', $is_met, $type, $value );

			$results[] = $is_met;

			$debug_log .= "Condition Type: {$type}\n";
			$debug_log .= "Condition Value: {$value}\n";
			$debug_log .= 'is_user_logged_in(): ' . ( is_user_logged_in() ? 'true' : 'false' ) . "\n";
			$debug_log .= 'Evaluated is_met: ' . ( $is_met ? 'true' : 'false' ) . "\n";
		}

		$final_result = false;
		if ( 'AND' === $relation ) {
			$final_result = ! in_array( false, $results, true );
		} else {
			$final_result = in_array( true, $results, true );
		}

		$debug_log .= "Relation: {$relation}\n";
		$debug_log .= 'Final Result: ' . ( $final_result ? 'true' : 'false' ) . "\n\n";

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		

		return $final_result;
	}
}
