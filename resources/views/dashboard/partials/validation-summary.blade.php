@if ($errors->any())
    <div class="alert alert-danger md-validation-summary" role="alert" tabindex="-1">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <div>
            <strong>{{ __('dashboard-ui.validation_title') }}</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->messages() as $field => $messages)
                    @foreach ($messages as $message)
                        <li><a href="#main-content" data-error-field="{{ $field }}">{{ $message }}</a></li>
                    @endforeach
                @endforeach
            </ul>
        </div>
    </div>
@endif
