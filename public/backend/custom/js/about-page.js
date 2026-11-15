/*
    Admin About page: confirm before deleting, live meter while typing a percentage, and the
    character counter. The server re-validates the percentage (whole number 0-100).
*/
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // ---- Live meter ----
    document.querySelectorAll('[data-bar-percent]').forEach(function (input) {
        var meter = input.closest('form').querySelector('.ab-meter span');

        if (!meter) {
            return;
        }

        input.addEventListener('input', function () {
            var value = parseInt(input.value, 10);
            meter.style.width = (isNaN(value) ? 0 : Math.max(0, Math.min(100, value))) + '%';
        });
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
