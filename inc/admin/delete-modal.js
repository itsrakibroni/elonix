/**
 * Elonix – Toolkit for Elementor Admin Deletion Modal Interceptor
 */
(function ($) {
	'use strict';

	function initDeleteInterceptor() {
		const deleteLink = document.querySelector( 'a.delete-now[data-plugin="elonix/elonix.php"]' );

		if (deleteLink) {
			deleteLink.classList.remove( 'delete-now' );
			deleteLink.classList.add( 'elonix-delete-trigger' );

			deleteLink.addEventListener(
				'click',
				function (e) {
					e.preventDefault();
					openModal();
				}
			);
		}
	}

	// Modal state functions
	function openModal() {
		injectModalHtml();
		const overlay = document.getElementById( 'elonix-delete-modal-overlay' );
		if (overlay) {
			overlay.classList.add( 'active' );
			document.body.style.overflow = 'hidden';
		}
	}

	function closeModal() {
		const overlay = document.getElementById( 'elonix-delete-modal-overlay' );
		if (overlay) {
			overlay.classList.remove( 'active' );
			document.body.style.overflow = '';
			const card                   = overlay.querySelector( '.elonix-modal-card' );
			if (card) {
				card.classList.remove( 'loading' );
			}
		}
	}

	function injectModalHtml() {
		if (document.getElementById( 'elonix-delete-modal-overlay' )) {
			return;
		}

		const html                 = `
			<div id="elonix-delete-modal-overlay" class="elonix-modal-overlay">
				<div class="elonix-modal-card">
					<button class="elonix-modal-close" aria-label="Close">&times;</button>
					<div class="elonix-modal-header">
						<h2>Delete Elonix – Toolkit for Elementor</h2>
					</div>
					<div class="elonix-modal-body">
						<p class="elonix-modal-intro">
							Choose what you'd like to do with your plugin data( services, projects, team members, gallery items, and settings ):
						</p>

						<div class="elonix-options-container">
							<!--Option 1: Keep My Data-->
							<div class="elonix-option-card keep-data" id="elonix-btn-keep">
								<div class="option-icon">
									<span class="dashicons dashicons-database-protect"></span>
								</div>
								<div class="option-content">
									<h3>Keep My Data</h3>
									<p>Plugin will be removed, but your content and settings will be restored automatically if you reinstall.</p>
								</div>
								<div class="option-action">
									<span class="action-badge badge-primary">Recommended</span>
								</div>
							</div>

							<!--Option 2: Delete Everything-->
							<div class="elonix-option-card delete-data" id="elonix-btn-delete">
								<div class="option-icon">
									<span class="dashicons dashicons-trash"></span>
								</div>
								<div class="option-content">
									<h3>Delete Everything</h3>
									<p class="danger-text">Permanently remove the plugin AND all its data. This cannot be undone.</p>
								</div>
								<div class="option-action">
									<span class="action-badge badge-danger">Permanent</span>
								</div>
							</div>
						</div>
					</div>

					<div class="elonix-modal-footer">
						<div class="elonix-modal-spinner"></div>
						<button class="elonix-modal-cancel-btn">Cancel</button>
					</div>
				</div>
			</div>
		`;

		const styles = `
			.elonix-modal-overlay {
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background-color: rgba( 15, 23, 42, 0.6 );
				backdrop-filter: blur( 4px );
				z-index: 100000;
				display: flex;
				align-items: center;
				justify-content: center;
				opacity: 0;
				transition: opacity 0.25s ease;
				pointer-events: none;
		}
			.elonix-modal-overlay.active {
				opacity: 1;
				pointer-events: auto;
		}
			.elonix-modal-card {
				background: #ffffff;
				border-radius: 12px;
				box-shadow: 0 20px 25px - 5px rgba( 0, 0, 0, 0.1 ), 0 10px 10px - 5px rgba( 0, 0, 0, 0.04 );
				width: 90%;
				max-width: 580px;
				border-top: 5px solid #2271b1;
				position: relative;
				transform: translateY( 20px );
				transition: transform 0.25s cubic-bezier( 0.16, 1, 0.3, 1 );
				padding: 30px;
				box-sizing: border-box;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
		}
			.elonix-modal-overlay.active .elonix-modal-card {
				transform: translateY( 0 );
		}
			.elonix-modal-close {
				position: absolute;
				top: 15px;
				right: 15px;
				background: none;
				border: none;
				font-size: 24px;
				color: #94a3b8;
				cursor: pointer;
				line-height: 1;
				padding: 4px;
				transition: color 0.15s ease;
		}
			.elonix-modal-close:hover {
				color: #475569;
		}
			.elonix-modal-header h2 {
				margin: 0 0 12px 0;
				font-size: 20px;
				font-weight: 600;
				color: #1e293b;
				line-height: 1.3;
		}
			.elonix-modal-intro {
				font-size: 14px;
				color: #64748b;
				margin-bottom: 24px;
				line-height: 1.5;
		}
			.elonix-options-container {
				display: flex;
				flex-direction: column;
				gap: 16px;
				margin-bottom: 24px;
		}
			.elonix-option-card {
				border: 1px solid #e2e8f0;
				border-radius: 8px;
				padding: 16px;
				display: flex;
				align-items: flex-start;
				gap: 16px;
				cursor: pointer;
				transition: all 0.2s ease;
				background: #f8fafc;
				position: relative;
		}
			.elonix-option-card:hover {
				border-color: #cbd5e1;
				background: #f1f5f9;
				transform: translateY( -2px );
				box-shadow: 0 4px 6px - 1px rgba( 0, 0, 0, 0.05 );
		}
			.elonix-option-card.keep-data {
				border-left: 4px solid #2271b1;
		}
			.elonix-option-card.keep-data:hover {
				border-color: #2271b1;
		}
			.elonix-option-card.delete-data {
				border-left: 4px solid #d93f3f;
		}
			.elonix-option-card.delete-data:hover {
				border-color: #d93f3f;
				background: #fff8f8;
		}
			.option-icon {
				font-size: 20px;
				color: #64748b;
				padding-top: 2px;
		}
			.keep-data .option-icon {
				color: #2271b1;
		}
			.delete-data .option-icon {
				color: #d93f3f;
		}
			.option-content {
				flex: 1;
		}
			.option-content h3 {
				margin: 0 0 4px 0;
				font-size: 15px;
				font-weight: 600;
				color: #0f172a;
		}
			.option-content p {
				margin: 0;
				font-size: 13px;
				line-height: 1.4;
				color: #475569;
		}
			.option-content p.danger-text {
				color: #991b1b;
		}
			.option-action {
				align-self: center;
		}
			.action-badge {
				font-size: 11px;
				font-weight: 600;
				padding: 3px 8px;
				border-radius: 4px;
				text-transform: uppercase;
				letter-spacing: 0.05em;
		}
			.badge-primary {
				background: #e0f2fe;
				color: #0369a1;
		}
			.badge-danger {
				background: #fee2e2;
				color: #b91c1c;
		}
			.elonix-modal-footer {
				display: flex;
				justify-content: flex-end;
				align-items: center;
				border-top: 1px solid #e2e8f0;
				padding-top: 16px;
				gap: 12px;
		}
			.elonix-modal-cancel-btn {
				background: #fff;
				border: 1px solid #cbd5e1;
				color: #475569;
				padding: 8px 16px;
				font-size: 13px;
				font-weight: 500;
				border-radius: 6px;
				cursor: pointer;
				transition: all 0.15s ease;
		}
			.elonix-modal-cancel-btn:hover {
				background: #f8fafc;
				border-color: #94a3b8;
				color: #1e293b;
		}
			.elonix-modal-spinner {
				display: none;
				width: 18px;
				height: 18px;
				border: 2px solid #e2e8f0;
				border-top-color: #2271b1;
				border-radius: 50%;
				animation: elonix-spin 0.8s linear infinite;
		}
			@keyframes elonix-spin {
				to { transform: rotate( 360deg ); }
		}
			.elonix-modal-card.loading {
				pointer-events: none;
				opacity: 0.7;
		}
			.elonix-modal-card.loading .elonix-modal-spinner {
				display: block;
		}
		`;

		// Inject CSS
		const styleEl     = document.createElement( 'style' );
		styleEl.innerHTML = styles;
		document.head.appendChild( styleEl );

		// Inject HTML
		const div     = document.createElement( 'div' );
		div.innerHTML = html.trim();
		document.body.appendChild( div.firstChild );

		// Setup event listeners for the modal
		setupModalListeners();
	}

	function setupModalListeners() {
		const overlay   = document.getElementById( 'elonix-delete-modal-overlay' );
		const closeBtn  = overlay.querySelector( '.elonix-modal-close' );
		const cancelBtn = overlay.querySelector( '.elonix-modal-cancel-btn' );
		const keepBtn   = overlay.getElementById( 'elonix-btn-keep' );
		const deleteBtn = overlay.getElementById( 'elonix-btn-delete' );

		// Close buttons
		closeBtn.addEventListener( 'click', closeModal );
		cancelBtn.addEventListener( 'click', closeModal );

		// Click outside to close
		overlay.addEventListener(
			'click',
			function (e) {
				if (e.target === overlay) {
					closeModal();
				}
			}
		);

		// Escape key to close
		document.addEventListener(
			'keydown',
			function (e) {
				if (e.key === 'Escape' && overlay.classList.contains( 'active' )) {
					closeModal();
				}
			}
		);

		// Choices click
		keepBtn.addEventListener( 'click', () => handleChoice( false ) );
		deleteBtn.addEventListener( 'click', () => handleChoice( true ) );
	}

	async function handleChoice(removeData) {
		const overlay = document.getElementById( 'elonix-delete-modal-overlay' );
		const card    = overlay.querySelector( '.elonix-modal-card' );

		// Show spinner and block inputs
		card.classList.add( 'loading' );

		try {
			const formData = new FormData();
			formData.append( 'action', 'elonix_set_uninstall_pref' );
			formData.append( 'nonce', elonixDeleteOpts.nonce );
			formData.append( 'remove_data', removeData ? 'true' : 'false' );

			const response = await fetch(
				elonixDeleteOpts.ajax_url,
				{
					method: 'POST',
					body: formData
				}
			);

			if ( ! response.ok) {
				throw new Error( 'Network response was not ok' );
			}

			const data = await response.json();
			if (data.success) {
				// Successfully saved preference. Close modal.
				closeModal();

				// Trigger WordPress native deletion AJAX
				if (typeof wp !== 'undefined' && wp.updates && typeof wp.updates.deletePlugin === 'function') {
					wp.updates.deletePlugin(
						{
							plugin: elonixDeleteOpts.plugin,
							slug: elonixDeleteOpts.slug
						}
					);
					if (typeof window.ElonixNotifier !== 'undefined') {
						window.ElonixNotifier.error( 'An error occurred. Please refresh and try again.' );
					}
			} else {
				throw new Error( data.data && data.data.message ? data.data.message : 'Unknown server error' );
			}
		} catch (err) {
			if (typeof window.ElonixNotifier !== 'undefined') {
				window.ElonixNotifier.error( 'Could not update uninstall preference: ' + err.message );
			}
			card.classList.remove( 'loading' );
		}
	}

	// Run when DOM is ready
	$(
		function () {
			initDeleteInterceptor();
		}
	);
})( jQuery );
