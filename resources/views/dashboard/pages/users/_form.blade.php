<div class="row">
    <div class="col-lg-6 form-group">
        {!! Form::label('name', TranslationHelper::translate(' Name'), ['class'=>'form-label']) !!}
        {!! Form::text('name', NULL, ['class' => 'form-control','']) !!}
    </div>

    <div class="col-lg-6 form-group">
        {!! Form::label('email', TranslationHelper::translate('email'), ['class'=>'form-label']) !!}
        {!! Form::email('email', NULL, ['class' => 'form-control','']) !!}
    </div>
    <div class="col-lg-6 form-group">
        {!! Form::label('phone', TranslationHelper::translate('phone'), ['class'=>'form-label']) !!}
        {!! Form::text('phone', NULL, ['class' => 'form-control','','placeholder' => 'phone number EX. 1007982550']) !!}
    </div>







    <button type="submit" class="btn btn-primary" id="kt_submit">{{ TranslationHelper::translate('save') }}</button>


</div>





<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

{{--<script>--}}
{{--    $('#county').on('change', function() {--}}
{{--        const county_id = $(this).val();--}}

{{--        if (county_id) {--}}
{{--            $.ajax({--}}
{{--                url: `/get-city/${county_id}/{{app()->getLocale()}}`,--}}
{{--                type: 'GET',--}}
{{--                success: function(response) {--}}
{{--                    // Clear existing options--}}
{{--                    $('#city').html('<option value="">{{TranslationHelper::translate('Select city')}}</option>');--}}

{{--                    // Populate the dropdown with received states--}}
{{--                    response.forEach(function(state) {--}}
{{--                        $('#city').append(`<option value="${state.id}">${state.name}</option>`);--}}
{{--                    });--}}
{{--                },--}}
{{--                error: function() {--}}
{{--                    // Show a message if no states are found--}}
{{--                    $('#city').html('<option value="">{{TranslationHelper::translate('No city Available')}}</option>');--}}
{{--                }--}}
{{--            });--}}
{{--        } else {--}}
{{--            // Reset the state select if no city is selected--}}
{{--            $('#city').html('<option value="">{{TranslationHelper::translate('Select city')}}</option>');--}}
{{--        }--}}
{{--    });--}}
{{--</script>--}}
