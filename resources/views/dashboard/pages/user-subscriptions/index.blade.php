@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('User Subscriptions') }} @endsection

@push('css')
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
<!--end::Page Vendor Stylesheets-->

<style>
/* Mazadat subscriptions responsive v1 */

.md-us-card,
.md-us-card > .card-body,
.md-us-datatable-host,
.md-us-card .dataTables_wrapper {
    width: 100%;
    max-width: 100%;
    min-width: 0;
}

.md-us-filters {
    padding: 20px;
    margin-bottom: 20px;
    background: #f9fbf9;
    border: 1px solid #e4ece6;
    border-radius: 14px;
}

.md-us-filters-title {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 18px;
    color: #3d5f45;
    font-size: 16px;
    font-weight: 700;
}

.md-us-filter-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    direction: rtl;
}

.md-us-field {
    min-width: 0;
}

.md-us-field label {
    display: block;
    margin-bottom: 8px;
    color: #627469;
    font-size: 13px;
    font-weight: 600;
}

.md-us-field .form-control,
.md-us-field .form-select {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    min-height: 42px;
    border: 1px solid #dce6df;
    border-radius: 9px;
    background-color: #fff;
    box-sizing: border-box;
}

.md-us-field .form-control:focus,
.md-us-field .form-select:focus {
    border-color: #3d5f45;
    box-shadow: 0 0 0 3px rgba(61, 95, 69, .1);
}

.md-us-reset-field {
    display: flex;
    align-items: end;
    min-width: 0;
}

.md-us-reset {
    width: 100%;
    min-height: 42px;
    background: #eaf2ec;
    color: #3d5f45;
    border: 1px solid #cbded1;
    border-radius: 9px;
    font-weight: 600;
}

.md-us-reset:hover {
    background: #3d5f45;
    border-color: #3d5f45;
    color: #fff;
}

/* DataTables controls outside the scrolling table. */

.md-us-toolbar,
.md-us-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    direction: rtl;
}

.md-us-toolbar {
    margin: 0 0 18px;
}

.md-us-footer {
    margin-top: 16px;
}

.md-us-search {
    flex: 1 1 280px;
    min-width: 0;
}

.md-us-length {
    flex: 0 1 auto;
}

.md-us-search .dataTables_filter,
.md-us-length .dataTables_length {
    float: none !important;
    width: 100%;
    margin: 0;
    text-align: start;
}

.md-us-search .dataTables_filter label {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    width: 100%;
    margin: 0;
    color: #627469;
}

.md-us-search .dataTables_filter input {
    flex: 1 1 180px;
    width: 100%;
    min-width: 0;
    min-height: 42px;
    margin: 0 !important;
    border: 1px solid #dce6df;
    border-radius: 9px;
}

.md-us-length .dataTables_length label {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.md-us-length .dataTables_length select {
    min-width: 65px;
    min-height: 42px;
    margin: 0;
}

/* The table alone scrolls horizontally. */

.md-us-table-scroll {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior-x: contain;
    scrollbar-width: thin;
    scrollbar-color: #b8c9bd #f0f4f1;
}

.md-us-table-scroll #data-table {
    width: 100% !important;
    min-width: 1210px;
    table-layout: auto;
}

.md-us-table-scroll #data-table th,
.md-us-table-scroll #data-table td {
    vertical-align: middle;
    white-space: nowrap;
    font-size: 12px;
}

.md-us-table-scroll #data-table th:nth-child(2),
.md-us-table-scroll #data-table td:nth-child(2) {
    min-width: 115px;
    white-space: normal;
    overflow-wrap: anywhere;
}

.md-us-table-scroll #data-table th:last-child,
.md-us-table-scroll #data-table td:last-child {
    min-width: 95px;
}

/* Tablet */

@media (max-width: 1199px) {
    .md-us-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Mobile */

@media (max-width: 767px) {
    .md-us-card > .card-body {
        padding: 12px;
    }

    .md-us-filters {
        padding: 14px;
    }

    .md-us-filter-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 13px;
    }

    .md-us-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 14px;
    }

    .md-us-search,
    .md-us-length {
        flex: 0 0 auto;
        width: 100%;
    }

    .md-us-search .dataTables_filter label {
        flex-direction: column;
        align-items: stretch;
        text-align: right;
    }

    .md-us-search .dataTables_filter input {
        flex: 0 0 auto;
        width: 100%;
        max-width: 100%;
    }

    .md-us-footer {
        align-items: center;
        gap: 12px;
    }

    .md-us-footer .dataTables_info {
        padding-top: 0;
    }
}
</style>

@endpush


@section('content')
@include('dashboard.partials.page-header', [
    'title' => TranslationHelper::translate('User Subscriptions'),
    'icon' => 'fa-solid fa-briefcase',
])

@php
    $approvedPct = $stats['total'] > 0 ? round($stats['approved'] / $stats['total'] * 100, 1) : 0;
    $pendingPct = $stats['total'] > 0 ? round($stats['pending'] / $stats['total'] * 100, 1) : 0;
    $rejectedPct = $stats['total'] > 0 ? round($stats['rejected'] / $stats['total'] * 100, 1) : 0;
@endphp
@include('dashboard.partials.stat-row', ['cards' => [
    [
        'icon' => 'fa-solid fa-circle-check',
        'value' => $stats['approved'],
        'label' => TranslationHelper::translate('Approved'),
        'color' => 'success',
        'trend' => ['direction' => 'up', 'text' => $approvedPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-clock',
        'value' => $stats['pending'],
        'label' => TranslationHelper::translate('Pending'),
        'color' => 'warning',
        'trend' => ['direction' => 'down', 'text' => $pendingPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-circle-xmark',
        'value' => $stats['rejected'],
        'label' => TranslationHelper::translate('Rejected'),
        'color' => 'danger',
        'trend' => ['direction' => 'down', 'text' => $rejectedPct . '%'],
    ],
    [
        'icon' => 'fa-solid fa-briefcase',
        'value' => $stats['total'],
        'label' => TranslationHelper::translate('total_subscriptions'),
        'color' => 'primary',
        'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
    ],
]])

<div class="card md-wide-search md-us-card">
    <div class="card-body">

        <div
            id="userSubscriptionsFilterPanel"
            class="md-us-filters"
        >
            <div class="md-us-filters-title">
                <i class="fa-solid fa-sliders"></i>
                البحث والتصفية
            </div>

            <div class="md-us-filter-grid">

                <div class="md-us-field">
                    <label for="filter_user">
                        {{ TranslationHelper::translate('user') }}
                    </label>
                    <input
                        type="text"
                        id="filter_user"
                        class="form-control"
                    >
                </div>

                <div class="md-us-field">
                    <label for="filter_subscription_type">
                        {{ TranslationHelper::translate('subscription_type') }}
                    </label>

                    <select
                        id="filter_subscription_type"
                        class="form-select"
                    >
                        <option value="">
                            {{ TranslationHelper::translate('all') }}
                        </option>
                        <option value="monthly">
                            {{ TranslationHelper::translate('Monthly') }}
                        </option>
                        <option value="annual">
                            {{ TranslationHelper::translate('Annual') }}
                        </option>
                    </select>
                </div>

                <div class="md-us-field">
                    <label for="filter_status">
                        {{ TranslationHelper::translate('approval_status') }}
                    </label>

                    <select
                        id="filter_status"
                        class="form-select"
                    >
                        <option value="">
                            {{ TranslationHelper::translate('all') }}
                        </option>
                        <option value="pending">
                            {{ TranslationHelper::translate('Pending') }}
                        </option>
                        <option value="approved">
                            {{ TranslationHelper::translate('Approved') }}
                        </option>
                        <option value="rejected">
                            {{ TranslationHelper::translate('Rejected') }}
                        </option>
                    </select>
                </div>

                <div class="md-us-field">
                    <label for="filter_date_from">
                        {{ TranslationHelper::translate('from') }}
                    </label>
                    <input
                        type="date"
                        id="filter_date_from"
                        class="form-control"
                    >
                </div>

                <div class="md-us-field">
                    <label for="filter_date_to">
                        {{ TranslationHelper::translate('to') }}
                    </label>
                    <input
                        type="date"
                        id="filter_date_to"
                        class="form-control"
                    >
                </div>

                <div class="md-us-reset-field">
                    <button
                        type="button"
                        id="filter_reset"
                        class="btn md-us-reset"
                    >
                        <i class="fa-solid fa-rotate-right"></i>
                        {{ TranslationHelper::translate('reset') }}
                    </button>
                </div>

            </div>
        </div>

        <!--begin::Table-->
        <div class="md-us-datatable-host">
            <table id="data-table" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ TranslationHelper::translate('user') }}</th>
                        <th>{{ TranslationHelper::translate('subscription_type') }}</th>
                        <th>{{ TranslationHelper::translate('auctions_limit') }}</th>
                        <th>{{ TranslationHelper::translate('remaining_auctions') }}</th>
                        <th>{{ TranslationHelper::translate('price') }}</th>
                        <th>{{ TranslationHelper::translate('expires_at') }}</th>
                        <th>{{ TranslationHelper::translate('approval_status') }}</th>
                        <th>{{ TranslationHelper::translate('status') }}</th>
                        <th>{{ TranslationHelper::translate('created_at') }}</th>
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
        processing: true,
        serverSide: true,
        search: {
            "caseInsensitive": true,
            "smart": true
        },
        ajax: {
            url : "{!! route('admin.user-subscriptions.getData') !!}",
            data: function (d) {
                d.filter_user = $('#filter_user').val();
                d.filter_subscription_type = $('#filter_subscription_type').val();
                d.filter_status = $('#filter_status').val();
                d.filter_date_from = $('#filter_date_from').val();
                d.filter_date_to = $('#filter_date_to').val();
            },
            type: "POST",
            dataType: "JSON"
        },
        columns: [
            {data: 'id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'user_id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'subscription_type', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'auctions_limit', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'remaining_auctions', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'price', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'expires_at', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'status', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'is_active', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'created_at', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'action', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false}
        ],
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
            "searchPlaceholder": "{{ TranslationHelper::translate('search_user_subscriptions_placeholder') }}",
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
        dom: '<"md-us-toolbar"<"md-us-search"f><"md-us-length"l>><"md-us-table-scroll"t><"md-us-footer"ip>'
    });

    $('#filter_user').on('keyup', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_subscription_type, #filter_status, #filter_date_from, #filter_date_to').on('change', function () {
        $('#data-table').DataTable().draw(true);
    });
    $('#filter_reset').on('click', function () {
        $('#filter_user, #filter_date_from, #filter_date_to').val('');
        $('#filter_subscription_type, #filter_status').val('');

        $('.md-us-search input[type="search"]').val('');

        $('#data-table').DataTable().search('').draw(true);
    });
</script>
@endsection

