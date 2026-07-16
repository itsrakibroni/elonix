/**
 * Elonix Nav Menu Widget Script
 *
 * Premium vanilla JavaScript header navigation menu handler with
 * responsive offcanvas side drawers, zero jQuery dependencies,
 * WAI-ARIA compliance, and complete keyboard accessibility support.
 *
 * @package Elonix_Toolkit
 */

jQuery( window ).on(
	'elementor/frontend/init',
	() => {
		elementorFrontend.hooks.addAction(
		'frontend/element_ready/tv-nav-menu.default',
		($element) => {
			const wrapper = $element[0].querySelector( '.tv-nav-menu-wrapper' );
        if ( ! wrapper || wrapper.classList.contains( 'tv-nav-menu-initialized' )) {
            return;
        }
			wrapper.classList.add( 'tv-nav-menu-initialized' );
			const triggerType = wrapper.getAttribute( 'data-trigger' ) || 'hover';
			const breakpoint  = parseInt( wrapper.getAttribute( 'data-breakpoint' ) || 1024, 10 );
			// DOM selectors
				const toggler      = wrapper.querySelector( '.tv-nav-menu-hamburger' );
			const closeBtn         = wrapper.querySelector( '.tv-nav-menu-mobile-close' );
			const overlay          = wrapper.querySelector( '.tv-nav-menu-mobile-overlay' );
			const mobileDrawer     = wrapper.querySelector( '.tv-nav-menu-mobile-drawer' );
			const mobileDropdown   = wrapper.querySelector( '.tv-nav-menu-mobile-dropdown' );
			const desktopContainer = wrapper.querySelector( '.tv-nav-menu-desktop-container' );
			const desktopMenu      = desktopContainer ? desktopContainer.querySelector( '.tv-navbar-nav' ) : null;
			// Active drawer check
				const getActiveDrawer = () => mobileDrawer || mobileDropdown;
			// Check if current viewport is responsive
				const isMobileViewport = () => {
					if ( ! breakpoint || isNaN( breakpoint )) {
						return false;
					}
					return window.innerWidth <= breakpoint;
        };
			/* =========================================================================
				1. MOBILE DRAWER SYSTEM
				========================================================================= */
				const openMobileMenu = () => {
					wrapper.classList.add( 'tv-mobile-menu-active' );
					if (toggler) {
						toggler.setAttribute( 'aria-expanded', 'true' );
					}
					trapFocus( getActiveDrawer() );
					document.body.style.overflow = 'hidden'; // Lock background scrolling
        };
			const closeMobileMenu        = () => {
				wrapper.classList.remove( 'tv-mobile-menu-active' );
				if (toggler) {
					toggler.setAttribute( 'aria-expanded', 'false' );
					toggler.focus(); // Return focus to toggler
				}
				document.body.style.removeProperty( 'overflow' );
				// Reset all submenus inside the mobile menu to closed state
					const activeContainer = getActiveDrawer();
				if (activeContainer) {
					const openSubmenus = activeContainer.querySelectorAll( '.tv-dropdown-open' );
					openSubmenus.forEach(
					(li) => {
						li.classList.remove( 'tv-dropdown-open' );
						const sub      = li.querySelector( ':scope > .tv-dropdown' );
						if (sub) {
							sub.style.display = 'none';
							sub.style.removeProperty( 'height' );
							sub.setAttribute( 'aria-hidden', 'true' );
						}
						const subLink   = li.querySelector( ':scope > a' );
						const subToggle = li.querySelector( '.tv-submenu-indicator-mobile-toggle' );
						if (subLink) {
							subLink.setAttribute( 'aria-expanded', 'false' );
						}
						if (subToggle) {
							subToggle.setAttribute( 'aria-expanded', 'false' );
						}
					}
						);
				}
        };
			if (toggler) {
				toggler.addEventListener(
					'click',
					(e) => {
						e.preventDefault();
						if (wrapper.classList.contains( 'tv-mobile-menu-active' )) {
							closeMobileMenu();
						} else {
						openMobileMenu();
                    }
						}
				);
			}
        if (closeBtn) {
            closeBtn.addEventListener(
            'click',
            (e) => {
                e.preventDefault();
                closeMobileMenu();
                }
            );
        }
        if (overlay) {
            overlay.addEventListener(
            'click',
            (e) => {
                e.preventDefault();
                closeMobileMenu();
                }
            );
        }
			// Close menu on Escape key press
				window.addEventListener(
					'keydown',
					(e) => {
						if (e.key === 'Escape' && wrapper.classList.contains( 'tv-mobile-menu-active' )) {
							closeMobileMenu();
						}
					}
				);
		// Trap focus inside offcanvas drawer for accessibility
			const trapFocus = (container) => {
				if ( ! container) {
					return;
				}
				// Dynamically fetch and filter currently visible and navigable elements
				const getFocusableElements = () => {
					const selector                 = 'a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"]), .elementor-button, .btn, [role="button"]';
					const elements                 = Array.from( container.querySelectorAll( selector ) );
					return elements.filter(
                (el) => {
						if (el.disabled || el.getAttribute( 'tabindex' ) === '-1') {
							return false;
						}
						const rect  = el.getBoundingClientRect();
						const style = window.getComputedStyle( el );
						// Ensure the element and its parents are visible (not hidden via display or visibility)
						return el.offsetWidth > 0 && el.offsetHeight > 0 && rect.width > 0 && rect.height > 0 && style.display !== 'none' && style.visibility !== 'hidden';
                }
            );
				};
				// Set focus on opening
				setTimeout(
					() => {
						const focusable = getFocusableElements();
						if (focusable.length > 0) {
							focusable[0].focus();
						}
					},
					150
				);
        // Clean up previous event listeners on container
			if (container._trapFocusHandler) {
				container.removeEventListener( 'keydown', container._trapFocusHandler );
			}

			// Keyboard handler
			container._trapFocusHandler = function (e) {
				if (e.key !== 'Tab') {
					return;
				}

				const focusable = getFocusableElements();
				if (focusable.length === 0) {
					e.preventDefault();
					return;
				}

				const firstElement = focusable[0];
				const lastElement  = focusable[focusable.length - 1];

				if (e.shiftKey) { // Shift + Tab
					if (document.activeElement === firstElement || ! focusable.includes( document.activeElement )) {
						lastElement.focus();
						e.preventDefault();
					}
				} else { // Tab
					if (document.activeElement === lastElement || ! focusable.includes( document.activeElement )) {
						firstElement.focus();
						e.preventDefault();
					}
				}
			};
				container.addEventListener( 'keydown', container._trapFocusHandler );
				// Setup observer for dynamic child lists or class/style changes
				if (window.MutationObserver) {
					if (container._trapObserver) {
						container._trapObserver.disconnect();
					}
					container._trapObserver = new MutationObserver(
						() => {
							// The observer guarantees DOM changes refresh focusable lists on the next navigation event
						}
					);
					container._trapObserver.observe(
						container,
						{
							childList: true,
							subtree: true,
							attributes: true,
							attributeFilter: ['style', 'class', 'hidden']
						}
					);
				}
			};
			/* =========================================================================
				2. MOBILE COLLAPSIBLE SUBMENUS
				========================================================================= */
				const initMobileSubmenus = () => {
					const activeContainer        = getActiveDrawer();
        if ( ! activeContainer) {
            return;
        }
					const accordionDuration = parseInt( wrapper.getAttribute( 'data-accordion-duration' ) || 300, 10 );
					const accordionEasing   = wrapper.getAttribute( 'data-accordion-easing' ) || 'ease-in-out';
					const dropdownItems     = activeContainer.querySelectorAll( '.tv-dropdown-has' );
					dropdownItems.forEach(
                (li) => {
						const link      = li.querySelector( ':scope > a' );
						const submenu   = li.querySelector( ':scope > .tv-dropdown' );
						if ( ! link || ! submenu) {
							return;
						}
						// Generate unique ID for submenu if not exists
						if ( ! submenu.id) {
							submenu.id = 'tv-submenu-' + Math.random().toString( 36 ).substr( 2, 9 );
						}
							submenu.setAttribute( 'aria-hidden', 'true' );
						// Ensure caret exists and wrap inside mobile-toggle if it exists
							let indicator = link.querySelector( '.tv-submenu-indicator' );
						let toggleBtn         = li.querySelector( '.tv-submenu-indicator-mobile-toggle' );
						if ( ! toggleBtn) {
							toggleBtn           = document.createElement( 'button' );
							toggleBtn.className = 'tv-submenu-indicator-mobile-toggle';
							toggleBtn.setAttribute( 'type', 'button' );
							toggleBtn.setAttribute( 'aria-label', 'Toggle Submenu' );
							toggleBtn.setAttribute( 'aria-controls', submenu.id );
							toggleBtn.setAttribute( 'aria-expanded', 'false' );
							if (indicator) {
								toggleBtn.appendChild( indicator );
							} else {
								toggleBtn.innerHTML = '<i class="fas fa-chevron-down" aria-hidden="true"></i>';
							}
							link.appendChild( toggleBtn );
						} else {
                toggleBtn.setAttribute( 'aria-controls', submenu.id );
                toggleBtn.setAttribute( 'aria-expanded', 'false' );
						}

							link.setAttribute( 'aria-expanded', 'false' );
						const isClickOnIcon = activeContainer.classList.contains( 'tv-submenu-click-on-icon' );
						// Setup accordion toggle method
							const toggleSubmenu = (itemLi, itemSubmenu, event) => {
                event.preventDefault();
                event.stopPropagation();
                const isOpen    = itemLi.classList.contains( 'tv-dropdown-open' );
                // Close other sibling dropdowns on same depth
								const siblings = itemLi.parentNode.querySelectorAll( ':scope > .tv-dropdown-has' );
                siblings.forEach(
								(sib) => {
									if (sib !== itemLi && sib.classList.contains( 'tv-dropdown-open' )) {
										sib.classList.remove( 'tv-dropdown-open' );
										const sibSub = sib.querySelector( ':scope > .tv-dropdown' );
										if (sibSub) {
												slideUp( sibSub, accordionDuration, accordionEasing );
												sibSub.setAttribute( 'aria-hidden', 'true' );
										}
										const sibLink   = sib.querySelector( ':scope > a' );
										const sibToggle = sib.querySelector( '.tv-submenu-indicator-mobile-toggle' );
										if (sibLink) {
												sibLink.setAttribute( 'aria-expanded', 'false' );
										}
										if (sibToggle) {
											sibToggle.setAttribute( 'aria-expanded', 'false' );
										}
												}
								}
							);
							if (isOpen) {
								itemLi.classList.remove( 'tv-dropdown-open' );
								slideUp( itemSubmenu, accordionDuration, accordionEasing );
								itemSubmenu.setAttribute( 'aria-hidden', 'true' );
								link.setAttribute( 'aria-expanded', 'false' );
								toggleBtn.setAttribute( 'aria-expanded', 'false' );
							} else {
                itemLi.classList.add( 'tv-dropdown-open' );
                slideDown( itemSubmenu, accordionDuration, accordionEasing );
                itemSubmenu.setAttribute( 'aria-hidden', 'false' );
                link.setAttribute( 'aria-expanded', 'true' );
                toggleBtn.setAttribute( 'aria-expanded', 'true' );
							}
						};
						if (isClickOnIcon) {
							// Icon Click Mode
							toggleBtn.addEventListener(
								'click',
								(e) => {
									toggleSubmenu( li, submenu, e );
								}
							);
						} else {
                // Entire Link Item Click Mode
							link.addEventListener(
                                'click',
                                (e) => {
								const href   = link.getAttribute( 'href' );
								const isHash = ! href || href === '#' || href === '' || href.startsWith( '#' );
								// Standard logic: if it's a hash link, always toggle
								if (isHash) {
									toggleSubmenu( li, submenu, e );
								} else {
									// If actual URL, open on first tap, follow link on second tap
									if ( ! li.classList.contains( 'tv-dropdown-open' )) {
										toggleSubmenu( li, submenu, e );
									}
								}
								}
							);
						}
						}
            );
        };
			// Run mobile submenu setup
				initMobileSubmenus();
			/* =========================================================================
				3. DESKTOP CLICK-MODE DRAG TRIGGERS
				========================================================================= */
			if (desktopMenu && triggerType === 'click') {
				const dropdownItems = desktopMenu.querySelectorAll( '.tv-dropdown-has' );

				dropdownItems.forEach(
					(li) => {
                    const link    = li.querySelector( ':scope > a' );
                    const submenu = li.querySelector( ':scope > .tv-dropdown' );
                    if ( ! link || ! submenu) {
                        return;
                    }
                    link.addEventListener(
                            'click',
                            (e) => {
							if (isMobileViewport()) {
								return; // Skip on mobile screens
							}
							const href   = link.getAttribute( 'href' );
							const isHash = ! href || href === '#' || href === '';
							const isOpen = li.classList.contains( 'tv-dropdown-open' );
							if (isHash || ! isOpen) {
								e.preventDefault();
								e.stopPropagation();

								// Close open siblings at this level
								const siblings = li.parentNode.querySelectorAll( ':scope > .tv-dropdown-has' );
								siblings.forEach(
									(sib) => {
                                    if (sib !== li) {
                                        sib.classList.remove( 'tv-dropdown-open' );
                                        const sibLink = sib.querySelector( ':scope > a' );
                                        if (sibLink) {
                                            sibLink.setAttribute( 'aria-expanded', 'false' );
                                        }
                                    }
                                    }
								);

								if (isOpen) {
									li.classList.remove( 'tv-dropdown-open' );
									link.setAttribute( 'aria-expanded', 'false' );
								} else {
									li.classList.add( 'tv-dropdown-open' );
									link.setAttribute( 'aria-expanded', 'true' );
								}
							}
								}
                        );
					}
				);

				// Global click outside listener to close dropdowns
				document.addEventListener(
					'click',
					(e) => {
                    if (isMobileViewport()) {
                        return;
                    }
                    if ( ! desktopMenu.contains( e.target )) {
                        dropdownItems.forEach(
                        (li) => {
                            li.classList.remove( 'tv-dropdown-open' );
                            const link = li.querySelector( ':scope > a' );
                            if (link) {
                                link.setAttribute( 'aria-expanded', 'false' );
                            }
                            }
                        );
                    }
                    }
				);
			}

				/* =========================================================================
				4. DESKTOP KEYBOARD NAVIGATION ACCESSIBILITY (WAI-ARIA)
				========================================================================= */
			if (desktopMenu) {
				const rootLinks = desktopMenu.querySelectorAll( ':scope > li > a' );

				rootLinks.forEach(
					(rootLink, index) => {
                    // Manage keydown triggers
						rootLink.addEventListener(
                            'keydown',
                            (e) => {
							if (isMobileViewport()) {
								return;
							}
							const li      = rootLink.parentElement;
							const submenu = li.querySelector( ':scope > .tv-dropdown' );
							if (e.key === 'ArrowRight') {
								e.preventDefault();
								const nextIndex = (index + 1) % rootLinks.length;
								rootLinks[nextIndex].focus();
							} else if (e.key === 'ArrowLeft') {
								e.preventDefault();
								const prevIndex = (index - 1 + rootLinks.length) % rootLinks.length;
								rootLinks[prevIndex].focus();
							} else if (e.key === 'ArrowDown' && submenu) {
									e.preventDefault();

									// Open submenu and focus first item
									li.classList.add( 'tv-dropdown-open' );
									rootLink.setAttribute( 'aria-expanded', 'true' );

									const subLinks = submenu.querySelectorAll( 'li > a' );
								if (subLinks.length > 0) {
									subLinks[0].focus();
								}
							}
							}
                        );
					// Submenu level listeners
					const submenu = rootLink.parentElement.querySelector( ':scope > .tv-dropdown' );
					if (submenu) {
						const subLinks = submenu.querySelectorAll( 'li > a' );
						subLinks.forEach(
                            (subLink, subIndex) => {
							subLink.addEventListener(
								'keydown',
								(e) => {
                                if (isMobileViewport()) {
                                    return;
                                }
                                if (e.key === 'ArrowDown') {
                                    e.preventDefault();
                                    const nextSub = (subIndex + 1) % subLinks.length;
                                    subLinks[nextSub].focus();
                                } else if (e.key === 'ArrowUp') {
                                e.preventDefault();
                                const prevSub = (subIndex - 1 + subLinks.length) % subLinks.length;
                                subLinks[prevSub].focus();
                                } else if (e.key === 'Escape') {
											e.preventDefault();

											// Close dropdown and focus parent root item
											rootLink.parentElement.classList.remove( 'tv-dropdown-open' );
											rootLink.setAttribute( 'aria-expanded', 'false' );
											rootLink.focus();
									}
									}
							);
                            }
						);
					}
					}
				);
			}

				/* =========================================================================
				5. SLIDE ACCORDION ANIMATION HELPERS (CSS-FREE, HEIGHT-ONLY ANIMATION)
				========================================================================= */
			function slideUp(element, duration = 300, easing = 'ease-in-out') {
				element.style.height                   = element.offsetHeight + 'px';
				element.style.transitionProperty       = 'height';
				element.style.transitionDuration       = duration + 'ms';
				element.style.transitionTimingFunction = easing;
				element.style.boxSizing                = 'border-box';
				element.style.overflow                 = 'hidden';

				// Trigger Reflow
				element.offsetHeight;

				element.style.height = '0';

				window.setTimeout(
					() => {
                    element.style.display = 'none';
                    element.style.removeProperty( 'height' );
                    element.style.removeProperty( 'overflow' );
                    element.style.removeProperty( 'transition-duration' );
                    element.style.removeProperty( 'transition-property' );
                    element.style.removeProperty( 'transition-timing-function' );
                    },
					duration
				);
			}

			function slideDown(element, duration = 300, easing = 'ease-in-out') {
				element.style.removeProperty( 'display' );
				let display = window.getComputedStyle( element ).display;
				if (display === 'none') {
					display = 'block';
				}
				element.style.display = display;

				let height             = element.offsetHeight;
				element.style.overflow = 'hidden';
				element.style.height   = '0';

				// Trigger Reflow
				element.offsetHeight;

				element.style.boxSizing                = 'border-box';
				element.style.transitionProperty       = 'height';
				element.style.transitionDuration       = duration + 'ms';
				element.style.transitionTimingFunction = easing;
				element.style.height                   = height + 'px';

				window.setTimeout(
					() => {
                    element.style.removeProperty( 'height' );
                    element.style.removeProperty( 'overflow' );
                    element.style.removeProperty( 'transition-duration' );
                    element.style.removeProperty( 'transition-property' );
                    element.style.removeProperty( 'transition-timing-function' );
                    },
					duration
				);
			}

				/* =========================================================================
				5.5 STICKY HEADER EFFECTS INITIALIZATION
				========================================================================= */
				const stickyAnimation = wrapper.getAttribute( 'data-sticky-animation' ) || 'none';
        if (stickyAnimation !== 'none') {
            // Clean up any existing sticky effect classes on body to avoid clashes (e.g. in Elementor editor)
            document.body.classList.forEach(
            className => {
                if (className.startsWith( 'tv-sticky-effect-' )) {
                    document.body.classList.remove( className );
                }
                }
            );

            // Add current sticky effect class to body
            document.body.classList.add( `tv-sticky-effect-${stickyAnimation}` );

            // Try to find the closest elementor sticky section/container
            const stickyParent = wrapper.closest( '.elementor-sticky' ) || wrapper.closest( '.elementor-section' ) || wrapper.closest( '.elementor-container' );
            const applyTarget  = stickyParent || document.body;

            const duration = wrapper.getAttribute( 'data-sticky-duration' );
            const delay    = wrapper.getAttribute( 'data-sticky-delay' );
            const easing   = wrapper.getAttribute( 'data-sticky-easing' );

            if (duration) {
                applyTarget.style.setProperty( '--tv-sticky-duration', `${duration}ms` );
            }
            if (delay) {
                applyTarget.style.setProperty( '--tv-sticky-delay', `${delay}ms` );
            }
            if (easing) {
                applyTarget.style.setProperty( '--tv-sticky-easing', easing );
            }
        }
			/* =========================================================================
				6. STICKY HEADER SCROLL DIRECTION TRACKING
				========================================================================= */
				let lastScrollTop   = 0;
			window.addEventListener(
                'scroll',
                () => {
						const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
						if (scrollTop > lastScrollTop && scrollTop > 50) {
							// Scrolling Down
							document.body.classList.remove( 'tv-scroll-up' );
							document.body.classList.add( 'tv-scroll-down' );
							wrapper.classList.remove( 'tv-scroll-up' );
							wrapper.classList.add( 'tv-scroll-down' );
						} else {
                // Scrolling Up
							document.body.classList.remove( 'tv-scroll-down' );
                document.body.classList.add( 'tv-scroll-up' );
                wrapper.classList.remove( 'tv-scroll-down' );
                wrapper.classList.add( 'tv-scroll-up' );
						}
						lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // For Mobile or negative scrolling
                },
                { passive: true }
            );
		}
		);
	}
);
