{{-- Last in the cascade: the same design system covers every dashboard view. --}}
<link rel="stylesheet" href="{{ asset('dashboard/css/workspace.css') }}?v={{ filemtime(public_path('dashboard/css/workspace.css')) }}">
@php
    $workspaceLabels = [
        'tableRegion' => __('dashboard-ui.table_region'),
        'tableError' => __('dashboard-ui.table_error'),
        'loading' => __('dashboard-ui.loading'),
        'passwordShow' => __('dashboard-ui.password_show'),
        'passwordHide' => __('dashboard-ui.password_hide'),
        'closeDialog' => __('dashboard-ui.close_dialog'),
        'moreActions' => __('dashboard-ui.more_actions'),
        'previousPage' => __('dashboard-ui.previous_page'),
        'nextPage' => __('dashboard-ui.next_page'),
        'theme' => __('dashboard-ui.theme'),
        'theme_light' => __('dashboard-ui.theme_light'),
        'theme_dark' => __('dashboard-ui.theme_dark'),
        'theme_system' => __('dashboard-ui.theme_system'),
    ];
@endphp
<script type="application/json" id="workspace-ui-labels">@json($workspaceLabels)</script>
