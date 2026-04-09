@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('User Subscriptions') }} @endsection

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
                {{TranslationHelper::translate('User Subscriptions') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>

                <li class="breadcrumb-item active">{{ TranslationHelper::translate('User Subscriptions') }}</li>
            </ul>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <!--begin::Table-->
        <div class="table-responsive">
            <table id="data-table" class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ TranslationHelper::translate('user') }}</th>
                        <th>{{ TranslationHelper::translate('package') }}</th>
                        <th>{{ TranslationHelper::translate('subscription_type') }}</th>
                        <th>{{ TranslationHelper::translate('auctions_limit') }}</th>
                        <th>{{ TranslationHelper::translate('remaining_auctions') }}</th>
                        <th>{{ TranslationHelper::translate('price') }}</th>
                        <th>{{ TranslationHelper::translate('expires_at') }}</th>
                        <th>{{ TranslationHelper::translate('approval_status') }}</th>
                        <th>{{ TranslationHelper::translate('status') }}</th>
                        <th>{{ TranslationHelper::translate('created_at') }}</th>
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
            url : "{!! route('admin.user-subscriptions.getData') !!}",
            data: {},
            type: "POST",
            dataType: "JSON"
        },
        columns: [
            {data: 'id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'user_id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'package_id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'subscription_type', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'auctions_limit', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'remaining_auctions', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'price', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'expires_at', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'status', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'is_active', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'created_at', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
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

