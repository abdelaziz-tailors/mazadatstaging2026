{!! Form::label('position', TranslationHelper::translate('Position'), ['class'=>'form-label']) !!}
<select name="position" id="position_selector" class="form-control">
    <option value="top">{{ TranslationHelper::translate('Top') }}</option>
    <option value="bottom">{{ TranslationHelper::translate('Bottom') }}</option>
</select>