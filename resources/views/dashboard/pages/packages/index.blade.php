@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Packages') }} @endsection

@push('css')
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
<!--end::Page Vendor Stylesheets-->
@endpush


@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{TranslationHelper::translate('Packages') }}
                @if(Auth::guard('admin')->user()->can('add package'))
                    <a href='{{ route('admin.packages.create') }}' class='btn btn-primary float-end'><i class="fas fa-plus"></i> {{ TranslationHelper::translate('Package') }}</a>
                @endif
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>

                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Package') }}</li>
            </ul>
        </div>
    </div>
</div>

@include('dashboard.partials.stat-row', ['cards' => [
    [
        'icon' => 'fa-solid fa-gavel',
        'value' => $stats['max_auctions_limit'],
        'label' => TranslationHelper::translate('max_auctions_limit'),
        'color' => 'purple',
    ],
    [
        'icon' => 'fa-solid fa-sack-dollar',
        'value' => number_format($stats['avg_monthly_price'], 2),
        'label' => TranslationHelper::translate('avg_monthly_price'),
        'color' => 'warning',
    ],
    [
        'icon' => 'fa-solid fa-shield-halved',
        'value' => $stats['active'],
        'label' => TranslationHelper::translate('active_packages'),
        'color' => 'success',
        'trend' => ['direction' => 'up', 'text' => $stats['active_pct'] . '%'],
    ],
    [
        'icon' => 'fa-solid fa-box',
        'value' => $stats['total'],
        'label' => TranslationHelper::translate('total_packages'),
        'color' => 'primary',
        'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
    ],
]])

<div class="card md-wide-search">
    <div class="card-body">
        <div class="row g-3 mb-3" id="packagesFilterPanel">
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('subscription_type') }}</label>
                <select id="filter_subscription_type" class="form-select form-select-sm">
                    <option value="">{{ TranslationHelper::translate('all') }}</option>
                    <option value="monthly">{{ TranslationHelper::translate('Monthly') }}</option>
                    <option value="annual">{{ TranslationHelper::translate('Annual') }}</option>
                </select>
            </div>
            <div class="col-xl col-lg-4 col-md-6 col-12">
                <label class="form-label small mb-1">{{ TranslationHelper::translate('Status') }}</label>
                <select id="filter_status" class="form-select form-select-sm">
                    <option value="">{{ TranslationHelper::translate('all') }}</option>
                    <option value="1">{{ TranslationHelper::translate('Active') }}</option>
                    <option value="0">{{ TranslationHelper::translate('inactive') }}</option>
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
                        <th>{{ TranslationHelper::translate('name') }}</th>
                        {{-- <th>{{ TranslationHelper::translate('Coin') }}</th>
                        <th>{{ TranslationHelper::translate('Price') }}</th> --}}
                        <th>{{ TranslationHelper::translate('subscription_type') }}</th>
                        <th>{{ TranslationHelper::translate('auctions_limit') }}</th>
                        <th>{{ TranslationHelper::translate('monthly_price') }}</th>
                        <th>{{ TranslationHelper::translate('annual_price') }}</th>
                        <th>{{ TranslationHelper::translate('is_active') }}</th>
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
            url : "{!! route('admin.packages.getData') !!}",
            data: function (d) {
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
            {data: 'name', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            // {data: 'coin', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            // {data: 'price', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'subscription_type', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'auctions_limit', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'monthly_price', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'annual_price', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'is_active', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'action', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false}
        ],
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
            "searchPlaceholder": "{{ TranslationHelper::translate('search_packages_placeholder') }}",
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
        dom: '<"d-flex justify-content-between"<l><f>>rt<"d-flex justify-content-between"<"d-flex align-items-center"<><i>><p>>'
    });

    $('#filter_subscription_type, #filter_status, #filter_date_from, #filter_date_to').on('change', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_reset').on('click', function () {
        $('#filter_subscription_type, #filter_status').val('');
        $('#filter_date_from, #filter_date_to').val('');
        $('#data-table').DataTable().draw(true);
    });
</script>
@endsection
