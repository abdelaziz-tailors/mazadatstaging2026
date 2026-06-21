<div class="row">
    <div class="col-lg-6 form-group">
        {!! Form::label('name', TranslationHelper::translate('name'), ['class'=>'form-label']) !!}
        {!! Form::text('name', NULL, ['class' => 'form-control']) !!}
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('email', TranslationHelper::translate('email'), ['class'=>'form-label']) !!}
        {!! Form::email('email', NULL, ['class' => 'form-control']) !!}
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('phone', TranslationHelper::translate('phone'), ['class'=>'form-label']) !!}
        {!! Form::text('phone', NULL, ['class' => 'form-control']) !!}
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
    <div class="col-lg-6 form-group">
        {!! Form::label('role_id', TranslationHelper::translate('role'), ['class'=>'form-label']) !!}
        <select class="form-control select" name="role_id" id="role_id">
            <option value="" disabled selected>{{ TranslationHelper::translate('choose_role') }}</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}"
                    @if(isset($admin))
                        @if($admin->hasRole($role)) selected @endif
                    @endif
                >
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

@if (!isset($admin))
    <div class="row">
        <div class="col-lg-6 form-group">
            {!! Form::label('password', TranslationHelper::translate('password'), ['class'=>'form-label']) !!}
            <input type="text" name="password" id="password" class="form-control" />
        </div>
        <div class="col-lg-6 form-group">
            {!! Form::label('password_confirmation', TranslationHelper::translate('password_confirmation'), ['class'=>'form-label']) !!}
            <input type="text" name="password_confirmation" id="password_confirmation" class="form-control" />
        </div>
    </div>
@endif

<button type="submit" class="btn btn-primary" id="kt_submit">{{ TranslationHelper::translate('save') }}</button>
