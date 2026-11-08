

@extends('dashboard.layouts.auth')
@section('title') {{ TranslationHelper::translate('dashboard_login') }} @endsection
@section('content')
<main class="login-right" id="main-content" tabindex="-1">
    <div class="login-right-wrap">
        <img class="md-login-mobile-brand" src="{{ asset('dashboard/img/logo.png') }}" alt="{{ TranslationHelper::translate('site_name') }}">
        <h1>{{ TranslationHelper::translate('login') }}</h1>
        <p class="account-subtitle">{{ TranslationHelper::translate('access_to_dashboard') }}</p>

        @include('dashboard.partials.validation-summary')
        <form action="{{ route('admin.auth.login') }}" method="POST">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="email">{{ TranslationHelper::translate('email') }} / {{ TranslationHelper::translate('phone') }}</label>
                {!! Form::text('email', null, ['class' => 'form-control', 'id' => 'email', 'autocomplete' => 'username', 'autofocus' => true, 'placeholder'=>TranslationHelper::translate('email').' / '.TranslationHelper::translate('phone')]) !!}
            </div>
            <div class="form-group">
                <label for="password">{{ TranslationHelper::translate('password') }}</label>
                <div class="md-password-field">
                    {!! Form::password('password', ['class' => 'form-control', 'id' => 'password', 'autocomplete' => 'current-password', 'placeholder'=>TranslationHelper::translate('password')]) !!}
                    <button type="button" class="md-password-toggle" data-password-toggle="password" aria-controls="password" aria-pressed="false" aria-label="{{ __('dashboard-ui.password_show') }}"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="form-group">
                <button class="btn btn-primary w-100" type="submit">{{ TranslationHelper::translate('login') }}</button>
            </div>
        </form>
        {{-- <hr />
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
        </div> --}}
    </div>
</main>
@endsection
