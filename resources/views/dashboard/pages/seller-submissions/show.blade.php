@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Seller Submission Details') }} @endsection

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
            <tr><th>{{ TranslationHelper::translate('partner') }}</th><td>{{ $submission->partner->name ?? '-' }}</td></tr>
            <tr><th>{{ TranslationHelper::translate('description') }}</th><td>{{ $submission->description ?? '-' }}</td></tr>
            <tr><th>{{ TranslationHelper::translate('notes') }}</th><td>{{ $submission->notes ?? '-' }}</td></tr>
            <tr><th>{{ TranslationHelper::translate('status') }}</th><td>{{ $submission->status }}</td></tr>
        </table>

        @if($submission->media->count())
            <h5 class="mt-3">{{ TranslationHelper::translate('Media') }}</h5>
            <div class="row">
                @foreach($submission->media as $media)
                    <div class="col-md-4 mb-3">
                        @if($media->type === 'image')
                            <img src="{{ Storage::disk('public')->url($media->path) }}" class="img-fluid" alt="submission-image">
                        @else
                            <video controls style="width:100%">
                                <source src="{{ Storage::disk('public')->url($media->path) }}">
                            </video>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
