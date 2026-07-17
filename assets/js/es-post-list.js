/**
 * Elonix – Toolkit for Elementor Premium Post List Script
 *
 * Handles AJAX load more and infinite scroll pagination using AbortController,
 * IntersectionObserver, and dynamic skeleton loader animations.
 *
 * @package Elonix_Toolkit
 */

/* global elementorFrontend */

jQuery( window ).on(
	'elementor/frontend/init',
	() => {
	elementorFrontend.hooks.addAction(
		'frontend/element_ready/es-post-list.default',
		($element) => {
			const wrap = $element[0].querySelector( '.es-post-list-wrap' );
			if ( ! wrap ) {
				return;
			}
			const container = wrap.querySelector( '.es-post-list-container' );
			if ( ! container ) {
				return;
			}
			// 1. Settings and State Parsing
			const rawSettings = wrap.getAttribute( 'data-settings' );
			let settings          = {};
			try {
				settings = JSON.parse( rawSettings );
			} catch ( e ) {
        return;
			}

			let currentPage  = parseInt( settings.paged ) || 1;
			let maxPages         = parseInt( wrap.getAttribute( 'data-max-pages' ) ) || 1;
			let isLoading        = false;
			let abortController  = null;
			const paginationType = settings.pagination_type || 'none';
			// Hide pagination container if we already hit the maximum pages initially
			if ( currentPage >= maxPages ) {
				const pagWrap = wrap.querySelector( '.es-post-list-pagination' );
				if ( pagWrap ) {
					pagWrap.style.display = 'none';
				}
			}

			// 2. Query/Load Function
			const loadMorePosts = () => {
				if ( isLoading || currentPage >= maxPages ) {
					return;
				}
				isLoading = true;
				// Abort previous overlapping request if active
				if ( abortController ) {
					abortController.abort();
				}
				abortController = new AbortController();
				currentPage++;
				// Get buttons & loading DOM elements
				const btn = wrap.querySelector( '.es-load-more-btn' );
				const spinner     = btn ? btn.querySelector( '.es-btn-loading-icon' ) : null;
				const trigger     = wrap.querySelector( '.es-infinite-scroll-trigger' );
				// Trigger loading styles (Spinner, disable button)
				if ( btn ) {
					btn.setAttribute( 'disabled', 'true' );
					if ( spinner ) {
						spinner.style.display = 'inline-block';
					}
				}

				// Generate and append inline skeleton cards matching layout structure
				const skeletons    = [];
				const renderSkeletonsCount = 2;
				for ( let i = 0; i < renderSkeletonsCount; i++ ) {
					const sk     = document.createElement( 'div' );
				sk.className = `es-post-card es-skeleton-card es-layout-${settings.layout}`;
				sk.innerHTML = `
					<div class="es-post-thumbnail es-skeleton-thumb"></div>
					<div class="es-post-content">
						<div class="es-skeleton-line es-skeleton-title"></div>
						<div class="es-skeleton-line es-skeleton-meta"></div>
						<div class="es-skeleton-line es-skeleton-excerpt"></div>
					</div>
				`;
					container.appendChild( sk );
					skeletons.push( sk );
				}
				// Construct Form Data parameters recursively
				const formData = new FormData();
				formData.append( 'action', 'es_post_list_fetch_posts' );
				formData.append( 'security', wrap.getAttribute( 'data-nonce' ) );
				formData.append( 'paged', currentPage );
				const appendSettings = ( obj, prefix = '' ) => {
					for ( const key in obj ) {
						if ( Object.prototype.hasOwnProperty.call( obj, key ) ) {
							const val     = obj[key];
							const formKey = prefix ? `${prefix}[${key}]` : `settings[${key}]`;
							if ( val !== null && typeof val === 'object' ) {
								appendSettings( val, formKey );
							} else {
								formData.append( formKey, val !== null ? val : '' );
							}
						}
							}
				};
				appendSettings( settings );
				// Execute asynchronous HTTP request
				fetch(
					wrap.getAttribute( 'data-ajax-url' ),
					{
						method: 'POST',
						body: formData,
						signal: abortController.signal
					}
				)
			.then( res => res.json() )
			.then(
				res => {
                // Remove skeleton card placeholders
						skeletons.forEach( sk => sk.remove() );
                if ( res.success && res.data && res.data.html ) {
                    // Append parsed HTML blocks
                    const tempDiv     = document.createElement( 'div' );
                    tempDiv.innerHTML = res.data.html;

                    while ( tempDiv.firstChild ) {
                        container.appendChild( tempDiv.firstChild );
                    }

                    if ( res.data.max_num_pages ) {
                        maxPages = parseInt( res.data.max_num_pages );
                    }

                    // Hide pagination structure if we reached page limit
                    if ( currentPage >= maxPages ) {
                        const pagWrap = wrap.querySelector( '.es-post-list-pagination' );
                        if ( pagWrap ) {
                            pagWrap.style.display = 'none';
                        }
                    }
                }
				}
			)
				.catch(
				err => {
					skeletons.forEach( sk => sk.remove() );
				}
			)
				.finally(
					() => {
						isLoading = false;
                    if ( btn ) {
                        btn.removeAttribute( 'disabled' );
                        if ( spinner ) {
                            spinner.style.display = 'none';
                        }
                    }
					}
				);
        };
        // 3. Setup pagination triggers
				if ( 'load_more' === paginationType ) {
					const loadMoreBtn = wrap.querySelector( '.es-load-more-btn' );
					if ( loadMoreBtn ) {
						loadMoreBtn.addEventListener(
							'click',
							( e ) => {
                            e.preventDefault();
                            loadMorePosts();
							}
						);
					}
				} else if ( 'infinite_scroll' === paginationType ) {
					const scrollTrigger = wrap.querySelector( '.es-infinite-scroll-trigger' );
					if ( scrollTrigger ) {
						const observer = new IntersectionObserver(
							( entries ) => {
								if ( entries[0].isIntersecting && ! isLoading && currentPage < maxPages ) {
									loadMorePosts();
								}
							},
							{
								root: null,
								rootMargin: '100px',
								threshold: 0.1
							}
						);
						observer.observe( scrollTrigger );
					}
				}

				// 4. Custom Filter Listener for AJAX Filter widgets (like es-tag-cloud)
				wrap.addEventListener(
					'elonix/post_list/filter',
					( e ) => {
                    const selectedTerms     = e.detail.selectedTerms;
                    settings.selected_terms = selectedTerms;
                    wrap.setAttribute( 'data-settings', JSON.stringify( settings ) );
                    // Reset paging state and clear container
						currentPage         = 0;
                    maxPages            = 999;
                    container.innerHTML = '';
                    // Show pagination block if it was hidden
						const paginationWrapper = wrap.querySelector( '.es-post-list-pagination' );
                    if ( paginationWrapper ) {
                        paginationWrapper.style.display = 'flex';
                    }
                    // Trigger a fresh post list loading cycle
						loadMorePosts();
					}
				);
        }
    );
	}
);
