<div class="md-navigation-brand">
    <a href="{{ route('admin.dashboard.index') }}">
        <img src="{{ asset('dashboard/img/logo.png') }}" alt="{{ TranslationHelper::translate('site_name') }}">
    </a>
    <button type="button" class="md-navigation-close" aria-label="{{ __('dashboard-ui.close_navigation') }}"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
</div>
<div class="md-workspace-label">
    <span class="md-workspace-symbol" aria-hidden="true"><i class="fa-solid fa-layer-group"></i></span>
    <span>{{ Auth::guard('admin')->user()->type === 'partner' ? __('dashboard-ui.partner_workspace') : __('dashboard-ui.admin_workspace') }}<small>{{ __('dashboard-ui.management_console') }}</small></span>
</div>
