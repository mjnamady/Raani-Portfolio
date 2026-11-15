/*
    Admin Testimonials: confirm before deleting, the section title preview, and on the
    testimonial form the live preview card and character counter.
    textContent only for previews (values are user input).
*/
document.addEventListener('DOMContentLoaded', function () {
    // ---- Confirm before deleting ----
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // ---- Section title preview ----
    var titleInput = document.querySelector('[data-title-preview]');
    var titlePreview = document.getElementById('tm-title-preview');

    if (titleInput && titlePreview) {
        titleInput.addEventListener('input', function () {
            titlePreview.textContent = titleInput.value.trim() || 'Testimonial';
        });
    }

    // ---- Live preview ----
    var fields = {
        quote: document.querySelector('[data-preview="quote"]'),
        name: document.querySelector('[data-preview="name"]'),
        role: document.querySelector('[data-preview="role"]')
    };

    if (fields.quote && fields.name && fields.role) {
        var quote = document.getElementById('tm-preview-quote');
        var name = document.getElementById('tm-preview-name');
        var role = document.getElementById('tm-preview-role');

        var setText = function (element, value, placeholder) {
            element.textContent = value || placeholder;
            element.classList.toggle('is-placeholder', !value);
        };

        var updatePreview = function () {
            setText(quote, fields.quote.value.trim().replace(/\s+/g, ' '), 'The quote appears here.');
            setText(name, fields.name.value.trim(), 'Their name');
            role.textContent = fields.role.value.trim();
            role.hidden = fields.role.value.trim() === '';
        };

        Object.keys(fields).forEach(function (key) {
            fields[key].addEventListener('input', updatePreview);
        });

        updatePreview();
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
