
<div class="col-12 d-flex">
    <div class="card flex-fill">
        <div class='card-body'>

        <div class="row">



                            <div class="col-lg-6 form-group">
                                <label class="form-label">{{ TranslationHelper::translate('City') }}</label>
                                <select class="form-control" id="city_id" name="city_id">
                                    <option value="">{{ TranslationHelper::translate('Select city') }}</option>
                                    @forelse($cities as $city)
                                        <option @if (isset($data)) @if($city->id ==$data->city_id?? 0) selected @endif @endif value="{{ $city->id }}">{{ $city->name }}</option>

                                    @empty
                                    @endforelse
                                </select>
                            </div>



                            <div class="col-lg-6 form-group">
                                {!! Form::label('title', TranslationHelper::translate('title'), ['class'=>'form-label']) !!}
                                {!! Form::text('title', NULL, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-lg-6 form-group">
                                {!! Form::label('title_ar', TranslationHelper::translate('title ar'), ['class'=>'form-label']) !!}
                                {!! Form::text('title_ar', NULL, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-lg-6 form-group">
                                {!! Form::label('date_start_at', TranslationHelper::translate('date start'), ['class'=>'form-label']) !!}
                                {!! Form::date('date_start_at', NULL, ['class' => 'form-control']) !!}
                            </div>

                            <div class="col-lg-6 form-group">
                                {!! Form::label('date_end_at', TranslationHelper::translate('date end'), ['class'=>'form-label']) !!}
                                {!! Form::date('date_end_at', NULL, ['class' => 'form-control']) !!}
                            </div>

                            <div class="col-lg-6 form-group">
                                {!! Form::label('time_start_at', TranslationHelper::translate('time start'), ['class'=>'form-label']) !!}
                                {!! Form::time('time_start_at', NULL, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-lg-6 form-group">
                                {!! Form::label('time_end_at', TranslationHelper::translate('time end'), ['class'=>'form-label']) !!}
                                {!! Form::time('time_end_at', NULL, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-lg-6 form-group">

                                {!! Form::label('information', TranslationHelper::translate('information'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('information', NULL, ['class' => 'form-control']) !!}
                            </div>

                            <div class="col-lg-6 form-group">

                                {!! Form::label('information_ar', TranslationHelper::translate('information_ar'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('information_ar', NULL, ['class' => 'form-control']) !!}
                            </div>



                            <div class="col-lg-6 form-group">

                                {!! Form::label('terms_conditions', TranslationHelper::translate('terms conditions'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('terms_conditions', NULL, ['class' => 'form-control']) !!}
                            </div>


                            <div class="col-lg-6 form-group">

                                {!! Form::label('terms_conditions_ar', TranslationHelper::translate('terms conditions ar'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('terms_conditions_ar', NULL, ['class' => 'form-control']) !!}
                            </div>





{{--                            <div class="form-group  col-lg-6 ">--}}
{{--                                {!! Form::label('image', TranslationHelper::translate('PNG Images'), ['class' => 'form-label']) !!}--}}
{{--                                <input type="file" multiple id="image_png" name="image[]" class="form-control" />--}}
{{--                            </div>--}}

{{--                            @if (isset($data))--}}

{{--                                @forelse(json_decode($data->image) as $feature)--}}
{{--                                    @if (Storage::disk('public')->exists($feature))--}}
{{--                                        <div class="form-group col-lg-1">--}}

{{--                                        <img src="{{ Storage::disk('public')->url($feature) }}" class="img-fluid" />--}}
{{--                                        </div>--}}
{{--                                    @endif--}}


{{--                                @empty--}}

{{--                                @endforelse--}}


{{--                            @endif--}}



                        </div>
        <button type="submit" name="action" value="save" class="btn btn-primary">
                {{ TranslationHelper::translate('save') }}
            </button>
            @if (!isset($data))
            <button type="submit" name="action" value="add_product" class="btn btn-secondary">
                {{ TranslationHelper::translate('add Product') }}
            </button>
            @endif
            </div>
        </div>
    </div>
</div>


{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>--}}
{{--<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />--}}
{{--<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>--}}
{{--<script>--}}

{{--    $(document).ready(function () {--}}
{{--//change selectboxes to selectize mode to be searchable--}}
{{--        $("select").select2();--}}
{{--    });--}}

{{--</script>--}}
