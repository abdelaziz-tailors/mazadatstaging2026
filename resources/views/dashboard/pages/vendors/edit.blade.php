@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('edit Vendor') }} @endsection

@section('content')
    <div class="page-header">
        <div class="row">
            <div class="col">
                <h3 class="page-title">
                    {{ TranslationHelper::translate('Vendor') }}




                </h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>


                    <li class="breadcrumb-item"><a href="{{route('admin.vendors.index')}}">
                            {{ TranslationHelper::translate('edit Vendor') }}


                        </a></li>
                </ul>
            </div>
        </div>
    </div>
    <!--begin::Card-->
    <div class="card">
        <div class="card-body">
            {!! Form::model($user, [ 'route' => ['admin.vendors.update', $user->id],'method' => 'PUT', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
            @include('dashboard.pages.vendors._form')
            {!! Form::close() !!}
        </div>
    </div>
@endsection


