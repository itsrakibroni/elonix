<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action(
	'acf/include_fields',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'                   => 'group_color_mode_options',
				'title'                 => 'Page Color Mode',
				'fields'                => array(
					array(
						'key'           => 'field_enable_dark_mode',
						'label'         => 'Color Mode',
						'name'          => 'enable_dark_mode',
						'type'          => 'select',
						'instructions'  => 'Select the color mode for this page.',
						'choices'       => array(
							'light-mode' => 'Light Mode (Default)',
							'dark-mode'  => 'Dark Mode',
						),
						'default_value' => 'light-mode',
						'ui'            => 1,
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'page',
						),
						array(
							'param'    => 'page_type',
							'operator' => '!=',
							'value'    => 'posts_page', // Posts page exclude
						),
					),
					array(
						array(
							'param'    => 'post_type',
							'operator' => '!=',
							'value'    => 'elementor_library',
						),
					),
				),
				'menu_order'            => 9,
				'position'              => 'side',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'active'                => true,
			)
		);

		acf_add_local_field_group(
			array(
				'key'                   => 'group_banner_options',
				'title'                 => 'Banner Options',
				'fields'                => array(
					array(
						'key'           => 'field_enable_page_banner',
						'label'         => 'Show Page Banner',
						'name'          => 'enable_page_banner',
						'type'          => 'true_false',
						'instructions'  => 'Turn this OFF to completely hide the page banner section.',
						'message'       => '',
						'default_value' => 1,
						'ui'            => 1,
						'ui_on_text'    => 'Show',
						'ui_off_text'   => 'Hide',
					),
					array(
						'key'               => 'field_enable_custom_title',
						'label'             => 'Use Custom Title',
						'name'              => 'enable_cus_pagetitle',
						'type'              => 'true_false',
						'instructions'      => 'Enable to use a custom title below instead of the default page/post title.',
						'default_value'     => 0,
						'ui'                => 1,
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_enable_page_banner',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
					),
					array(
						'key'               => 'field_custom_title',
						'label'             => 'Custom Page Title',
						'name'              => 'cus_pagetitle',
						'type'              => 'text',
						'instructions'      => 'Enter the custom title to display in the banner.',
						'placeholder'       => 'Your Custom Title',
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_enable_page_banner',
									'operator' => '==',
									'value'    => '1',
								),
								array(
									'field'    => 'field_enable_custom_title',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
					),
					array(
						'key'               => 'field_hide_breadcrumb',
						'label'             => 'Hide Breadcrumb',
						'name'              => 'hide_breadcrumb',
						'type'              => 'true_false',
						'instructions'      => 'Check this to hide the breadcrumb navigation in the banner.',
						'default_value'     => 0,
						'ui'                => 1,
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_enable_page_banner',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
					),
					array(
						'key'               => 'field_custom_banner_bg',
						'label'             => 'Custom Banner Background',
						'name'              => 'custom_banner_bg',
						'type'              => 'image',
						'instructions'      => 'Upload a specific background image for this page\'s banner. If left empty, the default from Theme Options will be used.',
						'return_format'     => 'url',
						'preview_size'      => 'medium',
						'library'           => 'all',
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_enable_page_banner',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'page',
						),
						array(
							'param'    => 'page_type',
							'operator' => '!=',
							'value'    => 'posts_page', // Posts page exclude
						),
					),
					array(
						array(
							'param'    => 'post_type',
							'operator' => '!=',
							'value'    => 'elementor_library',
						),
					),
				),
				'menu_order'            => 10,
				'position'              => 'side',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'active'                => true,
			)
		);

		acf_add_local_field_group(
			array(
				'key'                   => 'group_rtl_options',
				'title'                 => 'RTL Options',
				'fields'                => array(
					array(
						'key'           => 'field_enable_rtl_for_this_page',
						'label'         => 'Enable RTL for this page',
						'name'          => 'enable_rtl_for_this_page',
						'type'          => 'true_false',
						'instructions'  => 'Turn this ON to enable RTL (Right to Left) mode for this specific page or post.',
						'message'       => '',
						'default_value' => 0,
						'ui'            => 1,
						'ui_on_text'    => 'Yes',
						'ui_off_text'   => 'No',
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'page',
						),
					),
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'post',
						),
					),
				),
				'menu_order'            => 11,
				'position'              => 'side',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'active'                => true,
			)
		);


	}
);
