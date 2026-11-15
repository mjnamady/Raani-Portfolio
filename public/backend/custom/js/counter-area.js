/*
    Admin counter area scoreboard: number inputs that grow with their value,
    auto-growing labels, empty-tile states, "clear" buttons and a character counter.
    The server re-validates everything; these checks are for convenience only.
    Unsaved changes are tracked by admin-form-tracker.js.
*/
document.addEventListener('DOMContentLoaded', function () {
    // ---- Number inputs sized to their content, so number and suffix sit together ----
    document.querySelectorAll('[data-autowidth]').forEach(function (field) {
        function update() {
            var length = (field.value || field.placeholder || '0').length;
            field.style.width = (length + 0.4) + 'ch';
        }

        field.addEventListener('input', update);
        update();
    });

    // ---- Auto-growing textareas ----
    document.querySelectorAll('[data-autosize]').forEach(function (field) {
        function update() {
            field.style.height = 'auto';
            field.style.height = field.scrollHeight + 'px';
        }

        field.addEventListener('input', update);
        update();
    });

    // ---- Character counter ----
    document.querySelectorAll('[data-count]').forEach(function (field) {
        var counter = document.querySelector('[data-count-for="' + field.id + '"]');
        var max = parseInt(field.getAttribute('maxlength'), 10);

        if (!counter || !max) {
            return;
        }

        function update() {
            counter.textContent = field.value.length + ' / ' + max;
        }

        field.addEventListener('input', update);
        update();
    });

    // ---- Stat tiles: empty state and clear button ----
    document.querySelectorAll('[data-counter-tile]').forEach(function (tile) {
        var fields = tile.querySelectorAll('[data-counter]');

        function update() {
            var filled = Array.prototype.some.call(fields, function (field) {
                return field.value.trim() !== '';
            });

            tile.classList.toggle('is-empty', !filled);
        }

        fields.forEach(function (field) {
            field.addEventListener('input', update);
        });

        tile.querySelector('[data-clear]').addEventListener('click', function () {
            fields.forEach(function (field) {
                field.value = '';
                // Let the auto-width, empty state and change tracker react as if typed.
                field.dispatchEvent(new Event('input', { bubbles: true }));
            });
            fields[0].focus();
        });

        update();
    });
});
