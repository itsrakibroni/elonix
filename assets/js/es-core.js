(function($, window) {
    "use strict";

    window.Elonix = window.Elonix || {};

    var Cache = {
        data: {},
        set: function(key, value) {
            this.data[key] = value;
        },
        get: function(key) {
            return this.data[key];
        },
        has: function(key) {
            return this.data.hasOwnProperty(key);
        },
        clear: function() {
            this.data = {};
        }
    };

    var SettingsExtractor = {
        isEditMode: function() {
            return typeof elementorFrontend !== 'undefined' && elementorFrontend.isEditMode();
        },
        /**
         * Merge primary settings with optional DOM source settings.
         *
         * @param {Object|jQuery} settings - Pre-resolved settings object (from handler.getElementSettings()
         *                                   or $element.data('settings')). For backward compatibility,
         *                                   a jQuery element is accepted and read via .data('settings').
         * @param {jQuery}        [$source] - Optional DOM element whose data-settings are merged in.
         * @returns {Object} Merged settings.
         */
        getSettings: function(settings, $source) {
            // Backward compatibility: if a jQuery object is passed, read its data-settings
            if (settings && typeof settings.jquery !== 'undefined') {
                settings = settings.data('settings') || {};
            }

            settings = settings || {};

            if ($source && $source.length) {
                var sourceSettings = $source.data('settings') || {};

                // Source fills gaps; primary settings always win on conflicts
                settings = $.extend({}, sourceSettings, settings);
            }

            return settings;
        }
    };

    var ResponsiveManager = {
        getActiveBreakpoints: function() {
            if (Cache.has('activeBreakpoints')) {
                return Cache.get('activeBreakpoints');
            }
            
            var breakpoints = [];
            
            if (typeof elementorFrontend !== 'undefined') {
                // A & B: Official API
                if (elementorFrontend.breakpoints && typeof elementorFrontend.breakpoints.getActiveBreakpointsList === 'function' && typeof elementorFrontend.breakpoints.getBreakpointValues === 'function') {
                    try {
                        var names = elementorFrontend.breakpoints.getActiveBreakpointsList();
                        var values = elementorFrontend.breakpoints.getBreakpointValues();
                        if (names && names.length > 0 && values && values.length === names.length) {
                            for (var i = 0; i < names.length; i++) {
                                breakpoints.push({
                                    name: names[i],
                                    value: values[i]
                                });
                            }
                        }
                    } catch (e) {}
                }
                
                // C: Config object fallback
                if (breakpoints.length === 0 && elementorFrontend.config && elementorFrontend.config.responsive && elementorFrontend.config.responsive.activeBreakpoints) {
                    var activeBps = elementorFrontend.config.responsive.activeBreakpoints;
                    if (activeBps && Object.keys(activeBps).length > 0) {
                        for (var key in activeBps) {
                            breakpoints.push({
                                name: key,
                                value: activeBps[key].value
                            });
                        }
                    }
                }
            }
            
            // D: Safe hardcoded defaults
            if (breakpoints.length === 0) {
                breakpoints = [
                    { name: 'mobile', value: 767 },
                    { name: 'tablet', value: 1024 }
                ];
            }
            
            // Sort ascending by pixel value (Mobile -> Tablet -> Laptop)
            breakpoints.sort(function(a, b) {
                return a.value - b.value;
            });
            
            Cache.set('activeBreakpoints', breakpoints);
            return breakpoints;
        },

        resolve: function(rawSettings, mapping) {
            var breakpoints = this.getActiveBreakpoints();
            var resolved = {};

            for (var swiperKey in mapping) {
                var elementorKey = mapping[swiperKey];
                
                // The root Elementor setting is Desktop
                var desktopValue = rawSettings[elementorKey] !== undefined && rawSettings[elementorKey] !== '' ? rawSettings[elementorKey] : null;
                
                resolved[swiperKey] = {
                    desktop: desktopValue,
                    breakpoints: {}
                };

                // Downward Inheritance (Desktop -> Tablet -> Mobile)
                var currentValue = desktopValue;
                var reversedBreakpoints = breakpoints.slice().reverse();
                var inheritanceMap = {};
                
                reversedBreakpoints.forEach(function(bp) {
                    var bpKey = elementorKey + '_' + bp.name;
                    // For size objects like { size: 30, unit: 'px' }
                    if (rawSettings[bpKey] !== undefined && rawSettings[bpKey] !== '') {
                        if (typeof rawSettings[bpKey] === 'object' && rawSettings[bpKey].size !== undefined) {
                            if (rawSettings[bpKey].size !== '') {
                                currentValue = rawSettings[bpKey].size;
                            }
                        } else {
                            currentValue = rawSettings[bpKey];
                        }
                    }
                    inheritanceMap[bp.name] = currentValue;
                });
                
                // Override desktop value if it was a size object
                if (desktopValue !== null && typeof desktopValue === 'object' && desktopValue.size !== undefined) {
                    desktopValue = desktopValue.size;
                    resolved[swiperKey].desktop = desktopValue;
                }
                
                resolved[swiperKey].inheritanceMap = inheritanceMap;
            }

            return resolved;
        }
    };

    var Pipeline = {
        resolve: function($scope, mapping) {
            var rawSettings = SettingsExtractor.getSettings($scope);
            return ResponsiveManager.resolve(rawSettings, mapping);
        }
    };

    var InstanceRegistry = {
        instances: new Map(),
        register: function(element, instance) {
            this.instances.set(element, instance);
        },
        get: function(element) {
            return this.instances.get(element);
        },
        has: function(element) {
            return this.instances.has(element);
        },
        destroy: function(element) {
            if (this.has(element)) {
                var instance = this.get(element);
                if (instance && typeof instance.destroy === 'function') {
                    instance.destroy(true, true);
                }
                this.instances.delete(element);
            }
        },
        clear: function() {
            this.instances.forEach(function(instance) {
                if (instance && typeof instance.destroy === 'function') {
                    instance.destroy(true, true);
                }
            });
            this.instances.clear();
        }
    };

    var Utils = {
        parseNumber: function(val, fallback) {
            var parsed = parseFloat(val);
            return isNaN(parsed) ? fallback : parsed;
        },
        parseBoolean: function(val) {
            return val === 'yes' || val === true || val === 'true';
        }
    };

    Elonix.Core = {
        Cache: Cache,
        SettingsExtractor: SettingsExtractor,
        ResponsiveManager: ResponsiveManager,
        Pipeline: Pipeline,
        InstanceRegistry: InstanceRegistry,
        Utils: Utils
    };

})(jQuery, window);

(function($, window) {
    "use strict";

    window.Elonix = window.Elonix || {};
    window.Elonix.Builders = window.Elonix.Builders || {};

    Elonix.Builders.Swiper = {
        build: function(resolvedSettings, extraConfig) {
            var config = {
                breakpoints: {},
                observer: true,
                observeParents: true,
                resizeObserver: true
            };
            
            if (extraConfig) {
                $.extend(config, extraConfig);
            }
            
            var activeBreakpoints = Elonix.Core.ResponsiveManager.getActiveBreakpoints();
            
            for (var swiperKey in resolvedSettings) {
                var param = resolvedSettings[swiperKey];
                
                // Base Mobile First value is the lowest breakpoint (e.g. mobile)
                var mobileBreakpointName = activeBreakpoints[0] ? activeBreakpoints[0].name : 'mobile';
                var baseValue = param.inheritanceMap[mobileBreakpointName];
                
                // Enterprise Fallback: mobile -> tablet -> desktop
                if (baseValue === null || baseValue === undefined || baseValue === '') {
                    for (var i = 1; i < activeBreakpoints.length; i++) {
                        var nextBpName = activeBreakpoints[i].name;
                        var nextBpValue = param.inheritanceMap[nextBpName];
                        if (nextBpValue !== null && nextBpValue !== undefined && nextBpValue !== '') {
                            baseValue = nextBpValue;
                            break;
                        }
                    }
                }
                
                if (baseValue === null || baseValue === undefined || baseValue === '') {
                    baseValue = param.desktop;
                }
                
                if (baseValue !== null && baseValue !== undefined && baseValue !== '') {
                    if (swiperKey === 'slidesPerView' || swiperKey === 'slidesPerGroup') {
                        config[swiperKey] = Elonix.Core.Utils.parseNumber(baseValue, 1);
                    } else if (swiperKey === 'spaceBetween') {
                        config[swiperKey] = Elonix.Core.Utils.parseNumber(baseValue, 0);
                    } else {
                        config[swiperKey] = baseValue;
                    }
                }

                // Map upward breakpoints
                activeBreakpoints.forEach(function(bp, index) {
                    var bpPixelValue = bp.value + 1;
                    
                    if (!config.breakpoints[bpPixelValue]) {
                        config.breakpoints[bpPixelValue] = {};
                    }
                    
                    var targetValue;
                    if (index + 1 < activeBreakpoints.length) {
                        targetValue = param.inheritanceMap[activeBreakpoints[index + 1].name];
                    } else {
                        targetValue = param.desktop;
                    }
                    
                    if (targetValue === null || targetValue === undefined || targetValue === '') {
                        for (var j = index + 2; j < activeBreakpoints.length; j++) {
                            var nextTargetBp = activeBreakpoints[j].name;
                            if (param.inheritanceMap[nextTargetBp] !== null && param.inheritanceMap[nextTargetBp] !== undefined && param.inheritanceMap[nextTargetBp] !== '') {
                                targetValue = param.inheritanceMap[nextTargetBp];
                                break;
                            }
                        }
                    }
                    
                    if (targetValue === null || targetValue === undefined || targetValue === '') {
                        targetValue = param.desktop;
                    }

                    if (targetValue !== null && targetValue !== undefined && targetValue !== '') {
                        if (swiperKey === 'slidesPerView' || swiperKey === 'slidesPerGroup') {
                            config.breakpoints[bpPixelValue][swiperKey] = Elonix.Core.Utils.parseNumber(targetValue, 1);
                        } else if (swiperKey === 'spaceBetween') {
                            config.breakpoints[bpPixelValue][swiperKey] = Elonix.Core.Utils.parseNumber(targetValue, 0);
                        } else {
                            config.breakpoints[bpPixelValue][swiperKey] = targetValue;
                        }
                    }
                });
            }
            
            return config;
        }
    };

})(jQuery, window);
