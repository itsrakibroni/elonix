/**
 * Elonix – Toolkit for Elementor Post Share Widget Script
 *
 * Premium vanilla JavaScript handler for popup sharing, custom modals, and clipboard copying.
 */

jQuery(window).on('elementor/frontend/init', () => {
	elementorFrontend.hooks.addAction('frontend/element_ready/es-post-share.default', ($element) => {
		const container = $element.find('.es-post-share-container')[0];
		if (!container) return;

		// Modal Popup Management
		const modalTrigger = container.querySelector('.es-share-modal-trigger');
		const modalOverlay = container.querySelector('.es-share-modal-overlay');
		const modalClose = container.querySelector('.es-share-modal-close');
		const modalBox = container.querySelector('.es-share-modal-box');

		if (modalTrigger && modalOverlay) {
			const openModal = (e) => {
				e.preventDefault();
				modalOverlay.classList.add('es-modal-show');
				modalOverlay.setAttribute('aria-hidden', 'false');
				modalTrigger.setAttribute('aria-expanded', 'true');
				// Focus on close button for accessibility
				setTimeout(() => modalClose && modalClose.focus(), 100);
			};

			const closeModal = () => {
				modalOverlay.classList.remove('es-modal-show');
				modalOverlay.setAttribute('aria-hidden', 'true');
				modalTrigger.setAttribute('aria-expanded', 'false');
				modalTrigger.focus();
			};

			modalTrigger.addEventListener('click', openModal);

			if (modalClose) {
				modalClose.addEventListener('click', closeModal);
			}

			// Close on backdrop click
			modalOverlay.addEventListener('click', (e) => {
				if (e.target === modalOverlay) {
					closeModal();
				}
			});

			// Close on Escape keypress
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape' && modalOverlay.classList.contains('es-modal-show')) {
					closeModal();
				}
			});
		}

		// Individual Share Items Management
		const items = container.querySelectorAll('.es-social-item');
		
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

			// Browser Native Popup Share Handler
			if (item.getAttribute('data-es-share-popup') === 'true') {
				item.addEventListener('click', (e) => {
					e.preventDefault();
					const shareUrl = item.getAttribute('href');
					const width = parseInt(item.getAttribute('data-es-popup-width'), 10) || 600;
					const height = parseInt(item.getAttribute('data-es-popup-height'), 10) || 500;

					// Calculate center screen coordinates
					const left = (window.innerWidth - width) / 2 + window.screenX;
					const top = (window.innerHeight - height) / 2 + window.screenY;

					const popup = window.open(
						shareUrl,
						'es_share_popup',
						`width=${width},height=${height},top=${top},left=${left},toolbar=0,location=0,menubar=0,status=0`
					);

					// Fallback to new tab if popup is blocked
					if (!popup) {
						window.open(shareUrl, '_blank');
					} else {
						popup.focus();
					}
				});
			}

			// Copy Link Handler
			const copyUrl = item.getAttribute('data-es-copy-url');
			if (copyUrl) {
				item.addEventListener('click', (e) => {
					e.preventDefault();
					const successMsg = item.getAttribute('data-es-copy-success') || 'Copied!';

					const showToast = () => {
						// Append toast directly to container to ensure Elementor {{WRAPPER}} styles apply perfectly
						let toast = container.querySelector('.es-copy-success-toast');
						if (!toast) {
							toast = document.createElement('div');
							toast.className = 'es-copy-success-toast';
							container.appendChild(toast);
						}

						toast.innerHTML = `<svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg><span>${successMsg}</span>`;
						
						// Trigger reflow and show
						void toast.offsetWidth;
						toast.classList.add('es-toast-show');

						setTimeout(() => {
							toast.classList.remove('es-toast-show');
						}, 3000);
					};

					// Check if modern clipboard API is available (HTTPS / localhost)
					if (navigator.clipboard && navigator.clipboard.writeText) {
						navigator.clipboard.writeText(copyUrl).then(showToast).catch((err) => {
							console.error('Failed to copy link: ', err);
						});
					} else {
						// Fallback for non-secure HTTP contexts (e.g. http://*.local)
						const textarea = document.createElement('textarea');
						textarea.value = copyUrl;
						textarea.style.position = 'fixed';
						textarea.style.opacity = '0';
						document.body.appendChild(textarea);
						textarea.select();
						try {
							document.execCommand('copy');
							showToast();
						} catch (err) {
							console.error('Fallback failed to copy link: ', err);
						}
						document.body.removeChild(textarea);
					}
				});
			}
		});
	});
});
