(function() {
	'use strict';

	var esScreenLoader = {
		config: window.esScreenLoaderConfig || {},
		wrapper: document.getElementById('es-screen-loader'),
		timeoutId: null,
		
		// Stored bound functions to avoid memory leaks
		boundOnLoad: null,
		boundOnElementorInit: null,
		boundOnKeyUp: null,
		boundTransitionEnd: null,

		init: function() {
			if ( ! this.wrapper ) {
				return;
			}

			// Failsafe timeout
			var timeout = parseInt(this.config.timeout, 10);
			if ( isNaN(timeout) || timeout <= 0 ) {
				timeout = 5000;
			}
			
			this.timeoutId = setTimeout(this.hideLoader.bind(this), timeout);

			// Bind functions once and store references
			this.boundOnLoad = this.onLoad.bind(this);
			this.boundOnElementorInit = this.onLoad.bind(this);
			this.boundOnKeyUp = this.onKeyUp.bind(this);

			// Listen for standard window load
			window.addEventListener('load', this.boundOnLoad);

			// Listen for Elementor frontend init
			window.addEventListener('elementor/frontend/init', this.boundOnElementorInit);

			// Emergency Escape key bypass (Accessibility)
			document.addEventListener('keyup', this.boundOnKeyUp);
			
			// Close button bypass
			var closeBtn = document.querySelector('.es-screen-loader__close');
			if ( closeBtn ) {
				closeBtn.addEventListener('click', this.hideLoader.bind(this));
			}
		},

		onLoad: function() {
			// Small delay to ensure all micro-tasks are painted
			requestAnimationFrame(function() {
				requestAnimationFrame(this.hideLoader.bind(this));
			}.bind(this));
		},

		onKeyUp: function(e) {
			if ( ! this.config.enable_escape ) {
				return;
			}
			if ( e.key === 'Escape' || e.keyCode === 27 ) {
				this.hideLoader();
			}
		},

		hideLoader: function() {
			if ( ! this.wrapper ) {
				this.cleanup();
				return;
			}

			// Update session storage if "once" is enabled
			if ( this.config.once ) {
				try {
					sessionStorage.setItem('es_loader_shown', 'true');
				} catch(e) {}
			}

			// Apply out state class
			this.wrapper.className += ' es-loader-out';

			// Remove scroll lock from body
			document.documentElement.className = document.documentElement.className.replace(/\bes-loading-active\b/g, '');
			document.body.className = document.body.className.replace(/\bes-loading-active\b/g, '');

			// Remove element from DOM after transition
			var self = this;
			this.boundTransitionEnd = function(e) {
				if ( e.target === self.wrapper && self.wrapper.parentNode ) {
					self.wrapper.parentNode.removeChild(self.wrapper);
					self.wrapper = null;
				}
				self.cleanup();
			};
			
			this.wrapper.addEventListener('transitionend', this.boundTransitionEnd, { once: true });

			// Failsafe DOM removal in case transitionend fails
			setTimeout(function() {
				if ( self.wrapper && self.wrapper.parentNode ) {
					self.wrapper.parentNode.removeChild(self.wrapper);
					self.wrapper = null;
				}
				self.cleanup();
			}, 1000);
		},
		
		cleanup: function() {
			// Clear timeout if triggered by load or Escape
			if ( this.timeoutId ) {
				clearTimeout(this.timeoutId);
				this.timeoutId = null;
			}

			// Remove event listeners using stored references
			if ( this.boundOnLoad ) {
				window.removeEventListener('load', this.boundOnLoad);
				this.boundOnLoad = null;
			}
			
			if ( this.boundOnElementorInit ) {
				window.removeEventListener('elementor/frontend/init', this.boundOnElementorInit);
				this.boundOnElementorInit = null;
			}
			
			if ( this.boundOnKeyUp ) {
				document.removeEventListener('keyup', this.boundOnKeyUp);
				this.boundOnKeyUp = null;
			}
			
			if ( this.wrapper && this.boundTransitionEnd ) {
				this.wrapper.removeEventListener('transitionend', this.boundTransitionEnd);
			}
			this.boundTransitionEnd = null;
			
			// Null references to make the object garbage-collectable
			this.wrapper = null;
			this.config = null;
		}
	};

	esScreenLoader.init();

})();
