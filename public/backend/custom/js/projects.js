/*
    Admin portfolio list pages (projects board, categories): confirm before deleting,
    and apply the category filter as soon as it changes.
*/
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('form[data-auto-submit] select').forEach(function (select) {
        select.addEventListener('change', function () {
            select.form.submit();
        });
    });
});
