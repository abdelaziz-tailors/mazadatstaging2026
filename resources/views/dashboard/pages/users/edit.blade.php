@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('edit User') }} @endsection

@section('content')
    <div class="page-header">
        <div class="row">
            <div class="col">
                <h3 class="page-title">
                    {{ TranslationHelper::translate('User') }}




                </h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>


                    <li class="breadcrumb-item"><a href="{{route('admin.users.index')}}">
                            {{ TranslationHelper::translate('edit User') }}


                        </a></li>
                </ul>
            </div>
        </div>
    </div>
    <!--begin::Card-->
    <div class="card">
        <div class="card-body">
            {!! Form::model($user, [ 'route' => ['admin.users.update', $user->id],'method' => 'PUT', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
            @include('dashboard.pages.users._form')
            {!! Form::close() !!}
        </div>
    </div>
@endsection


