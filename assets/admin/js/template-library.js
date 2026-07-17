jQuery(document).ready(function($) {
    var libraryGrid = $('#es-library-grid');
    var allTemplates = [];
    var filteredTemplates = [];
    var currentRenderIndex = 0;
    var batchSize = 20;
    var wpTemplate = wp.template('es-template-card');
    var currentImportTemplate = null;
    var currentElementorId = null;
    
    libraryGrid.after('<div id="es-library-sentinel" style="height: 1px;"></div>');
    var sentinel = document.getElementById('es-library-sentinel');

    $.ajax({
        url: esTemplateLibrary.api_url + '/manifest',
        method: 'GET',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', esTemplateLibrary.nonce);
        },
        success: function(response) {
            if (Array.isArray(response)) {
                allTemplates = response;
                filteredTemplates = [...allTemplates];
                libraryGrid.empty();
                renderNextBatch();
                setupObserver();
            } else {
                libraryGrid.html('<p>' + esTemplateLibrary.strings.error + '</p>');
            }
        },
        error: function() {
            libraryGrid.html('<p>' + esTemplateLibrary.strings.error + '</p>');
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
        var searchTerm = $('#es-search-input').val().toLowerCase();
        var filterType = $('#es-filter-type').val();

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

    $('#es-search-input, #es-filter-type').on('input change', applyFilters);

    $(document).on('click', '.es-btn-delete-template', function(e) {
        e.preventDefault();
        var slug = $(this).data('slug');
        var type = $(this).data('type');
        var $card = $(this).closest('.es-template-card');
        
        ElonixNotifier.confirm(esTemplateLibrary.strings.confirm_delete.replace(/\\n/g, '\n'), function() {
            $.ajax({
                url: esTemplateLibrary.dev_api_url + '/package/delete',
                method: 'DELETE',
                data: { slug: slug, type: type },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', esTemplateLibrary.nonce);
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
        $('.es-wizard-step').hide();
        $('#' + stepId).show();
    }

    $(document).on('click', '.es-btn-import', function() {
        var id = $(this).data('id');
        currentImportTemplate = allTemplates.find(t => t.id === id);
        
        if (!currentImportTemplate) return;

        $('.es-tpl-name').text(currentImportTemplate.title);
        
        $('#es-wizard-modal').show();
        showWizardStep('es-step-info');
    });

    $(document).on('click', '.es-wizard-next[data-next="deps"]', function() {
        var $depsList = $('#es-wizard-deps-list');
        $depsList.empty();
        
        var deps = currentImportTemplate.required_plugins || [];
        if (deps.length === 0) {
            $depsList.append('<li><span class="dashicons dashicons-yes-alt" style="color:green"></span> No special dependencies required.</li>');
        } else {
            deps.forEach(function(dep) {
                $depsList.append('<li><span class="dashicons dashicons-yes-alt" style="color:green"></span> Requires ' + dep.toUpperCase() + '</li>');
            });
        }
        
        showWizardStep('es-step-deps');
    });

    $(document).on('click', '.es-wizard-do-import', function() {
        showWizardStep('es-step-importing');

        $.ajax({
            url: esTemplateLibrary.api_url + '/import',
            method: 'POST',
            data: { id: currentImportTemplate.id },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', esTemplateLibrary.nonce);
            },
            success: function(response) {
                if (response.success) {
                    currentElementorId = response.template_id;
                    buildContextActions();
                    showWizardStep('es-step-assign');
                } else {
                    ElonixNotifier.error(response.message || 'Import failed');
                    $('#es-wizard-modal').hide();
                }
            },
            error: function(xhr) {
                var msg = 'Import failed';
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                
                var errorButtons = [ { text: 'Close', type: 'secondary', onClick: (m) => m.close() } ];
                
                var details = '';
                if (esTemplateLibrary.is_dev) {
                    details = 'Endpoint: /import\nMethod: POST\nStatus: ' + xhr.status + '\nMessage: ' + msg;
                }
                
                ElonixNotifier.modal({
                    title: 'Import Failed',
                    icon: 'error',
                    message: 'This template type is not supported or the package is invalid.',
                    details: details,
                    buttons: errorButtons
                });
                $('#es-wizard-modal').hide();
            }
        });
    });

    function buildContextActions() {
        var $actions = $('#es-wizard-actions-container');
        $actions.empty();
        var type = currentImportTemplate.type.toLowerCase();

        var assignLabel = 'Assign to ' + type.charAt(0).toUpperCase() + type.slice(1) + ' Builder';
        if(type === 'header') assignLabel = 'Set as Active Header';
        if(type === 'footer') assignLabel = 'Set as Active Footer';

        var builderTypes = ['header', 'footer', 'archive', 'single', 'search', 'popup'];
        
        if (builderTypes.indexOf(type) !== -1) {
            $actions.append('<button class="button button-primary button-hero es-action-assign" data-type="'+type+'">' + assignLabel + '</button>');
        }

        $actions.append('<button class="button button-secondary button-hero es-action-close">Back to Library</button>');
    }

    $(document).on('click', '.es-action-assign', function() {
        var $btn = $(this);
        var type = $btn.data('type');
        $btn.text('Checking conflicts...').prop('disabled', true);

        // First check for conflicts
        $.ajax({
            url: esTemplateLibrary.api_url + '/conflicts',
            method: 'POST',
            data: { type: type },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', esTemplateLibrary.nonce);
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
                    url: esTemplateLibrary.api_url + '/assign',
                    method: 'POST',
                    data: { elementor_id: currentElementorId, type: type },
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', esTemplateLibrary.nonce);
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

    $(document).on('click', '.es-action-close', function() {
        $('#es-wizard-modal').hide();
    });

    // Preview
    $(document).on('click', '.es-btn-preview', function() {
        var previewUrl = $(this).data('preview');
        if (previewUrl) {
            $('#es-preview-image').attr('src', previewUrl);
            $('#es-preview-modal').show().find('.es-modal-close').focus();
        }
    });

    $('.es-modal-close').on('click', function() {
        $(this).closest('.es-modal').hide();
    });
});

// KITS LOGIC
jQuery(document).ready(function($) {
    var kitGrid = $("#es-kits-grid");
    var allKits = [];
    var currentKit = null;
    var wpKitTemplate = wp.template("es-kit-card");

    // Tabs
    $(".es-library-tabs .nav-tab").on("click", function(e) {
        e.preventDefault();
        $(".es-library-tabs .nav-tab").removeClass("nav-tab-active");
        $(this).addClass("nav-tab-active");
        $(".es-tab-content").hide();
        $("#es-tab-" + $(this).data("target")).show();
    });

    // Fetch Kits
    $.ajax({
        url: esTemplateLibrary.api_url + "/kits",
        method: "GET",
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-WP-Nonce", esTemplateLibrary.nonce);
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

    $("#es-search-kits-input").on("input", function() {
        var term = $(this).val().toLowerCase();
        $("#es-kits-grid .es-template-card").each(function() {
            var title = $(this).data("title");
            $(this).toggle(title.indexOf(term) > -1);
        });
    });

    // Wizard
    function showKitWizardStep(stepId) {
        $("#es-kit-wizard-modal .es-wizard-step").hide();
        $("#" + stepId).show();
    }

    $(document).on("click", ".es-btn-import-kit", function() {
        var slug = $(this).data("slug");
        currentKit = allKits.find(k => k.slug === slug);
        if (!currentKit) return;
        
        $(".es-kit-name").text(currentKit.title);
        $("#es-kit-wizard-modal").show();
        showKitWizardStep("es-kit-step-info");
    });

    $(document).on("click", ".es-kit-wizard-next[data-next=\"deps\"]", function() {
        var $deps = $("#es-kit-wizard-deps-list");
        $deps.empty();
        var reqs = currentKit.required_plugins || [];
        if (reqs.length === 0) {
            $deps.append("<li><span class=\"dashicons dashicons-yes-alt\" style=\"color:green\"></span> No special dependencies</li>");
        } else {
            reqs.forEach(dep => {
                $deps.append("<li><span class=\"dashicons dashicons-yes-alt\" style=\"color:green\"></span> " + dep.toUpperCase() + "</li>");
            });
        }
        showKitWizardStep("es-kit-step-deps");
    });

    $(document).on("click", ".es-kit-wizard-next[data-next=\"components\"]", function() {
        var $list = $("#es-kit-components-list");
        $list.empty();

        if (currentKit.global_styles) {
            $list.append("<label style=\"display:block;margin-bottom:8px;\"><input type=\"checkbox\" class=\"es-kit-comp-chk\" value=\"global_styles\" checked> Global Styles (Colors, Typography)</label>");
        }

        if (currentKit.templates) {
            Object.keys(currentKit.templates).forEach(function(key) {
                var label = key.charAt(0).toUpperCase() + key.slice(1);
                $list.append("<label style=\"display:block;margin-bottom:8px;\"><input type=\"checkbox\" class=\"es-kit-comp-chk\" value=\""+key+"\" checked> " + label + " Template</label>");
            });
        }

        showKitWizardStep("es-kit-step-components");
    });

    $(document).on("click", ".es-kit-do-import", function() {
        var selected = [];
        $(".es-kit-comp-chk:checked").each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            ElonixNotifier.warning("Select at least one component.");
            return;
        }

        showKitWizardStep("es-kit-step-progress");
        var $log = $("#es-kit-progress-log");
        $log.empty();

        importNextKitComponent(selected, 0, $log);
    });

    function importNextKitComponent(components, index, $log) {
        if (index >= components.length) {
            $("#es-kit-step-progress .spinner").removeClass("is-active").hide();
            setTimeout(function() { showKitWizardStep("es-kit-step-completed"); }, 1000);
            return;
        }

        var comp = components[index];
        $log.append("<li>Importing " + comp + "... <span class=\"status\" style=\"color:#888\">In progress</span></li>");
        
        var apiRoute = (comp === "global_styles") ? "/kits/styles" : "/kits/import";
        var payload = { slug: currentKit.slug };
        if (comp !== "global_styles") payload.component = comp;

        $.ajax({
            url: esTemplateLibrary.api_url + apiRoute,
            method: "POST",
            data: payload,
            beforeSend: function(xhr) {
                xhr.setRequestHeader("X-WP-Nonce", esTemplateLibrary.nonce);
            },
            success: function(res) {
                $log.find("li:last-child .status").text("✔").css("color", "green");
                
                // If it is a header/footer/archive, automatically try to assign it? 
                // The prompt says "automatically offer assignment" or just assign it. 
                // Let us auto-assign builder items to streamline kit import.
                if (res.success && res.elementor_id && ["header", "footer", "archive", "single", "popup"].includes(comp)) {
                    $log.append("<li>Assigning " + comp + " to builder... <span class=\"status\" style=\"color:#888\">In progress</span></li>");
                    $.ajax({
                        url: esTemplateLibrary.api_url + "/assign",
                        method: "POST",
                        data: { elementor_id: res.elementor_id, type: comp },
                        beforeSend: function(xhr) { xhr.setRequestHeader("X-WP-Nonce", esTemplateLibrary.nonce); },
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

    $(document).on("click", ".es-wizard-close, .es-action-close-kit", function() {
        $("#es-kit-wizard-modal").hide();
    });
});

