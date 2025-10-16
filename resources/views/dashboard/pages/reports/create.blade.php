@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('New Report') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('New Report') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>

                <li class="breadcrumb-item active">{{ TranslationHelper::translate('New Report') }}</li>
            </ul>
        </div>
    </div>
</div>
<!--begin::Form-->
{!! Form::open(['route' => 'admin.reports.store', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.reports._form')
{!! Form::close() !!}
@endsection
