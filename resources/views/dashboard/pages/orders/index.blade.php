@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Orders') }} @endsection

@push('css')
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
@endpush


@section('css_lib')
<style>
/* Mazadat orders responsive v1 */

.md-orders-card,
.md-orders-card > .card-body,
.md-orders-table-host,
.md-orders-card .dataTables_wrapper {
    width: 100%;
    max-width: 100%;
    min-width: 0;
}

/* Filters */

.md-orders-filters {
    padding: 20px;
    margin-bottom: 22px;

    background: #f9fbf9;
    border: 1px solid #e4ece6;
    border-radius: 14px;
}

.md-orders-filters-title {
    display: flex;
    align-items: center;
    gap: 9px;

    margin: 0 0 18px;

    color: #3d5f45;
    font-size: 16px;
    font-weight: 700;
}

.md-orders-filter-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    direction: rtl;
}

.md-orders-field {
    min-width: 0;
}

.md-orders-field label {
    display: block;
    margin-bottom: 8px;

    color: #627469;
    font-size: 13px;
    font-weight: 600;
}

.md-orders-field .form-control,
.md-orders-field .form-select {
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

.md-orders-field .form-control:focus,
.md-orders-field .form-select:focus {
    border-color: #3d5f45;
    box-shadow: 0 0 0 3px rgba(61, 95, 69, .10);
}

.md-orders-reset-field {
    display: flex;
    align-items: end;
    min-width: 0;
}

.md-orders-reset {
    width: 100%;
    min-height: 42px;

    color: #3d5f45;
    background: #eaf2ec;
    border: 1px solid #cbded1;
    border-radius: 9px;

    font-weight: 600;
}

.md-orders-reset:hover {
    color: #fff;
    background: #3d5f45;
    border-color: #3d5f45;
}

/* DataTables toolbar */

.md-orders-toolbar,
.md-orders-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;

    gap: 14px;
    direction: rtl;
}

.md-orders-toolbar {
    margin-bottom: 18px;
}

.md-orders-footer {
    margin-top: 16px;
}

.md-orders-search {
    flex: 1 1 300px;
    min-width: 0;
}

.md-orders-length {
    flex: 0 1 auto;
}

.md-orders-search .dataTables_filter,
.md-orders-length .dataTables_length {
    float: none !important;
    width: 100%;
    margin: 0;
}

.md-orders-search .dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 10px;

    width: 100%;
    margin: 0;
}

.md-orders-search .dataTables_filter input {
    flex: 1 1 180px;

    width: 100%;
    min-width: 0;
    min-height: 42px;

    margin: 0 !important;

    border: 1px solid #dce6df;
    border-radius: 9px;
}

.md-orders-length .dataTables_length label {
    display: flex;
    align-items: center;
    gap: 8px;

    margin: 0;
}

.md-orders-length .dataTables_length select {
    min-width: 65px;
    min-height: 42px;
    margin: 0;
}

/* Only the order table scrolls */

.md-orders-table-scroll {
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

.md-orders-table-scroll #data-table {
    width: 100% !important;
    min-width: 1180px;
    table-layout: auto;
}

.md-orders-table-scroll #data-table th,
.md-orders-table-scroll #data-table td {
    vertical-align: middle;
    white-space: nowrap;
}

/* Order number */
.md-orders-table-scroll #data-table th:nth-child(1),
.md-orders-table-scroll #data-table td:nth-child(1) {
    min-width: 170px;
}

/* Auction title */
.md-orders-table-scroll #data-table th:nth-child(2),
.md-orders-table-scroll #data-table td:nth-child(2) {
    min-width: 165px;
    white-space: normal;
}

/* Buyer */
.md-orders-table-scroll #data-table th:nth-child(7),
.md-orders-table-scroll #data-table td:nth-child(7) {
    min-width: 120px;
    white-space: normal;
}

/* Payment proof */

.md-orders-table-scroll #data-table th:nth-child(9),
.md-orders-table-scroll #data-table td:nth-child(9) {
    min-width: 90px;
    text-align: center;
}

.md-orders-table-scroll #data-table td:nth-child(9) img {
    display: block;

    width: 60px !important;
    height: 60px !important;
    max-width: 60px !important;

    margin: auto;

    object-fit: cover;
    border-radius: 9px;
}

/* Actions */

.md-orders-table-scroll #data-table th:nth-child(10),
.md-orders-table-scroll #data-table td:nth-child(10) {
    min-width: 75px;
    text-align: center;
}

/* Tablet */

@media (max-width: 1199.98px) {
    .md-orders-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Mobile */

@media (max-width: 767.98px) {

    .md-orders-card > .card-body {
        padding: 12px;
    }

    .md-orders-filters {
        padding: 14px;
    }

    .md-orders-filter-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 14px;
    }

    .md-orders-field .form-control,
    .md-orders-field .form-select {
        font-size: 16px;
    }

    .md-orders-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .md-orders-search,
    .md-orders-length {
        width: 100%;
        flex: 0 0 auto;
    }

    .md-orders-search .dataTables_filter label {
        flex-direction: column;
        align-items: stretch;
        text-align: right;
    }

    .md-orders-search .dataTables_filter input {
        width: 100%;
        max-width: 100%;
    }

    .md-orders-footer {
        gap: 12px;
    }

    .md-orders-table-scroll #data-table {
        min-width: 1160px;
        font-size: 12px;
    }

    .md-orders-table-scroll #data-table td:nth-child(9) img {
        width: 52px !important;
        height: 52px !important;
        max-width: 52px !important;
    }
}
</style>
@endsection

@section('content')
@include('dashboard.partials.page-header', [
    'title' => TranslationHelper::translate('Orders'),
    'icon' => 'fa-solid fa-cart-shopping',
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
        'icon' => 'fa-solid fa-clipboard-list',
        'value' => $stats['total'],
        'label' => TranslationHelper::translate('total_orders'),
        'color' => 'primary',
        'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
    ],
]])

<div class="card md-wide-search md-orders-card">
    <div class="card-body">

        <div id="ordersFilterPanel" class="md-orders-filters">

            <h5 class="md-orders-filters-title">
                <i class="fa-solid fa-sliders"></i>
                البحث والتصفية
            </h5>

            <div class="md-orders-filter-grid">

                <div class="md-orders-field">
                    <label for="filter_order_number">
                        {{ TranslationHelper::translate('order_number') }}
                    </label>
                    <input
                        type="text"
                        id="filter_order_number"
                        class="form-control"
                    >
                </div>

                <div class="md-orders-field">
                    <label for="filter_buyer">
                        {{ TranslationHelper::translate('Buyer') }}
                    </label>
                    <input
                        type="text"
                        id="filter_buyer"
                        class="form-control"
                    >
                </div>

                <div class="md-orders-field">
                    <label for="filter_status">
                        {{ TranslationHelper::translate('Status') }}
                    </label>
                    <select id="filter_status" class="form-select">
                        <option value="">{{ TranslationHelper::translate('all') }}</option>
                        <option value="pending">{{ TranslationHelper::translate('pending') }}</option>
                        <option value="confirmed">{{ TranslationHelper::translate('confirmed') }}</option>
                        <option value="preparation">{{ TranslationHelper::translate('preparation') }}</option>
                        <option value="ready_for_delivery">{{ TranslationHelper::translate('ready_for_delivery') }}</option>
                        <option value="shipping">{{ TranslationHelper::translate('shipping') }}</option>
                        <option value="delivered">{{ TranslationHelper::translate('delivered') }}</option>
                    </select>
                </div>

                <div class="md-orders-field">
                    <label for="filter_payment_status">
                        {{ TranslationHelper::translate('payment_status') }}
                    </label>
                    <select id="filter_payment_status" class="form-select">
                        <option value="">{{ TranslationHelper::translate('all') }}</option>
                        <option value="paid">{{ TranslationHelper::translate('paid') }}</option>
                        <option value="unpaid">{{ TranslationHelper::translate('unpaid') }}</option>
                    </select>
                </div>

                <div class="md-orders-field">
                    <label for="filter_date_from">
                        {{ TranslationHelper::translate('from') }}
                    </label>
                    <input
                        type="date"
                        id="filter_date_from"
                        class="form-control"
                    >
                </div>

                <div class="md-orders-field">
                    <label for="filter_date_to">
                        {{ TranslationHelper::translate('to') }}
                    </label>
                    <input
                        type="date"
                        id="filter_date_to"
                        class="form-control"
                    >
                </div>

                <div class="md-orders-reset-field">
                    <button
                        type="button"
                        id="filter_reset"
                        class="btn md-orders-reset"
                    >
                        <i class="fa-solid fa-rotate-right"></i>
                        {{ TranslationHelper::translate('reset') }}
                    </button>
                </div>

            </div>
        </div>

        <div class="md-orders-table-host">
            <table id="data-table" class="table">
                <thead>
                    <tr>
                        <th>{{ TranslationHelper::translate('order_number') }}</th>
                        <th>{{ TranslationHelper::translate('Auctions Title') }}</th>
                        <th>{{ TranslationHelper::translate('items') }}</th>
                        <th>{{ TranslationHelper::translate('Status') }}</th>
                        <th>{{ TranslationHelper::translate('City') }}</th>
                        <th>{{ TranslationHelper::translate('order_total') }}</th>
                        <th>{{ TranslationHelper::translate('Buyer') }}</th>
                        <th>{{ TranslationHelper::translate('order_date') }}</th>
                        <th>{{ TranslationHelper::translate('payment_proof') }}</th>
                        <th>{{ TranslationHelper::translate('actions') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts_lib')
<script src="{{asset('dashboard/plugins/datatables/datatables.min.js')}}"></script>
<script>
    $('#data-table').DataTable({
        autoFill: true,
        processing: true,
        serverSide: true,
        search: {
            caseInsensitive: true,
            smart: true
        },
        ajax: {
            url: "{!! route('admin.orders.getData') !!}",
            data: function (d) {
                d.filter_order_number = $('#filter_order_number').val();
                d.filter_buyer = $('#filter_buyer').val();
                d.filter_status = $('#filter_status').val();
                d.filter_payment_status = $('#filter_payment_status').val();
                d.filter_date_from = $('#filter_date_from').val();
                d.filter_date_to = $('#filter_date_to').val();
            },
            type: "POST",
            dataType: "JSON"
        },
        order: [[0, 'desc']],
        columns: [
            {data: 'order_number', name: 'order_number', searchable: true, orderable: true},
            {data: 'auction_title', searchable: false, orderable: false},
            {data: 'items_count', searchable: false, orderable: false},
            {data: 'status', searchable: false, orderable: false},
            {data: 'city', searchable: false, orderable: false},
            {data: 'total', searchable: false, orderable: true},
            {data: 'buyer', searchable: false, orderable: false},
            {data: 'order_date', searchable: false, orderable: true},
            {data: 'payment_proof', searchable: false, orderable: false},
            {data: 'action', searchable: false, orderable: false}
        ],
        language: {
            search: "{{ TranslationHelper::translate('search') }}",
            searchPlaceholder: "{{ TranslationHelper::translate('search_orders_placeholder') }}",
            lengthMenu: "{{ TranslationHelper::translate('display') }} _MENU_ {{ TranslationHelper::translate('records_per_page') }}",
            zeroRecords: "{{ TranslationHelper::translate('nothing_found') }}",
            info: "{{ TranslationHelper::translate('showing_page') }} _PAGE_ {{ TranslationHelper::translate('of') }} _PAGES_",
            infoEmpty: "{{ TranslationHelper::translate('nothing_found') }}",
            infoFiltered: "({{ TranslationHelper::translate('filtered_from') }} _MAX_)",
            loadingRecords: "{{ TranslationHelper::translate('loading') }}...",
            paginate: {
                previous: @if(app()->getLocale() == 'ar') "<i class='fas fa-angle-right'></i>" @else "<i class='fas fa-angle-left'></i>" @endif,
                next: @if(app()->getLocale() == 'ar') "<i class='fas fa-angle-left'></i>" @else "<i class='fas fa-angle-right'></i>" @endif
            }
        },
        dom: '<"md-orders-toolbar"<"md-orders-search"f><"md-orders-length"l>>r<"md-orders-table-scroll"t><"md-orders-footer"ip>'
    });

    $('#filter_order_number, #filter_buyer').on('keyup', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_status, #filter_payment_status, #filter_date_from, #filter_date_to').on('change', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_reset').on('click', function () {
        $('#filter_order_number, #filter_buyer, #filter_date_from, #filter_date_to').val('');
        $('#filter_status, #filter_payment_status').val('');

        $('.md-orders-search input[type="search"]').val('');

        $('#data-table').DataTable().search('').draw(true);
    });
</script>
@endsection
