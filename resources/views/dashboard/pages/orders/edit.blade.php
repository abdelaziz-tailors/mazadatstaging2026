@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Edit Order') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('Edit Order') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.orders.index')}}">{{ TranslationHelper::translate('Orders') }}</a></li>

                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Edit Order') }}</li>
            </ul>
        </div>
    </div>
</div>
{!! Form::model($data, ['route' => ['admin.orders.update', $data->id], 'method' => 'PUT', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.orders._form')
{!! Form::close() !!}
@endsection


