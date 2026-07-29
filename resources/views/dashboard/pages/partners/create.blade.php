@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('new partner') }} @endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-6 col-lg-8 col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="page-title mb-1">{{ TranslationHelper::translate('new partner') }}</h3>
                        <ul class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.partners.index') }}">{{ TranslationHelper::translate('admins') }}</a></li>
                            <li class="breadcrumb-item active">{{ TranslationHelper::translate('new partner') }}</li>
                        </ul>
                    </div>
                    <span class="md-page-icon"><i class="fa-solid fa-handshake"></i></span>
                </div>
                <hr>

                {!! Form::open(['route' => 'admin.partners.store', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
                    @include('dashboard.pages.partners._form')
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
