@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('edit age') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('edit age') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.ages.index')}}">{{ TranslationHelper::translate('ages') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('edit age') }}</li>
            </ul>
        </div>
    </div>
</div>
{!! Form::model($data, ['route' => ['admin.ages.update', $data->id], 'method' => 'PUT', 'files' => false, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.ages._form')
{!! Form::close() !!}
@endsection


