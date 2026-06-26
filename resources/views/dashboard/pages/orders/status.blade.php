@php $order = $order ?? null; @endphp
@if($order && $order->status === 'shipping')
    <span class="badge rounded-pill bg-black inv-badge">{{ TranslationHelper::translate($order->status) }}</span>
@elseif($order && $order->payment_status === 'paid')
    <span class="badge rounded-pill bg-success inv-badge">{{ TranslationHelper::translate('paid') }}</span>
@else
    <span class="badge rounded-pill bg-danger inv-badge">{{ TranslationHelper::translate($order->status ?? 'pending') }}</span>
@endif
