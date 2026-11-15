/*
    Admin Contact page: each card's preview follows its fields as you type, and the
    "Get in touch" text has a character counter. The server re-validates everything.
*/
document.addEventListener('DOMContentLoaded', function () {
    // ---- Live card previews ----
    document.querySelectorAll('[data-card]').forEach(function (card) {
        var titleInput = card.querySelector('[data-title-input]');
        var lineInputs = card.querySelectorAll('[data-line-input]');
        var previewTitle = card.querySelector('[data-preview-title]');
        var previewLines = card.querySelector('[data-preview-lines]');
        var previewEmpty = card.querySelector('[data-preview-empty]');

        function update() {
            previewTitle.textContent = titleInput.value.trim() || 'Untitled';
            previewLines.replaceChildren();

            var count = 0;

            lineInputs.forEach(function (input) {
                var value = input.value.trim();

                if (value === '') {
                    return;
                }

                var line = document.createElement('span');
                line.textContent = value;
                previewLines.appendChild(line);
                count++;
            });

            previewEmpty.hidden = count > 0;
            card.classList.toggle('is-empty', count === 0);
        }

        titleInput.addEventListener('input', update);
        lineInputs.forEach(function (input) {
            input.addEventListener('input', update);
        });
        update();
    });

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
