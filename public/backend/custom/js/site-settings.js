/*
    Admin Site Settings: live previews (text, images, social links, footer links), footer link
    rows that can be added, reordered and cleared, and the character counter.
    Everything is re-validated on the server.
*/
document.addEventListener('DOMContentLoaded', function () {
    // ---- Text previews ----
    document.querySelectorAll('[data-preview]').forEach(function (input) {
        var targets = document.querySelectorAll('[data-preview-target="' + input.dataset.preview + '"]');

        function update() {
            targets.forEach(function (target) {
                target.textContent = input.value.trim();
            });
        }

        input.addEventListener('input', update);
        update();
    });

    // ---- Image previews (local file, never uploaded until saved) ----
    document.querySelectorAll('[data-image-input]').forEach(function (input) {
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            var previews = document.querySelectorAll('[data-image-preview="' + input.dataset.imageInput + '"]');

            previews.forEach(function (img) {
                if (!img.dataset.originalSrc) {
                    img.dataset.originalSrc = img.src;
                }

                img.src = file && file.type.indexOf('image/') === 0 ? URL.createObjectURL(file) : img.dataset.originalSrc;
            });
        });
    });

    // ---- Social links: a dot shows which icons will appear ----
    document.querySelectorAll('[data-social-input]').forEach(function (input) {
        var dot = document.querySelector('[data-social-dot="' + input.dataset.socialInput + '"]');

        function update() {
            dot.classList.toggle('is-on', /^https:\/\/\S+$/i.test(input.value.trim()));
        }

        input.addEventListener('input', update);
        update();
    });

    // ---- Footer quick links ----
    var list = document.querySelector('[data-footer-links]');

    if (list) {
        var addButton = document.querySelector('[data-link-add]');
        var preview = document.querySelector('[data-footer-preview]');

        function rows() {
            return Array.prototype.slice.call(list.querySelectorAll('[data-link-row]'));
        }

        function isEmpty(row) {
            return row.querySelector('[data-link-label]').value.trim() === '' && row.querySelector('[data-link-url]').value.trim() === '';
        }

        // Keep the posted order matching the visible order.
        function renumber() {
            rows().forEach(function (row, index) {
                row.querySelector('[data-link-label]').name = 'footer_links[' + index + '][label]';
                row.querySelector('[data-link-url]').name = 'footer_links[' + index + '][url]';
                row.querySelector('.ss-link-handle').textContent = index + 1;
            });
        }

        // Show filled rows plus one empty row; "Add a link" reveals the next one.
        function refresh() {
            var shownEmpty = false;

            rows().forEach(function (row) {
                var hide = isEmpty(row) && shownEmpty;
                row.hidden = hide;
                shownEmpty = shownEmpty || isEmpty(row);
            });

            addButton.classList.toggle('d-none', !rows().some(function (row) { return row.hidden; }) || rows().some(function (row) { return !row.hidden && isEmpty(row); }));

            preview.replaceChildren();
            rows().forEach(function (row) {
                var label = row.querySelector('[data-link-label]').value.trim();

                if (label !== '') {
                    var item = document.createElement('li');
                    item.textContent = label;
                    preview.appendChild(item);
                }
            });
        }

        list.addEventListener('input', refresh);

        list.addEventListener('click', function (event) {
            var button = event.target.closest('button');

            if (!button) {
                return;
            }

            var row = button.closest('[data-link-row]');

            if (button.hasAttribute('data-link-clear')) {
                row.querySelector('[data-link-label]').value = '';
                row.querySelector('[data-link-url]').value = '';
                list.appendChild(row);
            } else if (button.dataset.linkMove === 'up' && row.previousElementSibling) {
                list.insertBefore(row, row.previousElementSibling);
            } else if (button.dataset.linkMove === 'down' && row.nextElementSibling && !row.nextElementSibling.hidden) {
                list.insertBefore(row.nextElementSibling, row);
            }

            renumber();
            refresh();
            list.dispatchEvent(new Event('change', { bubbles: true }));
        });

        addButton.addEventListener('click', function () {
            var next = rows().find(function (row) { return row.hidden; });

            if (next) {
                next.hidden = false;
                next.querySelector('[data-link-label]').focus();
                addButton.classList.add('d-none');
            }
        });

        refresh();
    }

    // ---- Character counter ----
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
