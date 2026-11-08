<div class="dropdown md-theme-control">
    <button type="button" class="md-theme-trigger" data-bs-toggle="dropdown" aria-haspopup="menu" aria-expanded="false" aria-label="{{ __('dashboard-ui.theme') }}" title="{{ __('dashboard-ui.theme') }}">
        <span class="md-theme-orbit" aria-hidden="true"><i class="fa-solid fa-sun md-theme-sun"></i><i class="fa-solid fa-moon md-theme-moon"></i></span>
    </button>
    <div class="dropdown-menu dropdown-menu-end md-theme-menu" role="menu" aria-label="{{ __('dashboard-ui.theme') }}">
        <span class="md-theme-menu-label">{{ __('dashboard-ui.appearance') }}</span>
        @foreach (['light' => 'sun', 'dark' => 'moon', 'system' => 'desktop'] as $preference => $icon)
            <button type="button" class="dropdown-item md-theme-option" role="menuitemradio" aria-checked="false" data-theme-choice="{{ $preference }}">
                <i class="fa-solid fa-{{ $icon }}" aria-hidden="true"></i><span>{{ __('dashboard-ui.theme_'.$preference) }}</span><i class="fa-solid fa-check md-theme-check" aria-hidden="true"></i>
            </button>
        @endforeach
    </div>
</div>
