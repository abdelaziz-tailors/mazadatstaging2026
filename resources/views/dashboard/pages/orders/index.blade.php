@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Orders') }} @endsection

@push('css')
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
@endpush

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

<div class="card md-wide-search">
    <div class="card-body">
        <div class="row g-3 mb-3" id="ordersFilterPanel">
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('order_number') }}</label>
                <input type="text" id="filter_order_number" class="form-control form-control-sm">
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('Buyer') }}</label>
                <input type="text" id="filter_buyer" class="form-control form-control-sm">
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('Status') }}</label>
                <select id="filter_status" class="form-select form-select-sm">
                    <option value="">{{ TranslationHelper::translate('all') }}</option>
                    <option value="pending">{{ TranslationHelper::translate('pending') }}</option>
                    <option value="confirmed">{{ TranslationHelper::translate('confirmed') }}</option>
                    <option value="preparation">{{ TranslationHelper::translate('preparation') }}</option>
                    <option value="ready_for_delivery">{{ TranslationHelper::translate('ready_for_delivery') }}</option>
                    <option value="shipping">{{ TranslationHelper::translate('shipping') }}</option>
                    <option value="delivered">{{ TranslationHelper::translate('delivered') }}</option>
                </select>
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('payment_status') }}</label>
                <select id="filter_payment_status" class="form-select form-select-sm">
                    <option value="">{{ TranslationHelper::translate('all') }}</option>
                    <option value="paid">{{ TranslationHelper::translate('paid') }}</option>
                    <option value="unpaid">{{ TranslationHelper::translate('unpaid') }}</option>
                </select>
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('from') }} - {{ TranslationHelper::translate('to') }}</label>
                <div class="d-flex gap-1">
                    <input type="date" id="filter_date_from" class="form-control form-control-sm">
                    <input type="date" id="filter_date_to" class="form-control form-control-sm">
                </div>
            </div>
            <div class="col-xl-auto col-lg-4 col-md-6 col-12 d-flex align-items-end">
                <button type="button" id="filter_reset" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="fa-solid fa-rotate-right"></i> {{ TranslationHelper::translate('reset') }}
                </button>
            </div>
        </div>

        <div class="table-responsive">
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
        dom: '<"d-flex justify-content-between"<l><f>>rt<"d-flex justify-content-between"<"d-flex align-items-center"<><i>><p>>'
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
        $('#data-table').DataTable().draw(true);
    });
</script>
@endsection
