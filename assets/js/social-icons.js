/**
 * Elonix – Toolkit for Elementor Social Icons Widget Script
 *
 * Lightweight vanilla JavaScript handler for premium social icons.
 */

jQuery(window).on('elementor/frontend/init', () => {
	elementorFrontend.hooks.addAction('frontend/element_ready/es-social-icons.default', ($element) => {
		const wrapper = $element.find('.elonix-social-icons-wrapper')[0];
		if (!wrapper) return;

		const items = wrapper.querySelectorAll('.es-social-item');
		
		items.forEach((item) => {
			// Keydown keyboard accessibility for Spacebar key
			item.addEventListener('keydown', (e) => {
				if (e.key === ' ' || e.key === 'Spacebar') {
					e.preventDefault();
					item.click();
				}
			});

			// Focus state class toggle helpers
			item.addEventListener('focus', () => {
				item.classList.add('es-focused');
			});
			
			item.addEventListener('blur', () => {
				item.classList.remove('es-focused');
			});
		});
	});
});
