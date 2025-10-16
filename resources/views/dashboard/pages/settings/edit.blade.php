@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Edit settings') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('Edit settings') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Edit settings') }}</li>
            </ul>
        </div>
    </div>
</div>
{!! Form::model($settings, ['route' => ['admin.settings.update'], 'method' => 'post', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator','enctype' => 'multipart/form-data']) !!}
    @include('dashboard.pages.settings._form')
{!! Form::close() !!}
@endsection


