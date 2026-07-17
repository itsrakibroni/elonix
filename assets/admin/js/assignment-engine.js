jQuery(document).ready(function($) {
	var currentPostId = 0;
	var currentPostType = '';

	$(document).on('click', '.es-open-assignment-drawer', function(e) {
		e.preventDefault();
		currentPostId = $(this).data('id');
		currentPostType = $(this).data('type');

		// Convert to navigation shortcut
		window.location.href = esAssignmentEngine.admin_url + 'post.php?post=' + currentPostId + '&action=edit#es-display-conditions-focus';
	});

	$(document).on('click', '.es-drawer-close, .es-drawer-overlay', function() {
		$('#es-assignment-drawer').removeClass('es-drawer-open');
	});

	$(document).on('click', '.es-remove-rule', function() {
		$(this).closest('li').remove();
	});

	function initSelect2Selectors() {
		var optionsHtml = '<option value="">' + esAssignmentEngine.strings.select_rule + '</option>';
		
		$.each(esAssignmentEngine.rule_options, function(groupKey, group) {
			optionsHtml += '<optgroup label="' + group.label + '">';
			$.each(group.value, function(val, label) {
				optionsHtml += '<option value="' + val + '">' + label + '</option>';
			});
			optionsHtml += '</optgroup>';
		});

		var addBtnTemplate = `
			<div class="es-rule-selector-wrap" style="margin-top: 10px; display: none;">
				<select class="es-rule-primary-select" style="width: 100%;">
					${optionsHtml}
				</select>
				<select class="es-rule-secondary-select" style="width: 100%; margin-top: 5px; display: none;"></select>
				<div style="margin-top: 10px;">
					<button class="button button-primary es-confirm-rule">${esAssignmentEngine.strings.add_rule}</button>
					<button class="button es-cancel-rule">${esAssignmentEngine.strings.cancel}</button>
				</div>
			</div>
		`;

		$('.es-add-rule-btn').each(function() {
			var $btn = $(this);
			var $wrap = $(addBtnTemplate);
			$btn.after($wrap);
			
			var $primary = $wrap.find('.es-rule-primary-select');
			var $secondary = $wrap.find('.es-rule-secondary-select');
			
			$btn.on('click', function() {
				$btn.hide();
				$wrap.show();
				if (!$primary.hasClass('select2-hidden-accessible')) {
					$primary.select2({
						placeholder: esAssignmentEngine.strings.select_rule,
						width: '100%',
						dropdownParent: $('#es-assignment-drawer')
					});
				}
			});

			$wrap.find('.es-cancel-rule').on('click', function() {
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
						placeholder: esAssignmentEngine.strings.search_sub,
						width: '100%',
						dropdownParent: $('#es-assignment-drawer'),
						minimumInputLength: 2,
						ajax: {
							url: esAssignmentEngine.ajax_url,
							dataType: 'json',
							type: 'POST',
							delay: 250,
							data: function(params) {
								return {
									q: params.term,
									rule: val,
									action: 'elonix_get_posts_by_query',
									nonce: esAssignmentEngine.search_nonce
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

			$wrap.find('.es-confirm-rule').on('click', function() {
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
				var li = '<li data-rule="' + finalVal + '"><span class="rule-name">' + finalText + '</span><button class="es-remove-rule">&times;</button></li>';
				
				if (type === 'include') {
					$('#es-include-list').append(li);
				} else {
					$('#es-exclude-list').append(li);
				}

				$wrap.find('.es-cancel-rule').trigger('click');
			});
		});
	}

	$(document).on('click', '.es-save-assignments', function() {
		var $btn = $(this);
		$btn.prop('disabled', true).text(esAssignmentEngine.strings.saving);

		var include = [];
		$('#es-include-list li').each(function() {
			include.push($(this).data('rule'));
		});

		var exclude = [];
		$('#es-exclude-list li').each(function() {
			exclude.push($(this).data('rule'));
		});

		var priority = $('#es-assign-priority').val();
		var active = $('#es-assign-active').is(':checked');

		$.ajax({
			url: esAssignmentEngine.ajax_url,
			method: 'POST',
			data: {
				action: 'elonix_assignment_save',
				nonce: esAssignmentEngine.nonce,
				post_id: currentPostId,
				post_type: currentPostType,
				include: include,
				exclude: exclude,
				priority: priority,
				active: active
			},
			success: function(res) {
				if (res.success) {
					$btn.text(esAssignmentEngine.strings.saved);
					setTimeout(function() {
						$('#es-assignment-drawer').removeClass('es-drawer-open');
						$btn.prop('disabled', false).text('Save Conditions');
						location.reload(); // Refresh list table
					}, 1000);
				} else {
					if (res.data && res.data.conflicts) {
						$btn.prop('disabled', false).text('Save Conditions');
						
						if ($('#es-conflict-warning').length === 0) {
							var conflictHtml = `
								<div id="es-conflict-warning" style="margin-top: 15px; padding: 10px; background: #fcf0f1; border-left: 4px solid #d63638;">
									<p style="margin:0 0 10px 0; color:#d63638;"><strong>Conflicts detected with priority!</strong></p>
									<button class="button es-force-save-btn">Force Save</button>
									<button class="button es-cancel-conflict-btn">Cancel</button>
								</div>
							`;
							$('.es-drawer-footer').prepend(conflictHtml);

							$('.es-force-save-btn').on('click', function() {
								$(this).prop('disabled', true).text('Saving...');
								$.ajax({
									url: esAssignmentEngine.ajax_url,
									method: 'POST',
									data: {
										action: 'elonix_assignment_save',
										nonce: esAssignmentEngine.nonce,
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

							$('.es-cancel-conflict-btn').on('click', function() {
								$('#es-conflict-warning').remove();
							});
						}
					}
				}
			}
		});
	});

	// Highlight logic on load for navigation shortcut
	if (window.location.hash === '#es-display-conditions-focus') {
		var $panel = $('#es_layout_assignments_box');
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
