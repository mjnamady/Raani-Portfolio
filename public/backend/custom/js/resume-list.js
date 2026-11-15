/*
    Admin education/experience list pages: confirm before deleting, and (experience page)
    a live preview of a newly chosen side photo.
    The server re-validates everything; these checks are for convenience only.
*/
document.addEventListener('DOMContentLoaded', function () {
    var ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    // ---- Confirm before deleting ----
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // ---- Side photo preview (experience page only) ----
    var input = document.getElementById('image');
    var preview = document.getElementById('ex-photo-preview');
    var frame = document.getElementById('ex-photo');
    var resetButton = document.getElementById('ex-photo-reset');
    var clientError = document.getElementById('ex-photo-error');
    var objectUrl = null;

    if (!input || !preview || !frame) {
        return;
    }

    // The server's real limit (the app's limit, lowered by PHP's upload_max_filesize when smaller).
    var maxBytes = parseInt(input.dataset.maxBytes, 10) || 4 * 1024 * 1024;
    var maxLabel = input.dataset.maxLabel || '4 MB';

    function showError(message) {
        clientError.textContent = message;
        clientError.classList.toggle('d-none', !message);
        clientError.classList.toggle('d-block', !!message);
        frame.classList.toggle('is-invalid', !!message);
    }

    function setPreview(src, isNew) {
        preview.src = src;
        frame.classList.toggle('is-new', isNew);
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

        // Clearing the file from JavaScript fires no event, so tell the change tracker.
        if (window.adminFormTracker) {
            window.adminFormTracker.refresh();
        }
    }

    input.addEventListener('change', function () {
        var file = input.files && input.files[0];

        if (!file) {
            reset();
            return;
        }

        if (ALLOWED_TYPES.indexOf(file.type) === -1) {
            reset();
            showError('Please choose a JPG, PNG or WebP image.');
            return;
        }

        if (file.size > maxBytes) {
            reset();
            showError('This image is ' + (file.size / 1024 / 1024).toFixed(1) + ' MB. Please choose one smaller than ' + maxLabel + '.');
            return;
        }

        showError('');

        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
        }
        objectUrl = URL.createObjectURL(file);
        setPreview(objectUrl, true);
    });

    resetButton.addEventListener('click', reset);
});
