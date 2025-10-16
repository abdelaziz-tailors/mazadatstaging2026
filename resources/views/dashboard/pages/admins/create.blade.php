@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('new_admin') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ TranslationHelper::translate('new_admin') }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.admins.index')}}">{{ TranslationHelper::translate('admins') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('new_admin') }}</li>
            </ul>
        </div>
    </div>
</div>
<!--begin::Form-->
<div class="card">
    <div class="card-body">
        {!! Form::open(['route' => 'admin.admins.store', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
                @include('dashboard.pages.admins._form')
        {!! Form::close() !!}
    </div>
</div>
<!--end::Form-->
@endsection


