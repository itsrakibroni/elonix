<?php
/**
 * Elonix – Toolkit for Elementor Social Icons Widget
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Social_Icons_Widget extends Elonix_Social_Base_Widget {

	/**
	 * Retrieve widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'tv-social-icons';
	}

	/**
	 * Retrieve widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Social Icons', 'elonix' );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_tv_widget_icon() {
		return 'eicon-social-icons';
	}

	/**
	 * Retrieve widget keywords.
	 *
	 * @return array Keywords list.
	 */
	public function get_tv_widget_keywords() {
		return array( 'social', 'icons', 'links', 'facebook', 'twitter', 'youtube', 'instagram', 'tvkit' );
	}

	/**
	 * Retrieve widget styles handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_style_depends() {
		return array( 'elonix-widget-tv-social-icons' );
	}

	/**
	 * Retrieve widget scripts handle dependency list.
	 *
	 * @return array Dependencies handles.
	 */
	public function get_script_depends() {
		return array( 'elonix-widget-tv-social-icons' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// ==========================================
		// CONTENT TAB - SOCIAL ICONS SECTION
		// ==========================================
		$this->start_controls_section(
			'section_social_icons',
			array(
				'label' => esc_html__( 'Social Icons', 'elonix' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'social_platform',
			array(
				'label'   => esc_html__( 'Social Platform', 'elonix' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'facebook',
				'options' => array(
					'facebook'  => esc_html__( 'Facebook', 'elonix' ),
					'twitter'   => esc_html__( 'X / Twitter', 'elonix' ),
					'instagram' => esc_html__( 'Instagram', 'elonix' ),
					'linkedin'  => esc_html__( 'LinkedIn', 'elonix' ),
					'youtube'   => esc_html__( 'YouTube', 'elonix' ),
					'tiktok'    => esc_html__( 'TikTok', 'elonix' ),
					'telegram'  => esc_html__( 'Telegram', 'elonix' ),
					'whatsapp'  => esc_html__( 'WhatsApp', 'elonix' ),
					'pinterest' => esc_html__( 'Pinterest', 'elonix' ),
					'reddit'    => esc_html__( 'Reddit', 'elonix' ),
					'discord'   => esc_html__( 'Discord', 'elonix' ),
					'github'    => esc_html__( 'GitHub', 'elonix' ),
					'behance'   => esc_html__( 'Behance', 'elonix' ),
					'dribbble'  => esc_html__( 'Dribbble', 'elonix' ),
					'medium'    => esc_html__( 'Medium', 'elonix' ),
					'skype'     => esc_html__( 'Skype', 'elonix' ),
					'vimeo'     => esc_html__( 'Vimeo', 'elonix' ),
					'snapchat'  => esc_html__( 'Snapchat', 'elonix' ),
					'threads'   => esc_html__( 'Threads', 'elonix' ),
					'custom'    => esc_html__( 'Custom Platform', 'elonix' ),
				),
			)
		);

		$repeater->add_control(
			'custom_icon',
			array(
				'label'            => esc_html__( 'Custom Icon', 'elonix' ),
				'type'             => \Elementor\Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => array(
					'value'   => '',
					'library' => '',
				),
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'       => esc_html__( 'Link URL', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'elonix' ),
				'default'     => array(
					'url'         => '#',
					'is_external' => true,
					'nofollow'    => false,
				),
			)
		);

		$repeater->add_control(
			'sponsored',
			array(
				'label'        => esc_html__( 'Sponsored Link', 'elonix' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elonix' ),
				'label_off'    => esc_html__( 'No', 'elonix' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$repeater->add_control(
			'custom_label',
			array(
				'label'       => esc_html__( 'Custom Label', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Follow us', 'elonix' ),
				'label_block' => true,
			)
		);

		// Per-Item Style Controls
		$this->register_item_style_controls( $repeater );

		$this->add_control(
			'social_icons_list',
			array(
				'label'       => esc_html__( 'Social Links Repeater', 'elonix' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'social_platform' => 'facebook',
						'custom_label'    => 'Facebook',
					),
					array(
						'social_platform' => 'twitter',
						'custom_label'    => 'Twitter',
					),
					array(
						'social_platform' => 'youtube',
						'custom_label'    => 'YouTube',
					),
				),
				'title_field' => '{{{ custom_label || social_platform }}}',
			)
		);

		$this->end_controls_section();

		// Register inherited base sections
		$this->register_layout_controls();
		$this->register_icon_settings_controls();
		$this->register_style_presets_controls();
		$this->register_tooltip_controls();
		$this->register_advanced_brand_controls();
		$this->register_style_tabs_controls();
	}

	/**
	 * Render social icons widget HTML output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['social_icons_list'] ) ) {
			return;
		}
		?>
		<div class="elonix-social-icons-wrapper">
			<?php
			foreach ( $settings['social_icons_list'] as $index => $item ) :
				$platform   = $item['social_platform'];
				$item_key   = $this->get_repeater_setting_key( 'link', 'social_icons_list', $index );
				$label_text = ! empty( $item['custom_label'] ) ? $item['custom_label'] : ucwords( str_replace( '-', ' ', $platform ) );

				// Configure dynamic links tags
				$this->add_link_attributes( $item_key, $item['link'] );

				$item_classes = 'tv-social-item tv-social-platform-' . esc_attr( $platform );
				if ( ! empty( $item['_id'] ) ) {
					$item_classes .= ' elementor-repeater-item-' . esc_attr( $item['_id'] );
				}
				$this->add_render_attribute( $item_key, 'class', $item_classes );
				$this->add_render_attribute( $item_key, 'aria-label', esc_attr( $label_text ) );

				// Accessible focus support
				$this->add_render_attribute( $item_key, 'tabindex', '0' );

				// Check for Sponsored Link
				if ( 'yes' === $item['sponsored'] ) {
					$rel = $this->get_render_attribute_string( $item_key );
					if ( strpos( $rel, 'rel=' ) !== false ) {
						// Append sponsored to existing rel
						$this->add_render_attribute( $item_key, 'rel', 'sponsored', true );
					} else {
						$this->add_render_attribute( $item_key, 'rel', 'sponsored' );
					}
				}
				?>
				<a <?php $this->print_render_attribute_string( $item_key ); ?>>
					<div class="tv-social-item-inner">
						
						<?php if ( 'label_only' !== $settings['icon_position'] ) : ?>
							<span class="tv-social-icon-box">
								<?php
								// Render custom icon if uploaded, otherwise fallback to platform brand icon
								if ( ! empty( $item['custom_icon']['value'] ) ) {
									\Elementor\Icons_Manager::render_icon( $item['custom_icon'], array( 'aria-hidden' => 'true' ) );
								} else {
									$default_class = $this->get_default_platform_icon( $platform );
									echo '<i class="' . esc_attr( $default_class ) . '" aria-hidden="true"></i>';
								}
								?>
							</span>
						<?php endif; ?>

						<?php if ( 'icon_only' !== $settings['icon_position'] && ! empty( $label_text ) ) : ?>
							<span class="tv-social-label">
								<?php echo esc_html( $label_text ); ?>
							</span>
						<?php endif; ?>

					</div>
					
					<?php if ( 'yes' === $settings['enable_tooltip'] && ! empty( $label_text ) ) : ?>
						<span class="tv-social-tooltip" role="tooltip">
							<?php echo esc_html( $label_text ); ?>
						</span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
