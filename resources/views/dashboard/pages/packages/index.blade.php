@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Packages') }} @endsection

@push('css')
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
<!--end::Page Vendor Stylesheets-->
@endpush



@section('css_lib')
<style>
/* Mazadat packages responsive v1 */

.md-pkg-card,
.md-pkg-card > .card-body,
.md-pkg-table-host,
.md-pkg-card .dataTables_wrapper {
    width: 100%;
    max-width: 100%;
    min-width: 0;
}

/* Filters */

.md-pkg-filters {
    background: #f9fbf9;
    border: 1px solid #e4ece6;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 22px;
}

.md-pkg-filters-title {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0 0 20px;
    color: #3d5f45;
    font-size: 16px;
    font-weight: 700;
}

.md-pkg-filter-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    direction: rtl;
}

.md-pkg-field {
    min-width: 0;
}

.md-pkg-field label {
    display: block;
    margin-bottom: 8px;
    color: #627469;
    font-size: 13px;
    font-weight: 600;
}

.md-pkg-field .form-control,
.md-pkg-field .form-select {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    min-height: 42px;
    border: 1px solid #dce6df;
    border-radius: 9px;
    background-color: #fff;
    box-sizing: border-box;
}

.md-pkg-field .form-control:focus,
.md-pkg-field .form-select:focus {
    border-color: #3d5f45;
    box-shadow: 0 0 0 3px rgba(61, 95, 69, .10);
}

.md-pkg-reset-field {
    display: flex;
    align-items: end;
    min-width: 0;
}

.md-pkg-reset {
    width: 100%;
    min-height: 42px;
    color: #3d5f45;
    background: #eaf2ec;
    border: 1px solid #cbded1;
    border-radius: 9px;
    font-weight: 600;
}

.md-pkg-reset:hover {
    color: #fff;
    background: #3d5f45;
    border-color: #3d5f45;
}

/* Search and page length stay above the table. */

.md-pkg-toolbar,
.md-pkg-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    direction: rtl;
}

.md-pkg-toolbar {
    margin-bottom: 18px;
}

.md-pkg-footer {
    margin-top: 16px;
}

.md-pkg-search {
    flex: 1 1 300px;
    min-width: 0;
}

.md-pkg-length {
    flex: 0 1 auto;
}

.md-pkg-search .dataTables_filter,
.md-pkg-length .dataTables_length {
    float: none !important;
    width: 100%;
    margin: 0;
    text-align: right;
}

.md-pkg-search .dataTables_filter label {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    width: 100%;
    margin: 0;
}

.md-pkg-search .dataTables_filter input {
    flex: 1 1 180px;
    min-width: 0;
    width: 100%;
    min-height: 42px;
    margin: 0 !important;
    border: 1px solid #dce6df;
    border-radius: 9px;
}

.md-pkg-length .dataTables_length label {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.md-pkg-length .dataTables_length select {
    min-width: 65px;
    min-height: 42px;
    margin: 0;
}

/* Only table data scrolls horizontally. */

.md-pkg-table-scroll {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior-x: contain;
    scrollbar-width: thin;
    scrollbar-color: #b8c9bd #f0f4f1;
}

.md-pkg-table-scroll #data-table {
    width: 100% !important;
    min-width: 900px;
    table-layout: auto;
}

.md-pkg-table-scroll #data-table th,
.md-pkg-table-scroll #data-table td {
    vertical-align: middle;
    white-space: nowrap;
}

.md-pkg-table-scroll #data-table th:last-child,
.md-pkg-table-scroll #data-table td:last-child {
    min-width: 125px;
}

/* Tablet */

@media (min-width: 768px) and (max-width: 1199.98px) {
    .md-pkg-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Mobile */

@media (max-width: 767.98px) {
    .md-pkg-header .page-title {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }

    .md-pkg-header .page-title > .btn {
        float: none !important;
        width: 100%;
        margin: 0 !important;
    }

    .md-pkg-card > .card-body {
        padding: 12px;
    }

    .md-pkg-filters {
        padding: 14px;
    }

    .md-pkg-filter-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 14px;
    }

    .md-pkg-field .form-control,
    .md-pkg-field .form-select {
        font-size: 16px;
    }

    .md-pkg-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .md-pkg-search,
    .md-pkg-length {
        width: 100%;
        flex: 0 0 auto;
    }

    .md-pkg-search .dataTables_filter label {
        flex-direction: column;
        align-items: stretch;
        text-align: right;
    }

    .md-pkg-search .dataTables_filter input {
        width: 100%;
        max-width: 100%;
    }

    .md-pkg-footer {
        gap: 12px;
    }
}
</style>
@endsection

@section('content')
<div class="page-header md-pkg-header">
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

<div class="card md-wide-search md-pkg-card">
    <div class="card-body">

        <div id="packagesFilterPanel" class="md-pkg-filters">

            <h5 class="md-pkg-filters-title">
                <i class="fa-solid fa-sliders"></i>
                البحث والتصفية
            </h5>

            <div class="md-pkg-filter-grid">

                <div class="md-pkg-field">
                    <label for="filter_subscription_type">
                        {{ TranslationHelper::translate('subscription_type') }}
                    </label>

                    <select id="filter_subscription_type" class="form-select">
                        <option value="">
                            {{ TranslationHelper::translate('all') }}
                        </option>
                        <option value="monthly">
                            {{ TranslationHelper::translate('Monthly') }}
                        </option>
                        <option value="annual">
                            {{ TranslationHelper::translate('Annual') }}
                        </option>
                    </select>
                </div>

                <div class="md-pkg-field">
                    <label for="filter_status">
                        {{ TranslationHelper::translate('Status') }}
                    </label>

                    <select id="filter_status" class="form-select">
                        <option value="">
                            {{ TranslationHelper::translate('all') }}
                        </option>
                        <option value="1">
                            {{ TranslationHelper::translate('Active') }}
                        </option>
                        <option value="0">
                            {{ TranslationHelper::translate('inactive') }}
                        </option>
                    </select>
                </div>

                <div class="md-pkg-field">
                    <label for="filter_date_from">
                        {{ TranslationHelper::translate('from') }}
                    </label>

                    <input
                        type="date"
                        id="filter_date_from"
                        class="form-control"
                    >
                </div>

                <div class="md-pkg-field">
                    <label for="filter_date_to">
                        {{ TranslationHelper::translate('to') }}
                    </label>

                    <input
                        type="date"
                        id="filter_date_to"
                        class="form-control"
                    >
                </div>

                <div class="md-pkg-reset-field">
                    <button
                        type="button"
                        id="filter_reset"
                        class="btn md-pkg-reset"
                    >
                        <i class="fa-solid fa-rotate-right"></i>
                        {{ TranslationHelper::translate('reset') }}
                    </button>
                </div>

            </div>
        </div>

        <!--begin::Table-->
        <div class="md-pkg-table-host">
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
        dom: '<"md-pkg-toolbar"<"md-pkg-search"f><"md-pkg-length"l>>r<"md-pkg-table-scroll"t><"md-pkg-footer"ip>'
    });

    $('#filter_subscription_type, #filter_status, #filter_date_from, #filter_date_to').on('change', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_reset').on('click', function () {
        $('#filter_subscription_type, #filter_status').val('');
        $('#filter_date_from, #filter_date_to').val('');
        $('.md-pkg-search input[type="search"]').val('');
        $('#data-table').DataTable().search('').draw(true);
    });
</script>
@endsection
