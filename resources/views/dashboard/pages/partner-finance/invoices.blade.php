@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('subscriber_invoices') }} @endsection


@push('css')
<style>
/* Mazadat invoices responsive v1 */

.md-inv-card,
.md-inv-card > .card-body {
    width: 100%;
    max-width: 100%;
    min-width: 0;
}

/* Filters */

.md-inv-filters {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    width: 100%;
    padding: 20px;
    margin-bottom: 22px;
    direction: rtl;

    background: #f9fbf9;
    border: 1px solid #e4ece6;
    border-radius: 14px;
}

.md-inv-filters > div {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
}

.md-inv-filters label {
    display: block;
    margin-bottom: 8px !important;
    color: #627469;
    font-size: 13px;
    font-weight: 600;
}

.md-inv-filters .form-control,
.md-inv-filters .form-select {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    min-height: 42px;

    background-color: #fff;
    border: 1px solid #dce6df;
    border-radius: 9px;
    box-sizing: border-box;
}

.md-inv-filters .form-control:focus,
.md-inv-filters .form-select:focus {
    border-color: #3d5f45;
    box-shadow: 0 0 0 3px rgba(61, 95, 69, .10);
}

/* Date inputs stay balanced. */

.md-inv-filters .d-flex.gap-1 {
    width: 100%;
    min-width: 0;
    gap: 10px !important;
}

.md-inv-filters .d-flex.gap-1 > input {
    flex: 1 1 0;
    min-width: 0;
}

/* Filter / reset buttons */

.md-inv-filters > div:last-child {
    display: flex !important;
    align-items: end !important;
    gap: 10px !important;
}

.md-inv-filters > div:last-child .btn {
    flex: 1 1 0;
    min-height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    white-space: nowrap;
}

/* Table */

.md-inv-table-scroll {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;

    overflow-x: auto;
    overflow-y: hidden;

    -webkit-overflow-scrolling: touch;
    overscroll-behavior-x: contain;

    scrollbar-width: thin;
    scrollbar-color: #b8c9bd #f0f4f1;
}

.md-inv-table-scroll table {
    width: 100% !important;
    min-width: 980px;
    margin-bottom: 0;
}

.md-inv-table-scroll th,
.md-inv-table-scroll td {
    vertical-align: middle;
    white-space: nowrap;
}

.md-inv-table-scroll th:nth-child(2),
.md-inv-table-scroll td:nth-child(2),
.md-inv-table-scroll th:nth-child(3),
.md-inv-table-scroll td:nth-child(3) {
    white-space: normal;
    min-width: 140px;
}

.md-inv-table-scroll th:first-child,
.md-inv-table-scroll td:first-child {
    min-width: 170px;
}

.md-inv-table-scroll th:last-child,
.md-inv-table-scroll td:last-child {
    min-width: 75px;
    text-align: center;
}

.md-inv-mobile-hint {
    display: none;
}

/* Pagination */

.md-inv-card .pagination {
    margin-bottom: 0;
}

/* Tablet */

@media (max-width: 1199.98px) {
    .md-inv-filters {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Mobile */

@media (max-width: 767.98px) {

    .md-inv-card > .card-body {
        padding: 12px;
    }

    .md-inv-filters {
        grid-template-columns: minmax(0, 1fr);
        gap: 14px;
        padding: 14px;
    }

    .md-inv-filters .form-control,
    .md-inv-filters .form-select {
        font-size: 16px;
    }

    .md-inv-filters .d-flex.gap-1 {
        flex-direction: column;
    }

    .md-inv-filters > div:last-child {
        flex-direction: column;
        align-items: stretch !important;
    }

    .md-inv-filters > div:last-child .btn {
        width: 100%;
        flex: 0 0 auto;
    }

    .md-inv-mobile-hint {
        display: flex;
        align-items: center;
        gap: 8px;
        direction: rtl;

        margin-bottom: 12px;
        padding: 10px 12px;

        color: #3d5f45;
        background: #edf5ef;
        border-radius: 8px;
        font-size: 12px;
    }

    .md-inv-table-scroll table {
        min-width: 960px;
        font-size: 12px;
    }

    .md-inv-card > .card-body > .mt-3 {
        overflow-x: auto;
    }
}
</style>
@endpush

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

<div class="card md-wide-search md-inv-card">
    <div class="card-body">
        <form method="GET" class="md-inv-filters" id="invoicesFilterPanel">
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
        <div class="md-inv-mobile-hint">
            <i class="fa-solid fa-arrows-left-right"></i>
            اسحب الجدول يمينًا ويسارًا لعرض جميع البيانات
        </div>

        <div class="md-inv-table-scroll">
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
