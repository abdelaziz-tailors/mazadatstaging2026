(function () {
    'use strict';

    const editor = document.getElementById('individual-pieces-editor');
    if (!editor) return;
    const toggle = document.getElementById('individual-pieces-toggle');
    const section = document.getElementById('pieces-section');
    const rows = document.getElementById('pieces-rows');
    const template = document.getElementById('piece-row-template');
    const count = document.getElementById('entered-pieces-count');
    const deletions = document.getElementById('deleted-piece-inputs');
    const initial = JSON.parse(document.getElementById('individual-pieces-data').textContent);
    const deleted = new Set((initial.deleted || []).map(String));
    let nextIndex = 0;

    function refresh() {
        const current = rows.querySelectorAll('tr');
        count.textContent = current.length;
        current.forEach(function (row, index) {
            row.querySelector('.piece-number').textContent = index + 1;
        });
        document.getElementById('apply-pieces-to-all').disabled = current.length < 2;
        deletions.replaceChildren();
        deleted.forEach(function (id) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'deleted_piece_ids[]';
            input.value = id;
            deletions.appendChild(input);
        });
    }

    function addRow(piece) {
        const row = template.content.firstElementChild.cloneNode(true);
        const index = nextIndex++;
        row.querySelectorAll('[data-name]').forEach(function (input) {
            const field = input.dataset.name;
            input.name = 'pieces[' + index + '][' + field + ']';
            const value = piece[field] == null ? '' : String(piece[field]);
            // Keep historical free-text ages even when the lookup is inactive.
            if (field === 'age' && value && !Array.from(input.options).some(function (option) { return option.value === value; })) {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = value;
                input.appendChild(option);
            }
            input.value = value;
        });
        row.querySelector('[data-remove-piece]').addEventListener('click', function () {
            if (piece.id) deleted.add(String(piece.id));
            row.remove();
            refresh();
        });
        rows.appendChild(row);
        refresh();
    }

    function setMode() {
        section.hidden = !toggle.checked;
        section.disabled = !toggle.checked;
        toggle.setAttribute('aria-expanded', String(toggle.checked));
    }

    Object.values(initial.pieces || {}).forEach(function (piece) {
        if (!piece.id || !deleted.has(String(piece.id))) addRow(piece);
    });
    toggle.addEventListener('change', setMode);
    document.getElementById('add-individual-piece').addEventListener('click', function () { addRow({}); });
    document.getElementById('apply-pieces-to-all').addEventListener('click', function () {
        const current = rows.querySelectorAll('tr');
        if (!current.length) return;
        ['age', 'gender', 'identifier', 'baham_count'].forEach(function (field) {
            const source = current[0].querySelector('[data-name="' + field + '"]');
            current.forEach(function (row, index) {
                if (!index) return;
                const target = row.querySelector('[data-name="' + field + '"]');
                if (field === 'age' && source.value && !Array.from(target.options).some(function (option) { return option.value === source.value; })) {
                    const option = document.createElement('option');
                    option.value = source.value;
                    option.textContent = source.value;
                    target.appendChild(option);
                }
                target.value = source.value;
            });
        });
    });

    // Do not touch Quantity: the row collection has its own lifecycle and count.
    setMode();
    refresh();
    const form = editor.closest('form');
    let submitting = false;
    form.addEventListener('submit', function (event) {
        if (submitting) {
            event.preventDefault();
            return;
        }
        submitting = true;
        // Preserve the clicked submitter's action value in the native submission.
        setTimeout(function () {
            form.querySelectorAll('button[type="submit"]').forEach(function (button) { button.disabled = true; });
        }, 0);
    });
    window.addEventListener('pageshow', function () {
        submitting = false;
        form.querySelectorAll('button[type="submit"]').forEach(function (button) { button.disabled = false; });
    });
}());
