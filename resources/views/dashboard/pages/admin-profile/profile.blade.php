@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('my_profile') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ TranslationHelper::translate('my_profile') }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('my_profile') }}</li>
            </ul>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <!--begin::Form-->
        {!! Form::model($admin, ['route' => ['admin.update_profile'], 'method' => 'POST', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
            <div class="row">
                <div class="col-lg-6 form-group">
                    {!! Form::label('name', TranslationHelper::translate('name'), ['class'=>'form-label']) !!}
                    {!! Form::text('name', NULL, ['class' => 'form-control']) !!}
                </div>
                <div class="col-lg-6 form-group">
                    {!! Form::label('user_name', TranslationHelper::translate('alias_name'), ['class'=>'form-label']) !!}
                    {!! Form::text('user_name', NULL, ['class' => 'form-control']) !!}
                </div>
                <div class="col-lg-6 form-group">
                    {!! Form::label('email', TranslationHelper::translate('email'), ['class'=>'form-label']) !!}
                    {!! Form::email('email', NULL, ['class' => 'form-control']) !!}
                </div>
                <div class="col-lg-6 form-group">
                    {!! Form::label('phone', TranslationHelper::translate('phone'), ['class'=>'form-label']) !!}
                    {!! Form::text('phone', NULL, ['class' => 'form-control']) !!}
                </div>
                <div class="col-lg-5 form-group">
                    {!! Form::label('image', TranslationHelper::translate('image'), ['class'=>'form-label']) !!}
                    <input type="file" name="image" id="image" class="form-control" />
                </div>
                <div class="col-lg-1 form-group">
                    <img src="{{ Storage::disk('public')->url(Auth::guard('admin')->user()->image) }}" alt="{{ Auth::guard('admin')->user()->name }}" class="avatar-img rounded-circle img-fluid" />
                </div>
            </div>
            <button type="submit" class="btn btn-primary">{{ TranslationHelper::translate('save') }}</button>
        {!! Form::close() !!}
    </div>
</div>

@endsection


