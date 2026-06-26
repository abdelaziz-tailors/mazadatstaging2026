@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Orders') }} @endsection

@push('css')
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
@endpush

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ TranslationHelper::translate('Orders') }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Orders') }}</li>
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
                        <th>{{ TranslationHelper::translate('order_number') }}</th>
                        <th>{{ TranslationHelper::translate('Auctions') }}</th>
                        <th>{{ TranslationHelper::translate('items') }}</th>
                        <th>{{ TranslationHelper::translate('Status') }}</th>
                        <th>{{ TranslationHelper::translate('City') }}</th>
                        <th>{{ TranslationHelper::translate('order_total') }}</th>
                        <th>{{ TranslationHelper::translate('Buyer') }}</th>
                        <th>{{ TranslationHelper::translate('payment_proof') }}</th>
                        <th>{{ TranslationHelper::translate('actions') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
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
            caseInsensitive: true,
            smart: true
        },
        ajax: {
            url: "{!! route('admin.orders.getData') !!}",
            type: "POST",
            dataType: "JSON"
        },
        order: [[0, 'desc']],
        columns: [
            {data: 'order_number', name: 'order_number', searchable: true, orderable: true},
            {data: 'auction_title', searchable: false, orderable: false},
            {data: 'items_count', searchable: false, orderable: false},
            {data: 'status', searchable: false, orderable: false},
            {data: 'city', searchable: false, orderable: false},
            {data: 'total', searchable: false, orderable: true},
            {data: 'buyer', searchable: false, orderable: false},
            {data: 'payment_proof', searchable: false, orderable: false},
            {data: 'action', searchable: false, orderable: false}
        ],
        language: {
            search: "{{ TranslationHelper::translate('search') }}",
            lengthMenu: "{{ TranslationHelper::translate('display') }} _MENU_ {{ TranslationHelper::translate('records_per_page') }}",
            zeroRecords: "{{ TranslationHelper::translate('nothing_found') }}",
            info: "{{ TranslationHelper::translate('showing_page') }} _PAGE_ {{ TranslationHelper::translate('of') }} _PAGES_",
            infoEmpty: "{{ TranslationHelper::translate('nothing_found') }}",
            infoFiltered: "({{ TranslationHelper::translate('filtered_from') }} _MAX_)",
            loadingRecords: "{{ TranslationHelper::translate('loading') }}...",
            paginate: {
                previous: @if(app()->getLocale() == 'ar') "<i class='fas fa-angle-right'></i>" @else "<i class='fas fa-angle-left'></i>" @endif,
                next: @if(app()->getLocale() == 'ar') "<i class='fas fa-angle-left'></i>" @else "<i class='fas fa-angle-right'></i>" @endif
            }
        },
        dom: '<"d-flex justify-content-between"<l><f>>rt<"d-flex justify-content-between"<"d-flex align-items-center"<><i>><p>>'
    });
</script>
@endsection
