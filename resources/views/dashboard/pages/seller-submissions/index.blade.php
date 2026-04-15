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
                    <th>{{ TranslationHelper::translate('partner') }}</th>
                    <th>{{ TranslationHelper::translate('city') }}</th>
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
        columns: [
            {data: 'id'},
            {data: 'name'},
            {data: 'phone'},
            {data: 'partner'},
            {data: 'city'},
            {data: 'status_badge'},
            {data: 'created_at'},
            {data: 'action', orderable: false, searchable: false}
        ]
    });
</script>
@endsection
