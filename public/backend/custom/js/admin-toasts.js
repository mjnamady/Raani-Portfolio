/*
    Admin toast notifications.

    The server renders flash messages (saved, deleted, validation failed) as toasts
    inside #adminToastContainer; this script shows them, counts down, pauses while
    hovered or focused, and slides them out. window.adminToast(type, message, title)
    adds one from JavaScript, e.g. after an AJAX action.
*/
(function () {
    var DURATION = 5000;
    var EXIT_MS = 250;

    var ICONS = {
        success: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        danger: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line><circle cx="12" cy="12" r="10"></circle></svg>'
    };

    function dismiss(el) {
        if (el.classList.contains('hide-anim')) {
            return;
        }

        clearTimeout(el._toastTimer);
        el.classList.add('hide-anim');

        setTimeout(function () {
            el.remove();
        }, EXIT_MS);
    }

    function startCountdown(el) {
        var remaining = DURATION;
        var startedAt;

        function run() {
            startedAt = Date.now();
            el._toastTimer = setTimeout(function () {
                dismiss(el);
            }, remaining);
        }

        function pause() {
            if (el.classList.contains('is-paused')) {
                return;
            }

            clearTimeout(el._toastTimer);
            remaining -= Date.now() - startedAt;
            el.classList.add('is-paused');
        }

        function resume() {
            if (!el.classList.contains('is-paused') || el.contains(document.activeElement)) {
                return;
            }

            el.classList.remove('is-paused');
            run();
        }

        el.addEventListener('mouseenter', pause);
        el.addEventListener('focusin', pause);
        el.addEventListener('mouseleave', resume);
        el.addEventListener('focusout', function () {
            setTimeout(resume, 0);
        });

        run();
    }

    function activate(el) {
        el.style.setProperty('--toast-duration', DURATION + 'ms');
        el.classList.add('show');

        var close = el.querySelector('.admin-toast-close');
        if (close) {
            close.addEventListener('click', function () {
                dismiss(el);
            });
        }

        startCountdown(el);
    }

    window.adminToast = function (type, message, title) {
        var container = document.getElementById('adminToastContainer');
        if (!container) {
            return;
        }

        type = type === 'danger' ? 'danger' : 'success';

        var el = document.createElement('div');
        el.className = 'toast admin-toast admin-toast-' + type;
        el.setAttribute('role', type === 'danger' ? 'alert' : 'status');
        el.setAttribute('aria-live', type === 'danger' ? 'assertive' : 'polite');
        el.setAttribute('aria-atomic', 'true');
        el.innerHTML =
            '<div class="admin-toast-icon" aria-hidden="true">' + ICONS[type] + '</div>' +
            '<div class="admin-toast-body"><p class="admin-toast-title"></p><p class="admin-toast-message"></p></div>' +
            '<button type="button" class="btn-close admin-toast-close" aria-label="Close"></button>' +
            '<div class="admin-toast-progress" aria-hidden="true"></div>';

        // Text only: messages may contain user input.
        el.querySelector('.admin-toast-title').textContent = title || (type === 'danger' ? 'Not saved' : 'Saved');
        el.querySelector('.admin-toast-message').textContent = message;

        container.appendChild(el);
        activate(el);
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#adminToastContainer .admin-toast').forEach(activate);
    });
})();
