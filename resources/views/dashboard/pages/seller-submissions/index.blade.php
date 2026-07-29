@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Seller Submissions') }} @endsection

@push('css')
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
@endpush

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ TranslationHelper::translate('Seller Submissions') }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Seller Submissions') }}</li>
            </ul>
        </div>
    </div>
</div>

@include('dashboard.partials.stat-row', ['cards' => [
    [
        'icon' => 'fa-solid fa-circle-xmark',
        'value' => $stats['rejected'],
        'label' => TranslationHelper::translate('rejected_submissions'),
        'color' => 'danger',
        'trend' => ['direction' => 'down', 'text' => $stats['rejected_pct'] . '%'],
    ],
    [
        'icon' => 'fa-solid fa-circle-check',
        'value' => $stats['approved'],
        'label' => TranslationHelper::translate('approved_submissions'),
        'color' => 'success',
        'trend' => ['direction' => 'up', 'text' => $stats['approved_pct'] . '%'],
    ],
    [
        'icon' => 'fa-solid fa-clock',
        'value' => $stats['under_review'],
        'label' => TranslationHelper::translate('submissions_under_review'),
        'color' => 'warning',
        'trend' => ['direction' => 'up', 'text' => $stats['under_review_pct'] . '%'],
    ],
    [
        'icon' => 'fa-solid fa-clipboard-list',
        'value' => $stats['total'],
        'label' => TranslationHelper::translate('total_submissions'),
        'color' => 'primary',
        'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
    ],
]])

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="data-table" class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ TranslationHelper::translate('name') }}</th>
                    <th>{{ TranslationHelper::translate('phone') }}</th>
                    <th>{{ TranslationHelper::translate('sheep_type') }}</th>
                    @if(!($isPartnerDashboard ?? false))
                    <th>{{ TranslationHelper::translate('partner') }}</th>
                    @endif
                    <th>{{ TranslationHelper::translate('status') }}</th>
                    <th>{{ TranslationHelper::translate('created_at') }}</th>
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
@php
    $sellerSubmissionDtColumns = ($isPartnerDashboard ?? false)
        ? [
            ['data' => 'id'],
            ['data' => 'name', 'orderable' => false, 'searchable' => false],
            ['data' => 'phone', 'orderable' => false, 'searchable' => false],
            ['data' => 'sheep_type'],
            ['data' => 'status_badge', 'orderable' => false, 'searchable' => false],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]
        : [
            ['data' => 'id'],
            ['data' => 'name', 'orderable' => false, 'searchable' => false],
            ['data' => 'phone', 'orderable' => false, 'searchable' => false],
            ['data' => 'sheep_type'],
            ['data' => 'partner', 'orderable' => false, 'searchable' => false],
            ['data' => 'status_badge', 'orderable' => false, 'searchable' => false],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ];
@endphp
<script src="{{asset('dashboard/plugins/datatables/datatables.min.js')}}"></script>
<script>
    $('#data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url : "{!! route('admin.seller-submissions.getData') !!}",
            type: "POST",
            dataType: "JSON"
        },
        order: [[0, 'desc']],
        columns: @json($sellerSubmissionDtColumns),
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
            "searchPlaceholder": "{{ TranslationHelper::translate('search_seller_submissions_placeholder') }}",
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
