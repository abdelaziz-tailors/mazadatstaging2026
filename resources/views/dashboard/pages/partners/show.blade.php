@extends('dashboard.layouts.app')

@section('title') {{ $admin->name }} @endsection

@section('content')
@include('dashboard.partials.page-header', [
    'title' => $admin->name,
    'icon' => 'fa-solid fa-user-tie',
    'breadcrumbs' => [
        ['label' => TranslationHelper::translate('partners'), 'route' => route('admin.partners.index')],
        ['label' => $admin->name],
    ],
])

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-4">
            @include('dashboard.partials.avatar', ['path' => $admin->image, 'name' => $admin->name, 'size' => 72])
            <div>
                <h4 class="mb-1">{{ $admin->name }}</h4>
                <div class="text-muted">{{ $admin->email ?? '-' }}</div>
            </div>
        </div>

        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('phone') }}</p>
            <p class="col-sm-10">{{ $admin->phone ?? '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('national_identity') }}</p>
            <p class="col-sm-10">{{ $admin->national_id ?? '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('Status') }}</p>
            <p class="col-sm-10">
                {{ ($admin->user->is_active ?? false) ? TranslationHelper::translate('Active') : TranslationHelper::translate('Inactive') }}
            </p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('created at') }}</p>
            <p class="col-sm-10">{{ optional($admin->created_at)->format('Y-m-d') ?? '-' }}</p>
        </div>
    </div>
</div>
@endsection
