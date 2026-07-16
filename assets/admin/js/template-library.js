jQuery(document).ready(function($) {
    var libraryGrid = $('#tv-library-grid');
    var allTemplates = [];
    var filteredTemplates = [];
    var currentRenderIndex = 0;
    var batchSize = 20;
    var wpTemplate = wp.template('tv-template-card');
    var currentImportTemplate = null;
    var currentElementorId = null;
    
    libraryGrid.after('<div id="tv-library-sentinel" style="height: 1px;"></div>');
    var sentinel = document.getElementById('tv-library-sentinel');

    $.ajax({
        url: tvTemplateLibrary.api_url + '/manifest',
        method: 'GET',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', tvTemplateLibrary.nonce);
        },
        success: function(response) {
            if (Array.isArray(response)) {
                allTemplates = response;
                filteredTemplates = [...allTemplates];
                libraryGrid.empty();
                renderNextBatch();
                setupObserver();
            } else {
                libraryGrid.html('<p>' + tvTemplateLibrary.strings.error + '</p>');
            }
        },
        error: function() {
            libraryGrid.html('<p>' + tvTemplateLibrary.strings.error + '</p>');
        }
    });

    function renderNextBatch() {
        if (currentRenderIndex === 0 && filteredTemplates.length === 0) {
            libraryGrid.html('<p>No templates found.</p>');
            return;
        }
        if (currentRenderIndex >= filteredTemplates.length) return;

        var fragment = document.createDocumentFragment();
        var end = Math.min(currentRenderIndex + batchSize, filteredTemplates.length);
        
        for (var i = currentRenderIndex; i < end; i++) {
            var html = wpTemplate(filteredTemplates[i]);
            var div = document.createElement('div');
            div.innerHTML = $.trim(html);
            fragment.appendChild(div.firstChild);
        }

        libraryGrid.append(fragment);
        currentRenderIndex = end;
    }

    function setupObserver() {
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                if (entries[0].isIntersecting) {
                    renderNextBatch();
                }
            }, { rootMargin: '200px' });
            observer.observe(sentinel);
        }
    }

    function applyFilters() {
        var searchTerm = $('#tv-search-input').val().toLowerCase();
        var filterType = $('#tv-filter-type').val();

        filteredTemplates = allTemplates.filter(function(tpl) {
            var matchesType = (filterType === 'all' || tpl.type === filterType);
            var matchesSearch = true;
            if (searchTerm !== '') {
                var searchableText = [
                    tpl.title || '', tpl.slug || '', tpl.description || '',
                    tpl.author || '', tpl.type || '',
                    (tpl.tags || []).join(' '), (tpl.category || []).join(' ')
                ].join(' ').toLowerCase();
                matchesSearch = searchableText.indexOf(searchTerm) !== -1;
            }
            return matchesType && matchesSearch;
        });

        currentRenderIndex = 0;
        libraryGrid.empty();
        renderNextBatch();
    }

    $('#tv-search-input, #tv-filter-type').on('input change', applyFilters);

    $(document).on('click', '.tv-btn-delete-template', function(e) {
        e.preventDefault();
        var slug = $(this).data('slug');
        var type = $(this).data('type');
        var $card = $(this).closest('.tv-template-card');
        
        ElonixNotifier.confirm(tvTemplateLibrary.strings.confirm_delete.replace(/\\n/g, '\n'), function() {
            $.ajax({
                url: tvTemplateLibrary.dev_api_url + '/package/delete',
                method: 'DELETE',
                data: { slug: slug, type: type },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', tvTemplateLibrary.nonce);
                    $card.css('opacity', '0.5');
                },
                success: function(response) {
                    if (response.success) {
                        $card.fadeOut(300, function() { $(this).remove(); });
                    } else {
                        $card.css('opacity', '1');
                        ElonixNotifier.error(response.message || 'Delete failed.');
                    }
                },
                error: function(err) {
                    $card.css('opacity', '1');
                    var msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Error deleting template.';
                    ElonixNotifier.error(msg);
                }
            });
        });
    });

    // WIZARD LOGIC
    function showWizardStep(stepId) {
        $('.tv-wizard-step').hide();
        $('#' + stepId).show();
    }

    $(document).on('click', '.tv-btn-import', function() {
        var id = $(this).data('id');
        currentImportTemplate = allTemplates.find(t => t.id === id);
        
        if (!currentImportTemplate) return;

        $('.tv-tpl-name').text(currentImportTemplate.title);
        
        $('#tv-wizard-modal').show();
        showWizardStep('tv-step-info');
    });

    $(document).on('click', '.tv-wizard-next[data-next="deps"]', function() {
        var $depsList = $('#tv-wizard-deps-list');
        $depsList.empty();
        
        var deps = currentImportTemplate.required_plugins || [];
        if (deps.length === 0) {
            $depsList.append('<li><span class="dashicons dashicons-yes-alt" style="color:green"></span> No special dependencies required.</li>');
        } else {
            deps.forEach(function(dep) {
                $depsList.append('<li><span class="dashicons dashicons-yes-alt" style="color:green"></span> Requires ' + dep.toUpperCase() + '</li>');
            });
        }
        
        showWizardStep('tv-step-deps');
    });

    $(document).on('click', '.tv-wizard-do-import', function() {
        showWizardStep('tv-step-importing');

        $.ajax({
            url: tvTemplateLibrary.api_url + '/import',
            method: 'POST',
            data: { id: currentImportTemplate.id },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', tvTemplateLibrary.nonce);
            },
            success: function(response) {
                if (response.success) {
                    currentElementorId = response.template_id;
                    buildContextActions();
                    showWizardStep('tv-step-assign');
                } else {
                    ElonixNotifier.error(response.message || 'Import failed');
                    $('#tv-wizard-modal').hide();
                }
            },
            error: function(xhr) {
                var msg = 'Import failed';
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                
                var errorButtons = [ { text: 'Close', type: 'secondary', onClick: (m) => m.close() } ];
                
                var details = '';
                if (tvTemplateLibrary.is_dev) {
                    details = 'Endpoint: /import\nMethod: POST\nStatus: ' + xhr.status + '\nMessage: ' + msg;
                }
                
                ElonixNotifier.modal({
                    title: 'Import Failed',
                    icon: 'error',
                    message: 'This template type is not supported or the package is invalid.',
                    details: details,
                    buttons: errorButtons
                });
                $('#tv-wizard-modal').hide();
            }
        });
    });

    function buildContextActions() {
        var $actions = $('#tv-wizard-actions-container');
        $actions.empty();
        var type = currentImportTemplate.type.toLowerCase();

        var assignLabel = 'Assign to ' + type.charAt(0).toUpperCase() + type.slice(1) + ' Builder';
        if(type === 'header') assignLabel = 'Set as Active Header';
        if(type === 'footer') assignLabel = 'Set as Active Footer';

        var builderTypes = ['header', 'footer', 'archive', 'single', 'search', 'popup'];
        
        if (builderTypes.indexOf(type) !== -1) {
            $actions.append('<button class="button button-primary button-hero tv-action-assign" data-type="'+type+'">' + assignLabel + '</button>');
        }

        $actions.append('<button class="button button-secondary button-hero tv-action-close">Back to Library</button>');
    }

    $(document).on('click', '.tv-action-assign', function() {
        var $btn = $(this);
        var type = $btn.data('type');
        $btn.text('Checking conflicts...').prop('disabled', true);

        // First check for conflicts
        $.ajax({
            url: tvTemplateLibrary.api_url + '/conflicts',
            method: 'POST',
            data: { type: type },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', tvTemplateLibrary.nonce);
            },
            success: function(response) {
                if (response.success && response.conflicts && response.conflicts.length > 0) {
                    ElonixNotifier.confirm('Warning: You already have an active ' + type + ' assigned to the entire site. Do you want to create a new one anyway and overwrite the active status?', function() {
                        proceedWithAssignment($btn, type);
                    });
                } else {
                    proceedWithAssignment($btn, type);
                }
            },
            error: function() {
                ElonixNotifier.warning('Conflict check failed. Proceeding safely.');
                proceedWithAssignment($btn, type);
            }
        });
        
        function proceedWithAssignment($btn, type) {
            $btn.text('Assigning...');
                $.ajax({
                    url: tvTemplateLibrary.api_url + '/assign',
                    method: 'POST',
                    data: { elementor_id: currentElementorId, type: type },
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', tvTemplateLibrary.nonce);
                    },
                    success: function(assignRes) {
                        if (assignRes.success && assignRes.edit_url) {
                            ElonixNotifier.success('Template imported successfully.');
                            window.location.href = assignRes.edit_url;
                        } else {
                            ElonixNotifier.error('Assignment failed.');
                            $btn.text('Failed');
                        }
                    },
                    error: function() {
                        ElonixNotifier.error('Assignment failed.');
                        $btn.text('Failed');
                    }
                });
        }
    });

    $(document).on('click', '.tv-action-close', function() {
        $('#tv-wizard-modal').hide();
    });

    // Preview
    $(document).on('click', '.tv-btn-preview', function() {
        var previewUrl = $(this).data('preview');
        if (previewUrl) {
            $('#tv-preview-image').attr('src', previewUrl);
            $('#tv-preview-modal').show().find('.tv-modal-close').focus();
        }
    });

    $('.tv-modal-close').on('click', function() {
        $(this).closest('.tv-modal').hide();
    });
});

// KITS LOGIC
jQuery(document).ready(function($) {
    var kitGrid = $("#tv-kits-grid");
    var allKits = [];
    var currentKit = null;
    var wpKitTemplate = wp.template("tv-kit-card");

    // Tabs
    $(".tv-library-tabs .nav-tab").on("click", function(e) {
        e.preventDefault();
        $(".tv-library-tabs .nav-tab").removeClass("nav-tab-active");
        $(this).addClass("nav-tab-active");
        $(".tv-tab-content").hide();
        $("#tv-tab-" + $(this).data("target")).show();
    });

    // Fetch Kits
    $.ajax({
        url: tvTemplateLibrary.api_url + "/kits",
        method: "GET",
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-WP-Nonce", tvTemplateLibrary.nonce);
        },
        success: function(response) {
            if (Array.isArray(response)) {
                allKits = response;
                renderKits();
            } else {
                kitGrid.html("<p>Error loading kits.</p>");
            }
        },
        error: function() {
            kitGrid.html("<p>Error loading kits.</p>");
        }
    });

    function renderKits() {
        kitGrid.empty();
        if (allKits.length === 0) {
            kitGrid.html("<p>No kits found.</p>");
            return;
        }
        allKits.forEach(function(kit) {
            kitGrid.append(wpKitTemplate(kit));
        });
    }

    $("#tv-search-kits-input").on("input", function() {
        var term = $(this).val().toLowerCase();
        $("#tv-kits-grid .tv-template-card").each(function() {
            var title = $(this).data("title");
            $(this).toggle(title.indexOf(term) > -1);
        });
    });

    // Wizard
    function showKitWizardStep(stepId) {
        $("#tv-kit-wizard-modal .tv-wizard-step").hide();
        $("#" + stepId).show();
    }

    $(document).on("click", ".tv-btn-import-kit", function() {
        var slug = $(this).data("slug");
        currentKit = allKits.find(k => k.slug === slug);
        if (!currentKit) return;
        
        $(".tv-kit-name").text(currentKit.title);
        $("#tv-kit-wizard-modal").show();
        showKitWizardStep("tv-kit-step-info");
    });

    $(document).on("click", ".tv-kit-wizard-next[data-next=\"deps\"]", function() {
        var $deps = $("#tv-kit-wizard-deps-list");
        $deps.empty();
        var reqs = currentKit.required_plugins || [];
        if (reqs.length === 0) {
            $deps.append("<li><span class=\"dashicons dashicons-yes-alt\" style=\"color:green\"></span> No special dependencies</li>");
        } else {
            reqs.forEach(dep => {
                $deps.append("<li><span class=\"dashicons dashicons-yes-alt\" style=\"color:green\"></span> " + dep.toUpperCase() + "</li>");
            });
        }
        showKitWizardStep("tv-kit-step-deps");
    });

    $(document).on("click", ".tv-kit-wizard-next[data-next=\"components\"]", function() {
        var $list = $("#tv-kit-components-list");
        $list.empty();

        if (currentKit.global_styles) {
            $list.append("<label style=\"display:block;margin-bottom:8px;\"><input type=\"checkbox\" class=\"tv-kit-comp-chk\" value=\"global_styles\" checked> Global Styles (Colors, Typography)</label>");
        }

        if (currentKit.templates) {
            Object.keys(currentKit.templates).forEach(function(key) {
                var label = key.charAt(0).toUpperCase() + key.slice(1);
                $list.append("<label style=\"display:block;margin-bottom:8px;\"><input type=\"checkbox\" class=\"tv-kit-comp-chk\" value=\""+key+"\" checked> " + label + " Template</label>");
            });
        }

        showKitWizardStep("tv-kit-step-components");
    });

    $(document).on("click", ".tv-kit-do-import", function() {
        var selected = [];
        $(".tv-kit-comp-chk:checked").each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            ElonixNotifier.warning("Select at least one component.");
            return;
        }

        showKitWizardStep("tv-kit-step-progress");
        var $log = $("#tv-kit-progress-log");
        $log.empty();

        importNextKitComponent(selected, 0, $log);
    });

    function importNextKitComponent(components, index, $log) {
        if (index >= components.length) {
            $("#tv-kit-step-progress .spinner").removeClass("is-active").hide();
            setTimeout(function() { showKitWizardStep("tv-kit-step-completed"); }, 1000);
            return;
        }

        var comp = components[index];
        $log.append("<li>Importing " + comp + "... <span class=\"status\" style=\"color:#888\">In progress</span></li>");
        
        var apiRoute = (comp === "global_styles") ? "/kits/styles" : "/kits/import";
        var payload = { slug: currentKit.slug };
        if (comp !== "global_styles") payload.component = comp;

        $.ajax({
            url: tvTemplateLibrary.api_url + apiRoute,
            method: "POST",
            data: payload,
            beforeSend: function(xhr) {
                xhr.setRequestHeader("X-WP-Nonce", tvTemplateLibrary.nonce);
            },
            success: function(res) {
                $log.find("li:last-child .status").text("✔").css("color", "green");
                
                // If it is a header/footer/archive, automatically try to assign it? 
                // The prompt says "automatically offer assignment" or just assign it. 
                // Let us auto-assign builder items to streamline kit import.
                if (res.success && res.elementor_id && ["header", "footer", "archive", "single", "popup"].includes(comp)) {
                    $log.append("<li>Assigning " + comp + " to builder... <span class=\"status\" style=\"color:#888\">In progress</span></li>");
                    $.ajax({
                        url: tvTemplateLibrary.api_url + "/assign",
                        method: "POST",
                        data: { elementor_id: res.elementor_id, type: comp },
                        beforeSend: function(xhr) { xhr.setRequestHeader("X-WP-Nonce", tvTemplateLibrary.nonce); },
                        success: function(assignRes) {
                            $log.find("li:last-child .status").text("✔").css("color", "green");
                            importNextKitComponent(components, index + 1, $log);
                        },
                        error: function() {
                            $log.find("li:last-child .status").text("⚠ Failed").css("color", "orange");
                            importNextKitComponent(components, index + 1, $log);
                        }
                    });
                } else {
                    importNextKitComponent(components, index + 1, $log);
                }
            },
            error: function() {
                $log.find("li:last-child .status").text("✖ Failed").css("color", "red");
                importNextKitComponent(components, index + 1, $log);
            }
        });
    }

    $(document).on("click", ".tv-wizard-close, .tv-action-close-kit", function() {
        $("#tv-kit-wizard-modal").hide();
    });
});

