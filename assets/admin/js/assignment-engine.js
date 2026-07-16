jQuery(document).ready(function($) {
	var currentPostId = 0;
	var currentPostType = '';

	$(document).on('click', '.tv-open-assignment-drawer', function(e) {
		e.preventDefault();
		currentPostId = $(this).data('id');
		currentPostType = $(this).data('type');

		// Convert to navigation shortcut
		window.location.href = tvAssignmentEngine.admin_url + 'post.php?post=' + currentPostId + '&action=edit#tv-display-conditions-focus';
	});

	$(document).on('click', '.tv-drawer-close, .tv-drawer-overlay', function() {
		$('#tv-assignment-drawer').removeClass('tv-drawer-open');
	});

	$(document).on('click', '.tv-remove-rule', function() {
		$(this).closest('li').remove();
	});

	function initSelect2Selectors() {
		var optionsHtml = '<option value="">' + tvAssignmentEngine.strings.select_rule + '</option>';
		
		$.each(tvAssignmentEngine.rule_options, function(groupKey, group) {
			optionsHtml += '<optgroup label="' + group.label + '">';
			$.each(group.value, function(val, label) {
				optionsHtml += '<option value="' + val + '">' + label + '</option>';
			});
			optionsHtml += '</optgroup>';
		});

		var addBtnTemplate = `
			<div class="tv-rule-selector-wrap" style="margin-top: 10px; display: none;">
				<select class="tv-rule-primary-select" style="width: 100%;">
					${optionsHtml}
				</select>
				<select class="tv-rule-secondary-select" style="width: 100%; margin-top: 5px; display: none;"></select>
				<div style="margin-top: 10px;">
					<button class="button button-primary tv-confirm-rule">${tvAssignmentEngine.strings.add_rule}</button>
					<button class="button tv-cancel-rule">${tvAssignmentEngine.strings.cancel}</button>
				</div>
			</div>
		`;

		$('.tv-add-rule-btn').each(function() {
			var $btn = $(this);
			var $wrap = $(addBtnTemplate);
			$btn.after($wrap);
			
			var $primary = $wrap.find('.tv-rule-primary-select');
			var $secondary = $wrap.find('.tv-rule-secondary-select');
			
			$btn.on('click', function() {
				$btn.hide();
				$wrap.show();
				if (!$primary.hasClass('select2-hidden-accessible')) {
					$primary.select2({
						placeholder: tvAssignmentEngine.strings.select_rule,
						width: '100%',
						dropdownParent: $('#tv-assignment-drawer')
					});
				}
			});

			$wrap.find('.tv-cancel-rule').on('click', function() {
				$wrap.hide();
				$btn.show();
				$primary.val(null).trigger('change');
				$secondary.val(null).trigger('change').hide();
			});

			$primary.on('change', function() {
				var val = $(this).val();
				if (!val) {
					$secondary.hide();
					return;
				}

				if (val.indexOf('|specific') !== -1) {
					$secondary.show();
					if ($secondary.hasClass('select2-hidden-accessible')) {
						$secondary.select2('destroy');
					}
					$secondary.empty().select2({
						placeholder: tvAssignmentEngine.strings.search_sub,
						width: '100%',
						dropdownParent: $('#tv-assignment-drawer'),
						minimumInputLength: 2,
						ajax: {
							url: tvAssignmentEngine.ajax_url,
							dataType: 'json',
							type: 'POST',
							delay: 250,
							data: function(params) {
								return {
									q: params.term,
									rule: val,
									action: 'elonix_get_posts_by_query',
									nonce: tvAssignmentEngine.search_nonce
								};
							},
							processResults: function(data) {
								return { results: data };
							}
						}
					});
				} else {
					$secondary.hide();
				}
			});

			$wrap.find('.tv-confirm-rule').on('click', function() {
				var primaryVal = $primary.val();
				var primaryText = $primary.find('option:selected').text();
				var finalVal = primaryVal;
				var finalText = primaryText;

				if (!primaryVal) return;

				if (primaryVal.indexOf('|specific') !== -1) {
					var secondaryVal = $secondary.val();
					if (!secondaryVal) return;
					var secondaryData = $secondary.select2('data')[0];
					
					finalVal = secondaryVal; // e.g. "post-123" or "tax-45"
					finalText = primaryText + ' -> ' + secondaryData.text;
				}

				var type = $btn.data('type');
				var li = '<li data-rule="' + finalVal + '"><span class="rule-name">' + finalText + '</span><button class="tv-remove-rule">&times;</button></li>';
				
				if (type === 'include') {
					$('#tv-include-list').append(li);
				} else {
					$('#tv-exclude-list').append(li);
				}

				$wrap.find('.tv-cancel-rule').trigger('click');
			});
		});
	}

	$(document).on('click', '.tv-save-assignments', function() {
		var $btn = $(this);
		$btn.prop('disabled', true).text(tvAssignmentEngine.strings.saving);

		var include = [];
		$('#tv-include-list li').each(function() {
			include.push($(this).data('rule'));
		});

		var exclude = [];
		$('#tv-exclude-list li').each(function() {
			exclude.push($(this).data('rule'));
		});

		var priority = $('#tv-assign-priority').val();
		var active = $('#tv-assign-active').is(':checked');

		$.ajax({
			url: tvAssignmentEngine.ajax_url,
			method: 'POST',
			data: {
				action: 'elonix_assignment_save',
				nonce: tvAssignmentEngine.nonce,
				post_id: currentPostId,
				post_type: currentPostType,
				include: include,
				exclude: exclude,
				priority: priority,
				active: active
			},
			success: function(res) {
				if (res.success) {
					$btn.text(tvAssignmentEngine.strings.saved);
					setTimeout(function() {
						$('#tv-assignment-drawer').removeClass('tv-drawer-open');
						$btn.prop('disabled', false).text('Save Conditions');
						location.reload(); // Refresh list table
					}, 1000);
				} else {
					if (res.data && res.data.conflicts) {
						$btn.prop('disabled', false).text('Save Conditions');
						
						if ($('#tv-conflict-warning').length === 0) {
							var conflictHtml = `
								<div id="tv-conflict-warning" style="margin-top: 15px; padding: 10px; background: #fcf0f1; border-left: 4px solid #d63638;">
									<p style="margin:0 0 10px 0; color:#d63638;"><strong>Conflicts detected with priority!</strong></p>
									<button class="button tv-force-save-btn">Force Save</button>
									<button class="button tv-cancel-conflict-btn">Cancel</button>
								</div>
							`;
							$('.tv-drawer-footer').prepend(conflictHtml);

							$('.tv-force-save-btn').on('click', function() {
								$(this).prop('disabled', true).text('Saving...');
								$.ajax({
									url: tvAssignmentEngine.ajax_url,
									method: 'POST',
									data: {
										action: 'elonix_assignment_save',
										nonce: tvAssignmentEngine.nonce,
										post_id: currentPostId,
										post_type: currentPostType,
										include: include,
										exclude: exclude,
										priority: priority,
										active: active,
										force: 1
									},
									success: function() {
										location.reload();
									}
								});
							});

							$('.tv-cancel-conflict-btn').on('click', function() {
								$('#tv-conflict-warning').remove();
							});
						}
					}
				}
			}
		});
	});

	// Highlight logic on load for navigation shortcut
	if (window.location.hash === '#tv-display-conditions-focus') {
		var $panel = $('#tv_layout_assignments_box');
		if ($panel.length) {
			// Ensure metabox is expanded
			if ($panel.hasClass('closed')) {
				$panel.removeClass('closed');
			}
			
			// Scroll to the panel
			$('html, body').animate({
				scrollTop: $panel.offset().top - 50
			}, 500);

			// Pulse animation
			$panel.css('transition', 'box-shadow 0.3s ease-in-out, border 0.3s ease-in-out');
			$panel.css('box-shadow', '0 0 15px rgba(59, 130, 246, 0.6)');
			$panel.css('border', '1px solid #3b82f6');
			
			setTimeout(function() {
				$panel.css('box-shadow', '');
				$panel.css('border', '');
				// Remove hash cleanly
				history.replaceState(null, null, ' ');
			}, 2000);
		}
	}
});
