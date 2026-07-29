@extends('dashboard.layouts.app')

@section('title') {{ $user->name }} @endsection

@section('content')
@include('dashboard.partials.page-header', [
    'title' => $user->name,
    'icon' => 'fa-solid fa-user',
    'breadcrumbs' => [
        ['label' => TranslationHelper::translate('Users'), 'route' => route('admin.users.index')],
        ['label' => $user->name],
    ],
])

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-4">
            @include('dashboard.partials.avatar', ['path' => $user->image, 'name' => $user->name, 'size' => 72])
            <div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <div class="text-muted">{{ $user->user_name ?? '-' }}</div>
            </div>
        </div>

        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('Email') }}</p>
            <p class="col-sm-10">{{ $user->email ?? '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('phone') }}</p>
            <p class="col-sm-10">{{ $user->phone ?? '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('user_type') }}</p>
            <p class="col-sm-10">{{ $user->user_type ? TranslationHelper::translate($user->user_type) : '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('account_type') }}</p>
            <p class="col-sm-10">{{ $user->account_type ? TranslationHelper::translate($user->account_type) : '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('Status') }}</p>
            <p class="col-sm-10">{{ $user->is_active ? TranslationHelper::translate('Active') : TranslationHelper::translate('Inactive') }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('created at') }}</p>
            <p class="col-sm-10">{{ optional($user->created_at)->format('Y-m-d') ?? '-' }}</p>
        </div>
    </div>
</div>
@endsection
