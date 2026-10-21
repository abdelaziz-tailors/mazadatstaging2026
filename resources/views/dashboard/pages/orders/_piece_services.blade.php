@php
    $orderEditable = $order->payment_status === 'unpaid'
        && $order->status !== 'delivered'
        && $order->settled_at === null;

    /*
     * OrderItem.seller_id (not LiveVideoItem.seller_id) is the
     * authoritative, already-resolved seller for this sale —
     * attachWonItem() sets it to the item's own seller_id, or falls back
     * to the item's user_id (the organizer) when the piece is the
     * organizer's own and was never given an explicit seller_id. Filtering
     * on liveVideoItem->seller_id here instead hid this entire section for
     * any such piece — same root cause already fixed in
     * OrderService::sellerInvoiceSummariesForOrder()/ForLiveVideo().
     */
    $sellerOrderItems = $order->items->filter(
        fn ($orderItem) => $orderItem->seller_id
    );
@endphp

@if ($sellerOrderItems->isNotEmpty())
<div class="mb-4">
    <div class="border rounded p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">{{ TranslationHelper::translate('item_services') }}</h6>
            <div class="text-end">
                <small class="text-muted d-block">{{ TranslationHelper::translate('piece_services_total') }}</small>
                <strong>{{ number_format($order->piece_services_total, 2) }}</strong>
            </div>
        </div>

        @if (! $orderEditable)
            <p class="text-muted mb-3">{{ TranslationHelper::translate('order_not_editable') }}</p>
        @endif

        @foreach ($sellerOrderItems as $orderItem)
            @php
                $item = $orderItem->liveVideoItem;
                $restoreInput = (string) old('order_item_id') === (string) $orderItem->id;
            @endphp

            <div class="border rounded p-3 mt-2 bg-light mb-3">
                @if ($item)
                    <strong>{{ app()->getLocale() === 'ar' ? ($item->title_ar ?? $item->title) : ($item->title ?? $item->title_ar) }}</strong>
                @else
                    <strong class="text-warning">{{ app()->getLocale() === 'ar' ? 'عنصر المزاد غير متاح' : 'Auction item unavailable' }} ({{ $orderItem->live_video_item_id }})</strong>
                    <p class="text-muted mb-2">{{ app()->getLocale() === 'ar' ? 'تعذر العثور على عنصر المزاد المرتبط. إضافة الخدمات غير متاحة لهذا البند.' : 'The related auction item could not be found. Adding services is unavailable for this order item.' }}</p>
                @endif

                @if ($orderItem->services->isNotEmpty())
                    <div class="table-responsive mb-3 mt-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>{{ TranslationHelper::translate('name') }}</th>
                                    <th>{{ TranslationHelper::translate('price') }}</th>
                                    @if ($orderEditable)
                                        <th>{{ TranslationHelper::translate('actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orderItem->services as $service)
                                    <tr>
                                        <td>{{ $service->displayName() }}</td>
                                        <td>{{ number_format($service->price, 2) }}</td>
                                        @if ($orderEditable)
                                            <td>
                                                <form action="{{ route('admin.order-piece-services.destroy', $service->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ TranslationHelper::translate('are_you_sure') }}')">
                                                        {{ TranslationHelper::translate('delete') }}
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-2 mt-2">{{ TranslationHelper::translate('no_item_services_yet') }}</p>
                @endif

                @if ($orderEditable && $item)
                    <form action="{{ route('admin.order-piece-services.store', $order->id) }}" method="POST" class="row g-2 align-items-end service-price-form" data-restore-input="{{ $restoreInput ? '1' : '0' }}" data-currency="{{ __('service_price.currency') }}" data-total-too-large="{{ __('service_price.total_too_large') }}">
                        @csrf
                        @if ($restoreInput && $errors->any())
                            <div class="col-12"><ul class="alert alert-danger mb-0" role="alert">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul></div>
                        @endif
                        <input type="hidden" name="order_item_id" value="{{ $orderItem->id }}">
                        <div class="col-md-4">
                            <label class="form-label">{{ TranslationHelper::translate('item_service') }}</label>
                            <select name="item_service_id" class="form-control item-service-type-select" required>
                                <option value="">{{ TranslationHelper::translate('select') }}</option>
                                @foreach ($itemServices as $type)
                                    <option value="{{ $type->id }}" data-default-price="{{ $type->getRawOriginal('default_price') ?? '' }}"
                                        data-requires-animals="{{ $type->pricingMethod() !== 'fixed' ? '1' : '0' }}"
                                        data-requires-days="{{ $type->pricingMethod() === 'per_animal_per_day' ? '1' : '0' }}"
                                        data-rate-label="{{ __('service_price.rate_'.$type->pricingMethod()) }}"
                                        {{ $restoreInput && (string) old('item_service_id') === (string) $type->id ? 'selected' : '' }}>
                                        {{ $type->localizedName() }}
                                        @if($type->default_price !== null)
                                            ({{ number_format($type->default_price, 2) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3" data-price-entry hidden>
                            <label class="form-label" for="price-entry-{{ $orderItem->id }}">{{ __('service_price.entry') }}</label>
                            <select name="price_entry" id="price-entry-{{ $orderItem->id }}" class="form-control">
                                <option value="manual" {{ $restoreInput && old('price_entry') === 'manual' ? 'selected' : '' }}>{{ __('service_price.manual') }}</option>
                                <option value="calculated" {{ $restoreInput && old('price_entry') === 'calculated' ? 'selected' : '' }}>{{ __('service_price.use_catalog') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3" data-manual-price>
                            <label class="form-label">{{ TranslationHelper::translate('price') }}</label>
                            <input type="number" name="price" class="form-control item-service-price" step="0.01" min="0" value="{{ $restoreInput ? old('price') : '' }}" placeholder="{{ TranslationHelper::translate('default_price') }}">
                        </div>
                        @foreach (['unit_rate' => 'unit_rate', 'number_of_days' => 'days', 'number_of_animals' => 'animals'] as $field => $label)
                            <div class="col-md-3" data-service-calculator="{{ $field }}" hidden>
                                <label class="form-label" for="{{ $field }}-{{ $orderItem->id }}" @if($field === 'unit_rate') data-unit-rate-label @endif>{{ __('service_price.'.$label) }}</label>
                                <input type="number" name="{{ $field }}" id="{{ $field }}-{{ $orderItem->id }}" class="form-control" dir="ltr"
                                    min="{{ $field === 'unit_rate' ? '0' : '1' }}"
                                    max="{{ $field === 'unit_rate' ? '9999999999.99' : '2147483647' }}"
                                    step="{{ $field === 'unit_rate' ? '0.01' : '1' }}"
                                    value="{{ $restoreInput && $field !== 'unit_rate' ? old($field) : '' }}" @if($field === 'unit_rate') readonly @endif disabled>
                            </div>
                        @endforeach
                        <div class="col-md-3">
                            <label class="form-label" for="service-total-{{ $orderItem->id }}">{{ __('service_price.total') }}</label>
                            <output id="service-total-{{ $orderItem->id }}" class="form-control" data-service-total dir="ltr" aria-live="polite">—</output>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">{{ TranslationHelper::translate('add') }}</button>
                        </div>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif

@once
    @push('scripts')
        <script>
            document.querySelectorAll('.service-price-form').forEach(function (form) {
                const service = form.querySelector('.item-service-type-select');
                const mode = form.elements.price_entry;
                const price = form.elements.price;
                const rate = form.elements.unit_rate;
                const days = form.elements.number_of_days;
                const animals = form.elements.number_of_animals;
                const output = form.querySelector('[data-service-total]');

                function preview() {
                    const selected = service.options[service.selectedIndex];
                    const supportsCalculation = selected.dataset.requiresAnimals === '1';
                    const calculated = supportsCalculation && mode.value === 'calculated';
                    const requiresDays = calculated && selected.dataset.requiresDays === '1';
                    form.querySelector('[data-manual-price]').hidden = calculated;
                    price.disabled = calculated;
                    [[rate, calculated], [animals, calculated], [days, requiresDays]].forEach(function ([input, visible]) {
                        form.querySelector('[data-service-calculator="' + input.name + '"]').hidden = !visible;
                        input.disabled = !visible;
                        input.required = visible;
                    });
                    rate.setCustomValidity('');
                    animals.setCustomValidity('');
                    output.textContent = '—';
                    if (!calculated) {
                        if (price.value !== '' && price.checkValidity()) {
                            output.textContent = Number(price.value).toFixed(2) + ' ' + form.dataset.currency;
                        }
                        return;
                    }
                    if (!/^\d+(?:\.\d{1,2})?$/.test(rate.value) || !/^\d+$/.test(animals.value)
                        || (requiresDays && !/^\d+$/.test(days.value))
                        || ![rate, animals, ...(requiresDays ? [days] : [])].every(function (input) { return input.checkValidity(); })) {
                        return;
                    }
                    const parts = rate.value.split('.');
                    const halalas = BigInt(parts[0]) * 100n + BigInt((parts[1] || '').padEnd(2, '0'));
                    const total = halalas * BigInt(animals.value) * (requiresDays ? BigInt(days.value) : 1n);
                    if (total > 999999999999n) {
                        animals.setCustomValidity(form.dataset.totalTooLarge);
                        return;
                    }
                    output.textContent = (total / 100n).toString() + '.' + (total % 100n).toString().padStart(2, '0') + ' ' + form.dataset.currency;
                }

                function syncService(resetInput) {
                    const selected = service.options[service.selectedIndex];
                    const supportsCalculation = selected.dataset.requiresAnimals === '1';
                    const defaultPrice = selected.dataset.defaultPrice || '';
                    form.querySelector('[data-price-entry]').hidden = !supportsCalculation;
                    form.querySelector('[data-unit-rate-label]').textContent = selected.dataset.rateLabel || '';
                    rate.value = defaultPrice;
                    price.placeholder = defaultPrice;
                    if (resetInput) {
                        price.value = defaultPrice;
                        mode.value = supportsCalculation ? 'calculated' : 'manual';
                        days.value = '';
                        animals.value = '';
                    }
                    if (!supportsCalculation) mode.value = 'manual';
                    preview();
                }

                service.addEventListener('change', function () { syncService(true); });
                mode.addEventListener('change', preview);
                [price, rate, days, animals].forEach(function (input) { input.addEventListener('input', preview); });
                syncService(form.dataset.restoreInput !== '1');
            });
        </script>
    @endpush
@endonce
