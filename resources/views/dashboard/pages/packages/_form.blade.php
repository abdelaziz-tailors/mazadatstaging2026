
<div class="row">
    @if(isset($packages))
        @php
            $names = json_decode($packages->name, true);
            $description = json_decode($packages->description, true);
        @endphp
    @else
        @php $names = []; @endphp
        @php $description = []; @endphp
        @php $instructions = []; @endphp
    @endif
    <div class="col-12 d-flex">
        <div class="card flex-fill">
            <div class='card-body'>
                {{-- English tab hidden — values synced from Arabic on save --}}
                {{--
                <div class="card-header">
                    <ul role="tablist" class="nav nav-tabs card-header-tabs float-start">
                        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                            <li class="nav-item">
                                <a href="#tab-{{ $localeCode }}" data-bs-toggle="tab" class="nav-link @if($loop->index == 0) active @endif ">
                                    <img class="me-3 rounded-circle" src="{{ asset('dashboard/img/language/'.$localeCode.'.png') }}" width="31" alt="{{ Auth::guard('admin')->user()->name }}" /> {{ $properties['native'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="tab-content pt-0">
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <div role="tabpanel" id="tab-{{ $localeCode }}" class="tab-pane fade @if($loop->index == 0) show active @endif "
                             dir="{{ TranslationHelper::language_direction($localeCode) }}" >
                            <div class="col-lg-6 form-group">
                                {!! Form::label('name['.$localeCode.']', TranslationHelper::translate('Name', $localeCode), ['class'=>'form-label']) !!}
                                {!! Form::text('name['.$localeCode.']', (is_array($names) && array_key_exists($localeCode, $names)) ? $names[$localeCode] : NULL, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-lg-12 form-group">
                                {!! Form::label('description['.$localeCode.']', TranslationHelper::translate('Description', $localeCode), ['class'=>'form-label']) !!}
                                {!! Form::textarea('description['.$localeCode.']', (is_array($description) && array_key_exists($localeCode, $description)) ? $description[$localeCode] : NULL, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
                --}}

                <div class="col-lg-6 form-group">
                    {!! Form::label('name[ar]', TranslationHelper::translate('Name', 'ar'), ['class'=>'form-label']) !!}
                    {!! Form::text('name[ar]', (is_array($names) && array_key_exists('ar', $names)) ? $names['ar'] : NULL, ['class' => 'form-control']) !!}
                </div>
                <div class="col-lg-12 form-group">
                    {!! Form::label('description[ar]', TranslationHelper::translate('Description', 'ar'), ['class'=>'form-label']) !!}
                    {!! Form::textarea('description[ar]', (is_array($description) && array_key_exists('ar', $description)) ? $description['ar'] : NULL, ['class' => 'form-control']) !!}
                </div>
                <div class="row">




                    {{-- <div class="col-lg-6 form-group">
                        {!! Form::label('coin', TranslationHelper::translate('Coin'), ['class'=>'form-label']) !!}
                        {!! Form::number('coin', isset($packages) ? $packages->coin : NULL, ['class' => 'form-control', 'step' => '0.01']) !!}
                    </div> --}}

                    {{-- <div class="col-lg-6 form-group">
                        {!! Form::label('price', TranslationHelper::translate('Price'), ['class'=>'form-label']) !!}
                        {!! Form::number('price', isset($packages) ? $packages->price : NULL, ['class' => 'form-control', 'step' => '0.01']) !!}
                    </div> --}}

                    {{-- <div class="col-lg-6 form-group">
                        {!! Form::label('subscription_type', TranslationHelper::translate('Subscription Type'), ['class'=>'form-label']) !!}
                        {!! Form::select('subscription_type', ['' => TranslationHelper::translate('Select Type'), 'monthly' => TranslationHelper::translate('Monthly'), 'annual' => TranslationHelper::translate('Annual')], isset($packages) ? $packages->subscription_type : NULL, ['class' => 'form-control']) !!}
                    </div> --}}

                    <div class="col-lg-6 form-group">
                        {!! Form::label('auctions_limit', TranslationHelper::translate('Auctions Limit'), ['class'=>'form-label']) !!}
                        {!! Form::number('auctions_limit', isset($packages) ? $packages->auctions_limit : NULL, ['class' => 'form-control', 'min' => '0']) !!}
                    </div>

                    <div class="col-lg-6 form-group">
                        {!! Form::label('monthly_price', TranslationHelper::translate('monthly_price'), ['class'=>'form-label']) !!}
                        {!! Form::number('monthly_price', isset($packages) ? $packages->monthly_price : NULL, ['class' => 'form-control', 'step' => '0.01', 'min' => '0']) !!}
                    </div>

                    <div class="col-lg-6 form-group">
                        {!! Form::label('annual_price', TranslationHelper::translate('annual_price'), ['class'=>'form-label']) !!}
                        {!! Form::number('annual_price', isset($packages) ? $packages->annual_price : NULL, ['class' => 'form-control', 'step' => '0.01', 'min' => '0']) !!}
                    </div>

                    <div class="form-group @if (isset($packages)) col-lg-5 @else col-lg-6 @endif ">
                        {!! Form::label('image', TranslationHelper::translate('PNG Image'), ['class' => 'form-label']) !!}
                        <input type="file" id="image_png" name="image_png" class="form-control" />
                    </div>
                    @if (isset($packages))
                        <div class="form-group col-lg-1">
                            @if (Storage::disk('public')->exists($packages->image))
                                <img src="{{ Storage::disk('public')->url($packages->image) }}" class="img-fluid" />
                            @else
                            @endif
                        </div>
                    @endif

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
                            @if(isset($packages)) @if($packages->is_active == 1) checked @endif @else checked @endif>
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
