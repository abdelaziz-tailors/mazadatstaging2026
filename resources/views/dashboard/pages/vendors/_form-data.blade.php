<div class="row">
    <div class="col-lg-6 form-group">
        {!! Form::label('name', TranslationHelper::translate('name'), ['class'=>'form-label']) !!}
        {!! Form::text('name', NULL, ['class' => 'form-control']) !!}
    </div>




    <div class="col-lg-6 form-group">
        {!! Form::label('parent_id', TranslationHelper::translate('Department'), ['class'=>'form-label']) !!}
        <select class="form-control" name="department" id="department">
{{--            <option>{{ TranslationHelper::translate('Department') }}</option>--}}
            @foreach ($department as $departments)
                <option value="{{ $departments->id }}"
                        @if(isset($provider))
                            @if($provider->department_id == $departments->id) selected @endif
                    @endif
                >
                    {{ $departments->name }}
                </option>
            @endforeach
        </select>
    </div>


    <div class="col-lg-6 form-group">
        {!! Form::label('email', TranslationHelper::translate('email'), ['class'=>'form-label']) !!}
        {!! Form::email('email', NULL, ['class' => 'form-control']) !!}
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('phone', TranslationHelper::translate('phone'), ['class'=>'form-label']) !!}
        {!! Form::text('phone', NULL, ['class' => 'form-control','placeholder' => 'phone number EX. 1007982550']) !!}
    </div>
    <div class="col-lg-12 form-group" id="doctor_job">
        {!! Form::label('Jobs', TranslationHelper::translate('Jobs'), ['class'=>'form-label']) !!}

        <div class="row form-control2" style="border: 1px solid #cccccc;padding: 10px 10px;">

            @forelse($job as $jobs)

                <div class="col-sm-6">

                    <input class=""  @if(is_array(old('job')) && in_array($jobs->id, old('job'))) checked @endif type="checkbox" name="job[]" value="{{$jobs->id}}">

                    {{$jobs->name ??''}}
                </div>
            @empty
            @endforelse

            <span class="form-text text-danger">{{$errors->has('job')? $errors->first("job"):''}}</span>
        </div>
        <br>
    </div>
    <div class="col-lg-12 form-group" id="doctor_pets" style="display: none">
        {!! Form::label('Jobs', TranslationHelper::translate('Jobs'), ['class'=>'form-label']) !!}

        <div class="row form-control2" style="border: 1px solid #cccccc;padding: 10px 10px;">

            @forelse($job_bats as $jobs)

                <div class="col-sm-6">

                    <input class=""  @if(is_array(old('job')) && in_array($jobs->id, old('job'))) checked @endif type="checkbox" name="job[]" value="{{$jobs->id}}">

                    {{$jobs->name ??''}}
                </div>
            @empty
            @endforelse

            <span class="form-text text-danger">{{$errors->has('job')? $errors->first("job"):''}}</span>
        </div>
        <br>
    </div>



    <div class="col-lg-12 form-group">
        {!! Form::label('city', TranslationHelper::translate('City'), ['class'=>'form-label']) !!}

        <select class="form-control select" name="city" id="city">
            <option   >{{ TranslationHelper::translate('City') }}</option>
            @foreach ($city as $cities)
                <option value="{{ $cities->id }}"
                        @if(isset($provider))
                            @if($provider->city_id == $cities->id) selected @endif
                    @endif
                >
                    {{ $cities->name }}
                </option>
            @endforeach
        </select>


    </div>


    <div class="col-lg-12 form-group">

        {!! Form::label('city', TranslationHelper::translate('Location'), ['class'=>'form-label']) !!}
        @php

//                $Seeting = new \App\Helpers\Seeting();

        @endphp


        <input id="searchTextField" name="location" class="form-control" type="text" value="" placeholder="{{TranslationHelper::translate('Location')}}">
        <input type="hidden" name="lat" class="MapLat" value="24.7135517"  placeholder="Latitude" style="width: 161px;" >
        <input type="hidden" name="lng" class="MapLon" value="46.6752957"  placeholder="Longitude" style="width: 161px;" >
        <div id="map_canvas" style="height: 350px;width: 100%;margin: 0.6em;"></div>

        <span  class="form-text text-danger">{{$errors->has('location')? $errors->first("location"):''}}</span>
    </div>







@if (!isset($provider))
    <div class="row">
        <div class="col-lg-6 form-group">
            {!! Form::label('password', TranslationHelper::translate('password'), ['class'=>'form-label']) !!}
            <input type="password" name="password" id="password" class="form-control" />
        </div>
        <div class="col-lg-6 form-group">
            {!! Form::label('password_confirmation', TranslationHelper::translate('password_confirmation'), ['class'=>'form-label']) !!}
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" />
        </div>
    </div>
@endif


    <div class="form-group @if (isset($provider)) col-lg-5 @else col-lg-6 @endif ">
        {!! Form::label('image', TranslationHelper::translate('Scientific_Certificate_Image'), ['class' => 'form-label']) !!}
        <input type="file" id="scientific_certificate_image" name="scientific_certificate_image" class="form-control" />
    </div>
    @if (isset($provider))
        <div class="form-group col-lg-1">
            @if (Storage::disk('public')->exists($provider->scientific_certificate_image))
                <img src="{{ Storage::disk('public')->url($provider->Scientific_certificate_image) }}" class="img-fluid" />
            @else
            @endif
        </div>
    @endif

    <div class="form-group @if (isset($provider)) col-lg-5 @else col-lg-6 @endif ">
        {!! Form::label('image', TranslationHelper::translate('Syndicate_image'), ['class' => 'form-label']) !!}
        <input type="file" id="syndicate_image" name="syndicate_image" class="form-control" />
    </div>
    @if (isset($provider))
        <div class="form-group col-lg-1">
            @if (Storage::disk('public')->exists($provider->syndicate_image))
                <img src="{{ Storage::disk('public')->url($provider->syndicate_image) }}" class="img-fluid" />
            @else
            @endif
        </div>
    @endif


    <div class="form-group @if (isset($provider)) col-lg-5 @else col-lg-6 @endif ">
        {!! Form::label('image', TranslationHelper::translate('Clinic_photos'), ['class' => 'form-label']) !!}
        <input type="file" id="clinic_photos" name="clinic_photos[]" class="form-control" multiple />
    </div>
    @if (isset($provider))
        <div class="form-group col-lg-1">
            @forelse(json_decode($provider->clinic_photos) as $clinic_photo)
                    @if (Storage::disk('public')->exists($clinic_photo))
                        <img src="{{ Storage::disk('public')->url($clinic_photo) }}" class="img-fluid" />
                     @endif
                    @empty
                    @endforelse
        </div>
    @endif

    <div class="form-group @if (isset($provider)) col-lg-5 @else col-lg-6 @endif ">
        {!! Form::label('image', TranslationHelper::translate('Logo'), ['class' => 'form-label']) !!}
        <input type="file" id="logo" name="logo" class="form-control" />
    </div>
    @if (isset($provider))
        <div class="form-group col-lg-1">
            @if (Storage::disk('public')->exists($provider->logo))
                <img src="{{ Storage::disk('public')->url($provider->logo) }}" class="img-fluid" />
            @else
            @endif
        </div>
    @endif

    <div class="form-group @if (isset($provider)) col-lg-5 @else col-lg-6 @endif ">
        {!! Form::label('image', TranslationHelper::translate('doctor_image'), ['class' => 'form-label']) !!}
        <input type="file" id="doctor_image" name="doctor_image" class="form-control" />
    </div>
    @if (isset($provider))
        <div class="form-group col-lg-1">
            @if (Storage::disk('public')->exists($provider->doctor_image))
                <img src="{{ Storage::disk('public')->url($provider->doctor_image) }}" class="img-fluid" />
            @else
            @endif
        </div>
    @endif
    <div class="form-group @if (isset($provider)) col-lg-5 @else col-lg-6 @endif ">
        {!! Form::label('image', TranslationHelper::translate('Professional License'), ['class' => 'form-label']) !!}
        <input type="file" id="license" name="license" class="form-control" />
    </div>
    @if (isset($provider))
        <div class="form-group col-lg-1">
            @if (Storage::disk('public')->exists($provider->license))
                <img src="{{ Storage::disk('public')->url($provider->license) }}" class="img-fluid" />
            @else
            @endif
        </div>
    @endif
    <div class="form-group @if (isset($provider)) col-lg-11 @else col-lg-12 @endif ">
        <label style="    width: 100%;font-size: 17px ">{{__('website.please_you_ID')}}  <a href="{{$contract_pdf ?? ''}}"  target="_blank"> {{__('website.Download_the_contract')}}</a> </label>
        <input type="file" id="contract" name="contract" class="form-control" />
    </div>
    @if (isset($provider))
        <div class="form-group col-lg-1">
            @if (Storage::disk('public')->exists($provider->contract))

                <a target="_blank" href="{{ Storage::disk('public')->url($provider->contract) }}">{{TranslationHelper::translate('Contract')}} </a>

            @else
            @endif
        </div>
    @endif



    <div class="col-lg-12 form-group">
        {!! Form::label('hear_about', TranslationHelper::translate('How you hear about dackatra'), ['class'=>'form-label']) !!}
        <select class="form-control" id="hear_about" name="hear_about" style="opacity: 0.6;">


            <option selected  > {{__('website.about_dackatra')}}</option>
            <option value="online"
                {{old('hear_about')=='online'?'selected':null}}
            > {{__('website.Online')}}</option>
            <option value="sales"
                {{old('hear_about')=='sales'?'selected':null}}
            > {{__('website.Seal_Rep')}}</option>
            <option value="other doctor"
                {{old('hear_about')=='other doctor'?'selected':null}}
            > {{__('website.other_doctor')}}</option>


        </select>
    </div>


    <div class="col-lg-12 form-group" id="seal_name" style=" display: none">
        {!! Form::label('seal_name', TranslationHelper::translate('add  seal name'), ['class'=>'form-label']) !!}
        {!! Form::text('seal_name', NULL, ['class' => 'form-control']) !!}
    </div>

    <div class="col-lg-12 form-group" id="doctor_id" style=" display: none">
        {!! Form::label('doctor_id', TranslationHelper::translate('Add doctor id'), ['class'=>'form-label']) !!}
        {!! Form::text('doctor_id', NULL, ['class' => 'form-control']) !!}
    </div>

    <div class="col-lg-12 form-group" id="link" style=" display: none">
        {!! Form::label('link', TranslationHelper::translate('Social Media Type & Group Name'), ['class'=>'form-label']) !!}
        {!! Form::text('link', NULL, ['class' => 'form-control']) !!}
    </div>








    <button type="submit" class="btn btn-primary" id="kt_submit">{{ TranslationHelper::translate('save') }}</button>


</div>





        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

        <script src="https://maps.google.com/maps/api/js?libraries=places&region=uk&language=en&sensor=true"></script>
        <script src="https://maps.google.com/maps/api/js?key=AIzaSyB-uADMlF6PqwccIr3q6Vpyl0wJgJNsxOM&libraries=places&region=uk&language=en&sensor=true"></script>
        <script>
            $(function () {
                var lat = 30.0444,
                    lng = 31.2357,
                    latlng = new google.maps.LatLng(lat, lng),
                    image = 'https://www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png';
                //zoomControl: true,
                //zoomControlOptions: google.maps.ZoomControlStyle.LARGE,
                var mapOptions = {
                        center: new google.maps.LatLng(lat, lng),
                        zoom: 13,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        panControl: true,
                        panControlOptions: {
                            position: google.maps.ControlPosition.TOP_RIGHT
                        },
                        zoomControl: true,
                        zoomControlOptions: {
                            style: google.maps.ZoomControlStyle.LARGE,
                            position: google.maps.ControlPosition.TOP_left
                        }
                    },
                    map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions),
                    marker = new google.maps.Marker({
                        position: latlng,
                        map: map,
                        icon: image
                    });
                var input = document.getElementById('searchTextField');
                var autocomplete = new google.maps.places.Autocomplete(input, {
                    types: ["geocode"]
                });
                autocomplete.bindTo('bounds', map);
                var infowindow = new google.maps.InfoWindow();
                google.maps.event.addListener(autocomplete, 'place_changed', function (event) {
                    infowindow.close();
                    var place = autocomplete.getPlace();
                    if (place.geometry.viewport) {
                        map.fitBounds(place.geometry.viewport);
                    } else {
                        map.setCenter(place.geometry.location);
                        map.setZoom(17);
                    }
                    moveMarker(place.name, place.geometry.location);
                    $('.MapLat').val(place.geometry.location.lat());
                    $('.MapLon').val(place.geometry.location.lng());
                });
                google.maps.event.addListener(map, 'click', function (event) {
                    $('.MapLat').val(event.latLng.lat());
                    $('.MapLon').val(event.latLng.lng());
                    infowindow.close();
                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode({
                        "latLng":event.latLng
                    }, function (results, status) {
                        console.log(results, status);
                        if (status == google.maps.GeocoderStatus.OK) {
                            console.log(results);
                            var lat = results[0].geometry.location.lat(),
                                lng = results[0].geometry.location.lng(),
                                placeName = results[0].address_components[0].long_name,
                                latlng = new google.maps.LatLng(lat, lng);
                            moveMarker(placeName, latlng);
                            $("#searchTextField").val(results[0].formatted_address);
                        }
                    });
                });

                function moveMarker(placeName, latlng) {
                    marker.setIcon(image);
                    marker.setPosition(latlng);
                    infowindow.setContent(placeName);
                    //infowindow.open(map, marker);
                }
            });
        </script>
<script>
    $(".is_approved").change(function() {
        if ($(this).val() == 2) {
            $("#otherAnswer").show();
        } else {
            $("#otherAnswer").hide();
        }
    });
</script>
<script>

    $("#hear_about").change(function(){
        if($(this).val() == "online") {
            $("#link").show('300');
            $("#seal_name").hide('300');
            $("#doctor_id").hide('300');

        }else if($(this).val() == "other doctor") {
            $("#doctor_id").show('300');
            $("#link").hide('300');
            $("#seal_name").hide('300');

        }else if($(this).val() == "sales") {

            $("#doctor_id").hide('300');
            $("#link").hide('300');
            $("#seal_name").show('300');
        }
    });
    $("#department").change(function(){
        if($(this).val() == "20") {
            $("#doctor_pets").show('300');
            $("#doctor_job").hide('300');

        } else {

            $("#doctor_pets").hide('300');
            $("#doctor_job").show('300');
        }
    });




</script>
