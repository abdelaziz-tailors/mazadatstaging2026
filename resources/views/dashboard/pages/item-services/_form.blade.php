@php $names = isset($data) ? (json_decode($data->name, true) ?: []) : []; @endphp

@if(!empty($showPartnerSelect))
    <div class="form-group mb-3">
        {!! Form::label('admin_id', TranslationHelper::translate('select_partner'), ['class' => 'form-label']) !!}
        <select name="admin_id" class="form-select form-control{{ $errors->has('admin_id') ? ' is-invalid' : '' }}" required>
            <option value="">{{ TranslationHelper::translate('select') }}</option>
            @foreach($partners as $partner)
                <option value="{{ $partner->id }}" @if((string) ($selectedPartnerId ?? '') === (string) $partner->id) selected @endif>
                    {{ $partner->name }}
                </option>
            @endforeach
        </select>
        @if ($errors->has('admin_id'))
            <div class="invalid-feedback d-block">{{ $errors->first('admin_id') }}</div>
        @endif
    </div>
@endif

<div class="form-group mb-3">
    {!! Form::label('name_ar', TranslationHelper::translate('name'), ['class' => 'form-label']) !!}
    {!! Form::text('name[ar]', old('name.ar', $names['ar'] ?? null), ['class' => 'form-control' . ($errors->has('name.ar') ? ' is-invalid' : '')]) !!}
    @if ($errors->has('name.ar'))
        <div class="invalid-feedback d-block">{{ $errors->first('name.ar') }}</div>
    @endif
</div>

<div class="form-group mb-3">
    {!! Form::label('default_price', TranslationHelper::translate('default_price'), ['class' => 'form-label']) !!}
    {!! Form::number('default_price', old('default_price', $data->default_price ?? null), ['class' => 'form-control' . ($errors->has('default_price') ? ' is-invalid' : ''), 'step' => '0.01', 'min' => '0']) !!}
    @if ($errors->has('default_price'))
        <div class="invalid-feedback d-block">{{ $errors->first('default_price') }}</div>
    @endif
</div>

<div class="form-group mb-3">
    {!! Form::label('sort_order', TranslationHelper::translate('sort_order'), ['class' => 'form-label']) !!}
    {!! Form::number('sort_order', old('sort_order', $data->sort_order ?? 0), ['class' => 'form-control' . ($errors->has('sort_order') ? ' is-invalid' : ''), 'min' => '0']) !!}
    @if ($errors->has('sort_order'))
        <div class="invalid-feedback d-block">{{ $errors->first('sort_order') }}</div>
    @endif
</div>

<div class="form-group mb-3">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
               @if(isset($data)) @if($data->is_active == 1) checked @endif @else checked @endif>
        <label class="form-check-label" for="is_active">{{ TranslationHelper::translate('Is Active') }}</label>
    </div>
</div>

<button type="submit" class="btn btn-primary w-100 mt-2">{{ TranslationHelper::translate('save') }}</button>
