/*
    Admin sign-in pages: light/dark toggle (shared with the Cork admin layout),
    password show/hide, Caps Lock warning and a submit spinner.
*/
document.addEventListener('DOMContentLoaded', function () {
    // ---- Light / dark mode, saved under the same keys the admin panel uses ----
    var themeToggle = document.getElementById('auth-theme-toggle');

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            var dark = !document.body.classList.contains('dark');

            document.body.classList.toggle('dark', dark);
            document.documentElement.classList.toggle('auth-dark', dark);

            try {
                localStorage.setItem('adminTheme', dark ? 'dark' : 'light');
                sessionStorage.setItem('_LayoutDark', dark ? 'true' : 'false');
            } catch (e) {
                // Storage can be unavailable (private mode); the toggle still works for this page.
            }
        });
    }

    // ---- Password show / hide ----
    document.querySelectorAll('.auth-password-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var field = document.getElementById(toggle.dataset.target);
            var show = field.type === 'password';

            field.type = show ? 'text' : 'password';
            toggle.classList.toggle('is-visible', show);
            toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            field.focus();
        });
    });

    // ---- Caps Lock warning ----
    var password = document.getElementById('password');
    var capsWarning = document.getElementById('capslock-warning');

    if (password && capsWarning) {
        var checkCaps = function (event) {
            if (typeof event.getModifierState === 'function') {
                capsWarning.classList.toggle('d-none', !event.getModifierState('CapsLock'));
            }
        };

        password.addEventListener('keydown', checkCaps);
        password.addEventListener('keyup', checkCaps);
        password.addEventListener('blur', function () {
            capsWarning.classList.add('d-none');
        });
    }

    // ---- Submit: prevent double submits and show progress ----
    var form = document.getElementById('login-form');
    var submit = document.getElementById('login-submit');

    if (form && submit) {
        form.addEventListener('submit', function () {
            submit.disabled = true;
            submit.querySelector('.spinner-border').classList.remove('d-none');
            submit.querySelector('.auth-submit-label').textContent = 'Signing in…';
        });

        // Re-enable the button when the page is restored from the back/forward cache.
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                submit.disabled = false;
                submit.querySelector('.spinner-border').classList.add('d-none');
                submit.querySelector('.auth-submit-label').textContent = 'Sign in';
            }
        });
    }
});
