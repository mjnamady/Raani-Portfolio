/*
    Unsaved-changes tracking for admin forms.

    Any form with data-track-changes is snapshotted once the page has loaded. Its save bar
    ([data-save-bar], see components/save_bar.blade.php) then shows "Unsaved changes" and a
    Discard link whenever the form differs from that snapshot, and the browser asks before
    leaving the page. Added, removed or reordered rows are noticed automatically; after
    changing a value from JavaScript without an input event, call window.adminFormTracker.refresh().
    Fields marked data-track-ignore (e.g. a current-password confirmation) never count as changes.
*/
(function () {
    var trackers = [];

    function snapshot(form) {
        var entries = [];
        var ignored = ['_token', '_method'];

        // Fields that only confirm a change (e.g. a browser-autofilled current password) are not changes,
        // and typed passwords are never copied into the snapshot; their length marks the change instead.
        // Matched by autocomplete, not type: the show/hide toggle switches password fields to type="text".
        form.querySelectorAll('[data-track-ignore][name], input[autocomplete$="-password"][name]').forEach(function (field) {
            ignored.push(field.name);
        });

        new FormData(form).forEach(function (value, name) {
            if (ignored.indexOf(name) !== -1) {
                return;
            }

            // Files cannot be compared by content; a selected file is a change.
            if (typeof File !== 'undefined' && value instanceof File) {
                entries.push([name, value.name ? 'file:' + value.name + ':' + value.size : '']);
                return;
            }

            entries.push([name, value]);
        });

        form.querySelectorAll('input[autocomplete$="-password"][name]:not([data-track-ignore])').forEach(function (field) {
            entries.push([field.name, field.value.length > 0 ? 'filled' : '']);
        });

        return JSON.stringify(entries);
    }

    function Tracker(form) {
        var self = this;

        this.form = form;
        this.bar = form.querySelector('[data-save-bar]');
        this.text = this.bar && this.bar.querySelector('[data-save-text]');
        this.discard = this.bar && this.bar.querySelector('[data-save-discard]');
        this.submitting = false;

        // After a failed validation the form shows edits that were never saved.
        this.initial = form.querySelector('.is-invalid') ? null : snapshot(form);

        form.addEventListener('input', function () { self.refresh(); });
        form.addEventListener('change', function () { self.refresh(); });
        form.addEventListener('submit', function () { self.submitting = true; });

        if (this.discard) {
            // Discard reloads the saved version on purpose, so skip the leave-page warning.
            this.discard.addEventListener('click', function () { self.submitting = true; });
        }

        if (window.MutationObserver) {
            new MutationObserver(function () { self.refresh(); }).observe(form, { childList: true, subtree: true });
        }

        this.refresh();
    }

    Tracker.prototype.isDirty = function () {
        return this.initial === null || snapshot(this.form) !== this.initial;
    };

    Tracker.prototype.refresh = function () {
        if (!this.bar) {
            return;
        }

        var dirty = this.isDirty();
        var label = dirty ? 'Unsaved changes' : 'All changes saved';

        this.bar.classList.toggle('is-dirty', dirty);

        // Only touch the text when it changes: the bar sits inside the observed form.
        if (this.text.textContent !== label) {
            this.text.textContent = label;
        }

        if (this.discard) {
            this.discard.classList.toggle('d-none', !dirty);
        }
    };

    window.adminFormTracker = {
        refresh: function () {
            trackers.forEach(function (tracker) { tracker.refresh(); });
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Let page scripts finish their own setup first, so it is not counted as a change.
        setTimeout(function () {
            document.querySelectorAll('form[data-track-changes]').forEach(function (form) {
                trackers.push(new Tracker(form));
            });
        }, 0);
    });

    window.addEventListener('beforeunload', function (event) {
        var unsaved = trackers.some(function (tracker) {
            return !tracker.submitting && tracker.isDirty();
        });

        if (unsaved) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
})();
