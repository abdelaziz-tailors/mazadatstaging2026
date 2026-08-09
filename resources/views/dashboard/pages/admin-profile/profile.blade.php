@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('my_profile') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">الملف الشخصي</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">الملف الشخصي</li>
            </ul>
        </div>
    </div>
</div>

<div class="card subscriber-profile-card">
    <div class="card-body">
        <h4 class="subscriber-profile-title">معلومات الحساب</h4>

        {!! Form::model($admin, ['route' => ['admin.update_profile'], 'method' => 'POST', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
            @php
                $storedPhone = preg_replace('/\D+/', '', (string) ($admin->phone ?? ''));
                if (str_starts_with($storedPhone, '00966')) {
                    $storedPhone = substr($storedPhone, 5);
                } elseif (str_starts_with($storedPhone, '966')) {
                    $storedPhone = substr($storedPhone, 3);
                }
                if (str_starts_with($storedPhone, '0')) {
                    $storedPhone = substr($storedPhone, 1);
                }
            @endphp

            <div class="row subscriber-profile-fields">
                <div class="col-lg-6 form-group">
                    {!! Form::label('name', 'الاسم الكامل', ['class' => 'form-label']) !!}
                    {!! Form::text('name', null, ['class' => 'form-control']) !!}
                </div>
                <div class="col-lg-6 form-group">
                    {!! Form::label('user_name', 'الاسم المستعار', ['class' => 'form-label']) !!}
                    {!! Form::text('user_name', null, ['class' => 'form-control']) !!}
                </div>

                <div class="col-lg-6 form-group">
                    {!! Form::label('email', 'البريد الإلكتروني', ['class' => 'form-label']) !!}
                    {!! Form::email('email', null, ['class' => 'form-control']) !!}
                </div>
                <div class="col-lg-6 form-group">
                    <label for="admin-phone-local" class="form-label">رقم الجوال</label>
                    <div class="saudi-phone-field" dir="ltr">
                        <div class="saudi-phone-prefix">
                            <span class="saudi-phone-flag" aria-hidden="true">🇸🇦</span>
                            <span>+966</span>
                            <span class="saudi-phone-chevron" aria-hidden="true">⌄</span>
                        </div>
                        <input type="tel" id="admin-phone-local" name="phone_local"
                               value="{{ old('phone_local', $storedPhone) }}"
                               class="form-control saudi-phone-input" inputmode="numeric"
                               autocomplete="tel-national" maxlength="9" placeholder="5XXXXXXXX"
                               dir="ltr" aria-describedby="admin-phone-help admin-phone-error" required>
                    </div>
                    <input type="hidden" name="phone" id="admin-phone" value="{{ old('phone', $admin->phone) }}">
                    <small id="admin-phone-help" class="form-text saudi-phone-help">سيتم استخدام رقم الجوال لتسجيل الدخول واستقبال الإشعارات</small>
                    <div id="admin-phone-error" class="invalid-feedback saudi-phone-error" role="alert">يرجى إدخال رقم جوال سعودي صحيح</div>
                </div>

                <div class="col-12">
                    <div class="subscriber-image-section">
                        <div class="subscriber-image-details">
                            <label class="form-label mb-3">الصورة الشخصية</label>
                            <div class="subscriber-image-actions">
                                <button type="button" class="subscriber-image-button subscriber-image-delete" id="clear-profile-image">
                                    <i class="fa-regular fa-trash-can"></i> حذف الصورة
                                </button>
                                <label for="image" class="subscriber-image-button subscriber-image-change">
                                    <i class="fa-solid fa-arrow-up-from-bracket"></i> تغيير الصورة
                                </label>
                            </div>
                            <div class="subscriber-image-note">الصيغ المسموحة: JPG, PNG. الحد الأقصى: 2MB</div>
                            <input type="file" name="image" id="image" class="d-none" accept=".jpg,.jpeg,.png">
                        </div>
                        <div class="subscriber-image-preview">
                            @include('dashboard.partials.avatar', ['path' => Auth::guard('admin')->user()->image, 'name' => Auth::guard('admin')->user()->name, 'size' => 88])
                        </div>
                    </div>
                </div>
            </div>

            <div class="subscriber-profile-submit">
                <button type="submit" class="btn subscriber-save-button">
                    حفظ التغييرات <i class="fa-regular fa-floppy-disk"></i>
                </button>
            </div>
        {!! Form::close() !!}
    </div>
</div>
@endsection

@push('css')
<style>
    .subscriber-profile-card { border: 0; border-radius: 16px; box-shadow: 0 10px 30px rgba(31, 52, 75, .07); }
    .subscriber-profile-card .card-body { padding: 32px 34px 28px; }
    .subscriber-profile-title { margin: 0 0 26px; color: #172b4d; font-size: 18px; font-weight: 700; }
    .subscriber-profile-fields .form-group { margin-bottom: 28px; }
    .subscriber-profile-fields .form-label { display: block; margin-bottom: 10px; color: #344054; font-size: 14px; font-weight: 500; }
    .subscriber-profile-fields .form-control { height: 48px; padding: 10px 16px; border: 1px solid #dfe5ec; border-radius: 8px; color: #344054; background: #fff; text-align: right; }
    .subscriber-profile-fields .form-control:focus { border-color: #b8c8d5; box-shadow: none; }
    .saudi-phone-field { display: flex; width: 100%; min-height: 48px; overflow: hidden; border: 1px solid #dfe5ec; border-radius: 8px; background: #fff; }
    .saudi-phone-prefix { display: flex; align-items: center; gap: 13px; min-width: 150px; padding: 0 14px; border-right: 1px solid #dfe5ec; color: #344054; background: #fff; font-size: 18px; white-space: nowrap; }
    .saudi-phone-flag { display: inline-block; width: 38px; height: 28px; border-radius: 7px; background-image: url('{{ asset('assets/plugins/intltelinput/img/flags.png') }}'); background-size: 11304px 30px; background-position: -8678px 0; background-repeat: no-repeat; font-size: 0; line-height: 0; }
    .saudi-phone-chevron { width: 10px; height: 10px; margin-left: auto; margin-right: 1px; border-right: 2px solid #344054; border-bottom: 2px solid #344054; transform: rotate(45deg) translateY(-2px); font-size: 0; line-height: 0; }
    .saudi-phone-input.form-control { min-width: 0; height: auto; border: 0; border-radius: 0; box-shadow: none; direction: ltr; text-align: right; letter-spacing: .04em; }
    .saudi-phone-input.form-control:focus { border: 0; box-shadow: none; }
    .saudi-phone-help { display: block; margin-top: 8px; color: #8a94a6; font-size: 12px; line-height: 1.7; }
    .saudi-phone-error { display: none; margin-top: 6px; }
    .saudi-phone-field.is-invalid { border-color: #f64e60; }
    .saudi-phone-field.is-invalid + input + .saudi-phone-help + .saudi-phone-error { display: block; }
    .subscriber-image-section { display: flex; align-items: center; justify-content: flex-end; gap: 28px; min-height: 150px; margin-top: 4px; direction: ltr; }
    .subscriber-image-details { text-align: right; }
    .subscriber-image-actions { display: flex; gap: 28px; align-items: center; }
    .subscriber-image-button { display: inline-flex; align-items: center; gap: 8px; min-height: 42px; padding: 8px 16px; border: 1px solid #cbd5df; border-radius: 8px; color: #344054; background: #fff; font-size: 13px; cursor: pointer; }
    .subscriber-image-change { border-color: #0f5132; color: #0f5132; font-weight: 600; }
    .subscriber-image-delete { background: #fff; }
    .subscriber-image-note { margin-top: 12px; color: #8a94a6; font-size: 12px; }
    .subscriber-image-preview { display: flex; align-items: center; justify-content: center; width: 100px; height: 100px; border: 1px solid #e1e7ec; border-radius: 50%; background: #fff; }
    .subscriber-image-preview .md-avatar, .subscriber-image-preview .md-avatar-placeholder { border-radius: 50%; }
    .subscriber-profile-submit { display: flex; justify-content: flex-start; margin-top: 18px; direction: ltr; }
    .subscriber-save-button { min-width: 150px; padding: 11px 20px; border: 0; border-radius: 8px; color: #fff; background: #0f5132; font-size: 14px; font-weight: 600; }
    .subscriber-save-button:hover { color: #fff; background: #0b4128; }
    @media (max-width: 767.98px) {
        .subscriber-profile-card .card-body { padding: 22px 18px; }
        .subscriber-image-section { align-items: flex-start; justify-content: flex-start; }
        .subscriber-image-actions { gap: 8px; flex-wrap: wrap; }
    }
</style>
@endpush

@section('scripts_lib')
<script>
    (function () {
        const form = document.getElementById('kt_form_1');
        const input = document.getElementById('admin-phone-local');
        const hidden = document.getElementById('admin-phone');
        const field = input && input.closest('.saudi-phone-field');
        const imageInput = document.getElementById('image');
        const clearImage = document.getElementById('clear-profile-image');
        if (!form || !input || !hidden || !field) return;
        const normalizeDigits = value => value.replace(/[٠-٩]/g, digit => String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit))).replace(/\D/g, '');
        const localNumber = () => { let value = normalizeDigits(input.value); if (value.indexOf('00966') === 0) value = value.slice(5); else if (value.indexOf('966') === 0) value = value.slice(3); if (value.indexOf('0') === 0) value = value.slice(1); return value; };
        input.addEventListener('input', () => { input.value = localNumber(); hidden.value = input.value ? '+966' + input.value : ''; field.classList.remove('is-invalid'); });
        form.addEventListener('submit', event => { const value = localNumber(); input.value = value; hidden.value = '+966' + value; if (!/^5\d{8}$/.test(value)) { event.preventDefault(); field.classList.add('is-invalid'); input.focus(); } });
        if (clearImage && imageInput) clearImage.addEventListener('click', () => { imageInput.value = ''; });
    })();
</script>
@endsection
