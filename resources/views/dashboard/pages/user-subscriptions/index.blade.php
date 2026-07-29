@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('User Subscriptions') }} @endsection

@push('css')
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
<!--end::Page Vendor Stylesheets-->
@endpush


@section('content')
@include('dashboard.partials.page-header', [
    'title' => TranslationHelper::translate('User Subscriptions'),
    'icon' => 'fa-solid fa-briefcase',
])

@php
    $approvedPct = $stats['total'] > 0 ? round($stats['approved'] / $stats['total'] * 100, 1) : 0;
    $pendingPct = $stats['total'] > 0 ? round($stats['pending'] / $stats['total'] * 100, 1) : 0;
    $rejectedPct = $stats['total'] > 0 ? round($stats['rejected'] / $stats['total'] * 100, 1) : 0;
@endphp
@include('dashboard.partials.stat-row', ['cards' => [
    [
        'icon' => 'fa-solid fa-circle-check',
        'value' => $stats['approved'],
        'label' => TranslationHelper::translate('Approved'),
        'color' => 'success',
        'trend' => ['direction' => 'up', 'text' => $approvedPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-clock',
        'value' => $stats['pending'],
        'label' => TranslationHelper::translate('Pending'),
        'color' => 'warning',
        'trend' => ['direction' => 'down', 'text' => $pendingPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-circle-xmark',
        'value' => $stats['rejected'],
        'label' => TranslationHelper::translate('Rejected'),
        'color' => 'danger',
        'trend' => ['direction' => 'down', 'text' => $rejectedPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-briefcase',
        'value' => $stats['total'],
        'label' => TranslationHelper::translate('total_subscriptions'),
        'color' => 'primary',
        'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
    ],
]])

<div class="card md-wide-search">
    <div class="card-body">
        <div class="row g-3 mb-3" id="userSubscriptionsFilterPanel">
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('user') }}</label>
                <input type="text" id="filter_user" class="form-control form-control-sm">
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('subscription_type') }}</label>
                <select id="filter_subscription_type" class="form-select form-select-sm">
                    <option value="">{{ TranslationHelper::translate('all') }}</option>
                    <option value="monthly">{{ TranslationHelper::translate('Monthly') }}</option>
                    <option value="annual">{{ TranslationHelper::translate('Annual') }}</option>
                </select>
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('approval_status') }}</label>
                <select id="filter_status" class="form-select form-select-sm">
                    <option value="">{{ TranslationHelper::translate('all') }}</option>
                    <option value="pending">{{ TranslationHelper::translate('Pending') }}</option>
                    <option value="approved">{{ TranslationHelper::translate('Approved') }}</option>
                    <option value="rejected">{{ TranslationHelper::translate('Rejected') }}</option>
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

        <!--begin::Table-->
        <div class="table-responsive">
            <table id="data-table" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ TranslationHelper::translate('user') }}</th>
                        <th>{{ TranslationHelper::translate('subscription_type') }}</th>
                        <th>{{ TranslationHelper::translate('auctions_limit') }}</th>
                        <th>{{ TranslationHelper::translate('remaining_auctions') }}</th>
                        <th>{{ TranslationHelper::translate('price') }}</th>
                        <th>{{ TranslationHelper::translate('expires_at') }}</th>
                        <th>{{ TranslationHelper::translate('approval_status') }}</th>
                        <th>{{ TranslationHelper::translate('status') }}</th>
                        <th>{{ TranslationHelper::translate('created_at') }}</th>
                        <th>{{ TranslationHelper::translate('actions') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->

@endsection

@section('scripts_lib')
<script src="{{asset('dashboard/plugins/datatables/datatables.min.js')}}"></script>
<script>
    $('#data-table').DataTable({
        autoFill: true,
        processing: true,
        serverSide: true,
        search: {
            "caseInsensitive": true,
            "smart": true
        },
        ajax: {
            url : "{!! route('admin.user-subscriptions.getData') !!}",
            data: function (d) {
                d.filter_user = $('#filter_user').val();
                d.filter_subscription_type = $('#filter_subscription_type').val();
                d.filter_status = $('#filter_status').val();
                d.filter_date_from = $('#filter_date_from').val();
                d.filter_date_to = $('#filter_date_to').val();
            },
            type: "POST",
            dataType: "JSON"
        },
        columns: [
            {data: 'id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'user_id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'subscription_type', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'auctions_limit', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'remaining_auctions', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'price', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'expires_at', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'status', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'is_active', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'created_at', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'action', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false}
        ],
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
            "searchPlaceholder": "{{ TranslationHelper::translate('search_user_subscriptions_placeholder') }}",
            "lengthMenu": "{{ TranslationHelper::translate('display') }} _MENU_ {{ TranslationHelper::translate('records_per_page') }}",
            "zeroRecords": "{{ TranslationHelper::translate('nothing_found') }}",
            "info": "{{ TranslationHelper::translate('showing_page') }} _PAGE_ {{ TranslationHelper::translate('of') }} _PAGES_",
            "infoEmpty": "{{ TranslationHelper::translate('nothing_found') }}",
            "infoFiltered": "({{ TranslationHelper::translate('filtered_from') }} _MAX_)",
            "loadingRecords": "{{TranslationHelper::translate('loading')}}...",
            "paginate": {
                "previous": @if(app()->getLocale() == 'ar') "<i class='fas fa-angle-right'></i>" @else "<i class='fas fa-angle-left'></i>" @endif,
                "next": @if(app()->getLocale() == 'ar') "<i class='fas fa-angle-left'></i>" @else "<i class='fas fa-angle-right'></i>" @endif
            }
        },
        dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3 px-2"<l><f>>rt<"d-flex justify-content-between px-2"<"d-flex align-items-center"<><i>><p>>'
    });

    $('#filter_user').on('keyup', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_subscription_type, #filter_status, #filter_date_from, #filter_date_to').on('change', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_reset').on('click', function () {
        $('#filter_user, #filter_date_from, #filter_date_to').val('');
        $('#filter_subscription_type, #filter_status').val('');
        $('#data-table').DataTable().draw(true);
    });
</script>
@endsection

