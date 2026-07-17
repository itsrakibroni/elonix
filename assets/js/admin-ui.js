jQuery(document).ready(function($) {
	// Debug logging removed for production

	// Setup Focus Trap Utility for Accessibility
	function setupFocusTrap($modal) {
		var focusableElements = $modal.find('a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex="0"], [contenteditable]');
		var $first = focusableElements.first();
		var $last = focusableElements.last();

		$modal.off('keydown.focus_trap').on('keydown.focus_trap', function(e) {
			if (e.key === 'Tab' || e.keyCode === 9) {
				if (e.shiftKey) {
					if (document.activeElement === $first[0]) {
						$last.focus();
						e.preventDefault();
					}
				} else {
					if (document.activeElement === $last[0]) {
						$first.focus();
						e.preventDefault();
					}
				}
			}
		});
	}

	// Search endpoint config
	function initSelect2Ajax(element) {
		var $select = $(element);
		if ($select.hasClass('select2-hidden-accessible')) {
			return; // Already initialized
		}
		$select.select2({
			width: '100%',
			dropdownParent: $select.closest('.es-modal-card'),
			placeholder: esAdminUiL10n.placeholder,
			minimumInputLength: 2,
			language: {
				noResults: function() {
					return esAdminUiL10n.noResults;
				},
				searching: function() {
					return esAdminUiL10n.searching;
				},
				inputTooShort: function(args) {
					return esAdminUiL10n.inputTooShort;
				}
			},
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				delay: 250,
				method: 'POST',
				data: function(params) {
					var ruleVal = $select.closest('form').find('.es-modal-condition-select').val();
					return {
						q: params.term,
						rule: ruleVal,
						action: 'elonix_get_posts_by_query',
						nonce: esAdminUiL10n.nonce
					};
				},
				processResults: function(data) {
					return {
						results: data
					};
				},
				cache: true
			}
		});

		$select.on('select2:select', function(e) {
		});
	}

	// Tab Toggle for create modal
	$('.es-modal-tab-link').on('click', function(e) {
		e.preventDefault();
		$('.es-modal-tab-link').removeClass('active');
		$(this).addClass('active');

		var targetTab = $(this).data('tab');
		if ('create_new' === targetTab) {
			$('#tab_create_new').show();
			$('#tab_import_file').hide();
		} else {
			$('#tab_create_new').hide();
			$('#tab_import_file').show();
		}
		setupFocusTrap($(this).closest('.es-modal-overlay'));
	});

	// Triggers Modal: Create Header
	$('#es_create_header_btn').on('click', function() {
		$('#es_create_modal').find('form')[0].reset();
		$('#es_create_modal').find('.es-modal-condition-select').val('').trigger('change');
		$('#es_create_modal').find('.es-select2-ajax-search').val(null).trigger('change');
		$('#template_type').val('es_header');
		$('.es-modal-tab-link[data-tab="create_new"]').click();
		$('#es_create_modal').addClass('open');
		setupFocusTrap($('#es_create_modal'));
		$('#template_name').focus();
	});

	// Triggers Modal: Create Footer
	$('#es_create_footer_btn').on('click', function() {
		$('#es_create_modal').find('form')[0].reset();
		$('#es_create_modal').find('.es-modal-condition-select').val('').trigger('change');
		$('#es_create_modal').find('.es-select2-ajax-search').val(null).trigger('change');
		$('#template_type').val('es_footer');
		$('.es-modal-tab-link[data-tab="create_new"]').click();
		$('#es_create_modal').addClass('open');
		setupFocusTrap($('#es_create_modal'));
		$('#template_name').focus();
	});

	// Triggers Modal: Import JSON
	$('#es_import_btn').on('click', function() {
		$('#es_create_modal').addClass('open');
		$('.es-modal-tab-link[data-tab="import_file"]').click();
		setupFocusTrap($('#es_create_modal'));
		$('#es_import_file').focus();
	});

	// Triggers Modal: Close triggers
	$('#es_create_modal_close, .es-modal-cancel').on('click', function() {
		$('.es-modal-overlay').removeClass('open');
	});

	// Escape button modal and dropdown close helper
	$(document).on('keydown', function(e) {
		if (e.keyCode === 27) { // ESC key
			$('.es-modal-overlay').removeClass('open');
			$('.es-actions-dropdown-wrapper').removeClass('active');
			$('.es-row').removeClass('es-row-active');
		}
	});

	// Select all row checkboxes (supporting top and bottom checkbox syncing)
	$('#cb-select-all-top').on('change', function() {
		var isChecked = $(this).is(':checked');
		$('.es-layout-row-checkbox').prop('checked', isChecked);
		setTimeout(updateStickyBulkBar, 50);
	});

	// Also support checkbox checking manually
	$('.es-layout-row-checkbox').on('change', function() {
		var allChecked = $('.es-layout-row-checkbox:not(:checked)').length === 0;
		$('#cb-select-all-top').prop('checked', allChecked);
		setTimeout(updateStickyBulkBar, 50);
	});

	// Sticky Bulk Bar count/visibility updater
	function updateStickyBulkBar() {
		var selectedCount = $('.es-layout-row-checkbox:checked').length;
		if (selectedCount > 0) {
			$('.es-selected-count').text(selectedCount + ' ' + (selectedCount === 1 ? 'template' : 'templates') + ' selected');
			$('#es_sticky_bulk_bar').addClass('active');
		} else {
			$('#es_sticky_bulk_bar').removeClass('active');
		}
	}

	$('#es_sticky_bulk_cancel').on('click', function() {
		$('.es-layout-row-checkbox, #cb-select-all-top').prop('checked', false);
		updateStickyBulkBar();
	});

	$('#es_sticky_bulk_apply').on('click', function(e) {
		e.preventDefault();
		var actionVal = $('#es_sticky_bulk_action').val();
		if (!actionVal) {
			if (typeof ElonixNotifier !== 'undefined') {
				ElonixNotifier.warning('Please select a bulk action to apply.');
			}
			return;
		}
		// Populate the top select and trigger submission
		$('#bulk-action-selector-top').val(actionVal);
		$('#es_bulk_form').submit();
	});

	// Dropdown Actions Toggle Handler (Click-based dropdown with active row z-index raising)
	$(document).on('click', '.es-actions-dropdown-trigger', function(e) {
		e.preventDefault();
		e.stopPropagation();
		var $wrapper = $(this).closest('.es-actions-dropdown-wrapper');
		var $row = $(this).closest('.es-row');

		// Close other dropdowns and reset row z-index values
		$('.es-actions-dropdown-wrapper').not($wrapper).removeClass('active');
		$('.es-row').not($row).removeClass('es-row-active');

		$wrapper.toggleClass('active');
		$row.toggleClass('es-row-active', $wrapper.hasClass('active'));
	});

	$(document).on('click', function(e) {
		if (!$(e.target).closest('.es-actions-dropdown-wrapper').length) {
			$('.es-actions-dropdown-wrapper').removeClass('active');
			$('.es-row').removeClass('es-row-active');
		}
	});

	// Quick Edit triggers handler (Delegated event binding for dynamic rows reliability)
	$(document).on('click', '.es-quick-edit-trigger', function(e) {
		e.preventDefault();
		var id = $(this).data('id');
		var title = $(this).data('title');
		var type = $(this).data('type');
		var priority = $(this).data('priority');
		var status = $(this).data('status');
		var condition = $(this).data('condition');
		var specifics = $(this).data('specifics');

		// Safe parsing for specifics JSON attribute data
		if (typeof specifics === 'string') {
			try {
				specifics = JSON.parse(specifics);
			} catch (err) {
				specifics = [];
			}
		}
		if (!specifics || !Array.isArray(specifics)) {
			specifics = [];
		}

		$('#quick_edit_id').val(id);
		$('#quick_edit_name').val(title);
		$('#quick_edit_type').val(type);
		$('#quick_edit_priority').val(priority);
		$('#quick_edit_status').val(status);

		var $conditionSelect = $('#quick_edit_condition');
		$conditionSelect.val(condition);

		var $searchWrapper = $('#es_quick_edit_modal').find('.es-specific-search-wrapper');
		var $select2 = $searchWrapper.find('.es-select2-ajax-search');

		// Clear select2
		$select2.empty().val(null).trigger('change');

		if (condition && condition.indexOf('specific') !== -1) {
			$searchWrapper.show();
			initSelect2Ajax($select2);

			// Render selected specifics options
			if (specifics && specifics.length > 0) {
				specifics.forEach(function(item) {
					var opt = new Option(item.text, item.id, true, true);
					$select2.append(opt);
				});
				$select2.trigger('change');
			}
		} else {
			$searchWrapper.hide();
		}

		$('#es_quick_edit_modal').addClass('open');
		setupFocusTrap($('#es_quick_edit_modal'));
		$('#quick_edit_name').focus();
	});

	$('#es_quick_edit_close').on('click', function() {
		$('#es_quick_edit_modal').removeClass('open');
	});

	// Dynamic display conditions select toggling
	$(document).on('change', '.es-modal-condition-select', function() {
		var ruleVal = $(this).val();
		var $modal = $(this).closest('.es-modal-card');
		var $searchWrapper = $modal.find('.es-specific-search-wrapper');
		var $select2 = $searchWrapper.find('.es-select2-ajax-search');

		$select2.empty().val(null).trigger('change');

		if (ruleVal && ruleVal.indexOf('specific') !== -1) {
			$searchWrapper.show();
			initSelect2Ajax($select2);
		} else {
			$searchWrapper.hide();
		}
	});

	// JS Search Filter (searches template name, shortcode ID, or literal shortcode syntax, or type)
	$('#es_live_search').on('keyup', function() {
		var searchValue = $(this).val().toLowerCase().trim();
		// Regex helper to extract ID if user types something like [es_header id="12"]
		var idMatch = searchValue.match(/\bid\s*=\s*["']?(\d+)["']?/);
		var idSearch = idMatch ? idMatch[1] : ($.isNumeric(searchValue) ? searchValue : '');

		$('.es-row').each(function() {
			var $row = $(this);
			var rowTitle = $row.data('title') || '';
			var rowId = String($row.data('id') || '');
			var rowType = $row.data('type') || ''; // 'es_header' or 'es_footer'
			var typeFriendly = ('es_header' === rowType) ? 'header' : 'footer';

			var matches = false;
			if (rowTitle.indexOf(searchValue) > -1) {
				matches = true;
			} else if (rowId.indexOf(searchValue) > -1 || (idSearch && rowId === idSearch)) {
				matches = true;
			} else if (rowType.indexOf(searchValue) > -1 || typeFriendly.indexOf(searchValue) > -1) {
				matches = true;
			}

			if (matches) {
				$row.show();
			} else {
				$row.hide();
			}
		});
	});

	// Get Shortcode Modal Trigger
	$('.es-shortcode-modal-trigger').on('click', function(e) {
		e.preventDefault();
		var shortcode = $(this).data('shortcode');
		var php = $(this).data('php');

		$('#shortcode_modal_text').val(shortcode);
		$('#php_modal_text').val(php);
		$('#copy_shortcode_modal_btn').attr('data-copy-text', shortcode);
		$('#copy_php_modal_btn').attr('data-copy-text', php);

		$('#es_shortcode_modal').addClass('open');
		setupFocusTrap($('#es_shortcode_modal'));
	});

	$('#es_shortcode_close, #es_shortcode_cancel').on('click', function() {
		$('#es_shortcode_modal').removeClass('open');
	});

	// Toast Notification Helper
	function esShowToast(message) {
		var $toast = $('#es-toast-notification');
		if ($toast.length === 0) {
			$toast = $('<div id="es-toast-notification" style="position:fixed; bottom:20px; right:20px; background:#ffffff; color:#2c3338; border-left:4px solid #46b450; padding:10px 16px; font-size:13px; font-weight:600; font-family:\'Inter\', sans-serif; box-shadow:0 3px 6px rgba(0,0,0,0.1); z-index:999999; display:none; border-radius:0 4px 4px 0;"></div>');
			$('body').append($toast);
		}
		$toast.text(message).stop(true, true).fadeIn(150).delay(2000).fadeOut(250);
	}

	// Copy to Clipboard Event Handler
	$(document).on('click', '.es-copy-btn', function(e) {
		e.preventDefault();
		var textToCopy = $(this).attr('data-copy-text');
		var successMsg = $(this).attr('data-success-msg') || 'Copied!';

		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(textToCopy).then(function() {
				esShowToast(successMsg);
			}, function() {
				fallbackCopy(textToCopy, successMsg);
			});
		} else {
			fallbackCopy(textToCopy, successMsg);
		}
	});

	function fallbackCopy(textToCopy, successMsg) {
		var $temp = $('<input>');
		$('body').append($temp);
		$temp.val(textToCopy).select();
		document.execCommand('copy');
		$temp.remove();
		esShowToast(successMsg);
	}
});
