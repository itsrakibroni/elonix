/**
 * Elonix Header Builder Front-end Script
 */
(function ($) {
	'use strict';

	$( window ).on(
		'elementor/frontend/init',
		function () {
			var $window  = $( window );
			var $headers = $( '.tv-site-header' );

			if ($headers.length === 0) {
				return;
			}

			// Handle sticky header class toggle on scroll
			var handleScroll = function () {
				var scrollTop = $window.scrollTop();
				if (scrollTop > 50) { // Toggle after scrolling 50px
					$headers.addClass( 'tv-header-sticky' );
				} else {
					$headers.removeClass( 'tv-header-sticky' );
				}
			};

			// Run once on load and bind to scroll event
			handleScroll();
			$window.on( 'scroll', handleScroll );
		}
	);
})( jQuery );
