/*
    Admin banner section: keeps the live stage (a miniature of the public banner) in sync
    with the tabbed form, previews a new portrait and counts characters.
    The server re-validates everything; these checks are for convenience only.
    Unsaved changes are tracked by admin-form-tracker.js.
*/
document.addEventListener('DOMContentLoaded', function () {
    var ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    function refreshTracker() {
        if (window.adminFormTracker) {
            window.adminFormTracker.refresh();
        }
    }

    // ---- Text fields mirrored on the stage (textContent only: values are user input) ----
    document.querySelectorAll('[data-bind]').forEach(function (field) {
        var targets = document.querySelectorAll('[data-show="' + field.dataset.bind + '"]');

        function update() {
            var value = field.value.trim();

            targets.forEach(function (target) {
                var placeholder = target.dataset.placeholder || '';
                target.textContent = value || placeholder;
                target.classList.toggle('is-placeholder', !value && !!placeholder);
            });
        }

        field.addEventListener('input', update);
        update();
    });

    // ---- Counters mirrored on the stage ----
    document.querySelectorAll('[data-stat-row]').forEach(function (row) {
        var preview = document.querySelector('[data-stat-preview="' + row.dataset.statRow + '"]');
        var value = row.querySelector('[data-stat="value"]');
        var suffix = row.querySelector('[data-stat="suffix"]');
        var label = row.querySelector('[data-stat="label"]');

        function update() {
            var hasContent = value.value !== '' || label.value.trim() !== '';

            preview.querySelector('strong').textContent = (value.value || '0') + suffix.value;
            preview.querySelector('span').textContent = label.value.trim() || 'Label';
            preview.classList.toggle('is-empty', !hasContent);
        }

        [value, suffix, label].forEach(function (field) {
            field.addEventListener('input', update);
        });
        update();
    });

    // ---- Visibility ----
    var visibility = document.getElementById('is_active');
    var stage = document.getElementById('bn-stage');
    var visibilityLabel = document.getElementById('bn-visibility-label');

    if (visibility && stage) {
        visibility.addEventListener('change', function () {
            stage.classList.toggle('is-off', !visibility.checked);
            visibilityLabel.textContent = visibility.checked ? 'Visible on home page' : 'Hidden from home page';
        });
    }

    // ---- Live portrait preview ----
    var input = document.getElementById('image');
    var preview = document.getElementById('banner-image-preview');
    var wrapper = document.querySelector('.bn-stage-portrait');
    var resetButton = document.getElementById('banner-image-reset');
    var clientError = document.getElementById('banner-image-client-error');
    var objectUrl = null;

    function showImageError(message) {
        clientError.textContent = message;
        clientError.classList.toggle('d-none', !message);
        input.classList.toggle('is-invalid', !!message);
    }

    function setPreview(src, isNew) {
        preview.src = src;
        wrapper.classList.toggle('is-new', isNew);
        resetButton.classList.toggle('d-none', !isNew);
    }

    function resetPreview() {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        input.value = '';
        showImageError('');
        setPreview(preview.dataset.original, false);
        refreshTracker();
    }

    if (input && preview) {
        // The server's real limit (the app's limit, lowered by PHP's upload_max_filesize when smaller).
        var maxBytes = parseInt(input.dataset.maxBytes, 10) || 4 * 1024 * 1024;
        var maxLabel = input.dataset.maxLabel || '4 MB';

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];

            if (!file) {
                resetPreview();
                return;
            }

            if (ALLOWED_TYPES.indexOf(file.type) === -1) {
                resetPreview();
                showImageError('Please choose a JPG, PNG or WebP image.');
                return;
            }

            if (file.size > maxBytes) {
                resetPreview();
                showImageError('This image is ' + (file.size / 1024 / 1024).toFixed(1) + ' MB. Please choose one smaller than ' + maxLabel + '.');
                return;
            }

            showImageError('');

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }
            objectUrl = URL.createObjectURL(file);
            setPreview(objectUrl, true);
        });

        resetButton.addEventListener('click', resetPreview);
    }

    // ---- Character counters ----
    document.querySelectorAll('[data-char-count]').forEach(function (field) {
        var counter = document.querySelector('.char-count[data-for="' + field.id + '"]');
        var max = parseInt(field.getAttribute('maxlength'), 10);

        if (!counter || !max) {
            return;
        }

        function update() {
            counter.textContent = field.value.length + ' / ' + max;
            counter.classList.toggle('is-near-limit', field.value.length > max * 0.9);
        }

        field.addEventListener('input', update);
        update();
    });
});
