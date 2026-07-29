@extends('dashboard.layouts.app')

@section('title') {{ $admin->name }} @endsection

@section('content')
@include('dashboard.partials.page-header', [
    'title' => $admin->name,
    'icon' => 'fa-solid fa-user-shield',
    'breadcrumbs' => [
        ['label' => TranslationHelper::translate('admins'), 'route' => route('admin.admins.index')],
        ['label' => $admin->name],
    ],
])

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-4">
            @include('dashboard.partials.avatar', ['path' => $admin->image, 'name' => $admin->name, 'size' => 72])
            <div>
                <h4 class="mb-1">{{ $admin->name }}</h4>
                <div class="text-muted">{{ count(json_decode($admin->getRoleNames())) > 0 ? json_decode($admin->getRoleNames())[0] : '-' }}</div>
            </div>
        </div>

        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('email') }}</p>
            <p class="col-sm-10">{{ $admin->email ?? '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('phone') }}</p>
            <p class="col-sm-10">{{ $admin->phone ?? '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('role') }}</p>
            <p class="col-sm-10">{{ count(json_decode($admin->getRoleNames())) > 0 ? json_decode($admin->getRoleNames())[0] : '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('created at') }}</p>
            <p class="col-sm-10">{{ optional($admin->created_at)->format('Y-m-d') ?? '-' }}</p>
        </div>
    </div>
</div>
@endsection
