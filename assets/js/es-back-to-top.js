/**
 * Elonix Back To Top Widget Javascript Engine (es-back-to-top)
 *
 * Implements passive scroll events, GPU-accelerated progress ring updates,
 * WCAG 2.1 AA keyboard flow, smart modal/popup visibility rules, and
 * reading duration estimation assistants.
 *
 * @package Elonix_Toolkit
 * @version 1.0.0
 */

'use strict';

class ElonixBackToTop {

	/**
	 * Constructor.
	 *
	 * @param {HTMLElement} container The widget container element.
	 */
	constructor(container) {
		this.container = container;
		this.button    = container.querySelector( '.es-back-to-top-button' );

		// Parse config
		try {
			this.config = JSON.parse( container.getAttribute( 'data-config' ) ) || {};
		} catch (e) {
			this.config = {};
		}

		// SVG progress elements
		this.svgFill     = container.querySelector( '.es-progress-ring-fill' );
		this.percentText = container.querySelector( '.es-progress-percent-text' );
		this.readingText = container.querySelector( '.es-reading-time-text' );
		this.barTrack    = container.querySelector( '.es-progress-track' );
		this.barFill     = container.querySelector( '.es-progress-fill' );

		// Reading variables
		this.contentElement     = null;
		this.wordCount          = 0;
		this.estimatedTimeTotal = 0;

		// Interactive status variables
		this.isVisible       = false;
		this.inactivityTimer = null;
		this.circumference   = 282.743; // 2 * Math.PI * 45 radius
		this.ticking         = false;

		// Initialize L10n fallback tags
		this.l10nMin = 'min';

		this.init();
	}

	/**
	 * Initialize widget elements and event bindings.
	 */
	init() {
		// Setup initial SVG circumference
		if (this.svgFill) {
			this.svgFill.style.strokeDasharray  = `${this.circumference} ${this.circumference}`;
			this.svgFill.style.strokeDashoffset = this.circumference;
		}

		// Load reading contents metadata
		if (this.config.readingAssistant && this.config.contentSelector) {
			this.contentElement = document.querySelector( this.config.contentSelector );
			if (this.contentElement) {
				const words             = this.contentElement.innerText.trim().split( /\s+/ );
				this.wordCount          = words.length > 0 && words[0] !== '' ? words.length : 0;
				this.estimatedTimeTotal = Math.ceil( this.wordCount / (this.config.readingSpeedWpm || 200) );
			}
		}

		// Bind events
		this.bindEvents();

		// Run initial status check
		this.handleScroll();
	}

	/**
	 * Bind all scroll, resize, click, and keyboard triggers.
	 */
	bindEvents() {
		// Passive scroll event listener for high frame rate execution
		window.addEventListener( 'scroll', () => this.requestTick(), { passive: true } );
		window.addEventListener( 'resize', () => this.requestTick(), { passive: true } );

		// Trigger Click action
		if (this.button) {
			this.button.addEventListener( 'click', (e) => this.handleAction( e ) );
		}
	}

	/**
	 * Use requestAnimationFrame to throttle rendering calculations.
	 */
	requestTick() {
		if ( ! this.ticking) {
			window.requestAnimationFrame(
				() => {
					this.handleScroll();
					this.ticking = false;
				}
			);
			this.ticking = true;
		}
	}

	/**
	 * Perform all scroll calculations: Visibility, Progress, Offsets, Assistants.
	 */
	handleScroll() {
		if ( ! document.body.contains( this.container )) {
			return;
		}

		const scrollTop      = window.scrollY || document.documentElement.scrollTop;
		const viewportHeight = window.innerHeight;
		const totalHeight    = document.documentElement.scrollHeight - viewportHeight;

		// Calculate progress percentage
		let progress = totalHeight > 0 ? scrollTop / totalHeight : 0;
		progress     = Math.min( Math.max( progress, 0 ), 1 );

		// Adjust progress if tracking a specific article content box
		if (this.config.readingAssistant && this.contentElement) {
			const rect                   = this.contentElement.getBoundingClientRect();
			const contentHeight          = rect.height;
			const contentTopFromViewport = rect.top;
			const contentTopFromDoc      = contentTopFromViewport + scrollTop;

			const startOffset         = contentTopFromDoc - viewportHeight;
			const currentOffset       = scrollTop - startOffset;
			const totalScrollableArea = contentHeight;

			if (scrollTop < startOffset) {
				progress = 0;
			} else if (scrollTop > contentTopFromDoc + contentHeight) {
				progress = 1;
			} else {
				progress = totalScrollableArea > 0 ? currentOffset / totalScrollableArea : 0;
				progress = Math.min( Math.max( progress, 0 ), 1 );
			}
		}

		// 1. Process Trigger Visibility rules
		this.evaluateVisibility( scrollTop, progress );

		// 2. Render progress metrics
		this.renderProgress( progress );

		// 3. Update Reading Assistant Text Indicators
		this.renderReadingAssistant( progress );

		// 4. Inactivity Fading timer loop
		if (this.isVisible && this.config.inactivityFade) {
			this.triggerInactivityTimer();
		}

		// 5. Overlap avoidance with page footer
		if (this.isVisible && this.config.footerAvoidance) {
			this.handleFooterAvoidance( viewportHeight );
		}
	}

	/**
	 * Evaluate trigger visibility criteria and modal checks.
	 *
	 * @param {number} scrollTop Current vertical scroll offset in px.
	 * @param {number} progress Current scroll/reading progress percentage ratio.
	 */
	evaluateVisibility(scrollTop, progress) {
		let shouldShow = false;

		// Check Trigger type configurations
		switch (this.config.triggerType) {
			case 'always':
				shouldShow = true;
				break;
			case 'scroll_offset':
				shouldShow = (scrollTop >= (this.config.triggerValuePx || 300));
				break;
			case 'scroll_percent':
				shouldShow = ((progress * 100) >= (this.config.triggerValuePct || 25));
				break;
			case 'after_selector':
				if (this.config.triggerSelector) {
					const el = document.querySelector( this.config.triggerSelector );
					if (el) {
						shouldShow = (el.getBoundingClientRect().top <= 0);
					}
				}
				break;
		}

		// Exclusions: Check presence of selectors
		if (shouldShow && this.config.hideOnSelectors) {
			const selectors = this.config.hideOnSelectors.split( ',' ).map( s => s.trim() );
			for (const selector of selectors) {
				if (selector && document.querySelector( selector )) {
					shouldShow = false;
					break;
				}
			}
		}

		// Exclusions: Check active elementor popups
		if (shouldShow && this.config.hideOnPopup) {
			if (document.querySelector( '.elementor-popup-modal, .dialog-widget' )) {
				shouldShow = false;
			}
		}

		// Apply visibility state changes
		if (shouldShow) {
			if ( ! this.isVisible) {
				this.container.classList.add( 'es-visible' );
				if (this.button) {
					this.button.setAttribute( 'aria-hidden', 'false' );
					this.button.setAttribute( 'tabindex', '0' );
				}
				this.isVisible = true;
			}
		} else {
			if (this.isVisible) {
				this.container.classList.remove( 'es-visible' );
				if (this.button) {
					this.button.setAttribute( 'aria-hidden', 'true' );
					this.button.setAttribute( 'tabindex', '-1' );
				}
				this.isVisible = false;
			}
		}
	}

	/**
	 * Render linear or circular progress indicators on the button.
	 *
	 * @param {number} progress Current scroll/reading progress percentage ratio.
	 */
	renderProgress(progress) {
		// Circular progress SVG Dash offset updating
		if (this.svgFill) {
			const offset                        = this.circumference - (progress * this.circumference);
			this.svgFill.style.strokeDashoffset = offset;
		}

		// Linear Viewport bar width updating
		if (this.barFill) {
			this.barFill.style.width = `${progress * 100} % `;
		}

		// Dynamic Percentage counter text updating
		if (this.percentText) {
			this.percentText.innerText = `${Math.round( progress * 100 )} % `;
		}

		// Dynamic 100% Reading complete icon swapping
		if (this.button && this.config.iconSwap) {
			if (progress >= 0.99) {
				this.button.classList.add( 'es-complete' );
			} else {
				this.button.classList.remove( 'es-complete' );
			}
		}
	}

	/**
	 * Render Estimated/Remaining Reading time text values.
	 *
	 * @param {number} progress Current scroll/reading progress percentage ratio.
	 */
	renderReadingAssistant(progress) {
		if ( ! this.readingText || ! this.contentElement) {
			return;
		}

		if (this.config.readingTimeMode === 'minutes_total') {
			this.readingText.innerText = `${this.estimatedTimeTotal} ${this.l10nMin}`;
		} else if (this.config.readingTimeMode === 'minutes_remaining') {
			const remaining            = Math.max( 1, Math.ceil( this.estimatedTimeTotal * (1 - progress) ) );
			this.readingText.innerText = progress >= 0.99 ? '' : `${remaining} ${this.l10nMin}`;
		}
	}

	/**
	 * Register Inactivity auto-fade timers.
	 */
	triggerInactivityTimer() {
		if (this.inactivityTimer) {
			clearTimeout( this.inactivityTimer );
		}

		this.container.classList.remove( 'es-inactive' );

		this.inactivityTimer = setTimeout(
			() => {
				if (this.isVisible) {
					this.container.classList.add( 'es-inactive' );
				}
			},
			this.config.inactivityTimeout || 3000
		);
	}

	/**
	 * Avoid overlapping or clashing with page footer.
	 *
	 * @param {number} viewportHeight Active browser window height in px.
	 */
	handleFooterAvoidance(viewportHeight) {
		if ( ! this.config.footerSelector) {
			return;
		}

		const footer = document.querySelector( this.config.footerSelector );
		if ( ! footer) {
			return;
		}

		const footerRect = footer.getBoundingClientRect();
		const footerTop  = footerRect.top;

		// Check if footer has scrolled into viewport view
		if (footerTop < viewportHeight) {
			const overlapHeight = viewportHeight - footerTop;
			// Subtract default alignment margin offset from translation distance
			const offsetDist               = Math.max( 0, overlapHeight - 15 );
			this.container.style.transform = `translateY( -${offsetDist}px )`;
			// Compensate center alignment transition transforms
			if (this.container.classList.contains( 'es-align-center' )) {
				this.container.style.transform = `translate( -50 % , -${offsetDist}px )`;
			}
		} else {
			this.container.style.transform = '';
		}
	}

	/**
	 * Handle button click triggers, scroll calculations, callback execution.
	 *
	 * @param {Event} e Click event trigger.
	 */
	handleAction(e) {
		e.preventDefault();

		switch (this.config.action) {
			case 'scroll_to_top':
				this.smoothScrollTo( 0 );
				break;

			case 'scroll_target':
				if (this.config.actionSelector) {
					const target = document.querySelector( this.config.actionSelector );
					if (target) {
						const top = target.getBoundingClientRect().top + window.scrollY;
						this.smoothScrollTo( top );
					}
				}
				break;

			case 'custom_link':
				if (this.config.actionUrl) {
					window.location.href = this.config.actionUrl;
				}
				break;

			case 'js_callback':
				if (this.config.actionJs && typeof window[this.config.actionJs] === 'function') {
					window[this.config.actionJs]();
				}
				break;
		}
	}

	/**
	 * Perform smooth GPU scroll transition and reset focus correctly for WCAG criteria.
	 *
	 * @param {number} targetY Scroll destination vertical offset in px.
	 */
	smoothScrollTo(targetY) {
		window.scrollTo(
			{
				top: targetY,
				behavior: 'smooth'
			}
		);

		// WCAG Focus Reset: Move focus to upper containers after scrolling
		if (targetY === 0) {
			const focusSequence = ['#content', '#main', 'main', '[role="main"]', 'body'];

			// Check scroll completion and shift focus programmatically
			const checkScrollCompletion = () => {
				if (Math.abs( window.scrollY - targetY ) < 5) {
					for (const selector of focusSequence) {
						const target = document.querySelector( selector );
						if (target) {
							target.setAttribute( 'tabindex', '-1' );
							target.focus( { preventScroll: true } );
							break;
						}
					}
				} else {
					setTimeout( checkScrollCompletion, 100 );
				}
			};

			setTimeout( checkScrollCompletion, 100 );
		}
	}
}

// Initialize all instances on the page
const esInitBackToTop = () => {
	const items       = document.querySelectorAll( '.es-back-to-top-container, .es-progress-bar-track' );
	items.forEach(
		item => {
			if ( ! item.hasAttribute( 'data-initialized' )) {
				new ElonixBackToTop( item );
				item.setAttribute( 'data-initialized', 'true' );
			}
		}
	);
};

if (document.readyState === 'loading') {
	document.addEventListener( 'DOMContentLoaded', esInitBackToTop );
} else {
	esInitBackToTop();
}

// Elementor Editor preview dynamic handler registration
const esRegisterElementorBackToTop = () => {
	if (window.elementorFrontend && elementorFrontend.hooks) {
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/es-back-to-top.default',
			($scope) => {
            if ( ! $scope || ! $scope.length) {
                return;
            }
				const target = $scope[0].querySelector( '.es-back-to-top-container, .es-progress-bar-track' );
				if (target && ! target.hasAttribute( 'data-initialized' )) {
					new ElonixBackToTop( target );
					target.setAttribute( 'data-initialized', 'true' );
				}
			}
		);
	}
};

if (window.elementorFrontend) {
	esRegisterElementorBackToTop();
} else {
	window.addEventListener( 'elementor/frontend/init', esRegisterElementorBackToTop );
}
