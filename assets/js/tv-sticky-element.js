/**
 * Elonix Sticky Element Engine
 * Zero jQuery Dependency | Native Elementor Integration
 */
class ElonixStickyEngine {

	constructor() {
		this.elements = new Map();
		this.ticking = false;
		this.lastScrollY = window.scrollY || document.documentElement.scrollTop;
		this.scrollDirection = 'down';
		this.eventsBound = false;

		this.onScroll = this.onScroll.bind(this);
		this.onResize = this.onResize.bind(this);
	}

	getConfig($scope) {
		const node = $scope[0];
		if (elementorFrontend.isEditMode()) {
			const settings = elementorFrontend.elements.getElementSettings($scope);
			return {
				devices: settings.tv_sticky_devices || ['desktop', 'tablet', 'mobile'],
				trigger: settings.tv_sticky_trigger || 'scroll_down',
				position: settings.tv_sticky_position || 'sticky',
				hideOnScroll: settings.tv_sticky_hide_on_scroll === 'yes'
			};
		} else {
			if (!node.hasAttribute('data-tv-sticky-config')) return null;
			try {
				return JSON.parse(node.getAttribute('data-tv-sticky-config'));
			} catch (e) {
				return null;
			}
		}
	}

	isStickyEnabled($scope) {
		if (elementorFrontend.isEditMode()) {
			const settings = elementorFrontend.elements.getElementSettings($scope);
			return settings.tv_sticky_enable === 'yes';
		}
		return $scope[0].classList.contains('tv-sticky-yes');
	}

	init($scope) {
		const node = $scope[0];
		if (!node) return;

		const id = $scope.data('id') || node.getAttribute('data-id');

		if (!this.isStickyEnabled($scope)) {
			this.destroy($scope);
			return;
		}

		const config = this.getConfig($scope);
		if (!config) return;

		this.destroy($scope);

		let placeholder = null;
		if (config.position === 'fixed') {
			placeholder = document.createElement('div');
			placeholder.style.display = 'none';
			placeholder.classList.add('tv-sticky-placeholder');
			node.parentNode.insertBefore(placeholder, node);
		}

		const rect = node.getBoundingClientRect();
		const currentScrollY = window.scrollY || document.documentElement.scrollTop;
		
		this.elements.set(id, {
			$scope: $scope,
			el: node,
			config: config,
			isSticking: false,
			placeholder: placeholder,
			initialOffsetTop: rect.top + currentScrollY,
			height: rect.height
		});

		if (!this.eventsBound && this.elements.size > 0) {
			window.addEventListener('scroll', this.onScroll, { passive: true });
			window.addEventListener('resize', this.onResize, { passive: true });
			this.eventsBound = true;
		}

		this.requestTick();
	}

	destroy($scope) {
		const node = $scope[0];
		if (!node) return;
		const id = $scope.data('id') || node.getAttribute('data-id');

		if (this.elements.has(id)) {
			const item = this.elements.get(id);
			this.resetElement(item);
			if (item.placeholder && item.placeholder.parentNode) {
				item.placeholder.parentNode.removeChild(item.placeholder);
			}
			this.elements.delete(id);
		}

		if (this.elements.size === 0 && this.eventsBound) {
			window.removeEventListener('scroll', this.onScroll);
			window.removeEventListener('resize', this.onResize);
			this.eventsBound = false;
		}
	}

	refresh($scope) {
		this.destroy($scope);
		this.init($scope);
	}

	update() {
		const currentScrollY = window.scrollY || document.documentElement.scrollTop;
		
		if (currentScrollY > this.lastScrollY) {
			this.scrollDirection = 'down';
		} else if (currentScrollY < this.lastScrollY) {
			this.scrollDirection = 'up';
		}

		let currentDevice = 'desktop';
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.getCurrentDeviceMode) {
			currentDevice = elementorFrontend.getCurrentDeviceMode();
		} else {
			const width = window.innerWidth;
			if (width < 768) currentDevice = 'mobile';
			else if (width <= 1024) currentDevice = 'tablet';
		}

		this.elements.forEach(item => {
			if (!item.config.devices.includes(currentDevice)) {
				this.resetElement(item);
				return;
			}

			const computedStyle = window.getComputedStyle(item.el);
			const topOffsetPx = parseFloat(computedStyle.top) || 0;
			const stickThreshold = item.initialOffsetTop - topOffsetPx;

			let shouldStick = false;
			let shouldHide = false;

			if (item.config.trigger === 'scroll_down') {
				if (currentScrollY >= stickThreshold) shouldStick = true;
			} else if (item.config.trigger === 'scroll_up') {
				if (currentScrollY >= stickThreshold) {
					shouldStick = true;
					if (this.scrollDirection === 'down') shouldHide = true;
				}
			}

			if (item.config.hideOnScroll && shouldStick) {
				if (this.scrollDirection === 'down') shouldHide = true;
			}

			if (shouldStick) {
				this.stickElement(item, shouldHide);
			} else {
				this.resetElement(item);
			}
		});

		this.lastScrollY = currentScrollY;
	}

	stickElement(item, shouldHide) {
		if (!item.isSticking) {
			item.el.classList.add('is-sticking');
			item.isSticking = true;

			if (item.config.position === 'fixed' && item.placeholder) {
				item.el.classList.add('tv-sticky-fixed');
				const computedStyle = window.getComputedStyle(item.el);
				item.el.style.top = computedStyle.top !== 'auto' ? computedStyle.top : '0px';
				item.placeholder.style.height = item.height + 'px';
				item.placeholder.style.display = 'block';
			}
		}

		if (shouldHide) {
			item.el.classList.add('is-hidden-scroll');
		} else {
			item.el.classList.remove('is-hidden-scroll');
		}
	}

	resetElement(item) {
		if (item.isSticking) {
			item.el.classList.remove('is-sticking');
			item.el.classList.remove('is-hidden-scroll');
			item.isSticking = false;

			if (item.config && item.config.position === 'fixed' && item.placeholder) {
				item.el.classList.remove('tv-sticky-fixed');
				item.el.style.top = '';
				item.placeholder.style.display = 'none';
			}
		}
	}

	onScroll() {
		this.requestTick();
	}

	onResize() {
		const currentScrollY = window.scrollY || document.documentElement.scrollTop;
		this.elements.forEach(item => {
			if (!item.isSticking) {
				const rect = item.el.getBoundingClientRect();
				item.initialOffsetTop = rect.top + currentScrollY;
				item.height = rect.height;
			}
		});
		this.requestTick();
	}

	requestTick() {
		if (!this.ticking) {
			window.requestAnimationFrame(() => {
				this.update();
				this.ticking = false;
			});
			this.ticking = true;
		}
	}
}

window.ElonixStickyEngineInstance = new ElonixStickyEngine();

// Integrate with Elementor native class handlers
class ElonixStickyHandler extends elementorModules.frontend.handlers.Base {
	onInit() {
		super.onInit();
		window.ElonixStickyEngineInstance.init(this.$element);
	}
	onDestroy() {
		window.ElonixStickyEngineInstance.destroy(this.$element);
		super.onDestroy();
	}
	onElementChange(propertyName) {
		if (propertyName.indexOf('tv_sticky_') === 0) {
			window.ElonixStickyEngineInstance.refresh(this.$element);
		}
	}
}

window.addEventListener('elementor/frontend/init', () => {
	elementorFrontend.hooks.addAction('frontend/element_ready/global', function($scope) {
		elementorFrontend.elementsHandler.addHandler(ElonixStickyHandler, { $element: $scope });
	});
});
