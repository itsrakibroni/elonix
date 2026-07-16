<?php
namespace Elonix_Toolkit\Modules\Template_Library;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts( $hook ) {
		if ( 'elonix_page_elonix-templates' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'elonix-template-library', ELONIX_ACC_URL . 'assets/admin/css/template-library.css', array(), ELONIX_VERSION );
		wp_enqueue_script( 'elonix-template-library', ELONIX_ACC_URL . 'assets/admin/js/template-library.js', array( 'jquery', 'wp-util' ), ELONIX_VERSION, true );

		$is_dev = class_exists( 'Elonix_Settings' ) && \Elonix_Settings::is_developer_mode() && current_user_can( 'manage_options' );

		wp_localize_script( 'elonix-template-library', 'tvTemplateLibrary', array(
			'api_url' => esc_url_raw( rest_url( 'elonix/v1/library' ) ),
			'dev_api_url' => esc_url_raw( rest_url( 'elonix/v1/developer' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'is_dev'  => $is_dev,
			'strings' => array(
				'importing' => esc_html__( 'Importing...', 'elonix' ),
				'imported'  => esc_html__( 'Imported!', 'elonix' ),
				'error'     => esc_html__( 'Import Failed', 'elonix' ),
				'preview'   => esc_html__( 'Preview', 'elonix' ),
				'import'    => esc_html__( 'Import', 'elonix' ),
				'delete'    => esc_html__( 'Delete', 'elonix' ),
				'confirm_delete' => esc_html__( 'Delete Template\n\nThis will permanently remove this template package from the Local Template Library. The original Elementor template will NOT be deleted.', 'elonix' ),
			)
		) );
	}

	public function render() {
		?>
		<div class="wrap elonix-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Library', 'elonix' ); ?></h1>
			<p><?php esc_html_e( 'Discover and import premium templates and website kits.', 'elonix' ); ?></p>

			<h2 class="nav-tab-wrapper tv-library-tabs">
				<a href="#templates" class="nav-tab nav-tab-active" data-target="templates"><?php esc_html_e( 'Templates', 'elonix' ); ?></a>
				<a href="#kits" class="nav-tab" data-target="kits"><?php esc_html_e( 'Website Kits', 'elonix' ); ?></a>
			</h2>

			<div id="tv-tab-templates" class="tv-library-container tv-tab-content" style="display:block;">
				<!-- Top Bar -->
				<div class="tv-library-toolbar">
					<div class="tv-library-filters">
						<select id="tv-filter-type">
							<option value="all"><?php esc_html_e( 'All Types', 'elonix' ); ?></option>
							<option value="page"><?php esc_html_e( 'Pages', 'elonix' ); ?></option>
							<option value="section"><?php esc_html_e( 'Sections', 'elonix' ); ?></option>
							<option value="header"><?php esc_html_e( 'Headers', 'elonix' ); ?></option>
							<option value="footer"><?php esc_html_e( 'Footers', 'elonix' ); ?></option>
						</select>
					</div>
					<div class="tv-library-search">
						<input type="text" id="tv-search-input" placeholder="<?php esc_attr_e( 'Search templates...', 'elonix' ); ?>">
					</div>
				</div>

				<!-- Grid -->
				<div class="tv-library-grid" id="tv-library-grid">
					<div class="tv-library-loading">
						<span class="spinner is-active"></span> <?php esc_html_e( 'Loading templates...', 'elonix' ); ?>
					</div>
				</div>
			</div>

			<div id="tv-tab-kits" class="tv-library-container tv-tab-content" style="display:none;">
				<!-- Top Bar -->
				<div class="tv-library-toolbar">
					<div class="tv-library-search">
						<input type="text" id="tv-search-kits-input" placeholder="<?php esc_attr_e( 'Search kits...', 'elonix' ); ?>">
					</div>
				</div>

				<!-- Grid -->
				<div class="tv-library-grid" id="tv-kits-grid">
					<div class="tv-library-loading">
						<span class="spinner is-active"></span> <?php esc_html_e( 'Loading kits...', 'elonix' ); ?>
					</div>
				</div>
			</div>

			<!-- Preview Modal -->
			<div id="tv-preview-modal" class="tv-modal" style="display:none;">
				<div class="tv-modal-content">
					<span class="tv-modal-close">&times;</span>
					<div class="tv-modal-body">
						<img id="tv-preview-image" src="" alt="">
					</div>
				</div>
			</div>

			<!-- Import Wizard Modal -->
			<div id="tv-wizard-modal" class="tv-modal" style="display:none;">
				<div class="tv-modal-content tv-wizard-content">
					<span class="tv-modal-close tv-wizard-close">&times;</span>

					<div class="tv-wizard-header">
						<h2 id="tv-wizard-title"><?php esc_html_e( 'Import Template', 'elonix' ); ?></h2>
					</div>

					<div class="tv-wizard-body">
						<!-- Step 1: Info -->
						<div class="tv-wizard-step active" id="tv-step-info">
							<p><?php esc_html_e( 'You are about to import', 'elonix' ); ?> <strong class="tv-tpl-name"></strong>.</p>
							<div class="tv-wizard-meta"></div>
							<div class="tv-wizard-footer">
								<button class="button button-primary tv-wizard-next" data-next="deps"><?php esc_html_e( 'Next: Check Dependencies', 'elonix' ); ?></button>
							</div>
						</div>

						<!-- Step 2: Dependencies -->
						<div class="tv-wizard-step" id="tv-step-deps" style="display:none;">
							<h3><?php esc_html_e( 'System Requirements', 'elonix' ); ?></h3>
							<ul id="tv-wizard-deps-list"></ul>
							<div class="tv-wizard-footer">
								<button class="button button-primary tv-wizard-do-import"><?php esc_html_e( 'Start Import', 'elonix' ); ?></button>
							</div>
						</div>

						<!-- Step 3: Importing -->
						<div class="tv-wizard-step" id="tv-step-importing" style="display:none; text-align:center;">
							<span class="spinner is-active" style="float:none;"></span>
							<p><?php esc_html_e( 'Importing template safely into Elementor...', 'elonix' ); ?></p>
						</div>

						<!-- Step 4: Assignment -->
						<div class="tv-wizard-step" id="tv-step-assign" style="display:none;">
							<h3><?php esc_html_e( 'Import Complete!', 'elonix' ); ?></h3>
							<p><?php esc_html_e( 'What would you like to do next?', 'elonix' ); ?></p>
							<div class="tv-wizard-actions" id="tv-wizard-actions-container"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script type="text/template" id="tmpl-tv-template-card">
			<div class="tv-template-card" data-id="{{data.id}}" data-type="{{data.type}}" data-title="{{data.title.toLowerCase()}}">
				<div class="tv-template-thumbnail">
					<# if ( data.thumbnail ) { #>
						<img src="{{data.thumbnail}}" alt="{{data.title}}">
					<# } else { #>
						<div class="tv-template-placeholder">No Image</div>
					<# } #>
					<div class="tv-template-overlay">
						<button class="button tv-btn-preview" data-preview="{{data.preview}}"><?php esc_html_e( 'Preview', 'elonix' ); ?></button>
						<button class="button button-primary tv-btn-import" data-id="{{data.id}}"><?php esc_html_e( 'Import', 'elonix' ); ?></button>
					</div>
					<# if ( tvTemplateLibrary.is_dev ) { #>
						<button class="button tv-btn-delete-template" data-slug="{{data.slug}}" data-type="{{data.type}}" style="position:absolute;top:10px;right:10px;background:rgba(211,47,47,0.9);color:#fff;border:none;box-shadow:none;z-index: 2;line-height: 0;padding: 0px 11px;"><span class="dashicons dashicons-trash" style="margin-top:2px;"></span></button>
					<# } #>
				</div>
				<div class="tv-template-info">
					<h3 class="tv-template-title">{{data.title}}</h3>
					<div>
						<span class="tv-template-badge">{{data.type}}</span>
						<# if ( data.import_status === 'Imported' ) { #>
							<span class="tv-template-badge" style="background:#46b450; color:#fff;">{{data.import_status}}</span>
						<# } #>
					</div>
				</div>
			</div>
		</script>

		<script type="text/template" id="tmpl-tv-kit-card">
			<div class="tv-template-card" data-slug="{{data.slug}}" data-title="{{data.title.toLowerCase()}}">
				<div class="tv-template-thumbnail">
					<# if ( data.thumbnail ) { #>
						<img src="{{data.thumbnail}}" alt="{{data.title}}">
					<# } else { #>
						<div class="tv-template-placeholder">No Image</div>
					<# } #>
					<div class="tv-template-overlay">
						<button class="button tv-btn-preview" data-preview="{{data.preview}}"><?php esc_html_e( 'Preview', 'elonix' ); ?></button>
						<button class="button button-primary tv-btn-import-kit" data-slug="{{data.slug}}"><?php esc_html_e( 'Import Kit', 'elonix' ); ?></button>
					</div>
				</div>
				<div class="tv-template-info">
					<h3 class="tv-template-title">{{data.title}}</h3>
					<div>
						<span class="tv-template-badge">Kit</span>
					</div>
				</div>
			</div>
		</script>

		<!-- Kit Wizard Modal -->
		<div id="tv-kit-wizard-modal" class="tv-modal" style="display:none;">
			<div class="tv-modal-content tv-wizard-content">
				<span class="tv-modal-close tv-wizard-close">&times;</span>

				<div class="tv-wizard-header">
					<h2 id="tv-kit-wizard-title"><?php esc_html_e( 'Import Website Kit', 'elonix' ); ?></h2>
				</div>

				<div class="tv-wizard-body">
					<!-- Step 1: Info -->
					<div class="tv-wizard-step active" id="tv-kit-step-info">
						<p><?php esc_html_e( 'You are about to import', 'elonix' ); ?> <strong class="tv-kit-name"></strong>.</p>
						<div class="tv-wizard-footer">
							<button class="button button-primary tv-kit-wizard-next" data-next="deps"><?php esc_html_e( 'Next: Dependencies', 'elonix' ); ?></button>
						</div>
					</div>

					<!-- Step 2: Deps -->
					<div class="tv-wizard-step" id="tv-kit-step-deps" style="display:none;">
						<p><?php esc_html_e( 'This kit requires the following plugins/modules:', 'elonix' ); ?></p>
						<ul id="tv-kit-wizard-deps-list"></ul>
						<div class="tv-wizard-footer">
							<button class="button button-primary tv-kit-wizard-next" data-next="components"><?php esc_html_e( 'Next: Select Components', 'elonix' ); ?></button>
						</div>
					</div>

					<!-- Step 3: Select Components -->
					<div class="tv-wizard-step" id="tv-kit-step-components" style="display:none;">
						<p><?php esc_html_e( 'Select the parts of the website you want to import:', 'elonix' ); ?></p>
						<div id="tv-kit-components-list" style="margin: 15px 0; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
							<!-- Checkboxes populated via JS -->
						</div>
						<div class="tv-wizard-footer">
							<button class="button button-primary tv-kit-do-import"><?php esc_html_e( 'Start Import', 'elonix' ); ?></button>
						</div>
					</div>

					<!-- Step 4: Progress -->
					<div class="tv-wizard-step" id="tv-kit-step-progress" style="display:none;">
						<p><?php esc_html_e( 'Importing your website kit...', 'elonix' ); ?></p>
						<ul id="tv-kit-progress-log" style="font-family: monospace; background: #f1f1f1; padding: 10px; max-height: 150px; overflow-y: auto;">
						</ul>
						<div class="tv-library-loading" style="margin-top: 15px;">
							<span class="spinner is-active" style="float:none;"></span>
						</div>
					</div>

					<!-- Step 5: Completed -->
					<div class="tv-wizard-step" id="tv-kit-step-completed" style="display:none;">
						<div style="text-align:center; padding: 20px 0;">
							<span class="dashicons dashicons-yes-alt" style="font-size: 50px; color: #46b450; width: 50px; height: 50px;"></span>
							<h3 style="margin-top: 10px;"><?php esc_html_e( 'Kit Imported Successfully!', 'elonix' ); ?></h3>
							<p><?php esc_html_e( 'Your website structure is ready.', 'elonix' ); ?></p>
						</div>
						<div class="tv-wizard-footer">
							<button class="button button-primary tv-action-close-kit"><?php esc_html_e( 'Finish', 'elonix' ); ?></button>
						</div>
					</div>

				</div>
			</div>
		</div>
		<?php
	}
}
