@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('item_services') }} @endsection

@push('css')
<link href="{{ asset('dashboard/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css"/>
@endpush

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">
                {{ TranslationHelper::translate('item_services') }}
                <a href="{{ route('admin.item-services.create') }}" class="btn btn-primary float-end">
                    <i class="fas fa-plus"></i> {{ TranslationHelper::translate('new_item_service') }}
                </a>
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('item_services') }}</li>
            </ul>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="data-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        @if(!empty($showPartnerColumn))
                            <th>{{ TranslationHelper::translate('Partner') }}</th>
                        @endif
                        <th>{{ TranslationHelper::translate('name') }}</th>
                        <th>{{ TranslationHelper::translate('default_price') }}</th>
                        <th>{{ TranslationHelper::translate('is_active') }}</th>
                        <th>{{ TranslationHelper::translate('actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts_lib')
<script src="{{ asset('dashboard/plugins/datatables/datatables.min.js') }}"></script>
<script>
    $('#data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{!! route('admin.item-services.getData') !!}',
            type: 'POST',
            dataType: 'JSON'
        },
        columns: [
            {data: 'id'},
            @if(!empty($showPartnerColumn))
            {data: 'partner', searchable: true, orderable: false},
            @endif
            {data: 'name'},
            {data: 'default_price'},
            {data: 'is_active', searchable: false, orderable: false},
            {data: 'action', searchable: false, orderable: false}
        ],
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
            "searchPlaceholder": "{{ TranslationHelper::translate('search_item_services_placeholder') }}",
            "lengthMenu": "{{ TranslationHelper::translate('display') }} _MENU_ {{ TranslationHelper::translate('records_per_page') }}",
            "zeroRecords": "{{ TranslationHelper::translate('nothing_found') }}",
            "info": "{{ TranslationHelper::translate('showing_page') }} _PAGE_ {{ TranslationHelper::translate('of') }} _PAGES_",
            "infoEmpty": "{{ TranslationHelper::translate('nothing_found') }}",
            "infoFiltered": "({{ TranslationHelper::translate('filtered_from') }} _MAX_)",
            "loadingRecords": "{{ TranslationHelper::translate('loading') }}...",
            "paginate": {
                "previous": @if(app()->getLocale() == 'ar') "<i class='fas fa-angle-right'></i>" @else "<i class='fas fa-angle-left'></i>" @endif,
                "next": @if(app()->getLocale() == 'ar') "<i class='fas fa-angle-left'></i>" @else "<i class='fas fa-angle-right'></i>" @endif
            }
        },
        dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3 px-2"<l><f>>rt<"d-flex justify-content-between px-2"<"d-flex align-items-center"<><i>><p>>'
    });
</script>
@endsection
