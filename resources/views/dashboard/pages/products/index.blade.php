@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Auction Products') }} @endsection

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
                {{TranslationHelper::translate('Auctions') }}
{{--                @if(Auth::guard('admin')->user()->can('add video'))--}}
                    <a href='{{ route('admin.products.create',request()->id) }}' class='btn btn-primary float-end'><i class="fas fa-plus"></i> {{ TranslationHelper::translate('Add Product') }}</a>
{{--                @endif--}}

            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.videos.index')}}">{{ TranslationHelper::translate('Auction') }}</a></li>

                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Auction Products') }}</li>
            </ul>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">

        <!--begin::Table-->
        <div class="table-responsive">
            <table id="data-table" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        {{-- <th>{{ TranslationHelper::translate('Title') }}</th> --}}
                        <th>{{ TranslationHelper::translate('Title ar') }}</th>
                        {{-- <th>{{ TranslationHelper::translate('Category') }}</th> --}}
                        <th>{{ TranslationHelper::translate('Age') }}</th>
                        <th>{{ TranslationHelper::translate('Status') }}</th>
                        {{-- <th>{{ TranslationHelper::translate('Star price') }}</th> --}}
                        <th>{{ TranslationHelper::translate('End price') }}</th>
                        <th>{{ TranslationHelper::translate('Buyer') }}</th>
                        <th>{{ TranslationHelper::translate('seller') }}</th>
                        <th>{{ TranslationHelper::translate('shipping address') }}</th>
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
            url : "{!! route('admin.products.getData',request()->id) !!}",
            data: {},
            type: "POST",
            dataType: "JSON"
        },
        order: [[0, 'desc']], // Sort by 'id' column in descending order (change to 'asc' for ascending)

        columns: [
            {data: 'id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            // {data: 'title', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'title_ar', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {{-- {data: 'category', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true}, --}}
            {data: 'ageData', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'status', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {{-- {data: 'start_price', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true}, --}}

            {data: 'finished_price', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'buyer', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'seller', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'shipping_address', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
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
