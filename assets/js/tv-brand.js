/**
 * Elonix Brand Widget JS
 */

(function($) {
	'use strict';

	var TVBrandHandler = elementorModules.frontend.handlers.Base.extend({
		
		getDefaultSettings: function() {
			return {
				selectors: {
					carousel: '.tv-brand__carousel'
				}
			};
		},

		getDefaultElements: function() {
			var selectors = this.getSettings('selectors');
			return {
				$carousel: this.$element.find(selectors.carousel)
			};
		},

		bindEvents: function() {
			// Add any custom events here
		},

		onInit: function() {
			elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);

			if (this.elements.$carousel.length) {
				this.initializeSwiper();
			}
		},

		initializeSwiper: function() {
			var $carousel = this.elements.$carousel;
			var elementSettings = this.getElementSettings();
			var rawSettings = Elonix.Core.SettingsExtractor.getSettings(elementSettings, $carousel);

			var mapping = {
				'slidesPerView': 'slides_per_view',
				'slidesPerGroup': 'slides_per_group',
				'spaceBetween': 'column_gap'
			};

			var resolved = Elonix.Core.ResponsiveManager.resolve(rawSettings, mapping);

			var config = Elonix.Builders.Swiper.build(resolved, rawSettings);

			// Add Continuous Marquee Mode support
			if (rawSettings.continuous_marquee === true) {
				config.freeMode = true;
				config.speed = rawSettings.speed || 5000;
				config.autoplay = {
					delay: 0,
					disableOnInteraction: false,
					pauseOnMouseEnter: rawSettings.pause_on_hover === true
				};
				// Linear easing via CSS ensures smooth scroll
				$carousel.addClass('tv-brand--continuous-marquee');
			}

			// Add Navigation
			if (rawSettings.navigation) {
				config.navigation = {
					nextEl: $carousel.find('.tv-swiper-button-next')[0],
					prevEl: $carousel.find('.tv-swiper-button-prev')[0]
				};
			}

			// Add Pagination
			if (rawSettings.pagination) {
				config.pagination = {
					el: $carousel.find('.swiper-pagination')[0],
					clickable: true,
					type: 'bullets'
				};
			}

			if (rawSettings.reverse_direction) {
				if (config.autoplay) {
					config.autoplay.reverseDirection = true;
				}
			}

			if (rawSettings.grab_cursor) {
				config.grabCursor = true;
			}

			if (rawSettings.keyboard) {
				config.keyboard = {
					enabled: true,
				};
			}

			if (rawSettings.mousewheel) {
				config.mousewheel = true;
			}

			var swiperInstance = new Swiper($carousel.find('.tv-swiper-container')[0], config);
			
			Elonix.Core.InstanceRegistry.register(this.$element.data('id'), swiperInstance);
		},

		onElementChange: function(propertyName) {
			if (propertyName.indexOf('slides_per_view') === 0 || 
			    propertyName.indexOf('slides_per_group') === 0 || 
			    propertyName.indexOf('column_gap') === 0 ||
			    propertyName === 'loop' ||
			    propertyName === 'autoplay' ||
			    propertyName === 'autoplaySpeed' ||
			    propertyName === 'pause_on_hover' ||
			    propertyName === 'speed' ||
			    propertyName === 'continuous_marquee' ||
			    propertyName === 'reverse_direction' ||
			    propertyName === 'navigation' ||
			    propertyName === 'pagination') {
				
				if (Elonix.Core.InstanceRegistry.has(this.$element.data('id'))) {
					Elonix.Core.InstanceRegistry.destroy(this.$element.data('id'));
				}
				
				if (this.elements.$carousel.length) {
					this.initializeSwiper();
				}
			}
		},

		onDestroy: function() {
			if (Elonix.Core.InstanceRegistry.has(this.$element.data('id'))) {
				Elonix.Core.InstanceRegistry.destroy(this.$element.data('id'));
			}
		}
	});

	$(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction('frontend/element_ready/tv-brand.default', function($element) {
			elementorFrontend.elementsHandler.addHandler(TVBrandHandler, {
				$element: $element
			});
		});
	});

})(jQuery);
