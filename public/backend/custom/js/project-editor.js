/*
    Admin project editor: slug from the title, the features list, tag chips, card/cover
    image previews and the gallery upload picker. The rich-text editor is admin-editor.js.
    The server re-validates everything; these checks are for convenience only.
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
            .slice(0, 130);
    }

    // ---- Slug follows the title on new projects until it is edited by hand ----
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

    // ---- Features list ----
    var list = document.getElementById('pj-features');
    var template = document.getElementById('pj-feature-template');
    var addFeature = document.getElementById('pj-add-feature');
    var empty = document.getElementById('pj-features-empty');

    if (list && template && addFeature) {
        var max = parseInt(list.dataset.max, 10) || 12;

        var refreshFeatures = function () {
            var count = list.children.length;
            addFeature.disabled = count >= max;
            empty.classList.toggle('d-none', count > 0);
        };

        list.addEventListener('click', function (event) {
            var button = event.target.closest('[data-remove-feature]');
            if (button) {
                button.closest('li').remove();
                refreshFeatures();
            }
        });

        addFeature.addEventListener('click', function () {
            if (list.children.length >= max) {
                return;
            }
            list.appendChild(template.content.firstElementChild.cloneNode(true));
            list.lastElementChild.querySelector('input').focus();
            refreshFeatures();
        });

        refreshFeatures();
    }

    // ---- Tag chips (textContent only: tags are user input) ----
    var tags = document.getElementById('tags');
    var chips = document.getElementById('pj-tag-chips');

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

    // ---- Card / cover image previews ----
    document.querySelectorAll('input[data-image-input]').forEach(function (input) {
        var drop = document.querySelector('label[for="' + input.id + '"]');
        var preview = drop.querySelector('img');
        var placeholder = drop.querySelector('.pj-drop-empty');
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

    // ---- Gallery upload picker ----
    var gallery = document.getElementById('gallery-images');
    var picked = document.getElementById('gallery-picked');
    var uploadButton = document.getElementById('gallery-upload-btn');

    if (gallery && picked && uploadButton) {
        var defaultText = picked.textContent;
        var galleryMax = parseInt(gallery.dataset.maxBytes, 10) || 4 * 1024 * 1024;
        var maxFiles = parseInt(gallery.dataset.maxFiles, 10) || 10;

        gallery.addEventListener('change', function () {
            var files = Array.prototype.slice.call(gallery.files || []);
            var problem = '';

            if (files.length > maxFiles) {
                problem = 'Please choose at most ' + maxFiles + ' images.';
            } else if (files.some(function (file) { return ALLOWED_TYPES.indexOf(file.type) === -1; })) {
                problem = 'Only JPG, PNG and WebP images can be added.';
            } else if (files.some(function (file) { return file.size > galleryMax; })) {
                problem = 'Each image must be smaller than ' + gallery.dataset.maxLabel + '.';
            }

            uploadButton.disabled = files.length === 0 || !!problem;
            picked.textContent = problem || (files.length ? files.length + ' image' + (files.length === 1 ? '' : 's') + ' selected' : defaultText);
            picked.classList.toggle('text-danger', !!problem);
        });
    }

    // ---- Confirm before removing gallery images ----
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
});
