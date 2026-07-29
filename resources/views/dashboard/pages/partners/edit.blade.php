@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('edit partner') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ TranslationHelper::translate('edit partner') }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.partners.index')}}">{{ TranslationHelper::translate('partners') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('edit partner') }}</li>
            </ul>
        </div>
    </div>
</div>
<!--begin::Card-->
<div class="card">
    <div class="card-body">

        @php
            $admin['user_name'] = $admin->user->user_name ??'';
            // The phone field now only collects the 9-digit local number
            // next to a fixed "+966" badge (matching the vendor form) —
            // strip that prefix from the stored value so editing shows just
            // the local digits back, not "+9665XXXXXXXX" in a 9-char box.
            $admin['phone'] = preg_replace('/^\+?966/', '', $admin->phone ?? '');
        @endphp
        {!! Form::model($admin, ['route' => ['admin.partners.update', $admin->id], 'method' => 'PUT', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
            @include('dashboard.pages.partners._form')
        {!! Form::close() !!}
    </div>
</div>
@endsection


