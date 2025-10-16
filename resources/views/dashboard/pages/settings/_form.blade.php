
<div class="row">
    <div class="col-12 d-flex">
        <div class="card flex-fill">
            <div class='card-body'>
                <div class="row">



                    <div class="col-6 form-group">
                        <label class="form-check-label" for="bank_name">  {{ TranslationHelper::translate('Bank name') }}</label>
                        <input class="form-control" type="text" step="0.01" id="bank_name" name="bank_name"
                            @if(isset($settings)) value="{{ $settings->bank_name }}" @else value="" @endif required>
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-check-label" for="branch_name">  {{ TranslationHelper::translate('Branch name') }}</label>
                        <input class="form-control" type="text" step="0.01" id="branch_name" name="branch_name"
                            @if(isset($settings)) value="{{ $settings->branch_name }}" @else value="" @endif required>
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-check-label" for="iban">  {{ TranslationHelper::translate('IBAN') }}</label>
                        <input class="form-control" type="text" step="0.01" id="iban" name="iban"
                            @if(isset($settings)) value="{{ $settings->iban }}" @else value="" @endif required>
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-check-label" for="swift_code">  {{ TranslationHelper::translate('Swift code') }}</label>
                        <input class="form-control" type="text" step="0.01" id="swift_code" name="swift_code"
                            @if(isset($settings)) value="{{ $settings->swift_code }}" @else value="" @endif required>
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-check-label" for="bank_account_number">  {{ TranslationHelper::translate('Bank account number') }}</label>
                        <input class="form-control" type="text" step="0.01" id="bank_account_number" name="bank_account_number"
                            @if(isset($settings)) value="{{ $settings->bank_account_number }}" @else value="" @endif required>
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-check-label" for="bank_account_name">  {{ TranslationHelper::translate('Bank account name') }}</label>
                        <input class="form-control" type="text" step="0.01" id="bank_account_name" name="bank_account_name"
                            @if(isset($settings)) value="{{ $settings->bank_account_name }}" @else value="" @endif required>
                    </div>

                    <div class="form-group @if (isset($settings)) col-lg-5 @else col-lg-6 @endif ">
                        {!! Form::label('image', TranslationHelper::translate('PNG Image'), ['class' => 'form-label']) !!}
                        <input type="file" id="logo" name="logo" class="form-control" />
                    </div>
                    @if (isset($settings))
                        <div class="form-group col-lg-1">
                            @if (Storage::disk('public')->exists($settings->logo))
                                <img src="{{ Storage::disk('public')->url($settings->logo) }}" class="img-fluid" />
                            @else
                            @endif
                        </div>
                    @endif






                    <div class="col-6 form-group">
                        <label class="form-check-label" for="phone">  {{ TranslationHelper::translate('Phone') }}</label>
                        <input class="form-control" type="text" step="0.01" id="phone" name="phone"
                            @if(isset($settings)) value="{{ $settings->phone }}" @else value="" @endif required>
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-check-label" for="whatsapp">  {{ TranslationHelper::translate('Whatsapp') }}</label>
                        <input class="form-control" type="text" step="0.01" id="whatsapp" name="whatsapp"
                            @if(isset($settings)) value="{{ $settings->whatsapp }}" @else value="" @endif required>
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-check-label" for="facebook">  {{ TranslationHelper::translate('Facebook') }}</label>
                        <input class="form-control" type="text" step="0.01" id="facebook" name="facebook"
                            @if(isset($settings)) value="{{ $settings->facebook }}" @else value="" @endif required>
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-check-label" for="instagram">  {{ TranslationHelper::translate('Instagram') }}</label>
                        <input class="form-control" type="text" step="0.01" id="instagram" name="instagram"
                            @if(isset($settings)) value="{{ $settings->instagram }}" @else value="" @endif required>
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-check-label" for="tiktok">  {{ TranslationHelper::translate('Tiktok') }}</label>
                        <input class="form-control" type="text" step="0.01" id="tiktok" name="tiktok"
                            @if(isset($settings)) value="{{ $settings->tiktok }}" @else value="" @endif required>
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
