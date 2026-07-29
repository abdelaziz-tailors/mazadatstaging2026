@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('change_password') }} @endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-6 col-lg-8 col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="page-title mb-1">{{ TranslationHelper::translate('change_password') }}</h3>
                        <ul class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.admins.index') }}">{{ TranslationHelper::translate('admins') }}</a></li>
                            <li class="breadcrumb-item active">{{ TranslationHelper::translate('change_password') }}</li>
                        </ul>
                    </div>
                    <span class="md-page-icon"><i class="fa-solid fa-lock"></i></span>
                </div>
                <hr>

                {!! Form::open(['route' => ['admin.admins.save_password', $admin->id], 'files' => false, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
                    <div class="form-group mb-3">
                        {!! Form::label('password', TranslationHelper::translate('new_password'), ['class' => 'form-label']) !!}
                        <div class="md-password-field">
                            <i class="fa-solid fa-lock md-password-icon"></i>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <button type="button" class="md-password-toggle" data-target="password" aria-label="{{ TranslationHelper::translate('view') }}">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted small">{{ TranslationHelper::translate('password_must_be_strong') }}</div>
                    </div>

                    <div class="form-group mb-3">
                        {!! Form::label('password_confirmation', TranslationHelper::translate('password_confirmation'), ['class' => 'form-label']) !!}
                        <div class="md-password-field">
                            <i class="fa-solid fa-lock md-password-icon"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            <button type="button" class="md-password-toggle" data-target="password_confirmation" aria-label="{{ TranslationHelper::translate('view') }}">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted small">{{ TranslationHelper::translate('reenter_password_to_confirm') }}</div>
                    </div>

                    <div class="md-password-tip">
                        <i class="fa-solid fa-lock"></i>
                        <span>{{ TranslationHelper::translate('password_tip') }}</span>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4" id="kt_submit">{{ TranslationHelper::translate('save') }}</button>
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
