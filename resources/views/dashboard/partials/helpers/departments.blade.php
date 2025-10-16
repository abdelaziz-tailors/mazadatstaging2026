{!! Form::label('department_id', TranslationHelper::translate('Department'), ['class'=>'form-label']) !!}
<select name="department_id" id="department_selector" class="form-control">
    @foreach ($departments as $department)
        <option value="{{ $department->id }}">{{ $department->name }}</option>
    @endforeach
</select>