<?php
namespace Elonix_Toolkit\Modules\Screen_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Admin_Page {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'handle_reset' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'screen_loader_admin_scripts' ) );

		// Secure SVG upload filters restricted to this page
		add_filter( 'upload_mimes', array( $this, 'allow_svg_upload' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'check_svg_filetype' ), 10, 4 );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'prepare_svg_for_media_library' ), 10, 3 );

		add_action( 'update_option_elonix_settings', array( $this, 'clear_svg_transient' ), 10, 2 );
	}

	public function clear_svg_transient( $old_value, $value ) {
		$old_img = isset( $old_value['screen_loader']['custom_image'] ) ? $old_value['screen_loader']['custom_image'] : '';
		$new_img = isset( $value['screen_loader']['custom_image'] ) ? $value['screen_loader']['custom_image'] : '';

		$version = defined( 'ELONIX_VERSION' ) ? ELONIX_VERSION : '1.0';

		if ( $old_img ) {
			delete_transient( 'es_sl_svg_' . md5( $old_img . $version ) );
		}
		if ( $new_img && $new_img !== $old_img ) {
			delete_transient( 'es_sl_svg_' . md5( $new_img . $version ) );
		}
	}

	private function is_screen_loader_context() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only context detection, not a state-changing action.
		if ( isset( $_GET['page'] ) && 'elonix-screen-loader' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			return true;
		}
		if ( isset( $_SERVER['HTTP_REFERER'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), 'page=elonix-screen-loader' ) !== false ) {
			return true;
		}
		return false;
	}

	public function allow_svg_upload( $mimes ) {
		if ( $this->is_screen_loader_context() ) {
			$mimes['svg']  = 'image/svg+xml';
			$mimes['svgz'] = 'image/svg+xml';
		}
		return $mimes;
	}

	public function check_svg_filetype( $data, $file, $filename, $mimes ) {
		if ( $this->is_screen_loader_context() ) {
			$ext = pathinfo( $filename, PATHINFO_EXTENSION );
			if ( 'svg' === $ext || 'svgz' === $ext ) {
				// Validate real file content to prevent disguised malicious files
				if ( file_exists( $file ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					$file_content = file_get_contents( $file, false, null, 0, 2048 );
					if ( strpos( $file_content, '<svg' ) !== false ) {
						$data['ext']  = $ext;
						$data['type'] = 'image/svg+xml';
					} else {
						$data['ext']  = false;
						$data['type'] = false;
					}
				} else {
					$data['ext']  = $ext;
					$data['type'] = 'image/svg+xml';
				}
			}
		}
		return $data;
	}

	public function prepare_svg_for_media_library( $response, $attachment, $meta ) {
		if ( $this->is_screen_loader_context() && 'image/svg+xml' === $response['mime'] ) {
			$response['sizes'] = array(
				'full' => array(
					'url'         => $response['url'],
					'width'       => isset( $response['width'] ) ? $response['width'] : 100,
					'height'      => isset( $response['height'] ) ? $response['height'] : 100,
					'orientation' => ( isset( $response['width'] ) && isset( $response['height'] ) && $response['width'] > $response['height'] ) ? 'landscape' : 'portrait',
				),
			);
		}
		return $response;
	}

	public function handle_reset() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- initial routing check only; real nonce + capability check follow on the next lines before any state change.
		if ( isset( $_GET['page'] ) && 'elonix-screen-loader' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) && isset( $_GET['reset'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['reset'] ) ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'elonix_screen_loader_reset' ) ) {
				wp_die( esc_html__( 'Security check failed.', 'elonix' ) );
			}
			if ( current_user_can( 'manage_options' ) ) {
				$settings = get_option( 'elonix_settings', array() );
				if ( isset( $settings['screen_loader'] ) ) {
					unset( $settings['screen_loader'] );
					update_option( 'elonix_settings', $settings );
				}
				wp_safe_redirect( admin_url( 'admin.php?page=elonix-screen-loader&reset_success=1' ) );
				exit;
			}
		}
	}

	public function enqueue_scripts( $hook ) {
		if ( 'elonix_page_elonix-screen-loader' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'elonix-settings-page-css',
			ELONIX_ACC_URL . 'assets/admin/css/settings-page.css',
			array(),
			ELONIX_VERSION
		);

		// Enqueue native UI components
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_media();

		wp_enqueue_script(
			'elonix-screen-loader-admin',
			ELONIX_ACC_URL . 'assets/js/screen-loader-admin.js',
			array( 'jquery', 'wp-color-picker' ),
			'1.0.0',
			true
		);
	}

	public function register_settings() {
		\Elonix_Toolkit_Settings_Framework::add_section( 'screen_loader', esc_html__( 'Screen Loader', 'elonix' ) );

		// General
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'enable',
				'type'    => 'switch',
				'label'   => esc_html__( 'Enable Screen Loader', 'elonix' ),
				'default' => '0',
			)
		);
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'engine',
				'type'    => 'select',
				'label'   => esc_html__( 'Loader Engine', 'elonix' ),
				'options' => array(
					'pure-css' => esc_html__( 'Pure CSS', 'elonix' ),
					'svg'      => esc_html__( 'SVG', 'elonix' ),
					'logo'     => esc_html__( 'Logo', 'elonix' ),
					'image'    => esc_html__( 'Image', 'elonix' ),
				),
				'default' => 'pure-css',
			)
		);
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'custom_image',
				'type'    => 'image',
				'label'   => esc_html__( 'Custom Image / Logo URL', 'elonix' ),
				'default' => '',
			)
		);
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'style',
				'type'    => 'select',
				'label'   => esc_html__( 'Loader Style', 'elonix' ),
				'options' => array(
					'default' => esc_html__( 'Classic Dual Ring (Default)', 'elonix' ),
					'spinner' => esc_html__( 'Modern Spinner', 'elonix' ),
					'pulse'   => esc_html__( 'Pulse', 'elonix' ),
					'ripple'  => esc_html__( 'Ripple', 'elonix' ),
					'dots'    => esc_html__( 'Dots', 'elonix' ),
					'wave'    => esc_html__( 'Wave', 'elonix' ),
					'orbit'   => esc_html__( 'Orbit', 'elonix' ),
				),
				'default' => 'default',
			)
		);

		// Style
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'bg',
				'type'    => 'color',
				'label'   => esc_html__( 'Background Color', 'elonix' ),
				'default' => '#ffffff',
			)
		);
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'color',
				'type'    => 'color',
				'label'   => esc_html__( 'Primary Color', 'elonix' ),
				'default' => '#000000',
			)
		);
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'          => 'color_alt',
				'type'        => 'color',
				'label'       => esc_html__( 'Secondary Color', 'elonix' ),
				'default'     => '#cccccc',
				'description' => esc_html__( 'Used only for engines that require it (e.g. Dual Ring, Pulse).', 'elonix' ),
			)
		);
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'opacity',
				'type'    => 'slider',
				'label'   => esc_html__( 'Overlay Opacity', 'elonix' ),
				'default' => '1',
				'min'     => '0',
				'max'     => '1',
				'step'    => '0.05',
			)
		);
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'size',
				'type'    => 'slider',
				'label'   => esc_html__( 'Loader Size (px)', 'elonix' ),
				'default' => '150',
				'min'     => '10',
				'max'     => '200',
				'step'    => '1',
				'unit'    => 'px',
			)
		);
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'speed',
				'type'    => 'slider',
				'label'   => esc_html__( 'Animation Speed (s)', 'elonix' ),
				'default' => '0.5s',
				'min'     => '0.1',
				'max'     => '5',
				'step'    => '0.1',
				'unit'    => 's',
			)
		);
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'zindex',
				'type'    => 'number',
				'label'   => esc_html__( 'Z-index', 'elonix' ),
				'default' => '999999',
			)
		);

		// Behaviour
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'animation',
				'type'    => 'select',
				'label'   => esc_html__( 'Animation Type', 'elonix' ),
				'options' => array(
					'fade'       => esc_html__( 'Fade', 'elonix' ),
					'slide-up'   => esc_html__( 'Slide Up', 'elonix' ),
					'slide-down' => esc_html__( 'Slide Down', 'elonix' ),
				),
				'default' => 'fade',
			)
		);
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'timeout',
				'type'    => 'number',
				'label'   => esc_html__( 'Maximum Timeout (ms)', 'elonix' ),
				'default' => '5000',
			)
		);
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'once',
				'type'    => 'switch',
				'label'   => esc_html__( 'Show Once Per Session', 'elonix' ),
				'default' => '0',
			)
		);
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'enable_escape',
				'type'    => 'switch',
				'label'   => esc_html__( 'Enable Escape Key', 'elonix' ),
				'default' => '1',
			)
		);

		// Advanced
		\Elonix_Toolkit_Settings_Framework::add_field(
			'screen_loader',
			array(
				'id'      => 'custom_class',
				'type'    => 'text',
				'label'   => esc_html__( 'Custom CSS Class', 'elonix' ),
				'default' => '',
			)
		);
	}

	public function screen_loader_admin_scripts() {
		$screen = get_current_screen();
		if ( ! $screen || 'elonix_page_elonix-screen-loader' !== $screen->id ) {
			return;
		}
		?>
		<!-- SCREEN LOADER JS ENQUEUED EXTERNALLY -->
		<?php
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'elonix' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice display, gated by manage_options above.
		if ( isset( $_GET['reset_success'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['reset_success'] ) ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Screen Loader settings have been reset.', 'elonix' ) . '</p></div>';
		}

		$reset_url = wp_nonce_url( admin_url( 'admin.php?page=elonix-screen-loader&reset=1' ), 'elonix_screen_loader_reset' );
		?>
		<div class="wrap elonix-settings-wrap" id="es-screen-loader-admin">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Screen Loader', 'elonix' ); ?></h1>
			<p><?php esc_html_e( 'Configure the visual appearance and behavior of the loading screen.', 'elonix' ); ?></p>
			<hr class="wp-header-end">
			
			<div style="display:flex; flex-wrap: wrap; gap: 30px; align-items: flex-start; margin-top: 24px;">
				<div style="flex: 1 1 55%; min-width: 350px;">
					<form method="post" action="options.php">
						<?php
						settings_fields( 'elonix_settings_group' );
						do_settings_sections( 'elonix_settings_screen_loader' );

						echo '<div style="margin-top:24px; display:flex; gap: 15px; background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">';
						submit_button( esc_html__( 'Save Changes', 'elonix' ), 'primary', 'submit', false, array( 'style' => 'margin:0;' ) );
						echo '<a href="' . esc_url( $reset_url ) . '" class="button button-secondary" onclick="return confirm(\'' . esc_js( __( 'Are you sure you want to reset all Screen Loader settings to default?', 'elonix' ) ) . '\');">' . esc_html__( 'Reset to Defaults', 'elonix' ) . '</a>';
						echo '</div>';
						?>
					</form>
				</div>
				<div style="flex: 1 1 40%; min-width: 300px; position: sticky; top: 40px;">
					<div class="elonix-settings-section-card" style="padding: 24px;">
						<h2 style="margin-top:0; font-size: 16px; font-weight: 600; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;"><?php esc_html_e( 'Live Preview', 'elonix' ); ?></h2>
						
						<div style="font-size: 12px; color: #64748b; margin-bottom: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
							<div><strong>Engine:</strong> <span id="es-live-engine"></span></div>
							<div><strong>Style:</strong> <span id="es-live-style"></span></div>
							<div><strong>Anim:</strong> <span id="es-live-anim"></span></div>
							<div><strong>Speed:</strong> <span id="es-live-speed"></span></div>
							<div><strong>Color 1:</strong> <span id="es-live-color"></span></div>
							<div><strong>Color 2:</strong> <span id="es-live-color-alt"></span></div>
						</div>

						<div id="es-screen-loader-preview-bg" style="position:relative; width: 100%; height: 320px; background: url('<?php echo esc_url( admin_url( 'images/pattern-light.svg' ) ); ?>') repeat; border: 1px solid #e2e8f0; overflow: hidden; display: flex; align-items: center; justify-content: center; border-radius: 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
							<div id="es-screen-loader-preview" style="position:absolute; top:0; left:0; width:100%; height:100%; display:flex; align-items:center; justify-content:center; transition: all 0.3s ease;">
								<div id="es-screen-loader-preview-inner" style="z-index: 2; position: relative;"></div>
							</div>
						</div>
						<p class="description" style="margin-top: 16px; text-align: center; font-size: 12px;"><span class="dashicons dashicons-visibility" style="font-size: 16px; margin-top: -2px;"></span> <?php esc_html_e( 'Changes are previewed instantly. Click Save Changes to apply.', 'elonix' ); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
