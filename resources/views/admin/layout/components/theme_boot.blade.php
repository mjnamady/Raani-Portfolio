{{--
    Applies the admin colour mode before anything is painted.
    Dark is the default; "light" only when the admin switched to it manually.
    The choice lives in localStorage ("adminTheme") so it survives new tabs and browser
    restarts, and is mirrored into Cork's sessionStorage key ("_LayoutDark") before the
    theme scripts run, so Cork's own mode() and the chart colours agree with it.
--}}
<script>
    (function () {
        var dark = true;

        try {
            dark = localStorage.getItem('adminTheme') !== 'light';
        } catch (e) {
            // Storage unavailable (private mode): keep the dark default.
        }

        document.body.classList.toggle('dark', dark);
        document.documentElement.setAttribute('data-admin-theme', dark ? 'dark' : 'light');

        try {
            sessionStorage.setItem('_LayoutDark', dark ? 'true' : 'false');
        } catch (e) {
            // Ignore: the body class above already applies the mode.
        }
    })();
</script>
