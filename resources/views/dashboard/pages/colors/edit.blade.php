@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('edit color') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('edit color') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.colors.index')}}">{{ TranslationHelper::translate('colors') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('edit color') }}</li>
            </ul>
        </div>
    </div>
</div>
{!! Form::model($data, ['route' => ['admin.colors.update', $data->id], 'method' => 'PUT', 'files' => false, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.colors._form')
{!! Form::close() !!}
@endsection


