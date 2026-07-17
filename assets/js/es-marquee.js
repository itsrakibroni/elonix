/**
 * Elonix Marquee Widget JS
 */
(function($) {
	'use strict';

	var ESMarqueeHandler = elementorModules.frontend.handlers.Base.extend({
		
		getDefaultSettings: function() {
			return {
				selectors: {
					carousel: '.es-marquee__carousel',
					track: '.es-marquee__track'
				}
			};
		},

		getDefaultElements: function() {
			var selectors = this.getSettings('selectors');
			return {
				$carousel: this.$element.find(selectors.carousel),
				$track: this.$element.find(selectors.track)
			};
		},

		onInit: function() {
			elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);

			if (this.elements.$track.length && $.fn.marquee) {
				this.initializeMarquee();
			}
		},

		initializeMarquee: function() {
			var $carousel = this.elements.$carousel;
			var $track = this.elements.$track;
			
			if (typeof Elonix === 'undefined' || typeof Elonix.Core === 'undefined' || !$.fn.marquee) {
				return;
			}
			
			// We can get settings from data-settings or Elementor directly
			var elementSettings = this.getElementSettings();
			var rawSettings = Elonix.Core.SettingsExtractor.getSettings(elementSettings, $carousel);
			
			// Extract parameters
			var durationMs = rawSettings.animation_speed && rawSettings.animation_speed.size ? rawSettings.animation_speed.size * 1000 : 20000;
			var gapVal = rawSettings.item_gap && rawSettings.item_gap.size ? rawSettings.item_gap.size : 30;
			var direction = rawSettings.direction || 'left';
			if (direction === 'top') direction = 'up';
			if (direction === 'bottom') direction = 'down';
			
			// Configuration object for marquee.min.js
			var config = {
				duration: durationMs,
				gap: gapVal,
				delayBeforeStart: rawSettings.delay_before_start || 0,
				direction: direction,
				duplicated: rawSettings.duplicated === true || rawSettings.duplicated === 'yes',
				duplicateCount: rawSettings.duplicateCount || 1,
				pauseOnHover: rawSettings.pause_on_hover === true || rawSettings.pause_on_hover === 'yes',
				pauseOnCycle: rawSettings.pause_on_cycle === true || rawSettings.pause_on_cycle === 'yes',
				startVisible: rawSettings.start_visible === true || rawSettings.start_visible === 'yes',
				allowCss3Support: rawSettings.allow_css3_support === true || rawSettings.allow_css3_support === 'yes'
			};
			
			$carousel.addClass('es-marquee--continuous');
			if (direction === 'up' || direction === 'down') {
				$carousel.addClass('es-marquee--vertical');
			}
			
			// Initialize plugin
			$track.marquee(config);
			
			// Register a proxy instance so InstanceRegistry can destroy it
			var marqueeInstance = {
				$el: $track,
				destroy: function() {
					if (this.$el.length && $.fn.marquee) {
						this.$el.marquee('destroy');
					}
				},
				pause: function() {
					if (this.$el.length && $.fn.marquee) {
						this.$el.marquee('pause');
					}
				},
				resume: function() {
					if (this.$el.length && $.fn.marquee) {
						this.$el.marquee('resume');
					}
				},
				toggle: function() {
					if (this.$el.length && $.fn.marquee) {
						this.$el.marquee('toggle');
					}
				}
			};
			
			// Forward plugin events to the DOM so other components can hook in
			$track.on('beforeStarting', function() {
				$carousel.trigger('es_marquee:beforeStarting');
			});
			$track.on('finished', function() {
				$carousel.trigger('es_marquee:finished');
			});
			$track.on('paused', function() {
				$carousel.trigger('es_marquee:paused');
			});
			$track.on('resumed', function() {
				$carousel.trigger('es_marquee:resumed');
			});

			Elonix.Core.InstanceRegistry.register(this.$element.data('id'), marqueeInstance);
			
			// Pause button binding
			var $pauseBtn = this.$element.find('.es-marquee__pause-btn');
			if ($pauseBtn.length) {
				$pauseBtn.off('click').on('click', function(e) {
					e.preventDefault();
					marqueeInstance.toggle();
					var isPaused = $(this).attr('aria-pressed') === 'true';
					$(this).attr('aria-pressed', !isPaused);
				});
			}
		},

		onElementChange: function(propertyName) {
			var triggerProps = [
				'layout_mode', 'animation_speed', 'pause_on_hover', 'pause_on_cycle', 
				'item_gap', 'direction', 'duplicated', 'duplicateCount',
				'delay_before_start', 'start_visible', 'allow_css3_support'
			];
			
			if (triggerProps.indexOf(propertyName) !== -1 || propertyName.indexOf('item_gap') === 0) {
				if (typeof Elonix !== 'undefined' && typeof Elonix.Core !== 'undefined' && Elonix.Core.InstanceRegistry.has(this.$element.data('id'))) {
					Elonix.Core.InstanceRegistry.destroy(this.$element.data('id'));
				}
				
				if (this.elements.$track.length && $.fn.marquee) {
					this.initializeMarquee();
				}
			}
		},

		onDestroy: function() {
			elementorModules.frontend.handlers.Base.prototype.onDestroy.apply(this, arguments);
			if (typeof Elonix !== 'undefined' && typeof Elonix.Core !== 'undefined' && Elonix.Core.InstanceRegistry.has(this.$element.data('id'))) {
				Elonix.Core.InstanceRegistry.destroy(this.$element.data('id'));
			}
		}
	});

	$(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction('frontend/element_ready/es-marquee.default', function($element) {
			elementorFrontend.elementsHandler.addHandler(ESMarqueeHandler, {
				$element: $element
			});
		});
	});

})(jQuery);
