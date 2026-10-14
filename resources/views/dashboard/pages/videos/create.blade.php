@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('New Auction') }} @endsection

@section('content')
<style>
    .auction-create-page {
        --auction-green: #114b35;
        --auction-dark-green: #0b3d2e;
        --auction-gold: #c99a35;
        --auction-bg: #f5f7f8;
        --auction-border: #e3e8ea;
        color: #1f2937;
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .auction-create-page .auction-create-card {
        border: 0;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 28px rgba(11, 61, 46, .08);
    }

    .auction-create-page .auction-create-card > .card-body {
        padding: clamp(1rem, 2.4vw, 2rem);
    }

    .auction-create-page .auction-page-heading {
        align-items: center !important;
        margin-bottom: 1.35rem !important;
        min-width: 0;
    }

    .auction-create-page .auction-page-heading > div:first-child {
        min-width: 0;
        flex: 1 1 auto;
    }

    .auction-create-page .auction-page-heading h3 {
        color: var(--auction-dark-green);
        font-weight: 700;
        margin-bottom: .4rem !important;
        line-height: 1.3;
    }

    .auction-create-page .auction-page-heading .breadcrumb {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .15rem 0;
        direction: rtl;
    }

    .auction-create-page .auction-page-heading .breadcrumb-item {
        white-space: nowrap;
    }

    .auction-create-page .auction-page-icon {
        display: inline-flex;
        width: 48px;
        height: 48px;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: var(--auction-dark-green);
        background: #e5eee9;
        font-size: 1.35rem;
    }

    .auction-create-page .auction-create-form {
        display: grid;
        grid-template-columns: 1fr;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        margin-inline: 0;
        gap: .35rem 1rem;
    }

    .auction-create-page #kt_form_1 {
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .auction-create-page .auction-section-heading {
        display: flex;
        align-items: center;
        gap: .55rem;
        margin: .65rem 0 .8rem;
        color: var(--auction-dark-green);
        font-weight: 700;
    }

    .auction-create-page .auction-section-heading i {
        color: var(--auction-gold);
    }

    .auction-create-page .auction-create-form > .auction-divider {
        grid-column: 1 / -1;
        margin: .45rem 0 .2rem;
        border-color: var(--auction-border);
        opacity: 1;
    }

    .auction-create-page .auction-create-form > .auction-section-heading-wrap {
        grid-column: 1 / -1;
    }

    .auction-create-page .auction-create-form > .auction-actions-wrap {
        grid-column: 1 / -1;
    }

    .auction-create-page .auction-create-form .form-label {
        display: block;
        min-height: 1.65rem;
        margin-bottom: .45rem;
        color: #1f2937;
        font-weight: 600;
        text-align: right;
    }

    .auction-create-page .auction-create-form .form-control,
    .auction-create-page .auction-create-form .select2-container--default .select2-selection--single {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        min-height: 46px;
        border: 1px solid var(--auction-border);
        border-radius: 9px;
        background-color: #fff;
        box-shadow: none;
    }

    .auction-create-page .auction-create-form .form-control:focus,
    .auction-create-page .auction-create-form select:focus {
        border-color: var(--auction-green);
        box-shadow: 0 0 0 .18rem rgba(17, 75, 53, .1);
    }

    /* Bootstrap's col widths are replaced by the page grid below. */
    .auction-create-page .auction-create-form > .auction-details-field,
    .auction-create-page .auction-create-form > .auction-schedule-field,
    .auction-create-page .auction-create-form > .auction-banner-field,
    .auction-create-page .auction-create-form > .auction-fees-field,
    .auction-create-page .auction-create-form > .auction-video-field,
    .auction-create-page .auction-create-form > .auction-terms-field,
    .auction-create-page .auction-create-form > #partner-select-field {
        width: 100%;
    }

    .auction-create-page .auction-create-form textarea.form-control {
        min-height: 132px;
        resize: vertical;
    }

    .auction-create-page .auction-banner-field,
    .auction-create-page .auction-fees-field,
    .auction-create-page .auction-video-field,
    .auction-create-page .auction-terms-field {
        padding: 1rem;
        border: 1px solid var(--auction-border);
        border-radius: 14px;
        background: #fff;
    }

    .auction-create-page .auction-upload-zone {
        position: relative;
        display: flex;
        min-height: 155px;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        border: 1.5px dashed #ccd8d3;
        border-radius: 12px;
        background: #fbfcfc;
        text-align: center;
        cursor: pointer;
    }

    .auction-create-page .auction-upload-zone:hover,
    .auction-create-page .auction-upload-zone:focus-within {
        border-color: var(--auction-green);
        background: #f5faf7;
    }

    .auction-create-page .auction-upload-zone input[type=file] {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
        opacity: 0;
    }

    .auction-create-page .auction-upload-zone i {
        display: block;
        margin-bottom: .5rem;
        color: var(--auction-green);
        font-size: 2rem;
    }

    .auction-create-page .auction-upload-zone strong,
    .auction-create-page .auction-upload-zone span {
        display: block;
    }

    .auction-create-page .auction-upload-zone strong {
        color: var(--auction-dark-green);
    }

    .auction-create-page .auction-upload-zone span {
        margin-top: .25rem;
        color: #7b8790;
        font-size: .78rem;
    }

    .auction-create-page .auction-fees-field {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .8rem;
    }

    .auction-create-page .auction-fees-field > .auction-fees-heading,
    .auction-create-page .auction-fees-field > .auction-fees-hint,
    .auction-create-page .auction-fees-field > .auction-fees-payer {
        grid-column: 1 / -1;
    }

    .auction-create-page .auction-fees-field > .auction-fees-heading {
        margin-bottom: -.5rem;
    }

    .auction-create-page .auction-fees-field > .auction-fees-hint {
        margin-bottom: 0;
    }

    .auction-create-page .auction-fees-field > .form-group {
        width: 100%;
        min-width: 0;
        margin-bottom: 0 !important;
    }

    .auction-create-page .auction-fees-field .md-label-2-lines {
        min-height: 2.9rem;
        width: 100%;
        line-height: 1.45;
    }

    .auction-create-page .auction-video-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .auction-create-page .auction-video-option {
        position: relative;
        display: flex;
        min-height: 78px;
        align-items: center;
        gap: .65rem;
        padding: .8rem 1rem;
        border: 1px solid var(--auction-border);
        border-radius: 10px;
        cursor: pointer;
    }

    .auction-create-page .auction-video-option:has(input:checked) {
        border-color: var(--auction-green);
        background: #f2f8f5;
        box-shadow: inset 0 0 0 1px var(--auction-green);
    }

    .auction-create-page .auction-video-option input {
        accent-color: var(--auction-green);
    }

    .auction-create-page .auction-video-option strong,
    .auction-create-page .auction-video-option small {
        display: block;
    }

    .auction-create-page .auction-video-option small {
        margin-top: .2rem;
        color: #7b8790;
    }

    .auction-create-page .auction-actions {
        display: flex;
        justify-content: flex-start;
        gap: .6rem;
        padding-top: .9rem;
        border-top: 1px solid var(--auction-border);
    }

    .auction-create-page .auction-actions .btn-primary {
        min-width: 155px;
        min-height: 46px;
        border-color: var(--auction-dark-green);
        border-radius: 9px;
        background: var(--auction-dark-green);
    }

    @media (min-width: 992px) {
        .auction-create-page .auction-create-form {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }

        .auction-create-page .auction-create-form > .auction-details-field {
            grid-column: span 4;
        }

        .auction-create-page .auction-create-form > .auction-schedule-field {
            grid-column: span 3;
        }

        .auction-create-page .auction-create-form > .auction-banner-field {
            grid-column: span 5;
        }

        .auction-create-page .auction-create-form > .auction-fees-field {
            grid-column: span 7;
        }

        .auction-create-page .auction-create-form > .auction-video-field {
            grid-column: span 5;
        }

        .auction-create-page .auction-create-form > .auction-terms-field,
        .auction-create-page .auction-create-form > #partner-select-field {
            grid-column: span 7;
        }

        .auction-create-page .auction-create-form > .auction-actions-wrap {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 991.98px) {
        /* Keep the create page full-width on phones. The global dashboard
           sidebar is fixed and must not reserve space for the form. */
        body:has(.auction-create-page) .page-wrapper,
        body:has(.auction-create-page) .content {
            width: 100%;
            max-width: 100%;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .auction-create-page,
        .auction-create-page > .row,
        .auction-create-page > .row > [class*="col-"] ,
        .auction-create-page .auction-create-card,
        .auction-create-page .auction-create-card > .card-body {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        .auction-create-page > .row {
            margin-inline: 0;
        }

        .auction-create-page > .row > [class*="col-"] {
            padding-inline: 0 !important;
        }

        .auction-create-page .auction-create-form > * {
            width: 100% !important;
            max-width: 100%;
            min-width: 0;
            min-inline-size: 0;
            flex: none;
            padding-inline: 0;
            box-sizing: border-box;
        }

        .auction-create-page .auction-create-form > .auction-details-field,
        .auction-create-page .auction-create-form > .auction-schedule-field,
        .auction-create-page .auction-create-form > .auction-banner-field,
        .auction-create-page .auction-create-form > .auction-fees-field,
        .auction-create-page .auction-create-form > .auction-video-field,
        .auction-create-page .auction-create-form > .auction-terms-field,
        .auction-create-page .auction-create-form > #partner-select-field {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 575.98px) {
        .auction-create-page .auction-create-card > .card-body {
            padding: .85rem;
        }

        .auction-create-page .auction-page-heading h3 {
            font-size: clamp(22px, 6vw, 25px);
            white-space: nowrap;
        }

        .auction-create-page .auction-fees-field {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .auction-create-page .auction-fees-field > .auction-fee-service,
        .auction-create-page .auction-fees-field > .auction-fees-payer {
            grid-column: 1 / -1;
        }

        .auction-create-page .auction-actions .btn-primary {
            width: 100%;
            min-width: 0;
        }
    }

    @media (max-width: 430px) {
        .auction-create-page .auction-fees-field {
            grid-template-columns: minmax(0, 1fr);
        }

        .auction-create-page .auction-fees-field > .auction-fee-tax,
        .auction-create-page .auction-fees-field > .auction-fee-commission,
        .auction-create-page .auction-fees-field > .auction-fee-service,
        .auction-create-page .auction-fees-field > .auction-fees-payer {
            grid-column: 1 / -1;
        }

        .auction-create-page .auction-terms-field textarea.form-control {
            min-height: 160px;
        }
    }

    @media (max-width: 375px) {
        .auction-create-page .auction-video-options {
            grid-template-columns: 1fr;
        }
    }

    /* UNIVERSAL AUCTION RESPONSIVE FIX */
    @media (max-width: 991.98px) {

        .auction-create-page {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow-x: hidden;
        }

        .auction-create-page .auction-create-card,
        .auction-create-page .auction-create-card > .card-body,
        .auction-create-page #kt_form_1 {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        /*
         * On tablet / mobile every main form section becomes one column.
         * This avoids Bootstrap widths fighting with the custom grid.
         */
        .auction-create-page .auction-create-form {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) !important;
            width: 100% !important;
            max-width: 100%;
            min-width: 0;
            margin-inline: 0 !important;
            gap: 0 !important;
        }

        .auction-create-page .auction-create-form > * {
            grid-column: 1 / -1 !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            min-inline-size: 0 !important;
            box-sizing: border-box;
        }

        /*
         * Fees section must be its own full-width grid.
         * No field is allowed to become a tiny Bootstrap column.
         */
        .auction-create-page .auction-fees-field {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            gap: .75rem !important;
        }

        .auction-create-page .auction-fees-field > .auction-fees-heading,
        .auction-create-page .auction-fees-field > .auction-fees-hint,
        .auction-create-page .auction-fees-field > .form-group {
            grid-column: 1 / -1 !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }

        /*
         * Desktop used a fixed two-line label height.
         * Mobile labels should take their natural height.
         */
        .auction-create-page .auction-fees-field .md-label-2-lines {
            min-height: 0 !important;
            height: auto !important;
            white-space: normal !important;
            overflow-wrap: normal;
            word-break: normal;
        }

        /*
         * Bootstrap / Select2 / native date-time controls.
         */
        .auction-create-page .form-control,
        .auction-create-page select,
        .auction-create-page textarea,
        .auction-create-page input,
        .auction-create-page .select2-container {
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box;
        }

        .auction-create-page .select2-container {
            width: 100% !important;
        }

        .auction-create-page .select2-container--default
        .select2-selection--single {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box;
        }

        .auction-create-page input[type="date"],
        .auction-create-page input[type="time"],
        .auction-create-page input[type="number"],
        .auction-create-page input[type="text"] {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }

        /*
         * Banner upload.
         */
        .auction-create-page .auction-banner-field,
        .auction-create-page .auction-upload-zone {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box;
        }

        .auction-create-page .auction-upload-zone {
            min-height: 170px;
        }

        /*
         * Video type.
         */
        .auction-create-page .auction-video-field {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }

        .auction-create-page .auction-video-options {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .65rem;
        }

        .auction-create-page .auction-video-option {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        .auction-create-page .auction-video-option span {
            min-width: 0;
        }

        .auction-create-page .auction-video-option strong,
        .auction-create-page .auction-video-option small {
            white-space: normal;
            word-break: normal;
            overflow-wrap: normal;
        }

        /*
         * Terms.
         */
        .auction-create-page .auction-terms-field,
        .auction-create-page .auction-terms-field textarea {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }

        .auction-create-page .auction-terms-field textarea {
            min-height: 150px;
            resize: vertical;
        }

        .auction-create-page .auction-actions-wrap {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }
    }


    @media (max-width: 575.98px) {

        .auction-create-page .auction-create-card > .card-body {
            padding: .75rem !important;
        }

        /*
         * 16px prevents automatic Safari/iPhone zoom when focusing inputs.
         */
        .auction-create-page .form-control,
        .auction-create-page select,
        .auction-create-page textarea,
        .auction-create-page input,
        .auction-create-page .select2-selection__rendered {
            font-size: 16px !important;
        }

        .auction-create-page .auction-section-heading {
            font-size: 1.05rem;
            line-height: 1.45;
            white-space: normal;
        }

        .auction-create-page .auction-fees-hint {
            line-height: 1.65;
        }

        /*
         * One video type card per row is safer for
         * iPhone SE / Samsung small screens / 360-430 widths.
         */
        .auction-create-page .auction-video-options {
            grid-template-columns: minmax(0, 1fr) !important;
        }

        .auction-create-page .auction-video-option {
            min-height: 72px;
            padding: .8rem;
        }

        .auction-create-page .auction-actions,
        .auction-create-page .auction-actions-wrap {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: .6rem;
        }

        .auction-create-page .auction-actions .btn,
        .auction-create-page .auction-actions-wrap .btn {
            width: 100% !important;
            max-width: 100%;
            min-width: 0 !important;
        }

        .auction-create-page .auction-page-heading {
            gap: .6rem !important;
        }

        .auction-create-page .auction-page-heading h3 {
            max-width: 100%;
            white-space: normal !important;
            line-height: 1.35;
        }
    }


    @media (max-width: 359.98px) {

        .auction-create-page .auction-create-card > .card-body {
            padding: .6rem !important;
        }

        .auction-create-page .auction-section-heading {
            font-size: 1rem;
        }

        .auction-create-page .auction-page-icon {
            width: 38px;
            height: 38px;
            min-width: 38px;
        }
    }

</style>

<div class="auction-create-page">
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card auction-create-card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3 auction-page-heading">
                    <div>
                        <h3 class="page-title mb-1">{{ TranslationHelper::translate('New Auction') }}</h3>
                        <ul class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.videos.index') }}">{{ TranslationHelper::translate('Auction') }}</a></li>
                            <li class="breadcrumb-item active">{{ TranslationHelper::translate('New Auction') }}</li>
                        </ul>
                    </div>
                    <span class="md-page-icon auction-page-icon"><i class="fa-solid fa-gavel"></i></span>
                </div>
                <hr>

                {!! Form::open(['route' => 'admin.videos.store', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
                    @include('dashboard.pages.videos._form')
                {!! Form::close() !!}
            </div>
        </div>
</div>
</div>
</div>

@endsection

@section('scripts_lib')
<script>
    // A stale mobile menu state can remain after navigating between dashboard
    // pages. Start this form with the sidebar closed without disabling the
    // existing hamburger toggle.
    document.addEventListener('DOMContentLoaded', function () {
        if (window.matchMedia('(max-width: 991.98px)').matches) {
            document.body.classList.remove('slide-nav');
            document.documentElement.classList.remove('menu-opened');
            document.querySelector('.sidebar-overlay')?.classList.remove('opened');
        }
    });
</script>
@endsection
