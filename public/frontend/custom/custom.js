/*
    Public site script overrides for the Reeni theme.
    The theme scripts under frontend/assets stay untouched; put every behaviour change here.
*/

/*
    Contact form, project form and chat popup (CON-3): send in the background and show the answer
    inline. The submit is caught in the capture phase, before the theme's contact.form.js (jQuery,
    on the form) can post it its own way. Without JavaScript the forms post normally and the page
    comes back with the same messages, rendered by the server.
*/
document.addEventListener('submit', function (event) {
    var form = event.target;

    if (!form.matches || !form.matches('form[data-message-form]') || !window.fetch || !window.FormData) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    var status = form.previousElementSibling && form.previousElementSibling.classList.contains('message-form-status')
        ? form.previousElementSibling
        : null;
    var button = form.querySelector('button[type="submit"]');

    function showStatus(text, kind) {
        if (status) {
            status.textContent = text;
            status.classList.remove('success', 'error');
            status.classList.add(kind);
        }
    }

    function clearErrors() {
        form.querySelectorAll('[data-error-for]').forEach(function (slot) {
            slot.textContent = '';
        });
        form.querySelectorAll('.is-invalid').forEach(function (field) {
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');
        });
    }

    clearErrors();
    showStatus('', 'success');
    button.setAttribute('aria-busy', 'true');

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
        .then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (data) {
                return { status: response.status, data: data };
            });
        })
        .then(function (result) {
            if (result.status === 200) {
                form.reset();
                showStatus(result.data.message || 'Thanks, your message has been sent.', 'success');
                return;
            }

            if (result.status === 422 && result.data.errors) {
                Object.keys(result.data.errors).forEach(function (name) {
                    var slot = form.querySelector('[data-error-for="' + name + '"]');
                    var field = form.querySelector('[name="' + name + '"]');

                    if (slot) {
                        slot.textContent = result.data.errors[name][0];
                    }

                    if (field) {
                        field.classList.add('is-invalid');
                        field.setAttribute('aria-invalid', 'true');
                    }
                });
                showStatus('Please check the highlighted fields.', 'error');
                return;
            }

            if (result.status === 419) {
                showStatus('This page has been open for a while. Please reload it and send your message again.', 'error');
                return;
            }

            showStatus(result.data.message || 'Sorry, your message could not be sent. Please try again in a moment.', 'error');
        })
        .catch(function () {
            showStatus('Sorry, your message could not be sent. Please check your connection and try again.', 'error');
        })
        .finally(function () {
            button.removeAttribute('aria-busy');
        });
}, true);
