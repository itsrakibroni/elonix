jQuery(document).ready(function($) {
	function toggleTriggerFields() {
		var selectedType = $('#es_popup_trigger_type').val();
		$('.es-trigger-dep').hide();
		$('.es-trigger-dep[data-dep="' + selectedType + '"]').show();
	}

	function toggleScrollCustomField() {
		var selectedScroll = $('#es_popup_scroll_value').val();
		if (selectedScroll === 'custom') {
			$('#es_custom_scroll_val_wrap').show();
		} else {
			$('#es_custom_scroll_val_wrap').hide();
		}
	}

	$('#es_popup_trigger_type').on('change', toggleTriggerFields);
	$('#es_popup_scroll_value').on('change', toggleScrollCustomField);

	toggleTriggerFields();
	toggleScrollCustomField();
});
