@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('change_password') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ TranslationHelper::translate('change_password') }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.providers.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.providers.index')}}">{{ TranslationHelper::translate('providers') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('change_password') }}</li>
            </ul>
        </div>
    </div>
</div>
<!--begin::Card-->
<div class="card">
    <div class="card-body">
	<!--begin::Form-->
	{!! Form::open(['route' => ['admin.providers.save_password', $user->id], 'files' => false, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
        <div class="row">
            <div class="col-md-10 col-lg-6">
                <div class="form-group">
                    {!! Form::label('password', TranslationHelper::translate('new_password'), ['class'=>'form-label']) !!}
                    <input type="password" name="password" id="password" class="form-control" />
                </div>
                <div class="form-group">
                    {!! Form::label('password_confirmation', TranslationHelper::translate('password_confirmation'), ['class'=>'form-label']) !!}
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" />
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" id="kt_submit">{{ TranslationHelper::translate('save') }}</button>
    {!! Form::close() !!}
</div>
@endsection


