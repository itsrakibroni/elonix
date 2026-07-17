jQuery(document).ready(function($) {
	// Setup AJAX Select2 search function
	function initSelect2Ajax(element) {
		var $select = $(element);
		$select.select2({
			placeholder: esDisplayConditionsL10n.placeholder,
			minimumInputLength: 2,
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				delay: 250,
				method: 'POST',
				data: function(params) {
					var $row = $select.closest('.es-rule-row-item');
					var ruleVal = $row.find('.es-rule-condition-select').val();
					return {
						q: params.term,
						rule: ruleVal,
						action: 'elonix_get_posts_by_query',
						nonce: esDisplayConditionsL10n.nonce
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
	}

	// Initialize Select2 on existing fields
	$('.es-select2-ajax-search').each(function() {
		initSelect2Ajax(this);
	});

	// Toggle specific select box visibility and handle select2 activation/clearing
	$(document).on('change', '.es-rule-condition-select', function() {
		var ruleVal = $(this).val();
		var $rowItem = $(this).closest('.es-rule-row-item');
		var $searchWrapper = $rowItem.find('.es-specific-search-wrapper');
		var $select2 = $searchWrapper.find('.es-select2-ajax-search');

		// Clear any existing selection
		$select2.val(null).trigger('change');

		if (ruleVal && ruleVal.indexOf('specific') !== -1) {
			$searchWrapper.show();
			initSelect2Ajax($select2);
		} else {
			$searchWrapper.hide();
		}
	});

	// Add location rule row
	$('.es-add-rule-row-btn').on('click', function(e) {
		e.preventDefault();
		var $wrapper = $(this).closest('.es-target-rules-wrapper');
		var name = $wrapper.data('name');
		var $container = $wrapper.find('.es-rules-rows-container');
		
		// Find next index
		var nextIdx = 0;
		$container.find('.es-rule-row-item').each(function() {
			var idx = parseInt($(this).attr('data-index'));
			if (idx >= nextIdx) {
				nextIdx = idx + 1;
			}
		});

		var tmpl = wp.template('es-location-row-template');
		var html = tmpl({ index: nextIdx, name: name });
		$container.append(html);
	});

	// Remove location rule row
	$(document).on('click', '.es-delete-rule-row', function() {
		var $wrapper = $(this).closest('.es-target-rules-wrapper');
		var $container = $wrapper.find('.es-rules-rows-container');
		var rowCount = $container.find('.es-rule-row-item').length;

		if (rowCount > 1) {
			$(this).closest('.es-rule-row-item').remove();
		} else {
			var $row = $(this).closest('.es-rule-row-item');
			$row.find('.es-rule-condition-select').val('').trigger('change');
			$row.find('.es-select2-ajax-search').val(null).trigger('change');
		}
	});

	// Add user role rule row
	$('.es-add-user-row-btn').on('click', function(e) {
		e.preventDefault();
		var $wrapper = $(this).closest('.es-user-rules-wrapper');
		var name = $wrapper.data('name');
		var $container = $wrapper.find('.es-user-rows-container');

		var nextIdx = 0;
		$container.find('.es-user-row-item').each(function() {
			var idx = parseInt($(this).attr('data-index'));
			if (idx >= nextIdx) {
				nextIdx = idx + 1;
			}
		});

		var tmpl = wp.template('es-user-row-template');
		var html = tmpl({ index: nextIdx, name: name });
		$container.append(html);
	});

	// Remove user role rule row
	$(document).on('click', '.es-delete-user-row', function() {
		var $wrapper = $(this).closest('.es-user-rules-wrapper');
		var $container = $wrapper.find('.es-user-rows-container');
		var rowCount = $container.find('.es-user-row-item').length;

		if (rowCount > 1) {
			$(this).closest('.es-user-row-item').remove();
		} else {
			$(this).closest('.es-user-row-item').find('select').val('');
		}
	});
});
