jQuery(document).ready(function($) {
	// Init Color Picker
	$('.elonix-color-picker').wpColorPicker({
		change: function(event, ui) {
			setTimeout(updatePreview, 50);
		},
		clear: function() {
			setTimeout(updatePreview, 50);
		}
	});

	// Init Range Slider Sync
	$('.elonix-slider').on('input change', function() {
		var val = $(this).val();
		var unit = $(this).siblings('.slider-unit').data('unit') || '';
		$(this).siblings('.elonix-slider-input').val(val + unit);
		updatePreview();
	});
	
	// Other inputs change
	$('select, input[type="text"], input[type="number"], input[type="checkbox"]').on('change input', function() {
		updatePreview();
	});

	// Init Image Uploader
	var mediaUploader;
	$('.elonix-upload-button').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var $wrapper = $btn.closest('.elonix-image-uploader');
		
		if (mediaUploader) {
			mediaUploader.open();
			return;
		}
		
		mediaUploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: { text: 'Choose Image' },
			multiple: false
		});
		
		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			$wrapper.find('.elonix-image-url').val(attachment.url).trigger('change');
			$wrapper.find('.elonix-image-preview img').attr('src', attachment.url);
			$wrapper.find('.elonix-image-preview').show();
			
			var filename = attachment.filename || attachment.url.split('/').pop();
			var dims = (attachment.width && attachment.height) ? attachment.width + ' x ' + attachment.height : '';
			
			if ($wrapper.find('.es-img-meta').length === 0) {
				$wrapper.find('.elonix-image-preview').after('<div class="es-img-meta" style="margin-bottom:12px; font-size:12px; color:#64748b; line-height:1.4;"></div>');
			}
			$wrapper.find('.es-img-meta').html('<strong>' + filename + '</strong><br>' + dims);
			
			$wrapper.find('.elonix-upload-button').text('Replace');
			$wrapper.find('.elonix-reset-button').show();
		});
		
		mediaUploader.open();
	});

	$('.elonix-reset-button').on('click', function(e) {
		e.preventDefault();
		var $wrapper = $(this).closest('.elonix-image-uploader');
		$wrapper.find('.elonix-image-url').val('').trigger('change');
		$wrapper.find('.elonix-image-preview img').attr('src', '');
		$wrapper.find('.elonix-image-preview').hide();
		$wrapper.find('.es-img-meta').remove();
		$wrapper.find('.elonix-upload-button').text('Upload Image');
		$(this).hide();
	});
	
	// Initialize existing image uploaders
	$('.elonix-image-uploader').each(function() {
		var $url = $(this).find('.elonix-image-url').val();
		if ($url) {
			$(this).find('.elonix-upload-button').text('Replace');
			var filename = $url.split('/').pop();
			$(this).find('.elonix-image-preview').after('<div class="es-img-meta" style="margin-bottom:12px; font-size:12px; color:#64748b; line-height:1.4;"><strong>' + filename + '</strong></div>');
		}
	});

	// Conditional Engine UI & Live Preview
	var $engineSelect = $('select[name="elonix_settings[screen_loader][engine]"]');
	var $styleSelect = $('select[name="elonix_settings[screen_loader][style]"]');
	if (!$engineSelect.length) return;

	var $imgRow = $('input[name="elonix_settings[screen_loader][custom_image]"]').closest('tr');
	var $colorRow = $('input[name="elonix_settings[screen_loader][color]"]').closest('tr');
	var $colorAltRow = $('input[name="elonix_settings[screen_loader][color_alt]"]').closest('tr');
	var $styleRow = $styleSelect.closest('tr');

	// Engine descriptions
	var engineDescs = {
		'pure-css': 'Ultra lightweight CSS loader. No external requests.',
		'svg': 'Animated SVG loader. Crisp at any resolution.',
		'logo': 'Company logo animation. Great for branding.',
		'image': 'Custom raster image loader.'
	};
	if ($engineSelect.next('.es-engine-desc').length === 0) {
		$engineSelect.after('<p class="description es-engine-desc"></p>');
	}
	var $engineDesc = $engineSelect.next('.es-engine-desc');

	function updateUI() {
		var engine = $engineSelect.val();
		var style = $styleSelect.val() || 'default';
		
		$engineDesc.text(engineDescs[engine] || '');
		
		if ($styleRow.length) {
			var $styleCard = $styleRow.closest('.es-admin-card-Style');
			if ($styleCard.length) {
				if (engine === 'pure-css') {
					$styleCard.slideDown(300);
				} else {
					$styleCard.slideUp(300);
				}
			} else {
				if (engine === 'pure-css') {
					$styleRow.show();
				} else {
					$styleRow.hide();
				}
			}
		}

		if ($imgRow.length) {
			var isUploadEngine = (engine === 'logo' || engine === 'image' || engine === 'svg');
			if (isUploadEngine) {
				$imgRow.show();
			} else {
				$imgRow.hide();
			}
			
			if (isUploadEngine) {
				var $th = $imgRow.find('th');
				if (engine === 'svg') {
					$th.text('SVG Upload (Media Library)');
				} else if (engine === 'logo') {
					$th.text('Logo Upload (Media Library)');
				} else if (engine === 'image') {
					$th.text('Image Upload (Media Library)');
				}
			}
		}

		if ($colorRow.length) {
			if (engine === 'image') {
				$colorRow.hide();
			} else {
				$colorRow.show();
			}
		}

		if ($colorAltRow.length) {
			var multiColorEngines = ['default', 'dual-ring', 'pulse', 'dots', 'ripple', 'wave', 'orbit'];
			if (engine === 'pure-css' && multiColorEngines.indexOf(style) !== -1) {
				$colorAltRow.show();
			} else {
				$colorAltRow.hide();
			}
		}
	}

	var previewDebounce;
	function updatePreview() {
		clearTimeout(previewDebounce);
		previewDebounce = setTimeout(function() {
			var engine = $engineSelect.val();
			var style = $styleSelect.val() || 'default';
			var bg = $('input[name="elonix_settings[screen_loader][bg]"]').val() || '#ffffff';
			var color = $('input[name="elonix_settings[screen_loader][color]"]').val() || '#000000';
			var colorAlt = $('input[name="elonix_settings[screen_loader][color_alt]"]').val() || '#cccccc';
			var opacity = $('input[name="elonix_settings[screen_loader][opacity]"]').siblings('input[type="range"]').val() || '1';
			var size = ($('input[name="elonix_settings[screen_loader][size]"]').siblings('input[type="range"]').val() || '40') + 'px';
			var speed = ($('input[name="elonix_settings[screen_loader][speed]"]').siblings('input[type="range"]').val() || '0.5') + 's';
			var customImage = $('input[name="elonix_settings[screen_loader][custom_image]"]').val() || '';
			var animType = $('select[name="elonix_settings[screen_loader][animation]"]').val() || 'fade';
			
			// Update Live Text
			$('#es-live-engine').text(engine);
			$('#es-live-style').text(style);
			$('#es-live-anim').text(animType);
			$('#es-live-speed').text(speed);
			$('#es-live-color').text(color);
			$('#es-live-color-alt').text(colorAlt);

			var $previewWrap = $('#es-screen-loader-preview');
			var $previewInner = $('#es-screen-loader-preview-inner');

		// Apply opacity and background
		var rgb = hexToRgb(bg);
		if (rgb) {
			$previewWrap.css('background-color', 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + opacity + ')');
		} else {
			$previewWrap.css('background-color', bg);
			$previewWrap.css('opacity', opacity);
		}

		// Render loader
		var html = '';
		if (engine === 'pure-css') {
			if (style === 'default') {
				html = '<div class="es-screen-loader--default" style="--es-loader-size:'+size+';--es-loader-color:'+color+';--es-loader-secondary:'+colorAlt+';--es-loader-speed:'+speed+';--es-loader-ring-width: 4px;--es-loader-shadow: 0 0 10px rgba(0,0,0,0.2);"><div class="es-screen-loader__spinner"><span class="es-screen-loader__ring"></span></div></div>';
			} else if (style === 'spinner') {
				html = '<div style="width:'+size+';height:'+size+';border:4px solid '+colorAlt+';border-top-color:'+color+';border-radius:50%;animation:es-spin '+speed+' linear infinite;"></div>';
			} else if (style === 'dual-ring') {
				html = '<div style="width:'+size+';height:'+size+';border:4px solid transparent;border-top-color:'+color+';border-bottom-color:'+colorAlt+';border-radius:50%;animation:es-spin '+speed+' linear infinite;"></div>';
			} else if (style === 'pulse') {
				html = '<div style="width:'+size+';height:'+size+';background-color:'+color+';border-radius:50%;animation:es-pulse '+speed+' ease-in-out infinite;"></div>';
			} else if (style === 'dots') {
				html = '<div style="display:flex;gap:5px;"><div style="width:10px;height:10px;border-radius:50%;background-color:'+color+';animation:es-bounce '+speed+' infinite alternate;"></div><div style="width:10px;height:10px;border-radius:50%;background-color:'+colorAlt+';animation:es-bounce '+speed+' infinite alternate;animation-delay:0.2s;"></div><div style="width:10px;height:10px;border-radius:50%;background-color:'+color+';animation:es-bounce '+speed+' infinite alternate;animation-delay:0.4s;"></div></div>';
			} else if (style === 'ripple') {
				html = '<div style="position:relative;width:'+size+';height:'+size+';"><div style="position:absolute;border:4px solid '+color+';opacity:1;border-radius:50%;animation:es-ripple '+speed+' cubic-bezier(0, 0.2, 0.8, 1) infinite;"></div><div style="position:absolute;border:4px solid '+colorAlt+';opacity:1;border-radius:50%;animation:es-ripple '+speed+' cubic-bezier(0, 0.2, 0.8, 1) infinite;animation-delay:-0.5s;"></div></div>';
			} else {
				html = '<div style="color:'+color+';">[' + style + ']</div>';
			}
		} else if (engine === 'logo' || engine === 'image' || engine === 'svg') {
			if (customImage) {
				html = '<img src="'+customImage+'" style="max-width:'+size+';height:auto;animation:es-pulse '+speed+' ease-in-out infinite;">';
			} else {
				html = '<div style="color:'+color+';">No Image</div>';
			}
		} else {
			html = '<div style="color:'+color+';">[' + engine + ']</div>';
		}

		$previewInner.html(html);
		}, 50);
	}

	function hexToRgb(hex) {
		var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
		return result ? {
			r: parseInt(result[1], 16),
			g: parseInt(result[2], 16),
			b: parseInt(result[3], 16)
		} : null;
	}

	if ($engineSelect.length) {
		$engineSelect.on('change', function() {
			updateUI();
			updatePreview();
		});
	}
	if ($styleSelect.length) {
		$styleSelect.on('change', function() {
			updateUI();
			updatePreview();
		});
	}
	
	// Listen to inputs for instant live preview debounce
	$('input, select', '.elonix-settings-wrap').on('change input', function() {
		updatePreview();
	});

	updateUI();
	updatePreview();

	// Group into Admin Cards
	var cardStructure = [
		{ title: 'General', desc: 'Core loader settings.', keys: ['enable', 'engine', 'custom_image'] },
		{ title: 'Style', desc: 'Visual appearance and colors.', keys: ['style', 'bg', 'color', 'color_alt', 'opacity', 'size', 'speed', 'zindex'] },
		{ title: 'Behaviour', desc: 'Animation and timing.', keys: ['animation', 'timeout', 'once', 'enable_escape'] },
		{ title: 'Advanced', desc: 'Custom developer classes.', keys: ['custom_class'] }
	];
	var $table = $('.elonix-settings-wrap table.form-table').first();
	if ($table.length) {
		var $rows = $table.find('> tbody > tr');
		var $container = $('<div class="es-cards-container"></div>');
		$table.before($container);
		
		$.each(cardStructure, function(i, group) {
			var $card = $('<div class="elonix-settings-section-card es-admin-card-' + group.title + '"></div>');
			$card.append('<h2>' + group.title + '</h2>');
			$card.append('<p class="description">' + group.desc + '</p>');
			var $newTable = $('<table class="form-table"><tbody></tbody></table>');
			
			$.each(group.keys, function(j, key) {
				var $row = $rows.filter(function() {
					return $(this).find('[name*="[' + key + ']"]').length > 0;
				});
				if ($row.length) {
					$newTable.find('tbody').append($row);
				}
			});
			$card.append($newTable);
			$container.append($card);
		});
		$table.remove(); // Remove original empty table
	}

	// Inject preview animations if not exist
	if (!$('#es-sl-preview-styles').length) {
		$('head').append('<style id="es-sl-preview-styles">@keyframes es-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } } @keyframes es-pulse { 0% { transform: scale(0.8); opacity: 0.5; } 50% { transform: scale(1); opacity: 1; } 100% { transform: scale(0.8); opacity: 0.5; } } @keyframes es-bounce { 0% { transform: translateY(0); } 100% { transform: translateY(-10px); } } @keyframes es-ripple { 0% { top: 50%; left: 50%; width: 0; height: 0; opacity: 0; transform: translate(-50%, -50%); } 5% { top: 50%; left: 50%; width: 0; height: 0; opacity: 1; transform: translate(-50%, -50%); } 100% { top: 50%; left: 50%; width: 100%; height: 100%; opacity: 0; transform: translate(-50%, -50%); } }</style>');
	}
});
