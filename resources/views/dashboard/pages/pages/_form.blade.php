
<div class="row">
    @if(isset($packages))
        @php
            $names = json_decode($packages->name, true);
            $description = json_decode($packages->description, true);
        @endphp
    @else
        @php $names = []; @endphp
        @php $description = []; @endphp
    @endif
    <div class="col-12 d-flex">
        <div class="card flex-fill">
            <div class='card-body'>
                <div class="row align-items-start">

                    {{-- English fields hidden — values synced from Arabic on save --}}
                    {{--
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <div class="col-lg-6 form-group">
                            {!! Form::label('name['.$localeCode.']', TranslationHelper::translate('Name '. $localeCode), ['class'=>'form-label']) !!}
                            {!! Form::text('name['.$localeCode.']', (is_array($names) && array_key_exists($localeCode, $names)) ? $names[$localeCode] : NULL, ['class' => 'form-control']) !!}
                        </div>
                    @endforeach
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <div class="col-lg-6 form-group">
                            {!! Form::label('description['.$localeCode.']', TranslationHelper::translate('Description '. $localeCode), ['class'=>'form-label']) !!}
                            {!! Form::textarea('description['.$localeCode.']', (is_array($description) && array_key_exists($localeCode, $description)) ? $description[$localeCode] : NULL, ['class' => 'form-control']) !!}
                        </div>
                    @endforeach
                    --}}

                    {{-- Grouped in one column (rather than each field being its own
                    top-level col-lg-6 sibling) so the name/image fields stack inside
                    their own column regardless of how tall the description column
                    next to them is — flex "row" wrapping otherwise starts this
                    column's second line only after the *tallest* column in the row
                    above it, leaving a large empty gap here whenever the textarea
                    grows past a couple of lines. --}}
                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('name[ar]', TranslationHelper::translate('Name ar'), ['class'=>'form-label']) !!}
                            {!! Form::text('name[ar]', (is_array($names) && array_key_exists('ar', $names)) ? $names['ar'] : NULL, ['class' => 'form-control']) !!}
                        </div>

                        <div class="d-flex align-items-start gap-2">
                            <div class="form-group flex-fill">
                                {!! Form::label('image', TranslationHelper::translate('PNG Image'), ['class' => 'form-label']) !!}
                                <input type="file" id="image_png" name="image_png" class="form-control" />
                            </div>
                            @if (isset($packages) && Storage::disk('public')->exists($packages->image))
                                <div class="form-group">
                                    <img src="{{ Storage::disk('public')->url($packages->image) }}" class="img-fluid" />
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-6 form-group">
                        {!! Form::label('description[ar]', TranslationHelper::translate('Description ar'), ['class'=>'form-label']) !!}
                        {!! Form::textarea('description[ar]', (is_array($description) && array_key_exists('ar', $description)) ? $description['ar'] : NULL, ['class' => 'form-control', 'rows' => 6]) !!}
                    </div>

{{--                    @if (isset($gift))--}}
{{--                        <div class="form-group col-lg-1">--}}
{{--                            @if (Storage::disk('public')->exists($gift->image_svg))--}}
{{--                                <img src="{{ Storage::disk('public')->url($gift->image_svg) }}" class="img-fluid" />--}}
{{--                            @else--}}
{{--                            @endif--}}
{{--                        </div>--}}
{{--                    @endif--}}







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
