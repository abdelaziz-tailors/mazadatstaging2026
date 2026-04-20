@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Seller Submission Details') }} @endsection

@push('css')
<style>
    .seller-submission-media-grid {
        --ssm-radius: 0.5rem;
    }
    .seller-submission-media-card {
        border-radius: var(--ssm-radius);
        overflow: hidden;
        transition: box-shadow 0.2s ease;
    }
    .seller-submission-media-card:hover {
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.1) !important;
    }
    .seller-submission-media-frame {
        position: relative;
        width: 100%;
        aspect-ratio: 4 / 3;
        background: #f1f3f5;
        overflow: hidden;
    }
    .seller-submission-media-frame--video {
        aspect-ratio: 16 / 9;
        background: #0d0d0d;
    }
    .seller-submission-media-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }
    .seller-submission-media-video {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }
    .seller-submission-media-meta {
        padding: 0.65rem 0.85rem;
        font-size: 0.8125rem;
        border-top: 1px solid rgba(0, 0, 0, 0.06);
        background: #fafbfc;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ TranslationHelper::translate('Seller Submission Details') }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.seller-submissions.index')}}">{{ TranslationHelper::translate('Seller Submissions') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('details') }}</li>
            </ul>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <tr><th>ID</th><td>{{ $submission->id }}</td></tr>
            <tr><th>{{ TranslationHelper::translate('name') }}</th><td>{{ $submission->user->name ?? '-' }}</td></tr>
            <tr><th>{{ TranslationHelper::translate('phone') }}</th><td>{{ $submission->user->phone ?? '-' }}</td></tr>
            <tr><th>{{ TranslationHelper::translate('sheep_type') }}</th><td>{{ $submission->sheep_type }}</td></tr>
            @if(!($isPartnerDashboard ?? false))
            <tr><th>{{ TranslationHelper::translate('partner') }}</th><td>{{ $submission->partner->name ?? '-' }}</td></tr>
            @endif
            <tr><th>{{ TranslationHelper::translate('description') }}</th><td>{{ $submission->description ?? '-' }}</td></tr>
            <tr><th>{{ TranslationHelper::translate('notes') }}</th><td>{{ $submission->notes ?? '-' }}</td></tr>
            <tr><th>{{ TranslationHelper::translate('status') }}</th><td>{{ $submission->status }}</td></tr>
        </table>

        @if($submission->media->count())
            <hr class="my-4">
            <h5 class="mb-3 fw-semibold">{{ TranslationHelper::translate('Media') }}</h5>
            {{-- <p class="text-muted small mb-3">{{ TranslationHelper::translate('seller_submission_media_hint') }}</p> --}}
            <div class="row g-3 seller-submission-media-grid">
                @foreach($submission->media as $media)
                    <div class="col-12 col-sm-6 col-xl-4">
                        <div class="card border shadow-sm h-100 seller-submission-media-card">
                            <div class="seller-submission-media-frame {{ $media->type === 'video' ? 'seller-submission-media-frame--video' : '' }}">
                                @if($media->type === 'image')
                                    <img
                                        src="{{ Storage::disk('public')->url($media->path) }}"
                                        class="seller-submission-media-img"
                                        alt=""
                                        loading="lazy"
                                    >
                                @else
                                    <video class="seller-submission-media-video" controls playsinline preload="metadata">
                                        <source src="{{ Storage::disk('public')->url($media->path) }}">
                                    </video>
                                @endif
                            </div>
                            <div class="seller-submission-media-meta d-flex align-items-center justify-content-between text-muted">
                                <span>
                                    @if($media->type === 'image')
                                        <i class="fa-regular fa-image me-1"></i>{{ TranslationHelper::translate('image') }}
                                    @else
                                        <i class="fa-solid fa-film me-1"></i>{{ TranslationHelper::translate('video') }}
                                    @endif
                                </span>
                                <span class="badge bg-light text-secondary border">#{{ $loop->iteration }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
