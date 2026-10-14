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
            <p class="col-sm-10">{{ $admin->national_id ?: ($admin->user->national_id ?? '-') }}</p>
        </div>

        <div class="row">
            <p class="col-sm-2 text-muted mb-3">السجل التجاري</p>
            <div class="col-sm-10 mb-3">
                @php
                    $commercialRegister = $admin->user->commercial_register ?? null;
                    $commercialExists = $commercialRegister
                        && \Illuminate\Support\Facades\Storage::disk('public')->exists($commercialRegister);
                @endphp

                @if ($commercialExists)
                    <a
                        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($commercialRegister) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-sm btn-outline-success"
                    >
                        <i class="fa-solid fa-file-arrow-down me-1"></i>
                        عرض السجل التجاري
                    </a>
                @elseif ($commercialRegister)
                    <span class="text-warning">
                        السجل التجاري مسجل، لكن الملف غير موجود في مسار التخزين المتوقع
                    </span>
                @else
                    <span class="text-muted">لم يتم رفع سجل تجاري</span>
                @endif
            </div>
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
