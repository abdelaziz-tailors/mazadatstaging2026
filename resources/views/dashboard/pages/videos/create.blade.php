@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('New Auction') }} @endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="page-title mb-1">{{ TranslationHelper::translate('New Auction') }}</h3>
                        <ul class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.videos.index') }}">{{ TranslationHelper::translate('Auction') }}</a></li>
                            <li class="breadcrumb-item active">{{ TranslationHelper::translate('New Auction') }}</li>
                        </ul>
                    </div>
                    <span class="md-page-icon"><i class="fa-solid fa-gavel"></i></span>
                </div>
                <hr>

                {!! Form::open(['route' => 'admin.videos.store', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
                    @include('dashboard.pages.videos._form')
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
