@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Edit Auction') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('Edit Auction') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.videos.index')}}">{{ TranslationHelper::translate('Auction') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Edit Auction') }}</li>
            </ul>
        </div>
    </div>
</div>
{!! Form::model($data, ['route' => ['admin.videos.update', $data->id], 'method' => 'PUT', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.videos._form')
{!! Form::close() !!}
@endsection


