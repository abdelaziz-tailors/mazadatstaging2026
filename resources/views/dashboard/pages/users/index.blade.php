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

<div class="card md-wide-search">

    <div class="card-body">
        @if ($isBuyersView)
            <div class="row g-3 mb-3" id="usersFilterPanel">
                <div class="col-xl col-lg-4 col-md-6 col-12">
                    <label class="form-label small mb-1">{{ TranslationHelper::translate('User Name') }}</label>
                    <input type="text" id="filter_username" class="form-control form-control-sm">
                </div>
                <div class="col-xl col-lg-4 col-md-6 col-12">
                    <label class="form-label small mb-1">{{ TranslationHelper::translate('Email') }}</label>
                    <input type="text" id="filter_email" class="form-control form-control-sm">
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
        @endif

        <div class="table-responsive">


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
        dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3 px-2"<l><f><B>>rt<"d-flex justify-content-between px-2"<"d-flex align-items-center"<><i>><p>>',
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
        $('#data-table').DataTable().draw(true);
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
