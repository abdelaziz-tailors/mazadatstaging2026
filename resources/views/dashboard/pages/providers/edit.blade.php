@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('edit provider') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">
                @if($provider->type=='doctor')
                    {{ TranslationHelper::translate('Doctor Profile') }}

                @else
                    {{ TranslationHelper::translate('Lab Profile') }}


                @endif


            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>


                <li class="breadcrumb-item"><a href="{{route('admin.providers.index')}}">
                        @if($provider->type=='doctor')
                            {{ TranslationHelper::translate('Doctor Request') }}

                        @else
                            {{ TranslationHelper::translate('Lab Request') }}


                        @endif
                    </a></li>
                <li class="breadcrumb-item active">
                    @if($provider->type=='doctor')
                        {{ TranslationHelper::translate('Doctor Profile') }}

                    @else
                        {{ TranslationHelper::translate('Lab Profile') }}


                    @endif
                </li>
            </ul>
        </div>
    </div>
</div>
<!--begin::Card-->
<div class="card">
    <div class="card-body">
        {!! Form::model($provider, [ 'route' => ['admin.providers.update', $provider->id],'method' => 'PUT', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
            @include('dashboard.pages.providers._form')
        {!! Form::close() !!}
    </div>
</div>
@endsection


