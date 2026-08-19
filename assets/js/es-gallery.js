/**
 * Elonix Gallery Widget JS
 */

(function($) {
	'use strict';

	var ESGalleryHandler = elementorModules.frontend.handlers.Base.extend({
		
		getDefaultSettings: function() {
			return {
				selectors: {
					masonry: '.es-gallery__masonry',
					grid: '.es-gallery__grid',
					filterBar: '.es-gallery__filter-bar',
					filterBtn: '.es-gallery__filter-btn',
					item: '.es-gallery__item',
					loadMoreBtn: '.es-gallery__load-more-btn',
					wrapper: '.es-gallery'
				}
			};
		},

		getDefaultElements: function() {
			var selectors = this.getSettings('selectors');
			return {
				$masonry: this.$element.find(selectors.masonry),
				$grid: this.$element.find(selectors.grid),
				$filterBar: this.$element.find(selectors.filterBar),
				$filterBtns: this.$element.find(selectors.filterBtn),
				$items: this.$element.find(selectors.item),
				$loadMoreBtn: this.$element.find(selectors.loadMoreBtn),
				$wrapper: this.$element.find(selectors.wrapper)
			};
		},

		bindEvents: function() {
			var self = this;
			if (this.elements.$filterBtns.length) {
				this.elements.$filterBtns.on('click', function(e) {
					e.preventDefault();
					var $btn = $(this);
					var filterValue = $btn.attr('data-filter');
					
					self.elements.$filterBtns.removeClass('es-active').attr('aria-pressed', 'false');
					$btn.addClass('es-active').attr('aria-pressed', 'true');
					
					if (self.isotopeInstance) {
						var filterSelector = filterValue === '*' ? '*' : '[data-category="' + filterValue + '"]';
						self.isotopeInstance.arrange({ filter: filterSelector });
					}
					
					// Reset button state based on remaining items for this filter
					self.checkLoadMoreState();
				});
			}

			if (this.elements.$loadMoreBtn.length) {
				this.elements.$loadMoreBtn.on('click', function(e) {
					e.preventDefault();
					if (elementorFrontend.isEditMode()) {
						self.showEditorNotice();
						return; // Disable AJAX in Elementor editor
					}
					self.loadMoreItems();
				});
			}
		},

		showEditorNotice: function() {
			var $btnWrapper = this.elements.$loadMoreBtn.parent();
			if ($btnWrapper.find('.es-gallery__editor-notice').length) {
				return; // Prevent spam
			}

			var $notice = $('<div class="es-gallery__editor-notice" role="status" aria-live="polite" style="position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); margin-bottom: 10px; padding: 10px 15px; background-color: #111; color: #fff; border-radius: 4px; font-size: 13px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.1); white-space: nowrap; pointer-events: none;">Load More is disabled in Elementor Preview.<br>Please view the live page to test AJAX.</div>');
			
			if ($btnWrapper.css('position') === 'static') {
				$btnWrapper.css('position', 'relative');
			}
			
			$btnWrapper.append($notice);

			setTimeout(function() {
				$notice.fadeOut(300, function() {
					$(this).remove();
				});
			}, 3500);
		},

		onInit: function() {
			elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);

			var elementSettings = this.getElementSettings();
			var layoutMode = elementSettings.layout_mode || 'grid';

			if (layoutMode === 'masonry' && this.elements.$masonry.length) {
				this.initializeIsotope();
			}
			
			// Global Resize Debouncer
			var self = this;
			this.resizeTimer = null;
			$(window).on('resize.esGallery' + this.$element.data('id'), function() {
				clearTimeout(self.resizeTimer);
				self.resizeTimer = setTimeout(function() {
					if (self.isotopeInstance) {
						self.isotopeInstance.layout();
					}
				}, 250);
			});

			this.checkLoadMoreState();
		},

		initializeIsotope: function() {
			var self = this;
			var $grid = this.elements.$masonry.find('.es-isotope-grid');
			
			if (!window.Isotope || !$grid.length) return;

			// 1. Create Isotope immediately
			self.isotopeInstance = new Isotope($grid[0], {
				itemSelector: '.es-gallery__item',
				layoutMode: 'masonry',
				percentPosition: true
			});

			if (typeof Elonix !== 'undefined' && Elonix.Core && Elonix.Core.InstanceRegistry) {
				Elonix.Core.InstanceRegistry.register(self.$element.data('id') + '_isotope', self.isotopeInstance);
			}

			// 2. Attach imagesLoaded()
			if (typeof imagesLoaded !== 'undefined') {
				var imgLoad = imagesLoaded($grid[0]);
				
				// 3. On every image progress call isotope.layout()
				imgLoad.on('progress', function() {
					if (self.isotopeInstance) {
						self.isotopeInstance.layout();
					}
				});
				
				// 4. On imagesLoaded done call layout() again
				imgLoad.on('done', function() {
					if (self.isotopeInstance) {
						self.isotopeInstance.layout();
					}
				});
			}
		},

		checkLoadMoreState: function() {
			var ajaxSettings = this.elements.$wrapper.data('ajax-settings');
			if (!ajaxSettings || !this.elements.$loadMoreBtn.length) return;

			var activeFilter = this.elements.$filterBtns.filter('.es-active').attr('data-filter') || '*';
			var filterSelector = activeFilter === '*' ? '.es-gallery__item' : '.es-gallery__item[data-category="' + activeFilter + '"]';
			var currentOffset = this.$element.find(filterSelector).length;

			// If we know there are no more items, disable button.
			// This will be properly evaluated by the server response in loadMoreItems.
			// We reset the button text to default just in case.
			var $btn = this.elements.$loadMoreBtn;
			$btn.removeClass('is-loading').prop('disabled', false).attr('aria-busy', 'false');
			$btn.find('.es-gallery__load-more-text').text($btn.data('default-text'));
			$btn.find('.es-gallery__spinner').hide();
			$btn.show();
		},

		loadMoreItems: function() {
			var self = this;
			var $btn = this.elements.$loadMoreBtn;
			var ajaxSettings = this.elements.$wrapper.data('ajax-settings');
			
			if (!ajaxSettings || $btn.hasClass('is-loading')) return;

			var activeFilter = this.elements.$filterBtns.filter('.es-active').attr('data-filter') || '*';
			var filterSelector = activeFilter === '*' ? '.es-gallery__item' : '.es-gallery__item[data-category="' + activeFilter + '"]';
			var currentOffset = this.$element.find(filterSelector).length;

			// UI Loading State
			$btn.addClass('is-loading').prop('disabled', true).attr('aria-busy', 'true');
			$btn.find('.es-gallery__load-more-text').text($btn.data('loading-text'));
			$btn.find('.es-gallery__spinner').show();

			var data = {
				action: 'elonix_gallery_load_more',
				post_id: ajaxSettings.post_id,
				widget_id: ajaxSettings.widget_id,
				nonce: ajaxSettings.nonce,
				offset: currentOffset,
				limit: ajaxSettings.limit,
				active_filter: activeFilter
			};

			$.ajax({
				url: elementorFrontend.config.environmentMode.ajaxurl || '/wp-admin/admin-ajax.php',
				type: 'POST',
				data: data,
				success: function(response) {
					if (response.success && response.data.html) {
						var $newItems = $(response.data.html);
						var elementSettings = self.getElementSettings();
						var layoutMode = elementSettings.layout_mode || 'grid';
						var $container = layoutMode === 'masonry' ? self.elements.$masonry.find('.es-isotope-grid') : self.elements.$grid;

						$container.append($newItems);

						if (layoutMode === 'masonry' && self.isotopeInstance && typeof imagesLoaded !== 'undefined') {
							$newItems.imagesLoaded(function() {
								self.isotopeInstance.appended($newItems);
								
								// Reapply current filter
								var currentFilterSelector = activeFilter === '*' ? '*' : '[data-category="' + activeFilter + '"]';
								self.isotopeInstance.arrange({ filter: currentFilterSelector });
								self.isotopeInstance.layout();
								
								// Shift focus for accessibility
								$newItems.first().attr('tabindex', '-1').trigger('focus');
							});
						} else {
							// Shift focus for accessibility
							$newItems.first().attr('tabindex', '-1').trigger('focus');
						}

						// Handle button visibility
						if (!response.data.has_more) {
							$btn.hide();
						} else {
							$btn.removeClass('is-loading').prop('disabled', false).attr('aria-busy', 'false');
							$btn.find('.es-gallery__load-more-text').text($btn.data('default-text'));
							$btn.find('.es-gallery__spinner').hide();
						}
					} else {
						// Error or no more items
						$btn.hide();
					}
				},
				error: function() {
					$btn.removeClass('is-loading').prop('disabled', false).attr('aria-busy', 'false');
					$btn.find('.es-gallery__load-more-text').text($btn.data('default-text'));
					$btn.find('.es-gallery__spinner').hide();
				}
			});
		},

		onElementChange: function(propertyName) {
			var isMasonryControl = propertyName.indexOf('columns') === 0 || 
			                       propertyName.indexOf('column_gap') === 0 ||
			                       propertyName.indexOf('row_gap') === 0;

			var elementSettings = this.getElementSettings();
			var layoutMode = elementSettings.layout_mode || 'grid';

			// 7. Reinitialize inside Elementor Editor
			if (layoutMode === 'masonry' && isMasonryControl) {
				if (this.isotopeInstance) {
					this.isotopeInstance.layout();
				}
			}
		},

		onDestroy: function() {
			// 6. Destroy safely
			if (typeof Elonix !== 'undefined' && Elonix.Core && Elonix.Core.InstanceRegistry) {
				if (Elonix.Core.InstanceRegistry.has(this.$element.data('id') + '_isotope')) {
					Elonix.Core.InstanceRegistry.destroy(this.$element.data('id') + '_isotope');
				}
			} else {
				if (this.isotopeInstance) {
					this.isotopeInstance.destroy();
					this.isotopeInstance = null;
				}
			}

			if (this.elements.$filterBtns.length) {
				this.elements.$filterBtns.off('click');
			}
			
			if (this.elements.$loadMoreBtn.length) {
				this.elements.$loadMoreBtn.off('click');
			}
			
			$(window).off('resize.esGallery' + this.$element.data('id'));
		}
	});

	$(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction('frontend/element_ready/es-gallery.default', function($element) {
			elementorFrontend.elementsHandler.addHandler(ESGalleryHandler, {
				$element: $element
			});
		});
	});

})(jQuery);
