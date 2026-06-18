
<div class="col-12 d-flex">
    <div class="card flex-fill">
        <div class='card-body'>

            <div class="row">

                @if ($errors->any())
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                            <div class="col-12"><hr></div>

                            <div class="col-lg-6 form-group">
                                {!! Form::label('title', TranslationHelper::translate('title') . ' *', ['class'=>'form-label']) !!}
                                {!! Form::text('title', old('title', $data->title ?? null), ['class' => 'form-control'.($errors->has('title') ? ' is-invalid' : ''), 'required']) !!}
                                @error('title')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-6 form-group">
                                {!! Form::label('title_ar', TranslationHelper::translate('title ar') . ' *', ['class'=>'form-label']) !!}
                                {!! Form::text('title_ar', old('title_ar', $data->title_ar ?? null), ['class' => 'form-control'.($errors->has('title_ar') ? ' is-invalid' : ''), 'required']) !!}
                                @error('title_ar')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-6 form-group">
                                {!! Form::label('date_start_at', TranslationHelper::translate('date start') . ' *', ['class'=>'form-label']) !!}
                                {!! Form::date('date_start_at', old('date_start_at', isset($data) ? \Carbon\Carbon::parse($data->date_start_at)->format('Y-m-d') : null), ['class' => 'form-control'.($errors->has('date_start_at') ? ' is-invalid' : ''), 'required']) !!}
                                @error('date_start_at')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-6 form-group">
                                {!! Form::label('date_end_at', TranslationHelper::translate('date end') . ' *', ['class'=>'form-label']) !!}
                                {!! Form::date('date_end_at', old('date_end_at', isset($data) ? \Carbon\Carbon::parse($data->date_end_at)->format('Y-m-d') : null), ['class' => 'form-control'.($errors->has('date_end_at') ? ' is-invalid' : ''), 'required']) !!}
                                @error('date_end_at')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-6 form-group">
                                {!! Form::label('time_start_at', TranslationHelper::translate('time start') . ' *', ['class'=>'form-label']) !!}
                                {!! Form::time('time_start_at', old('time_start_at', $data->time_start_at ?? null), ['class' => 'form-control'.($errors->has('time_start_at') ? ' is-invalid' : ''), 'required']) !!}
                                @error('time_start_at')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-6 form-group">
                                {!! Form::label('time_end_at', TranslationHelper::translate('time end') . ' *', ['class'=>'form-label']) !!}
                                {!! Form::time('time_end_at', old('time_end_at', $data->time_end_at ?? null), ['class' => 'form-control'.($errors->has('time_end_at') ? ' is-invalid' : ''), 'required']) !!}
                                @error('time_end_at')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-6 form-group">
                                {!! Form::label('information', TranslationHelper::translate('information'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('information', old('information', $data->information ?? null), ['class' => 'form-control'.($errors->has('information') ? ' is-invalid' : '')]) !!}
                                @error('information')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-6 form-group">
                                {!! Form::label('information_ar', TranslationHelper::translate('information_ar'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('information_ar', old('information_ar', $data->information_ar ?? null), ['class' => 'form-control'.($errors->has('information_ar') ? ' is-invalid' : '')]) !!}
                                @error('information_ar')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-6 form-group">
                                {!! Form::label('terms_conditions', TranslationHelper::translate('terms conditions'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('terms_conditions', old('terms_conditions', $data->terms_conditions ?? null), ['class' => 'form-control'.($errors->has('terms_conditions') ? ' is-invalid' : '')]) !!}
                                @error('terms_conditions')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-6 form-group">
                                {!! Form::label('terms_conditions_ar', TranslationHelper::translate('terms conditions ar'), ['class'=>'form-label']) !!}
                                {!! Form::textArea('terms_conditions_ar', old('terms_conditions_ar', $data->terms_conditions_ar ?? null), ['class' => 'form-control'.($errors->has('terms_conditions_ar') ? ' is-invalid' : '')]) !!}
                                @error('terms_conditions_ar')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-6 form-group">
                                <label class="form-label">{{ TranslationHelper::translate('City') }}</label>
                                <select class="form-control @error('city_id') is-invalid @enderror" id="city_id" name="city_id">
                                    <option value="">{{ TranslationHelper::translate('Select city') }}</option>
                                    @forelse($cities as $city)
                                        <option @if (isset($data)) @if($city->id ==$data->city_id?? 0) selected @endif @endif value="{{ $city->id }}">{{ $city->name }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('city_id')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-6 form-group">
                                <label class="form-label">{{ TranslationHelper::translate('video_type') }} <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    {!! Form::radio('type', 'live', isset($data) ? $data->type === 'live' : true, ['class' => 'form-check-input', 'id' => 'type_live']) !!}
                                    <label class="form-check-label" for="type_live">{{ TranslationHelper::translate('live') }}</label>
                                </div>
                                <div class="form-check">
                                    {!! Form::radio('type', 'recorded', isset($data) ? $data->type === 'recorded' : false, ['class' => 'form-check-input', 'id' => 'type_recorded']) !!}
                                    <label class="form-check-label" for="type_recorded">{{ TranslationHelper::translate('recorded') }}</label>
                                </div>
                                <div class="form-check">
                                    {!! Form::radio('type', 'photo', isset($data) ? $data->type === 'photo' : false, ['class' => 'form-check-input', 'id' => 'type_photo']) !!}
                                    <label class="form-check-label" for="type_photo">{{ TranslationHelper::translate('photo_auction') }}</label>
                                </div>
                                @error('type')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            @if (!($isPartnerDashboard ?? false))
                            <!-- Partners Type Radio -->
                            <div class="col-6 form-group mt-4">
                                <label class="form-label">{{ TranslationHelper::translate('partners_type') }} <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    {!! Form::radio('partners_type', 'single', isset($data) ? $data->partners_type === 'single' : true, ['class' => 'form-check-input', 'id' => 'partners_type_single']) !!}
                                    <label class="form-check-label" for="partners_type_single">{{ TranslationHelper::translate('Single Partner') }}</label>
                                </div>
                                <div class="form-check">
                                    {!! Form::radio('partners_type', 'multiple', isset($data) ? $data->partners_type === 'multiple' : false, ['class' => 'form-check-input', 'id' => 'partners_type_multiple']) !!}
                                    <label class="form-check-label" for="partners_type_multiple">{{ TranslationHelper::translate('Multiple Partners') }}</label>
                                </div>
                                @error('partners_type')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-6 form-group" id="partner-select-field">
                                <label class="form-label">{{ TranslationHelper::translate('Partner') }}</label>
                                <select class="form-control @error('partner_id') is-invalid @enderror" id="partner_id" name="partner_id">
                                    <option value="">{{ TranslationHelper::translate('Select Partner') }}</option>
                                    @forelse($providers as $provider)
                                        <option @if (isset($data)) @if($provider->id ==$data->partner_id?? 0) selected @endif @endif value="{{ $provider->id }}">{{ $provider->name }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('partner_id')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            @endif


                            <div class="col-lg-6 form-group">
                                {!! Form::label('start_price', TranslationHelper::translate('start price') . ' *', ['class'=>'form-label']) !!}
                                {!! Form::number('start_price', old('start_price', $data->start_price ?? null), ['step'=>"0.01", 'min'=>'0', 'class' => 'form-control'.($errors->has('start_price') ? ' is-invalid' : ''), 'required']) !!}
                                @error('start_price')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-12"><h6 class="text-muted mt-2">{{ TranslationHelper::translate('auction_fees') }}</h6></div>
                            <div class="col-lg-3 form-group">
                                {!! Form::label('tax_amount', TranslationHelper::translate('tax_amount'), ['class'=>'form-label']) !!}
                                {!! Form::number('tax_amount', old('tax_amount', isset($data) ? $data->tax_amount : null), ['class' => 'form-control', 'min'=>'0']) !!}
                                @error('tax_amount')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-lg-3 form-group">
                                {!! Form::label('commission_amount', TranslationHelper::translate('commission_amount'), ['class'=>'form-label']) !!}
                                {!! Form::number('commission_amount', old('commission_amount', isset($data) ? $data->commission_amount : null), ['class' => 'form-control', 'min'=>'0']) !!}
                                @error('commission_amount')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-lg-3 form-group">
                                <label class="form-label" for="commission_payer">{{ TranslationHelper::translate('commission_payer') }}</label>
                                <select name="commission_payer" id="commission_payer" class="form-control">
                                    <option value="buyer" >{{ TranslationHelper::translate('buyer') }}</option>
                                    <option value="seller">{{ TranslationHelper::translate('seller') }}</option>
                                </select>
                                @error('commission_payer')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-lg-3 form-group">
                                {!! Form::label('service_fee', TranslationHelper::translate('service_fee'), ['class'=>'form-label']) !!}
                                {!! Form::number('service_fee', old('service_fee', isset($data) ? $data->service_fee : null), ['class' => 'form-control', 'min'=>'0']) !!}
                                @error('service_fee')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-lg-6">
                                {!! Form::label('image', TranslationHelper::translate('PNG Images'), ['class' => 'form-label']) !!}
                                <input type="file" multiple id="image_png" name="image[]" class="form-control @error('image') is-invalid @enderror @error('image.*') is-invalid @enderror" accept="image/*" />
                                @error('image')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                                @error('image.*')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            @if (isset($data))
                                @forelse(json_decode($data->image) as $feature)
                                    @if (Storage::disk('public')->exists($feature))
                                        <div class="form-group col-lg-1">
                                            <img src="{{ Storage::disk('public')->url($feature) }}" class="img-fluid" />
                                        </div>
                                    @endif
                                @empty
                                @endforelse
                            @endif

                        </div>
        <button type="submit" name="action" value="save" class="btn btn-primary">
                {{ TranslationHelper::translate('save') }}
            </button>
            @if (isset($data))
            <button type="submit" name="action" value="add_product" class="btn btn-secondary">
                {{ TranslationHelper::translate('add Product') }}
            </button>
            @endif
            </div>
        </div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>


@if (!($isPartnerDashboard ?? false))
<script>
   $(document).ready(function () {
    $('input[name="partners_type"]').change(function() {
        if ($(this).val() === 'single') {
            $('#partner-select-field').show();
        } else {
            $('#partner-select-field').hide();
        }
    });

    // Trigger change on page load
    $('input[name="partners_type"]:checked').trigger('change');
});
</script>
@endif




<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $("select").select2();
    });
</script>
