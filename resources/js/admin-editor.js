/*
    Rich-text editor for the admin panel (Quill 2, bundled by Vite and served from this site).

    Every <textarea data-rich-text> becomes an editor. The textarea stays in the form (hidden)
    and receives the editor's HTML on every change, so the form posts it as usual and the
    unsaved-changes tracker sees the edit. The server sanitizes this HTML (mews/purifier)
    before saving; the toolbar only offers what that allow-list keeps.
*/
import Quill from 'quill';

const TOOLBAR = [
    [{ header: [2, 3, 4, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['blockquote', 'link'],
    ['clean'],
];

document.querySelectorAll('textarea[data-rich-text]').forEach((textarea) => {
    const holder = document.createElement('div');
    holder.className = 'rich-text-editor';
    textarea.insertAdjacentElement('afterend', holder);
    textarea.hidden = true;

    const quill = new Quill(holder, {
        theme: 'snow',
        placeholder: textarea.getAttribute('placeholder') || '',
        modules: { toolbar: TOOLBAR },
        formats: ['header', 'bold', 'italic', 'underline', 'strike', 'list', 'blockquote', 'link'],
    });

    // The stored value was sanitized on save, so it is safe to load back into the editor.
    if (textarea.value.trim() !== '') {
        quill.clipboard.dangerouslyPasteHTML(textarea.value, 'silent');
    }

    const counter = document.querySelector(`[data-rich-count-for="${textarea.id}"]`);

    const sync = () => {
        // getSemanticHTML() writes real <ul>/<ol> lists (Quill's internal markup uses data attributes).
        textarea.value = quill.getText().trim() === '' ? '' : quill.getSemanticHTML();
        textarea.dispatchEvent(new Event('input', { bubbles: true }));

        if (counter) {
            counter.textContent = `${quill.getText().trim().length} characters`;
        }
    };

    quill.on('text-change', sync);

    if (counter) {
        counter.textContent = `${quill.getText().trim().length} characters`;
    }

    // Keep the label working: clicking it focuses the editor.
    document.querySelectorAll(`label[for="${textarea.id}"]`).forEach((label) => {
        label.addEventListener('click', () => quill.focus());
    });

    if (textarea.classList.contains('is-invalid')) {
        holder.classList.add('is-invalid');
    }
});
