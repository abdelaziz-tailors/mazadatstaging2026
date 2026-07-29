<div class="form-group mb-3">
    {!! Form::label('name', TranslationHelper::translate('full_name'), ['class' => 'form-label']) !!}
    {!! Form::text('name', old('name'), ['class' => 'form-control' . ($errors->has('name') ? ' is-invalid' : '')]) !!}
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
    {!! Form::label('email', TranslationHelper::translate('email'), ['class' => 'form-label']) !!}
    {!! Form::email('email', old('email'), ['class' => 'form-control' . ($errors->has('email') ? ' is-invalid' : '')]) !!}
    @if ($errors->has('email'))
        <div class="invalid-feedback d-block">{{ $errors->first('email') }}</div>
    @endif
</div>

<div class="form-group mb-3">
    {!! Form::label('phone', TranslationHelper::translate('mobile_number'), ['class' => 'form-label']) !!}
    <div class="md-phone-field">
        <span class="md-phone-code-badge">🇸🇦 +966</span>
        {!! Form::text('phone', old('phone'), ['class' => 'form-control' . ($errors->has('phone') ? ' is-invalid' : ''), 'placeholder' => '5X XXX XXXX', 'maxlength' => 9]) !!}
    </div>
    @if ($errors->has('phone'))
        <div class="invalid-feedback d-block">{{ $errors->first('phone') }}</div>
    @endif
</div>

@if (!isset($admin))
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
@endif

<div class="form-group mb-3">
    {!! Form::label('commercial_register', TranslationHelper::translate('commercial_register'), ['class' => 'form-label']) !!}
    <input type="file" name="commercial_register" id="commercial_register" class="form-control{{ $errors->has('commercial_register') ? ' is-invalid' : '' }}" accept=".jpeg,.jpg,.png,.pdf">
    @if ($errors->has('commercial_register'))
        <div class="invalid-feedback d-block">{{ $errors->first('commercial_register') }}</div>
    @endif
    @if (isset($admin) && $admin->user && $admin->user->commercial_register)
        <a href="{{ Storage::disk('public')->url($admin->user->commercial_register) }}" target="_blank" class="d-block mt-2">
            {{ TranslationHelper::translate('commercial_register') }}
        </a>
    @endif
</div>

<button type="submit" class="btn btn-primary w-100 mt-2" id="kt_submit">{{ TranslationHelper::translate('save') }}</button>
