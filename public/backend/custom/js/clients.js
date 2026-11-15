/*
    Admin supported companies: confirm before deleting, and on the logo form a
    drag-and-drop logo preview with a dark/light background switch.
    The server re-validates everything; these checks are for convenience only.
*/
document.addEventListener('DOMContentLoaded', function () {
    var ALLOWED_TYPES = ['image/png', 'image/webp', 'image/jpeg'];

    // ---- Confirm before deleting ----
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // ---- Logo form ----
    var input = document.getElementById('logo');
    var drop = document.getElementById('cl-drop');
    var preview = document.getElementById('cl-logo-preview');
    var empty = document.getElementById('cl-drop-empty');
    var resetButton = document.getElementById('cl-logo-reset');
    var clientError = document.getElementById('cl-logo-error');
    var objectUrl = null;

    if (!input || !drop || !preview) {
        return;
    }

    var maxBytes = parseInt(input.dataset.maxBytes, 10) || 2 * 1024 * 1024;
    var maxLabel = input.dataset.maxLabel || '2 MB';

    function refreshTracker() {
        if (window.adminFormTracker) {
            window.adminFormTracker.refresh();
        }
    }

    function showError(message) {
        clientError.textContent = message;
        clientError.classList.toggle('d-none', !message);
        clientError.classList.toggle('d-block', !!message);
        drop.classList.toggle('is-invalid', !!message);
    }

    function setPreview(src, isNew) {
        preview.src = src;
        preview.hidden = !src;
        empty.hidden = !!src;
        drop.classList.toggle('is-new', isNew);
        resetButton.classList.toggle('d-none', !isNew);
    }

    function reset() {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        input.value = '';
        showError('');
        setPreview(preview.dataset.original, false);
        refreshTracker();
    }

    function handleFile(file) {
        if (!file) {
            reset();
            return;
        }

        if (ALLOWED_TYPES.indexOf(file.type) === -1) {
            reset();
            showError(file.type === 'image/svg+xml'
                ? 'SVG files are not accepted. Please export the logo as PNG or WebP.'
                : 'Please choose a PNG, WebP or JPG image.');
            return;
        }

        if (file.size > maxBytes) {
            reset();
            showError('This logo is ' + (file.size / 1024 / 1024).toFixed(1) + ' MB. Please choose one smaller than ' + maxLabel + '.');
            return;
        }

        showError('');

        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
        }
        objectUrl = URL.createObjectURL(file);
        setPreview(objectUrl, true);
        refreshTracker();
    }

    input.addEventListener('change', function () {
        handleFile(input.files && input.files[0]);
    });

    ['dragenter', 'dragover'].forEach(function (type) {
        drop.addEventListener(type, function (event) {
            event.preventDefault();
            drop.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach(function (type) {
        drop.addEventListener(type, function (event) {
            event.preventDefault();
            drop.classList.remove('is-dragover');
        });
    });

    drop.addEventListener('drop', function (event) {
        var file = event.dataTransfer && event.dataTransfer.files[0];

        if (!file) {
            return;
        }

        // Put the dropped file into the real input so it is submitted with the form.
        try {
            var transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
        } catch (e) {
            showError('Your browser does not support dropping files here. Please click to browse instead.');
            return;
        }

        handleFile(file);
    });

    resetButton.addEventListener('click', reset);

    // ---- Dark / light preview background ----
    document.querySelectorAll('[data-preview-bg]').forEach(function (button) {
        button.addEventListener('click', function () {
            var dark = button.dataset.previewBg === 'dark';

            drop.classList.toggle('is-dark', dark);
            drop.classList.toggle('is-light', !dark);

            document.querySelectorAll('[data-preview-bg]').forEach(function (other) {
                other.classList.toggle('is-active', other === button);
            });
        });
    });
});
