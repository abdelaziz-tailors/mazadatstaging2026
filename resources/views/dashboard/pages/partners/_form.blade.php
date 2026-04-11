<div class="row">
    <div class="col-lg-6 form-group">
        {!! Form::label('name', TranslationHelper::translate('name'), ['class'=>'form-label']) !!}
        {!! Form::text('name', NULL, ['class' => 'form-control']) !!}
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('user_name', TranslationHelper::translate('user_name'), ['class'=>'form-label']) !!}
        {!! Form::text('user_name', NULL, ['class' => 'form-control']) !!}
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('email', TranslationHelper::translate('email'), ['class'=>'form-label']) !!}
        {!! Form::email('email', NULL, ['class' => 'form-control']) !!}
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('phone', TranslationHelper::translate('phone'), ['class'=>'form-label']) !!}
        {!! Form::text('phone', NULL, ['class' => 'form-control']) !!}
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('national_id', TranslationHelper::translate('national_identity'), ['class'=>'form-label']) !!}
        {!! Form::text('national_id', isset($admin) ? $admin->national_id : null, ['class' => 'form-control', 'dir' => 'ltr', 'autocomplete' => 'off']) !!}
    </div>
    @if (isset($admin))
        <div class="col-lg-5 form-group">
    @else
        <div class="col-lg-6 form-group">
    @endif
        {!! Form::label('image', TranslationHelper::translate('image'), ['class'=>'form-label']) !!}
        <input type="file" name="image" id="image" class="form-control" />
    </div>
    @if (isset($admin))
        <div class="col-lg-1 form-group">
            <img src="{{ Storage::disk('public')->url($admin->image) }}" alt="{{ $admin->name }}" class="avatar-img rounded-circle img-fluid" />
        </div>
    @endif

    {{-- <div class="col-lg-6 form-group">
        {!! Form::label('email', TranslationHelper::translate('email'), ['class'=>'form-label']) !!}
        {!! Form::email('email', NULL, ['class' => 'form-control']) !!}
    </div> --}}

    {{-- <div class="col-6 form-group">
        <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" id="is_verified" name="is_verified"
            @if(isset($admin)) @if($admin->user->is_verified == 1) checked @endif @else checked @endif>
            <label class="form-check-label" for="is_verified">  {{ TranslationHelper::translate('Is Verified') }}</label>
        </div>
    </div> --}}



</div>

@if (!isset($admin))
    <div class="row">
        <div class="col-lg-6 form-group">
            {!! Form::label('password', TranslationHelper::translate('password'), ['class'=>'form-label']) !!}
            <input type="password" name="password" id="password" class="form-control" />
        </div>
        <div class="col-lg-6 form-group">
            {!! Form::label('password_confirmation', TranslationHelper::translate('password_confirmation'), ['class'=>'form-label']) !!}
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" />
        </div>
    </div>
@endif

<button type="submit" class="btn btn-primary" id="kt_submit">{{ TranslationHelper::translate('save') }}</button>
