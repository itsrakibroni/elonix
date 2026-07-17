window.ElonixNotifier = (function($) {
    function showToast(message, type = 'success') {
        let $container = $('.es-toast-container');
        if (!$container.length) {
            $container = $('<div class="es-toast-container"></div>').appendTo('body');
        }

        let iconClass = 'dashicons-info';
        if (type === 'success') iconClass = 'dashicons-yes';
        if (type === 'error') iconClass = 'dashicons-dismiss';
        if (type === 'warning') iconClass = 'dashicons-warning';

        const $toast = $('<div class="es-toast es-toast-' + type + '"><span class="dashicons ' + iconClass + '"></span><span class="es-toast-message">' + message + '</span></div>');

        $container.append($toast);
        setTimeout(() => $toast.addClass('show'), 10);
        setTimeout(() => {
            $toast.removeClass('show').addClass('hide');
            setTimeout(() => $toast.remove(), 300);
        }, 4000);
    }

    function showModal(options) {
        options = $.extend({
            title: 'Notification',
            icon: 'info',
            message: '',
            details: null, // For technical details (Developer Mode)
            buttons: [
                { text: 'Close', type: 'secondary', onClick: function(modal) { modal.close(); } }
            ]
        }, options);

        let iconHtml = '';
        if (options.icon === 'error') iconHtml = '<span class="dashicons dashicons-no-alt" style="color: #d63638; font-size: 40px; width: 40px; height: 40px;"></span>';
        else if (options.icon === 'warning') iconHtml = '<span class="dashicons dashicons-warning" style="color: #f56e28; font-size: 40px; width: 40px; height: 40px;"></span>';
        else if (options.icon === 'success') iconHtml = '<span class="dashicons dashicons-yes" style="color: #00a32a; font-size: 40px; width: 40px; height: 40px;"></span>';

        let detailsHtml = '';
        if (options.details) {
            detailsHtml = '<div class="es-notifier-details"><div class="es-notifier-details-toggle">Show Technical Details <span class="dashicons dashicons-arrow-down-alt2"></span></div><div class="es-notifier-details-content" style="display:none;"><pre>' + options.details + '</pre></div></div>';
        }

        let buttonsHtml = '';
        options.buttons.forEach((btn, idx) => {
            let cls = btn.type === 'primary' ? 'button-primary' : 'button-secondary';
            buttonsHtml += '<button type="button" class="button ' + cls + '" data-idx="' + idx + '">' + btn.text + '</button> ';
        });

        const $modal = $(`
            <div class="es-notifier-overlay">
                <div class="es-notifier-dialog">
                    <div class="es-notifier-header">
                        ${iconHtml}
                        <h2>${options.title}</h2>
                    </div>
                    <div class="es-notifier-body">
                        <p>${options.message}</p>
                        ${detailsHtml}
                    </div>
                    <div class="es-notifier-footer">
                        ${buttonsHtml}
                    </div>
                </div>
            </div>
        `).appendTo('body');

        setTimeout(() => $modal.addClass('show'), 10);

        const modalContext = {
            close: function() {
                $modal.removeClass('show').addClass('hide');
                setTimeout(() => $modal.remove(), 300);
            }
        };

        $modal.find('.es-notifier-details-toggle').on('click', function() {
            $modal.find('.es-notifier-details-content').slideToggle();
        });

        $modal.find('.es-notifier-footer button').on('click', function() {
            const idx = $(this).data('idx');
            if (options.buttons[idx] && typeof options.buttons[idx].onClick === 'function') {
                options.buttons[idx].onClick(modalContext);
            } else {
                modalContext.close();
            }
        });
    }

    return {
        success: (msg) => showToast(msg, 'success'),
        error: (msg) => showToast(msg, 'error'),
        warning: (msg) => showToast(msg, 'warning'),
        info: (msg) => showToast(msg, 'info'),
        modal: showModal,
        confirm: function(msg, onConfirm) {
            showModal({
                title: 'Confirm Action',
                icon: 'warning',
                message: msg,
                buttons: [
                    { text: 'Cancel', type: 'secondary', onClick: (m) => m.close() },
                    { text: 'Confirm', type: 'primary', onClick: (m) => { m.close(); onConfirm(); } }
                ]
            });
        }
    };
})(jQuery);
