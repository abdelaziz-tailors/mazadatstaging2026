{{--
    Shared page header: icon badge + title + breadcrumb trail.

    Usage:
        @include('dashboard.partials.page-header', [
            'title' => TranslationHelper::translate('Orders'),
            'icon' => 'fa-solid fa-cart-shopping',
        ])

    Optional:
        'breadcrumbs' => [
            ['label' => TranslationHelper::translate('Orders')], // no 'route' => current/active item
        ]
--}}
@php
    $icon = $icon ?? 'fa-solid fa-grip';
    $breadcrumbs = $breadcrumbs ?? [['label' => $title]];
@endphp
<div class="page-header md-page-header">
    <div class="row align-items-center">
        <div class="col">
            <div class="d-flex align-items-center gap-3">
                <span class="md-page-icon"><i class="{{ $icon }}"></i></span>
                <div>
                    <h3 class="page-title mb-1">{{ $title }}</h3>
                    <ul class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                        @foreach ($breadcrumbs as $crumb)
                            @if (!empty($crumb['route']))
                                <li class="breadcrumb-item"><a href="{{ $crumb['route'] }}">{{ $crumb['label'] }}</a></li>
                            @else
                                <li class="breadcrumb-item active">{{ $crumb['label'] }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @isset($actions)
            <div class="col-auto">
                {!! $actions !!}
            </div>
        @endisset
    </div>
</div>
