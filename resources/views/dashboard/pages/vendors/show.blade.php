@extends('dashboard.layouts.app')

@section('title') {{ $vendor->name }} @endsection

@section('content')
@include('dashboard.partials.page-header', [
    'title' => $vendor->name,
    'icon' => 'fa-solid fa-store',
    'breadcrumbs' => [
        ['label' => TranslationHelper::translate('vendors'), 'route' => route('admin.vendors.index')],
        ['label' => $vendor->name],
    ],
])

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-4">
            @include('dashboard.partials.avatar', ['path' => $vendor->image, 'name' => $vendor->name, 'size' => 72])
            <div>
                <h4 class="mb-1">{{ $vendor->name }}</h4>
                <div class="text-muted">{{ $vendor->user_name ?? '-' }}</div>
            </div>
        </div>

        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('Email') }}</p>
            <p class="col-sm-10">{{ $vendor->email ?? '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('phone') }}</p>
            <p class="col-sm-10">{{ $vendor->phone ?? '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('national_identity') }}</p>
            <p class="col-sm-10">{{ $vendor->national_id ?? '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('Status') }}</p>
            <p class="col-sm-10">{{ $vendor->is_active ? TranslationHelper::translate('Active') : TranslationHelper::translate('Inactive') }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('created at') }}</p>
            <p class="col-sm-10">{{ optional($vendor->created_at)->format('Y-m-d') ?? '-' }}</p>
        </div>
    </div>
</div>
@endsection
