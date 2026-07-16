/**
 * Elonix Categories Widget Script
 *
 * Handles nested accordion toggle menus and Swiper carousel initialization
 * without external jQuery dependencies.
 */

/* global elementorFrontend, Swiper */

jQuery(window).on('elementor/frontend/init', () => {
	elementorFrontend.hooks.addAction('frontend/element_ready/tv-categories.default', ($element) => {
		const container = $element[0].querySelector('.tv-categories-wrapper');
		if ( ! container ) {
			return;
		}

		// 1. Initialize Swiper Carousel if layout is carousel
		if ( container.classList.contains('tv-categories-style-carousel') ) {
			const swiperContainer = container.querySelector('.tv-categories-swiper');
			const optionsAttr = container.getAttribute('data-swiper-options');
			
			if ( swiperContainer && optionsAttr ) {
				try {
					const opts = JSON.parse(optionsAttr);
					
					// Core Swiper Config mapping
					const swiperArgs = {
						slidesPerView: opts.slidesPerView.desktop || 3,
						spaceBetween: 20,
						loop: !!opts.loop,
						centeredSlides: !!opts.centered,
						grabCursor: true,
						breakpoints: {
							320: {
								slidesPerView: opts.slidesPerView.mobile || 1,
								spaceBetween: 10
							},
							768: {
								slidesPerView: opts.slidesPerView.tablet || 2,
								spaceBetween: 15
							},
							1024: {
								slidesPerView: opts.slidesPerView.desktop || 3,
								spaceBetween: 20
							}
						}
					};

					// Autoplay check
					if ( opts.autoplay ) {
						swiperArgs.autoplay = {
							delay: 3000,
							disableOnInteraction: false
						};
					}

					// Navigation arrows integration
					const prevArrow = swiperContainer.querySelector('.swiper-button-prev');
					const nextArrow = swiperContainer.querySelector('.swiper-button-next');
					if ( prevArrow && nextArrow ) {
						swiperArgs.navigation = {
							nextEl: nextArrow,
							prevEl: prevArrow
						};
					}

					// Pagination integration
					const paginationEl = swiperContainer.querySelector('.swiper-pagination');
					if ( paginationEl ) {
						const pagOpt = {
							el: paginationEl,
							clickable: true
						};

						// Map pagination types
						if ( paginationEl.classList.contains('tv-pag-fraction') ) {
							pagOpt.type = 'fraction';
						} else if ( paginationEl.classList.contains('tv-pag-progress') ) {
							pagOpt.type = 'progressbar';
						} else if ( paginationEl.classList.contains('tv-pag-numbers') ) {
							pagOpt.type = 'bullets';
							pagOpt.renderBullet = (index, className) => {
								return `<span class="${className} tv-swiper-pagination-number" style="display:inline-flex; width:24px; height:24px; align-items:center; justify-content:center; font-size:11px; margin:0 3px; cursor:pointer; border-radius:50%; background:rgba(0,0,0,0.06); color:#333; line-height:1;">${index + 1}</span>`;
							};
						} else {
							pagOpt.type = 'bullets';
						}

						swiperArgs.pagination = pagOpt;
					}

					// Instantiate Swiper
					if ( typeof Swiper !== 'undefined' ) {
						new Swiper(swiperContainer, swiperArgs);
					}
				} catch ( e ) {
					// Fallback handle silently
				}
			}
		}

		// 2. Accordion expand/collapse menu tree bindings
		const accordionButtons = container.querySelectorAll('.tv-cat-toggle-btn');
		accordionButtons.forEach((btn) => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				
				const targetId = btn.getAttribute('aria-controls');
				const targetList = container.querySelector('#' + targetId);
				if ( ! targetList ) {
					return;
				}

				const isExpanded = btn.getAttribute('aria-expanded') === 'true';

				// Toggle collapsed and expanded icon spans
				const collapsedIcon = btn.querySelector('.tv-cat-icon-collapsed');
				const expandedIcon = btn.querySelector('.tv-cat-icon-expanded');

				if ( isExpanded ) {
					// Collapse nested tree
					targetList.classList.add('tv-cat-children-hidden');
					targetList.setAttribute('aria-hidden', 'true');
					btn.setAttribute('aria-expanded', 'false');
					
					if ( collapsedIcon && expandedIcon ) {
						collapsedIcon.style.display = 'inline-block';
						expandedIcon.style.display = 'none';
					}

					// Update parent list item ARIA state if present
					const parentLi = btn.closest('[role="treeitem"]');
					if ( parentLi ) {
						parentLi.setAttribute('aria-expanded', 'false');
					}
				} else {
					// Expand nested tree
					targetList.classList.remove('tv-cat-children-hidden');
					targetList.setAttribute('aria-hidden', 'false');
					btn.setAttribute('aria-expanded', 'true');

					if ( collapsedIcon && expandedIcon ) {
						collapsedIcon.style.display = 'none';
						expandedIcon.style.display = 'inline-block';
					}

					// Update parent list item ARIA state if present
					const parentLi = btn.closest('[role="treeitem"]');
					if ( parentLi ) {
						parentLi.setAttribute('aria-expanded', 'true');
					}
				}
			});
		});

		// 3. Tree Keyboard Navigation Loop (role="tree" ARIA behaviors)
		const treeWrapper = container.querySelector('[role="tree"]');
		if ( treeWrapper ) {
			const treeItems = Array.from(treeWrapper.querySelectorAll('.tv-cat-child-link, .tv-cat-title-link'));
			
			treeItems.forEach((link, idx) => {
				link.addEventListener('keydown', (e) => {
					let targetIdx = null;

					if ( e.key === 'ArrowDown' ) {
						e.preventDefault();
						targetIdx = (idx + 1) % treeItems.length;
						if ( targetIdx !== null ) {
							treeItems[targetIdx].focus();
						}
					} else if ( e.key === 'ArrowUp' ) {
						e.preventDefault();
						targetIdx = (idx - 1 + treeItems.length) % treeItems.length;
						if ( targetIdx !== null ) {
							treeItems[targetIdx].focus();
						}
					} else if ( e.key === 'ArrowRight' ) {
						if ( link.classList.contains('tv-cat-title-link') ) {
							const card = link.closest('.tv-cat-card-classic, .tv-cat-card');
							if ( card ) {
								const toggleBtn = card.querySelector('.tv-cat-toggle-btn');
								const childList = card.querySelector('.tv-cat-children-list');
								if ( toggleBtn && childList ) {
									const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
									if ( ! isExpanded ) {
										e.preventDefault();
										toggleBtn.click();
									} else {
										const firstChild = childList.querySelector('.tv-cat-child-link');
										if ( firstChild ) {
											e.preventDefault();
											firstChild.focus();
										}
									}
								}
							}
						}
					} else if ( e.key === 'ArrowLeft' ) {
						if ( link.classList.contains('tv-cat-title-link') ) {
							const card = link.closest('.tv-cat-card-classic, .tv-cat-card');
							if ( card ) {
								const toggleBtn = card.querySelector('.tv-cat-toggle-btn');
								if ( toggleBtn ) {
									const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
									if ( isExpanded ) {
										e.preventDefault();
										toggleBtn.click();
									}
								}
							}
						} else if ( link.classList.contains('tv-cat-child-link') ) {
							const card = link.closest('.tv-cat-card-classic, .tv-cat-card');
							if ( card ) {
								const parentLink = card.querySelector('.tv-cat-title-link');
								if ( parentLink ) {
									e.preventDefault();
									parentLink.focus();
								}
							}
						}
					}
				});
			});
		}
	});
});
