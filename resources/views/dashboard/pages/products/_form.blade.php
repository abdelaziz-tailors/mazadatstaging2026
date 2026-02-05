
<div class="col-12 d-flex">
    <div class="card flex-fill">
        <div class='card-body'>

        <div class="row">





                        @if ($live_video->partners_type=='single')

                        <div class="col-lg-12 form-group">
                            <label class="form-label">{{ TranslationHelper::translate('Vendor') }}</label>

                            <select class="form-control" id="user_id" disabled name="">
                                <option value="">{{ TranslationHelper::translate('Select Vendor') }}</option>
                                @forelse($providers as $provider)
                                    <option @if($provider->id ==$live_video->partner_id ?? 0) selected  @endif value="{{ $provider->id }}">{{ $provider->name }}</option>

                                @empty
                                @endforelse
                            </select>
                            <input type="hidden" name="user_id" value="{{ $live_video->partner_id }}">
                        </div>



                        @else


                            <div class="col-lg-12 form-group">
                                <label class="form-label">{{ TranslationHelper::translate('Vendor') }}</label>
                                <select class="form-control" id="user_id" name="user_id">
                                    <option value="">{{ TranslationHelper::translate('Select Vendor') }}</option>
                                    @forelse($providers as $provider)
                                        <option @if (isset($data)) @if($provider->id ==$data->user_id ?? 0) selected @endif @endif value="{{ $provider->id }}">{{ $provider->name }}</option>

                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        @endif



                            {{-- <div class="col-lg-6 form-group">
                                <label class="form-label">{{ TranslationHelper::translate('Color') }}</label>
                                <select class="form-control" id="colorSelect" name="color_id">
                                    <option value="">{{ TranslationHelper::translate('Select Color') }}</option>
                                    @foreach($colors as $color)
                                        <option
                                            value="{{ $color->id }}"
                                            data-color="{{ $color->color }}"
                                            style="background: {{ $color->color }};"
                                            {{ (isset($data) && $color->id == ($data->color_id ?? 0)) ? 'selected' : '' }}>
                                        </option>
                                    @endforeach
                                </select>
                            </div> --}}
                                <input type="hidden" name="video_id" value="{{$id ?? $data->live_video_id }}">

                            <div class="col-lg-6 form-group">
                                <label class="form-label">{{ TranslationHelper::translate('Category') }}</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">{{ TranslationHelper::translate('Select Category') }}</option>
                                    @forelse($categories as $category)
                                        <option @if (isset($data)) @if($category->id ==$data->category_id?? 0) selected @endif @endif value="{{ $category->id }}">{{ $category->name }}</option>

                                    @empty
                                    @endforelse
                                </select>
                            </div>


                            <div class="col-lg-4 form-group">
                                <label class="form-label">{{ TranslationHelper::translate('Age') }}</label>
                                <select class="form-control" id="age_id" name="age">
                                <option value="">{{ TranslationHelper::translate('Select Age') }}</option>
                                @forelse($ages as $age)
                                    <option @if (isset($data)) @if($age->name ==$data->age) selected @endif @endif value="{{ $age->name }}">{{ $age->name }}</option>

                                @empty
                                @endforelse
                                </select>
                            </div>

                            <div class="col-lg-4 form-group">
                                {!! Form::label('age_type', TranslationHelper::translate('age type'), ['class'=>'form-label']) !!}

                                <select class="form-control" id="age_type" name="age_type">
                                    <option value="">{{ TranslationHelper::translate('Select age type') }}</option>
                                    <option @if (isset($data)) @if('year' ==$data->age_type?? 0) selected @endif @endif value="year">{{ TranslationHelper::translate('year') }}</option>
                                    <option @if (isset($data)) @if('month' ==$data->age_type?? 0) selected @endif @endif value="month">{{ TranslationHelper::translate('month') }}</option>
                                </select>
                            </div>









                            {{-- <div class="col-lg-6 form-group">
                                <label class="form-label">{{ TranslationHelper::translate('Age') }}</label>





                                <select class="form-control" id="age_id" name="age_id">
                                    <option value="">{{ TranslationHelper::translate('Select Age') }}</option>
                                    @forelse($ages as $age)
                                        <option @if (isset($data)) @if($age->id ==$data->category_id?? 0) selected @endif @endif value="{{ $age->id }}">{{ $age->name }}</option>

                                    @empty
                                    @endforelse
                                </select>
                            </div>
                                --}}

                            <div class="col-lg-4 form-group">
                                <label class="form-label">{{ TranslationHelper::translate('Type') }}</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="">{{ TranslationHelper::translate('Select Type') }}</option>
                                    <option @if (isset($data)) @if('male' ==$data->type?? 0) selected @endif @endif value="male">{{ TranslationHelper::translate('male') }}</option>
                                    <option @if (isset($data)) @if('female' ==$data->type?? 0) selected @endif @endif value="female">{{ TranslationHelper::translate('female') }}</option>

                                </select>
                            </div>


                            <div class="col-lg-6 form-group">
                                {!! Form::label('title', TranslationHelper::translate('lineage title'), ['class'=>'form-label']) !!}
                                {!! Form::text('title', NULL, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-lg-6 form-group">
                                {!! Form::label('title_ar', TranslationHelper::translate(' lineage title ar'), ['class'=>'form-label']) !!}
                                {!! Form::text('title_ar', NULL, ['class' => 'form-control']) !!}
                            </div>
                            {{-- <div class="col-lg-6 form-group">
                                {!! Form::label('date_barth', TranslationHelper::translate('date of  barth'), ['class'=>'form-label']) !!}
                                {!! Form::date('date_barth', NULL, ['class' => 'form-control']) !!}
                            </div> --}}
                            {{-- <div class="col-lg-4 form-group">
                                {!! Form::label('weight', TranslationHelper::translate('weight'), ['class'=>'form-label']) !!}
                                {!! Form::number('weight', NULL, [ 'step'=>"0.01",'class' => 'form-control']) !!}
                            </div> --}}
                            <div class="col-lg-4 form-group">
                                {!! Form::label('start_price', TranslationHelper::translate('start price'), ['class'=>'form-label']) !!}
                                {!! Form::number('start_price', NULL, ['step'=>"0.01",'class' => 'form-control']) !!}
                            </div>
                            <div class="col-lg-4 form-group">
                                {!! Form::label('bidding', TranslationHelper::translate('bidding Price'), ['class'=>'form-label']) !!}
                                {!! Form::number('bidding', NULL, ['step'=>"0.01",'class' => 'form-control']) !!}
                            </div>




                            {{-- <div class="col-lg-6 form-group">
                                <label class="form-label">{{ TranslationHelper::translate('animal pen ') }}</label>
                                <select class="form-control" id="animal_pen" name="animal_pen_id">
                                    <option value="">{{ TranslationHelper::translate('Select animal pen ') }}</option>
                                    @forelse($animal_pens as $animal_pen)
                                        <option @if (isset($data)) @if($animal_pen->id ==$age->animal_pen?? 0) selected @endif @endif value="{{ $animal_pen->id }}">{{ $animal_pen->name }}</option>

                                    @empty
                                    @endforelse

                                </select>
                            </div>
 --}}


                            <div class="col-lg-6 form-group">

                                {!! Form::label('address', TranslationHelper::translate('address'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('address', NULL, ['class' => 'form-control']) !!}
                            </div>



                            <div class="col-lg-6 form-group" @if($live_video->partners_type=='single') @endif >
                                {!! Form::label('health_certificate', TranslationHelper::translate('health certificate'), ['class'=>'form-label']) !!}
                                <input type="file"  id="image_png" name="health_certificate" class="form-control" />
                                @if (isset($data))
                                <a href="{{ Storage::disk('public')->url($data->health_certificate) }}" target="_blank">
                                <img src="{{ Storage::disk('public')->url($data->health_certificate) }}" style="width: 100px; height: 100px;" alt="{{ $data->name }}" class="avatar-img rounded-circle img-fluid" />
                                </a>
                                @endif

                            </div>


                            <div class="col-lg-6 form-group">

                                {!! Form::label('information', TranslationHelper::translate('information'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('information', NULL, ['class' => 'form-control']) !!}
                            </div>

                            <div class="col-lg-6 form-group">

                                {!! Form::label('information_ar', TranslationHelper::translate('information ar'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('information_ar', NULL, ['class' => 'form-control']) !!}
                            </div>


                            <div class="col-lg-6 form-group">

                                {!! Form::label('terms', TranslationHelper::translate('terms'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('terms', NULL, ['class' => 'form-control']) !!}
                            </div>

                            <div class="col-lg-6 form-group">

                                {!! Form::label('terms_ar', TranslationHelper::translate('terms ar'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('terms_ar', NULL, ['class' => 'form-control']) !!}
                            </div>



                            @if ($live_video->type == 'recorded')
                            <div class="col-lg-6 form-group">
                                {!! Form::label('video', TranslationHelper::translate('video'), ['class'=>'form-label']) !!}

                                <input type="file"  id="image_png" name="video" class="form-control" />
                                @if (isset($data))
                                <a href="{{ Storage::disk('public')->url($data->video) }}" target="_blank">
                                <embed src="{{ Storage::disk('public')->url($data->video) }}" style="width: 300px; height: 300px;" alt="{{ $data->name }}" />
                                </a>
                                @endif
                            </div>

                            @endif



                        <div class="form-group  col-lg-6 ">
                                {!! Form::label('image', TranslationHelper::translate('PNG Images'), ['class' => 'form-label']) !!}
                                <input type="file" multiple id="image_png" name="image[]" class="form-control" />

                            @if (isset($data))
                            <div class="row">

                                @forelse(json_decode($data->image) as $feature)
                                    @if (Storage::disk('public')->exists($feature))

                                        <div class="form-group col-lg-2">
                                            <a href="{{ Storage::disk('public')->url($feature) }}" target="_blank">
                                                <img src="{{ Storage::disk('public')->url($feature) }}" class="img-fluid" />
                                            </a>
                                        </div>
                                    @endif


                                @empty

                                @endforelse

                            </div>
                            @endif



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

<script>
    const select = document.getElementById('colorSelect');

    function updateSelectBackground() {
        const selectedOption = select.options[select.selectedIndex];
        const color = selectedOption.getAttribute('data-color');
        if (color) {
            select.style.backgroundColor = color;
        } else {
            select.style.backgroundColor = ''; // Reset
        }
    }

    // Set background on page load
    window.addEventListener('DOMContentLoaded', updateSelectBackground);

    // Update background when user changes selection
    select.addEventListener('change', updateSelectBackground);
</script>

{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>--}}
{{--<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />--}}
{{--<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>--}}
{{--<script>--}}

{{--    $(document).ready(function () {--}}
{{--//change selectboxes to selectize mode to be searchable--}}
{{--        $("select").select2();--}}
{{--    });--}}

{{--</script>--}}
