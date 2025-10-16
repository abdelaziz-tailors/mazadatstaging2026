@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Edit') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('Edit') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Edit') }}</li>
            </ul>
        </div>
    </div>
</div>
{!! Form::model($packages, ['route' => ['admin.pages.update', $packages->id], 'method' => 'PUT', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.pages._form')
{!! Form::close() !!}
@endsection


