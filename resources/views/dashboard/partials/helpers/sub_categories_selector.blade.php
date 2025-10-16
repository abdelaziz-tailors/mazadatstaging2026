{!! Form::label('sub_category_id', TranslationHelper::translate('Sub-Category'), ['class'=>'form-label']) !!}
<select name="sub_category_id" id="sub_category_id" class="form-control">
    <option value="" disabled selected>{{ TranslationHelper::translate('Choose Sub-Category') }}</option>
    @foreach ($categories as $category)
        <option value="{{ $category->id }}">{{ $category->name }}</option>
    @endforeach
</select>