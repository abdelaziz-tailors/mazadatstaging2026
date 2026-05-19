@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('new slider') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">
                {{ TranslationHelper::translate('new slider') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.sliders.index')}}">{{ TranslationHelper::translate('sliders') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('new slider') }}</li>
            </ul>
        </div>
    </div>
</div>

{!! Form::open(['route' => 'admin.sliders.store', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.sliders._form')
{!! Form::close() !!}
@endsection
