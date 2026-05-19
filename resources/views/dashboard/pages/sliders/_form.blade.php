<div class="row">
    <div class="col-12 d-flex">
        <div class="card flex-fill">
            <div class="card-body">
                <div class="row">
                    <div class="form-group @if (isset($slider)) col-lg-5 @else col-lg-6 @endif">
                        {!! Form::label('image', TranslationHelper::translate('Image'), ['class' => 'form-label']) !!}
                        <input type="file" id="image" name="image" class="form-control" />
                    </div>

                    @if (isset($slider))
                        <div class="form-group col-lg-1">
                            @if (Storage::disk('public')->exists($slider->image))
                                <img src="{{ Storage::disk('public')->url($slider->image) }}" class="img-fluid" />
                            @endif
                        </div>
                    @endif

                    <div class="col-lg-6 form-group">
                        {!! Form::label('link', TranslationHelper::translate('link'), ['class' => 'form-label']) !!}
                        {!! Form::url('link', NULL, ['class' => 'form-control']) !!}
                    </div>

                    <div class="col-lg-6 form-group">
                        {!! Form::label('position', TranslationHelper::translate('position'), ['class' => 'form-label']) !!}
                        {!! Form::number('position', isset($slider) ? $slider->position : 0, ['class' => 'form-control', 'min' => 0]) !!}
                    </div>

                    <div class="col-lg-6 form-group">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                   @if(isset($slider) && $slider->is_active == 1) checked @elseif(!isset($slider)) checked @endif>
                            <label class="form-check-label" for="is_active">{{ TranslationHelper::translate('Is Active') }}</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="kt_submit">{{ TranslationHelper::translate('save') }}</button>
            </div>
        </div>
    </div>
</div>
