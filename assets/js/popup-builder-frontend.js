/**
 * Elonix Popup Builder Frontend Engine
 *
 * Implements Elonix_Popup_Manager and ElonixPopupQueue.
 * Handles target device verification, trigger bindings, rendering queue logic,
 * cookie suppression, and WCAG accessibility controls.
 *
 * ZERO jQuery dependency. Uses passive scroll listeners and requestAnimationFrame.
 *
 * @package Elonix_Toolkit
 */

(function() {
	'use strict';

	/**
	 * Queue Manager for Popups
	 * Ensures only one popup is visible at a time to prevent overlay conflicts.
	 * Supports priority system (1 = Highest, 100 = Lowest).
	 */
	class ElonixPopupQueue {
		constructor() {
			this.queue = [];
			this.activePopup = null;
		}

		/**
		 * Add a popup engine instance to the queue and sort by priority.
		 *
		 * @param {Elonix_Popup_Manager} popupInstance
		 */
		add(popupInstance) {
			// Prevent duplicates in queue
			if (this.queue.indexOf(popupInstance) !== -1 || this.activePopup === popupInstance) {
				return;
			}

			this.queue.push(popupInstance);

			// Sort by priority: highest priority (lowest numeric value) first.
			// If priorities are equal, preserve the natural registration order (FIFO).
			this.queue.sort((a, b) => a.priority - b.priority);

			this.process();
		}

		/**
		 * Process the queue and render the next popup if none is active.
		 */
		process() {
			if (this.activePopup || this.queue.length === 0) {
				return;
			}

			this.activePopup = this.queue.shift();
			this.activePopup.render();
		}

		/**
		 * Close the active popup and advance the queue.
		 */
		closeActive() {
			if (!this.activePopup) {
				return;
			}

			const current = this.activePopup;
			this.activePopup = null;

			current.dismiss(() => {
				// Wait for CSS animations to complete before showing the next popup
				setTimeout(() => {
					this.process();
				}, 500);
			});
		}
	}

	// Instantiate the global queue manager on window
	window.ElonixPopupQueue = new ElonixPopupQueue();

	/**
	 * Scroll Trigger Handler
	 * Uses passive listeners, debounced calculations via requestAnimationFrame, and triggers once.
	 */
	class Elonix_Scroll_Trigger {
		/**
		 * Constructor.
		 *
		 * @param {string|number} scrollVal Percentage depth value (e.g. 50)
		 * @param {Function} callback Callback triggered upon meeting target percent
		 */
		constructor(scrollVal, callback) {
			this.targetPercent = parseFloat(scrollVal) || 0;
			this.callback      = callback;
			this.hasTriggered  = false;
			this.ticking       = false;

			this.scrollHandler = this.onScroll.bind(this);
			this.init();
		}

		init() {
			window.addEventListener('scroll', this.scrollHandler, { passive: true });
			// Initial check
			this.onScroll();
		}

		onScroll() {
			if (!this.ticking) {
				window.requestAnimationFrame(() => {
					this.checkScrollDepth();
					this.ticking = false;
				});
				this.ticking = true;
			}
		}

		checkScrollDepth() {
			if (this.hasTriggered) {
				return;
			}

			const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
			const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;

			if (docHeight <= 0) {
				return;
			}

			const scrolledPercent = (scrollTop / docHeight) * 100;

			if (scrolledPercent >= this.targetPercent) {
				this.hasTriggered = true;
				this.destroy();
				this.callback();
			}
		}

		destroy() {
			window.removeEventListener('scroll', this.scrollHandler, { passive: true });
		}
	}

	/**
	 * Exit Intent Trigger Handler
	 * Detects mouse leaving the top edge of viewport (Desktop only).
	 */
	class Elonix_Exit_Intent {
		/**
		 * Constructor.
		 *
		 * @param {number} sensitivity Pixel boundary from top edge
		 * @param {Function} callback Callback triggered when exit intent matches
		 */
		constructor(sensitivity, callback) {
			this.sensitivity  = sensitivity || 20;
			this.callback     = callback;
			this.hasTriggered = false;

			this.mouseleaveHandler = this.onMouseLeave.bind(this);
			this.init();
		}

		init() {
			// Disable on mobile/touch interfaces automatically
			if (window.matchMedia('(max-width: 767px)').matches || ('ontouchstart' in window) || navigator.maxTouchPoints > 0) {
				return;
			}

			document.addEventListener('mouseleave', this.mouseleaveHandler);
		}

		onMouseLeave(e) {
			// Check if mouse left the top boundary of the viewport
			if (e.clientY <= this.sensitivity) {
				this.hasTriggered = true;
				this.destroy();
				this.callback();
			}
		}

		destroy() {
			document.removeEventListener('mouseleave', this.mouseleaveHandler);
		}
	}

	/**
	 * Main Frontend Engine representing a single Popup instance.
	 */
	class Elonix_Popup_Manager {
		/**
		 * Constructor.
		 *
		 * @param {HTMLElement} element
		 */
		constructor(element) {
			this.wrapper      = element;
			this.id           = this.wrapper.getAttribute('data-popup-id');
			this.type         = this.wrapper.classList.contains('tv-popup-type-modal') ? 'modal' : 
			                    (this.wrapper.classList.contains('tv-popup-type-slide_in') ? 'slide_in' : 'notification_bar');
			this.trigger      = this.wrapper.getAttribute('data-trigger');
			this.triggerVal   = this.wrapper.getAttribute('data-trigger-val');
			this.scrollVal    = this.wrapper.getAttribute('data-scroll-val');
			this.sensitivity  = parseInt(this.wrapper.getAttribute('data-sensitivity'), 10) || 20;
			this.priority     = parseInt(this.wrapper.getAttribute('data-priority'), 10) || 10;
			this.frequency    = this.wrapper.getAttribute('data-frequency');
			this.cookieExpiry = parseInt(this.wrapper.getAttribute('data-cookie-expiry'), 10) || 30;
			this.devices      = (this.wrapper.getAttribute('data-devices') || '').split(',');

			this.isInitialized          = false;
			this.previousFocusedElement = null;
			this.keydownHandler         = null;
			this.scrollTriggerInstance  = null;
			this.exitIntentInstance     = null;

			this.init();
		}

		/**
		 * Initialize popup triggers if device matches and frequency isn't suppressed.
		 */
		init() {
			// Ensure Elementor editor or preview matches are protected
			if (document.body.classList.contains('elementor-editor-active') || window.location.href.indexOf('elementor-preview') !== -1) {
				return;
			}

			if ( ! this.matchDevice() ) {
				return;
			}

			if ( this.isSuppressed() ) {
				return;
			}

			this.bindTriggerEvents();
			this.isInitialized = true;
		}

		/**
		 * Check if device viewport supports the current popup.
		 *
		 * @return {boolean} True if matching, false otherwise.
		 */
		matchDevice() {
			const isDesktop = window.matchMedia('(min-width: 1025px)').matches;
			const isTablet  = window.matchMedia('(min-width: 768px) and (max-width: 1024px)').matches;
			const isMobile  = window.matchMedia('(max-width: 767px)').matches;

			if (isDesktop && this.devices.indexOf('desktop') !== -1) return true;
			if (isTablet && this.devices.indexOf('tablet') !== -1) return true;
			if (isMobile && this.devices.indexOf('mobile') !== -1) return true;

			document.body.classList.add('tv-popup-device-mismatch');
			return false;
		}

		/**
		 * Check if display is suppressed based on throttling configurations.
		 *
		 * @return {boolean} True if suppressed.
		 */
		isSuppressed() {
			const onceKey   = `tv_popup_once_${this.id}`;
			const sessKey   = `tv_popup_sess_${this.id}`;
			const cookieKey = `tv_popup_cookie_${this.id}`;

			if ('show_once' === this.frequency) {
				return !!localStorage.getItem(onceKey);
			}

			if ('session' === this.frequency) {
				return !!sessionStorage.getItem(sessKey);
			}

			if ('cookie' === this.frequency) {
				return !!this.getCookie(cookieKey);
			}

			return false;
		}

		/**
		 * Mark the popup as shown to trigger frequency throttling rules.
		 */
		suppressFutureViews() {
			const onceKey   = `tv_popup_once_${this.id}`;
			const sessKey   = `tv_popup_sess_${this.id}`;
			const cookieKey = `tv_popup_cookie_${this.id}`;

			if ('show_once' === this.frequency) {
				localStorage.setItem(onceKey, 'true');
			}

			if ('session' === this.frequency) {
				sessionStorage.setItem(sessKey, 'true');
			}

			if ('cookie' === this.frequency) {
				this.setCookie(cookieKey, 'true', this.cookieExpiry);
			}
		}

		/**
		 * Bind triggers based on CPT Settings.
		 */
		bindTriggerEvents() {
			const queueAdd = () => {
				window.ElonixPopupQueue.add(this);
			};

			if ('page_load' === this.trigger) {
				window.addEventListener('load', queueAdd);
				if (document.readyState === 'complete') {
					queueAdd();
				}
			} else if ('time_delay' === this.trigger) {
				const delaySeconds = parseFloat(this.triggerVal) || 0;
				window.addEventListener('load', () => {
					setTimeout(queueAdd, delaySeconds * 1000);
				});
				if (document.readyState === 'complete') {
					setTimeout(queueAdd, delaySeconds * 1000);
				}
			} else if ('scroll_depth' === this.trigger) {
				this.scrollTriggerInstance = new Elonix_Scroll_Trigger(this.scrollVal, queueAdd);
			} else if ('exit_intent' === this.trigger) {
				this.exitIntentInstance = new Elonix_Exit_Intent(this.sensitivity, queueAdd);
			} else if ('click_trigger' === this.trigger) {
				if (this.triggerVal) {
					document.addEventListener('click', (e) => {
						let target = e.target;
						while (target && target !== document) {
							if (target.matches && target.matches(this.triggerVal)) {
								e.preventDefault();
								queueAdd();
								break;
							}
							target = target.parentNode;
						}
					});
				}
			}
		}

		/**
		 * Render visual templates inside viewport.
		 */
		render() {
			// Track previous active element for accessibility focus return
			this.previousFocusedElement = document.activeElement;

			// Show container
			this.wrapper.style.display = '';

			// Force DOM reflow to trigger smooth CSS animations
			this.wrapper.offsetHeight;

			this.wrapper.classList.add('tv-popup-active');
			this.wrapper.setAttribute('aria-hidden', 'false');

			// Register frequency suppression rule
			this.suppressFutureViews();

			// Bind close button actions
			const closeTargets = this.wrapper.querySelectorAll('.tv-popup-close-btn, .tv-popup-overlay');
			closeTargets.forEach(target => {
				target.addEventListener('click', (e) => {
					e.preventDefault();
					window.ElonixPopupQueue.closeActive();
				});
			});

			// Setup accessibility focus traps
			this.trapAccessibilityFocus();
		}

		/**
		 * Close visual templates and release focus.
		 *
		 * @param {Function} callback Callback triggered upon closing transition complete.
		 */
		dismiss(callback) {
			this.wrapper.classList.remove('tv-popup-active');
			this.wrapper.setAttribute('aria-hidden', 'true');

			// Restore focus to original trigger element (WCAG compliance)
			if (this.previousFocusedElement && typeof this.previousFocusedElement.focus === 'function') {
				this.previousFocusedElement.focus();
			}

			// Clean up focus trap keyboard listener
			if (this.keydownHandler) {
				this.wrapper.removeEventListener('keydown', this.keydownHandler);
			}

			// Wait for opacity transition duration
			setTimeout(() => {
				this.wrapper.style.display = 'none';
				if (callback) {
					callback();
				}
			}, 400);
		}

		/**
		 * Trap Focus within dialogue wrapper for WCAG compliance.
		 */
		trapAccessibilityFocus() {
			const container = this.wrapper.querySelector('.tv-popup-container');
			if (!container) {
				return;
			}

			const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
			const elements = Array.from(container.querySelectorAll(focusableElements));

			if (elements.length === 0) {
				return;
			}

			const firstEl = elements[0];
			const lastEl  = elements[elements.length - 1];

			// Set initial focus to close button
			const closeBtn = this.wrapper.querySelector('.tv-popup-close-btn');
			if (closeBtn) {
				closeBtn.focus();
			} else {
				firstEl.focus();
			}

			// Trapped tab sequence
			this.keydownHandler = (e) => {
				const isTab = (e.key === 'Tab' || e.keyCode === 9);

				if (!isTab) {
					return;
				}

				if (e.shiftKey) { // Shift + Tab
					if (document.activeElement === firstEl) {
						lastEl.focus();
						e.preventDefault();
					}
				} else { // Tab
					if (document.activeElement === lastEl) {
						firstEl.focus();
						e.preventDefault();
					}
				}
			};

			this.wrapper.addEventListener('keydown', this.keydownHandler);
		}

		/**
		 * Write a cookie helper.
		 */
		setCookie(name, value, days) {
			let expires = '';
			if (days) {
				const date = new Date();
				date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
				expires = '; expires=' + date.toUTCString();
			}
			document.cookie = name + '=' + (value || '') + expires + '; path=/; SameSite=Lax';
		}

		/**
		 * Read a cookie helper.
		 */
		getCookie(name) {
			const nameEQ = name + '=';
			const ca = document.cookie.split(';');
			for (let i = 0; i < ca.length; i++) {
				let c = ca[i];
				while (c.charAt(0) === ' ') {
					c = c.substring(1, c.length);
				}
				if (c.indexOf(nameEQ) === 0) {
					return c.substring(nameEQ.length, c.length);
				}
			}
			return null;
		}
	}

	// Expose globally
	window.Elonix_Popup_Manager = Elonix_Popup_Manager;
	window.Elonix_Exit_Intent   = Elonix_Exit_Intent;
	window.Elonix_Scroll_Trigger = Elonix_Scroll_Trigger;

	// Global escape button listener (WCAG compliance)
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' || e.keyCode === 27) {
			if (window.ElonixPopupQueue && window.ElonixPopupQueue.activePopup) {
				window.ElonixPopupQueue.closeActive();
			}
		}
	});

	// Initialize all matched popup wrappers in current page markup on DOMContentLoaded
	document.addEventListener('DOMContentLoaded', () => {
		const wrappers = document.querySelectorAll('.tv-popup-wrapper');
		wrappers.forEach(el => {
			new Elonix_Popup_Manager(el);
		});
	});

})();
