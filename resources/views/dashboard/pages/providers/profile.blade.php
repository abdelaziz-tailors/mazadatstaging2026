@extends('dashboard.layouts.app')

@section('title')
    {{ TranslationHelper::translate('Profile') }}
@endsection

@section('content')
    <div class="page-header">
        <div class="row">
            <div class="col">
                <h3 class="page-title">{{ TranslationHelper::translate('Profile') }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a
                            href="{{ route('admin.providers.index') }}">{{ TranslationHelper::translate('providers') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ TranslationHelper::translate('Profile') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6 form-group">
                    {!! Form::label('degree', TranslationHelper::translate('degree'), ['class' => 'form-label']) !!}
                    {!! Form::text('degree', $profile->degree ?? old('degree'), ['class' => 'form-control']) !!}
                </div>
                <div class="col-lg-6 form-group">
                    {!! Form::label('experience_years', TranslationHelper::translate('experience_years'), ['class' => 'form-label']) !!}
                    {!! Form::number('experience_years', $profile->experience_years ?? (old('experience_years')??0), [
                        'class' => 'form-control',
                        'placeholder' => 0,'min'=>0
                    ]) !!}
                </div>





                {{-- <select name="page" id="banner_page_selector" class="form-control">
                    <option value="home" @if (isset($banner) && $banner->page == 'home') selected @endif >{{ TranslationHelper::translate('Home') }}</option>
                    <option value="department" @if (isset($banner) && $banner->page == 'department') selected @endif >{{ TranslationHelper::translate('Department') }}</option>
                    <option value="category" @if (isset($banner) && $banner->page == 'category') selected @endif >{{ TranslationHelper::translate('Category') }}</option>
                    <option value="sub_category" @if (isset($banner) && $banner->page == 'sub_category') selected @endif >{{ TranslationHelper::translate('Sub-Category') }}</option>
                </select> --}}
                {{-- <div class="col-lg-6 form-group">
                    {!! Form::label('phone', TranslationHelper::translate('phone'), ['class' => 'form-label']) !!}
                    {!! Form::text('phone', null, ['class' => 'form-control']) !!}
                </div> --}}
                <div class="col-lg-5 form-group">
                    {!! Form::label('national_id_front', TranslationHelper::translate('national id front'), ['class' => 'form-label']) !!}
                    <input type="file" name="national_id_front" id="image" class="form-control" />
                </div>
                <div class="col-lg-1 form-group">
                    <img src="{{ checkImageExists($profile->national_id_front?? null) }}"
                        alt="national_id_front" class="avatar-img rounded-circle img-fluid" />
                </div>

                <div class="col-lg-5 form-group">
                    {!! Form::label('national_id_back', TranslationHelper::translate('national id back'), ['class' => 'form-label']) !!}
                    <input type="file" name="national_id_back" id="image" class="form-control" />
                </div>
                <div class="col-lg-1 form-group">
                    <img src="{{ checkImageExists($profile->national_id_back?? null) }}"
                        alt="national_id_back" class="avatar-img rounded-circle img-fluid" />
                </div>


                <div class="col-lg-5 form-group">
                    {!! Form::label('scientific_certificate', TranslationHelper::translate('scientific certificate'), ['class' => 'form-label']) !!}
                    <input type="file" name="scientific_certificate" id="image" class="form-control" />
                </div>
                <div class="col-lg-1 form-group">
                    <img src="{{ checkImageExists($profile->scientific_certificate?? null) }}"
                        alt="scientific_certificate" class="avatar-img rounded-circle img-fluid" />
                </div>


                <div class="col-lg-5 form-group">
                    {!! Form::label('passport', TranslationHelper::translate('passport'), ['class' => 'form-label']) !!}
                    <input type="file" name="passport" id="image" class="form-control" />
                </div>
                <div class="col-lg-1 form-group">
                    <img src="{{ checkImageExists($profile->passport?? null) }}"
                        alt="passport" class="avatar-img rounded-circle img-fluid" />
                </div>

                <div class="col-lg-5 form-group">
                    {!! Form::label('residence', TranslationHelper::translate('residence'), ['class' => 'form-label']) !!}
                    <input type="file" name="residence" id="image" class="form-control" />
                </div>
                <div class="col-lg-1 form-group">
                    <img src="{{ checkImageExists($profile->residence?? null) }}"
                        alt="residence" class="avatar-img rounded-circle img-fluid" />
                </div>


                <div class="col-lg-5 form-group">
                    {!! Form::label('signed_contract', TranslationHelper::translate('signed contract'), ['class' => 'form-label']) !!}
                    <input type="file" name="signed_contract" id="image" class="form-control" />
                </div>
                <div class="col-lg-1 form-group">
                    <img src="{{ checkImageExists($profile->signed_contract?? null) }}"
                        alt="signed_contract" class="avatar-img rounded-circle img-fluid" />
                </div>

                <div class="col-lg-6 form-group">
                    <label class="form-check-label"> {{ TranslationHelper::translate('Gender') }}</label>
                    <div class="form-check ftch">
                        <input class="form-check-input" id="gender_male" type="radio" name="gender" value="male"  @if($provider->gender == 'male') checked @endif>
                        <label class="form-check-label" for="gender_male">
                            {{ TranslationHelper::translate('Male') }}</label>
                    </div>
                    <div class="form-check ftch">
                        <input class="form-check-input" id="gender_female" type="radio" name="gender"
                            value="female" @if($provider->gender == 'female') checked @endif>
                        <label class="form-check-label" for="gender_female">
                            {{ TranslationHelper::translate('Female') }}</label>
                    </div>
                </div>

                <div class="col-lg-6 form-group">
                    {!! Form::label('nationality_id', TranslationHelper::translate('Nationality'), ['class'=>'form-label']) !!}
                    <select class="form-control select" name="nationality_id" id="nationality_id">
                        <option value="" disabled selected>{{ TranslationHelper::translate('Choose Nationality') }}</option>
                        @foreach ($nationalities as $nationality)
                            <option value="{{ $nationality->id }}"
                                @if($nationality->id == $provider->nationality_id) selected @endif
                            >
                                {{ $nationality->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-6 form-group">
                    {!! Form::label('country_id', TranslationHelper::translate('Country'), ['class'=>'form-label']) !!}
                    <select class="form-control select" name="country_id" id="country_id">
                        <option value="" disabled selected>{{ TranslationHelper::translate('Choose Country') }}</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}"
                                @if($country->id == $provider->country_id) selected @endif
                            >
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-6 form-group">
                    {!! Form::label('birth_date', TranslationHelper::translate('Birth Date'), ['class' => 'form-label']) !!}
                    {!! Form::date('birth_date', $provider->birth_date ?? old('birth_date'), ['class' => 'form-control','max'=> date('Y-m-d',strtotime('-22 year')) ]) !!}
                </div>
                {{-- before:'.now()->subYears(18)->toDateString() --}}

                <div class="col-lg-6 form-group">
                    <label class="form-check-label"> {{ TranslationHelper::translate('Foundation') }}</label>
                    <div class="form-check ftch">
                        <input class="form-check-input foundation_radio" id="foundation_oun" type="radio" name="foundation" value="oun"  @if($profile &&  $profile->foundation == 'oun') checked @endif>
                        <label class="form-check-label" for="foundation_oun">
                            {{ TranslationHelper::translate('oun') }}</label>
                    </div>
                    <div class="form-check ftch">
                        <input class="form-check-input foundation_radio" id="foundation_other" type="radio" name="foundation"
                            value="other" @if($profile &&  $profile->foundation == 'other') checked @endif>
                        <label class="form-check-label" for="foundation_other">
                            {{ TranslationHelper::translate('other') }}</label>
                    </div>
                </div>

                <div id="otherAnswer" class="col-lg-6 form-group" style="@if(!$profile ||  $profile->foundation == 'oun') display: none; @endif ">
                    {!! Form::label('foundation name', TranslationHelper::translate('foundation name'), ['class' => 'form-label']) !!}
                    {!! Form::text('foundation_name', $profile->foundation_name ?? old('foundation_name'), [
                        'class' => 'form-control',
                    ]) !!}
                </div>

            </div>
            <div class="row">
                <div class="col-lg-6 form-group">
                    {!! Form::label('description', TranslationHelper::translate('Description'), ['class' => 'form-label']) !!}
                    {!! Form::textarea('description', $profile->description ?? old('description'), ['class'=>'form-control']) !!}
                </div>

                <div class="col-lg-3 form-group">
                    <label class="form-check-label"> {{ TranslationHelper::translate('Languages') }}</label>
                    @foreach ($languages as $language)

                        <div class="form-check ftch">
                            <input class="form-check-input " id="lan_{{$language->id}}" type="checkbox" name="languages[]" value="{{$language->id}}"
                            @if($profile &&  in_array($language->id ,$profile->languages->pluck('id')->toArray()) ) checked @endif
                            >
                            <label class="form-check-label" for="lan_{{$language->id}}">
                                {{ $language->name }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="col-lg-3 form-group">
                    <label class="form-check-label"> {{ TranslationHelper::translate('Over Night') }}</label>

                    <div class="form-check ftch">
                        <input class="form-check-input " id="over_night0" type="radio" name="over_night" value="0"
                        @if($profile && $profile->over_night == 0)  checked @endif
                        >
                        <label class="form-check-label" for="over_night0">
                            {{ TranslationHelper::translate('No') }}</label>
                    </div>

                    <div class="form-check ftch">
                        <input class="form-check-input " id="over_night1" type="radio" name="over_night" value="1"
                        @if($profile && $profile->over_night == 1)  checked @endif
                        >
                        <label class="form-check-label" for="over_night1">
                            {{ TranslationHelper::translate('Yes') }}</label>
                    </div>


                </div>

            </div>
            {!! Form::close() !!}
        </div>
    </div>


@endsection

@section('scripts_lib')
    <script>
        $(".foundation_radio").change(function() {
            if ($(this).val() == "other") {
                $("#otherAnswer").show();
            } else {
                $("#otherAnswer").hide();
            }
        });



    $(document).ready(function() {
            window.initSelectStationDrop=()=>{
                $('.select').select2({
                    placeholder: "Select department",
                    dir: 'ltr'
                });
            }
            initSelectStationDrop();
            window.livewire.on('select',()=>{
                initSelectStationDrop();
            });

        });
</script>

<script>
    $(document).ready(function() {

      $('#department_id').change(function () {
          var department_id =  $('#department_id').val();
          livewire.emit('selecteddepartment', department_id);

      })
      $('#category_id').change(function () {
          var category_id =  $('#category_id').val();
          livewire.emit('selectedcategory', category_id);

      })

      $('#sub_category_id').change(function () {
          var sub_category_id =  $('#sub_category_id').val();
          livewire.emit('selectedsubcategories', sub_category_id);

      })

    });
</script>
@endsection
