@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('vendors') }} @endsection

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

<div class="card md-wide-search">

    <div class="card-body">
        <div class="row g-3 mb-3" id="vendorsFilterPanel">
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

        <div class="table-responsive">


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
