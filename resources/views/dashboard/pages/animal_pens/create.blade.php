@extends('dashboard.layouts.app')

@section('title'){{ TranslationHelper::translate('new animal pen') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
               {{ TranslationHelper::translate('new animal pen') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.animal-pens.index')}}">{{ TranslationHelper::translate('animal pens') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('new animal pens') }}</li>
            </ul>
        </div>
    </div>
</div>
<!--begin::Form-->
{!! Form::open(['route' => 'admin.animal-pens.store', 'files' => false, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.animal_pens._form')
{!! Form::close() !!}
@endsection


