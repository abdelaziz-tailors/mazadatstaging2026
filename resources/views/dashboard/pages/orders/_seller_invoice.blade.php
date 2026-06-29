@php
    $live = $order->liveVideo;
    $commissionPayer = $live?->commission_payer ?? 'buyer';
    $commissionPct = (float) ($live?->commission_amount ?? 0);
@endphp

<div class="mb-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">{{ TranslationHelper::translate('seller_order_summary') }}</h6>
        @if ($commissionPayer === 'seller' && $commissionPct > 0)
            <small class="text-muted">
                {{ TranslationHelper::translate('commission') }}: {{ $commissionPct }}%
            </small>
        @endif
    </div>

    @foreach ($sellerInvoices as $invoice)
        <div class="border rounded p-3 bg-light mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong>{{ TranslationHelper::translate('consignor_seller') }}: {{ $invoice['seller_name'] }}</strong>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-3">
                    <thead>
                        <tr>
                            <th>{{ TranslationHelper::translate('item_title') }}</th>
                            <th>{{ TranslationHelper::translate('finished_price') }}</th>
                            @if ($commissionPayer === 'seller')
                                <th>{{ TranslationHelper::translate('commission') }}</th>
                            @endif
                            <th>{{ TranslationHelper::translate('service_fee') }}</th>
                            <th>{{ TranslationHelper::translate('piece_services_total') }}</th>
                            <th>{{ TranslationHelper::translate('net_to_seller') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice['lines'] as $line)
                            <tr>
                                <td>{{ $line['title'] }}</td>
                                <td>{{ number_format($line['price'], 2) }}</td>
                                @if ($commissionPayer === 'seller')
                                    <td>{{ number_format($line['commission'], 2) }}</td>
                                @endif
                                <td>{{ number_format($line['service_fee'], 2) }}</td>
                                <td>{{ number_format($line['piece_services'], 2) }}</td>
                                <td><strong>{{ number_format($line['net'], 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary">
                            <th>{{ TranslationHelper::translate('gross_sales') }}</th>
                            <th>{{ number_format($invoice['gross'], 2) }}</th>
                            @if ($commissionPayer === 'seller')
                                <th>{{ number_format($invoice['commission'], 2) }}</th>
                            @endif
                            <th>{{ number_format($invoice['service_fee'], 2) }}</th>
                            <th>{{ number_format($invoice['piece_services'], 2) }}</th>
                            <th><strong>{{ number_format($invoice['net'], 2) }}</strong></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endforeach
</div>
