@php
    $live = $order->liveVideo;
    $cityName = '';
    if ($order->shippingCity?->name) {
        $decoded = json_decode($order->shippingCity->name, true);
        $cityName = is_array($decoded)
            ? ($decoded[app()->getLocale()] ?? $decoded['ar'] ?? $decoded['en'] ?? '')
            : $order->shippingCity->name;
    }
    $auctionTitle = app()->getLocale() === 'ar'
        ? ($live->title_ar ?? $live->title ?? '—')
        : ($live->title ?? $live->title_ar ?? '—');
@endphp

<div class="col-12 d-flex">
    <div class="card flex-fill">
        <div class="card-body">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="border rounded p-3 bg-light">
                        <h6 class="mb-3">{{ TranslationHelper::translate('Order') }} {{ $order->order_number }}</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted d-block">{{ TranslationHelper::translate('order_number') }}</small>
                                <strong>{{ $order->order_number }}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">{{ TranslationHelper::translate('Auctions') }}</small>
                                <strong>{{ $auctionTitle }}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">{{ TranslationHelper::translate('Buyer') }}</small>
                                <strong>{{ $order->buyer->name ?? '—' }}</strong>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <small class="text-muted d-block">{{ TranslationHelper::translate('items') }}</small>
                                <strong>{{ $order->items->count() }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-4">
                    <div class="border rounded p-3 bg-light">
                        <h6 class="mb-3">{{ TranslationHelper::translate('buyer_order_summary') }}</h6>
                        <div class="row">
                            <div class="col-6 col-md-3">
                                <small class="text-muted d-block">{{ TranslationHelper::translate('sub_total') }}</small>
                                <strong>{{ number_format($order->subtotal, 2) }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <small class="text-muted d-block">{{ TranslationHelper::translate('tax') }}</small>
                                <strong>{{ number_format($order->tax_value, 2) }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <small class="text-muted d-block">{{ TranslationHelper::translate('commission') }}</small>
                                <strong>{{ number_format($order->commission_value, 2) }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <small class="text-muted d-block">{{ TranslationHelper::translate('order_total') }}</small>
                                <strong>{{ number_format($order->total, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 form-group">
                    {!! Form::label('shipping_address', TranslationHelper::translate('Shipping Address'), ['class' => 'form-label']) !!}
                    {!! Form::textarea('shipping_address_display', $order->shipping_address ?? '', ['class' => 'form-control', 'readonly', 'rows' => 3]) !!}
                </div>

                <div class="col-lg-6 form-group">
                    {!! Form::label('city', TranslationHelper::translate('City'), ['class' => 'form-label']) !!}
                    {!! Form::text('city_display', $cityName, ['class' => 'form-control', 'readonly']) !!}
                </div>

                <div class="col-6 form-group">
                    <label class="form-label">{{ TranslationHelper::translate('payment status') }}</label>
                    <div class="form-check">
                        {!! Form::radio('payment_status', 'paid', $order->payment_status === 'paid', ['class' => 'form-check-input', 'id' => 'payment_status_paid']) !!}
                        <label class="form-check-label" for="payment_status_paid">{{ TranslationHelper::translate('paid') }}</label>
                    </div>
                    <div class="form-check">
                        {!! Form::radio('payment_status', 'unpaid', $order->payment_status === 'unpaid', ['class' => 'form-check-input', 'id' => 'payment_status_unpaid']) !!}
                        <label class="form-check-label" for="payment_status_unpaid">{{ TranslationHelper::translate('unpaid') }}</label>
                    </div>
                </div>

                <div class="col-lg-6 form-group">
                    <label class="form-label">{{ TranslationHelper::translate('Status ') }}</label>
                    <select class="form-control" id="status" name="status">
                        @foreach (['pending', 'confirmed', 'preparation', 'ready_for_delivery', 'shipping', 'delivered'] as $statusOption)
                            <option value="{{ $statusOption }}" @if($order->status === $statusOption) selected @endif>
                                {{ TranslationHelper::translate($statusOption === 'ready_for_delivery' ? 'Ready for delivery' : $statusOption) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 form-group">
                    <label class="form-label">{{ TranslationHelper::translate('payment_proof') }}</label>
                    @php $proofPath = $order->payment_proof; @endphp
                    @if (!empty($proofPath) && file_exists(public_path($proofPath)))
                        @php $proofExt = strtolower(pathinfo($proofPath, PATHINFO_EXTENSION)); @endphp
                        <div class="border rounded p-3 bg-light">
                            @if (in_array($proofExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true))
                                <a href="{{ asset($proofPath) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset($proofPath) }}" alt="" class="img-fluid rounded" style="max-height: 360px;">
                                </a>
                            @else
                                <a href="{{ asset($proofPath) }}" target="_blank" rel="noopener" class="btn btn-primary">
                                    <i class="fa fa-file-pdf"></i> {{ TranslationHelper::translate('view_payment_proof') }}
                                </a>
                            @endif
                        </div>
                    @else
                        <p class="text-muted mb-0">{{ TranslationHelper::translate('no_payment_proof_yet') }}</p>
                    @endif
                </div>

                <div class="col-12">
                    <h6 class="mb-3">{{ TranslationHelper::translate('items') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    {{-- <th>#</th> --}}
                                    <th>{{ TranslationHelper::translate('item_title') }}</th>
                                    {{-- <th>{{ TranslationHelper::translate('Category') }}</th> --}}
                                    {{-- <th>{{ TranslationHelper::translate('age') }}</th> --}}
                                    {{-- <th>{{ TranslationHelper::translate('weight') }}</th> --}}
                                    <th>{{ TranslationHelper::translate('buyer') }}</th>
                                    <th>{{ TranslationHelper::translate('finished price') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($order->items as $orderItem)
                                    @php $item = $orderItem->liveVideoItem; @endphp
                                    @if ($item)
                                        <tr>
                                            {{-- <td>{{ $item->id }}</td> --}}
                                            <td>{{ app()->getLocale() === 'ar' ? ($item->title_ar ?? $item->title) : ($item->title ?? $item->title_ar) }}</td>
                                            {{-- <td>{{ $item->categoryData->name ?? '—' }}</td> --}}
                                            {{-- <td>{{ $item->primaryPiece()?->age ?? '—' }}</td> --}}
                                            {{-- <td>{{ $item->primaryPiece()?->weight ?? '—' }}</td> --}}
                                            <td>{{ $order->buyer->name ?? '—' }}</td>
                                            <td>{{ number_format($orderItem->finished_price, 2) }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">{{ TranslationHelper::translate('nothing_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <button type="submit" name="action" value="save" class="btn btn-primary mt-3">
                {{ TranslationHelper::translate('save') }}
            </button>
        </div>
    </div>
</div>
