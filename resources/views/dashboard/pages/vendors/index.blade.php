@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('vendors') }} @endsection

@push('css')
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
<!--end::Page Vendor Stylesheets-->
@endpush



@section('css_lib')
<style>
/* Mazadat vendors responsive v1 */

.md-vendors-card,
.md-vendors-card > .card-body,
.md-vendors-table-host,
.md-vendors-card .dataTables_wrapper {
    width: 100%;
    max-width: 100%;
    min-width: 0;
}

/* Filters */

.md-vendors-filters {
    padding: 20px;
    margin-bottom: 22px;
    background: #f9fbf9;
    border: 1px solid #e4ece6;
    border-radius: 14px;
}

.md-vendors-filters-title {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0 0 18px;
    color: #3d5f45;
    font-size: 16px;
    font-weight: 700;
}

.md-vendors-filter-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    direction: rtl;
}

.md-vendors-field {
    min-width: 0;
}

.md-vendors-field label {
    display: block;
    margin-bottom: 8px;
    color: #627469;
    font-size: 13px;
    font-weight: 600;
}

.md-vendors-field .form-control,
.md-vendors-field .form-select {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    min-height: 42px;
    border: 1px solid #dce6df;
    border-radius: 9px;
    background: #fff;
    box-sizing: border-box;
}

.md-vendors-field .form-control:focus,
.md-vendors-field .form-select:focus {
    border-color: #3d5f45;
    box-shadow: 0 0 0 3px rgba(61, 95, 69, .10);
}

.md-vendors-reset-field {
    display: flex;
    align-items: end;
}

.md-vendors-reset {
    width: 100%;
    min-height: 42px;
    color: #3d5f45;
    background: #eaf2ec;
    border: 1px solid #cbded1;
    border-radius: 9px;
    font-weight: 600;
}

.md-vendors-reset:hover {
    color: #fff;
    background: #3d5f45;
    border-color: #3d5f45;
}

/* DataTables controls */

.md-vendors-toolbar,
.md-vendors-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    direction: rtl;
}

.md-vendors-toolbar {
    margin-bottom: 18px;
}

.md-vendors-footer {
    margin-top: 16px;
}

.md-vendors-length {
    flex: 0 1 auto;
}

.md-vendors-length .dataTables_length {
    float: none !important;
    margin: 0;
}

.md-vendors-length .dataTables_length label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.md-vendors-length .dataTables_length select {
    min-width: 65px;
    min-height: 42px;
    margin: 0;
}

/* Excel / PDF */

.md-vendors-buttons .dt-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.md-vendors-buttons .dt-button {
    margin: 0 !important;
    border-radius: 7px !important;
}

/* Table only scrolls */

.md-vendors-table-scroll {
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

.md-vendors-table-scroll #data-table {
    width: 100% !important;
    min-width: 900px;
}

.md-vendors-table-scroll #data-table th,
.md-vendors-table-scroll #data-table td {
    vertical-align: middle;
    white-space: nowrap;
}

.md-vendors-table-scroll #data-table th:nth-child(2),
.md-vendors-table-scroll #data-table td:nth-child(2) {
    min-width: 140px;
    white-space: normal;
}

.md-vendors-table-scroll #data-table th:nth-child(3),
.md-vendors-table-scroll #data-table td:nth-child(3) {
    min-width: 130px;
    white-space: normal;
}

.md-vendors-table-scroll #data-table th:nth-child(4),
.md-vendors-table-scroll #data-table td:nth-child(4) {
    min-width: 190px;
    white-space: normal;
    overflow-wrap: anywhere;
}

.md-vendors-table-scroll #data-table th:last-child,
.md-vendors-table-scroll #data-table td:last-child {
    min-width: 140px;
    text-align: center;
}

/* Tablet */

@media (max-width: 1199.98px) {
    .md-vendors-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Mobile */

@media (max-width: 767.98px) {

    .md-vendors-header .page-title {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }

    .md-vendors-header .page-title > .btn {
        float: none !important;
        width: 100%;
        margin: 0 !important;
    }

    .md-vendors-card > .card-body {
        padding: 12px;
    }

    .md-vendors-filters {
        padding: 14px;
    }

    .md-vendors-filter-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 14px;
    }

    .md-vendors-field .form-control,
    .md-vendors-field .form-select {
        font-size: 16px;
    }

    .md-vendors-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .md-vendors-length,
    .md-vendors-buttons {
        width: 100%;
    }

    .md-vendors-buttons .dt-buttons {
        width: 100%;
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .md-vendors-buttons .dt-button {
        width: 100%;
    }

    .md-vendors-table-scroll #data-table {
        min-width: 880px;
        font-size: 12px;
    }

    .md-vendors-footer {
        gap: 12px;
    }
}
</style>
@endsection

@section('content')
<div class="page-header md-vendors-header">
    <div class="row">
        <div class="col">

            <h3 class="page-title" >

                    {{ TranslationHelper::translate('vendors') }}
                    <a href='{{ route('admin.vendors.create') }}' class='btn btn-primary float-end'><i class="fas fa-plus"></i> {{ TranslationHelper::translate('new vendor') }}</a>


            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">

                        {{ TranslationHelper::translate('vendors') }}



                </li>
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
        'label' => TranslationHelper::translate('inactive_vendors'),
        'color' => 'danger',
        'trend' => ['direction' => 'down', 'text' => $stats['inactive_pct'] . '%'],
    ],
    [
        'icon' => 'fa-solid fa-circle-check',
        'value' => $stats['active'],
        'label' => TranslationHelper::translate('active_vendors'),
        'color' => 'success',
        'trend' => ['direction' => 'up', 'text' => $stats['active_pct'] . '%'],
    ],
    [
        'icon' => 'fa-solid fa-store',
        'value' => $stats['total'],
        'label' => TranslationHelper::translate('total_vendors'),
        'color' => 'primary',
        'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
    ],
]])

<div class="card md-wide-search md-vendors-card">

    <div class="card-body">

        <div id="vendorsFilterPanel" class="md-vendors-filters">

            <h5 class="md-vendors-filters-title">
                <i class="fa-solid fa-sliders"></i>
                البحث والتصفية
            </h5>

            <div class="md-vendors-filter-grid">

                <div class="md-vendors-field">
                    <label for="filter_username">
                        {{ TranslationHelper::translate('User Name') }}
                    </label>
                    <input
                        type="text"
                        id="filter_username"
                        class="form-control"
                    >
                </div>

                <div class="md-vendors-field">
                    <label for="filter_email">
                        {{ TranslationHelper::translate('Email') }}
                    </label>
                    <input
                        type="text"
                        id="filter_email"
                        class="form-control"
                    >
                </div>

                <div class="md-vendors-field">
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

                <div class="md-vendors-field">
                    <label for="filter_date_from">
                        {{ TranslationHelper::translate('from') }}
                    </label>
                    <input
                        type="date"
                        id="filter_date_from"
                        class="form-control"
                    >
                </div>

                <div class="md-vendors-field">
                    <label for="filter_date_to">
                        {{ TranslationHelper::translate('to') }}
                    </label>
                    <input
                        type="date"
                        id="filter_date_to"
                        class="form-control"
                    >
                </div>

                <div class="md-vendors-reset-field">
                    <button
                        type="button"
                        id="filter_reset"
                        class="btn md-vendors-reset"
                    >
                        <i class="fa-solid fa-rotate-right"></i>
                        {{ TranslationHelper::translate('reset') }}
                    </button>
                </div>

            </div>
        </div>

        <div class="md-vendors-table-host">


            <table id="data-table" class="table">
                <thead>
                    <tr>
                        <th># </th>
                        <th>{{ TranslationHelper::translate('Full Name') }}</th>
                        <th>{{ TranslationHelper::translate('User Name') }}</th>
{{--                        <th>{{ TranslationHelper::translate('Age') }}</th>--}}
                        <th>{{ TranslationHelper::translate('Email') }}</th>
                        <th>{{ TranslationHelper::translate('created at') }}</th>
                        <th>{{ TranslationHelper::translate('Status') }}</th>
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
    <script src="{{asset('dashboard/plugins/datatables/dataTables.buttons.min.js')}}"></script>
    <script src="{{asset('dashboard/plugins/datatables/jszip.min.js')}}"></script>
    <script src="{{asset('dashboard/plugins/datatables/pdfmake.min.js')}}"></script>
    <script src="{{asset('dashboard/plugins/datatables/vfs_fonts.js')}}"></script>
    <script src="{{asset('dashboard/plugins/datatables/buttons.html5.min.js')}}"></script>
<script>
    // alert({{$request->category_id}});
    $('#data-table').DataTable({
        order: [[0, 'desc']],
        autoFill: true,
        processing: true,
        serverSide: true,
        search: {
            "caseInsensitive": true,
            "smart": true
        },
        ajax: {
            url : '{!! route("admin.vendors.getData") !!}',
            data: function (d) {
                d.status = $('#status').val();
                d.filter_username = $('#filter_username').val();
                d.filter_email = $('#filter_email').val();
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
            {data: 'user_name', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            // {data: 'age', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'email', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'created_at', 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'is_active', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'action', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false}
        ],
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
            "searchPlaceholder": "{{ TranslationHelper::translate('search_vendors_placeholder') }}",
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
        dom: '<"md-vendors-toolbar"<"md-vendors-length"l><"md-vendors-buttons"B>>r<"md-vendors-table-scroll"t><"md-vendors-footer"ip>',
        buttons: [

            {
                extend:    'excel',
                text:      '<i class="fa fa-table"></i> {{ TranslationHelper::translate("export_as_excel_") }}',
                titleAttr: 'Excel',
                className: 'btn btn-success  btn-md mr-2 btn-excel'
            },
            {
                extend:    'pdf',
                text:      '<i class="fas fa-file-pdf"></i> {{ TranslationHelper::translate("export_as_pdf_") }}',
                titleAttr: 'PDF',
                className: 'btn btn-danger btn-md mr-2 btn-pdf'
            },

        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],


    });
    $('#status').on('keyup change', function () {
        $('#data-table').DataTable().draw(true);
    });

    $('#filter_username, #filter_email').on('keyup', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_status, #filter_date_from, #filter_date_to').on('change', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_reset').on('click', function () {
        $('#filter_username, #filter_email, #filter_date_from, #filter_date_to').val('');
        $('#filter_status').val('');
        $('#data-table').DataTable().search('').draw(true);
    });




    $( document ).ready(function() {
        $('body').on('submit', '.provider_suspension_form', function (e) {
            e.preventDefault();
            var provider_id = $(this).attr('data-provider');
            $('#provider_suspension_form-'+provider_id).empty();
            $('#provider_suspension_form-'+provider_id).html('<i class="fas fa-spinner fa-spin"></i>');
            var action = $(this).attr('action');
            var formData = new FormData($(this)[0]);
            $.ajax({
                type: 'POST',
                data: formData,
                async: true,
                cache: false,
                contentType: false,
                processData: false,
                url: action,
                error: function(data) {
                    $('#provider_suspension_form-'+provider_id).empty();
                    jQuery.each(data.errors, function(key, value){
                        toastr.error(value);
                    });
                },
                success: function(data)
                {
                    $('#provider_suspension_form-'+provider_id).empty();
                    if(data.success)
                    {
                        window.location.reload();
                    }
                    else
                    {
                        toastr.error(data.errors);
                    }
                }
            });
            return false;
        });
    });

</script>
@endsection
