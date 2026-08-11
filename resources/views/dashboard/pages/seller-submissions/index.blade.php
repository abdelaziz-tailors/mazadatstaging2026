@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Seller Submissions') }} @endsection

@push('css')
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
<style>
    .seller-submissions-page {
        --seller-green: #154734;
        --seller-dark-green: #0b3d2e;
        --seller-gold: #c99a35;
        --seller-success: #22b573;
        --seller-warning: #e7a834;
        --seller-danger: #e75a5a;
        --seller-border: #e4e8ec;
        --seller-muted: #667085;
        direction: rtl;
        min-width: 0;
    }

    .seller-submissions-page .seller-page-header {
        margin-bottom: 1.25rem;
    }

    .seller-submissions-page .seller-page-header .page-title {
        color: var(--seller-dark-green);
        font-size: clamp(1.55rem, 2.2vw, 2rem);
        font-weight: 800;
        margin-bottom: .35rem;
    }

    .seller-submissions-page .seller-page-description {
        color: var(--seller-muted);
        font-size: .9rem;
        margin: 0;
    }

    .seller-submissions-page .seller-submissions-stats > .row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin: 0 0 1.25rem;
    }

    .seller-submissions-page .seller-submissions-stats > .row > [class*="col-"] {
        width: auto;
        max-width: none;
        padding: 0;
    }

    .seller-submissions-page .seller-submissions-stats > .row > [class*="col-"] > .card {
        height: 100% !important;
        margin: 0;
        border: 1px solid var(--seller-border);
        border-radius: 16px;
        box-shadow: 0 5px 18px rgba(16, 24, 40, .045);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .seller-submissions-page .seller-submissions-stats > .row > [class*="col-"] > .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(16, 24, 40, .08);
    }

    .seller-submissions-page .seller-submissions-stats > .row .stat-card {
        min-height: 142px;
        padding: 20px;
    }

    .seller-submissions-page .seller-submissions-stats > .row .stat-card .stat-icon {
        top: 18px;
        inset-inline-start: 18px;
    }

    .seller-submissions-page .seller-submissions-stats > .row .stat-card .stat-value {
        margin-top: 34px;
        font-size: 28px;
    }

    .seller-submissions-page .seller-submissions-stats > .row .stat-card .stat-label {
        font-size: 14px;
    }

    .seller-submissions-page .seller-requests-card {
        border: 1px solid var(--seller-border);
        border-radius: 18px;
        box-shadow: 0 7px 24px rgba(16, 24, 40, .05);
    }

    .seller-submissions-page .seller-requests-card > .card-body {
        padding: 1.15rem 1.25rem;
    }

    .seller-submissions-page .seller-requests-title {
        color: var(--seller-dark-green);
        font-size: 1.1rem;
        font-weight: 800;
        margin: 0;
    }

    .seller-submissions-page .seller-requests-subtitle {
        color: var(--seller-muted);
        font-size: .8rem;
        margin: .25rem 0 0;
    }

    .seller-submissions-page .dataTables_wrapper {
        direction: rtl;
    }

    .seller-submissions-page .seller-requests-card .dataTables_wrapper > .d-flex {
        gap: .9rem;
        padding: 0 !important;
        margin-bottom: 1rem !important;
    }

    .seller-submissions-page .dataTables_length,
    .seller-submissions-page .dataTables_filter {
        color: var(--seller-muted);
        font-size: .85rem;
    }

    .seller-submissions-page .dataTables_filter input,
    .seller-submissions-page .dataTables_length select {
        height: 42px;
        border: 1px solid var(--seller-border);
        border-radius: 10px;
        background: #fff;
        color: #1f2937;
        outline: none;
    }

    .seller-submissions-page .dataTables_filter input {
        width: min(360px, 100%);
        margin-inline-start: .45rem;
        padding: 0 .85rem;
    }

    .seller-submissions-page .dataTables_length select {
        min-width: 68px;
        margin: 0 .35rem;
        padding: 0 .55rem;
    }

    .seller-submissions-page #data-table {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    .seller-submissions-page #data-table thead th {
        border: 0;
        background: #f8fafb;
        color: #667085;
        font-size: .78rem;
        font-weight: 700;
        padding: .9rem .75rem;
        white-space: nowrap;
    }

    .seller-submissions-page #data-table tbody td {
        border-bottom: 1px solid #edf0f2;
        color: #344054;
        font-size: .88rem;
        padding: .85rem .75rem;
        vertical-align: middle;
    }

    .seller-submissions-page #data-table tbody tr:hover td {
        background: #fafbfc;
    }

    .seller-submissions-page #data-table tbody td:nth-child(3) {
        direction: ltr;
        text-align: right;
        white-space: nowrap;
    }

    .seller-submissions-page #data-table .badge {
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        padding: .42rem .7rem;
    }

    .seller-submissions-page #data-table .badge.bg-secondary {
        background: #fff6e5 !important;
        color: #986b10 !important;
    }

    .seller-submissions-page #data-table .badge.bg-success {
        background: #e9f8f1 !important;
        color: #168052 !important;
    }

    .seller-submissions-page #data-table .badge.bg-danger {
        background: #fdecec !important;
        color: #c44242 !important;
    }

    .seller-submissions-page #data-table .badge.bg-warning {
        background: #fff6e5 !important;
        color: #986b10 !important;
    }

    .seller-submissions-page .seller-table-actions {
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .seller-submissions-page .seller-table-actions .md-icon-btn {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-color: var(--seller-border);
    }

    .seller-submissions-page .seller-table-actions form {
        margin: 0;
    }

    .seller-submissions-page .dataTables_info,
    .seller-submissions-page .dataTables_paginate {
        color: var(--seller-muted);
        font-size: .82rem;
        margin-top: 1rem;
    }

    .seller-submissions-page .dataTables_paginate .paginate_button {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        margin: 0 .15rem !important;
        padding: 0 .65rem !important;
        border: 1px solid var(--seller-border) !important;
        border-radius: 9px !important;
        background: #fff !important;
        color: var(--seller-dark-green) !important;
    }

    .seller-submissions-page .dataTables_paginate .paginate_button.current {
        border-color: var(--seller-dark-green) !important;
        background: var(--seller-dark-green) !important;
        color: #fff !important;
    }

    @media (max-width: 1199px) {
        .seller-submissions-page .seller-submissions-stats > .row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .seller-submissions-page .seller-page-header {
            margin-bottom: 1rem;
        }

        .seller-submissions-page .seller-page-description {
            line-height: 1.6;
        }

        .seller-submissions-page .seller-submissions-stats > .row {
            gap: .7rem;
        }

        .seller-submissions-page .seller-submissions-stats > .row .stat-card {
            min-height: 126px;
            padding: 14px;
        }

        .seller-submissions-page .seller-submissions-stats > .row .stat-card .stat-icon {
            top: 12px;
            inset-inline-start: 12px;
            width: 34px;
            height: 34px;
            min-width: 34px;
            font-size: 13px;
        }

        .seller-submissions-page .seller-submissions-stats > .row .stat-card .stat-value {
            margin-top: 28px;
            font-size: 24px;
        }

        .seller-submissions-page .seller-submissions-stats > .row .stat-card .stat-label {
            font-size: 12px;
        }

        .seller-submissions-page .seller-submissions-stats > .row .stat-card .stat-trend {
            font-size: 11px;
            margin-top: 7px;
        }

        .seller-submissions-page .seller-requests-card > .card-body {
            padding: .85rem;
        }

        .seller-submissions-page .seller-requests-card .dataTables_wrapper > .d-flex {
            align-items: stretch !important;
            flex-direction: column;
        }

        .seller-submissions-page .dataTables_length,
        .seller-submissions-page .dataTables_filter,
        .seller-submissions-page .dataTables_filter input {
            width: 100%;
        }

        .seller-submissions-page .dataTables_filter input {
            margin: .35rem 0 0;
        }

        .seller-submissions-page #data-table,
        .seller-submissions-page #data-table tbody,
        .seller-submissions-page #data-table tr,
        .seller-submissions-page #data-table td {
            display: block;
            width: 100% !important;
        }

        .seller-submissions-page #data-table thead {
            display: none;
        }

        .seller-submissions-page #data-table tbody {
            display: grid;
            gap: .75rem;
        }

        .seller-submissions-page #data-table tbody tr {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .65rem .9rem;
            padding: .95rem;
            border: 1px solid var(--seller-border);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 14px rgba(16, 24, 40, .04);
        }

        .seller-submissions-page #data-table tbody td {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .5rem;
            min-width: 0;
            padding: .35rem 0;
            border: 0;
            text-align: right;
            white-space: normal;
        }

        .seller-submissions-page #data-table tbody td::before {
            flex: 0 0 auto;
            color: var(--seller-muted);
            content: attr(data-label);
            font-size: .72rem;
            font-weight: 700;
        }

        .seller-submissions-page #data-table tbody td:nth-child(1),
        .seller-submissions-page #data-table tbody td:nth-last-child(1),
        .seller-submissions-page #data-table tbody td:nth-last-child(2) {
            grid-column: 1 / -1;
        }

        .seller-submissions-page #data-table tbody td:nth-child(1) {
            color: var(--seller-dark-green);
            font-weight: 800;
        }

        .seller-submissions-page #data-table tbody td:nth-child(3) {
            direction: ltr;
            text-align: right;
        }

        .seller-submissions-page #data-table tbody td:last-child {
            align-items: center;
            margin-top: .3rem;
            padding-top: .8rem;
            border-top: 1px solid #edf0f2;
        }

        .seller-submissions-page .seller-table-actions {
            width: 100%;
            justify-content: flex-start;
            gap: .45rem !important;
        }

        .seller-submissions-page .seller-table-actions .md-icon-btn {
            width: 42px;
            height: 42px;
            min-width: 42px;
        }

        .seller-submissions-page .dataTables_info,
        .seller-submissions-page .dataTables_paginate {
            float: none !important;
            text-align: center !important;
        }
    }

    @media (max-width: 375px) {
        .seller-submissions-page .seller-submissions-stats > .row {
            grid-template-columns: 1fr;
        }

        .seller-submissions-page #data-table tbody tr {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="seller-submissions-page">
<div class="page-header seller-page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ TranslationHelper::translate('Seller Submissions') }}</h3>
            <p class="seller-page-description">إدارة ومراجعة طلبات عرض القطع المقدمة من البائعين</p>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Seller Submissions') }}</li>
            </ul>
        </div>
    </div>
</div>

<div class="seller-submissions-stats">
@include('dashboard.partials.stat-row', ['cards' => [
    [
        'icon' => 'fa-solid fa-circle-xmark',
        'value' => $stats['rejected'],
        'label' => TranslationHelper::translate('rejected_submissions'),
        'color' => 'danger',
        'trend' => ['direction' => 'down', 'text' => $stats['rejected_pct'] . '%'],
    ],
    [
        'icon' => 'fa-solid fa-circle-check',
        'value' => $stats['approved'],
        'label' => TranslationHelper::translate('approved_submissions'),
        'color' => 'success',
        'trend' => ['direction' => 'up', 'text' => $stats['approved_pct'] . '%'],
    ],
    [
        'icon' => 'fa-solid fa-clock',
        'value' => $stats['under_review'],
        'label' => TranslationHelper::translate('submissions_under_review'),
        'color' => 'warning',
        'trend' => ['direction' => 'up', 'text' => $stats['under_review_pct'] . '%'],
    ],
    [
        'icon' => 'fa-solid fa-clipboard-list',
        'value' => $stats['total'],
        'label' => TranslationHelper::translate('total_submissions'),
        'color' => 'primary',
        'trend' => ['direction' => $stats['total_trend_direction'], 'text' => $stats['total_trend_pct'] . '%'],
    ],
]])
</div>

<div class="card seller-requests-card">
    <div class="card-body">
        <div class="seller-requests-heading mb-3">
            <h4 class="seller-requests-title">طلبات عرض القطع</h4>
            <p class="seller-requests-subtitle">عرض ومراجعة الطلبات المقدمة من البائعين</p>
        </div>
        <div class="table-responsive">
            <table id="data-table" class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ TranslationHelper::translate('name') }}</th>
                    <th>{{ TranslationHelper::translate('phone') }}</th>
                    <th>{{ TranslationHelper::translate('sheep_type') }}</th>
                    @if(!($isPartnerDashboard ?? false))
                    <th>{{ TranslationHelper::translate('partner') }}</th>
                    @endif
                    <th>{{ TranslationHelper::translate('status') }}</th>
                    <th>{{ TranslationHelper::translate('created_at') }}</th>
                    <th>{{ TranslationHelper::translate('actions') }}</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection

@section('scripts_lib')
@php
    $sellerSubmissionDtColumns = ($isPartnerDashboard ?? false)
        ? [
            ['data' => 'id'],
            ['data' => 'name', 'orderable' => false, 'searchable' => false],
            ['data' => 'phone', 'orderable' => false, 'searchable' => false],
            ['data' => 'sheep_type'],
            ['data' => 'status_badge', 'orderable' => false, 'searchable' => false],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]
        : [
            ['data' => 'id'],
            ['data' => 'name', 'orderable' => false, 'searchable' => false],
            ['data' => 'phone', 'orderable' => false, 'searchable' => false],
            ['data' => 'sheep_type'],
            ['data' => 'partner', 'orderable' => false, 'searchable' => false],
            ['data' => 'status_badge', 'orderable' => false, 'searchable' => false],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ];
    $sellerSubmissionDtLabels = ($isPartnerDashboard ?? false)
        ? ['#', TranslationHelper::translate('name'), TranslationHelper::translate('phone'), TranslationHelper::translate('sheep_type'), TranslationHelper::translate('status'), TranslationHelper::translate('created_at'), TranslationHelper::translate('actions')]
        : ['#', TranslationHelper::translate('name'), TranslationHelper::translate('phone'), TranslationHelper::translate('sheep_type'), TranslationHelper::translate('partner'), TranslationHelper::translate('status'), TranslationHelper::translate('created_at'), TranslationHelper::translate('actions')];
@endphp
<script src="{{asset('dashboard/plugins/datatables/datatables.min.js')}}"></script>
<script>
    $('#data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url : "{!! route('admin.seller-submissions.getData') !!}",
            type: "POST",
            dataType: "JSON"
        },
        order: [[0, 'desc']],
        columns: @json($sellerSubmissionDtColumns),
        createdRow: function (row) {
            const labels = @json($sellerSubmissionDtLabels);
            $('td', row).each(function (index) {
                $(this).attr('data-label', labels[index] || '');
            });
            $('td:last-child', row).find('> div').addClass('seller-table-actions');
        },
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
            "searchPlaceholder": "{{ TranslationHelper::translate('search_seller_submissions_placeholder') }}",
            "lengthMenu": "{{ TranslationHelper::translate('display') }} _MENU_ {{ TranslationHelper::translate('records_per_page') }}",
            "zeroRecords": "{{ TranslationHelper::translate('nothing_found') }}",
            "info": "{{ TranslationHelper::translate('showing_page') }} _PAGE_ {{ TranslationHelper::translate('of') }} _PAGES_",
            "infoEmpty": "{{ TranslationHelper::translate('nothing_found') }}",
            "infoFiltered": "({{ TranslationHelper::translate('filtered_from') }} _MAX_)",
            "loadingRecords": "{{ TranslationHelper::translate('loading') }}...",
            "paginate": {
                "previous": @if(app()->getLocale() == 'ar') "<i class='fas fa-angle-right'></i>" @else "<i class='fas fa-angle-left'></i>" @endif,
                "next": @if(app()->getLocale() == 'ar') "<i class='fas fa-angle-left'></i>" @else "<i class='fas fa-angle-right'></i>" @endif
            }
        },
        dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3 px-2"<l><f>>rt<"d-flex justify-content-between px-2"<"d-flex align-items-center"<><i>><p>>'
    });
</script>
@endsection
