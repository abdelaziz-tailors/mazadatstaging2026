@extends('dashboard.layouts.app')

@section('title')
    {{ TranslationHelper::translate('home') }}
@endsection

@section('content')

    @include('dashboard.partials.page-header', [
        'title' => TranslationHelper::translate('welcome') . ' ' . Auth::guard('admin')->user()->name . '!',
        'icon' => 'fa-solid fa-gauge-high',
        'breadcrumbs' => [['label' => TranslationHelper::translate('dashboard')]],
    ])

    @php
        $mdDaysOptions = [7, 30, 90];
    @endphp

    <div class="stat-grid">
        @foreach ($reports as $report)
            @include('dashboard.partials.stat-card', [
                'icon' => $report['icon'],
                'value' => ($report['is_currency'] ?? false) ? number_format($report['value'], 0) . ' ' . TranslationHelper::translate('sar') : number_format($report['value']),
                'label' => $report['name'],
                'color' => $report['color'],
                'trend' => isset($report['trend']) ? ['direction' => $report['trend']['direction'], 'text' => $report['trend']['pct'] . '%'] : null,
            ])
        @endforeach
    </div>

    {{-- Right-to-left reading order in the RTL layout: first DOM child renders
         rightmost. Per the design reference, that order is sales trend →
         user registrations → status donut. --}}
    <div class="row g-3 mt-1">
        <div class="col-xl-{{ $registrationsChart ? 4 : 8 }} col-12">
            <div class="card chart-card h-100">
                <div class="card-header md-chart-card-header">
                    <h4 class="card-title mb-0">{{ TranslationHelper::translate('sales_trend') }}</h4>
                    <select class="form-select form-select-sm w-auto" onchange="location.href=this.value">
                        @foreach ($mdDaysOptions as $option)
                            <option value="{{ request()->fullUrlWithQuery(['days' => $option]) }}" {{ $days == $option ? 'selected' : '' }}>
                                {{ TranslationHelper::translate('last') }} {{ $option }} {{ TranslationHelper::translate($option == 1 ? 'day' : 'days') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body">
                    @if ($salesTrend)
                        <div>
                            <span class="md-chart-stat-value">{{ number_format($salesTrend['value'], 0) }}</span>
                            <span class="md-chart-stat-label">{{ TranslationHelper::translate('sar') }} · {{ TranslationHelper::translate('gross_sales') }}</span>
                        </div>
                        <div class="md-chart-stat-trend {{ $salesTrend['direction'] }}">
                            <i class="fa-solid fa-arrow-{{ $salesTrend['direction'] === 'up' ? 'up' : 'down' }}"></i>
                            {{ $salesTrend['pct'] }}%
                            <span class="md-chart-stat-trend-caption">{{ TranslationHelper::translate('vs_previous_period') }}</span>
                        </div>
                    @endif
                    <div id="salesChart"></div>
                </div>
            </div>
        </div>

        @if ($registrationsChart)
            <div class="col-xl-4 col-12">
                <div class="card chart-card h-100">
                    <div class="card-header md-chart-card-header">
                        <h4 class="card-title mb-0">{{ TranslationHelper::translate('user_registrations') }}</h4>
                        <select class="form-select form-select-sm w-auto" onchange="location.href=this.value">
                            @foreach ($mdDaysOptions as $option)
                                <option value="{{ request()->fullUrlWithQuery(['days' => $option]) }}" {{ $days == $option ? 'selected' : '' }}>
                                    {{ TranslationHelper::translate('last') }} {{ $option }} {{ TranslationHelper::translate($option == 1 ? 'day' : 'days') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="card-body">
                        @if ($registrationsTrend)
                            <div>
                                <span class="md-chart-stat-value">{{ number_format($registrationsTrend['value'], 0) }}</span>
                                <span class="md-chart-stat-label">{{ TranslationHelper::translate('new_users') }}</span>
                            </div>
                            <div class="md-chart-stat-trend {{ $registrationsTrend['direction'] }}">
                                <i class="fa-solid fa-arrow-{{ $registrationsTrend['direction'] === 'up' ? 'up' : 'down' }}"></i>
                                {{ $registrationsTrend['pct'] }}%
                                <span class="md-chart-stat-trend-caption">{{ TranslationHelper::translate('vs_previous_period') }}</span>
                            </div>
                        @endif
                        <div id="registrationsChart"></div>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-xl-4 col-12">
            <div class="card chart-card h-100">
                <div class="card-header md-chart-card-header">
                    <h4 class="card-title mb-0">{{ TranslationHelper::translate('auctions_by_status') }}</h4>
                    <select class="form-select form-select-sm w-auto" onchange="location.href=this.value">
                        @foreach ($mdDaysOptions as $option)
                            <option value="{{ request()->fullUrlWithQuery(['days' => $option]) }}" {{ $days == $option ? 'selected' : '' }}>
                                {{ TranslationHelper::translate('last') }} {{ $option }} {{ TranslationHelper::translate($option == 1 ? 'day' : 'days') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body md-donut-card-body">
                    @if (array_sum($statusChart) > 0)
                        <div class="md-status-donut-row">
                            <div class="md-status-donut-wrap">
                                <div id="statusDonutChart"></div>
                                <div class="md-status-donut-total">
                                    <div class="md-status-donut-total-value">{{ array_sum($statusChart) }}</div>
                                    <div class="md-status-donut-total-label">{{ TranslationHelper::translate('total') }}</div>
                                </div>
                            </div>
                            <ul class="md-status-legend-list">
                                <li>
                                    <span class="legend-dot" style="background: var(--md-success)"></span>
                                    <span class="md-status-legend-label">{{ TranslationHelper::translate('active') }}</span>
                                    <span class="md-status-legend-count">{{ $statusChart['active'] }}</span>
                                </li>
                                <li>
                                    <span class="legend-dot" style="background: var(--md-warning)"></span>
                                    <span class="md-status-legend-label">{{ TranslationHelper::translate('scheduled') }}</span>
                                    <span class="md-status-legend-count">{{ $statusChart['scheduled'] }}</span>
                                </li>
                                <li>
                                    <span class="legend-dot" style="background: var(--md-danger)"></span>
                                    <span class="md-status-legend-label">{{ TranslationHelper::translate('ended') }}</span>
                                    <span class="md-status-legend-count">{{ $statusChart['ended'] }}</span>
                                </li>
                            </ul>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0 py-5">{{ TranslationHelper::translate('no_results_found') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @php
        $mdLatestColWidth = $latestUsers->isNotEmpty() ? 4 : 6;
    @endphp
    {{-- Right-to-left reading order: quick actions (rightmost, next to the
         sidebar) → latest auctions → latest users. --}}
    <div class="row g-3 mt-1">
        <div class="col-lg-{{ $mdLatestColWidth }} col-12">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ TranslationHelper::translate('quick_actions') }}</h4>
                </div>
                <div class="card-body">
                    <div class="md-quick-action-grid">
                        <a href="{{ route('admin.videos.create') }}" class="md-quick-action">
                            <span class="stat-icon stat-icon-success"><i class="fa-solid fa-gavel"></i></span>
                            <span>{{ TranslationHelper::translate('create_new_auction') }}</span>
                        </a>
                        <a href="{{ route('admin.categories.create') }}" class="md-quick-action">
                            <span class="stat-icon stat-icon-success"><i class="fa-solid fa-list"></i></span>
                            <span>{{ TranslationHelper::translate('add_new_category') }}</span>
                        </a>
                        <a href="{{ route('admin.seller-submissions.index') }}" class="md-quick-action">
                            <span class="stat-icon stat-icon-success"><i class="fa-solid fa-clipboard-list"></i></span>
                            <span>{{ TranslationHelper::translate('review_seller_submissions') }}</span>
                            @if ($pendingReviewCount > 0)
                                <span class="badge rounded-pill bg-danger ms-1">{{ $pendingReviewCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.user-subscriptions.index') }}" class="md-quick-action">
                            <span class="stat-icon stat-icon-success"><i class="fa-solid fa-crown"></i></span>
                            <span>{{ TranslationHelper::translate('manage_subscriptions') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-{{ $mdLatestColWidth }} col-12">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">{{ TranslationHelper::translate('latest_auctions') }}</h4>
                    <a href="{{ route('admin.auctions.index') }}" class="small">{{ TranslationHelper::translate('view_all') }}</a>
                </div>
                <div class="card-body p-0">
                    @if ($latestAuctions->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>{{ TranslationHelper::translate('Auctions Title') }}</th>
                                        <th>{{ TranslationHelper::translate('User') }}</th>
                                        <th>{{ TranslationHelper::translate('date_end') }}</th>
                                        <th>{{ TranslationHelper::translate('Status') }}</th>
                                        <th>{{ TranslationHelper::translate('view') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($latestAuctions as $auction)
                                        <tr>
                                            <td>
                                                @include('dashboard.partials.avatar', ['path' => $auction->image, 'name' => $auction->title_ar, 'size' => 40, 'placeholderIcon' => 'fa-solid fa-image'])
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ $auction->title_ar ?? $auction->title ?? '-' }}</span>
                                            </td>
                                            <td>{{ $auction->partner->name ?? '-' }}</td>
                                            <td>
                                                @if ($auction->date_end_at)
                                                    <div>{{ \Carbon\Carbon::parse($auction->date_end_at)->format('Y/m/d') }}</div>
                                                    @if ($auction->time_end_at)
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($auction->time_end_at)->translatedFormat('h:i A') }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>@include('dashboard.pages.videos.status', ['item' => $auction])</td>
                                            <td>
                                                <a href="{{ route('admin.auctions.show', $auction->id) }}" class="md-icon-btn" title="{{ TranslationHelper::translate('view') }}">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0 p-3">{{ TranslationHelper::translate('nothing_found') }}</p>
                    @endif
                </div>
            </div>
        </div>

        @if ($latestUsers->isNotEmpty())
            <div class="col-lg-{{ $mdLatestColWidth }} col-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0">{{ TranslationHelper::translate('latest_users') }}</h4>
                        <a href="{{ route('admin.users.index') }}" class="small">{{ TranslationHelper::translate('view_all') }}</a>
                    </div>
                    <div class="card-body">
                        @foreach ($latestUsers as $latestUser)
                            <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: var(--md-border) !important;">
                                <div class="md-row-media">
                                    @include('dashboard.partials.avatar', ['path' => $latestUser->image, 'name' => $latestUser->name])
                                    <div>
                                        <div class="fw-semibold">{{ $latestUser->name ?? '-' }}</div>
                                        <div class="text-muted small">{{ $latestUser->phone ?? $latestUser->email ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge rounded-pill {{ $latestUser->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $latestUser->is_active ? TranslationHelper::translate('Active') : TranslationHelper::translate('Inactive') }}
                                    </span>
                                    <div class="text-muted small mt-1">{{ $latestUser->created_at?->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection

@section('scripts_lib')
    <script src="{{ asset('dashboard/plugins/apex/apexcharts.min.js') }}"></script>
    <script>
        const mdChartTheme = {
            fontFamily: 'inherit',
            foreColor: '#9aa2b1',
        };
        const mdIsSmallScreen = window.innerWidth < 576;
        // ApexCharts' "tickAmount" is meant to thin out labels on a category
        // x-axis, but with many daily labels it wasn't reliably cutting them
        // down — every date rendered, cramming into the card width until
        // they overlapped and became unreadable. A label formatter that only
        // returns text for every Nth category (blank string otherwise) is
        // deterministic regardless of that, and guarantees real spacing.
        const mdTrendXaxis = (categories) => {
            const desiredTicks = mdIsSmallScreen ? 4 : 8;
            const step = Math.max(1, Math.ceil(categories.length / desiredTicks));
            return {
                categories,
                tickAmount: desiredTicks,
                labels: {
                    style: { colors: '#6b7385', fontSize: '11px' },
                    formatter: (value) => {
                        const idx = categories.indexOf(value);
                        return (idx % step === 0) ? value : '';
                    },
                },
            };
        };

        @if (array_sum($statusChart) > 0)
        // ApexCharts can't compute percentages for an all-zero donut series
        // (division by zero), which rendered as a broken sliver instead of a
        // ring — the card only mounts the chart div at all when there's real
        // data for the selected date range (see the condition wrapping this block).
        new ApexCharts(document.querySelector("#statusDonutChart"), {
            chart: { type: 'donut', height: 160, width: 160, ...mdChartTheme, animations: { enabled: false }, toolbar: { show: false } },
            series: [{{ $statusChart['active'] }}, {{ $statusChart['scheduled'] }}, {{ $statusChart['ended'] }}],
            labels: ["{{ TranslationHelper::translate('active') }}", "{{ TranslationHelper::translate('scheduled') }}", "{{ TranslationHelper::translate('ended') }}"],
            colors: ['#34c38f', '#f1b44c', '#f15b5b'],
            legend: { show: false },
            // The counts + total already live in the side legend and the
            // center overlay (see the .md-status-donut-total div layered on
            // top of this chart in the blade template) — in-ring labels
            // would just duplicate that.
            dataLabels: { enabled: false },
            tooltip: { y: { formatter: (val) => val } },
            // A visible stroke between slices keeps them visually separated at
            // any container size — without it, small/squeezed mobile widths
            // made adjacent slice edges blend into each other.
            stroke: { show: true, width: 2, colors: ['#ffffff'] },
            plotOptions: { pie: { donut: { size: '70%' } } },
        }).render();
        @endif

        @if ($registrationsChart)
        (function () {
            // A user count is always a whole number. ApexCharts'
            // "decimalsInFloat" only formats the digits it decides to draw —
            // it doesn't stop the underlying "nice scale" generator from
            // choosing fractional tick positions in the first place (e.g.
            // 0.7, 1.3 when the max value in range is small), so it never
            // actually fixed this. Explicitly picking a min/max/tickAmount
            // whose max is evenly divisible by tickAmount guarantees every
            // generated tick lands exactly on a whole number instead.
            var data = @json($registrationsChart['values']);
            var rawMax = Math.max(1, ...data);
            var tickAmount = Math.min(rawMax, 5);
            var niceMax = Math.ceil(rawMax / tickAmount) * tickAmount;

            new ApexCharts(document.querySelector("#registrationsChart"), {
                chart: { type: 'area', height: 200, toolbar: { show: false }, ...mdChartTheme },
                series: [{ name: "{{ TranslationHelper::translate('user_registrations') }}", data: data }],
                xaxis: mdTrendXaxis(@json($registrationsChart['labels'])),
                yaxis: { labels: { style: { colors: '#6b7385' } }, min: 0, max: niceMax, tickAmount: tickAmount },
                colors: ['#1f4a38'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0, stops: [0, 90, 100] } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                grid: { borderColor: '#2a3143' },
            }).render();
        })();
        @endif

        new ApexCharts(document.querySelector("#salesChart"), {
            chart: { type: 'area', height: 200, toolbar: { show: false }, ...mdChartTheme },
            series: [{ name: "{{ TranslationHelper::translate('Sales') }}", data: @json($salesChart['values']) }],
            xaxis: mdTrendXaxis(@json($salesChart['labels'])),
            yaxis: { labels: { style: { colors: '#6b7385' } } },
            colors: ['#1f4a38'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0, stops: [0, 90, 100] } },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            grid: { borderColor: '#2a3143' },
        }).render();
    </script>
@endsection
