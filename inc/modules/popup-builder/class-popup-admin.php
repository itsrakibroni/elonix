<?php
/**
 * Elonix Popup Builder Custom Admin Metabox Configuration
 *
 * @package Elonix_Toolkit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elonix_Toolkit_Popup_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		// Custom Columns Hooks
		add_filter( 'manage_elonix_popup_posts_columns', array( $this, 'register_custom_columns' ) );
		add_action( 'manage_elonix_popup_posts_custom_column', array( $this, 'render_custom_columns' ), 10, 2 );

		// Custom Meta Box Hooks
		add_action( 'add_meta_boxes', array( $this, 'add_popup_settings_metabox' ) );
		add_action( 'save_post_elonix_popup', array( $this, 'save_popup_settings' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Enqueue admin scripts for the popup editor.
	 */
	public function enqueue_admin_scripts( $hook ) {
		global $post;
		if ( ! $post || 'elonix_popup' !== $post->post_type ) {
			return;
		}

		wp_enqueue_script(
			'elonix-popup-admin',
			ELONIX_ACC_URL . 'assets/js/popup-admin.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);
	}

	/**
	 * Define custom columns for CPT list table.
	 */
	
	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_admin_assets( $hook ) {
		global $post;
		if ( ! $post || 'elonix_popup' !== $post->post_type ) {
			return;
		}

		wp_enqueue_style(
			'elonix-module-meta',
			ELONIX_ACC_URL . 'assets/admin/css/module-meta.css',
			array(),
			ELONIX_VERSION
		);
	}

	public function register_custom_columns( $columns ) {
		$new_columns = array(
			'cb'                  => $columns['cb'],
			'title'               => $columns['title'],
			'es_popup_type'       => esc_html__( 'Popup Type', 'elonix' ),
			'es_popup_trigger'    => esc_html__( 'Trigger Action', 'elonix' ),
			'es_popup_conditions' => esc_html__( 'Display Targeting', 'elonix' ),
			'es_popup_frequency'  => esc_html__( 'Frequency', 'elonix' ),
			'date'                => $columns['date'],
		);
		return $new_columns;
	}

	/**
	 * Render metadata inside custom columns.
	 */
	public function render_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'es_popup_type':
				$type  = get_post_meta( $post_id, '_es_popup_type', true );
				$types = array(
					'modal'            => esc_html__( 'Modal Popup', 'elonix' ),
					'slide_in'         => esc_html__( 'Slide In Popup', 'elonix' ),
					'notification_bar' => esc_html__( 'Notification Bar', 'elonix' ),
				);
				echo esc_html( isset( $types[ $type ] ) ? $types[ $type ] : '-' );
				break;

			case 'es_popup_trigger':
				$trigger  = get_post_meta( $post_id, '_es_popup_trigger_type', true );
				$val      = get_post_meta( $post_id, '_es_popup_trigger_value', true );
				$triggers = array(
					'page_load'     => esc_html__( 'Page Load', 'elonix' ),
					/* translators: %d: Time delay in seconds. */
					'time_delay'    => sprintf( esc_html__( 'Time Delay (%ds)', 'elonix' ), intval( $val ) ),
					/* translators: %s: CSS selector to trigger the popup. */
					'click_trigger' => sprintf( esc_html__( 'On Click (%s)', 'elonix' ), esc_html( $val ) ),
				);
				echo esc_html( isset( $triggers[ $trigger ] ) ? $triggers[ $trigger ] : '-' );
				break;

			case 'es_popup_conditions':
				$rule  = get_post_meta( $post_id, '_es_popup_target_rule', true );
				$ids   = get_post_meta( $post_id, '_es_popup_target_ids', true );
				$rules = array(
					'entire_site'    => esc_html__( 'Entire Site', 'elonix' ),
					/* translators: %s: Comma-separated list of page IDs. */
					'specific_pages' => sprintf( esc_html__( 'Pages (IDs: %s)', 'elonix' ), esc_html( $ids ) ),
					/* translators: %s: Comma-separated list of post IDs. */
					'specific_posts' => sprintf( esc_html__( 'Posts (IDs: %s)', 'elonix' ), esc_html( $ids ) ),
				);
				echo esc_html( isset( $rules[ $rule ] ) ? $rules[ $rule ] : '-' );
				break;

			case 'es_popup_frequency':
				$freq  = get_post_meta( $post_id, '_es_popup_frequency', true );
				$freqs = array(
					'show_once' => esc_html__( 'Show Once', 'elonix' ),
					'session'   => esc_html__( 'Session Based', 'elonix' ),
					'cookie'    => esc_html__( 'Cookie Suppressed', 'elonix' ),
				);
				echo esc_html( isset( $freqs[ $freq ] ) ? $freqs[ $freq ] : '-' );
				break;
		}
	}

	/**
	 * Register Custom Settings Meta Box.
	 */
	public function add_popup_settings_metabox() {
		add_meta_box(
			'es_popup_settings_metabox',
			esc_html__( 'Popup Configuration Settings (MVP v1)', 'elonix' ),
			array( $this, 'render_settings_metabox' ),
			'elonix_popup',
			'normal',
			'high'
		);
	}

	/**
	 * Render settings layout template inside the meta box.
	 */
	public function render_settings_metabox( $post ) {
		// Nonce check tag
		wp_nonce_field( 'es_popup_settings_save', 'es_popup_settings_nonce' );

		// Load values
		$type             = get_post_meta( $post->ID, '_es_popup_type', true );
		$trigger          = get_post_meta( $post->ID, '_es_popup_trigger_type', true );
		$trigger_val      = get_post_meta( $post->ID, '_es_popup_trigger_value', true );
		$scroll_val       = get_post_meta( $post->ID, '_es_popup_scroll_value', true );
		$exit_sensitivity = get_post_meta( $post->ID, '_es_popup_exit_intent_sensitivity', true );
		$priority         = get_post_meta( $post->ID, '_es_popup_priority', true );
		$target_rule      = get_post_meta( $post->ID, '_es_popup_target_rule', true );
		$target_ids       = get_post_meta( $post->ID, '_es_popup_target_ids', true );
		$devices          = get_post_meta( $post->ID, '_es_popup_devices', true );
		$frequency        = get_post_meta( $post->ID, '_es_popup_frequency', true );
		$cookie_expiry    = get_post_meta( $post->ID, '_es_popup_cookie_expiry', true );

		// Advanced Conditions values
		$user_state      = get_post_meta( $post->ID, '_es_popup_user_state', true );
		$user_roles      = get_post_meta( $post->ID, '_es_popup_user_roles', true );
		$page_conditions = get_post_meta( $post->ID, '_es_popup_page_conditions', true );

		if ( ! is_array( $devices ) ) {
			$devices = array( 'desktop', 'tablet', 'mobile' );
		}
		if ( empty( $cookie_expiry ) ) {
			$cookie_expiry = 30;
		}
		if ( empty( $exit_sensitivity ) ) {
			$exit_sensitivity = 20;
		}
		if ( empty( $priority ) ) {
			$priority = 10;
		}
		if ( empty( $user_state ) ) {
			$user_state = 'all';
		}
		if ( ! is_array( $user_roles ) ) {
			$user_roles = array();
		}
		if ( ! is_array( $page_conditions ) ) {
			$page_conditions = array();
		}
		?>
		<!-- MODULE META CSS MOVED EXTERNALLY -->
		<div class="es-popup-meta-wrapper">
			
			<!-- Popup Type Selection -->
			<div class="es-popup-meta-row">
				<div class="es-popup-meta-label"><?php esc_html_e( 'Popup Display Type', 'elonix' ); ?></div>
				<div class="es-popup-meta-field">
					<select name="es_popup_type">
						<option value="modal" <?php selected( $type, 'modal' ); ?>><?php esc_html_e( 'Modal Popup', 'elonix' ); ?></option>
						<option value="slide_in" <?php selected( $type, 'slide_in' ); ?>><?php esc_html_e( 'Slide In Popup', 'elonix' ); ?></option>
						<option value="notification_bar" <?php selected( $type, 'notification_bar' ); ?>><?php esc_html_e( 'Notification Bar', 'elonix' ); ?></option>
					</select>
				</div>
			</div>

			<!-- Trigger Selection -->
			<div class="es-popup-meta-row">
				<div class="es-popup-meta-label"><?php esc_html_e( 'Trigger Hook', 'elonix' ); ?></div>
				<div class="es-popup-meta-field">
					<select name="es_popup_trigger_type" id="es_popup_trigger_type" style="margin-bottom: 8px;">
						<option value="page_load" <?php selected( $trigger, 'page_load' ); ?>><?php esc_html_e( 'Page Load (Immediate)', 'elonix' ); ?></option>
						<option value="time_delay" <?php selected( $trigger, 'time_delay' ); ?>><?php esc_html_e( 'Time Delay (seconds)', 'elonix' ); ?></option>
						<option value="scroll_depth" <?php selected( $trigger, 'scroll_depth' ); ?>><?php esc_html_e( 'Scroll Depth', 'elonix' ); ?></option>
						<option value="exit_intent" <?php selected( $trigger, 'exit_intent' ); ?>><?php esc_html_e( 'Exit Intent', 'elonix' ); ?></option>
						<option value="click_trigger" <?php selected( $trigger, 'click_trigger' ); ?>><?php esc_html_e( 'Click Trigger (CSS Selector)', 'elonix' ); ?></option>
					</select>

					<!-- Time Delay value -->
					<div class="es-trigger-dep" data-dep="time_delay" style="margin-top: 8px; display: none;">
						<input type="number" step="0.1" name="es_popup_trigger_value_delay" value="<?php echo esc_attr( 'time_delay' === $trigger ? $trigger_val : '' ); ?>" placeholder="<?php esc_attr_e( 'Delay in seconds (e.g. 5)', 'elonix' ); ?>" />
					</div>

					<!-- Click Trigger value -->
					<div class="es-trigger-dep" data-dep="click_trigger" style="margin-top: 8px; display: none;">
						<input type="text" name="es_popup_trigger_value_click" value="<?php echo esc_attr( 'click_trigger' === $trigger ? $trigger_val : '' ); ?>" placeholder="<?php esc_attr_e( 'CSS Selector (e.g. .my-btn-class)', 'elonix' ); ?>" />
					</div>

					<!-- Scroll Depth Trigger value -->
					<div class="es-trigger-dep" data-dep="scroll_depth" style="margin-top: 8px; display: none;">
						<select name="es_popup_scroll_value" id="es_popup_scroll_value" style="margin-bottom: 8px;">
							<option value="10" <?php selected( $scroll_val, '10' ); ?>>10%</option>
							<option value="25" <?php selected( $scroll_val, '25' ); ?>>25%</option>
							<option value="50" <?php selected( $scroll_val, '50' ); ?>>50%</option>
							<option value="75" <?php selected( $scroll_val, '75' ); ?>>75%</option>
							<option value="90" <?php selected( $scroll_val, '90' ); ?>>90%</option>
							<option value="custom" <?php selected( ( ! in_array( $scroll_val, array( '10', '25', '50', '75', '90' ) ) && ! empty( $scroll_val ) ), true ); ?>><?php esc_html_e( 'Custom %', 'elonix' ); ?></option>
						</select>
						<div id="es_custom_scroll_val_wrap" style="margin-top: 4px; display: none;">
							<input type="number" name="es_popup_scroll_value_custom" value="<?php echo esc_attr( $scroll_val ); ?>" placeholder="<?php esc_attr_e( 'Custom percentage (1-100)', 'elonix' ); ?>" min="1" max="100" />
						</div>
					</div>

					<!-- Exit Intent Sensitivity -->
					<div class="es-trigger-dep" data-dep="exit_intent" style="margin-top: 8px; display: none;">
						<label style="font-size: 11px; font-weight: bold; display: block; margin-bottom: 4px;">
							<?php esc_html_e( 'Trigger Sensitivity (pixels from viewport top edge):', 'elonix' ); ?>
						</label>
						<input type="number" name="es_popup_exit_intent_sensitivity" value="<?php echo esc_attr( $exit_sensitivity ); ?>" placeholder="20" min="5" max="150" />
					</div>
				</div>
			</div>

			<!-- Priority Queue System -->
			<div class="es-popup-meta-row">
				<div class="es-popup-meta-label"><?php esc_html_e( 'Queue Priority (1-100)', 'elonix' ); ?></div>
				<div class="es-popup-meta-field">
					<input type="number" name="es_popup_priority" value="<?php echo esc_attr( $priority ); ?>" min="1" max="100" style="width: 100px;" />
					<p class="description" style="margin-top: 4px; font-size: 11px;">
						<?php esc_html_e( 'Used when multiple popups qualify. Priority: 1 = Highest, 100 = Lowest.', 'elonix' ); ?>
					</p>
				</div>
			</div>

			<!-- Location Targeting Rules -->
			<div class="es-popup-meta-row">
				<div class="es-popup-meta-label"><?php esc_html_e( 'Location Targeting', 'elonix' ); ?></div>
				<div class="es-popup-meta-field">
					<select name="es_popup_target_rule" style="margin-bottom: 8px;">
						<option value="entire_site" <?php selected( $target_rule, 'entire_site' ); ?>><?php esc_html_e( 'Entire Site', 'elonix' ); ?></option>
						<option value="specific_pages" <?php selected( $target_rule, 'specific_pages' ); ?>><?php esc_html_e( 'Specific Pages', 'elonix' ); ?></option>
						<option value="specific_posts" <?php selected( $target_rule, 'specific_posts' ); ?>><?php esc_html_e( 'Specific Posts', 'elonix' ); ?></option>
					</select>
					<br/>
					<input type="text" name="es_popup_target_ids" value="<?php echo esc_attr( $target_ids ); ?>" placeholder="<?php esc_attr_e( 'Target Post/Page IDs (comma separated, e.g. 10,24,105)', 'elonix' ); ?>" />
				</div>
			</div>

			<!-- Device Targeting -->
			<div class="es-popup-meta-row">
				<div class="es-popup-meta-label"><?php esc_html_e( 'Device Support', 'elonix' ); ?></div>
				<div class="es-popup-meta-field">
					<label>
						<input type="checkbox" name="es_popup_devices[]" value="desktop" <?php checked( in_array( 'desktop', $devices, true ) ); ?> />
						<?php esc_html_e( 'Desktop', 'elonix' ); ?>
					</label>
					<label>
						<input type="checkbox" name="es_popup_devices[]" value="tablet" <?php checked( in_array( 'tablet', $devices, true ) ); ?> />
						<?php esc_html_e( 'Tablet', 'elonix' ); ?>
					</label>
					<label>
						<input type="checkbox" name="es_popup_devices[]" value="mobile" <?php checked( in_array( 'mobile', $devices, true ) ); ?> />
						<?php esc_html_e( 'Mobile', 'elonix' ); ?>
					</label>
				</div>
			</div>

			<!-- Frequency Throttling Controls -->
			<div class="es-popup-meta-row">
				<div class="es-popup-meta-label"><?php esc_html_e( 'Display Frequency', 'elonix' ); ?></div>
				<div class="es-popup-meta-field">
					<select name="es_popup_frequency" style="margin-bottom: 8px;">
						<option value="show_once" <?php selected( $frequency, 'show_once' ); ?>><?php esc_html_e( 'Show Once', 'elonix' ); ?></option>
						<option value="session" <?php selected( $frequency, 'session' ); ?>><?php esc_html_e( 'Session Based (Expires on Tab Close)', 'elonix' ); ?></option>
						<option value="cookie" <?php selected( $frequency, 'cookie' ); ?>><?php esc_html_e( 'Cookie Suppressed (Expires in Days)', 'elonix' ); ?></option>
					</select>
					<br/>
					<label style="font-size: 11px;">
						<?php esc_html_e( 'Cookie Expiry Days:', 'elonix' ); ?>
						<input type="number" name="es_popup_cookie_expiry" value="<?php echo esc_attr( $cookie_expiry ); ?>" min="1" max="365" style="width: 70px; display: inline-block;" />
					</label>
				</div>
			</div>

			<!-- ADVANCED CONDITIONS SECTION -->
			<div class="es-popup-meta-section-title"><?php esc_html_e( 'Advanced Display Conditions', 'elonix' ); ?></div>

			<!-- User State -->
			<div class="es-popup-meta-row">
				<div class="es-popup-meta-label"><?php esc_html_e( 'User Authentication State', 'elonix' ); ?></div>
				<div class="es-popup-meta-field">
					<select name="es_popup_user_state">
						<option value="all" <?php selected( $user_state, 'all' ); ?>><?php esc_html_e( 'All Users (No Restrictions)', 'elonix' ); ?></option>
						<option value="logged_in" <?php selected( $user_state, 'logged_in' ); ?>><?php esc_html_e( 'Logged In Users Only', 'elonix' ); ?></option>
						<option value="logged_out" <?php selected( $user_state, 'logged_out' ); ?>><?php esc_html_e( 'Logged Out Users Only', 'elonix' ); ?></option>
					</select>
				</div>
			</div>

			<!-- User Roles -->
			<div class="es-popup-meta-row">
				<div class="es-popup-meta-label"><?php esc_html_e( 'Target User Roles', 'elonix' ); ?></div>
				<div class="es-popup-meta-field">
					<?php
					$roles = array(
						'administrator' => esc_html__( 'Administrator', 'elonix' ),
						'editor'        => esc_html__( 'Editor', 'elonix' ),
						'author'        => esc_html__( 'Author', 'elonix' ),
						'contributor'   => esc_html__( 'Contributor', 'elonix' ),
						'subscriber'    => esc_html__( 'Subscriber', 'elonix' ),
					);
					foreach ( $roles as $role_key => $role_label ) :
						?>
						<label>
							<input type="checkbox" name="es_popup_user_roles[]" value="<?php echo esc_attr( $role_key ); ?>" <?php checked( in_array( $role_key, $user_roles, true ) ); ?> />
							<?php echo esc_html( $role_label ); ?>
						</label>
					<?php endforeach; ?>
					<p class="description" style="margin-top: 4px; font-size: 11px;">
						<?php esc_html_e( 'If roles are checked, the current logged-in user must match at least one selected role.', 'elonix' ); ?>
					</p>
				</div>
			</div>

			<!-- Page Conditions -->
			<div class="es-popup-meta-row">
				<div class="es-popup-meta-label"><?php esc_html_e( 'Specific Page Templates', 'elonix' ); ?></div>
				<div class="es-popup-meta-field">
					<?php
					$page_conds = array(
						'front_page'     => esc_html__( 'Front Page', 'elonix' ),
						'blog_page'      => esc_html__( 'Blog Page', 'elonix' ),
						'search_results' => esc_html__( 'Search Results Page', 'elonix' ),
						'404_page'       => esc_html__( '404 Error Page', 'elonix' ),
						'archive_pages'  => esc_html__( 'Archive Pages', 'elonix' ),
						'single_posts'   => esc_html__( 'Single Posts', 'elonix' ),
						'woo_products'   => esc_html__( 'WooCommerce Single Products', 'elonix' ),
					);
					foreach ( $page_conds as $cond_key => $cond_label ) :
						?>
						<label>
							<input type="checkbox" name="es_popup_page_conditions[]" value="<?php echo esc_attr( $cond_key ); ?>" <?php checked( in_array( $cond_key, $page_conditions, true ) ); ?> />
							<?php echo esc_html( $cond_label ); ?>
						</label>
					<?php endforeach; ?>
					<p class="description" style="margin-top: 4px; font-size: 11px;">
						<?php esc_html_e( 'If conditions are checked, the popup will only render when the current screen matches one of them.', 'elonix' ); ?>
					</p>
				</div>
			</div>

		</div>

		<!-- POPUP JS ENQUEUED EXTERNALLY -->
		<?php
	}

	/**
	 * Save Metabox Settings safely.
	 */
	public function save_popup_settings( $post_id ) {
		// Nonce check validation
		if ( ! isset( $_POST['es_popup_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['es_popup_settings_nonce'] ), 'es_popup_settings_save' ) ) {
			return;
		}

		// Autosave check
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Capability checks
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save fields safely
		if ( isset( $_POST['es_popup_type'] ) ) {
			update_post_meta( $post_id, '_es_popup_type', sanitize_text_field( wp_unslash( $_POST['es_popup_type'] ) ) );
		}

		if ( isset( $_POST['es_popup_trigger_type'] ) ) {
			update_post_meta( $post_id, '_es_popup_trigger_type', sanitize_text_field( wp_unslash( $_POST['es_popup_trigger_type'] ) ) );
		}

		// Save specific trigger values
		if ( isset( $_POST['es_popup_trigger_type'] ) ) {
			$t_type = sanitize_text_field( wp_unslash( $_POST['es_popup_trigger_type'] ) );
			if ( 'time_delay' === $t_type && isset( $_POST['es_popup_trigger_value_delay'] ) ) {
				update_post_meta( $post_id, '_es_popup_trigger_value', sanitize_text_field( wp_unslash( $_POST['es_popup_trigger_value_delay'] ) ) );
			} elseif ( 'click_trigger' === $t_type && isset( $_POST['es_popup_trigger_value_click'] ) ) {
				update_post_meta( $post_id, '_es_popup_trigger_value', sanitize_text_field( wp_unslash( $_POST['es_popup_trigger_value_click'] ) ) );
			} else {
				update_post_meta( $post_id, '_es_popup_trigger_value', '' );
			}

			// Scroll Depth Value Saving
			if ( 'scroll_depth' === $t_type && isset( $_POST['es_popup_scroll_value'] ) ) {
				$s_val = sanitize_text_field( wp_unslash( $_POST['es_popup_scroll_value'] ) );
				if ( 'custom' === $s_val && isset( $_POST['es_popup_scroll_value_custom'] ) ) {
					$s_val = sanitize_text_field( wp_unslash( $_POST['es_popup_scroll_value_custom'] ) );
				}
				update_post_meta( $post_id, '_es_popup_scroll_value', $s_val );
			}
		}

		if ( isset( $_POST['es_popup_exit_intent_sensitivity'] ) ) {
			update_post_meta( $post_id, '_es_popup_exit_intent_sensitivity', intval( $_POST['es_popup_exit_intent_sensitivity'] ) );
		}

		if ( isset( $_POST['es_popup_priority'] ) ) {
			update_post_meta( $post_id, '_es_popup_priority', intval( $_POST['es_popup_priority'] ) );
		}

		if ( isset( $_POST['es_popup_target_rule'] ) ) {
			update_post_meta( $post_id, '_es_popup_target_rule', sanitize_text_field( wp_unslash( $_POST['es_popup_target_rule'] ) ) );
		}

		if ( isset( $_POST['es_popup_target_ids'] ) ) {
			update_post_meta( $post_id, '_es_popup_target_ids', sanitize_text_field( wp_unslash( $_POST['es_popup_target_ids'] ) ) );
		}

		if ( isset( $_POST['es_popup_frequency'] ) ) {
			update_post_meta( $post_id, '_es_popup_frequency', sanitize_text_field( wp_unslash( $_POST['es_popup_frequency'] ) ) );
		}

		if ( isset( $_POST['es_popup_cookie_expiry'] ) ) {
			update_post_meta( $post_id, '_es_popup_cookie_expiry', intval( $_POST['es_popup_cookie_expiry'] ) );
		}

		if ( isset( $_POST['es_popup_devices'] ) && is_array( $_POST['es_popup_devices'] ) ) {
			$clean_devices = array_map( 'sanitize_text_field', wp_unslash( $_POST['es_popup_devices'] ) );
			update_post_meta( $post_id, '_es_popup_devices', $clean_devices );
		} else {
			update_post_meta( $post_id, '_es_popup_devices', array() );
		}

		// Save Advanced Conditions
		if ( isset( $_POST['es_popup_user_state'] ) ) {
			update_post_meta( $post_id, '_es_popup_user_state', sanitize_text_field( wp_unslash( $_POST['es_popup_user_state'] ) ) );
		}

		if ( isset( $_POST['es_popup_user_roles'] ) && is_array( $_POST['es_popup_user_roles'] ) ) {
			$clean_roles = array_map( 'sanitize_text_field', wp_unslash( $_POST['es_popup_user_roles'] ) );
			update_post_meta( $post_id, '_es_popup_user_roles', $clean_roles );
		} else {
			update_post_meta( $post_id, '_es_popup_user_roles', array() );
		}

		if ( isset( $_POST['es_popup_page_conditions'] ) && is_array( $_POST['es_popup_page_conditions'] ) ) {
			$clean_conds = array_map( 'sanitize_text_field', wp_unslash( $_POST['es_popup_page_conditions'] ) );
			update_post_meta( $post_id, '_es_popup_page_conditions', $clean_conds );
		} else {
			update_post_meta( $post_id, '_es_popup_page_conditions', array() );
		}
	}
}
