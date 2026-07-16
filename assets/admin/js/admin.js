/**
 * Elonix – Toolkit for Elementor - Admin Interface Script
 */
(function($) {
    'use strict';

    // Global variables
    const opts = typeof elonixAdminOpts !== 'undefined' ? elonixAdminOpts : {
        ajax_url: '',
        widgets_nonce: '',
        modules_nonce: '',
        i18n: {
            copied: 'Copied!',
            copy_error: 'Error copying, please copy manually.',
            update_failed: 'Failed to update status. Please try again.',
            saving: 'Saving...',
            success: 'Saved successfully!'
        }
    };

    $(document).ready(function() {
        // Initialize components
        initLiveSearch();
        initStatusFilters();
        initAjaxToggles();
        initBulkActions();
        initSystemInfoCopy();
        initTemplateLibrary();
        updateCountBadges();
        
        $(document).on('click', '.tv-action-trash, .tv-action-confirm', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            var msg = $(this).data('confirm') || 'Are you sure you want to perform this action?';
            ElonixNotifier.confirm(msg, function() {
                window.location.href = href;
            });
        });
    });

    /**
     * Show premium toast notification
     */
    function showToast(message, type = 'success') {
        let $container = $('.tv-toast-container');
        if (!$container.length) {
            $container = $('<div class="tv-toast-container"></div>').appendTo('body');
        }

        const iconClass = type === 'success' ? 'dashicons-yes' : 'dashicons-warning';
        const $toast = $(`
            <div class="tv-toast tv-toast-${type}">
                <span class="dashicons ${iconClass}"></span>
                <span class="tv-toast-message">${message}</span>
            </div>
        `);

        $container.append($toast);

        // Animate in
        setTimeout(() => {
            $toast.addClass('show');
        }, 10);

        // Remove after 3 seconds
        setTimeout(() => {
            $toast.removeClass('show').addClass('hide');
            setTimeout(() => {
                $toast.remove();
            }, 300);
        }, 3000);
    }

    /**
     * Live Search Implementation for Cards
     */
    function initLiveSearch() {
        const $searchInput = $('#tv-search-input');
        if (!$searchInput.length) return;

        $searchInput.on('input', function() {
            filterCards();
        });
    }

    /**
     * Status Filter Tabs Implementation
     */
    function initStatusFilters() {
        const $filterTabs = $('.tv-filter-tab');
        if (!$filterTabs.length) return;

        $filterTabs.on('click', function(e) {
            // Ignore if on templates page
            if ($('#tv-tpl-search').length) return;

            e.preventDefault();
            $filterTabs.removeClass('active');
            $(this).addClass('active');
            filterCards();
        });
    }

    /**
     * Update filter count badges dynamically based on current DOM state
     */
    function updateCountBadges() {
        const total = $('.tv-card-item').length;
        if (total === 0) return;

        const enabled = $('.tv-card-item .tv-toggle-input:checked').length;
        const disabled = total - enabled;

        $('.count-enabled').text(enabled);
        $('.count-disabled').text(disabled);
    }

    /**
     * Filter Cards based on search query and status tab
     */
    function filterCards() {
        const query = $('#tv-search-input').val() ? $('#tv-search-input').val().toLowerCase().trim() : '';
        const activeTab = $('.tv-filter-tab.active').data('filter') || 'all'; // all, enabled, disabled
        const $cards = $('.tv-card-item');
        let visibleCount = 0;

        $cards.each(function() {
            const $card = $(this);
            const title = $card.find('.tv-card-title').text().toLowerCase();
            const desc = $card.find('.tv-card-desc').text().toLowerCase();
            const keywordsAttr = $card.data('keywords') || '';
            const keywords = keywordsAttr.toString().toLowerCase();
            
            const isEnabled = $card.find('.tv-toggle-input').is(':checked');

            // Search check
            const matchesSearch = title.includes(query) || desc.includes(query) || keywords.includes(query);

            // Status filter check
            let matchesStatus = true;
            if (activeTab === 'enabled') {
                matchesStatus = isEnabled;
            } else if (activeTab === 'disabled') {
                matchesStatus = !isEnabled;
            }

            if (matchesSearch && matchesStatus) {
                $card.removeClass('hidden-card');
                visibleCount++;
            } else {
                $card.addClass('hidden-card');
            }
        });

        // Show/hide empty state
        const $emptyState = $('.tv-empty-state');
        if (visibleCount === 0) {
            if (!$emptyState.length) {
                const emptyHtml = `
                    <div class="tv-empty-state">
                        <span class="dashicons dashicons-search"></span>
                        <h3>No matches found</h3>
                        <p>Try refining your search terms or filters.</p>
                    </div>
                `;
                $('.tv-cards-grid').parent().append(emptyHtml);
            } else {
                $emptyState.show();
            }
        } else {
            $emptyState.hide();
        }
    }

    /**
     * AJAX Toggle for single switch
     */
    function initAjaxToggles() {
        $(document).on('change', '.tv-toggle-input', function() {
            const $input = $(this);
            const $card = $input.closest('.tv-card-item');
            const type = $input.data('type'); // widget or module
            const slug = $input.data('slug');
            const isChecked = $input.is(':checked');
            
            // Set loading class to card
            $card.addClass('is-loading');
            $input.prop('disabled', true);

            // AJAX data setup
            const data = {
                action: type === 'widget' ? 'elonix_toggle_widget' : 'elonix_toggle_module',
                nonce: type === 'widget' ? opts.widgets_nonce : opts.modules_nonce,
                slug: slug,
                status: isChecked ? 1 : 0
            };

            $.ajax({
                url: opts.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    $card.removeClass('is-loading');
                    $input.prop('disabled', false);

                    if (response.success) {
                        // Update status badge UI
                        const $badge = $card.find('.tv-status-badge');
                        if (isChecked) {
                            $badge.removeClass('badge-inactive').addClass('badge-active').text('Active');
                            $card.removeClass('is-disabled');
                        } else {
                            $badge.removeClass('badge-active').addClass('badge-inactive').text('Inactive');
                            $card.addClass('is-disabled');
                        }
                        
                        showToast(response.data.message || opts.i18n.success, 'success');
                        
                        updateCountBadges();
                        
                        // Re-filter in case status tab is active
                        setTimeout(filterCards, 200);
                    } else {
                        // Revert checkbox state
                        $input.prop('checked', !isChecked);
                        showToast(response.data.message || opts.i18n.update_failed, 'error');
                    }
                },
                error: function() {
                    $card.removeClass('is-loading');
                    $input.prop('disabled', false);
                    $input.prop('checked', !isChecked);
                    showToast(opts.i18n.update_failed, 'error');
                }
            });
        });
    }

    /**
     * AJAX Bulk Enable / Disable
     */
    function initBulkActions() {
        const $bulkButtons = $('.tv-bulk-btn');
        if (!$bulkButtons.length) return;

        $bulkButtons.on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const type = $btn.data('type'); // widget or module
            const actionType = $btn.data('action'); // enable_all or disable_all
            const $container = $('.tv-cards-grid');

            if ($btn.hasClass('is-loading')) return;

            // Confirm dialog
            if (actionType === 'disable_all') {
                ElonixNotifier.confirm('Are you sure you want to disable all items? This may affect frontend layouts.', function() {
                    executeBulkAction();
                });
                return;
            }
            
            executeBulkAction();
            
            function executeBulkAction() {

            // Set loading
            $btn.addClass('is-loading');
            $container.addClass('grid-loading-blur');

            const data = {
                action: type === 'widget' ? 'elonix_bulk_widgets' : 'elonix_bulk_modules',
                nonce: type === 'widget' ? opts.widgets_nonce : opts.modules_nonce,
                bulk_action: actionType
            };

            $.ajax({
                url: opts.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    $btn.removeClass('is-loading');
                    $container.removeClass('grid-loading-blur');

                    if (response.success) {
                        const statusToSet = actionType === 'enable_all';
                        
                        // Update UI toggles & classes
                        $container.find('.tv-card-item').each(function() {
                            const $card = $(this);
                            const $input = $card.find('.tv-toggle-input');
                            const $badge = $card.find('.tv-status-badge');

                            $input.prop('checked', statusToSet);
                            
                            if (statusToSet) {
                                $badge.removeClass('badge-inactive').addClass('badge-active').text('Active');
                                $card.removeClass('is-disabled');
                            } else {
                                $badge.removeClass('badge-active').addClass('badge-inactive').text('Inactive');
                                $card.addClass('is-disabled');
                            }
                        });

                        showToast(response.data.message || opts.i18n.success, 'success');
                        
                        updateCountBadges();
                        setTimeout(filterCards, 200);
                    } else {
                        showToast(response.data.message || opts.i18n.update_failed, 'error');
                    }
                },
                error: function() {
                    $btn.removeClass('is-loading');
                    $container.removeClass('grid-loading-blur');
                    showToast(opts.i18n.update_failed, 'error');
                }
            });
            }
        });
    }

    /**
     * Copy System Diagnostic Info to Clipboard
     */
    function initSystemInfoCopy() {
        const $copyBtn = $('#elonix-copy-report-btn');
        if (!$copyBtn.length) return;

        $copyBtn.on('click', function(e) {
            e.preventDefault();
            const $reportText = $('#elonix-sysinfo-report');
            if (!$reportText.length) return;

            $reportText.select();
            $reportText[0].setSelectionRange(0, 99999); // Mobile compatibility

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    const $btnText = $copyBtn.find('.btn-text');
                    const originalText = $btnText.text();
                    
                    $btnText.text(opts.i18n.copied);
                    $copyBtn.addClass('copied-success');

                    setTimeout(() => {
                        $btnText.text(originalText);
                        $copyBtn.removeClass('copied-success');
                    }, 2000);
                } else {
                    showToast(opts.i18n.copy_error, 'error');
                }
            } catch (err) {
                showToast(opts.i18n.copy_error, 'error');
            }
        });
    }

    /**
     * Template Library Interaction
     */
    function initTemplateLibrary() {
        const $tplSearch = $('#tv-tpl-search');
        const $tplTabs = $('.tv-filter-tab');
        const $tplCards = $('.tv-template-card');

        if (!$tplSearch.length && !$tplCards.length) return;

        function filterTemplates() {
            const query = $tplSearch.val() ? $tplSearch.val().toLowerCase().trim() : '';
            const activeTab = $('.tv-filter-tab.active').data('filter') || 'all';

            $tplCards.each(function() {
                const $card = $(this);
                const title = $card.find('.tv-template-title').text().toLowerCase();
                const type = $card.data('type') || ''; // page, section, header, footer

                const matchesSearch = title.includes(query);
                
                let matchesTab = true;
                if (activeTab === 'page') {
                    matchesTab = (type === 'page');
                } else if (activeTab === 'section') {
                    matchesTab = (type === 'section');
                } else if (activeTab === 'header-footer') {
                    matchesTab = (type === 'header' || type === 'footer');
                }

                if (matchesSearch && matchesTab) {
                    $card.removeClass('hidden-card').show();
                } else {
                    $card.addClass('hidden-card').hide();
                }
            });
        }

        if ($tplSearch.length) {
            $tplSearch.on('input', filterTemplates);
        }

        $tplTabs.on('click', function(e) {
            e.preventDefault();
            $tplTabs.removeClass('active');
            $(this).addClass('active');
            filterTemplates();
        });
    }

})(jQuery);
