/*
    Admin profile settings: live profile image preview, password show/hide,
    and client-side hints for the change-password form.
    The server re-validates everything; these checks are for convenience only.
*/
document.addEventListener('DOMContentLoaded', function () {
    var MAX_IMAGE_BYTES = 2 * 1024 * 1024;
    var ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    // ---- Live profile image preview ----
    var input = document.getElementById('profile_image');
    var preview = document.getElementById('profile-image-preview');
    var previewWrapper = document.querySelector('.ps-avatar-preview');
    var headerAvatar = document.getElementById('profile-header-avatar');
    var resetButton = document.getElementById('profile-image-reset');
    var clientError = document.getElementById('profile-image-client-error');
    var objectUrl = null;

    function showImageError(message) {
        clientError.textContent = message;
        clientError.classList.toggle('d-none', !message);
        input.classList.toggle('is-invalid', !!message);
    }

    function setPreview(src, isNew) {
        preview.src = src;
        if (headerAvatar) {
            headerAvatar.src = src;
        }
        previewWrapper.classList.toggle('is-new', isNew);
        resetButton.classList.toggle('d-none', !isNew);
    }

    function resetPreview() {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        input.value = '';
        showImageError('');
        setPreview(preview.dataset.original, false);
    }

    if (input && preview) {
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];

            if (!file) {
                resetPreview();
                return;
            }

            if (ALLOWED_TYPES.indexOf(file.type) === -1) {
                resetPreview();
                showImageError('Please choose a JPG, PNG or WebP image.');
                return;
            }

            if (file.size > MAX_IMAGE_BYTES) {
                resetPreview();
                showImageError('The profile image may not be larger than 2 MB.');
                return;
            }

            showImageError('');

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }
            objectUrl = URL.createObjectURL(file);
            setPreview(objectUrl, true);
        });

        resetButton.addEventListener('click', resetPreview);
    }

    // ---- Password show / hide ----
    document.querySelectorAll('.form-password-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var field = document.getElementById(toggle.dataset.target);
            var show = field.type === 'password';

            field.type = show ? 'text' : 'password';
            toggle.classList.toggle('is-visible', show);
            toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });

    // ---- Change password hints ----
    var newPassword = document.getElementById('password');
    var confirmPassword = document.getElementById('password_confirmation');
    var matchHint = document.getElementById('password-match-hint');

    var rules = {
        length: function (v) { return v.length >= 8; },
        case: function (v) { return /[a-z]/.test(v) && /[A-Z]/.test(v); },
        number: function (v) { return /\d/.test(v); },
        symbol: function (v) { return /[^A-Za-z0-9]/.test(v); }
    };

    function updateRules() {
        var value = newPassword.value;

        Object.keys(rules).forEach(function (name) {
            var item = document.querySelector('.password-rules [data-rule="' + name + '"]');
            if (item) {
                item.classList.toggle('is-met', rules[name](value));
            }
        });
    }

    function updateMatch() {
        if (!confirmPassword.value) {
            matchHint.textContent = '';
            matchHint.className = 'form-text';
            return;
        }

        var matches = confirmPassword.value === newPassword.value;
        matchHint.textContent = matches ? 'Passwords match.' : 'Passwords do not match.';
        matchHint.className = 'form-text ' + (matches ? 'text-success' : 'text-danger');
    }

    if (newPassword && confirmPassword) {
        newPassword.addEventListener('input', function () {
            updateRules();
            updateMatch();
        });
        confirmPassword.addEventListener('input', updateMatch);
        updateRules();
    }
});
