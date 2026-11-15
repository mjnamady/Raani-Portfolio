/*
    Admin My Skill: confirm before deleting, and on the skill form the icon picker,
    live preview card and character counter.
    textContent only for previews (values are user input). The server re-validates the icon.
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

    var iconInput = document.getElementById('icon');
    if (!iconInput) {
        return;
    }

    var ICON_PATTERN = /^fa-[a-z]+ fa-[a-z0-9]+(?:-[a-z0-9]+)*$/;

    var currentIcon = document.getElementById('sk-icon-current');
    var previewIcon = document.getElementById('sk-preview-icon');
    var grid = document.getElementById('sk-icon-grid');
    var search = document.getElementById('sk-icon-search');

    // Only plain Font Awesome class names ever reach className.
    function applyIcon(value) {
        var icon = value.trim().replace(/\s+/g, ' ');

        if (ICON_PATTERN.test(icon)) {
            currentIcon.className = icon;
            previewIcon.className = icon;
        }

        grid.querySelectorAll('.sk-icon-option').forEach(function (option) {
            var selected = option.dataset.icon === icon;
            option.classList.toggle('is-selected', selected);
            option.setAttribute('aria-selected', selected ? 'true' : 'false');
        });
    }

    grid.addEventListener('click', function (event) {
        var option = event.target.closest('.sk-icon-option');
        if (!option) {
            return;
        }

        iconInput.value = option.dataset.icon;
        // Let the preview and the change tracker react as if typed.
        iconInput.dispatchEvent(new Event('input', { bubbles: true }));
    });

    iconInput.addEventListener('input', function () {
        applyIcon(iconInput.value);
    });

    search.addEventListener('input', function () {
        var term = search.value.trim().toLowerCase();

        grid.querySelectorAll('.sk-icon-option').forEach(function (option) {
            option.hidden = term !== '' && option.dataset.name.indexOf(term) === -1;
        });
    });

    // ---- Live preview ----
    var fields = {
        title: document.querySelector('[data-preview="title"]'),
        count: document.querySelector('[data-preview="count"]'),
        label: document.querySelector('[data-preview="label"]'),
        description: document.querySelector('[data-preview="description"]'),
        highlight: document.querySelector('[data-preview="highlight"]')
    };
    var card = document.getElementById('sk-preview-card');
    var title = document.getElementById('sk-preview-title');
    var count = document.getElementById('sk-preview-count');
    var description = document.getElementById('sk-preview-desc');

    function setText(element, value, placeholder) {
        element.textContent = value || placeholder;
        element.classList.toggle('is-placeholder', !value);
    }

    function updatePreview() {
        setText(title, fields.title.value.trim(), 'Skill title');
        count.textContent = (fields.count.value === '' ? 0 : Number(fields.count.value).toLocaleString()) + ' ' + (fields.label.value.trim() || 'Done');
        setText(description, fields.description.value.trim(), 'A short description of this skill.');
        card.classList.toggle('is-highlighted', fields.highlight.checked);
    }

    Object.keys(fields).forEach(function (key) {
        fields[key].addEventListener('input', updatePreview);
        fields[key].addEventListener('change', updatePreview);
    });

    updatePreview();
    applyIcon(iconInput.value);

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
