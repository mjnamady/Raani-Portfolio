/*
    Admin comment moderation: confirm before deleting, and open/close the inline reply boxes.
*/
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-reply-toggle]').forEach(function (button) {
        var form = document.getElementById(button.dataset.replyToggle);

        if (!form) {
            return;
        }

        button.addEventListener('click', function () {
            form.hidden = !form.hidden;
            button.setAttribute('aria-expanded', form.hidden ? 'false' : 'true');

            if (!form.hidden) {
                form.querySelector('textarea').focus();
            }
        });
    });
});
