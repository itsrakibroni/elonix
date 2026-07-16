(function($) {
	"use strict";

	class TVFeatureCardsHandler extends elementorModules.frontend.handlers.Base {
		getDefaultSettings() {
			return {
				selectors: {
					carousel: '.tv-fc-carousel',
					swiperContainer: '.swiper-container, .swiper'
				}
			};
		}

		getDefaultElements() {
			var selectors = this.getSettings('selectors');
			var elements = {
				$carousel: this.$element.find(selectors.carousel)
			};
			elements.$swiperContainer = elements.$carousel.find(selectors.swiperContainer).first();
			return elements;
		}

		onInit(...args) {
			// MUST call super.onInit() FIRST — this runs initElements() which populates this.elements
			super.onInit(...args);

			var $carousel = this.elements.$carousel;
			var $swiperContainer = this.elements.$swiperContainer;

			if (!$carousel.length || !$swiperContainer.length) {
				return;
			}

			if (typeof Swiper === 'undefined' || typeof Elonix === 'undefined' || typeof Elonix.Core === 'undefined') {
				return;
			}

			this.swiperElement = $swiperContainer[0];
			this.hasInitialized = false;

			var self = this;

			// If Elementor re-renders the widget, destroy the old instance
			if (Elonix.Core.InstanceRegistry.has(this.swiperElement)) {
				Elonix.Core.InstanceRegistry.destroy(this.swiperElement);
			}

			if (this.checkAndInitialize()) {
				return;
			}

			// Deferred Layout Initialization
			if (typeof ResizeObserver !== 'undefined') {
				this.observer = new ResizeObserver(function(entries) {
					if (self.hasInitialized) {
						self.observer.disconnect();
						return;
					}
					for (var i = 0; i < entries.length; i++) {
						if (entries[i].contentRect.width > 0) {
							if (self.checkAndInitialize()) {
								self.observer.disconnect();
								break;
							}
						}
					}
				});
				this.observer.observe(this.swiperElement);
				if (this.$element[0]) this.observer.observe(this.$element[0]);
			} else {
				var tryInit = function() {
					if (self.hasInitialized) return;
					if (self.checkAndInitialize()) {
						return;
					}
					requestAnimationFrame(tryInit);
				};
				requestAnimationFrame(tryInit);
			}
		}

		checkAndInitialize() {
			if (this.hasInitialized) return true;

			if (this.swiperElement.clientWidth > 0 && this.swiperElement.offsetWidth > 0) {
				this.hasInitialized = true;
				this.initializeSwiper();
				return true;
			}
			return false;
		}

		initializeSwiper() {
			var $carousel = this.elements.$carousel;

			// Handler retrieves settings via inherited Base method (Elementor canonical API)
			var elementSettings = this.getElementSettings();

			var rawSettings = Elonix.Core.SettingsExtractor.getSettings(elementSettings, $carousel);

			var mapping = {
				'slidesPerView': 'slides_per_view',
				'slidesPerGroup': 'slides_per_group',
				'spaceBetween': 'column_gap'
			};

			var resolvedSettings = Elonix.Core.ResponsiveManager.resolve(rawSettings, mapping);

			var extraConfig = {
				loop: rawSettings.loop === 'yes',
				speed: rawSettings.speed ? parseInt(rawSettings.speed, 10) : 500,
				effect: 'slide',
				grabCursor: rawSettings.grabCursor === 'yes',
				mousewheel: rawSettings.mousewheel === 'yes',
				keyboard: rawSettings.keyboard === 'yes' ? { enabled: true } : false,
				autoplay: rawSettings.autoplay === 'yes' ? {
					delay: rawSettings.autoplaySpeed ? parseInt(rawSettings.autoplaySpeed, 10) : 5000,
					disableOnInteraction: false,
					pauseOnMouseEnter: rawSettings.pauseOnHover === 'yes'
				} : false
			};

			var swiperOptions = Elonix.Builders.Swiper.build(resolvedSettings, extraConfig);

			if (rawSettings.navigation === 'yes') {
				swiperOptions.navigation = {
					nextEl: this.$element.find('.tv-swiper-button-next')[0],
					prevEl: this.$element.find('.tv-swiper-button-prev')[0],
				};
			}

			if (rawSettings.pagination === 'yes') {
				swiperOptions.pagination = {
					el: this.$element.find('.swiper-pagination')[0],
					clickable: true,
				};
			}

			this.swiperInstance = new Swiper(this.swiperElement, swiperOptions);

			Elonix.Core.InstanceRegistry.register(this.swiperElement, this.swiperInstance);
		}

		onElementChange(propertyName) {
			// Stub for future use or to satisfy interface if needed,
			// Elementor will call this if PHP frontend_available is set.
		}

		onDestroy() {
			if (this.observer) {
				this.observer.disconnect();
			}
			if (this.swiperElement && Elonix.Core.InstanceRegistry.has(this.swiperElement)) {
				Elonix.Core.InstanceRegistry.destroy(this.swiperElement);
			}
			super.onDestroy();
		}
	}

	$(window).on('elementor/frontend/init', function() {
		elementorFrontend.elementsHandler.attachHandler(
			'tv-feature-cards',
			TVFeatureCardsHandler
		);
	});

})(jQuery);
