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
		add_action( 'wp_head', array( $this, 'output_critical_css' ), 1 );

		// Enqueue the deferred JS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add defer attribute to script
		add_filter( 'script_loader_tag', array( $this, 'add_defer_attribute' ), 10, 2 );
	}

	public function output_critical_css() {
		$bg_color  = esc_attr( $this->settings['bg'] );
		$primary   = esc_attr( $this->settings['color'] );
		$secondary = esc_attr( $this->settings['color_alt'] );
		$opacity   = esc_attr( $this->settings['opacity'] );
		$size      = esc_attr( $this->settings['size'] );
		$speed     = esc_attr( $this->settings['speed'] );
		$zindex    = esc_attr( $this->settings['zindex'] );

		// Convert hex/rgb to rgba for overlay opacity if needed,
		// but using CSS variables we can just apply opacity to a pseudo element or background
		// For simplicity, we just use a CSS custom property.

		?>
		<style id="tv-screen-loader-critical-css">
			:root {
				--tv-loader-bg: <?php echo esc_html( $bg_color ); ?>;
				--tv-loader-opacity: <?php echo esc_html( $opacity ); ?>;
				--tv-loader-color: <?php echo esc_html( $primary ); ?>;
				--tv-loader-secondary: <?php echo esc_html( $secondary ); ?>;
				--tv-loader-size: <?php echo esc_html( $size ); ?>;
				--tv-loader-speed: <?php echo esc_html( $speed ); ?>;
				--tv-loader-zindex: <?php echo esc_html( $zindex ); ?>;
				--tv-loader-ring-width: 4px;
				--tv-loader-shadow: 0 0 10px rgba(0,0,0,0.2);
			}

			@media (max-width: 768px) {
				:root {
					--tv-loader-size: 120px;
				}
			}

			/* Core Screen Loader Critical CSS */
			body.tv-loading-active {
				overflow: hidden !important;
				height: 100vh !important;
			}
			.tv-screen-loader-wrapper {
				position: fixed;
				top: 0;
				left: 0;
				width: 100vw;
				height: 100vh;
				background-color: rgba(0,0,0,0); /* Fallback */
				background-color: var(--tv-loader-bg);
				z-index: var(--tv-loader-zindex);
				display: flex;
				justify-content: center;
				align-items: center;
				transition: opacity 0.5s cubic-bezier(0.25, 1, 0.5, 1), transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), visibility 0.5s;
			}
			
			/* Exit Animations */
			.tv-loader--fade.tv-loader-out {
				opacity: 0 !important;
				visibility: hidden !important;
			}
			.tv-loader--slide-up.tv-loader-out {
				transform: translateY(-100%) !important;
				opacity: 0 !important;
				visibility: hidden !important;
			}
			.tv-loader--slide-down.tv-loader-out {
				transform: translateY(100%) !important;
				opacity: 0 !important;
				visibility: hidden !important;
			}
			
			/* Apply opacity via pseudo if user selected < 1 */
			.tv-screen-loader-wrapper::before {
				content: '';
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background-color: var(--tv-loader-bg);
				opacity: var(--tv-loader-opacity);
				z-index: -1;
			}
			
			/* Instant hiding for sessionStorage / Early Destruction */
			html.tv-loader-hidden .tv-screen-loader-wrapper {
				display: none !important;
				opacity: 0 !important;
				visibility: hidden !important;
			}

			/* Classic Dual Ring (Default) */
			.tv-screen-loader--default {
				display: flex;
				justify-content: center;
				align-items: center;
				position: relative;
			}
			.tv-screen-loader__close {
				position: fixed;
				top: 20px;
				right: 20px;
				width: 40px;
				height: 40px;
				background: transparent;
				border: none;
				cursor: pointer;
				z-index: calc(var(--tv-loader-zindex) + 1);
			}
			.tv-screen-loader__close::before,
			.tv-screen-loader__close::after {
				content: '';
				position: absolute;
				top: 50%;
				left: 50%;
				width: 20px;
				height: 2px;
				background-color: var(--tv-loader-color);
			}
			.tv-screen-loader__close::before {
				transform: translate(-50%, -50%) rotate(45deg);
			}
			.tv-screen-loader__close::after {
				transform: translate(-50%, -50%) rotate(-45deg);
			}
			.tv-screen-loader__spinner {
				position: relative;
				width: var(--tv-loader-size);
				height: var(--tv-loader-size);
			}
			.tv-screen-loader__ring {
				display: block;
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				border-radius: 50%;
				border: var(--tv-loader-ring-width) solid transparent;
				border-top-color: var(--tv-loader-color);
				border-bottom-color: var(--tv-loader-secondary);
				box-shadow: var(--tv-loader-shadow);
				animation: tv-spin var(--tv-loader-speed) linear infinite;
				box-sizing: border-box;
			}
			.tv-screen-loader__ring::before,
			.tv-screen-loader__ring::after {
				content: '';
				position: absolute;
				border-radius: 50%;
				border: var(--tv-loader-ring-width) solid transparent;
				box-shadow: var(--tv-loader-shadow);
				box-sizing: border-box;
			}
			.tv-screen-loader__ring::before {
				top: 10px;
				left: 10px;
				right: 10px;
				bottom: 10px;
				border-top-color: var(--tv-loader-color);
				border-bottom-color: var(--tv-loader-secondary);
				animation: tv-spin calc(var(--tv-loader-speed) * 1.5) linear infinite reverse;
			}
			.tv-screen-loader__ring::after {
				top: 25px;
				left: 25px;
				right: 25px;
				bottom: 25px;
				border-top-color: var(--tv-loader-color);
				border-bottom-color: var(--tv-loader-secondary);
				animation: tv-spin calc(var(--tv-loader-speed) * 0.75) linear infinite;
			}
			
			/* Base Loader Engines CSS */
			.tv-css-loader.tv-loader-spinner {
				width: var(--tv-loader-size);
				height: var(--tv-loader-size);
				border: 3px solid var(--tv-loader-secondary);
				border-radius: 50%;
				border-top-color: var(--tv-loader-color);
				animation: tv-spin var(--tv-loader-speed) ease-in-out infinite;
			}
			.tv-css-loader.tv-loader-dual-ring {
				width: var(--tv-loader-size);
				height: var(--tv-loader-size);
				border-radius: 50%;
				border: 3px solid transparent;
				border-top-color: var(--tv-loader-color);
				border-bottom-color: var(--tv-loader-secondary);
				animation: tv-spin var(--tv-loader-speed) linear infinite;
			}
			.tv-css-loader.tv-loader-pulse {
				width: var(--tv-loader-size);
				height: var(--tv-loader-size);
				background-color: var(--tv-loader-color);
				border-radius: 50%;
				animation: tv-scale var(--tv-loader-speed) ease-in-out infinite alternate;
			}
			.tv-css-loader.tv-loader-dots {
				width: var(--tv-loader-size);
				height: calc(var(--tv-loader-size) / 3);
				display: flex;
				justify-content: space-between;
			}
			.tv-css-loader.tv-loader-dots::before,
			.tv-css-loader.tv-loader-dots::after,
			.tv-css-loader.tv-loader-dots span {
				content: '';
				width: 30%;
				height: 100%;
				background-color: var(--tv-loader-color);
				border-radius: 50%;
				animation: tv-scale var(--tv-loader-speed) ease-in-out infinite alternate;
			}
			.tv-css-loader.tv-loader-dots span { animation-delay: 0.15s; }
			.tv-css-loader.tv-loader-dots::after { animation-delay: 0.3s; }
			
			/* Hybrid Logo CSS */
			.tv-hybrid-loader img {
				width: var(--tv-loader-size);
				height: auto;
				animation: tv-pulse var(--tv-loader-speed) ease-in-out infinite alternate;
			}

			/* SVG Loader CSS */
			.tv-svg-loader {
				width: var(--tv-loader-size);
				height: var(--tv-loader-size);
				animation: tv-spin var(--tv-loader-speed) linear infinite;
			}
			.tv-svg-loader .tv-path {
				stroke: var(--tv-loader-color);
				stroke-dasharray: 1, 200;
				stroke-dashoffset: 0;
				animation: tv-dash calc(var(--tv-loader-speed) * 1.5) ease-in-out infinite;
				stroke-linecap: round;
			}

			/* Animations */
			@keyframes tv-spin {
				to { transform: rotate(360deg); }
			}
			@keyframes tv-scale {
				0% { transform: scale(0.5); opacity: 0.5; }
				100% { transform: scale(1); opacity: 1; }
			}
			@keyframes tv-pulse {
				0% { opacity: 0.6; transform: scale(0.95); }
				100% { opacity: 1; transform: scale(1.05); }
			}
			@keyframes tv-dash {
				0% { stroke-dasharray: 1, 200; stroke-dashoffset: 0; }
				50% { stroke-dasharray: 89, 200; stroke-dashoffset: -35px; }
				100% { stroke-dasharray: 89, 200; stroke-dashoffset: -124px; }
			}

			/* Accessibility: Reduced Motion */
			@media (prefers-reduced-motion: reduce) {
				.tv-screen-loader-wrapper * {
					animation-duration: 0.01ms !important;
					animation-iteration-count: 1 !important;
					transition-duration: 0.01ms !important;
					scroll-behavior: auto !important;
				}
			}
		</style>
		<?php
		// Render early script to add body class before rendering body
		?>
		<script type="text/javascript" id="tv-screen-loader-early-js">
			(function() {
				<?php if ( $this->settings['once'] ) : ?>
					if ( sessionStorage.getItem('tv_loader_shown') ) {
						// Do not add loading class if already shown and hide the loader instantly
						document.documentElement.className += ' tv-loader-hidden';
						return;
					}
				<?php endif; ?>
				document.documentElement.className += ' tv-loading-active';
			})();
		</script>
		<?php
	}

	public function enqueue_scripts() {
		// Enqueue the main vanilla JS controller deferred.
		wp_register_script( 'tv-screen-loader-js', ELONIX_ACC_URL . 'assets/js/screen-loader.js', array(), ELONIX_VERSION, true );

		wp_localize_script(
			'tv-screen-loader-js',
			'tvScreenLoaderConfig',
			array(
				'timeout'       => absint( $this->settings['timeout'] ),
				'once'          => (bool) $this->settings['once'],
				'enable_escape' => isset( $this->settings['enable_escape'] ) ? (bool) $this->settings['enable_escape'] : true,
			)
		);

		wp_enqueue_script( 'tv-screen-loader-js' );
	}

	public function add_defer_attribute( $tag, $handle ) {
		if ( 'tv-screen-loader-js' === $handle ) {
			return str_replace( ' src', ' defer="defer" src', $tag );
		}
		return $tag;
	}
}
