@extends('dashboard.layouts.app')

@section('title'){{ TranslationHelper::translate('new_city') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
               {{ TranslationHelper::translate('new_city') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.cities.index')}}">{{ TranslationHelper::translate('cities') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('new_city') }}</li>
            </ul>
        </div>
    </div>
</div>
<!--begin::Form-->
{!! Form::open(['route' => 'admin.cities.store', 'files' => false, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.cities._form')
{!! Form::close() !!}
@endsection


