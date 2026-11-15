/*
    Admin blog: confirm before deleting, and on the post form the slug from the title,
    tag chips, the featured image preview and character counters. The rich-text editor is
    admin-editor.js. The server re-validates everything; these checks are for convenience only.
*/
document.addEventListener('DOMContentLoaded', function () {
    var ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    function refreshTracker() {
        if (window.adminFormTracker) {
            window.adminFormTracker.refresh();
        }
    }

    function slugify(text) {
        return text
            .toLowerCase()
            .normalize('NFKD')
            .replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 170);
    }

    // ---- Confirm before deleting ----
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // ---- Slug follows the title on new posts until it is edited by hand ----
    var title = document.getElementById('title');
    var slug = document.getElementById('slug');

    if (title && slug && slug.dataset.slugAuto === '1') {
        var auto = true;

        title.addEventListener('input', function () {
            if (auto) {
                slug.value = slugify(title.value);
            }
        });

        slug.addEventListener('input', function () {
            auto = slug.value === '';
        });
    }

    // ---- Tag chips (textContent only: tags are user input) ----
    var tags = document.getElementById('tags');
    var chips = document.getElementById('bl-tag-chips');

    if (tags && chips) {
        var renderChips = function () {
            chips.textContent = '';
            var seen = {};

            tags.value.split(',').forEach(function (raw) {
                var tag = raw.trim();
                var key = slugify(tag);

                if (!tag || seen[key]) {
                    return;
                }
                seen[key] = true;

                var chip = document.createElement('span');
                chip.textContent = tag;
                chips.appendChild(chip);
            });
        };

        tags.addEventListener('input', renderChips);
        renderChips();
    }

    // ---- Featured image preview ----
    document.querySelectorAll('input[data-image-input]').forEach(function (input) {
        var drop = document.querySelector('label[for="' + input.id + '"]');
        var preview = drop.querySelector('img');
        var placeholder = drop.querySelector('.bl-drop-empty');
        var error = drop.nextElementSibling;
        var maxBytes = parseInt(input.dataset.maxBytes, 10) || 4 * 1024 * 1024;
        var objectUrl = null;

        function show(src, isNew) {
            preview.src = src || '';
            preview.hidden = !src;
            placeholder.hidden = !!src;
            drop.classList.toggle('is-new', isNew);
        }

        // Only shows or clears the message; it never touches the chosen file.
        function setError(message) {
            error.textContent = message;
            error.classList.toggle('d-block', !!message);
            drop.classList.toggle('is-invalid', !!message);
        }

        // Rejects the chosen file: empties the input and goes back to the saved image.
        function reject(message) {
            input.value = '';
            show(preview.dataset.original, false);
            setError(message);
            refreshTracker();
        }

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            if (!file) {
                reject('');
                return;
            }

            if (ALLOWED_TYPES.indexOf(file.type) === -1) {
                reject('Please choose a JPG, PNG or WebP image.');
                return;
            }

            if (file.size > maxBytes) {
                reject('This image is ' + (file.size / 1024 / 1024).toFixed(1) + ' MB. Please choose one smaller than ' + input.dataset.maxLabel + '.');
                return;
            }

            // A valid file: keep it in the input so it is uploaded on save.
            setError('');
            objectUrl = URL.createObjectURL(file);
            show(objectUrl, true);
        });
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
});
