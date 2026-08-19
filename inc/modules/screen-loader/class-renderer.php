<?php
namespace Elonix_Toolkit\Modules\Screen_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Renderer {
	/**
	 * @var array
	 */
	private $settings;

	/**
	 * @var Interfaces\Loader_Engine_Interface
	 */
	private $engine;

	public function __construct( $settings, $engine ) {
		$this->settings = $settings;
		$this->engine   = $engine;
	}

	public function register_hooks() {
		// Priority 1 to be the absolute first element in the body.
		add_action( 'wp_body_open', array( $this, 'render_loader' ), 1 );
	}

	public function render_loader() {
		$custom_class    = ! empty( $this->settings['custom_class'] ) ? esc_attr( $this->settings['custom_class'] ) : '';
		$animation       = ! empty( $this->settings['animation'] ) ? esc_attr( $this->settings['animation'] ) : 'fade';
		$wrapper_classes = trim( "es-screen-loader-wrapper es-loader--{$animation} {$custom_class}" );
		?>
		<div id="es-screen-loader" class="<?php echo esc_attr( $wrapper_classes ); ?>" role="alert" aria-live="assertive" aria-busy="true">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup is built from admin-configured settings (manage_options), not visitor input.
			echo $this->engine->get_markup( $this->settings );
			?>
		</div>
		<?php
	}
}
