@extends('dashboard.layouts.app')

@section('title') Animal Pens  @endsection

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
                {{ TranslationHelper::translate('animal pen') }}
{{--                @if(Auth::guard('admin')->user()->can('add animal_pen'))--}}
                    <a href='{{ route('admin.animal-pens.create') }}' class='btn btn-primary float-end'><i class="fas fa-plus"></i> {{ TranslationHelper::translate('new animal pen') }}</a>
{{--                @endif--}}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('animal pens') }}</li>
            </ul>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <!--begin::Table-->
        <div class="table-responsive">
            <table id="data-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ TranslationHelper::translate('name') }}</th>
                        <th>{{ TranslationHelper::translate('is_active') }}</th>
                        <th>{{ TranslationHelper::translate('actions') }}</th>
                    </tr>
                </thead>
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
            url : '{!! route("admin.animal-pens.getData") !!}',
            type: "POST",
            dataType: "JSON"
        },
        columns: [
            {data: 'id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'name', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'is_active', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
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
