@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('New Vendor') }} @endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-6 col-lg-8 col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="page-title mb-1">{{ TranslationHelper::translate('New Vendor') }}</h3>
                        <ul class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.vendors.index') }}">{{ TranslationHelper::translate('Vendors') }}</a></li>
                            <li class="breadcrumb-item active">{{ TranslationHelper::translate('New Vendor') }}</li>
                        </ul>
                    </div>
                    <span class="md-page-icon"><i class="fa-solid fa-user-plus"></i></span>
                </div>
                <hr>

                {!! Form::open(['route' => 'admin.vendors.store', 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
                    <div class="form-group mb-3">
                        {!! Form::label('name', TranslationHelper::translate('full_name'), ['class' => 'form-label']) !!}
                        {!! Form::text('name', old('name'), ['class' => 'form-control' . ($errors->has('name') ? ' is-invalid' : ''), 'placeholder' => TranslationHelper::translate('full_name_placeholder')]) !!}
                        @if ($errors->has('name'))
                            <div class="invalid-feedback d-block">{{ $errors->first('name') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        {!! Form::label('user_name', TranslationHelper::translate('alias_name'), ['class' => 'form-label']) !!}
                        {!! Form::text('user_name', old('user_name'), ['class' => 'form-control' . ($errors->has('user_name') ? ' is-invalid' : '')]) !!}
                        @if ($errors->has('user_name'))
                            <div class="invalid-feedback d-block">{{ $errors->first('user_name') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        {!! Form::label('phone', TranslationHelper::translate('phone'), ['class' => 'form-label']) !!}
                        <div class="md-phone-field">
                            <span class="md-phone-code-badge">🇸🇦 +966</span>
                            {!! Form::text('phone', old('phone'), ['class' => 'form-control' . ($errors->has('phone') ? ' is-invalid' : ''), 'placeholder' => '5X XXX XXXX', 'maxlength' => 9]) !!}
                        </div>
                        @if ($errors->has('phone'))
                            <div class="invalid-feedback d-block">{{ $errors->first('phone') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        {!! Form::label('email', TranslationHelper::translate('email'), ['class' => 'form-label']) !!}
                        {!! Form::email('email', old('email'), ['class' => 'form-control' . ($errors->has('email') ? ' is-invalid' : ''), 'placeholder' => 'name@example.com']) !!}
                        @if ($errors->has('email'))
                            <div class="invalid-feedback d-block">{{ $errors->first('email') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        {!! Form::label('password', TranslationHelper::translate('password'), ['class' => 'form-label']) !!}
                        <div class="md-password-field">
                            <i class="fa-solid fa-lock md-password-icon"></i>
                            <input type="password" name="password" id="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required>
                            <button type="button" class="md-password-toggle" data-target="password" aria-label="{{ TranslationHelper::translate('view') }}">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        @if ($errors->has('password'))
                            <div class="invalid-feedback d-block">{{ $errors->first('password') }}</div>
                        @endif
                    </div>


                    <div class="form-group mb-3">
                        {!! Form::label('city_id', TranslationHelper::translate('city'), ['class' => 'form-label']) !!}
                        <select name="city_id" class="form-select form-control{{ $errors->has('city_id') ? ' is-invalid' : '' }}">
                            <option value="">{{ TranslationHelper::translate('select_city') }}</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('city_id'))
                            <div class="invalid-feedback d-block">{{ $errors->first('city_id') }}</div>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-2" id="kt_submit">{{ TranslationHelper::translate('create_new_account') }}</button>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts_lib')
<script>
    document.querySelectorAll('.md-password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.target);
            var icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
</script>
@endsection
