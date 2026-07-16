/**
 * Elonix Accordion Widget Script
 *
 * Lightweight vanilla JavaScript accordion handler with WAI-ARIA compliance.
 */

jQuery(window).on('elementor/frontend/init', () => {
	elementorFrontend.hooks.addAction('frontend/element_ready/tv-accordion.default', ($element) => {
		const accordion = $element.find('.tv-accordion-container')[0];
		if (!accordion) return;

		const allowMultiple    = accordion.getAttribute('data-allow-multiple') === 'yes';
		const collapseOthers   = accordion.getAttribute('data-collapse-others') === 'yes';
		const duration         = parseInt(accordion.getAttribute('data-animation-duration') || 300, 10);
		
		const items    = accordion.querySelectorAll('.tv-accordion-item');
		const triggers = accordion.querySelectorAll('.tv-accordion-header-trigger');

		triggers.forEach((trigger, index) => {
			const item  = items[index];
			const panel = item.querySelector('.tv-accordion-panel');

			// Dynamic initial active states check
			if (trigger.getAttribute('aria-expanded') === 'true') {
				panel.style.display = 'block';
				panel.setAttribute('aria-hidden', 'false');
			} else {
				panel.style.display = 'none';
				panel.setAttribute('aria-hidden', 'true');
			}

			// Click trigger toggle
			trigger.addEventListener('click', (e) => {
				e.preventDefault();
				toggleItem(index);
			});

			// Header focus visual markers
			trigger.addEventListener('focus', () => {
				trigger.closest('.tv-accordion-header').classList.add('tv-focus');
			});
			trigger.addEventListener('blur', () => {
				trigger.closest('.tv-accordion-header').classList.remove('tv-focus');
			});

			// Keyboard WAI-ARIA navigation
			trigger.addEventListener('keydown', (e) => {
				let targetIndex = null;

				if (e.key === 'ArrowDown') {
					e.preventDefault();
					targetIndex = (index + 1) % triggers.length;
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					targetIndex = (index - 1 + triggers.length) % triggers.length;
				} else if (e.key === 'Home') {
					e.preventDefault();
					targetIndex = 0;
				} else if (e.key === 'End') {
					e.preventDefault();
					targetIndex = triggers.length - 1;
				} else if (e.key === ' ' || e.key === 'Spacebar') {
					e.preventDefault();
					toggleItem(index);
				}

				if (targetIndex !== null) {
					triggers[targetIndex].focus();
				}
			});
		});

		function toggleItem(index) {
			const currentItem = items[index];
			const currentTrigger = triggers[index];
			const currentPanel = currentItem.querySelector('.tv-accordion-panel');
			const isExpanded = currentTrigger.getAttribute('aria-expanded') === 'true';

			if (isExpanded) {
				slideUp(currentPanel, duration, () => {
					currentItem.classList.remove('tv-active');
					currentTrigger.setAttribute('aria-expanded', 'false');
					currentPanel.setAttribute('aria-hidden', 'true');
				});
			} else {
				if (!allowMultiple) {
					items.forEach((item, idx) => {
						if (idx !== index && item.classList.contains('tv-active')) {
							const otherTrigger = triggers[idx];
							const otherPanel   = item.querySelector('.tv-accordion-panel');
							slideUp(otherPanel, duration, () => {
								item.classList.remove('tv-active');
								otherTrigger.setAttribute('aria-expanded', 'false');
								otherPanel.setAttribute('aria-hidden', 'true');
							});
						}
					});
				}

				currentItem.classList.add('tv-active');
				currentTrigger.setAttribute('aria-expanded', 'true');
				currentPanel.setAttribute('aria-hidden', 'false');
				slideDown(currentPanel, duration);
			}
		}

		// CSS-free high-performance transition sliding calculations
		function slideUp(element, duration, callback) {
			element.style.height = element.offsetHeight + 'px';
			element.style.transitionProperty = 'height, margin, padding';
			element.style.transitionDuration = duration + 'ms';
			element.style.boxSizing = 'border-box';
			element.style.overflow = 'hidden';

			// Force DOM Reflow
			element.offsetHeight;

			element.style.height = '0';
			element.style.paddingTop = '0';
			element.style.paddingBottom = '0';
			element.style.marginTop = '0';
			element.style.marginBottom = '0';

			window.setTimeout(() => {
				element.style.display = 'none';
				element.style.removeProperty('height');
				element.style.removeProperty('padding-top');
				element.style.removeProperty('padding-bottom');
				element.style.removeProperty('margin-top');
				element.style.removeProperty('margin-bottom');
				element.style.removeProperty('overflow');
				element.style.removeProperty('transition-duration');
				element.style.removeProperty('transition-property');
				if (callback) callback();
			}, duration);
		}

		function slideDown(element, duration, callback) {
			element.style.removeProperty('display');
			let display = window.getComputedStyle(element).display;
			if (display === 'none') {
				display = 'block';
			}
			element.style.display = display;

			let height = element.offsetHeight;
			element.style.overflow = 'hidden';
			element.style.height = '0';
			element.style.paddingTop = '0';
			element.style.paddingBottom = '0';
			element.style.marginTop = '0';
			element.style.marginBottom = '0';

			// Force DOM Reflow
			element.offsetHeight;

			element.style.boxSizing = 'border-box';
			element.style.transitionProperty = 'height, margin, padding';
			element.style.transitionDuration = duration + 'ms';
			element.style.height = height + 'px';
			element.style.removeProperty('padding-top');
			element.style.removeProperty('padding-bottom');
			element.style.removeProperty('margin-top');
			element.style.removeProperty('margin-bottom');

			window.setTimeout(() => {
				element.style.removeProperty('height');
				element.style.removeProperty('overflow');
				element.style.removeProperty('transition-duration');
				element.style.removeProperty('transition-property');
				if (callback) callback();
			}, duration);
		}
	});
});
