@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Auctions') }} @endsection

@push('css')
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
<!--end::Page Vendor Stylesheets-->
@endpush


@section('content')
@include('dashboard.partials.page-header', [
    'title' => TranslationHelper::translate('Auctions'),
    'icon' => 'fa-solid fa-gavel',
    'actions' => '<a href="'.route('admin.videos.create').'" class="btn btn-primary"><i class="fas fa-plus"></i> '.TranslationHelper::translate('new Auction').'</a>',
])

@php
    $inProgressPct = $stats['total'] > 0 ? round($stats['in_progress'] / $stats['total'] * 100, 1) : 0;
    $upcomingPct = $stats['total'] > 0 ? round($stats['upcoming'] / $stats['total'] * 100, 1) : 0;
    $archivedPct = $stats['total'] > 0 ? round($stats['archived'] / $stats['total'] * 100, 1) : 0;
@endphp
@include('dashboard.partials.stat-row', ['cards' => [
    [
        'icon' => 'fa-solid fa-video',
        'value' => $stats['in_progress'],
        'label' => TranslationHelper::translate('in_progress'),
        'color' => 'success',
        'trend' => ['direction' => 'up', 'text' => $inProgressPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-clock',
        'value' => $stats['upcoming'],
        'label' => TranslationHelper::translate('upcoming'),
        'color' => 'warning',
        'trend' => ['direction' => 'up', 'text' => $upcomingPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-box-archive',
        'value' => $stats['archived'],
        'label' => TranslationHelper::translate('archived'),
        'color' => 'dark',
        'trend' => ['direction' => 'down', 'text' => $archivedPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-gavel',
        'value' => $stats['total'],
        'label' => TranslationHelper::translate('total_auctions'),
        'color' => 'primary',
        'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
    ],
]])

<div class="card md-wide-search">
    <div class="card-body">

        <div class="row g-3 mb-3" id="auctionsFilterPanel">
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('Status') }}</label>
                <select id="filter_status" class="form-select form-select-sm">
                    <option value="">{{ TranslationHelper::translate('all') }}</option>
                    <option value="start">{{ TranslationHelper::translate('in_progress') }}</option>
                    <option value="upcoming">{{ TranslationHelper::translate('upcoming') }}</option>
                    <option value="end">{{ TranslationHelper::translate('archived') }}</option>
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
                        <th>{{ TranslationHelper::translate('Auctions Title') }}</th>
                        <th>{{ TranslationHelper::translate('User') }}</th>
                        <th>{{ TranslationHelper::translate('start_date') }}</th>
                        <th>{{ TranslationHelper::translate('end_date') }}</th>
                        <th>{{ TranslationHelper::translate('Status') }}</th>
                        <th>{{ TranslationHelper::translate('products_count') }}</th>
                        <th>{{ TranslationHelper::translate('participations_count') }}</th>
                        <th>{{ TranslationHelper::translate('pieces') }}</th>
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
            url : "{!! route('admin.auctions.getData') !!}",
            data: function (d) {
                d.filter_status = $('#filter_status').val();
                d.filter_date_from = $('#filter_date_from').val();
                d.filter_date_to = $('#filter_date_to').val();
            },
            type: "POST",
            dataType: "JSON"
        },
        order: [[0, 'desc']], // Sort by 'id' column in descending order (change to 'asc' for ascending)

        columns: [
            {data: 'id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'title', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'user_name', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'start_date', 'searchable': false, 'orderable': false, 'exportable': true, 'printable': true},
            {data: 'end_date', 'searchable': false, 'orderable': false, 'exportable': true, 'printable': true},
            {data: 'status', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'products_count', 'searchable': false, 'orderable': false, 'exportable': true, 'printable': true},
            {data: 'participations_count', 'searchable': false, 'orderable': false, 'exportable': true, 'printable': true},
            {data: 'pieces_action', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'action', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false}

        ],
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
            "searchPlaceholder": "{{ TranslationHelper::translate('search_auctions_placeholder') }}",
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

    $('#filter_status, #filter_date_from, #filter_date_to').on('change', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_reset').on('click', function () {
        $('#filter_status').val('');
        $('#filter_date_from, #filter_date_to').val('');
        $('#data-table').DataTable().draw(true);
    });
</script>
@endsection
