@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('admins') }} @endsection

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
                {{ TranslationHelper::translate('admins') }}
                @if(Auth::guard('admin')->user()->can('add admin'))
                    <a href='{{ route('admin.admins.create') }}' class='btn btn-primary float-end'><i class="fas fa-plus"></i> {{ TranslationHelper::translate('new_admin') }}</a>
                @endif
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('admins') }}</li>
            </ul>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="data-table" class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ TranslationHelper::translate('name') }}</th>
                        <th>{{ TranslationHelper::translate('email') }}</th>
                        <th>{{ TranslationHelper::translate('role') }}</th>
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
            url : '{!! route("admin.admins.getData") !!}',
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
            {data: 'role', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
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
        dom: '<"d-flex justify-content-between"<l><f>>rt<"d-flex justify-content-between"<"d-flex align-items-center"<><i>><p>>'
    });
</script>
@endsection
