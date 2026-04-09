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
<div class="page-header">
    <div class="row">
        <div class="col">

            <h3 class="page-title" >

                    {{ TranslationHelper::translate('Users') }}


            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">

                        {{ TranslationHelper::translate('Users') }}



                </li>
            </ul>
        </div>
    </div>
</div>
<div class="card">

    <div class="card-body">
        <div class="row"style="margin-top:20px;margin-bottom:20px">
            <div class="col-md-4">
            </div>
            <div class="col-md-4">
            </div>
{{--            <div class="col-md-2" style="margin-right: -49px;--}}
{{--    margin-left: 48px;">--}}
{{--                <button class="btn btn-success"><i class="fa fa-table"></i>  {{ TranslationHelper::translate('Export as Excel ') }}</button>--}}
{{--            </div>--}}
{{--            <div class="col-md-2">--}}
{{--                <button class="btn btn-danger"><i class="fa fa-file-pdf"></i>  {{ TranslationHelper::translate('Export as PDF ') }}</button>--}}
{{--            </div>--}}
        </div>

        <div class="row">
{{--            <div class="col-md-3">--}}
{{--                <div class="form-group">--}}
{{--                    <label>{{ TranslationHelper::translate('Search By') }}--}}
{{--                    </label>--}}
{{--                    <select name="" id=""--}}
{{--                            class="form-select form-control ">--}}
{{--                        <option>{{ TranslationHelper::translate('Search By') }}</option>--}}
{{--                        <option>{{ TranslationHelper::translate('Name') }}</option>--}}
{{--                        <option>{{ TranslationHelper::translate('User Name') }}</option>--}}
{{--                        <option>{{ TranslationHelper::translate('Age') }}</option>--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-4">--}}
{{--                <div class="form-group">--}}
{{--                    <label>{{ TranslationHelper::translate('Search') }}--}}
{{--                    </label>--}}
{{--                    <input type="text" class="form-control " placeholder="{{ TranslationHelper::translate('') }}" >--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-2">--}}
{{--                <div class="form-group">--}}
{{--                    <label>{{ TranslationHelper::translate('Status') }}--}}
{{--                    </label>--}}
{{--                    <select name="status" id="status"--}}
{{--                            class="form-select form-control {{$errors->has('status')? 'is-invalid':''}}">--}}
{{--                        <option {{old('status')==null ?'selected':''}} value="">{{ TranslationHelper::translate('Status') }}</option>--}}
{{--                        <option--}}
{{--                            value="2" {{old('status')=='2'?'selected':''}}>{{ TranslationHelper::translate('Active') }}</option>--}}
{{--                        <option value="1" {{old('status')=='1'?'selected':''}}>{{ TranslationHelper::translate('Pending') }}</option>--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="col-md-1">--}}
{{--                <div class="form-group">--}}
{{--                    <label>--}}
{{--                    </label>--}}
{{--                    <br>--}}
{{--                    <button class="btn btn-success"><i class="fa fa-search"></i>  </button>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>

    </div>



        <div class="table-responsive">


            <table id="data-table" class="table table-striped">
                <thead>
                    <tr>
                        <th># </th>
                        <th>{{ TranslationHelper::translate('Full Name') }}</th>
                        <th>{{ TranslationHelper::translate('User Name') }}</th>
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
            url : '{!! route("admin.users.getData") !!}',
            data: function (d) {
                d.status = $('#status').val();
            },
            type: "POST",
            dataType: "JSON"
        },
        columns: [
            {data: 'id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'name', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'user_name', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'email', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'created_at', 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'is_active', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'action', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false}
        ],
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
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
        dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3"<l><f><B>>rt<"d-flex justify-content-between"<"d-flex align-items-center"<><i>><p>>',
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
