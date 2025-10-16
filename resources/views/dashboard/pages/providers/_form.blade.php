<div class="row">
    <div class="col-lg-6 form-group">
        {!! Form::label('name', TranslationHelper::translate('English Name'), ['class'=>'form-label']) !!}
        {!! Form::text('name', NULL, ['class' => 'form-control','disabled']) !!}
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('name_ar', TranslationHelper::translate('Arabic Name'), ['class'=>'form-label']) !!}
        {!! Form::text('name_ar', NULL, ['class' => 'form-control','disabled']) !!}
    </div>

    <div class="col-lg-6 form-group">
        {!! Form::label('email', TranslationHelper::translate('email'), ['class'=>'form-label']) !!}
        {!! Form::email('email', NULL, ['class' => 'form-control','disabled']) !!}
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('phone', TranslationHelper::translate('phone'), ['class'=>'form-label']) !!}
        {!! Form::text('phone', NULL, ['class' => 'form-control','disabled','placeholder' => 'phone number EX. 1007982550']) !!}
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('city', TranslationHelper::translate('City'), ['class'=>'form-label']) !!}

        {!! Form::text('phone', $city->name ?? '', ['class' => 'form-control','disabled','placeholder' => 'City']) !!}
    </div>


    <div class="col-lg-12 form-group">
        {!! Form::label('Jobs', TranslationHelper::translate('Jobs'), ['class'=>'form-label']) !!}
        <br>

        @if($provider->doctor_professional_id!=null)
            @forelse( $jobs as $job)
                @if(in_array($job->id, json_decode($provider->doctor_professional_id)))

                    <button type="button" class="btn btn-info"  style="    color: white;"> {{ $job->name }}</button>


                @endif

                @endforeach
                @endif

    </div>

    <div class="col-lg-12 form-group">

        {!! Form::label('city', TranslationHelper::translate('Location'), ['class'=>'form-label']) !!}
        @php

            //                $Seeting = new \App\Helpers\Seeting();

        @endphp

        <input id="searchTextField" name="location" class="form-control" disabled type="text" value="{{$provider->location ??''}}" placeholder="{{TranslationHelper::translate('Location')}}">
        <input type="hidden" name="lat" class="MapLat" value="{{$provider->lat ??''}}"  placeholder="Latitude" style="width: 161px;" >
        <input type="hidden" name="lng" class="MapLon" value="{{$provider->lng ?? ''}}"  placeholder="Longitude" style="width: 161px;" >
        <div id="map_canvas" style="height: 350px;width: 100%;margin: 0.6em;"></div>
    </div>



    @if($provider->type=='doctor')
        <div class="form-group col-sm-6">
            {!! Form::label('city', TranslationHelper::translate('Scientific Certificate Image'), ['class'=>'form-label']) !!}
            <br>
            @if(!$provider->scientific_certificate_image)

                N/A
            @else

                <img
                    src="{{  Storage::disk('public')->url($provider->scientific_certificate_image) }}"
                    width="150px"  style="height:150px"
                    srcset="{{  Storage::disk('public')->url($provider->scientific_certificate_image) }}"
                    class="img-thumbnail image-preview"
                >

            @endif

        </div>
        <div class="form-group col-sm-6">
            {!! Form::label('city', TranslationHelper::translate('Syndicate Image'), ['class'=>'form-label']) !!}
            <br>
            @if(!$provider->syndicate_image)

                N/A
            @else

                <img
                    src="{{  Storage::disk('public')->url($provider->syndicate_image) }}"
                    width="150px"  style="height:150px"
                    srcset="{{  Storage::disk('public')->url($provider->syndicate_image) }}"
                    class="img-thumbnail image-preview"
                >

            @endif

        </div>

        <div class="form-group col-sm-12">

            {!! Form::label('city', TranslationHelper::translate('Clinic Photos'), ['class'=>'form-label']) !!}
            <br>




            @if($provider->clinic_photos==null || count(json_decode($provider->clinic_photos))==0)

                N/A
            @else

                @forelse(json_decode($provider->clinic_photos) as $clinic_photo)


                    <img
                        src="{{ Storage::disk('public')->url($clinic_photo)  }}"
                        width="150px"  style="height:150px"
                        srcset="{{ Storage::disk('public')->url($clinic_photo)  }}"
                        class="img-thumbnail image-preview"
                    >
                @empty
                    N/A
                @endforelse
            @endif
        </div>


        <div class="form-group col-sm-6">
            {!! Form::label('city', TranslationHelper::translate('Logo'), ['class'=>'form-label']) !!}
            </br>
            @if(!$provider->logo)

                N/A
            @else

                <img
                    src="{{ Storage::disk('public')->url($provider->logo)  }}"
                    width="150px"  style="height:150px"
                    srcset="{{ Storage::disk('public')->url($provider->logo)  }}"
                    class="img-thumbnail image-preview"
                >
            @endif

        </div>

    @else
        <div class="form-group col-sm-6">
            {!! Form::label('city', TranslationHelper::translate('Lab License'), ['class'=>'form-label']) !!}
            <br>
            @if(!$provider->lab_license)

                N/A
            @else

                <img
                    src="{{  Storage::disk('public')->url($provider->lab_license) }}"
                    width="150px"  style="height:150px"
                    srcset="{{  Storage::disk('public')->url($provider->lab_license) }}"
                    class="img-thumbnail image-preview"
                >

            @endif

        </div>
        <div class="form-group col-sm-6">
            {!! Form::label('city', TranslationHelper::translate('Lab Commercial Register'), ['class'=>'form-label']) !!}
            <br>
            @if(!$provider->syndicate_image)

                N/A
            @else

                <img
                    src="{{  Storage::disk('public')->url($provider->tax_card) }}"
                    width="150px"  style="height:150px"
                    srcset="{{  Storage::disk('public')->url($provider->tax_card) }}"
                    class="img-thumbnail image-preview"
                >

            @endif

        </div>

{{--        <div class="form-group col-sm-12">--}}

{{--            {!! Form::label('city', TranslationHelper::translate('Lab photos'), ['class'=>'form-label']) !!}--}}
{{--            <br>--}}

{{--            @if(count(json_decode($provider->clinic_photos))==0)--}}

{{--                N/A--}}
{{--            @else--}}

{{--                @forelse(json_decode($provider->clinic_photos) as $clinic_photo)--}}


{{--                    <img--}}
{{--                        src="{{ Storage::disk('public')->url($clinic_photo)  }}"--}}
{{--                        width="150px"  style="height:150px"--}}
{{--                        srcset="{{ Storage::disk('public')->url($clinic_photo)  }}"--}}
{{--                        class="img-thumbnail image-preview"--}}
{{--                    >--}}
{{--                @empty--}}
{{--                    N/A--}}
{{--                @endforelse--}}
{{--            @endif--}}
{{--        </div>--}}

    @endif








    @if($provider->type=='doctor')

    <div class="form-group col-sm-6">
        {!! Form::label('city', TranslationHelper::translate('Contract'), ['class'=>'form-label']) !!}

        <a target="_blank" href="{{ Storage::disk('public')->url($provider->contract) }}">{{TranslationHelper::translate('Contract')}} </a>

    </div>


    <div class="form-group col-sm-6">
        {!! Form::label('city', TranslationHelper::translate('Contact by'), ['class'=>'form-label']) !!}

        <input type="text" name="" disabled class="form-control" value="{{$provider->contact_by ?? 'N/A'}}" >

    </div>

    @if($provider->contact_by=="sales")

        <div class="form-group col-sm-6">
            {!! Form::label('city', TranslationHelper::translate('Sales Name'), ['class'=>'form-label']) !!}
            <input type="text" name="" disabled class="form-control" value="{{$provider->seal_name ?? 'N/A'}}" >


        </div>

    @elseif($provider->contact_by=="other doctor")
        {!! Form::label('city', TranslationHelper::translate('Doctor'), ['class'=>'form-label']) !!}


        <input type="text" name="" disabled class="form-control" value="{{$provider->add_id ?? 'N/A'}}" >

    @elseif($provider->contact_by=="online")
        {!! Form::label('city', TranslationHelper::translate('Link'), ['class'=>'form-label']) !!}

        <a href="{{$provider->link}}">{{TranslationHelper::translate('Link')}}</a>

    @endif

    <div class="col-lg-6 form-group">
        {!! Form::label('about_doctor', TranslationHelper::translate('About Doctor'), ['class'=>'form-label']) !!}
        {!! Form::textarea('about_doctor', NULL, ['class' => 'form-control','disabled']) !!}
    </div>

    <div class="col-lg-6 form-group">
        {!! Form::label('department', TranslationHelper::translate('department'), ['class'=>'form-label']) !!}
        <input type="text" name="" disabled class="form-control" value="{{$provider->department->name ?? 'N/A'}}" >
    </div>

    <div class="col-lg-6 form-group">
        {!! Form::label('department', TranslationHelper::translate('Sub Department'), ['class'=>'form-label']) !!}
        <input type="text" name="" disabled class="form-control" value="{{$provider->sub_department->name ?? 'N/A'}}" >
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('department', TranslationHelper::translate('Education City'), ['class'=>'form-label']) !!}
        <input type="text" name="" disabled class="form-control" value="{{$provider->education_city_data->name ?? 'N/A'}}" >
    </div>

    <div class="col-lg-6 form-group">
        {!! Form::label('department', TranslationHelper::translate('Education Degree'), ['class'=>'form-label']) !!}
        <input type="text" name="" disabled class="form-control" value="{{$provider->education_degree ?? 'N/A'}}" >
    </div>


    <div class="col-lg-6 form-group">
        {!! Form::label('department', TranslationHelper::translate('Education University'), ['class'=>'form-label']) !!}
        <input type="text" name="" disabled class="form-control" value="{{$provider->education_university ?? 'N/A'}}" >
    </div>


    <div class="col-lg-6 form-group">
        {!! Form::label('department', TranslationHelper::translate('Education Year'), ['class'=>'form-label']) !!}
        <input type="text" name="" disabled class="form-control" value="{{$provider->education_year ?? 'N/A'}}" >
    </div>

    <div class="col-lg-6 form-group">
        {!! Form::label('department', TranslationHelper::translate('Job Title'), ['class'=>'form-label']) !!}
        <input type="text" name="" disabled class="form-control" value="{{$provider->job_title ?? 'N/A'}}" >
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('department', TranslationHelper::translate('Entity'), ['class'=>'form-label']) !!}
        <input type="text" name="" disabled class="form-control" value="{{$provider->entity ?? 'N/A'}}" >
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('department', TranslationHelper::translate('Currently Work in This Role'), ['class'=>'form-label']) !!}
        <input type="text" name="" disabled class="form-control" value="{{$provider->currently_work_this_role == 1 ? TranslationHelper::translate('Yes') : TranslationHelper::translate('No')}}" >
    </div>

    <div class="col-lg-6 form-group">
        {!! Form::label('department', TranslationHelper::translate('Start Date'), ['class'=>'form-label']) !!}
        <input type="text" name="" disabled class="form-control" value="{{$provider->start_date ?? 'N/A'}}" >
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('department', TranslationHelper::translate('End Date'), ['class'=>'form-label']) !!}
        <input type="text" name="" disabled class="form-control" value="{{$provider->end_date ?? 'N/A'}}" >
    </div>



@endif


    @if (isset($provider))
        <div class="col-lg-5 form-group">
            @else
                <div class="col-lg-6 form-group">
                    @endif
                </div>


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


        <div class="col-lg-6 form-group">
            <label class="form-check-label"> {{ TranslationHelper::translate('Is Approved') }}</label>
            <div class="form-check ftch">
                <input class="form-check-input is_approved" id="approved" type="radio" name="is_approved" value="1" @if(!$provider ||  $provider->is_approved == 1) checked @endif  >
                <label class="form-check-label" for="approved">
                    {{ TranslationHelper::translate('Approved') }}</label>
            </div>
            <div class="form-check ftch">
                <input class="form-check-input is_approved" id="suspended" type="radio" name="is_approved"
                       value="2" @if(!$provider ||  $provider->is_approved == 2) checked @endif >
                <label class="form-check-label" for="suspended">
                    {{ TranslationHelper::translate('Suspended') }}</label>
            </div>
        </div>
        <div id="otherAnswer" class="col-lg-6 form-group" style="@if($provider->is_approved != '2') display: none; @endif">
            {!! Form::label('foundation name', TranslationHelper::translate('Suspend Result'), ['class' => 'form-label']) !!}

            {!! Form::textarea('suspend_result', $profile->description ?? old('suspend_result'), ['class'=>'form-control']) !!}

        </div>






        <button type="submit" class="btn btn-primary" id="kt_submit">{{ TranslationHelper::translate('save') }}</button>


</div>





<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<script src="https://maps.google.com/maps/api/js?libraries=places&region=uk&language=en&sensor=true"></script>
<script src="https://maps.google.com/maps/api/js?key=AIzaSyB-uADMlF6PqwccIr3q6Vpyl0wJgJNsxOM&libraries=places&region=uk&language=en&sensor=true"></script>
<script>
    $(function () {
        var lat = {{$provider->lat}},
            lng = {{$provider->lng}},
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
