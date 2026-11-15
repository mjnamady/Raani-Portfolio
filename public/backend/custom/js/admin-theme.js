/*
    Keeps the admin colour mode stable: dark by default, light only after a manual change.

    The Cork theme stores the mode in sessionStorage (per tab, lost on browser restart)
    and applies it at the end of its init, after many other functions that can fail on
    pages missing their markup. This script remembers manual changes in localStorage and
    re-applies the saved mode once the theme scripts have finished.
*/
(function () {
    var STORAGE_KEY = 'adminTheme';

    function preferredDark() {
        try {
            return localStorage.getItem(STORAGE_KEY) !== 'light';
        } catch (e) {
            return true;
        }
    }

    function apply(dark) {
        document.body.classList.toggle('dark', dark);
        document.documentElement.setAttribute('data-admin-theme', dark ? 'dark' : 'light');

        try {
            sessionStorage.setItem('_LayoutDark', dark ? 'true' : 'false');
        } catch (e) {
            // Ignore: the body class already applies the mode.
        }
    }

    function rememberCurrent() {
        var dark = document.body.classList.contains('dark');

        try {
            localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light');
        } catch (e) {
            // Storage unavailable: the change still applies to this page.
        }

        apply(dark);
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Navbar sun/moon toggle (Cork flips the class in its own click handler first).
        document.querySelectorAll('.theme-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                setTimeout(rememberCurrent, 0);
            });
        });

        // Theme customizer light/dark options, if the customizer panel is present.
        document.querySelectorAll('input[name="theme-colorScheme-style"]').forEach(function (input) {
            input.addEventListener('change', function () {
                setTimeout(rememberCurrent, 0);
            });
        });
    });

    // Safety net: once every theme script has run, make sure the saved mode is still applied.
    window.addEventListener('load', function () {
        apply(preferredDark());
    });
})();
