@extends('dashboard.layouts.app')

@section('title'){{ TranslationHelper::translate('new age') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
               {{ TranslationHelper::translate('new age') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.ages.index')}}">{{ TranslationHelper::translate('ages') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('new age') }}</li>
            </ul>
        </div>
    </div>
</div>
<!--begin::Form-->
{!! Form::open(['route' => 'admin.ages.store', 'files' => false, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.ages._form')
{!! Form::close() !!}
@endsection


