<?php
namespace Elonix_Toolkit\Modules\Screen_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Assets {
	/**
	 * @var array
	 */
	private $settings;

	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	public function register_hooks() {
		// Output critical CSS inline at priority 1 to guarantee it's the first style
		// (Moved to enqueue_scripts using wp_add_inline_style)

		// Enqueue the deferred JS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add defer attribute to script
		add_filter( 'script_loader_tag', array( $this, 'add_defer_attribute' ), 10, 2 );
	}

	public function get_critical_css() {
		$bg_color  = esc_attr( $this->settings['bg'] );
		$primary   = esc_attr( $this->settings['color'] );
		$secondary = esc_attr( $this->settings['color_alt'] );
		$opacity   = esc_attr( $this->settings['opacity'] );
		$size      = esc_attr( $this->settings['size'] );
		$speed     = esc_attr( $this->settings['speed'] );
		$zindex    = esc_attr( $this->settings['zindex'] );

		ob_start();
		?>
			:root {
				--es-loader-bg: <?php echo esc_html( $bg_color ); ?>;
				--es-loader-opacity: <?php echo esc_html( $opacity ); ?>;
				--es-loader-color: <?php echo esc_html( $primary ); ?>;
				--es-loader-secondary: <?php echo esc_html( $secondary ); ?>;
				--es-loader-size: <?php echo esc_html( $size ); ?>;
				--es-loader-speed: <?php echo esc_html( $speed ); ?>;
				--es-loader-zindex: <?php echo esc_html( $zindex ); ?>;
				--es-loader-ring-width: 4px;
				--es-loader-shadow: 0 0 10px rgba(0,0,0,0.2);
			}

			@media (max-width: 768px) {
				:root {
					--es-loader-size: 120px;
				}
			}

			/* Core Screen Loader Critical CSS */
			body.es-loading-active {
				overflow: hidden !important;
				height: 100vh !important;
			}
			.es-screen-loader-wrapper {
				position: fixed;
				top: 0;
				left: 0;
				width: 100vw;
				height: 100vh;
				background-color: rgba(0,0,0,0); /* Fallback */
				background-color: var(--es-loader-bg);
				z-index: var(--es-loader-zindex);
				display: flex;
				justify-content: center;
				align-items: center;
				transition: opacity 0.5s cubic-bezier(0.25, 1, 0.5, 1), transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), visibility 0.5s;
			}
			
			/* Exit Animations */
			.es-loader--fade.es-loader-out {
				opacity: 0 !important;
				visibility: hidden !important;
			}
			.es-loader--slide-up.es-loader-out {
				transform: translateY(-100%) !important;
				opacity: 0 !important;
				visibility: hidden !important;
			}
			.es-loader--slide-down.es-loader-out {
				transform: translateY(100%) !important;
				opacity: 0 !important;
				visibility: hidden !important;
			}
			
			/* Apply opacity via pseudo if user selected < 1 */
			.es-screen-loader-wrapper::before {
				content: '';
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background-color: var(--es-loader-bg);
				opacity: var(--es-loader-opacity);
				z-index: -1;
			}
			
			/* Instant hiding for sessionStorage / Early Destruction */
			html.es-loader-hidden .es-screen-loader-wrapper {
				display: none !important;
				opacity: 0 !important;
				visibility: hidden !important;
			}

			/* Classic Dual Ring (Default) */
			.es-screen-loader--default {
				display: flex;
				justify-content: center;
				align-items: center;
				position: relative;
			}
			.es-screen-loader__close {
				position: fixed;
				top: 20px;
				right: 20px;
				width: 40px;
				height: 40px;
				background: transparent;
				border: none;
				cursor: pointer;
				z-index: calc(var(--es-loader-zindex) + 1);
			}
			.es-screen-loader__close::before,
			.es-screen-loader__close::after {
				content: '';
				position: absolute;
				top: 50%;
				left: 50%;
				width: 20px;
				height: 2px;
				background-color: var(--es-loader-color);
			}
			.es-screen-loader__close::before {
				transform: translate(-50%, -50%) rotate(45deg);
			}
			.es-screen-loader__close::after {
				transform: translate(-50%, -50%) rotate(-45deg);
			}
			.es-screen-loader__spinner {
				position: relative;
				width: var(--es-loader-size);
				height: var(--es-loader-size);
			}
			.es-screen-loader__ring {
				display: block;
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				border-radius: 50%;
				border: var(--es-loader-ring-width) solid transparent;
				border-top-color: var(--es-loader-color);
				border-bottom-color: var(--es-loader-secondary);
				box-shadow: var(--es-loader-shadow);
				animation: es-spin var(--es-loader-speed) linear infinite;
				box-sizing: border-box;
			}
			.es-screen-loader__ring::before,
			.es-screen-loader__ring::after {
				content: '';
				position: absolute;
				border-radius: 50%;
				border: var(--es-loader-ring-width) solid transparent;
				box-shadow: var(--es-loader-shadow);
				box-sizing: border-box;
			}
			.es-screen-loader__ring::before {
				top: 10px;
				left: 10px;
				right: 10px;
				bottom: 10px;
				border-top-color: var(--es-loader-color);
				border-bottom-color: var(--es-loader-secondary);
				animation: es-spin calc(var(--es-loader-speed) * 1.5) linear infinite reverse;
			}
			.es-screen-loader__ring::after {
				top: 25px;
				left: 25px;
				right: 25px;
				bottom: 25px;
				border-top-color: var(--es-loader-color);
				border-bottom-color: var(--es-loader-secondary);
				animation: es-spin calc(var(--es-loader-speed) * 0.75) linear infinite;
			}
			
			/* Base Loader Engines CSS */
			.es-css-loader.es-loader-spinner {
				width: var(--es-loader-size);
				height: var(--es-loader-size);
				border: 3px solid var(--es-loader-secondary);
				border-radius: 50%;
				border-top-color: var(--es-loader-color);
				animation: es-spin var(--es-loader-speed) ease-in-out infinite;
			}
			.es-css-loader.es-loader-dual-ring {
				width: var(--es-loader-size);
				height: var(--es-loader-size);
				border-radius: 50%;
				border: 3px solid transparent;
				border-top-color: var(--es-loader-color);
				border-bottom-color: var(--es-loader-secondary);
				animation: es-spin var(--es-loader-speed) linear infinite;
			}
			.es-css-loader.es-loader-pulse {
				width: var(--es-loader-size);
				height: var(--es-loader-size);
				background-color: var(--es-loader-color);
				border-radius: 50%;
				animation: es-scale var(--es-loader-speed) ease-in-out infinite alternate;
			}
			.es-css-loader.es-loader-dots {
				width: var(--es-loader-size);
				height: calc(var(--es-loader-size) / 3);
				display: flex;
				justify-content: space-between;
			}
			.es-css-loader.es-loader-dots::before,
			.es-css-loader.es-loader-dots::after,
			.es-css-loader.es-loader-dots span {
				content: '';
				width: 30%;
				height: 100%;
				background-color: var(--es-loader-color);
				border-radius: 50%;
				animation: es-scale var(--es-loader-speed) ease-in-out infinite alternate;
			}
			.es-css-loader.es-loader-dots span { animation-delay: 0.15s; }
			.es-css-loader.es-loader-dots::after { animation-delay: 0.3s; }
			
			/* Hybrid Logo CSS */
			.es-hybrid-loader img {
				width: var(--es-loader-size);
				height: auto;
				animation: es-pulse var(--es-loader-speed) ease-in-out infinite alternate;
			}

			/* SVG Loader CSS */
			.es-svg-loader {
				width: var(--es-loader-size);
				height: var(--es-loader-size);
				animation: es-spin var(--es-loader-speed) linear infinite;
			}
			.es-svg-loader .es-path {
				stroke: var(--es-loader-color);
				stroke-dasharray: 1, 200;
				stroke-dashoffset: 0;
				animation: es-dash calc(var(--es-loader-speed) * 1.5) ease-in-out infinite;
				stroke-linecap: round;
			}

			/* Animations */
			@keyframes es-spin {
				to { transform: rotate(360deg); }
			}
			@keyframes es-scale {
				0% { transform: scale(0.5); opacity: 0.5; }
				100% { transform: scale(1); opacity: 1; }
			}
			@keyframes es-pulse {
				0% { opacity: 0.6; transform: scale(0.95); }
				100% { opacity: 1; transform: scale(1.05); }
			}
			@keyframes es-dash {
				0% { stroke-dasharray: 1, 200; stroke-dashoffset: 0; }
				50% { stroke-dasharray: 89, 200; stroke-dashoffset: -35px; }
				100% { stroke-dasharray: 89, 200; stroke-dashoffset: -124px; }
			}

			/* Accessibility: Reduced Motion */
			@media (prefers-reduced-motion: reduce) {
				.es-screen-loader-wrapper * {
					animation-duration: 0.01ms !important;
					animation-iteration-count: 1 !important;
					transition-duration: 0.01ms !important;
					scroll-behavior: auto !important;
				}
			}
		<?php
		return ob_get_clean();
	}

	public function get_early_js() {
		ob_start();
		?>
			(function() {
				<?php if ( $this->settings['once'] ) : ?>
					if ( sessionStorage.getItem('es_loader_shown') ) {
						document.documentElement.className += ' es-loader-hidden';
						return;
					}
				<?php endif; ?>
				document.documentElement.className += ' es-loading-active';
			})();
		<?php
		return ob_get_clean();
	}

	public function enqueue_scripts() {
		// Enqueue the critical CSS via a dummy handle
		wp_register_style( 'elonix-screen-loader-critical', false );
		wp_enqueue_style( 'elonix-screen-loader-critical' );
		wp_add_inline_style( 'elonix-screen-loader-critical', $this->get_critical_css() );

		// Enqueue the early JS via a dummy handle in the head
		wp_register_script( 'elonix-screen-loader-early', false );
		wp_enqueue_script( 'elonix-screen-loader-early' );
		wp_add_inline_script( 'elonix-screen-loader-early', $this->get_early_js() );

		// Enqueue the main vanilla JS controller deferred.
		wp_register_script( 'elonix-screen-loader-js', ELONIX_ACC_URL . 'assets/js/screen-loader.js', array(), ELONIX_VERSION, true );

		wp_localize_script(
			'elonix-screen-loader-js',
			'esScreenLoaderConfig',
			array(
				'timeout'       => absint( $this->settings['timeout'] ),
				'once'          => (bool) $this->settings['once'],
				'enable_escape' => isset( $this->settings['enable_escape'] ) ? (bool) $this->settings['enable_escape'] : true,
			)
		);

		wp_enqueue_script( 'elonix-screen-loader-js' );
	}

	public function add_defer_attribute( $tag, $handle ) {
		if ( 'elonix-screen-loader-js' === $handle ) {
			return str_replace( ' src', ' defer="defer" src', $tag );
		}
		return $tag;
	}
}
