/**
 * Elonix Search Results — Frontend Script
 *
 * Hooks into the Elementor frontend init so the widget gets the same AJAX
 * pagination, Load More, and Infinite Scroll behaviour as tv-post-block.
 *
 * The widget renders identical HTML to Post Block Grid (same .tv-post-block-*
 * classes), so this script only needs to:
 *   1. Register the Elementor element-ready hook so tv-post-block.js picks up
 *      the wrapper automatically.
 *   2. Handle the search-results-specific filter sidebar toggle.
 *
 * AJAX Load More / Infinite Scroll / Numeric Pagination is handled by
 * tv-post-block.js which reads data-action="tv_search_results_fetch" from the
 * wrapper element and POSTs to that action instead of tv_post_block_fetch_posts.
 *
 * @package Elonix_Toolkit
 */

/* global elementorFrontend */
(function ($) {
	'use strict';

	/**
	 * Init search-results specific chrome (stats bar update, filter sidebar).
	 *
	 * @param {jQuery} $scope Elementor widget scope.
	 */
	function initSearchResultsChrome($scope) {
		var wrap = $scope && $scope[0] ? $scope[0].querySelector('.tv-post-block-wrap.tv-search-results-wrap') : null;
		if (!wrap) {
			return;
		}

		// Filter sidebar toggle (search-results specific).
		var sidebar = wrap.querySelector('.tv-search-results-sidebar');
		var toggle  = wrap.querySelector('.tv-search-results-filter-toggle');
		if (toggle && sidebar) {
			toggle.addEventListener('click', function () {
				var open = !sidebar.classList.contains('is-open');
				sidebar.classList.toggle('is-open', open);
				toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			});
		}

		// Sidebar filter selects.
		wrap.querySelectorAll('.tv-search-results-filter').forEach(function (field) {
			field.addEventListener('change', function () {
				// The tv-post-block.js handles the actual AJAX call via the
				// data-action attribute on the wrapper. We just need to update
				// the data-settings payload and trigger a re-fetch.
				var settings;
				try {
					settings = JSON.parse(wrap.getAttribute('data-settings') || '{}');
				} catch (e) {
					settings = {};
				}

				var filterName  = field.getAttribute('data-filter') || '';
				var filterValue = field.value || '';

				if ('post_type' === filterName) {
					settings.post_type_filter = filterValue;
				} else if ('date_filter' === filterName) {
					settings.date_filter = filterValue;
				}

				wrap.setAttribute('data-settings', JSON.stringify(settings));

				// Dispatch a custom event that tv-post-block.js can optionally
				// listen to. For now the sidebar filter triggers a page reload
				// with the filter appended to the URL as a search param so the
				// server-side query picks it up.
				var currentUrl = new URL(window.location.href);
				if (filterValue) {
					currentUrl.searchParams.set(filterName, filterValue);
				} else {
					currentUrl.searchParams.delete(filterName);
				}
				window.location.href = currentUrl.toString();
			});
		});
	}

	// Register Elementor element-ready hook.
	$(window).on('elementor/frontend/init', function () {
		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			window.elementorFrontend.hooks.addAction(
				'frontend/element_ready/tv-search-results.default',
				initSearchResultsChrome
			);
		}
	});

	// Run on DOM ready for non-Elementor contexts.
	$(function () {
		document.querySelectorAll('.tv-post-block-wrap.tv-search-results-wrap').forEach(function (wrap) {
			var sidebar = wrap.querySelector('.tv-search-results-sidebar');
			var toggle  = wrap.querySelector('.tv-search-results-filter-toggle');
			if (toggle && sidebar) {
				toggle.addEventListener('click', function () {
					var open = !sidebar.classList.contains('is-open');
					sidebar.classList.toggle('is-open', open);
					toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
				});
			}
		});
	});
})(jQuery);
