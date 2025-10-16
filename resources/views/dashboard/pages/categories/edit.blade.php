@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Edit Category') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('Edit Category') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.categories.index')}}">{{ TranslationHelper::translate('Category') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Edit Category') }}</li>
            </ul>
        </div>
    </div>
</div>
{!! Form::model($data, ['route' => ['admin.categories.update', $data->id], 'method' => 'PUT', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.categories._form')
{!! Form::close() !!}
@endsection


