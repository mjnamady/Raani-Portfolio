/*
    Admin education/experience entry form: live preview of the public card and character counters.
    textContent only: the values are user input. Unsaved changes are tracked by admin-form-tracker.js.
*/
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bind]').forEach(function (field) {
        var key = field.dataset.bind;
        var targets = document.querySelectorAll('[data-show="' + key + '"]');
        var joined = document.querySelectorAll('[data-show-join="' + key + '"]');

        function update() {
            var value = field.value.trim();

            targets.forEach(function (target) {
                var placeholder = target.dataset.placeholder || '';
                target.textContent = value || placeholder;
                target.classList.toggle('is-placeholder', !value && !!placeholder);
            });

            // Optional parts (e.g. " · Institution") simply disappear when empty.
            joined.forEach(function (target) {
                target.textContent = value;
            });
        }

        field.addEventListener('input', update);
        update();
    });

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
