<div class="row">
    <div class="col-lg-6 form-group">
        {!! Form::label('department_id', TranslationHelper::translate('Department'), ['class'=>'form-label']) !!}
        <select class="form-control select7" name="department_id" id="department_id" wire:model="department">
            <option value=""  selected>{{ TranslationHelper::translate('Choose Department') }}</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}">
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 form-group">
        {!! Form::label('category_id', TranslationHelper::translate('Category'), ['class'=>'form-label']) !!}
        <select class="form-control select7" name="category_id" id="category_id" wire:model="category">
            <option value=""  selected>{{ TranslationHelper::translate('Choose Category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">
                    {{ $category->name }}
                    {{-- {{($category->name['en']??'') }} --}}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 form-group">
        {!! Form::label('sub_category_id', TranslationHelper::translate('Sub Category'), ['class'=>'form-label']) !!}
        <select class="form-control select7" multiple name="sub_category_id[]" id="sub_category_id" wire:model="sub_category"  style="min-height: 100px">
            <option value=""  selected>{{ TranslationHelper::translate('Choose Sub Category') }}</option>
            @foreach ($sub_categories as $sub)
                <option value="{{ $sub->id }}">
                    {{ $sub->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 form-group">
       
        @foreach ($sub_categories_prices as $key => $sub_cat)
            <div class="col-md-6">
                <label for="p{{$key}}" class="form-label">{{TranslationHelper::translate('Price for ') 
                    .'('. $sub_cat['category_name'] .')'
                    }}</label>
                <input class="form-control" name="sub_categories_price[{{$key}}]"  type="number"  required id="p{{$key}}" value="{{ $sub_cat['price']??''}}">
            </div>

        @endforeach

    </div>


</div>


