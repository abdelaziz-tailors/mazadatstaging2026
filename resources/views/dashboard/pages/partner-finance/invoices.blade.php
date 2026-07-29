@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('subscriber_invoices') }} @endsection

@section('content')
@include('dashboard.partials.page-header', [
    'title' => TranslationHelper::translate('subscriber_invoices'),
    'icon' => 'fa-solid fa-file-invoice',
])

@php
    $pendingPct = $stats['total'] > 0 ? round($stats['pending'] / $stats['total'] * 100, 1) : 0;
    $paidPct = $stats['total'] > 0 ? round($stats['paid'] / $stats['total'] * 100, 1) : 0;
    $shippingPct = $stats['total'] > 0 ? round($stats['shipping'] / $stats['total'] * 100, 1) : 0;
@endphp
@include('dashboard.partials.stat-row', ['cards' => [
    [
        'icon' => 'fa-solid fa-clock',
        'value' => $stats['pending'],
        'label' => TranslationHelper::translate('pending'),
        'color' => 'warning',
        'trend' => ['direction' => 'down', 'text' => $pendingPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-circle-check',
        'value' => $stats['paid'],
        'label' => TranslationHelper::translate('paid'),
        'color' => 'success',
        'trend' => ['direction' => 'up', 'text' => $paidPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-truck',
        'value' => $stats['shipping'],
        'label' => TranslationHelper::translate('shipping'),
        'color' => 'info',
        'trend' => ['direction' => 'up', 'text' => $shippingPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-file-invoice',
        'value' => $stats['total'],
        'label' => TranslationHelper::translate('total_invoices'),
        'color' => 'primary',
        'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
    ],
]])

<div class="card md-wide-search">
    <div class="card-body">
        <form method="GET" class="row g-3 mb-3" id="invoicesFilterPanel">
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('search') }}</label>
                <input
                    type="text"
                    name="search"
                    class="form-control form-control-sm"
                    value="{{ request('search') }}"
                    placeholder="{{ TranslationHelper::translate('search_invoices_placeholder') }}"
                >
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('status_') }}</label>
                <select name="filter_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ TranslationHelper::translate('all') }}</option>
                    <option value="pending" {{ request('filter_status') === 'pending' ? 'selected' : '' }}>{{ TranslationHelper::translate('pending') }}</option>
                    <option value="confirmed" {{ request('filter_status') === 'confirmed' ? 'selected' : '' }}>{{ TranslationHelper::translate('confirmed') }}</option>
                    <option value="preparation" {{ request('filter_status') === 'preparation' ? 'selected' : '' }}>{{ TranslationHelper::translate('preparation') }}</option>
                    <option value="ready_for_delivery" {{ request('filter_status') === 'ready_for_delivery' ? 'selected' : '' }}>{{ TranslationHelper::translate('ready_for_delivery') }}</option>
                    <option value="shipping" {{ request('filter_status') === 'shipping' ? 'selected' : '' }}>{{ TranslationHelper::translate('shipping') }}</option>
                    <option value="delivered" {{ request('filter_status') === 'delivered' ? 'selected' : '' }}>{{ TranslationHelper::translate('delivered') }}</option>
                </select>
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('payment_status') }}</label>
                <select name="filter_payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ TranslationHelper::translate('all') }}</option>
                    <option value="paid" {{ request('filter_payment_status') === 'paid' ? 'selected' : '' }}>{{ TranslationHelper::translate('paid') }}</option>
                    <option value="unpaid" {{ request('filter_payment_status') === 'unpaid' ? 'selected' : '' }}>{{ TranslationHelper::translate('unpaid') }}</option>
                </select>
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('from') }} - {{ TranslationHelper::translate('to') }}</label>
                <div class="d-flex gap-1">
                    <input type="date" name="filter_date_from" class="form-control form-control-sm" value="{{ request('filter_date_from') }}" onchange="this.form.submit()">
                    <input type="date" name="filter_date_to" class="form-control form-control-sm" value="{{ request('filter_date_to') }}" onchange="this.form.submit()">
                </div>
            </div>
            <div class="col-xl-auto col-lg-4 col-md-6 col-12 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-magnifying-glass"></i> {{ TranslationHelper::translate('filter') }}
                </button>
                <a href="{{ route('admin.partner-finance.invoices') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-rotate-right"></i> {{ TranslationHelper::translate('reset') }}
                </a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>{{ TranslationHelper::translate('order_number') }}</th>
                    <th>{{ TranslationHelper::translate('auctions') }}</th>
                    <th>{{ TranslationHelper::translate('buyer') }}</th>
                    <th>{{ TranslationHelper::translate('order_total') }}</th>
                    <th>{{ TranslationHelper::translate('payment_status') }}</th>
                    <th>{{ TranslationHelper::translate('status_') }}</th>
                    <th>{{ TranslationHelper::translate('date') }}</th>
                    <th>{{ TranslationHelper::translate('actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->order_number }}</td>
                        <td>
                            {{ app()->getLocale() === 'ar'
                                ? ($invoice->liveVideo->title_ar ?? $invoice->liveVideo->title ?? '-')
                                : ($invoice->liveVideo->title ?? $invoice->liveVideo->title_ar ?? '-') }}
                        </td>
                        <td>{{ $invoice->buyer->name ?? '-' }}</td>
                        <td>{{ number_format((float) $invoice->total, 2) }}</td>
                        <td>{{ TranslationHelper::translate($invoice->payment_status ?? 'unpaid') }}</td>
                        <td>{{ TranslationHelper::translate($invoice->status ?? 'pending') }}</td>
                        <td>{{ optional($invoice->created_at)->format('Y-m-d H:i') }}</td>
                        <td>
                            <a class="md-icon-btn" href="{{ route('admin.orders.edit', $invoice->id) }}" title="{{ TranslationHelper::translate('view') }}">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">{{ TranslationHelper::translate('nothing_found') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection
