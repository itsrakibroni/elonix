document.addEventListener('DOMContentLoaded', function() {
	function initESComments( $scope ) {
		const wrapper = $scope && $scope.length ? $scope[0] : document;
		const commentsAreas = wrapper.querySelectorAll('.es-comments-area');
		
		commentsAreas.forEach(area => {
			const form = area.querySelector('.es-comment-respond form');
			if (!form) return;
			if (form.dataset.esAjaxInit) return;
			form.dataset.esAjaxInit = 'true';

			form.addEventListener('submit', function(e) {
				e.preventDefault();

				const submitBtn = form.querySelector('.es-submit-btn, .submit');
				if (!submitBtn) return;

				const btnContent = submitBtn.innerHTML || submitBtn.value;
				const errorMsgContainer = form.querySelector('.es-comment-error');
				
				if (errorMsgContainer) {
					errorMsgContainer.remove();
				}

				submitBtn.disabled = true;
				if (submitBtn.tagName.toLowerCase() === 'button') {
					submitBtn.innerHTML = '<span class="es-spinner" style="display:inline-block;width:14px;height:14px;border:2px solid currentColor;border-right-color:transparent;border-radius:50%;animation:es-spin .75s linear infinite;margin-right:8px;vertical-align:middle;"></span> Submitting...';
				} else {
					submitBtn.value = 'Submitting...';
				}

				if (!document.getElementById('es-spinner-style')) {
					const style = document.createElement('style');
					style.id = 'es-spinner-style';
					style.innerHTML = '@keyframes es-spin { to { transform: rotate(360deg); } }';
					document.head.appendChild(style);
				}

				const formData = new FormData(form);
				formData.append('action', 'es_submit_comment');
				if (typeof es_post_comments_ajax !== 'undefined' && es_post_comments_ajax.nonce) {
					formData.append('nonce', es_post_comments_ajax.nonce);
				}
				
				const requestUrl = (typeof es_post_comments_ajax !== 'undefined' && es_post_comments_ajax.ajaxurl) ? es_post_comments_ajax.ajaxurl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
				
				fetch(requestUrl, {
					method: 'POST',
					body: formData,
					headers: {
						'Accept': 'application/json'
					}
				})
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}
					return response.text();
				})
				.then(text => {
					try {
						const json = JSON.parse(text);
						return json;
					} catch(e) {
						throw e;
					}
				})
				.then(data => {
					submitBtn.disabled = false;
					if (submitBtn.tagName.toLowerCase() === 'button') submitBtn.innerHTML = btnContent;
					else submitBtn.value = btnContent;

					if (data.success) {
						const commentHtml = data.data.html;
						const parentId = data.data.parent;
						
						let targetList;
						if (parentId > 0) {
							const parentLi = area.querySelector('#comment-' + parentId);
							if (parentLi) {
								let childrenList = parentLi.querySelector('.children');
								if (!childrenList) {
									childrenList = document.createElement('ol');
									childrenList.className = 'children';
									parentLi.appendChild(childrenList);
								}
								targetList = childrenList;
							}
						}

						if (!targetList) {
							targetList = area.querySelector('.es-comment-list');
							if (!targetList) {
								targetList = document.createElement('ol');
								targetList.className = 'es-comment-list';
								const respond = area.querySelector('.es-comment-respond');
								area.insertBefore(targetList, respond);
								
								const noComments = area.querySelector('.es-no-comments');
								if (noComments) noComments.remove();
							}
						}

						if (targetList) {
							const tempDiv = document.createElement('div');
							tempDiv.innerHTML = commentHtml;
							const newComment = tempDiv.firstElementChild;
							
							const animType = area.dataset.animType || 'fade';
							const animDuration = area.dataset.animDuration || '0.4s';
							
							if (animType !== 'none') {
								newComment.style.opacity = '0';
								newComment.style.transition = `all ${animDuration} ease`;
								if (animType === 'slide_up') newComment.style.transform = 'translateY(20px)';
								else if (animType === 'scale') newComment.style.transform = 'scale(0.95)';
							}
							
							targetList.appendChild(newComment);
							
							if (animType !== 'none') {
								void newComment.offsetWidth; // reflow
								newComment.style.opacity = '1';
								if (animType === 'slide_up' || animType === 'scale') newComment.style.transform = 'none';
							}
							
							newComment.setAttribute('tabindex', '-1');
							newComment.focus();
							
							const countEl = area.querySelector('.es-comments-title');
							if (countEl) {
								countEl.innerHTML = countEl.innerHTML.replace(/\d+/, match => parseInt(match) + 1);
							}
							
							form.reset();
							const cancelBtn = document.getElementById('cancel-comment-reply-link');
							if (cancelBtn && cancelBtn.style.display !== 'none') cancelBtn.click();

							const msg = document.createElement('div');
							msg.className = 'es-comment-success-msg';
							msg.setAttribute('role', 'alert');
							msg.style.cssText = 'background:#e6f9ed;color:#189c54;padding:12px;margin-top:15px;border-radius:4px;font-weight:500;';
							msg.textContent = data.data.message;
							form.appendChild(msg);
							setTimeout(() => {
								msg.style.transition = 'opacity 0.4s';
								msg.style.opacity = '0';
								setTimeout(() => msg.remove(), 400);
							}, 5000);
						}
					} else {
						const msg = document.createElement('div');
						msg.className = 'es-comment-error';
						msg.setAttribute('role', 'alert');
						msg.style.cssText = 'background:#fceced;color:#d92550;padding:12px;margin-top:15px;border-radius:4px;font-weight:500;';
						msg.innerHTML = data.data || 'Unknown error occurred.';
						form.appendChild(msg);
					}
				})
				.catch(error => {
					console.error('Error submitting comment:', error);
					submitBtn.disabled = false;
					if (submitBtn.tagName.toLowerCase() === 'button') submitBtn.innerHTML = btnContent;
					else submitBtn.value = btnContent;
				});
			});
		});
		
		// AJAX Pagination
		const navLinks = wrapper.querySelectorAll('.es-comment-navigation a');
		navLinks.forEach(function(link) {
			if (link.dataset.esAjaxInit) return;
			link.dataset.esAjaxInit = 'true';
			link.addEventListener('click', function(e) {
				e.preventDefault();
				const url = this.getAttribute('href');
				const commentsArea = this.closest('.es-comments-area');
				if (!commentsArea) {
					return;
				}

				let fetchUrl = new URL(url, window.location.href);
				fetchUrl.searchParams.set('es_ajax_fetch', '1');

				commentsArea.style.opacity = '0.5';
				commentsArea.style.pointerEvents = 'none';

				fetch(fetchUrl.toString())
					.then(response => {
						if (!response.ok) {
							throw new Error('Network response was not ok');
						}
						return response.text();
					})
					.then(html => {
						if (!html || !html.trim()) {
							throw new Error('Received empty HTML response');
						}
						const parser = new DOMParser();
						const doc = parser.parseFromString(html, 'text/html');
						const newCommentsArea = doc.querySelector('.es-comments-area');
						
						if (newCommentsArea && newCommentsArea.innerHTML.trim() !== '') {
							commentsArea.innerHTML = newCommentsArea.innerHTML;
							commentsArea.style.opacity = '1';
							commentsArea.style.pointerEvents = 'auto';
							initESComments(); // Re-bind forms and pagination
                            
                            const scrollAfter = commentsArea.dataset.scrollAfter || 'smooth';
                            if (scrollAfter === 'top') {
                                window.scrollTo(0, 0);
                            } else if (scrollAfter === 'list') {
                                const offsetTop = commentsArea.getBoundingClientRect().top + window.scrollY - 100;
                                window.scrollTo(0, offsetTop);
                            } else if (scrollAfter === 'smooth') {
                                const offsetTop = commentsArea.getBoundingClientRect().top + window.scrollY - 100;
                                window.scrollTo({top: offsetTop, behavior: 'smooth'});
                            }
						} else {
							throw new Error('Comments area missing or empty in server response');
						}
					})
					.catch(error => {
						console.error('Error fetching pagination:', error);
						commentsArea.style.opacity = '1';
						commentsArea.style.pointerEvents = 'auto';
					});
			});
		});
	}

	initESComments();
	
	if (typeof jQuery !== 'undefined') {
		jQuery(window).on('elementor/frontend/init', function () {
			if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
				elementorFrontend.hooks.addAction('frontend/element_ready/es-post-comments.default', initESComments);
			}
		});
	}
});
