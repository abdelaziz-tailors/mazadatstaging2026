<div class="row">
    @if(isset($data))
        @php $names = json_decode($data->name, true); @endphp
    @else
        @php $names = []; @endphp
    @endif
    <div class="col-12 d-flex">
        <div class="card flex-fill">
            <div class="card-body">
                <div class="row">
                    @if(!empty($showPartnerSelect))
                        <div class="col-lg-6 form-group">
                            {!! Form::label('admin_id', TranslationHelper::translate('select_partner'), ['class' => 'form-label']) !!}
                            <select name="admin_id" class="form-control" required>
                                <option value="">{{ TranslationHelper::translate('select') }}</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}" @if((string) ($selectedPartnerId ?? '') === (string) $partner->id) selected @endif>
                                        {{ $partner->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <div class="col-lg-6 form-group">
                            {!! Form::label('name['.$localeCode.']', TranslationHelper::translate('Name '.$localeCode), ['class'=>'form-label']) !!}
                            {!! Form::text('name['.$localeCode.']', (is_array($names) && array_key_exists($localeCode, $names)) ? $names[$localeCode] : null, ['class' => 'form-control']) !!}
                        </div>
                    @endforeach

                    <div class="col-lg-6 form-group">
                        {!! Form::label('default_price', TranslationHelper::translate('default_price'), ['class'=>'form-label']) !!}
                        {!! Form::number('default_price', $data->default_price ?? null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0']) !!}
                    </div>

                    <div class="col-lg-6 form-group">
                        {!! Form::label('sort_order', TranslationHelper::translate('sort_order'), ['class'=>'form-label']) !!}
                        {!! Form::number('sort_order', $data->sort_order ?? 0, ['class' => 'form-control', 'min' => '0']) !!}
                    </div>

                    <div class="col-6 form-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                   @if(isset($data)) @if($data->is_active == 1) checked @endif @else checked @endif>
                            <label class="form-check-label" for="is_active">{{ TranslationHelper::translate('Is Active') }}</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ TranslationHelper::translate('save') }}</button>
            </div>
        </div>
    </div>
</div>
