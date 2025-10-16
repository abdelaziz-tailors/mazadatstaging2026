

@extends('dashboard.layouts.auth')
@section('title') {{ TranslationHelper::translate('dashboard_login') }} @endsection
@section('content')
<div class="login-right">
    <div class="login-right-wrap">
        <h1>{{ TranslationHelper::translate('login') }}</h1>
        <p class="account-subtitle">{{ TranslationHelper::translate('access_to_dashboard') }}</p>

        <form action="{{ route('admin.auth.login') }}" method="POST">
            {{ csrf_field() }}
            <div class="form-group">
                {!! Form::email('email', null, ['class' => 'form-control', 'placeholder'=>TranslationHelper::translate('email')]) !!}
            </div>
            <div class="form-group">
                {!! Form::password('password', ['class' => 'form-control', 'placeholder'=>TranslationHelper::translate('password')]) !!}
            </div>
            <div class="form-group">
                <button class="btn btn-primary w-100" type="submit">{{ TranslationHelper::translate('login') }}</button>
            </div>
        </form>
        <hr />
        <div class="form-group text-end">
            <div class="btn-group">
                <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <i class="fa-solid fa-globe"></i> {{ LaravelLocalization::getCurrentLocaleNative() }}
                </button>
                <div class="dropdown-menu">
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        @if(app()->getLocale() != $localeCode)
                            <a class="dropdown-item" href="{{ LaravelLocalization::getLocalizedURL($localeCode) }}">{{ $properties['native'] }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
