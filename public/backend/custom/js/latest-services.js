/*
    Admin latest service area: in-place heading editing, numbered item cards,
    a drag-and-drop side image and the header visibility pill.
    Unsaved changes are tracked by admin-form-tracker.js.
    The server re-validates everything; these checks are for convenience only.
*/
document.addEventListener('DOMContentLoaded', function () {
    var ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    // ---- Auto-growing textareas ----
    function autosize(field) {
        field.style.height = 'auto';
        field.style.height = field.scrollHeight + 'px';
    }

    document.querySelectorAll('[data-autosize]').forEach(function (field) {
        field.addEventListener('input', function () {
            autosize(field);
        });
        autosize(field);
    });

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

    // ---- Visibility pill ----
    var visibility = document.getElementById('is_active');
    var pill = document.getElementById('ls-visibility-pill');

    if (visibility && pill) {
        visibility.addEventListener('change', function () {
            pill.classList.toggle('is-live', visibility.checked);
            pill.querySelector('.ls-visibility-text').textContent = visibility.checked ? 'Live on home page' : 'Hidden';
        });
    }

    // ---- Numbered item cards: empty cards are skipped, so filled cards are renumbered ----
    var items = Array.prototype.slice.call(document.querySelectorAll('.ls-item'));

    function renumber() {
        var position = 0;

        items.forEach(function (item) {
            var title = item.querySelector('[data-item="title"]').value.trim();
            var description = item.querySelector('[data-item="description"]').value.trim();
            var filled = title !== '' || description !== '';
            var label;

            if (filled) {
                position++;
            }

            label = filled ? (position < 10 ? '0' : '') + position + '.' : '—';
            item.querySelector('.ls-item-number').textContent = label;
            item.querySelector('.ls-item-status').textContent = filled ? 'Shown as ' + label : 'Not shown';
            item.classList.toggle('is-empty', !filled);
        });
    }

    items.forEach(function (item) {
        item.querySelectorAll('[data-item]').forEach(function (field) {
            field.addEventListener('input', renumber);
        });
    });
    renumber();

    // ---- Side image drop zone ----
    var input = document.getElementById('image');
    var preview = document.getElementById('latest-image-preview');
    var dropzone = document.getElementById('ls-dropzone');
    var resetButton = document.getElementById('latest-image-reset');
    var clientError = document.getElementById('latest-image-client-error');
    var objectUrl = null;

    function showImageError(message) {
        clientError.textContent = message;
        clientError.classList.toggle('d-none', !message);
        clientError.classList.toggle('d-block', !!message);
        dropzone.classList.toggle('is-invalid', !!message);
    }

    function setPreview(src, isNew) {
        preview.src = src;
        dropzone.classList.toggle('is-new', isNew);
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
        markDirty();
    }

    function handleFile(file) {
        if (!file) {
            resetPreview();
            return;
        }

        if (ALLOWED_TYPES.indexOf(file.type) === -1) {
            resetPreview();
            showImageError('Please choose a JPG, PNG or WebP image.');
            return;
        }

        // The server's real limit (the app's limit, lowered by PHP's upload_max_filesize when smaller).
        var maxBytes = parseInt(input.dataset.maxBytes, 10) || 4 * 1024 * 1024;

        if (file.size > maxBytes) {
            resetPreview();
            showImageError('This image is ' + (file.size / 1024 / 1024).toFixed(1) + ' MB. Please choose one smaller than ' + (input.dataset.maxLabel || '4 MB') + '.');
            return;
        }

        showImageError('');

        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
        }
        objectUrl = URL.createObjectURL(file);
        setPreview(objectUrl, true);
        markDirty();
    }

    if (input && preview && dropzone) {
        input.addEventListener('change', function () {
            handleFile(input.files && input.files[0]);
        });

        ['dragenter', 'dragover'].forEach(function (type) {
            dropzone.addEventListener(type, function (event) {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (type) {
            dropzone.addEventListener(type, function (event) {
                event.preventDefault();
                dropzone.classList.remove('is-dragover');
            });
        });

        dropzone.addEventListener('drop', function (event) {
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
                showImageError('Your browser does not support dropping files here. Please click to browse instead.');
                return;
            }

            handleFile(file);
        });

        resetButton.addEventListener('click', resetPreview);
    }

    // A file set from JavaScript (drop or undo) fires no input event, so tell the change tracker.
    function markDirty() {
        if (window.adminFormTracker) {
            window.adminFormTracker.refresh();
        }
    }
});
