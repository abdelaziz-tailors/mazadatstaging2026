<option value="" disabled selected>{{ TranslationHelper::translate('Choose Category') }}</option>
@foreach ($categories as $category)
    <option value="{{ $category->id }}">{{ $category->name }}</option>
@endforeach