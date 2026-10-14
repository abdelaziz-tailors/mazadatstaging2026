@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('partners') }} @endsection

@push('css')
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
<!--end::Page Vendor Stylesheets-->
@endpush



@section('css_lib')
<style>
/* Mazadat partners responsive v1 */

.md-partners-card,
.md-partners-card > .card-body,
.md-partners-table-host,
.md-partners-card .dataTables_wrapper {
    width: 100%;
    max-width: 100%;
    min-width: 0;
}

/* Filters */

.md-partners-filters {
    padding: 20px;
    margin-bottom: 22px;

    background: #f9fbf9;
    border: 1px solid #e4ece6;
    border-radius: 14px;
}

.md-partners-filters-title {
    display: flex;
    align-items: center;
    gap: 9px;

    margin: 0 0 18px;

    color: #3d5f45;
    font-size: 16px;
    font-weight: 700;
}

.md-partners-filter-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    direction: rtl;
}

.md-partners-field {
    min-width: 0;
}

.md-partners-field label {
    display: block;
    margin-bottom: 8px;

    color: #627469;
    font-size: 13px;
    font-weight: 600;
}

.md-partners-field .form-control,
.md-partners-field .form-select {
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

.md-partners-field .form-control:focus,
.md-partners-field .form-select:focus {
    border-color: #3d5f45;
    box-shadow: 0 0 0 3px rgba(61, 95, 69, .10);
}

.md-partners-reset-field {
    display: flex;
    align-items: end;
    min-width: 0;
}

.md-partners-reset {
    width: 100%;
    min-height: 42px;

    color: #3d5f45;
    background: #eaf2ec;
    border: 1px solid #cbded1;
    border-radius: 9px;

    font-weight: 600;
}

.md-partners-reset:hover {
    color: #fff;
    background: #3d5f45;
    border-color: #3d5f45;
}

/* DataTables controls */

.md-partners-toolbar,
.md-partners-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;

    gap: 14px;
    direction: rtl;
}

.md-partners-toolbar {
    margin-bottom: 18px;
}

.md-partners-footer {
    margin-top: 16px;
}

.md-partners-search {
    flex: 1 1 300px;
    min-width: 0;
}

.md-partners-length {
    flex: 0 1 auto;
}

.md-partners-search .dataTables_filter,
.md-partners-length .dataTables_length {
    float: none !important;
    width: 100%;
    margin: 0;
}

.md-partners-search .dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 10px;

    width: 100%;
    margin: 0;
}

.md-partners-search .dataTables_filter input {
    flex: 1 1 180px;

    width: 100%;
    min-width: 0;
    min-height: 42px;

    margin: 0 !important;

    border: 1px solid #dce6df;
    border-radius: 9px;
}

.md-partners-length .dataTables_length label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.md-partners-length .dataTables_length select {
    min-width: 65px;
    min-height: 42px;
    margin: 0;
}

/* Table scrolling */

.md-partners-table-scroll {
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

.md-partners-table-scroll #data-table {
    width: 100% !important;
    min-width: 1060px;
    table-layout: auto;
}

.md-partners-table-scroll #data-table th,
.md-partners-table-scroll #data-table td {
    vertical-align: middle;
    white-space: nowrap;
}

/* Name */

.md-partners-table-scroll #data-table th:nth-child(2),
.md-partners-table-scroll #data-table td:nth-child(2) {
    min-width: 150px;
    white-space: normal;
}

/* Email */

.md-partners-table-scroll #data-table th:nth-child(3),
.md-partners-table-scroll #data-table td:nth-child(3) {
    min-width: 200px;
    white-space: normal;
    overflow-wrap: anywhere;
}

/* National identity */

.md-partners-table-scroll #data-table th:nth-child(4),
.md-partners-table-scroll #data-table td:nth-child(4) {
    min-width: 135px;
}

/* Commercial register */

.md-partners-table-scroll #data-table th:nth-child(5),
.md-partners-table-scroll #data-table td:nth-child(5) {
    min-width: 130px;
    text-align: center;
}

/* Actions */

.md-partners-table-scroll #data-table th:nth-child(8),
.md-partners-table-scroll #data-table td:nth-child(8) {
    min-width: 135px;
    text-align: center;
}

/* Tablet */

@media (max-width: 1199.98px) {
    .md-partners-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Mobile */

@media (max-width: 767.98px) {

    .md-partners-header .page-title {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }

    .md-partners-header .page-title > .btn {
        float: none !important;
        width: 100%;
        margin: 0 !important;
    }

    .md-partners-card > .card-body {
        padding: 12px;
    }

    .md-partners-filters {
        padding: 14px;
    }

    .md-partners-filter-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 14px;
    }

    .md-partners-field .form-control,
    .md-partners-field .form-select {
        font-size: 16px;
    }

    .md-partners-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .md-partners-search,
    .md-partners-length {
        width: 100%;
        flex: 0 0 auto;
    }

    .md-partners-search .dataTables_filter label {
        flex-direction: column;
        align-items: stretch;
        text-align: right;
    }

    .md-partners-search .dataTables_filter input {
        width: 100%;
        max-width: 100%;
    }

    .md-partners-footer {
        gap: 12px;
    }

    .md-partners-table-scroll #data-table {
        min-width: 1040px;
        font-size: 12px;
    }
}
</style>
@endsection

@section('content')
<div class="page-header md-partners-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('partners') }}
                @if(Auth::guard('admin')->user()->can('add partner'))
                    <a href='{{ route('admin.partners.create') }}' class='btn btn-primary float-end'><i class="fas fa-plus"></i> {{ TranslationHelper::translate('New partner') }}</a>
                @endif
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('partners') }}</li>
            </ul>
        </div>
    </div>
</div>

@include('dashboard.partials.stat-row', ['cards' => [
    [
        'icon' => 'fa-solid fa-building',
        'value' => $stats['new_this_month'],
        'label' => TranslationHelper::translate('new_this_month'),
        'color' => 'purple',
        'trend' => ['direction' => $stats['new_this_month_trend_direction'], 'text' => $stats['new_this_month_trend_pct'] . '%'],
    ],
    [
        'icon' => 'fa-solid fa-xmark',
        'value' => $stats['inactive'],
        'label' => TranslationHelper::translate('inactive_partners'),
        'color' => 'danger',
        'trend' => ['direction' => 'down', 'text' => $stats['inactive_pct'] . '%'],
    ],
    [
        'icon' => 'fa-solid fa-circle-check',
        'value' => $stats['active'],
        'label' => TranslationHelper::translate('active_partners'),
        'color' => 'success',
        'trend' => ['direction' => 'up', 'text' => $stats['active_pct'] . '%'],
    ],
    [
        'icon' => 'fa-solid fa-users',
        'value' => $stats['total'],
        'label' => TranslationHelper::translate('total_partners'),
        'color' => 'primary',
        'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
    ],
]])

<div class="card md-wide-search md-partners-card">
    <div class="card-body">

        <div id="partnersFilterPanel" class="md-partners-filters">

            <h5 class="md-partners-filters-title">
                <i class="fa-solid fa-sliders"></i>
                البحث والتصفية
            </h5>

            <div class="md-partners-filter-grid">

                <div class="md-partners-field">
                    <label for="filter_name">
                        {{ TranslationHelper::translate('name') }}
                    </label>
                    <input
                        type="text"
                        id="filter_name"
                        class="form-control"
                    >
                </div>

                <div class="md-partners-field">
                    <label for="filter_email">
                        {{ TranslationHelper::translate('email') }}
                    </label>
                    <input
                        type="text"
                        id="filter_email"
                        class="form-control"
                    >
                </div>

                <div class="md-partners-field">
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

                <div class="md-partners-field">
                    <label for="filter_date_from">
                        {{ TranslationHelper::translate('from') }}
                    </label>
                    <input
                        type="date"
                        id="filter_date_from"
                        class="form-control"
                    >
                </div>

                <div class="md-partners-field">
                    <label for="filter_date_to">
                        {{ TranslationHelper::translate('to') }}
                    </label>
                    <input
                        type="date"
                        id="filter_date_to"
                        class="form-control"
                    >
                </div>

                <div class="md-partners-reset-field">
                    <button
                        type="button"
                        id="filter_reset"
                        class="btn md-partners-reset"
                    >
                        <i class="fa-solid fa-rotate-right"></i>
                        {{ TranslationHelper::translate('reset') }}
                    </button>
                </div>

            </div>
        </div>

        <div class="md-partners-table-host">
            <table id="data-table" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ TranslationHelper::translate('name') }}</th>
                        <th>{{ TranslationHelper::translate('email') }}</th>
                        <th>{{ TranslationHelper::translate('national_identity') }}</th>
                        <th>{{ TranslationHelper::translate('commercial_register') }}</th>
                        <th>{{ TranslationHelper::translate('Status') }}</th>
                        <th>{{ TranslationHelper::translate('created at') }}</th>
                        <th>{{ TranslationHelper::translate('actions') }}</th>
                    </tr>
                </thead>
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
            "caseInsensitive": true,
            "smart": true
        },
        ajax: {
            url : '{!! route("admin.partners.getData") !!}',
            data: function (d) {
                d.filter_name = $('#filter_name').val();
                d.filter_email = $('#filter_email').val();
                d.filter_status = $('#filter_status').val();
                d.filter_date_from = $('#filter_date_from').val();
                d.filter_date_to = $('#filter_date_to').val();
            },
            type: "POST",
            dataType: "JSON"
        },
        columns: [
            {
                data: null,
                searchable: false,
                orderable: false,
                render: function (data, type, row, meta) {
                    var info = new $.fn.dataTable.Api(meta.settings).page.info();
                    return info.start + meta.row + 1;
                }
            },
            {data: 'name', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'email', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'national_id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true, 'defaultContent': ''},
            {data: 'commercial_register', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'status', 'searchable': false, 'orderable': false, 'exportable': true, 'printable': true},
            {data: 'created_at', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'action', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false}
        ],
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
            "searchPlaceholder": "{{ TranslationHelper::translate('search_partners_placeholder') }}",
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
        dom: '<"md-partners-toolbar"<"md-partners-length"l>>r<"md-partners-table-scroll"t><"md-partners-footer"ip>'
    });

    $('#filter_name, #filter_email').on('keyup', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_status, #filter_date_from, #filter_date_to').on('change', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_reset').on('click', function () {
        $('#filter_name, #filter_email, #filter_date_from, #filter_date_to').val('');
        $('#filter_status').val('');

        $('#data-table').DataTable().search('').draw(true);
    });
</script>
@endsection
