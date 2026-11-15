/*
    Admin services area board: add, remove, reorder and show/hide card tiles, each with
    a live face of the public card. The server re-validates everything; this is for convenience only.
    Unsaved changes (including added, removed and moved tiles) are tracked by admin-form-tracker.js.
*/
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('services-form');
    var board = document.getElementById('service-rows');
    var template = document.getElementById('service-row-template');
    var addColumn = document.getElementById('sv-add-col');
    var addButton = document.getElementById('add-service');
    var emptyState = document.getElementById('services-empty');
    var countLabel = document.getElementById('sv-count');
    var shownLabel = document.getElementById('sv-shown');

    if (!form || !board || !template) {
        return;
    }

    var max = parseInt(form.dataset.max, 10) || 8;
    var nextKey = 0;

    // New tiles need keys that don't collide with existing ones (existing keys are numbers).
    board.querySelectorAll('[name^="services["]').forEach(function (input) {
        var match = input.name.match(/^services\[(\d+)\]/);
        if (match) {
            nextKey = Math.max(nextKey, parseInt(match[1], 10) + 1);
        }
    });

    function tiles() {
        return Array.prototype.slice.call(board.querySelectorAll('[data-service-row]'));
    }

    function refresh() {
        var all = tiles();
        var shown = 0;

        all.forEach(function (tile, index) {
            tile.querySelector('[data-row-number]').textContent = index + 1;
            tile.querySelector('[data-move="up"]').disabled = index === 0;
            tile.querySelector('[data-move="down"]').disabled = index === all.length - 1;

            if (tile.querySelector('[data-preview="active"]').checked) {
                shown++;
            }
        });

        countLabel.textContent = all.length;
        shownLabel.textContent = shown;
        addColumn.classList.toggle('d-none', all.length >= max);
        emptyState.classList.toggle('d-none', all.length > 0);
    }

    function updatePreview(tile) {
        var title = tile.querySelector('[data-preview="title"]').value.trim();
        var icon = tile.querySelector('[data-preview="icon"]').value.trim().replace(/\s+/g, ' ');
        var count = tile.querySelector('[data-preview="count"]').value;
        var label = tile.querySelector('[data-preview="label"]').value.trim();
        var active = tile.querySelector('[data-preview="active"]').checked;

        tile.querySelector('[data-card-title]').textContent = title || 'Service title';
        tile.querySelector('[data-card-count]').textContent = (count === '' ? 0 : Number(count).toLocaleString()) + ' ' + (label || 'Projects');

        // Only plain Font Awesome class names reach the className.
        if (/^fa-[a-z]+ fa-[a-z0-9-]+$/.test(icon)) {
            tile.querySelector('[data-card-icon]').className = icon;
            tile.querySelector('[data-icon-preview]').className = icon;
        }

        tile.querySelector('.sv-tile').classList.toggle('is-hidden-on-site', !active);
    }

    function bindTile(tile) {
        tile.querySelectorAll('[data-preview]').forEach(function (input) {
            input.addEventListener('input', function () { updatePreview(tile); });
            input.addEventListener('change', function () {
                updatePreview(tile);
                refresh();
            });
        });

        tile.querySelector('[data-move="up"]').addEventListener('click', function () {
            var previous = tile.previousElementSibling;
            if (previous && previous.hasAttribute('data-service-row')) {
                board.insertBefore(tile, previous);
                refresh();
                this.focus();
            }
        });

        tile.querySelector('[data-move="down"]').addEventListener('click', function () {
            var next = tile.nextElementSibling;
            if (next && next.hasAttribute('data-service-row')) {
                board.insertBefore(next, tile);
                refresh();
                this.focus();
            }
        });

        tile.querySelector('[data-remove]').addEventListener('click', function () {
            var title = tile.querySelector('[data-preview="title"]').value.trim() || 'this card';
            var existing = tile.dataset.existing === '1';

            if (existing && !window.confirm('Remove "' + title + '" from the home page? The service itself is kept.')) {
                return;
            }

            tile.remove();
            refresh();
            addButton.focus();
        });

        updatePreview(tile);
    }

    addButton.addEventListener('click', function () {
        if (tiles().length >= max) {
            return;
        }

        var html = template.innerHTML.replace(/__KEY__/g, String(nextKey++));
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        var tile = wrapper.firstElementChild;

        board.insertBefore(tile, addColumn);
        bindTile(tile);
        refresh();
        tile.querySelector('[data-preview="title"]').focus();
    });

    tiles().forEach(bindTile);
    refresh();
});
