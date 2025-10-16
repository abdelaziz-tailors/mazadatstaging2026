
<div class="row">
    @if(isset($data))
        @php
            $names = json_decode($data->name, true);
        @endphp
    @else
        @php $names = []; @endphp
        @php $description = []; @endphp
        @php $instructions = []; @endphp
    @endif
    <div class="col-12 d-flex">
        <div class="card flex-fill">
            <div class='card-body'>
                <div class="tab-content pt-0">

                </div>
                <div class="row">

                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                            <div class="col-lg-6 form-group">
                                {!! Form::label('name['.$localeCode.']', TranslationHelper::translate('Name '. $localeCode), ['class'=>'form-label']) !!}
                                {!! Form::text('name['.$localeCode.']', (is_array($names) && array_key_exists($localeCode, $names)) ? $names[$localeCode] : NULL, ['class' => 'form-control']) !!}
                            </div>
                    @endforeach




{{--                    @if (isset($gift))--}}
{{--                        <div class="form-group col-lg-1">--}}
{{--                            @if (Storage::disk('public')->exists($gift->image_svg))--}}
{{--                                <img src="{{ Storage::disk('public')->url($gift->image_svg) }}" class="img-fluid" />--}}
{{--                            @else--}}
{{--                            @endif--}}
{{--                        </div>--}}
{{--                    @endif--}}







                    <div class="col-6 form-group">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                            @if(isset($data)) @if($data->is_active == 1) checked @endif @else checked @endif>
                            <label class="form-check-label" for="is_active">  {{ TranslationHelper::translate('Is Active') }}</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="kt_submit">{{ TranslationHelper::translate('save') }}</button>
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
