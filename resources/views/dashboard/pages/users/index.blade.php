@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Users') }} @endsection

@push('css')
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
<!--end::Page Vendor Stylesheets-->
    <style>

        .dataTables_wrapper .dt-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .dataTables_wrapper .dt-buttons .btn {
            margin: 0 !important;
        }
    

        /* Mazadat buyers/users responsive v1 */

        .md-users-card,
        .md-users-card > .card-body,
        .md-users-table-host,
        .md-users-card .dataTables_wrapper {
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        /* Filters */

        .md-users-filters {
            padding: 20px;
            margin-bottom: 22px;
            background: #f9fbf9;
            border: 1px solid #e4ece6;
            border-radius: 14px;
        }

        .md-users-filters-title {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 0 0 18px;
            color: #3d5f45;
            font-size: 16px;
            font-weight: 700;
        }

        .md-users-filter-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            direction: rtl;
        }

        .md-users-field {
            min-width: 0;
        }

        .md-users-field label {
            display: block;
            margin-bottom: 8px;
            color: #627469;
            font-size: 13px;
            font-weight: 600;
        }

        .md-users-field .form-control,
        .md-users-field .form-select {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            min-height: 42px;
            background: #fff;
            border: 1px solid #dce6df;
            border-radius: 9px;
            box-sizing: border-box;
        }

        .md-users-field .form-control:focus,
        .md-users-field .form-select:focus {
            border-color: #3d5f45;
            box-shadow: 0 0 0 3px rgba(61, 95, 69, .10);
        }

        .md-users-reset-field {
            display: flex;
            align-items: end;
            min-width: 0;
        }

        .md-users-reset {
            width: 100%;
            min-height: 42px;
            color: #3d5f45;
            background: #eaf2ec;
            border: 1px solid #cbded1;
            border-radius: 9px;
            font-weight: 600;
        }

        .md-users-reset:hover {
            color: #fff;
            background: #3d5f45;
            border-color: #3d5f45;
        }

        /* DataTables toolbar */

        .md-users-toolbar,
        .md-users-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            direction: rtl;
        }

        .md-users-toolbar {
            margin-bottom: 18px;
        }

        .md-users-footer {
            margin-top: 16px;
        }

        /*
         * Buyers already have username/email filters,
         * so hide the duplicated DataTables search there only.
         */
        .md-users-buyers .md-users-search {
            display: none !important;
        }

        .md-users-general .md-users-search {
            display: block;
            flex: 1 1 300px;
            min-width: 0;
        }

        .md-users-search .dataTables_filter {
            float: none !important;
            width: 100%;
            margin: 0;
        }

        .md-users-search .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            margin: 0;
        }

        .md-users-search .dataTables_filter input {
            flex: 1 1 180px;
            width: 100%;
            min-width: 0;
            min-height: 42px;
            margin: 0 !important;
            border: 1px solid #dce6df;
            border-radius: 9px;
        }

        .md-users-length {
            flex: 0 1 auto;
        }

        .md-users-length .dataTables_length {
            float: none !important;
            margin: 0;
        }

        .md-users-length .dataTables_length label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .md-users-length .dataTables_length select {
            min-width: 65px;
            min-height: 42px;
            margin: 0;
        }

        /* Export buttons */

        .md-users-buttons .dt-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .md-users-buttons .dt-button {
            margin: 0 !important;
            border-radius: 7px !important;
        }

        /* Table only scrolls */

        .md-users-table-scroll {
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

        .md-users-table-scroll #data-table {
            width: 100% !important;
            min-width: 980px;
        }

        .md-users-table-scroll #data-table th,
        .md-users-table-scroll #data-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        /* Image */
        .md-users-table-scroll #data-table th:nth-child(2),
        .md-users-table-scroll #data-table td:nth-child(2) {
            min-width: 70px;
            text-align: center;
        }

        .md-users-table-scroll #data-table td:nth-child(2) img {
            width: 44px !important;
            height: 44px !important;
            max-width: 44px !important;
            object-fit: cover;
            border-radius: 50%;
        }

        /* Full name / username */

        .md-users-table-scroll #data-table th:nth-child(3),
        .md-users-table-scroll #data-table td:nth-child(3),
        .md-users-table-scroll #data-table th:nth-child(4),
        .md-users-table-scroll #data-table td:nth-child(4) {
            min-width: 145px;
            white-space: normal;
        }

        /* Email */

        .md-users-table-scroll #data-table th:nth-child(5),
        .md-users-table-scroll #data-table td:nth-child(5) {
            min-width: 210px;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        /* Actions */

        .md-users-table-scroll #data-table th:last-child,
        .md-users-table-scroll #data-table td:last-child {
            min-width: 140px;
            text-align: center;
        }

        @media (max-width: 1199.98px) {
            .md-users-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {

            .md-users-card > .card-body {
                padding: 12px;
            }

            .md-users-filters {
                padding: 14px;
            }

            .md-users-filter-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 14px;
            }

            .md-users-field .form-control,
            .md-users-field .form-select {
                font-size: 16px;
            }

            .md-users-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .md-users-length,
            .md-users-buttons,
            .md-users-general .md-users-search {
                width: 100%;
            }

            .md-users-general .md-users-search {
                flex: 0 0 auto;
            }

            .md-users-search .dataTables_filter label {
                flex-direction: column;
                align-items: stretch;
                text-align: right;
            }

            .md-users-search .dataTables_filter input {
                width: 100%;
                max-width: 100%;
            }

            .md-users-buttons .dt-buttons {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .md-users-buttons .dt-button {
                width: 100%;
            }

            .md-users-table-scroll #data-table {
                min-width: 960px;
                font-size: 12px;
            }

            .md-users-footer {
                gap: 12px;
            }
        }

    </style>
@endpush


@section('content')
@php
    $isBuyersView = $request->filled('user_type');
@endphp
@include('dashboard.partials.page-header', [
    'title' => $isBuyersView ? TranslationHelper::translate('Buyers') : TranslationHelper::translate('Users'),
    'icon' => $isBuyersView ? 'fa-solid fa-user-check' : 'fa-solid fa-users',
    'actions' => ($isBuyersView && Auth::guard('admin')->user()->can('add user'))
        ? '<a href="'.route('admin.users.create').'" class="btn btn-primary"><i class="fas fa-plus"></i> '.TranslationHelper::translate('add_new_buyer').'</a>'
        : null,
])

@if ($isBuyersView)
    @php
        $activePct = $stats['total'] > 0 ? round($stats['active'] / $stats['total'] * 100, 1) : 0;
        $inactivePct = $stats['total'] > 0 ? round($stats['inactive'] / $stats['total'] * 100, 1) : 0;
        $verifiedPct = $stats['total'] > 0 ? round($stats['verified'] / $stats['total'] * 100, 1) : 0;
    @endphp
    @include('dashboard.partials.stat-row', ['cards' => [
        [
            'icon' => 'fa-solid fa-circle-check',
            'value' => $stats['active'],
            'label' => TranslationHelper::translate('Active'),
            'color' => 'success',
            'trend' => ['direction' => 'up', 'text' => $activePct . '%'],
        ],
        [
            'icon' => 'fa-solid fa-circle-xmark',
            'value' => $stats['inactive'],
            'label' => TranslationHelper::translate('Inactive'),
            'color' => 'danger',
            'trend' => ['direction' => 'down', 'text' => $inactivePct . '%'],
        ],
        [
            'icon' => 'fa-solid fa-shield-halved',
            'value' => $stats['verified'],
            'label' => TranslationHelper::translate('verified'),
            'color' => 'info',
            'trend' => ['direction' => 'up', 'text' => $verifiedPct . '%'],
        ],
        [
            'icon' => 'fa-solid fa-user-check',
            'value' => $stats['total'],
            'label' => TranslationHelper::translate('total_buyers'),
            'color' => 'primary',
            'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
        ],
    ]])
@else
    @include('dashboard.partials.stat-row', ['cards' => [
        [
            'icon' => 'fa-solid fa-user-check',
            'value' => $stats['buyers'],
            'label' => TranslationHelper::translate('Buyers'),
            'color' => 'success',
            'trend' => ['direction' => 'up', 'text' => $stats['buyers_pct'] . '%'],
        ],
        [
            'icon' => 'fa-solid fa-user-tie',
            'value' => $stats['vendors'],
            'label' => TranslationHelper::translate('vendors'),
            'color' => 'info',
            'trend' => ['direction' => 'up', 'text' => $stats['vendors_pct'] . '%'],
        ],
        [
            'icon' => 'fa-solid fa-user-tag',
            'value' => $stats['sellers'],
            'label' => TranslationHelper::translate('sellers'),
            'color' => 'warning',
            'trend' => ['direction' => 'up', 'text' => $stats['sellers_pct'] . '%'],
        ],
        [
            'icon' => 'fa-solid fa-users',
            'value' => $stats['total'],
            'label' => TranslationHelper::translate('total_users'),
            'color' => 'primary',
            'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
        ],
    ]])
@endif

<div class="card md-wide-search md-users-card {{ $isBuyersView ? 'md-users-buyers' : 'md-users-general' }}">

    <div class="card-body">
        @if ($isBuyersView)

            <div id="usersFilterPanel" class="md-users-filters">

                <h5 class="md-users-filters-title">
                    <i class="fa-solid fa-sliders"></i>
                    البحث والتصفية
                </h5>

                <div class="md-users-filter-grid">

                    <div class="md-users-field">
                        <label for="filter_username">
                            {{ TranslationHelper::translate('User Name') }}
                        </label>
                        <input
                            type="text"
                            id="filter_username"
                            class="form-control"
                        >
                    </div>

                    <div class="md-users-field">
                        <label for="filter_email">
                            {{ TranslationHelper::translate('Email') }}
                        </label>
                        <input
                            type="text"
                            id="filter_email"
                            class="form-control"
                        >
                    </div>

                    <div class="md-users-field">
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

                    <div class="md-users-field">
                        <label for="filter_date_from">
                            {{ TranslationHelper::translate('from') }}
                        </label>
                        <input
                            type="date"
                            id="filter_date_from"
                            class="form-control"
                        >
                    </div>

                    <div class="md-users-field">
                        <label for="filter_date_to">
                            {{ TranslationHelper::translate('to') }}
                        </label>
                        <input
                            type="date"
                            id="filter_date_to"
                            class="form-control"
                        >
                    </div>

                    <div class="md-users-reset-field">
                        <button
                            type="button"
                            id="filter_reset"
                            class="btn md-users-reset"
                        >
                            <i class="fa-solid fa-rotate-right"></i>
                            {{ TranslationHelper::translate('reset') }}
                        </button>
                    </div>

                </div>
            </div>

        @endif

        <div class="md-users-table-host">


            <table id="data-table" class="table">
                <thead>
                    <tr>
                        <th># </th>
                        <th>{{ TranslationHelper::translate('image') }}</th>
                        <th>{{ TranslationHelper::translate('Full Name') }}</th>
                        <th>{{ TranslationHelper::translate('User Name') }}</th>
                        <th>{{ TranslationHelper::translate('Email') }}</th>
                        @unless ($isBuyersView)
                            <th>{{ TranslationHelper::translate('account_type') }}</th>
                        @endunless
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
            url : '{!! route("admin.users.getData") !!}',
            data: function (d) {
                d.status = $('#status').val();
                d.user_type = '{{ $request->user_type }}';
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
            {data: 'image', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'name', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'user_name', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'email', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            @unless ($isBuyersView)
                {data: 'account_type', 'searchable': false, 'orderable': false, 'exportable': true, 'printable': true},
            @endunless
            {data: 'created_at', 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'is_active', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'action', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false}
        ],
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
            "searchPlaceholder": "{{ TranslationHelper::translate('search_users_placeholder') }}",
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
        dom: '<"md-users-toolbar"<"md-users-search"f><"md-users-length"l><"md-users-buttons"B>>r<"md-users-table-scroll"t><"md-users-footer"ip>',
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

        $('.md-users-search input[type="search"]').val('');

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
