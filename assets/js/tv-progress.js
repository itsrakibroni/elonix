/**
 * Elonix Progress Bar Widget JS
 */

(function($) {
	'use strict';

	var TVProgressHandler = elementorModules.frontend.handlers.Base.extend({
		
		getDefaultSettings: function() {
			return {
				selectors: {
					progress: '.tv-progress',
					counter: '.tv-progress__value-number, .tv-progress__marker-val',
					circleFill: '.tv-progress__circle-fill'
				},
				classes: {
					animated: 'is-animated'
				}
			};
		},

		getDefaultElements: function() {
			var selectors = this.getSettings('selectors');
			return {
				$progress: this.$element.find(selectors.progress),
				$counters: this.$element.find(selectors.counter),
				$circleFills: this.$element.find(selectors.circleFill)
			};
		},

		getFrontendSettings: function() {
			var $progress = this.elements.$progress;
			var dataSettings = $progress.length && $progress.data('settings') ? $progress.data('settings') : {};
			var liveSettings = {};
			
			var keys = ['enable_counter', 'enable_animation', 'animate_once', 'restart_on_scroll', 'animation_duration', 'animation_delay', 'animation_curve'];
			keys.forEach(function(key) {
				var val = this.getElementSettings(key);
				if (val !== undefined && val !== '') {
					if (val === 'yes') val = true;
					if (val === 'no') val = false;
					if (key === 'animation_duration' || key === 'animation_delay') {
						if (typeof val === 'object' && val.size !== undefined) {
							val = parseFloat(val.size) || 0;
						} else {
							val = parseFloat(val) || 0;
						}
					}
					liveSettings[key] = val;
				}
			}.bind(this));
			
			return $.extend({}, dataSettings, liveSettings);
		},

		onInit: function() {
			elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);

			if (this.elements.$progress.length) {
				this.initSVGCalculations();
				this.initObserver();
			}
		},

		initSVGCalculations: function() {
			var self = this;
			this.elements.$circleFills.each(function() {
				var $fill = $(this);
				var radius = parseFloat($fill.attr('r'));
				var isSemi = self.elements.$progress.hasClass('tv-progress--semi-circle');
				var circumference = 2 * Math.PI * radius;
				
				if (isSemi) {
					circumference = circumference / 2;
				}

				$fill.css('--tv-progress-dasharray', circumference);
				
				var percent = parseFloat($fill.data('percentage')) || 0;
				var offset = circumference - (percent / 100) * circumference;
				$fill.css('--tv-progress-dashoffset', offset);
			});
		},

		initObserver: function() {
			var self = this;
			var $progress = this.elements.$progress;
			var classes = this.getSettings('classes');
			var settings = this.getFrontendSettings();
			var animateOnce = settings.animate_once;
			var enableAnimation = settings.enable_animation;

			if (!enableAnimation || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
				$progress.addClass(classes.animated);
				self.initCounters();
				return;
			}

			var observerOptions = {
				root: null,
				rootMargin: '0px 0px -50px 0px',
				threshold: 0
			};

			this.observer = new IntersectionObserver(function(entries, observer) {
				entries.forEach(function(entry) {
					if (entry.isIntersecting) {
						$(entry.target).addClass(classes.animated);
						self.initCounters();
						if (animateOnce) {
							observer.unobserve(entry.target);
						}
					} else if (settings.restart_on_scroll && !animateOnce) {
						$(entry.target).removeClass(classes.animated);
						// Reset counters
						self.elements.$counters.each(function() {
							$(this).removeClass('is-counted').text('0');
						});
					}
				});
			}, observerOptions);

			$progress.each(function() {
				self.observer.observe(this);
			});
		},

		cubicBezier: function(x1, y1, x2, y2) {
			var cx = 3.0 * x1, bx = 3.0 * (x2 - x1) - cx, ax = 1.0 - cx - bx;
			var cy = 3.0 * y1, by = 3.0 * (y2 - y1) - cy, ay = 1.0 - cy - by;
			var sampleCurveX = function(t) { return ((ax * t + bx) * t + cx) * t; };
			var sampleCurveY = function(t) { return ((ay * t + by) * t + cy) * t; };
			var sampleCurveDerivativeX = function(t) { return (3.0 * ax * t + 2.0 * bx) * t + cx; };
			var solveCurveX = function(x, epsilon) {
				var t0, t1, t2, x2, d2, i;
				for (t2 = x, i = 0; i < 8; i++) {
					x2 = sampleCurveX(t2) - x;
					if (Math.abs(x2) < epsilon) return t2;
					d2 = sampleCurveDerivativeX(t2);
					if (Math.abs(d2) < 1e-6) break;
					t2 = t2 - x2 / d2;
				}
				t0 = 0.0; t1 = 1.0; t2 = x;
				if (t2 < t0) return t0;
				if (t2 > t1) return t1;
				while (t0 < t1) {
					x2 = sampleCurveX(t2);
					if (Math.abs(x2 - x) < epsilon) return t2;
					if (x > x2) t0 = t2; else t1 = t2;
					t2 = (t1 - t0) * 0.5 + t0;
				}
				return t2;
			};
			return function(x) { return sampleCurveY(solveCurveX(x, 1e-5)); };
		},

		getEasingFn: function(easing) {
			var beziers = {
				'ease': [0.25, 0.1, 0.25, 1],
				'ease-in': [0.42, 0, 1, 1],
				'ease-out': [0, 0, 0.58, 1],
				'ease-in-out': [0.42, 0, 0.58, 1],
				'linear': [0, 0, 1, 1],
				'cubic-bezier(0.175, 0.885, 0.32, 1.275)': [0.175, 0.885, 0.32, 1.275],
				'cubic-bezier(0.68, -0.55, 0.265, 1.55)': [0.68, -0.55, 0.265, 1.55]
			};
			var b = beziers[easing] || beziers['ease-in-out'];
			return this.cubicBezier(b[0], b[1], b[2], b[3]);
		},

		initCounters: function() {
			var settings = this.getFrontendSettings();
			var duration = settings.animation_duration !== undefined ? settings.animation_duration : 1500;
			var delay = settings.animation_delay !== undefined ? settings.animation_delay : 0;
			var enableCounter = settings.enable_counter;
			var animateOnce = settings.animate_once;
			var curve = settings.animation_curve || 'ease-in-out';
			var easingFn = this.getEasingFn(curve);
			var self = this;
			
			if (!enableCounter) {
				return;
			}

			this.elements.$counters.each(function() {
				var $counter = $(this);
				if ($counter.hasClass('is-counted') && animateOnce) {
					return;
				}
				
				var targetValue = parseFloat($counter.attr('data-target'));
				if (isNaN(targetValue)) return;
				
				$counter.addClass('is-counted');
				
				if (duration <= 0) {
					var finalVal = targetValue % 1 !== 0 ? targetValue.toFixed(1) : targetValue;
					$counter.text(finalVal);
					return;
				}

				var startTime = null;
				var hasStarted = false;
				
				var formatValue = function(current) {
					return targetValue % 1 !== 0 ? current.toFixed(1) : Math.floor(current);
				};

				var animateCounter = function(timestamp) {
					if (!startTime) startTime = timestamp;
					var elapsed = timestamp - startTime;
					
					if (elapsed < delay) {
						self.counterFrames = self.counterFrames || [];
						self.counterFrames.push(requestAnimationFrame(animateCounter));
						return;
					}
					
					if (!hasStarted) {
						hasStarted = true;
						startTime = timestamp - delay; // Reset start time to precisely after delay
						elapsed = 0;
					}
					
					var progress = Math.min(elapsed / duration, 1);
					var easedProgress = easingFn(progress);
					var currentVal = easedProgress * targetValue;
					
					$counter.text(formatValue(currentVal));
					
					if (progress < 1) {
						self.counterFrames = self.counterFrames || [];
						self.counterFrames.push(requestAnimationFrame(animateCounter));
					} else {
						$counter.text(targetValue % 1 !== 0 ? targetValue.toFixed(1) : targetValue);
					}
				};
				
				self.counterFrames = self.counterFrames || [];
				self.counterFrames.push(requestAnimationFrame(animateCounter));
			});
		},

		onElementChange: function(propertyName) {
			if (propertyName === 'layout_mode' || 
				propertyName === 'current_value' || 
				propertyName === 'max_value' ||
				propertyName.indexOf('animation') !== -1 ||
				propertyName === 'enable_counter' ||
				propertyName === 'display_format') {
				
				if (this.observer && this.elements.$progress.length) {
					var self = this;
					this.elements.$progress.each(function() {
						self.observer.unobserve(this);
					});
				}

				if (this.counterFrames) {
					this.counterFrames.forEach(function(id) { cancelAnimationFrame(id); });
					this.counterFrames = [];
				}

				this.elements.$counters.removeClass('is-counted').text('0');
				this.elements.$progress.removeClass(this.getSettings('classes.animated'));

				if (this.elements.$progress.length) {
					// Force DOM reflow to ensure CSS transition restarts
					void this.elements.$progress[0].offsetHeight;
					
					this.initSVGCalculations();
					this.initObserver();
				}
			}
		},

		onDestroy: function() {
			if (this.observer && this.elements.$progress.length) {
				var self = this;
				this.elements.$progress.each(function() {
					self.observer.unobserve(this);
				});
			}
		}
	});

	$(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction('frontend/element_ready/tv-progress.default', function($element) {
			elementorFrontend.elementsHandler.addHandler(TVProgressHandler, {
				$element: $element
			});
		});
	});

})(jQuery);
