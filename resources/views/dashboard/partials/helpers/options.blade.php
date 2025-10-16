<div class="col-lg-6 form-group">
    {!! Form::label('department_id', TranslationHelper::translate('Department'), ['class'=>'form-label']) !!}
    <select name="department_id" id="department_selector" class="form-control">
        <option value="" disabled selected>{{ TranslationHelper::translate('Choose Department') }}</option>
        @foreach ($departments as $department)
            <option value="{{ $department->id }}">{{ $department->name }}</option>
        @endforeach
    </select>
</div>
@if ($page == 'category' || $page == 'sub_category')
    <div class="col-lg-6 form-group">
        {!! Form::label('category_id', TranslationHelper::translate('Category'), ['class'=>'form-label']) !!}
        <select name="category_id" id="category_selector" class="form-control">
            <option value="" disabled selected>{{ TranslationHelper::translate('Choose Category') }}</option>
            @if(isset($categories))
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            @endif
        </select>
    </div>
@endif
@if ($page == 'sub_category')
    <div class="col-lg-6 form-group" id="sub_category_selector">

    </div>
@endif