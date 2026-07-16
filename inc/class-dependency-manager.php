<?php
/**
 * Elonix – Toolkit for Elementor Dependency Manager
 *
 * Manages the Elementor dependency for Elonix – Toolkit for Elementor.
 * Implements professional dependency checking following WordPress Coding Standards
 * and ThemeForest Premium Plugin quality guidelines.
 *
 * Handles all dependency scenarios:
 *   - Case 1: Elementor not installed  → show install notice.
 *   - Case 2: Elementor inactive       → show activate notice.
 *   - Case 3: Plugin activated without Elementor → auto-deactivate.
 *   - Case 4: Admin tries to deactivate Elementor while Elonix – Toolkit for Elementor is active
 *             → block deactivation, redirect, show error notice.
 *   - Case 5: Elementor active         → all notices cleared; plugin loads normally.
 *
 * @package Elonix_Toolkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Elonix_Toolkit_Dependency_Manager
 *
 * Singleton class responsible for all Elementor dependency checking
 * and admin notice rendering for Elonix – Toolkit for Elementor.
 */
class Elonix_Toolkit_Dependency_Manager {

	/**
	 * Elementor plugin basename.
	 *
	 * @var string
	 */
	const ELEMENTOR_PLUGIN_BASENAME = 'elementor/elementor.php';

	/**
	 * Minimum required Elementor version.
	 *
	 * @var string
	 */
	const ELEMENTOR_MINIMUM_VERSION = '3.20.0';

	/**
	 * Elonix – Toolkit for Elementor plugin name (display).
	 *
	 * @var string
	 */
	const PLUGIN_NAME = 'Elonix – Toolkit for Elementor';

	/**
	 * Transient key: a deactivation attempt was blocked.
	 *
	 * @var string
	 */
	const TRANSIENT_BLOCKED_DEACTIVATION = 'elonix_blocked_elementor_deactivation';

	/**
	 * Transient key: activation was blocked due to missing dependency.
	 *
	 * @var string
	 */
	const TRANSIENT_ACTIVATION_NOTICE = 'elonix_activation_notice';

	/**
	 * Singleton instance.
	 *
	 * @var Elonix_Toolkit_Dependency_Manager|null
	 */
	private static $instance = null;

	/**
	 * Whether a dependency notice has already been rendered this request.
	 *
	 * Prevents duplicate notices on the same page load.
	 *
	 * @var bool
	 */
	private $notice_rendered = false;

	/**
	 * Get the singleton instance.
	 *
	 * @return Elonix_Toolkit_Dependency_Manager
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — use instance().
	 */
	private function __construct() {}

	/**
	 * Register all required hooks.
	 *
	 * Called once from the main plugin file before any plugin initialization.
	 *
	 * @return void
	 */
	public function register_hooks() {
		// Case 4: Intercept and block any attempt to deactivate Elementor.
		add_action( 'admin_init', array( $this, 'block_elementor_deactivation' ) );

		// Case 4 (UI): Replace the Deactivate link with a visible-but-disabled span.
		// block_elementor_deactivation() is the authoritative server-side enforcement;
		// this is the accessibility-compliant visual layer on top of it.
		add_filter(
			'plugin_action_links_' . self::ELEMENTOR_PLUGIN_BASENAME,
			array( $this, 'modify_elementor_deactivate_link' )
		);

		// "Required by" dependency row — WP 6.5+ renders this natively via the
		// "Requires Plugins: elementor" plugin header. Register the fallback only
		// on older WordPress versions to prevent a duplicate row.
		global $wp_version;
		if ( version_compare( $wp_version, '6.5', '<' ) ) {
			add_action(
				'after_plugin_row_' . self::ELEMENTOR_PLUGIN_BASENAME,
				array( $this, 'render_elementor_dependency_row' ),
				10,
				3
			);
		}

		// Show appropriate admin notice when a dependency condition is unmet.
		add_action( 'admin_notices', array( $this, 'render_dependency_notice' ) );

		// Case 3 (activation): handled via register_activation_hook in main plugin file.
	}

	// -------------------------------------------------------------------------
	// Public Dependency State Checks
	// -------------------------------------------------------------------------

	/**
	 * Check whether Elementor plugin files exist on disk (installed).
	 *
	 * @return bool
	 */
	public function is_elementor_installed() {
		return file_exists( WP_PLUGIN_DIR . '/' . self::ELEMENTOR_PLUGIN_BASENAME );
	}

	/**
	 * Check whether Elementor is currently active (loaded by WordPress).
	 *
	 * Uses is_plugin_active() which requires plugin.php to be loaded.
	 *
	 * @return bool
	 */
	public function is_elementor_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( self::ELEMENTOR_PLUGIN_BASENAME );
	}

	/**
	 * Check whether Elonix – Toolkit for Elementor itself is currently active.
	 *
	 * @return bool
	 */
	private function is_our_plugin_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( plugin_basename( ELONIX_PLUGIN_FILE ) );
	}

	/**
	 * Master dependency gate: returns true only when all requirements are met.
	 *
	 * @return bool True if Elonix – Toolkit for Elementor may initialize; false otherwise.
	 */
	public function is_dependency_met() {
		return $this->is_elementor_installed() && $this->is_elementor_active();
	}

	// -------------------------------------------------------------------------
	// Case 3 — Activation Hook Handler
	// -------------------------------------------------------------------------

	/**
	 * Activation hook callback.
	 *
	 * If Elementor is missing or inactive when this plugin is activated,
	 * immediately deactivate this plugin and set a transient to display a notice.
	 *
	 * @return void
	 */
	public function handle_activation() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! $this->is_elementor_installed() ) {
			// Elementor not installed.
			deactivate_plugins( plugin_basename( ELONIX_PLUGIN_FILE ) );
			set_transient( self::TRANSIENT_ACTIVATION_NOTICE, 'not_installed', 60 );
			// Abort with a graceful wp_die to prevent white screen.
			wp_die(
				'<strong>' . esc_html( self::PLUGIN_NAME ) . '</strong> ' .
				esc_html__( 'requires Elementor Website Builder. Please install Elementor first, then activate Elonix – Toolkit for Elementor.', 'elonix' ),
				esc_html__( 'Activation Error', 'elonix' ),
				array(
					'back_link' => true,
					'response'  => 200,
				)
			);
		}

		if ( ! $this->is_elementor_active() ) {
			// Elementor installed but inactive.
			deactivate_plugins( plugin_basename( ELONIX_PLUGIN_FILE ) );
			set_transient( self::TRANSIENT_ACTIVATION_NOTICE, 'not_active', 60 );
			wp_die(
				'<strong>' . esc_html( self::PLUGIN_NAME ) . '</strong> ' .
				esc_html__( 'requires Elementor Website Builder to be active. Please activate Elementor first, then activate Elonix – Toolkit for Elementor.', 'elonix' ),
				esc_html__( 'Activation Error', 'elonix' ),
				array(
					'back_link' => true,
					'response'  => 200,
				)
			);
		}
	}

	// -------------------------------------------------------------------------
	// Case 4 — Block Elementor Deactivation
	// -------------------------------------------------------------------------

	/**
	 * Intercept and block any attempt to deactivate Elementor while Elonix
	 * Toolkit is active.
	 *
	 * Runs on admin_init — fires before plugins.php processes any plugin action,
	 * so this correctly cancels the deactivation before WordPress acts on it.
	 *
	 * Handles two paths:
	 *   - Single deactivation via the Deactivate row-action link (GET request).
	 *   - Bulk deactivation via the Bulk Actions > Deactivate dropdown (POST request).
	 *     When Elementor is included in a bulk selection, all other selected plugins
	 *     are deactivated normally; only Elementor is preserved.
	 *
	 * @return void
	 */
	public function block_elementor_deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Only block when Elonix – Toolkit for Elementor itself is active.
		if ( ! $this->is_our_plugin_active() ) {
			return;
		}

		// ---- Single deactivation (Deactivate link → GET request) ----
		if (
			isset( $_GET['action'] ) &&
			'deactivate' === $_GET['action'] &&
			isset( $_GET['plugin'] ) &&
			self::ELEMENTOR_PLUGIN_BASENAME === wp_unslash( $_GET['plugin'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		) {
			// Verify the WordPress-generated nonce for this specific deactivation URL.
			// If the nonce is absent or invalid this is not a legitimate request; pass through.
			if (
				! isset( $_GET['_wpnonce'] ) ||
				! wp_verify_nonce(
					sanitize_key( $_GET['_wpnonce'] ),
					'deactivate-plugin_' . self::ELEMENTOR_PLUGIN_BASENAME
				)
			) {
				return;
			}

			set_transient( self::TRANSIENT_BLOCKED_DEACTIVATION, '1', 60 );

			// Single redirect to plugins page — no loop risk.
			wp_safe_redirect( admin_url( 'plugins.php' ) );
			exit;
		}

		// ---- Bulk deactivation (Bulk Actions > Deactivate → POST request) ----
		// WordPress uses two action slots: top dropdown ('action') and bottom ('action2').
		$bulk_action = '';
		if ( isset( $_POST['action'] ) && '-1' !== $_POST['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$bulk_action = sanitize_key( $_POST['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} elseif ( isset( $_POST['action2'] ) && '-1' !== $_POST['action2'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$bulk_action = sanitize_key( $_POST['action2'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if (
			'deactivate-selected' === $bulk_action &&
			isset( $_POST['checked'] ) &&
			is_array( $_POST['checked'] ) &&
			in_array(
				self::ELEMENTOR_PLUGIN_BASENAME,
				array_map( 'sanitize_text_field', wp_unslash( $_POST['checked'] ) ),
				true
			)
		) {
			// Verify the bulk-plugins nonce that WordPress sets for the plugins list form.
			if (
				! isset( $_POST['_wpnonce'] ) ||
				! wp_verify_nonce(
					sanitize_key( $_POST['_wpnonce'] ),
					'bulk-plugins'
				)
			) {
				return;
			}

			// Deactivate every other selected plugin except Elementor.
			$checked       = array_map( 'sanitize_text_field', wp_unslash( $_POST['checked'] ) );
			$to_deactivate = array_values(
				array_filter(
					$checked,
					function ( $basename ) {
						return self::ELEMENTOR_PLUGIN_BASENAME !== $basename;
					}
				)
			);

			if ( ! empty( $to_deactivate ) ) {
				deactivate_plugins( $to_deactivate );
			}

			set_transient( self::TRANSIENT_BLOCKED_DEACTIVATION, '1', 60 );

			// Redirect back — WordPress will not process the bulk action further.
			wp_safe_redirect( admin_url( 'plugins.php' ) );
			exit;
		}
	}

	/**
	 * Replace the Elementor Deactivate link with a visible-but-disabled element.
	 *
	 * Requirements:
	 *   - Greyed out (color: #a7aaad matches WP disabled UI colour).
	 *   - cursor: not-allowed.
	 *   - aria-disabled="true" for screen-reader accessibility.
	 *   - title attribute provides a browser tooltip explaining the restriction.
	 *   - <span> — not an <a> — so clicking produces no navigation or action.
	 *   - No JavaScript required.
	 *
	 * block_elementor_deactivation() remains the server-side enforcement.
	 * This method is the accessibility-compliant visual layer only.
	 *
	 * @param array $actions Existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function modify_elementor_deactivate_link( array $actions ) {
		if ( ! $this->is_our_plugin_active() ) {
			return $actions;
		}

		$actions['deactivate'] = sprintf(
			'<span
				class="elonix-disabled-deactivate"
				aria-disabled="true"
				title="%s"
				style="color:#a7aaad;cursor:not-allowed;user-select:none;"
			>%s</span>',
			esc_attr__( 'Deactivate Elonix – Toolkit for Elementor first before deactivating Elementor.', 'elonix' ),
			esc_html__( 'Deactivate', 'elonix' )
		);

		return $actions;
	}

	// -------------------------------------------------------------------------
	// Dependency UI — "Required by" row (WordPress < 6.5 fallback)
	// -------------------------------------------------------------------------

	/**
	 * Return the list of active plugins that declare a dependency on Elementor.
	 *
	 * Provides an extension point so future plugins that also depend on Elementor
	 * can register themselves in the "Required by" section without modifying this
	 * class:
	 *
	 *   add_filter( 'elonix_elementor_dependents', function( $list ) {
	 *       $list[] = 'My Other Plugin';
	 *       return $list;
	 *   } );
	 *
	 * @return string[] Display names of plugins that require Elementor.
	 */
	private function get_elementor_dependents() {
		/**
		 * Filter the list of plugin display names that require Elementor.
		 *
		 * Allows additional plugins that depend on Elementor to appear in the
		 * "Required by" row on the Plugins page without duplicating logic.
		 *
		 * @param string[] $dependents Indexed array of plugin display names.
		 */
		return (array) apply_filters(
			'elonix_elementor_dependents',
			array( self::PLUGIN_NAME )
		);
	}

	/**
	 * Render the "Required by" dependency information row below Elementor's row.
	 *
	 * Registered on after_plugin_row_elementor/elementor.php for WordPress < 6.5
	 * only. WordPress 6.5+ renders an equivalent row natively when the plugin
	 * header contains "Requires Plugins: elementor".
	 *
	 * The HTML structure deliberately matches the native WordPress dependency row
	 * introduced in WordPress 6.5 (notice inline / notice-warning / notice-alt)
	 * so the visual result is identical regardless of the rendering path.
	 *
	 * @param string $plugin_file The plugin basename (elementor/elementor.php).
	 * @param array  $plugin_data Array of plugin metadata.
	 * @param string $status      Plugin status ('active', 'inactive', etc.).
	 * @return void
	 */
	public function render_elementor_dependency_row( $plugin_file, $plugin_data, $status ) {
		// Only display when Elonix – Toolkit for Elementor itself is active.
		if ( ! $this->is_our_plugin_active() ) {
			return;
		}

		$dependents = $this->get_elementor_dependents();
		if ( empty( $dependents ) ) {
			return;
		}

		// Escape each dependent name individually before joining.
		$dependents_escaped = array_map( 'esc_html', $dependents );
		$dependents_list    = implode( ', ', $dependents_escaped );

		?>
		<tr class="plugin-update-tr active"
			id="elementor-required-by-row"
			data-slug="elementor"
			data-plugin="<?php echo esc_attr( self::ELEMENTOR_PLUGIN_BASENAME ); ?>"
		>
			<td colspan="4" class="plugin-update colspanchange">
				<div class="notice inline notice-warning notice-alt">
					<p>
						<strong><?php esc_html_e( 'Required by:', 'elonix' ); ?></strong>
						<?php
						// $dependents_list is safe — each item was escaped with esc_html above.
						echo $dependents_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo ' &#8212; ';
						printf(
							/* translators: %s: comma-separated list of plugin names that require Elementor. */
							esc_html__( 'This plugin cannot be deactivated while it is required by: %s.', 'elonix' ),
							$dependents_list // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						);
						?>
					</p>
				</div>
			</td>
		</tr>
		<?php
	}

	// -------------------------------------------------------------------------
	// Admin Notices (Cases 1, 2, 3, 4, 5)
	// -------------------------------------------------------------------------

	/**
	 * Output the appropriate admin dependency notice.
	 *
	 * Runs on admin_notices. Only one notice is ever rendered per page load.
	 *
	 * @return void
	 */
	public function render_dependency_notice() {
		// Prevent duplicate notices on the same page load.
		if ( $this->notice_rendered ) {
			return;
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Case 4: A deactivation attempt was blocked — show the blocked notice.
		$was_blocked = get_transient( self::TRANSIENT_BLOCKED_DEACTIVATION );
		if ( $was_blocked ) {
			delete_transient( self::TRANSIENT_BLOCKED_DEACTIVATION );
			$this->render_notice_blocked_deactivation();
			$this->notice_rendered = true;
			return;
		}

		// Case 3: Activation was blocked (transient set during activation).
		$activation_notice = get_transient( self::TRANSIENT_ACTIVATION_NOTICE );
		if ( $activation_notice ) {
			delete_transient( self::TRANSIENT_ACTIVATION_NOTICE );
			if ( 'not_installed' === $activation_notice ) {
				$this->render_notice_not_installed();
			} else {
				$this->render_notice_not_active();
			}
			$this->notice_rendered = true;
			return;
		}

		// Cases 1 & 2: Plugin is active but Elementor is missing/inactive.
		if ( ! $this->is_elementor_installed() ) {
			// Case 1: Elementor not installed.
			$this->render_notice_not_installed();
			$this->notice_rendered = true;
			return;
		}

		if ( ! $this->is_elementor_active() ) {
			// Case 2: Elementor installed but not activated.
			$this->render_notice_not_active();
			$this->notice_rendered = true;
			return;
		}

		// Case 5: Elementor is active — no notice needed.
	}

	// -------------------------------------------------------------------------
	// Notice Renderers
	// -------------------------------------------------------------------------

	/**
	 * Render: Elementor deactivation was blocked (Case 4).
	 *
	 * @return void
	 */
	private function render_notice_blocked_deactivation() {
		?>
		<div class="notice notice-error elonix-dependency-notice">
			<p>
				<strong><?php echo esc_html( self::PLUGIN_NAME ); ?></strong>
				<?php
				echo ' &mdash; ';
				esc_html_e( 'Elementor cannot be deactivated because Elonix – Toolkit for Elementor is currently active.', 'elonix' );
				echo '<br>';
				esc_html_e( 'Please deactivate Elonix – Toolkit for Elementor first.', 'elonix' );
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render: Elementor is NOT installed (Case 1).
	 *
	 * Displays an install button using the official WordPress plugin installer URL
	 * with a proper nonce so the link never expires.
	 *
	 * @return void
	 */
	private function render_notice_not_installed() {
		$install_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'install-plugin',
					'plugin' => 'elementor',
				),
				admin_url( 'update.php' )
			),
			'install-plugin_elementor'
		);

		?>
		<div class="notice notice-error elonix-dependency-notice">
			<p>
				<strong><?php echo esc_html( self::PLUGIN_NAME ); ?></strong>
				<?php
				echo ' &mdash; ';
				esc_html_e( 'requires Elementor Website Builder.', 'elonix' );
				echo '<br>';
				esc_html_e( 'Install Elementor to use this plugin.', 'elonix' );
				?>
			</p>
			<p>
				<a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary">
					<?php esc_html_e( 'Install Elementor', 'elonix' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Render: Elementor is installed but inactive (Case 2).
	 *
	 * Displays an activate button using the official WordPress plugin activation URL
	 * with a correct nonce.
	 *
	 * @return void
	 */
	private function render_notice_not_active() {
		$activate_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'activate',
					'plugin' => rawurlencode( self::ELEMENTOR_PLUGIN_BASENAME ),
				),
				admin_url( 'plugins.php' )
			),
			'activate-plugin_' . self::ELEMENTOR_PLUGIN_BASENAME
		);

		?>
		<div class="notice notice-error elonix-dependency-notice">
			<p>
				<strong><?php echo esc_html( self::PLUGIN_NAME ); ?></strong>
				<?php
				echo ' &mdash; ';
				esc_html_e( 'requires Elementor Website Builder to be active.', 'elonix' );
				echo '<br>';
				esc_html_e( 'Activate Elementor to use this plugin.', 'elonix' );
				?>
			</p>
			<p>
				<a href="<?php echo esc_url( $activate_url ); ?>" class="button button-primary">
					<?php esc_html_e( 'Activate Elementor', 'elonix' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

}
