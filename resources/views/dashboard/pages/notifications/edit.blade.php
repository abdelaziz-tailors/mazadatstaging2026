@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Edit Notification') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('Edit Notification') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.notifications.index')}}">{{ TranslationHelper::translate('Notifications') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Edit Notification') }}</li>
            </ul>
        </div>
    </div>
</div>
{!! Form::model($notification, ['route' => ['admin.notifications.update', $notification->id], 'method' => 'PUT', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
    @include('dashboard.pages.notifications._form_view')
{!! Form::close() !!}
@endsection


