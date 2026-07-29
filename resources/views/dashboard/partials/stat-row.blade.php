{{--
    Row of stat-cards shown as a "brief" above a page's table.
    Wraps the row with the correct spacing (mb-4) from the table below it,
    so every page that includes this gets consistent spacing for free.

    Usage:
        @include('dashboard.partials.stat-row', [
            'cards' => [
                ['icon' => 'fa-solid fa-clock', 'value' => 6, 'label' => 'Pending', 'color' => 'warning'],
                ...
            ],
        ])
--}}
@php $cards = $cards ?? []; @endphp
<div class="row mb-4">
    @foreach ($cards as $card)
        <div class="col-md-3 col-sm-6">
            @include('dashboard.partials.stat-card', $card)
        </div>
    @endforeach
</div>
