@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Edit Report') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('Edit Sound') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.reports.index')}}">{{ TranslationHelper::translate('Report') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Edit Report') }}</li>
            </ul>
        </div>
    </div>
</div>
{!! Form::model($gift, ['route' => ['admin.reports.update', $gift->id], 'method' => 'PUT', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.reports._form')
{!! Form::close() !!}
@endsection


