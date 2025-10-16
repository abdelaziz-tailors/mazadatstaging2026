<div class="row">
    @if (!isset($data))
    @endif
    @if(isset($data))
        @php
        $names = json_decode($data->name, true);
        @endphp
    @else
        @php $names = []; @endphp
    @endif
    <div class="col-12 d-flex">
        <div class="card flex-fill">
            <div class='card-body'>
                <div class="row">
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <div class="col-lg-6 form-group">
                            <div class="form-check form-switch">

                            {!! Form::label('name['.$localeCode.']', TranslationHelper::translate('Name '.$localeCode), ['class'=>'form-label']) !!}
                            {!! Form::text('name['.$localeCode.']', (is_array($names) && array_key_exists($localeCode, $names)) ? $names[$localeCode] : NULL, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    @endforeach

                        <div class="col-6 form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                       @if(isset($city)) @if($city->is_active == 1) checked @endif @else checked @endif>
                                <label class="form-check-label" for="is_active">  {{ TranslationHelper::translate('Is Active') }}</label>
                            </div>
                        </div>
                </div>
                <button type="submit" class="btn btn-primary" id="kt_submit">{{ TranslationHelper::translate('save') }}</button>
            </div>
        </div>
    </div>

