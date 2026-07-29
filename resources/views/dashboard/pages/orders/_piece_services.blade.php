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
            @php $item = $orderItem->liveVideoItem; @endphp

            <div class="border rounded p-3 mt-2 bg-light mb-3">
                <strong>{{ app()->getLocale() === 'ar' ? ($item->title_ar ?? $item->title) : ($item->title ?? $item->title_ar) }}</strong>

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

                @if ($orderEditable)
                    <form action="{{ route('admin.order-piece-services.store', $order->id) }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <input type="hidden" name="order_item_id" value="{{ $orderItem->id }}">
                        <div class="col-md-4">
                            <label class="form-label">{{ TranslationHelper::translate('item_service') }}</label>
                            <select name="item_service_id" class="form-control item-service-type-select" required>
                                <option value="">{{ TranslationHelper::translate('select') }}</option>
                                @foreach ($itemServices as $type)
                                    <option value="{{ $type->id }}" data-default-price="{{ $type->default_price ?? '' }}">
                                        {{ $type->localizedName() }}
                                        @if($type->default_price !== null)
                                            ({{ number_format($type->default_price, 2) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ TranslationHelper::translate('price') }}</label>
                            <input type="number" name="price" class="form-control item-service-price" step="0.01" min="0" placeholder="{{ TranslationHelper::translate('default_price') }}">
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
            document.querySelectorAll('.item-service-type-select').forEach(function (select) {
                const form = select.closest('form');
                const priceInput = form.querySelector('.item-service-price');

                function syncFields() {
                    const selected = select.options[select.selectedIndex];
                    const defaultPrice = selected ? selected.getAttribute('data-default-price') : '';

                    if (select.value && defaultPrice !== '' && defaultPrice !== null) {
                        priceInput.value = defaultPrice;
                        priceInput.placeholder = defaultPrice;
                    } else if (select.value) {
                        priceInput.value = '';
                        priceInput.placeholder = '';
                    }
                }

                select.addEventListener('change', syncFields);
                syncFields();
            });
        </script>
    @endpush
@endonce
