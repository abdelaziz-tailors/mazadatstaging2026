// Run the shipped editor against a small DOM double; no browser or packages required.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/dashboard/js/individual-pieces.js'), 'utf8');

class Element {
    constructor() {
        this.children = []; this.listeners = {}; this.dataset = {}; this.value = ''; this.options = [];
        this.disabled = false; this.hidden = false; this.checked = false;
    }
    addEventListener(event, callback) { (this.listeners[event] ||= []).push(callback); }
    fire(event, payload = {}) { for (const callback of this.listeners[event] || []) callback(payload); }
    appendChild(child) { child.parent = this; this.children.push(child); return child; }
    replaceChildren() { this.children = []; }
    remove() { this.parent.children = this.parent.children.filter(child => child !== this); }
    setAttribute(key, value) { this[key] = value; }
    querySelectorAll(selector) {
        if (selector === 'tr') return this.children;
        if (selector === '[data-name]') return Object.values(this.fields);
        if (selector === 'button[type="submit"]') return this.submitters;
        throw Error('Unexpected selector: ' + selector);
    }
    querySelector(selector) {
        if (selector === '.piece-number') return this.number;
        if (selector === '[data-remove-piece]') return this.removeButton;
        const match = selector.match(/^\[data-name="(.*)"\]$/);
        if (match) return this.fields[match[1]];
        throw Error('Unexpected selector: ' + selector);
    }
}

function setup(initial = { pieces: [], deleted: [] }) {
    const ids = {};
    for (const id of ['individual-pieces-editor', 'individual-pieces-toggle', 'pieces-section', 'pieces-rows',
        'piece-row-template', 'entered-pieces-count', 'deleted-piece-inputs', 'individual-pieces-data',
        'apply-pieces-to-all', 'add-individual-piece', 'quantity']) ids[id] = new Element();
    ids.quantity.value = '17';
    ids['individual-pieces-data'].textContent = JSON.stringify(initial);
    const form = new Element(); form.submitters = [new Element(), new Element()];
    ids['individual-pieces-editor'].closest = () => form;
    ids['piece-row-template'].content = { firstElementChild: { cloneNode() {
        const row = new Element(); row.number = new Element(); row.removeButton = new Element(); row.fields = {};
        for (const field of ['id', 'age', 'gender', 'identifier', 'baham_count']) {
            const input = new Element(); input.dataset.name = field; row.fields[field] = input;
        }
        row.fields.age.options = row.fields.age.children;
        return row;
    } } };
    const window = new Element();
    vm.runInNewContext(source, {
        document: { getElementById: id => ids[id], createElement: () => new Element() },
        window, setTimeout: callback => callback(),
    });
    return {
        ids, form, window, rows: () => ids['pieces-rows'].children,
        add: () => ids['add-individual-piece'].fire('click'),
        mode: enabled => { ids['individual-pieces-toggle'].checked = enabled; ids['individual-pieces-toggle'].fire('change'); },
    };
}

test('default OFF; quantity changes never build rows; rows never change quantity', () => {
    const ui = setup();
    assert.equal(ui.ids['pieces-section'].hidden, true);
    assert.equal(ui.ids['pieces-section'].disabled, true);
    assert.equal(Number(ui.ids['entered-pieces-count'].textContent), 0);
    ui.ids.quantity.value = '30'; ui.ids.quantity.fire('input'); ui.ids.quantity.fire('change');
    assert.equal(ui.rows().length, 0);
    ui.mode(true); ui.add(); ui.add();
    assert.equal(ui.rows().length, 2);
    assert.equal(Number(ui.ids['entered-pieces-count'].textContent), 2);
    ui.rows()[0].fields.age.value = 'Edited';
    ui.ids.quantity.value = '4'; ui.ids.quantity.fire('input');
    assert.equal(ui.rows()[0].fields.age.value, 'Edited');
    assert.equal(ui.rows().length, 2);
    ui.rows()[1].removeButton.fire('click');
    assert.equal(ui.ids.quantity.value, '4');
    assert.equal(Number(ui.ids['entered-pieces-count'].textContent), 1);
    assert.equal(ui.ids['deleted-piece-inputs'].children.length, 0);
});

test('toggle hides without discarding edits; explicit saved-row deletion submits only its ID', () => {
    const ui = setup({ pieces: [{ id: 9, age: 'Historical age' }, { id: 12, gender: 'female' }], deleted: [] });
    assert.equal(ui.rows()[0].fields.id.value, '9');
    assert.equal(ui.rows()[0].fields.age.options[0].value, 'Historical age');
    ui.mode(true); ui.rows()[0].fields.identifier.value = 'Unsaved edit';
    ui.mode(false); ui.mode(true);
    assert.equal(ui.rows()[0].fields.identifier.value, 'Unsaved edit');
    assert.equal(ui.ids['deleted-piece-inputs'].children.length, 0);
    ui.rows()[0].removeButton.fire('click');
    assert.equal(ui.rows().length, 1);
    assert.equal(ui.rows()[0].fields.id.value, '12');
    assert.equal(ui.ids['deleted-piece-inputs'].children[0].name, 'deleted_piece_ids[]');
    assert.equal(ui.ids['deleted-piece-inputs'].children[0].value, '9');
    ui.mode(false);
    assert.equal(ui.ids['deleted-piece-inputs'].children[0].value, '9');
    assert.equal(ui.ids.quantity.value, '17');
});

test('validation restoration handles sparse indices and deletion list; apply-all leaves IDs alone', () => {
    const ui = setup({ pieces: { 0: { id: 1, age: 'Retired', gender: 'male' }, 3: { id: 2, gender: 'female' } }, deleted: [5] });
    ui.mode(true);
    ui.ids['apply-pieces-to-all'].fire('click');
    assert.equal(ui.rows()[1].fields.age.value, 'Retired');
    assert.equal(ui.rows()[1].fields.gender.value, 'male');
    assert.equal(ui.rows()[1].fields.id.value, '2');
    assert.equal(ui.rows()[1].fields.id.name, 'pieces[1][id]');
    assert.equal(ui.ids['deleted-piece-inputs'].children[0].value, '5');
});

test('second submit is prevented and back navigation re-enables submission', () => {
    const ui = setup(); let prevented = false;
    ui.form.fire('submit', { preventDefault() { prevented = true; } });
    assert.equal(prevented, false);
    assert.equal(ui.form.submitters[0].disabled, true);
    ui.form.fire('submit', { preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    ui.window.fire('pageshow');
    assert.equal(ui.form.submitters[0].disabled, false);
});
