@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Edit Sound') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('Edit Sound') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.sounds.index')}}">{{ TranslationHelper::translate('Sound') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Edit Sound') }}</li>
            </ul>
        </div>
    </div>
</div>
{!! Form::model($gift, ['route' => ['admin.sounds.update', $gift->id], 'method' => 'PUT', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.sounds._form')
{!! Form::close() !!}
@endsection


