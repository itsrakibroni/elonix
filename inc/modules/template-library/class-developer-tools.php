<?php
namespace Elonix_Toolkit\Modules\Template_Library;

use Elonix_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Developer_Tools {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ( ! $this->is_developer_mode() ) {
			return;
		}


		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'render_modal' ) );
	}

	private function is_developer_mode() {
		return class_exists( 'Elonix_Settings' ) && Elonix_Settings::is_developer_mode();
	}

	// add_developer_actions has been removed because it is now centralized in Elonix_Admin_Row_Actions.


	public function enqueue_scripts( $hook ) {
		if ( ! $this->is_developer_mode() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();
		$post_type = $screen ? $screen->post_type : '';

		$supported_post_types = array(
			'elonix_header', 'elonix_footer', 'elonix_single',
			'elonix_archive', 'es_search_template', 'elonix_popup', 'es_404_template', 'es_loop'
		);

		$supported_hooks = array(
			'elonix_page_elonix-header-footer',
			'elonix_page_elonix-templates'
		);

		$is_supported = false;

		if ( in_array( $hook, $supported_hooks, true ) ) {
			$is_supported = true;
		} elseif ( 'edit.php' === $hook && in_array( $post_type, $supported_post_types, true ) ) {
			$is_supported = true;
		}

		if ( ! $is_supported ) {
			return;
		}

		wp_enqueue_media();

		// Create inline styles & scripts to avoid missing file 404s during initial dev
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Dummy inline style handle. No physical asset exists.
		wp_register_style( 'elonix-dev-tools', false );
		wp_enqueue_style( 'elonix-dev-tools' );
		wp_add_inline_style( 'elonix-dev-tools', '
			.es-dev-modal-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:99999; display:flex; align-items:center; justify-content:center; }
			.es-modal-content { max-width: 600px; width:95%; background:#fff; border-radius:4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display:flex; flex-direction:column; max-height: 90vh; position: relative; overflow:hidden; }
			.es-modal-header { padding: 15px 20px; border-bottom: 1px solid #ddd; background: #f8f9fa; }
			.es-modal-header h2 { margin: 0; font-size: 1.2em; }
		' );

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion, WordPress.WP.EnqueuedResourceParameters.NotInFooter -- Dummy inline script handle. Version is irrelevant because no file is loaded.
		wp_register_script( 'elonix-dev-tools', false );
		wp_enqueue_script( 'elonix-dev-tools' );
		wp_add_inline_script( 'elonix-dev-tools', '
			jQuery(document).ready(function($){
				$(document).on("click", ".es-dev-add-library", function(e){
					e.preventDefault();
					var id = $(this).data("id");
					var title = $(this).data("title");
					var nonce = $(this).data("nonce");
					var type = $(this).data("type");
					var slug = "";
					if(title){
						slug = String(title).toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/(^-|-$)+/g, "");
					}

					$("#es_dev_post_id").val(id);
					$("#es_dev_nonce").val(nonce);
					$("#es_dev_title").val(title);
					$("#es_dev_slug").val(slug);
					$("#es_dev_type").val(type);

					$("#es-dev-modal").show();
				});

				$(document).on("click", ".es-dev-modal-close", function(){
					$("#es-dev-modal").hide();
				});

				$(document).on("submit", "#es-dev-library-form", function(e){
					e.preventDefault();
					var $btn = $("#es-dev-submit-btn");
					$btn.prop("disabled", true).text(esDevTools.strings.processing);
					$("#es-dev-feedback").css("color", "inherit").text("");

					var data = $(this).serialize();
					$.ajax({
						url: esDevTools.api_url + "/package/library",
						method: "POST",
						beforeSend: function(xhr) {
							xhr.setRequestHeader("X-WP-Nonce", esDevTools.nonce);
						},
						data: data,
						success: function(res) {
							if(res.success) {
								$("#es-dev-feedback").css("color", "green").text(res.message);
								$("#es_dev_conflict_action").val(""); // reset
								setTimeout(function(){
									window.location.href = "' . esc_url( admin_url( 'admin.php?page=elonix-templates' ) ) . '";
								}, 2000);
							}
						},
						error: function(err) {
							$btn.prop("disabled", false).text("' . esc_html__( 'Generate & Add to Library', 'elonix' ) . '");
							if ( err.status === 409 && err.responseJSON && err.responseJSON.code === "slug_exists" ) {
								var conflictHtml = "<div style=\"color:red; margin-bottom:10px;\"><strong>Conflict:</strong> " + err.responseJSON.message + "</div>";
								conflictHtml += "<button type=\"button\" class=\"button es-conflict-btn\" data-action=\"overwrite\" style=\"margin-right:10px; border-color:#d32f2f; color:#d32f2f;\">Overwrite Existing Package</button>";
								conflictHtml += "<button type=\"button\" class=\"button es-conflict-btn\" data-action=\"duplicate\" style=\"margin-right:10px;\">Create Duplicate</button>";
								conflictHtml += "<button type=\"button\" class=\"button es-conflict-btn\" data-action=\"cancel\">Cancel</button>";
								$("#es-dev-feedback").html(conflictHtml);

								$(".es-conflict-btn").on("click", function(){
									var action = $(this).data("action");
									if ( action === "cancel" ) {
										$("#es-dev-feedback").html("");
										$("#es_dev_conflict_action").val("");
										return;
									}
									$("#es_dev_conflict_action").val(action);
									$("#es-dev-library-form").submit();
								});
							} else {
								var msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : esDevTools.strings.error;
								$("#es-dev-feedback").css("color", "red").text(msg);
							}
						}
					});
				});

				var mediaFrame;
				$(document).on("click", ".es-btn-upload-image", function(e){
					e.preventDefault();
					var $btn = $(this);
					var targetId = $btn.data("target");
					var $container = $btn.closest(".es-image-uploader");

					if ( mediaFrame ) {
						mediaFrame.open();
					} else {
						mediaFrame = wp.media({
							title: "Select Image",
							button: { text: "Use this image" },
							multiple: false
						});
					}

					mediaFrame.off("select").on("select", function(){
						var attachment = mediaFrame.state().get("selection").first().toJSON();
						$(targetId).val(attachment.id);
						$container.find("img").attr("src", attachment.url);
						$container.find(".es-image-preview-container").show();
						$btn.text("Replace Image");
						$container.find(".es-btn-remove-image").show();
					});

					mediaFrame.open();
				});

				$(document).on("click", ".es-btn-remove-image", function(e){
					e.preventDefault();
					var $btn = $(this);
					var targetId = $btn.data("target");
					var $container = $btn.closest(".es-image-uploader");

					$(targetId).val("");
					$container.find("img").attr("src", "");
					$container.find(".es-image-preview-container").hide();
					$container.find(".es-btn-upload-image").text( targetId === "#es_dev_thumbnail_id" ? "Select Thumbnail" : "Select Preview" );
					$btn.hide();
				});
				$(document).on("click", ".es-dev-export-package", function(e){
					e.preventDefault();
					var id = $(this).data("id");
					var nonce = $(this).data("nonce");

					var $link = $(this);
					var oldText = $link.text();
					$link.text("Generating...");

					$.ajax({
						url: esDevTools.api_url + "/package/export",
						method: "POST",
						beforeSend: function(xhr) {
							xhr.setRequestHeader("X-WP-Nonce", esDevTools.nonce);
						},
						data: { post_id: id, nonce: nonce },
						success: function(res) {
							$link.text(oldText);
							if(res.success && res.download_url) {
								window.location.href = res.download_url;
							}
						},
						error: function(err) {
							$link.text("Error!");
							setTimeout(function(){ $link.text(oldText); }, 2000);
							var msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : "Error";
							var details = "Endpoint: /package/export\\nMethod: POST\\nStatus: " + err.status + "\\nMessage: " + msg;

							ElonixNotifier.modal({
								title: "Export Package",
								icon: "warning",
								message: "This template has not yet been added to the Local Library. Please add it to the Local Library before exporting a package.",
								details: details,
								buttons: [
									{ text: "Cancel", type: "secondary", onClick: (m) => m.close() },
									{ text: "Go to Add to Library", type: "primary", onClick: (m) => {
										m.close();
										// Find the Add to Library button in the same dropdown and trigger it to preserve context
										$link.closest(".es-dropdown-menu").find(".es-dev-add-library").trigger("click");
									} }
								]
							});
						}
					});
				});

			});
		' );

		wp_localize_script( 'elonix-dev-tools', 'esDevTools', array(
			'api_url' => esc_url_raw( rest_url( 'elonix/v1/developer' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'strings' => array(
				'processing' => esc_html__( 'Processing...', 'elonix' ),
				'success'    => esc_html__( 'Success!', 'elonix' ),
				'error'      => esc_html__( 'An error occurred.', 'elonix' ),
			)
		) );
	}

	public function render_modal() {
		$screen = get_current_screen();
		if ( ! $screen ) return;

		$supported_types = array( 'elonix_header', 'elonix_footer', 'elonix_single', 'elonix_archive', 'es_search_template', 'elonix_popup', 'es_404_template', 'es_loop' );
		$supported_screens = array( 'elonix_page_elonix-header-footer', 'elonix_page_elonix-templates' );

		if ( ! in_array( $screen->post_type, $supported_types, true ) && ! in_array( $screen->id, $supported_screens, true ) ) {
			return;
		}
		?>
		<div id="es-dev-modal" class="es-modal es-dev-modal-overlay" style="display:none;">
			<div class="es-modal-content">
				<span class="es-modal-close es-dev-modal-close" style="position:absolute; top:15px; right:15px; cursor:pointer; font-size:20px; z-index:10; color:#666;">&times;</span>
				<div class="es-modal-header">
					<h2><?php esc_html_e( 'Add to Library (Developer Mode)', 'elonix' ); ?></h2>
				</div>
				<form id="es-dev-library-form" style="display:flex; flex-direction:column; overflow:hidden; flex:1; margin:0;">
					<div class="es-modal-body" style="padding:20px; overflow-y:auto; flex:1;">
						<input type="hidden" id="es_dev_post_id" name="post_id" value="">
						<input type="hidden" id="es_dev_nonce" name="nonce" value="">
						<input type="hidden" id="es_dev_type" name="type" value="">
						<input type="hidden" id="es_dev_conflict_action" name="conflict_action" value="">

						<div class="es-form-group" style="margin-bottom: 15px;">
							<label style="display:block; margin-bottom:5px;"><strong><?php esc_html_e( 'Title', 'elonix' ); ?></strong></label>
							<input type="text" id="es_dev_title" name="title" style="width:100%; padding:8px;" required>
						</div>

						<div class="es-form-group" style="margin-bottom: 15px;">
							<label style="display:block; margin-bottom:5px;"><strong><?php esc_html_e( 'Slug (Folder Name)', 'elonix' ); ?></strong></label>
							<input type="text" id="es_dev_slug" name="slug" style="width:100%; padding:8px;" required>
						</div>

						<div class="es-form-group" style="margin-bottom: 15px;">
							<label style="display:block; margin-bottom:5px;"><strong><?php esc_html_e( 'Description', 'elonix' ); ?></strong></label>
							<textarea id="es_dev_description" name="description" style="width:100%; padding:8px;"></textarea>
						</div>

						<div style="display:flex; gap: 15px; margin-bottom: 15px;">
							<div class="es-form-group" style="flex:1;">
								<label style="display:block; margin-bottom:5px;"><strong><?php esc_html_e( 'Category', 'elonix' ); ?></strong></label>
								<input type="text" id="es_dev_category" name="category" placeholder="<?php esc_attr_e( 'e.g. hero', 'elonix' ); ?>" style="width:100%; padding:8px;">
							</div>
							<div class="es-form-group" style="flex:1;">
								<label style="display:block; margin-bottom:5px;"><strong><?php esc_html_e( 'Tags', 'elonix' ); ?></strong></label>
								<input type="text" id="es_dev_tags" name="tags" placeholder="<?php esc_attr_e( 'Comma separated', 'elonix' ); ?>" style="width:100%; padding:8px;">
							</div>
						</div>

						<div style="display:flex; gap: 15px; margin-bottom: 15px;">
							<div class="es-form-group" style="flex:1;">
								<label style="display:block; margin-bottom:5px;"><strong><?php esc_html_e( 'Version', 'elonix' ); ?></strong></label>
								<input type="text" id="es_dev_version" name="version" value="1.0.0" style="width:100%; padding:8px;">
							</div>
							<div class="es-form-group" style="flex:1;">
								<label style="display:block; margin-bottom:5px;"><strong><?php esc_html_e( 'Author', 'elonix' ); ?></strong></label>
								<input type="text" id="es_dev_author" name="author" value="Elonix" style="width:100%; padding:8px;">
							</div>
						</div>

						<div style="display:flex; gap: 15px; margin-bottom: 15px;">
							<div class="es-form-group" style="flex:1;">
								<label style="display:block; margin-bottom:5px;"><strong><?php esc_html_e( 'Thumbnail Image', 'elonix' ); ?></strong></label>
								<div class="es-image-uploader" style="border:1px dashed #ccc; padding:10px; text-align:center;">
									<input type="hidden" id="es_dev_thumbnail_id" name="thumbnail_id" value="">
									<div class="es-image-preview-container" style="display:none; margin-bottom:10px;">
										<img src="" style="max-width:100%; max-height:100px; display:block; margin:0 auto;">
									</div>
									<button type="button" class="button es-btn-upload-image" data-target="#es_dev_thumbnail_id"><?php esc_html_e( 'Select Thumbnail', 'elonix' ); ?></button>
									<button type="button" class="button es-btn-remove-image" style="display:none;" data-target="#es_dev_thumbnail_id"><?php esc_html_e( 'Remove', 'elonix' ); ?></button>
								</div>
							</div>
							<div class="es-form-group" style="flex:1;">
								<label style="display:block; margin-bottom:5px;"><strong><?php esc_html_e( 'Preview Image', 'elonix' ); ?></strong></label>
								<div class="es-image-uploader" style="border:1px dashed #ccc; padding:10px; text-align:center;">
									<input type="hidden" id="es_dev_preview_id" name="preview_id" value="">
									<div class="es-image-preview-container" style="display:none; margin-bottom:10px;">
										<img src="" style="max-width:100%; max-height:100px; display:block; margin:0 auto;">
									</div>
									<button type="button" class="button es-btn-upload-image" data-target="#es_dev_preview_id"><?php esc_html_e( 'Select Preview', 'elonix' ); ?></button>
									<button type="button" class="button es-btn-remove-image" style="display:none;" data-target="#es_dev_preview_id"><?php esc_html_e( 'Remove', 'elonix' ); ?></button>
								</div>
							</div>
						</div>

						<div class="es-form-group" style="margin-bottom: 15px;">
							<label style="display:block; margin-bottom:5px;"><strong><?php esc_html_e( 'Status', 'elonix' ); ?></strong></label>
							<select id="es_dev_status" name="status" style="width:100%; padding:8px;">
								<option value="Published">Published</option>
								<option value="Draft">Draft</option>
								<option value="Deprecated">Deprecated</option>
							</select>
						</div>

						<div id="es-dev-feedback" style="margin-bottom: 15px; font-weight: bold;"></div>
					</div>
					<div class="es-modal-footer" style="padding: 15px 20px; border-top: 1px solid #ddd; background: #f8f9fa; text-align: right;">
						<button type="submit" class="button button-primary" id="es-dev-submit-btn"><?php esc_html_e( 'Generate Add to Library', 'elonix' ); ?></button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}
}
