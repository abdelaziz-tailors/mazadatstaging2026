@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Auctions') }} @endsection

@push('css')
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
<!--end::Page Vendor Stylesheets-->

<style>
/* Auction filters only */
.md-wide-search .auction-filter-panel {
    background: #f9fbf9;
    border: 1px solid #e6ede8;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 22px;
}

.auction-filter-heading {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 20px;
    color: #3d5f45;
    font-size: 16px;
    font-weight: 700;
}

.auction-filter-main {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.auction-filter-field {
    min-width: 0;
}

.auction-filter-field .form-label {
    display: block;
    margin-bottom: 8px;
    color: #66776d;
    font-size: 13px;
    font-weight: 600;
}

.auction-filter-field .form-control,
.auction-filter-field .form-select {
    width: 100%;
    min-height: 42px;
    border: 1px solid #dde6df;
    border-radius: 9px;
    background-color: #fff;
}

.auction-filter-field .form-control:focus,
.auction-filter-field .form-select:focus {
    border-color: #3d5f45;
    box-shadow: 0 0 0 3px rgba(61, 95, 69, .10);
}

.auction-filter-bottom {
    display: flex;
    flex-wrap: wrap;
    align-items: end;
    gap: 18px;
    padding-top: 20px;
    margin-top: 20px;
    border-top: 1px solid #e6ede8;
}

.auction-search-slot {
    flex: 1 1 300px;
    min-width: 0;
}

.auction-length-slot {
    flex: 0 1 auto;
}

#auctionSearchSlot .dataTables_filter,
#auctionLengthSlot .dataTables_length {
    float: none !important;
    width: 100%;
    margin: 0;
    text-align: start;
}

#auctionSearchSlot .dataTables_filter label {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    width: 100%;
    margin: 0;
    color: #66776d;
    font-size: 13px;
    font-weight: 600;
}

#auctionSearchSlot .dataTables_filter input {
    flex: 1 1 180px;
    min-width: 0;
    width: 100%;
    min-height: 42px;
    margin: 0 !important;
    padding: 8px 12px;
    border: 1px solid #dde6df;
    border-radius: 9px;
    background: #fff;
}

#auctionLengthSlot .dataTables_length label {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin: 0;
    color: #66776d;
    font-size: 13px;
}

#auctionLengthSlot .dataTables_length select {
    width: auto;
    min-width: 65px;
    height: 42px;
    margin: 0;
    border: 1px solid #dde6df;
    border-radius: 9px;
    background-color: #fff;
}

.auction-filter-reset {
    min-height: 42px;
    padding: 8px 16px;
    background: #eaf2ec;
    border: 1px solid #cbded1;
    border-radius: 9px;
    color: #3d5f45;
    font-weight: 600;
    white-space: nowrap;
}

.auction-filter-reset:hover {
    background: #3d5f45;
    border-color: #3d5f45;
    color: #fff;
}

.auction-dt-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 18px;
}

@media (max-width: 991px) {
    .auction-filter-main {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 575px) {
    .md-wide-search .auction-filter-panel {
        padding: 15px;
    }

    .auction-filter-main {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .auction-filter-bottom {
        gap: 15px;
    }

    .auction-search-slot,
    .auction-length-slot,
    .auction-filter-reset {
        width: 100%;
        flex-basis: 100%;
    }

    .auction-filter-reset {
        justify-content: center;
    }
}
</style>


<style>
/* Auction table: keep scrolling inside the table */

.auction-table-scroll {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #b8c9bd #f4f7f5;
}

.auction-table-scroll::-webkit-scrollbar {
    height: 7px;
}

.auction-table-scroll::-webkit-scrollbar-track {
    background: #f4f7f5;
    border-radius: 10px;
}

.auction-table-scroll::-webkit-scrollbar-thumb {
    background: #b8c9bd;
    border-radius: 10px;
}

.auction-table-scroll #data-table th,
.auction-table-scroll #data-table td {
    vertical-align: middle;
    overflow-wrap: anywhere;
}

/* Desktop: distribute all 10 columns across the card */

@media (min-width: 1200px) {

    .auction-table-scroll #data-table {
        width: 100% !important;
        min-width: 0 !important;
        table-layout: fixed;
    }

    .auction-table-scroll #data-table th,
    .auction-table-scroll #data-table td {
        white-space: normal !important;
    }

    .auction-table-scroll #data-table th:nth-child(1) {
        width: 4%;
    }

    .auction-table-scroll #data-table th:nth-child(2) {
        width: 16%;
    }

    .auction-table-scroll #data-table th:nth-child(3) {
        width: 13%;
    }

    .auction-table-scroll #data-table th:nth-child(4),
    .auction-table-scroll #data-table th:nth-child(5) {
        width: 13%;
    }

    .auction-table-scroll #data-table th:nth-child(6) {
        width: 9%;
    }

    .auction-table-scroll #data-table th:nth-child(7),
    .auction-table-scroll #data-table th:nth-child(8),
    .auction-table-scroll #data-table th:nth-child(9),
    .auction-table-scroll #data-table th:nth-child(10) {
        width: 8%;
    }

    .auction-table-scroll #data-table th:nth-child(9),
    .auction-table-scroll #data-table td:nth-child(9),
    .auction-table-scroll #data-table th:nth-child(10),
    .auction-table-scroll #data-table td:nth-child(10) {
        white-space: nowrap !important;
    }
}

/* Tablet and mobile: scroll the table, not the whole page */

@media (max-width: 1199px) {

    .auction-table-scroll #data-table {
        width: 100%;
        min-width: 1080px;
        table-layout: auto;
    }

    .auction-table-scroll #data-table th,
    .auction-table-scroll #data-table td {
        white-space: nowrap;
    }
}
</style>


<style>
/* Final auction desktop scrollbar fix */

/* Wide desktop: table fits inside the card */
@media (min-width: 1500px) {
    .auction-table-scroll {
        overflow-x: clip !important;
        scrollbar-width: none;
    }

    .auction-table-scroll #data-table {
        width: 100% !important;
        max-width: 100%;
        table-layout: fixed;
    }
}

/* Smaller screens: preserve horizontal scrolling */
@media (max-width: 1499px) {
    .auction-table-scroll {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
}
</style>


<style>
/* Auction mobile layout fix */

.auction-mobile-hint {
    display: none;
}

@media (max-width: 767px) {

    .card.md-wide-search,
    .card.md-wide-search > .card-body {
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    #auctionsFilterPanel {
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    #auctionsFilterPanel .auction-filter-main {
        grid-template-columns: minmax(0, 1fr) !important;
    }

    #auctionsFilterPanel .auction-filter-field,
    #auctionsFilterPanel .auction-filter-bottom {
        min-width: 0;
        max-width: 100%;
    }

    #auctionsFilterPanel .form-select,
    #auctionsFilterPanel .form-control {
        display: block;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .auction-mobile-hint {
        display: flex;
        align-items: center;
        gap: 8px;
        direction: rtl;
        color: #3d5f45;
        background: #edf5ef;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 12px;
        font-size: 12px;
    }

    .md-wide-search .auction-table-scroll {
        display: block;
        position: relative;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-x: contain;
        scrollbar-width: thin;
    }

    .md-wide-search .auction-table-scroll #data-table {
        width: 1080px !important;
        min-width: 1080px !important;
        table-layout: auto !important;
    }

    .md-wide-search .auction-table-scroll #data-table th,
    .md-wide-search .auction-table-scroll #data-table td {
        white-space: nowrap !important;
        font-size: 12px;
        vertical-align: middle;
    }

    .md-wide-search .auction-dt-footer {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }
}
</style>


<style>
/* Auction custom mobile status dropdown */

.auction-status-mobile {
    display: none;
}

@media (max-width: 767px) {

    #auctionsFilterPanel.auction-status-ready #filter_status {
        display: none !important;
    }

    #auctionsFilterPanel.auction-status-ready .auction-status-mobile {
        display: block;
        position: relative;
        width: 100%;
        min-width: 0;
    }

    .auction-status-button {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-direction: row-reverse;
        width: 100%;
        min-height: 42px;
        padding: 9px 13px;
        border: 1px solid #dde6df;
        border-radius: 9px;
        background: #fff;
        color: #263c2e;
        cursor: pointer;
        text-align: right;
    }

    .auction-status-button:focus {
        outline: 2px solid #3d5f45;
        outline-offset: 2px;
    }

    .auction-status-menu {
        position: absolute;
        top: calc(100% + 5px);
        right: 0;
        left: 0;
        z-index: 1050;
        width: 100%;
        max-height: 220px;
        overflow-y: auto;
        border: 1px solid #dde6df;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
        box-sizing: border-box;
    }

    .auction-status-menu[hidden] {
        display: none !important;
    }

    .auction-status-choice {
        display: block;
        width: 100%;
        padding: 12px 14px;
        border: 0;
        border-bottom: 1px solid #edf0ee;
        background: #fff;
        color: #263c2e;
        text-align: right;
        cursor: pointer;
    }

    .auction-status-choice:last-child {
        border-bottom: 0;
    }

    .auction-status-choice:hover,
    .auction-status-choice:focus,
    .auction-status-choice.is-selected {
        background: #edf5ef;
        color: #3d5f45;
    }
}
</style>

@endpush


@section('content')
@include('dashboard.partials.page-header', [
    'title' => TranslationHelper::translate('Auctions'),
    'icon' => 'fa-solid fa-gavel',
    'actions' => '<a href="'.route('admin.videos.create').'" class="btn btn-primary"><i class="fas fa-plus"></i> '.TranslationHelper::translate('new Auction').'</a>',
])

@php
    $inProgressPct = $stats['total'] > 0 ? round($stats['in_progress'] / $stats['total'] * 100, 1) : 0;
    $upcomingPct = $stats['total'] > 0 ? round($stats['upcoming'] / $stats['total'] * 100, 1) : 0;
    $archivedPct = $stats['total'] > 0 ? round($stats['archived'] / $stats['total'] * 100, 1) : 0;
@endphp
@include('dashboard.partials.stat-row', ['cards' => [
    [
        'icon' => 'fa-solid fa-video',
        'value' => $stats['in_progress'],
        'label' => TranslationHelper::translate('in_progress'),
        'color' => 'success',
        'trend' => ['direction' => 'up', 'text' => $inProgressPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-clock',
        'value' => $stats['upcoming'],
        'label' => TranslationHelper::translate('upcoming'),
        'color' => 'warning',
        'trend' => ['direction' => 'up', 'text' => $upcomingPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-box-archive',
        'value' => $stats['archived'],
        'label' => TranslationHelper::translate('archived'),
        'color' => 'dark',
        'trend' => ['direction' => 'down', 'text' => $archivedPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-gavel',
        'value' => $stats['total'],
        'label' => TranslationHelper::translate('total_auctions'),
        'color' => 'primary',
        'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
    ],
]])

<div class="card md-wide-search">
    <div class="card-body">

        <div id="auctionsFilterPanel" class="auction-filter-panel">

            <div class="auction-filter-heading">
                <i class="fa-solid fa-filter"></i>
                <span>البحث والتصفية</span>
            </div>

            <div class="auction-filter-main">

                <div class="auction-filter-field">
                    <label for="filter_status" class="form-label">
                        {{ TranslationHelper::translate('Status') }}
                    </label>

                    <select id="filter_status" class="form-select">
                        <option value="">
                            {{ TranslationHelper::translate('all') }}
                        </option>
                        <option value="start">
                            {{ TranslationHelper::translate('in_progress') }}
                        </option>
                        <option value="upcoming">
                            {{ TranslationHelper::translate('upcoming') }}
                        </option>
                        <option value="end">
                            {{ TranslationHelper::translate('archived') }}
                        </option>
                    </select>

<div class="auction-status-mobile">
    <button
        type="button"
        id="auctionStatusButton"
        class="auction-status-button"
        aria-expanded="false"
        aria-controls="auctionStatusMenu"
    >
        <span class="auction-status-label"></span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>

    <div
        id="auctionStatusMenu"
        class="auction-status-menu"
        hidden
    ></div>
</div>

                </div>

                <div class="auction-filter-field">
                    <label for="filter_date_from" class="form-label">
                        {{ TranslationHelper::translate('from') }}
                    </label>

                    <input
                        type="date"
                        id="filter_date_from"
                        class="form-control"
                    >
                </div>

                <div class="auction-filter-field">
                    <label for="filter_date_to" class="form-label">
                        {{ TranslationHelper::translate('to') }}
                    </label>

                    <input
                        type="date"
                        id="filter_date_to"
                        class="form-control"
                    >
                </div>

            </div>

            <div class="auction-filter-bottom">

                <div
                    id="auctionSearchSlot"
                    class="auction-search-slot"
                ></div>

                <div
                    id="auctionLengthSlot"
                    class="auction-length-slot"
                ></div>

                <button
                    type="button"
                    id="filter_reset"
                    class="btn auction-filter-reset"
                >
                    <i class="fa-solid fa-rotate-right"></i>
                    {{ TranslationHelper::translate('reset') }}
                </button>

            </div>

        </div>

        <!--begin::Table-->
        <div class="auction-mobile-hint">
            <i class="fa-solid fa-arrows-left-right"></i>
            اسحب الجدول يمينًا ويسارًا لعرض جميع الأعمدة
        </div>

        <div class="table-responsive auction-table-scroll">
            <table id="data-table" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ TranslationHelper::translate('Auctions Title') }}</th>
                        <th>{{ TranslationHelper::translate('User') }}</th>
                        <th>{{ TranslationHelper::translate('start_date') }}</th>
                        <th>{{ TranslationHelper::translate('end_date') }}</th>
                        <th>{{ TranslationHelper::translate('Status') }}</th>
                        <th>{{ TranslationHelper::translate('products_count') }}</th>
                        <th>{{ TranslationHelper::translate('participations_count') }}</th>
                        <th>{{ TranslationHelper::translate('pieces') }}</th>
                        <th>{{ TranslationHelper::translate('actions') }}</th>

                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->

@endsection

@section('scripts_lib')
<script src="{{asset('dashboard/plugins/datatables/datatables.min.js')}}"></script>
<script>
    $('#data-table').DataTable({
        autoFill: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        search: {
            "caseInsensitive": true,
            "smart": true
        },
        ajax: {
            url : "{!! route('admin.auctions.getData') !!}",
            data: function (d) {
                d.filter_status = $('#filter_status').val();
                d.filter_date_from = $('#filter_date_from').val();
                d.filter_date_to = $('#filter_date_to').val();
            },
            type: "POST",
            dataType: "JSON"
        },
        order: [[0, 'desc']], // Sort by 'id' column in descending order (change to 'asc' for ascending)

        columns: [
            {data: 'id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'title', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'user_name', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'start_date', 'searchable': false, 'orderable': false, 'exportable': true, 'printable': true},
            {data: 'end_date', 'searchable': false, 'orderable': false, 'exportable': true, 'printable': true},
            {data: 'status', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'products_count', 'searchable': false, 'orderable': false, 'exportable': true, 'printable': true},
            {data: 'participations_count', 'searchable': false, 'orderable': false, 'exportable': true, 'printable': true},
            {data: 'pieces_action', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'action', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false}

        ],
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
            "searchPlaceholder": "{{ TranslationHelper::translate('search_auctions_placeholder') }}",
            "lengthMenu": "{{ TranslationHelper::translate('display') }} _MENU_ {{ TranslationHelper::translate('records_per_page') }}",
            "zeroRecords": "{{ TranslationHelper::translate('nothing_found') }}",
            "info": "{{ TranslationHelper::translate('showing_page') }} _PAGE_ {{ TranslationHelper::translate('of') }} _PAGES_",
            "infoEmpty": "{{ TranslationHelper::translate('nothing_found') }}",
            "infoFiltered": "({{ TranslationHelper::translate('filtered_from') }} _MAX_)",
            "loadingRecords": "{{TranslationHelper::translate('loading')}}...",
            "paginate": {
                "previous": @if(app()->getLocale() == 'ar') "<i class='fas fa-angle-right'></i>" @else "<i class='fas fa-angle-left'></i>" @endif,
                "next": @if(app()->getLocale() == 'ar') "<i class='fas fa-angle-left'></i>" @else "<i class='fas fa-angle-right'></i>" @endif
            }
        },
        dom: 'lfrt<"auction-dt-footer"ip>',
        initComplete: function () {
            var $wrapper = $(this.api().table().container());

            $wrapper.find('.dataTables_filter')
                .appendTo('#auctionSearchSlot');

            $wrapper.find('.dataTables_length')
                .appendTo('#auctionLengthSlot');
        }
    });

    $('#filter_status, #filter_date_from, #filter_date_to').on('change', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_reset').on('click', function () {
        $('#filter_status').val('');
        $('#filter_date_from, #filter_date_to').val('');
        $('#data-table').DataTable().draw(true);
    });

$(function () {
    var $panel = $('#auctionsFilterPanel');
    var $select = $('#filter_status');
    var $button = $('#auctionStatusButton');
    var $menu = $('#auctionStatusMenu');
    var $label = $button.find('.auction-status-label');

    if (!$select.length || !$button.length || !$menu.length) {
        return;
    }

    function closeStatusMenu() {
        $menu.prop('hidden', true);
        $button.attr('aria-expanded', 'false');
    }

    function syncStatusMenu() {
        var selected = $select.val();

        $label.text($select.find('option:selected').text().trim());

        $menu.find('.auction-status-choice').each(function () {
            var active = $(this).val() === selected;

            $(this)
                .toggleClass('is-selected', active)
                .attr('aria-pressed', active ? 'true' : 'false');
        });
    }

    $select.find('option').each(function () {
        var $option = $(this);

        $('<button>', {
            type: 'button',
            'class': 'auction-status-choice',
            text: $option.text().trim()
        })
        .val($option.val())
        .appendTo($menu);
    });

    $button.on('click', function () {
        var opening = $menu.prop('hidden');

        $menu.prop('hidden', !opening);

        $button.attr(
            'aria-expanded',
            opening ? 'true' : 'false'
        );
    });

    $menu.on('click', '.auction-status-choice', function () {
        $select.val($(this).val()).trigger('change');

        syncStatusMenu();
        closeStatusMenu();
    });

    $select.on('change', syncStatusMenu);

    $(document).on('click', function (event) {
        if (!$(event.target).closest('.auction-status-mobile').length) {
            closeStatusMenu();
        }
    });

    $button.add($menu).on('keydown', function (event) {
        if (event.key === 'Escape') {
            closeStatusMenu();
            $button.trigger('focus');
        }
    });

    syncStatusMenu();

    // Switch to the custom control only after initialization.
    $panel.addClass('auction-status-ready');
});

</script>
@endsection
