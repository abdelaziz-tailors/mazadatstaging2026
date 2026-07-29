{{--
    Unified stat card: icon circle + value + label (+ optional trend).

    Usage:
        @include('dashboard.partials.stat-card', [
            'icon' => 'fa-solid fa-users',
            'value' => $usersCount,
            'label' => TranslationHelper::translate('Users'),
            'color' => 'success', // primary|success|danger|warning|info|purple|dark
        ])

    Optional 'trend' => ['direction' => 'up'|'down', 'text' => '+12%'] to show a small trend line.
--}}
@php
    $color = $color ?? 'primary';
@endphp
<div class="card h-100">
    <div class="stat-card">
        <span class="stat-icon stat-icon-{{ $color }}">
            <i class="{{ $icon }}"></i>
        </span>
        <div class="stat-value">{{ $value }}</div>
        <div class="stat-label">{{ $label }}</div>
        @isset($trend)
            <div class="stat-trend {{ $trend['direction'] }}">
                <i class="fa-solid fa-arrow-{{ $trend['direction'] === 'up' ? 'up' : 'down' }}"></i>
                {{ $trend['text'] }}
                <span class="stat-trend-caption">{{ TranslationHelper::translate('vs_last_month') }}</span>
            </div>
        @endisset
    </div>
</div>
