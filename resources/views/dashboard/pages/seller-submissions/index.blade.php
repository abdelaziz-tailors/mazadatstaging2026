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

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="data-table" class="table table-striped">
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
        columns: @json($sellerSubmissionDtColumns)
    });
</script>
@endsection
