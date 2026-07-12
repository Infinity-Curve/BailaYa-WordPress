/**
 * Confirmation prompt for destructive links on the BailaYa management screens.
 *
 * The markup is passed through wp_kses(), which strips inline event handlers, so
 * the prompt is bound here from a data-confirm attribute instead of onclick.
 */
(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        var link = event.target.closest('[data-confirm]');
        if (!link) {
            return;
        }
        if (!window.confirm(link.getAttribute('data-confirm'))) {
            event.preventDefault();
        }
    });
})();
