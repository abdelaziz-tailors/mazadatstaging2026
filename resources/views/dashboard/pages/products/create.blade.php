@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('New Product') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('New Product') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.videos.index')}}">{{ TranslationHelper::translate('Auction') }}</a></li>

                <li class="breadcrumb-item active">{{ TranslationHelper::translate('New Product') }}</li>
            </ul>
        </div>
    </div>
</div>
<!--begin::Form-->
{!! Form::open(['route' => 'admin.products.store', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.products._form')
{!! Form::close() !!}
@endsection
