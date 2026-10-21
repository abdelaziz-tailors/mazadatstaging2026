const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function setup(saved = {}) {
    function input(name, value = '', min = 0, max = 9999999999.99, step = 0.01) {
        return {
            name, value, disabled: false, required: false, validityMessage: '', listeners: {},
            addEventListener(event, callback) { this.listeners[event] = callback; },
            setCustomValidity(message) { this.validityMessage = message; },
            checkValidity() {
                const number = Number(this.value);
                return this.value !== '' && !this.validityMessage && Number.isFinite(number)
                    && number >= min && number <= max && (step !== 1 || Number.isInteger(number));
            },
            change(value, event = 'input') { this.value = value; this.listeners[event]?.(); },
        };
    }
    function form(restore = {}) {
        const elements = {
            price_entry: input('price_entry', restore.mode || 'manual'),
            price: input('price', restore.price || ''), unit_rate: input('unit_rate'),
            number_of_days: input('number_of_days', restore.days || '', 1, 2147483647, 1),
            number_of_animals: input('number_of_animals', restore.animals || '', 1, 2147483647, 1),
        };
        const manual = { hidden: false }, entry = { hidden: false }, label = { textContent: '' };
        const sections = Object.fromEntries(['unit_rate', 'number_of_days', 'number_of_animals'].map(name => [name, { hidden: true }]));
        const output = { textContent: '' };
        const service = {
            ...input('item_service_id', restore.selected || '1'),
            selectedIndex: Number(restore.selected || 1),
            options: [
                { value: '', dataset: {} },
                { value: '1', dataset: { defaultPrice: '150', requiresAnimals: '0', requiresDays: '0', rateLabel: 'Service price' } },
                { value: '2', dataset: { defaultPrice: '35', requiresAnimals: '1', requiresDays: '0', rateLabel: 'Price per animal' } },
                { value: '3', dataset: { defaultPrice: '3', requiresAnimals: '1', requiresDays: '1', rateLabel: 'Daily price per animal' } },
            ],
            change(value) { this.value = value; this.selectedIndex = Number(value); this.listeners.change(); },
        };
        return {
            elements, manual, entry, label, sections, output, service,
            dataset: { currency: restore.currency || 'SAR', totalTooLarge: 'Total exceeds the price limit.', restoreInput: restore.restored ? '1' : '0' },
            querySelector(selector) {
                const name = selector.match(/^\[data-service-calculator="(.+)"\]$/)?.[1];
                return name ? sections[name] : {
                    '[data-manual-price]': manual, '[data-price-entry]': entry,
                    '[data-service-total]': output, '[data-unit-rate-label]': label,
                    '.item-service-type-select': service,
                }[selector];
            },
        };
    }
    const forms = [form(saved), form()];
    const document = { querySelectorAll: () => forms };
    const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/dashboard/pages/orders/_piece_services.blade.php'), 'utf8');
    vm.runInNewContext(blade.match(/<script>([\s\S]*?)<\/script>/)[1], { document });
    return forms;
}

function feeding(form, days = '5', animals = '20') {
    form.service.change('3');
    form.elements.number_of_days.change(days);
    form.elements.number_of_animals.change(animals);
}

test('Arabic currency follows the form locale for calculated and manual prices', () => {
    const [form] = setup({ currency: 'ريال' });
    feeding(form);
    assert.equal(form.output.textContent, '300.00 ريال');
    form.service.change('2');
    form.elements.number_of_animals.change('20');
    assert.equal(form.output.textContent, '700.00 ريال');
    form.service.change('1');
    assert.equal(form.output.textContent, '150.00 ريال');
    form.elements.price.change('123.45');
    assert.equal(form.output.textContent, '123.45 ريال');
});

test('fixed catalog service shows only price and total, and allows manual overrides', () => {
    const [form] = setup();
    assert.equal(form.elements.price.value, '150');
    assert.equal(form.output.textContent, '150.00 SAR');
    assert.equal(form.entry.hidden, true);
    assert.ok(Object.values(form.sections).every(section => section.hidden));
    form.elements.price.change('175.25');
    assert.equal(form.output.textContent, '175.25 SAR');
});

test('35 x 20 previews 700.00 SAR without a days field', () => {
    const [form] = setup();
    form.service.change('2');
    form.elements.number_of_animals.change('20');
    assert.equal(form.output.textContent, '700.00 SAR');
    assert.equal(form.elements.unit_rate.value, '35');
    assert.equal(form.label.textContent, 'Price per animal');
    assert.equal(form.sections.number_of_days.hidden, true);
    assert.equal(form.elements.number_of_days.required, false);
    form.elements.number_of_days.change('invalid');
    assert.equal(form.output.textContent, '700.00 SAR');
});

test('3 x 5 x 20 previews 300.00 SAR and updates instantly', () => {
    const [form] = setup();
    feeding(form);
    assert.equal(form.output.textContent, '300.00 SAR');
    assert.equal(form.sections.number_of_days.hidden, false);
    assert.equal(form.elements.number_of_days.required, true);
    form.elements.number_of_days.change('6');
    assert.equal(form.output.textContent, '360.00 SAR');
    form.elements.number_of_animals.change('10');
    assert.equal(form.output.textContent, '180.00 SAR');
});

test('changing service updates rate, labels and visible inputs', () => {
    const [form] = setup();
    feeding(form);
    form.service.change('2');
    assert.equal(form.elements.unit_rate.value, '35');
    assert.equal(form.sections.number_of_days.hidden, true);
    assert.equal(form.elements.number_of_animals.value, '');
    form.service.change('1');
    assert.equal(form.manual.hidden, false);
    assert.equal(form.elements.price.disabled, false);
    assert.equal(form.output.textContent, '150.00 SAR');
    assert.ok(Object.values(form.sections).every(section => section.hidden));
});

test('manual total override survives toggling and ignores calculator inputs', () => {
    const [form] = setup();
    feeding(form);
    form.elements.price_entry.change('manual', 'change');
    form.elements.price.change('49.75');
    assert.equal(form.output.textContent, '49.75 SAR');
    assert.equal(form.elements.number_of_days.disabled, true);
    form.elements.price_entry.change('calculated', 'change');
    assert.equal(form.output.textContent, '300.00 SAR');
    form.elements.price_entry.change('manual', 'change');
    assert.equal(form.elements.price.value, '49.75');
});

test('each sold item has an independent calculator', () => {
    const [first, second] = setup();
    feeding(first);
    assert.equal(second.output.textContent, '150.00 SAR');
    second.service.change('2');
    second.elements.number_of_animals.change('2');
    assert.equal(second.output.textContent, '70.00 SAR');
    assert.equal(first.output.textContent, '300.00 SAR');
});

test('invalid quantity and catalog rate clear the preview', () => {
    const [form] = setup();
    for (const [days, animals] of [['0', '20'], ['1.5', '20'], ['5', ''], ['5', '-1']]) {
        feeding(form, days, animals);
        assert.equal(form.output.textContent, '\u2014');
    }
    form.service.options[3].dataset.defaultPrice = '1.001';
    feeding(form);
    assert.equal(form.output.textContent, '\u2014');
});

test('overflow blocks submission and correction clears the error', () => {
    const [form] = setup();
    form.service.options[3].dataset.defaultPrice = '9999999999.99';
    feeding(form, '2', '1');
    assert.equal(form.elements.number_of_animals.validityMessage, form.dataset.totalTooLarge);
    assert.equal(form.output.textContent, '\u2014');
    form.elements.number_of_days.change('1');
    assert.equal(form.elements.number_of_animals.validityMessage, '');
    assert.equal(form.output.textContent, '9999999999.99 SAR');
});

test('decimal rates and zero calculate exact halalas', () => {
    const [form] = setup();
    form.service.options[3].dataset.defaultPrice = '0.29';
    feeding(form, '3', '7');
    assert.equal(form.output.textContent, '6.09 SAR');
    form.service.options[3].dataset.defaultPrice = '0';
    feeding(form);
    assert.equal(form.output.textContent, '0.00 SAR');
});

test('validation redisplay preserves service, mode and entered quantities', () => {
    const [form] = setup({ restored: true, selected: '3', mode: 'calculated', days: '5', animals: '20' });
    assert.equal(form.service.value, '3');
    assert.equal(form.elements.number_of_days.value, '5');
    assert.equal(form.output.textContent, '300.00 SAR');
    const [manual] = setup({ restored: true, selected: '2', mode: 'manual', price: '49.75' });
    assert.equal(manual.output.textContent, '49.75 SAR');
    assert.equal(manual.elements.price.value, '49.75');
});

test('catalog method updates the default_price label without changing its input', () => {
    const method = { selectedIndex: 0, options: ['Fixed rate', 'Animal rate', 'Daily rate'].map(rateLabel => ({ dataset: { rateLabel } })), addEventListener(event, callback) { this.change = callback; } };
    const label = { textContent: '' };
    const document = { addEventListener(event, callback) { callback(); }, getElementById(id) { return id === 'cost_pricing_method' ? method : label; } };
    const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/dashboard/pages/item-services/_form.blade.php'), 'utf8');
    vm.runInNewContext(blade.match(/<script>([\s\S]*?)<\/script>/)[1], { document });
    assert.equal(label.textContent, 'Fixed rate');
    method.selectedIndex = 1; method.change();
    assert.equal(label.textContent, 'Animal rate');
    method.selectedIndex = 2; method.change();
    assert.equal(label.textContent, 'Daily rate');
});
