/**
 * Elonix Toggle Widget JS
 * Phase 3.5: Internal Event Bus & Public API Foundation
 */

(function($) {
	'use strict';

	var TVToggleHandler = elementorModules.frontend.handlers.Base.extend({
		
		getDefaultSettings: function() {
			return {
				selectors: {
					toggleWrapper: '.tv-toggle__wrapper',
					toggleList: '.tv-toggle__list',
					toggleItem: '.tv-toggle__item',
					toggleButton: '.tv-toggle__button'
				},
				classes: {
					activeButton: 'tv-toggle__button--active',
					stateHidden: 'tv-state-is-hidden',
					stateVisible: 'tv-state-is-visible',
					stateInactive: 'tv-state-is-inactive',
					stateActive: 'tv-state-is-active'
				},
				keyboard: {
					ENTER: 13,
					SPACE: 32,
					END: 35,
					HOME: 36,
					LEFT: 37,
					UP: 38,
					RIGHT: 39,
					DOWN: 40
				},
				constants: {
					events: {
						INIT: 'tvToggle:init',
						BEFORE_TOGGLE: 'tvToggle:beforeToggle',
						AFTER_TOGGLE: 'tvToggle:afterToggle',
						DESTROY: 'tvToggle:destroy',
						REFRESH: 'tvToggle:refresh',
						SYNC: 'tvToggle:sync',
						ERROR: 'tvToggle:error'
					},
					states: {
						IDLE: 'IDLE',
						READY: 'READY',
						TRANSITIONING: 'TRANSITIONING',
						SYNCING: 'SYNCING',
						DESTROYED: 'DESTROYED',
						ERROR: 'ERROR'
					}
				}
			};
		},

		getDefaultElements: function() {
			var selectors = this.getSettings('selectors');
			return {
				$toggleWrapper: this.$element.find(selectors.toggleWrapper),
				$toggleList: this.$element.find(selectors.toggleList),
				$toggleItems: this.$element.find(selectors.toggleItem),
				$toggleButtons: this.$element.find(selectors.toggleButton)
			};
		},

		onInit: function() {
			elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);
			
			this.prepareState();
			this.prepareStateMachine();
			this.prepareEventBus();
			this.prepareAPI();
			
			this.syncSettings();
			this.prepareSyncGroup();
			this.cacheTargets();

			this.prepareObservers();

			if (typeof Elonix !== 'undefined' && Elonix.Core && Elonix.Core.InstanceRegistry) {
				var id = this.$element.data('id');
				if (id) {
					if (Elonix.Core.InstanceRegistry.has && Elonix.Core.InstanceRegistry.has(id)) {
						if (Elonix.Core.InstanceRegistry.destroy) {
							Elonix.Core.InstanceRegistry.destroy(id);
						} else if (Elonix.Core.InstanceRegistry.remove) {
							Elonix.Core.InstanceRegistry.remove(id);
						}
					}
					if (Elonix.Core.InstanceRegistry.register) {
						Elonix.Core.InstanceRegistry.register(id, this);
					}
				}
			}

			this.bindEvents();
			this.emit(this.getSettings('constants.events').INIT, this);
			
			this.hideAllTargets();
			this.resolveInitialState();
		},

		onDestroy: function() {
			this.unbindEvents();
			
			this.clearObservers();
			this.clearSyncGroup();
			this.clearEventBus();
			this.clearStateMachine();
			this.clearCaches();

			if (typeof Elonix !== 'undefined' && Elonix.Core && Elonix.Core.InstanceRegistry) {
				var id = this.$element.data('id');
				if (id && Elonix.Core.InstanceRegistry.has && Elonix.Core.InstanceRegistry.has(id)) {
					if (Elonix.Core.InstanceRegistry.destroy) {
						Elonix.Core.InstanceRegistry.destroy(id);
					} else if (Elonix.Core.InstanceRegistry.remove) {
						Elonix.Core.InstanceRegistry.remove(id);
					}
				}
			}
			
			elementorModules.frontend.handlers.Base.prototype.onDestroy.apply(this, arguments);
		},

		/* -------------------------------------------------------------
		 * LIFECYCLE
		 * ------------------------------------------------------------- */
		prepareState: function() {
			this.state = {
				activeIndex: -1,
				activeSelector: null,
				isAnimating: false,
				isDestroyed: false,
				currentHash: null,
				storageKey: null
			};
		},

		prepareEventBus: function() {
			this.events = {};
		},

		prepareAPI: function() {
			// Public API is natively on the prototype.
		},
		
		prepareObservers: function() {
			if (!elementorFrontend.isEditMode()) return;
			
			var self = this;
			
			if ('MutationObserver' in window) {
				this.editorMutationObserver = new MutationObserver(function(mutations) {
					var shouldRefresh = false;
					for (var i = 0; i < mutations.length; i++) {
						var mutation = mutations[i];
						if (mutation.type === 'childList') {
							for (var j = 0; j < mutation.addedNodes.length; j++) {
								var node = mutation.addedNodes[j];
								if (node.nodeType === 1 && (node.classList.contains('elementor-element') || node.classList.contains('elementor-container'))) {
									shouldRefresh = true;
									break;
								}
							}
						}
						if (shouldRefresh) break;
					}
					
					if (shouldRefresh) {
						if (self.mutationTimer) clearTimeout(self.mutationTimer);
						self.mutationTimer = setTimeout(function() {
							self.cacheTargets();
							self.hideAllTargets();
							if (self.state.activeIndex !== -1) {
								var idx = self.state.activeIndex;
								self.state.activeIndex = -1;
								self._activateInternal(idx);
							}
						}, 250);
					}
				});
				
				var previewContainer = document.querySelector('.elementor');
				if (previewContainer) {
					this.editorMutationObserver.observe(previewContainer, {
						childList: true,
						subtree: true
					});
				}
			}
			
			if ('ResizeObserver' in window) {
				this.editorResizeObserver = new ResizeObserver(function(entries) {
					if (self.resizeTimer) clearTimeout(self.resizeTimer);
					self.resizeTimer = setTimeout(function() {
						self.validateTargets();
					}, 250);
				});
				
				this.observeTargets = function() {
					if (self.editorResizeObserver) {
						self.editorResizeObserver.disconnect();
						if (self.state.activeSelector && self.targetCache[self.state.activeSelector]) {
							var nodes = self.targetCache[self.state.activeSelector];
							for (var i = 0; i < nodes.length; i++) {
								self.editorResizeObserver.observe(nodes[i]);
							}
						}
					}
				};
				
				this.on(this.getSettings('constants.events').AFTER_TOGGLE, this.observeTargets);
			}
		},

		clearObservers: function() {
			if (this.editorMutationObserver) {
				this.editorMutationObserver.disconnect();
				this.editorMutationObserver = null;
			}
			if (this.editorResizeObserver) {
				this.editorResizeObserver.disconnect();
				this.editorResizeObserver = null;
			}
			if (this.observeTargets) {
				this.off(this.getSettings('constants.events').AFTER_TOGGLE, this.observeTargets);
				this.observeTargets = null;
			}
			if (this.mutationTimer) {
				clearTimeout(this.mutationTimer);
				this.mutationTimer = null;
			}
			if (this.resizeTimer) {
				clearTimeout(this.resizeTimer);
				this.resizeTimer = null;
			}
		},

		prepareSyncGroup: function() {
			this.syncGroup = this.toggleSettings.sync_group || null;
			this.groupMembers = [];
			
			if (!this.syncGroup) return;
			
			if (!TVToggleHandler.syncGroups) TVToggleHandler.syncGroups = {};
			if (!TVToggleHandler.syncGroups[this.syncGroup]) TVToggleHandler.syncGroups[this.syncGroup] = [];
			
			TVToggleHandler.syncGroups[this.syncGroup].push(this);
		},
		
		clearSyncGroup: function() {
			if (this.syncGroup && TVToggleHandler.syncGroups && TVToggleHandler.syncGroups[this.syncGroup]) {
				var idx = TVToggleHandler.syncGroups[this.syncGroup].indexOf(this);
				if (idx > -1) {
					TVToggleHandler.syncGroups[this.syncGroup].splice(idx, 1);
				}
			}
		},

		clearEventBus: function() {
			this.emit(this.getSettings('constants.events').DESTROY, this);
			this.events = {};
		},

		clearCaches: function() {
			this.buttonCache = null;
			this.targetCache = null;
			this.selectorCache = null;
		},

		resolveInitialState: function() {
			var mode = this.toggleSettings.default_active_mode;
			var indexToActivate = -1;
			
			if (this.toggleSettings.remember_state === 'session_storage' && sessionStorage) {
				var stored = sessionStorage.getItem('tvToggle_' + this.$element.data('id'));
				if (stored !== null) indexToActivate = parseInt(stored, 10);
			} else if (this.toggleSettings.remember_state === 'local_storage' && localStorage) {
				var stored = localStorage.getItem('tvToggle_' + this.$element.data('id'));
				if (stored !== null) indexToActivate = parseInt(stored, 10);
			}

			if (indexToActivate === -1 && this.toggleSettings.enable_url_hash === 'yes') {
				var hash = window.location.hash;
				if (hash) {
					var $btn = this.elements.$toggleButtons.filter('[data-tv-target="' + hash + '"]');
					if ($btn.length) {
						indexToActivate = $btn.data('tv-index');
					}
				}
			}
			
			if (indexToActivate === -1 && mode === 'query_parameter') {
				var urlParams = new URLSearchParams(window.location.search);
				var param = urlParams.get('tv_toggle');
				if (param) {
					var $btn = this.elements.$toggleButtons.filter('[data-tv-target="#' + param + '"], [data-tv-target=".' + param + '"]');
					if ($btn.length) {
						indexToActivate = $btn.data('tv-index');
					}
				}
			}

			if (indexToActivate === -1 && mode === 'index') {
				indexToActivate = parseInt(this.toggleSettings.default_active_index, 10);
			}

			if (indexToActivate >= 0 && indexToActivate < this.elements.$toggleButtons.length) {
				this.activate(indexToActivate);
			} else if (this.elements.$toggleButtons.length > 0) {
				this.activate(0);
			}
		},

		/* -------------------------------------------------------------
		 * STATE MACHINE
		 * ------------------------------------------------------------- */
		prepareStateMachine: function() {
			this.machineState = this.getSettings('constants.states.IDLE');
			this.transitionTo(this.getSettings('constants.states.READY'));
		},

		clearStateMachine: function() {
			this.transitionTo(this.getSettings('constants.states.DESTROYED'));
			this.machineState = null;
		},

		getState: function() {
			return this.machineState;
		},

		setState: function(newState) {
			this.machineState = newState;
		},

		isState: function(stateName) {
			return this.machineState === stateName;
		},

		canTransition: function(targetState) {
			var states = this.getSettings('constants.states');
			var current = this.machineState;

			if (current === states.IDLE && targetState === states.READY) return true;
			if (current === states.READY && targetState === states.TRANSITIONING) return true;
			if (current === states.TRANSITIONING && targetState === states.READY) return true;
			if (current === states.READY && targetState === states.SYNCING) return true;
			if (current === states.SYNCING && targetState === states.READY) return true;
			if (current === states.READY && targetState === states.DESTROYED) return true;
			if (current === states.READY && targetState === states.ERROR) return true;
			if (current === states.ERROR && targetState === states.READY) return true;

			return false;
		},

		transitionTo: function(targetState) {
			if (!this.canTransition(targetState)) {
				return false;
			}
			
			var previousState = this.machineState;
			var events = this.getSettings('constants.events');
			var states = this.getSettings('constants.states');
			
			if (targetState === states.TRANSITIONING) {
				this.emit(events.BEFORE_TOGGLE, { previousState: previousState, targetState: targetState });
			}
			
			this.setState(targetState);
			
			if (targetState === states.READY && previousState === states.TRANSITIONING) {
				this.emit(events.AFTER_TOGGLE, { currentState: this.machineState });
			}

			if (targetState === states.TRANSITIONING) {
				this.startTransitionWatchdog();
			} else {
				this.stopTransitionWatchdog();
			}
			
			return true;
		},
		
		startTransitionWatchdog: function() {
			var self = this;
			this.stopTransitionWatchdog();
			this.watchdogTimer = setTimeout(function() {
				if (self.isState(self.getSettings('constants.states.TRANSITIONING'))) {
					self.setState(self.getSettings('constants.states.READY'));
					self.emitError('DEADLOCK', 'State remained TRANSITIONING for longer than 2 seconds.');
				}
			}, 2000);
		},

		stopTransitionWatchdog: function() {
			if (this.watchdogTimer) {
				clearTimeout(this.watchdogTimer);
				this.watchdogTimer = null;
			}
		},

		resetState: function() {
			if (!this.canTransition(this.getSettings('constants.states.TRANSITIONING'))) return;
			this.transitionTo(this.getSettings('constants.states.READY'));
		},

		emitError: function(code, message, selector) {
			this.lastError = {
				code: code,
				message: message,
				selector: selector || null,
				time: new Date().getTime()
			};
			
			var states = this.getSettings('constants.states');
			if (this.canTransition(states.ERROR)) {
				this.transitionTo(states.ERROR);
				this.emit(this.getSettings('constants.events').ERROR, this.lastError);
				
				if (this.toggleSettings && this.toggleSettings.debug_mode === 'yes' && elementorFrontend.isEditMode()) {
					console.error('[TV Toggle Error] ' + code + ': ' + message + (selector ? ' (' + selector + ')' : ''));
				}
				
				this.transitionTo(states.READY);
			}
		},

		/* -------------------------------------------------------------
		 * INTERNAL EVENT BUS
		 * ------------------------------------------------------------- */
		emit: function(name, payload) {
			if (this.events[name]) {
				var callbacks = this.events[name].slice();
				for (var i = 0; i < callbacks.length; i++) {
					callbacks[i](payload);
				}
			}

			// Developer Events
			var events = this.getSettings('constants.events');
			if (this.toggleSettings) {
				if (name === events.BEFORE_TOGGLE && this.toggleSettings.before_toggle_event) {
					jQuery(document).trigger(this.toggleSettings.before_toggle_event, [payload, this]);
				}
				if (name === events.AFTER_TOGGLE && this.toggleSettings.after_toggle_event) {
					jQuery(document).trigger(this.toggleSettings.after_toggle_event, [payload, this]);
				}
			}
		},

		on: function(name, callback) {
			if (!this.events[name]) this.events[name] = [];
			this.events[name].push(callback);
		},

		off: function(name, callback) {
			if (!this.events[name]) return;
			var index = this.events[name].indexOf(callback);
			if (index > -1) this.events[name].splice(index, 1);
		},

		once: function(name, callback) {
			var self = this;
			var wrapper = function(payload) {
				self.off(name, wrapper);
				callback(payload);
			};
			this.on(name, wrapper);
		},

		/* -------------------------------------------------------------
		 * PUBLIC API
		 * ------------------------------------------------------------- */
		activate: function(index) {
			if (!this.canTransition(this.getSettings('constants.states.TRANSITIONING'))) return;
			if (this.state.activeIndex === index) return;
			
			this.transitionTo(this.getSettings('constants.states.TRANSITIONING'));
			
			var self = this;
			var proceed = function() {
				self._activateInternal(index).then(function() {
					self.transitionTo(self.getSettings('constants.states.READY'));
					self.broadcast(index);
					self.saveState(index);
				});
			};
			
			if (this.state.activeIndex !== -1) {
				this._deactivateInternal(this.state.activeIndex).then(proceed);
			} else {
				proceed();
			}
		},

		deactivate: function(index) {
			if (!this.canTransition(this.getSettings('constants.states.TRANSITIONING'))) return;
			if (this.state.activeIndex !== index) return;
			
			this.transitionTo(this.getSettings('constants.states.TRANSITIONING'));
			
			var self = this;
			this._deactivateInternal(index).then(function() {
				self.transitionTo(self.getSettings('constants.states.READY'));
			});
		},

		toggle: function(index) {
			if (!this.canTransition(this.getSettings('constants.states.TRANSITIONING'))) return;
			this.activate(index);
		},

		activateBySelector: function(selector) {
			if (!this.canTransition(this.getSettings('constants.states.TRANSITIONING'))) return;
			var indexToActivate = -1;
			for (var index in this.selectorCache) {
				if (this.selectorCache[index] === selector) {
					indexToActivate = parseInt(index, 10);
					break;
				}
			}
			if (indexToActivate !== -1) this.activate(indexToActivate);
		},

		activateByHash: function(hash) {
			if (!this.canTransition(this.getSettings('constants.states.TRANSITIONING'))) return;
			this.activateBySelector(hash);
		},

		activateByQuery: function(query) {
			if (!this.canTransition(this.getSettings('constants.states.TRANSITIONING'))) return;
			this.activateBySelector('#' + query);
		},

		refresh: function() {
			if (!this.canTransition(this.getSettings('constants.states.TRANSITIONING'))) return;
			this.emit(this.getSettings('constants.events').REFRESH, this);
		},

		destroy: function() {
			if (!this.canTransition(this.getSettings('constants.states.DESTROYED'))) return;
			this.onDestroy();
		},

		sync: function() {
			if (!this.canTransition(this.getSettings('constants.states.SYNCING'))) return;
			this.emit(this.getSettings('constants.events').SYNC, this);
		},

		/* -------------------------------------------------------------
		 * INTERNAL ENGINE
		 * ------------------------------------------------------------- */
		_activateInternal: function(index) {
			var self = this;
			var classes = this.getSettings('classes');
			var $btn = this.buttonCache[index] || this.elements.$toggleButtons.filter('[data-tv-index="' + index + '"]');
			if (!$btn.length) return Promise.resolve();
			
			$btn.addClass(classes.activeButton);
			$btn.attr('aria-selected', 'true');
			$btn.attr('tabindex', '0');
			
			var selector = this.selectorCache[index] || $btn.data('tv-target');
			var targets = this.targetCache[selector] || this.refreshTargets(selector);
			
			var promises = [];
			
			if (targets && targets.length) {
				for (var i = 0; i < targets.length; i++) {
					promises.push(new Promise(function(resolve) {
						var node = targets[i];
						node.classList.remove(classes.stateInactive);
						node.setAttribute('aria-hidden', 'false');
						
						if (self.transitionEffect === 'none') {
							node.classList.remove(classes.stateHidden);
							node.classList.add(classes.stateVisible);
							resolve();
						} else {
							node.classList.add('tv-transition-enabled', 'tv-anim-' + self.transitionEffect + '-enter');
							node.classList.remove(classes.stateHidden);
							node.classList.add(classes.stateVisible);
							
							requestAnimationFrame(function() {
								requestAnimationFrame(function() {
									node.classList.add('tv-anim-' + self.transitionEffect + '-enter-active');
								});
							});
							
							var transitionPromise = new Promise(function(res) {
								var onTransitionEnd = function(e) {
									if (e && e.target !== node) return;
									node.removeEventListener('transitionend', onTransitionEnd);
									res();
								};
								node.addEventListener('transitionend', onTransitionEnd);
							});
							
							var fallbackPromise = new Promise(function(res) {
								setTimeout(res, self.transitionDurationMs + 50);
							});
							
							Promise.race([transitionPromise, fallbackPromise]).then(function() {
								node.classList.remove('tv-transition-enabled', 'tv-anim-' + self.transitionEffect + '-enter', 'tv-anim-' + self.transitionEffect + '-enter-active');
								resolve();
							});
						}
					}));
				}
			} else {
				this.emitError('MISSING_TARGET', 'Cannot activate. Target not found.', selector);
			}
			
			this.state.activeIndex = index;
			this.state.activeSelector = selector;
			
			return Promise.all(promises);
		},

		_deactivateInternal: function(index) {
			var self = this;
			var classes = this.getSettings('classes');
			var isEditor = elementorFrontend.isEditMode();
			var isHelperEnabled = (this.toggleSettings.editor_helper === 'yes');
			
			var $btn = this.buttonCache[index] || this.elements.$toggleButtons.filter('[data-tv-index="' + index + '"]');
			if (!$btn.length) return Promise.resolve();
			
			$btn.removeClass(classes.activeButton);
			$btn.attr('aria-selected', 'false');
			$btn.attr('tabindex', '-1');
			
			var selector = this.selectorCache[index] || $btn.data('tv-target');
			var targets = this.targetCache[selector] || this.refreshTargets(selector);
			
			var promises = [];
			
			if (targets && targets.length) {
				for (var i = 0; i < targets.length; i++) {
					promises.push(new Promise(function(resolve) {
						var node = targets[i];
						node.classList.remove(classes.stateVisible);
						node.setAttribute('aria-hidden', 'true');
						
						if (self.transitionEffect === 'none') {
							if (isEditor && isHelperEnabled) {
								node.classList.add(classes.stateInactive);
								node.classList.remove(classes.stateHidden);
							} else {
								node.classList.add(classes.stateHidden);
								node.classList.remove(classes.stateInactive);
							}
							resolve();
						} else {
							node.classList.add('tv-transition-enabled', 'tv-anim-' + self.transitionEffect + '-exit');
							
							var transitionPromise = new Promise(function(res) {
								var onTransitionEnd = function(e) {
									if (e && e.target !== node) return;
									node.removeEventListener('transitionend', onTransitionEnd);
									res();
								};
								node.addEventListener('transitionend', onTransitionEnd);
							});
							
							var fallbackPromise = new Promise(function(res) {
								setTimeout(res, self.transitionDurationMs + 50);
							});
							
							Promise.race([transitionPromise, fallbackPromise]).then(function() {
								node.classList.remove('tv-transition-enabled', 'tv-anim-' + self.transitionEffect + '-exit');
								if (isEditor && isHelperEnabled) {
									node.classList.add(classes.stateInactive);
									node.classList.remove(classes.stateHidden);
								} else {
									node.classList.add(classes.stateHidden);
									node.classList.remove(classes.stateInactive);
								}
								resolve();
							});
						}
					}));
				}
			}
			
			this.state.activeIndex = -1;
			this.state.activeSelector = null;
			
			return Promise.all(promises);
		},
		
		saveState: function(index) {
			if (this.toggleSettings.remember_state === 'session_storage' && sessionStorage) {
				sessionStorage.setItem('tvToggle_' + this.$element.data('id'), index);
			} else if (this.toggleSettings.remember_state === 'local_storage' && localStorage) {
				localStorage.setItem('tvToggle_' + this.$element.data('id'), index);
			}
			
			if (this.toggleSettings.enable_url_hash === 'yes') {
				var hash = this.selectorCache[index];
				if (hash && hash.charAt(0) === '#') {
					if(history.replaceState) {
						history.replaceState(null, null, hash);
					} else {
						window.location.hash = hash;
					}
				}
			}
		},

		/* -------------------------------------------------------------
		 * SYNC GROUP FOUNDATION
		 * ------------------------------------------------------------- */
		refreshGroup: function() {
			if (!this.syncGroup || !TVToggleHandler.syncGroups[this.syncGroup]) return;
			var members = TVToggleHandler.syncGroups[this.syncGroup];
			this.groupMembers = members.filter(function(m) { return !m.isState('DESTROYED'); });
			TVToggleHandler.syncGroups[this.syncGroup] = this.groupMembers;
		},

		broadcast: function(index) {
			if (!this.syncGroup) return;
			if (!this.canTransition(this.getSettings('constants.states.SYNCING'))) return;
			
			this.transitionTo(this.getSettings('constants.states.SYNCING'));
			this.refreshGroup();
			
			var targetSelector = this.selectorCache[index];
			if (targetSelector) {
				for (var i = 0; i < this.groupMembers.length; i++) {
					var member = this.groupMembers[i];
					if (member !== this && member.receive) {
						member.receive(targetSelector);
					}
				}
			}
			
			this.transitionTo(this.getSettings('constants.states.READY'));
		},

		receive: function(targetSelector) {
			var indexToActivate = -1;
			for (var index in this.selectorCache) {
				if (this.selectorCache[index] === targetSelector) {
					indexToActivate = parseInt(index, 10);
					break;
				}
			}
			
			if (indexToActivate !== -1 && this.state.activeIndex !== indexToActivate) {
				this.activate(indexToActivate);
			}
		},

		/* -------------------------------------------------------------
		 * DOM & CACHE
		 * ------------------------------------------------------------- */
		bindEvents: function() {
			var self = this;
			
			this.elements.$toggleButtons.on('click.tvToggle', function(e) {
				e.preventDefault();
				var index = $(this).data('tv-index');
				self.toggle(index);
			});

			this.elements.$toggleButtons.on('keydown.tvToggle', function(e) {
				var key = e.which || e.keyCode;
				var k = self.getSettings('keyboard');
				var index = $(this).data('tv-index');
				var total = self.elements.$toggleButtons.length;
				var nextIndex = index;

				switch (key) {
					case k.ENTER:
					case k.SPACE:
						e.preventDefault();
						self.toggle(index);
						break;
					case k.RIGHT:
					case k.DOWN:
						e.preventDefault();
						nextIndex = (index + 1) % total;
						self.elements.$toggleButtons.eq(nextIndex).focus();
						break;
					case k.LEFT:
					case k.UP:
						e.preventDefault();
						nextIndex = (index - 1 + total) % total;
						self.elements.$toggleButtons.eq(nextIndex).focus();
						break;
					case k.HOME:
						e.preventDefault();
						self.elements.$toggleButtons.first().focus();
						break;
					case k.END:
						e.preventDefault();
						self.elements.$toggleButtons.last().focus();
						break;
				}
			});

			if (elementorFrontend.isEditMode()) {
				var namespace = '.tvToggleEditor_' + this.$element.data('id');
				$('body').on('click' + namespace, '.tv-state-is-inactive', function(e) {
					for (var index in self.selectorCache) {
						var sel = self.selectorCache[index];
						if (sel && $(this).is(sel)) {
							self.activate(parseInt(index, 10));
							break;
						}
					}
				});
			}

			this.updateIndicatorHandler = this.updateIndicator.bind(this);
			this.on(this.getSettings('constants.events').AFTER_TOGGLE, this.updateIndicatorHandler);
			this.on(this.getSettings('constants.events').INIT, this.updateIndicatorHandler);
			this.on(this.getSettings('constants.events').REFRESH, this.updateIndicatorHandler);
		},

		unbindEvents: function() {
			this.elements.$toggleButtons.off('.tvToggle');
			if (elementorFrontend.isEditMode()) {
				var namespace = '.tvToggleEditor_' + this.$element.data('id');
				$('body').off(namespace);
			}
			if (this.updateIndicatorHandler) {
				this.off(this.getSettings('constants.events').AFTER_TOGGLE, this.updateIndicatorHandler);
				this.off(this.getSettings('constants.events').INIT, this.updateIndicatorHandler);
				this.off(this.getSettings('constants.events').REFRESH, this.updateIndicatorHandler);
			}
		},

		updateIndicator: function() {
			var $indicator = this.$element.find('.tv-toggle__indicator');
			if (!$indicator.length) return;
			
			var indicatorNode = $indicator[0];
			var index = this.state.activeIndex;
			
			if (index === -1) {
				requestAnimationFrame(function() {
					indicatorNode.style.opacity = '0';
				});
				return;
			}
			
			var $btn = this.buttonCache[index] || this.elements.$toggleButtons.filter('[data-tv-index="' + index + '"]');
			if (!$btn.length) return;
			
			var list = this.$element.find('.tv-toggle__list')[0];
			var btn = $btn[0];
			
			// Compute bounds strictly once
			var listRect = list.getBoundingClientRect();
			var btnRect = btn.getBoundingClientRect();
			var isRTL = elementorFrontend.config.is_rtl;
			
			var offsetX = 0;
			var offsetY = btnRect.top - listRect.top;
			
			if (isRTL) {
				offsetX = listRect.right - btnRect.right;
				offsetX = -offsetX;
			} else {
				offsetX = btnRect.left - listRect.left;
			}
			
			// Batch visual mutations via requestAnimationFrame to prevent layout thrashing
			requestAnimationFrame(function() {
				indicatorNode.style.transform = 'translate3d(' + offsetX + 'px, ' + offsetY + 'px, 0)';
				indicatorNode.style.width = btnRect.width + 'px';
				indicatorNode.style.height = btnRect.height + 'px';
				indicatorNode.style.opacity = '1';
			});
		},

		cacheTargets: function() {
			this.buttonCache = {};
			this.targetCache = {};
			this.selectorCache = {};
			
			if (elementorFrontend.isEditMode()) {
				// Caches are built even in Editor Mode for helper
			}
			
			var self = this;
			var durationStr = this.transitionDurationStr;
			var easingStr = this.transitionEasingStr;
			
			this.elements.$toggleButtons.each(function(index, element) {
				var $button = $(element);
				var targetSelector = $button.data('tv-target');
				
				self.buttonCache[index] = $button;
				self.selectorCache[index] = targetSelector;
				
				if (targetSelector && !self.targetCache[targetSelector]) {
					var nodes = document.querySelectorAll(targetSelector);
					self.targetCache[targetSelector] = nodes;
					for (var i = 0; i < nodes.length; i++) {
						nodes[i].style.setProperty('--tv-toggle-duration', durationStr);
						nodes[i].style.setProperty('--tv-toggle-easing', easingStr);
					}
				}
			});
			
			this.validateTargets();
		},

		refreshTargets: function(selector) {
			if (!selector) {
				return null;
			}
			var nodes = document.querySelectorAll(selector);
			for (var i = 0; i < nodes.length; i++) {
				var node = nodes[i];
				node.style.setProperty('--tv-toggle-duration', this.transitionDurationStr);
				node.style.setProperty('--tv-toggle-easing', this.transitionEasingStr);
			}
			return nodes;
		},

		validateTargets: function() {
			var isEditor = elementorFrontend.isEditMode();
			var action = this.toggleSettings.missing_target_action;
			
			for (var index in this.selectorCache) {
				var selector = this.selectorCache[index];
				var nodes = this.targetCache[selector];
				if (!nodes || nodes.length === 0) {
					if (isEditor && this.toggleSettings.debug_mode === 'yes' && action === 'editor_warning') {
						console.warn('[TV Toggle] Warning: Target selector "' + selector + '" found no matching elements on this page.');
					}
					
					if (action === 'hide_toggle') {
						var $btn = this.buttonCache[index];
						if ($btn) $btn.hide();
					} else if (action === 'disable_toggle') {
						var $btn = this.buttonCache[index];
						if ($btn) {
							$btn.prop('disabled', true);
							$btn.css({'opacity': '0.5', 'pointer-events': 'none'});
						}
					}
				}
			}
		},

		hideAllTargets: function() {
			var classes = this.getSettings('classes');
			var isEditor = elementorFrontend.isEditMode();
			var isHelperEnabled = (this.toggleSettings.editor_helper === 'yes');
			
			this.elements.$toggleButtons.each(function(index, element) {
				var targetSelector = $(element).data('tv-target');
				if (targetSelector) {
					var targets = document.querySelectorAll(targetSelector);
					if (targets && targets.length) {
						for (var i = 0; i < targets.length; i++) {
							var node = targets[i];
							node.classList.remove(classes.stateVisible);
							node.setAttribute('role', 'tabpanel');
							if (isEditor && isHelperEnabled) {
								node.classList.add(classes.stateInactive);
								node.classList.remove(classes.stateHidden);
							} else {
								node.classList.add(classes.stateHidden);
								node.classList.remove(classes.stateInactive);
							}
							node.setAttribute('aria-hidden', 'true');
						}
					}
				}
			});
		},

		syncSettings: function() {
			this.toggleSettings = this.getElementSettings();
			
			this.transitionEffect = this.toggleSettings.animation_type || 'fade';
			
			var duration = this.toggleSettings.animation_duration || { size: 300, unit: 'ms' };
			var easing = this.toggleSettings.animation_easing || 'ease';
			this.transitionDurationStr = duration.size + (duration.unit || 'ms');
			this.transitionEasingStr = easing;
			this.transitionDurationMs = (duration.unit === 's' ? duration.size * 1000 : duration.size) || 300;
			
			if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
				this.transitionEffect = 'none';
				this.transitionDurationMs = 0;
			}
		}

	});

	$(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction('frontend/element_ready/tv-toggle.default', function($element) {
			elementorFrontend.elementsHandler.addHandler(TVToggleHandler, {
				$element: $element
			});
		});
	});

})(jQuery);
