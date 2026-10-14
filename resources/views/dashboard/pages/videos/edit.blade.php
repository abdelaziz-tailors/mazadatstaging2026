@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Edit Auction') }} @endsection


@push('css')
<style>
/* Mazadat auction edit responsive v1 */

.md-auction-edit-page,
.md-auction-edit-page > .col-12,
.md-auction-edit-page .card,
.md-auction-edit-page .card-body,
.md-auction-edit-page .auction-create-form {
    min-width: 0;
    max-width: 100%;
}

.md-auction-edit-page .card-body {
    padding: clamp(14px, 2vw, 28px);
}

.md-auction-edit-page .auction-create-form > [class*="col-"] {
    min-width: 0;
}

.md-auction-edit-page .form-control,
.md-auction-edit-page .form-select,
.md-auction-edit-page select,
.md-auction-edit-page textarea {
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.md-auction-edit-page .select2-container {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0;
}

.md-auction-edit-page .select2-selection {
    min-height: 40px;
}

/* Fees: use the available width instead of three narrow columns. */
.md-auction-edit-page .auction-fees-field {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    align-items: start;
    gap: 16px;
    width: 100%;
}

.md-auction-edit-page .auction-fees-heading,
.md-auction-edit-page .auction-fees-hint {
    grid-column: 1 / -1;
    margin-bottom: 0 !important;
}

.md-auction-edit-page .auction-fees-field > .auction-fee-commission,
.md-auction-edit-page .auction-fees-field > .auction-fee-service,
.md-auction-edit-page .auction-fees-field > .auction-fees-payer {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0;
    margin: 0 !important;
}

.md-auction-edit-page .auction-fees-field .form-control,
.md-auction-edit-page .auction-fees-field select {
    width: 100%;
}

/* Banner and existing images. */
.md-auction-edit-page .auction-upload-zone {
    display: flex;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-wrap: anywhere;
}

.md-auction-edit-page .auction-upload-zone input[type="file"] {
    max-width: 100%;
}

.md-auction-edit-page .auction-banner-preview img {
    width: 90px;
    height: 90px;
    max-width: 100%;
    object-fit: cover;
    border-radius: 10px;
}

/* Video type choices. */
.md-auction-edit-page .auction-video-options {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.md-auction-edit-page .auction-video-option {
    flex: 1 1 180px;
    min-width: 0;
}

/* Form actions. */
.md-auction-edit-page .auction-actions-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

/* Tablets. */
@media (min-width: 768px) and (max-width: 991.98px) {
    .md-auction-edit-page .auction-fees-field {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .md-auction-edit-page .auction-fees-payer {
        grid-column: 1 / -1;
    }
}

/* Phones. */
@media (max-width: 767.98px) {
    .md-auction-edit-page .card-body {
        padding: 14px;
    }

    .md-auction-edit-page .card-body > .d-flex:first-child {
        flex-wrap: wrap;
        gap: 12px !important;
    }

    .md-auction-edit-page .auction-create-form {
        margin-left: 0;
        margin-right: 0;
    }

    .md-auction-edit-page .auction-create-form >
    .auction-details-field,
    .md-auction-edit-page .auction-create-form >
    .auction-schedule-field,
    .md-auction-edit-page .auction-create-form >
    .auction-banner-field,
    .md-auction-edit-page .auction-create-form >
    .auction-video-field,
    .md-auction-edit-page #partner-select-field {
        flex: 0 0 100% !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .md-auction-edit-page .auction-fees-field {
        grid-template-columns: minmax(0, 1fr);
        gap: 14px;
    }

    .md-auction-edit-page .auction-fees-field >
    .auction-fee-commission,
    .md-auction-edit-page .auction-fees-field >
    .auction-fee-service,
    .md-auction-edit-page .auction-fees-field >
    .auction-fees-payer {
        grid-column: 1;
    }

    .md-auction-edit-page .auction-video-options {
        flex-direction: column;
    }

    .md-auction-edit-page .auction-video-option {
        flex: 0 0 auto;
        width: 100%;
    }

    .md-auction-edit-page .auction-actions-wrap {
        flex-direction: column;
        align-items: stretch;
    }

    .md-auction-edit-page .auction-actions-wrap .btn {
        width: 100%;
        margin: 0 !important;
    }
}
</style>
@endpush


@section('css_lib')
<style>
/* Mazadat edit mobile sidebar fix v1 */

@media (max-width: 991.98px) {

    /*
     * Keep the RTL sidebar completely outside
     * the viewport while the mobile menu is closed.
     */
    html[lang="ar"] .sidebar {
        position: fixed !important;
        left: auto !important;
        right: 0 !important;

        width: min(280px, 85vw) !important;
        margin-left: 0 !important;
        margin-right: 0 !important;

        transform: translate3d(105%, 0, 0) !important;
        transition: transform .22s ease !important;

        z-index: 1051;
    }

    /*
     * script.js adds slide-nav when the
     * mobile menu button is clicked.
     */
    html[lang="ar"] .slide-nav .sidebar {
        transform: translate3d(0, 0, 0) !important;
    }

    /*
     * The page itself must always use
     * the available mobile screen width.
     */
    html[lang="ar"] .page-wrapper {
        margin-left: 0 !important;
        margin-right: 0 !important;

        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
    }

    html[lang="ar"] .page-wrapper > .content {
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .md-auction-edit-page,
    .md-auction-edit-page > .col-12 {
        min-width: 0;
        max-width: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    html[lang="ar"] .sidebar {
        transition: none !important;
    }
}

/* Mazadat edit mobile form final fix v2 */

@media (max-width: 767.98px) {

    .md-auction-edit-page,
    .md-auction-edit-page > .col-12,
    .md-auction-edit-page .card,
    .md-auction-edit-page .card-body,
    .md-auction-edit-page #kt_form_1 {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
    }

    .md-auction-edit-page .auction-create-form {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
    }

    .md-auction-edit-page .auction-create-form >
    [class*="col-"]:not(.auction-banner-preview) {
        flex: 0 0 100% !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    /* Banner: prevent the upload control squeezing its text */

    .md-auction-edit-page .auction-upload-zone {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        height: auto !important;
        padding: 18px 12px !important;
        box-sizing: border-box;
        overflow: hidden;
        text-align: center;
    }

    .md-auction-edit-page .auction-upload-zone > span {
        display: block !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        white-space: normal !important;
        overflow-wrap: break-word;
        line-height: 1.7;
    }

    .md-auction-edit-page .auction-upload-zone > span > * {
        display: block;
        width: 100%;
        max-width: 100%;
        margin-bottom: 6px;
        white-space: normal !important;
    }

    .md-auction-edit-page .auction-upload-zone input[type="file"] {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-top: 14px;
        font-size: 12px;
        box-sizing: border-box;
        white-space: normal;
    }

    .md-auction-edit-page .auction-banner-preview {
        flex: 0 0 auto !important;
        width: auto !important;
        max-width: 100% !important;
    }

    /* Fees: one complete field per row */

    .md-auction-edit-page .auction-fees-field {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) !important;
        width: 100% !important;
        max-width: 100% !important;
        gap: 14px !important;
    }

    .md-auction-edit-page .auction-fees-field >
    .auction-fee-commission,
    .md-auction-edit-page .auction-fees-field >
    .auction-fee-service,
    .md-auction-edit-page .auction-fees-field >
    .auction-fees-payer {
        grid-column: 1 !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
    }

    /* Video type: separate full-width choices */

    .md-auction-edit-page .auction-video-options {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) !important;
        gap: 12px !important;
        width: 100% !important;
        min-width: 0 !important;
    }

    .md-auction-edit-page .auction-video-option {
        display: flex !important;
        align-items: flex-start !important;
        gap: 10px !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        white-space: normal !important;
        box-sizing: border-box;
    }

    .md-auction-edit-page .auction-video-option input[type="radio"] {
        flex: 0 0 auto !important;
    }

    .md-auction-edit-page .auction-video-option > span {
        display: block !important;
        flex: 1 1 0 !important;
        min-width: 0 !important;
        white-space: normal !important;
    }

    .md-auction-edit-page .auction-video-option strong,
    .md-auction-edit-page .auction-video-option small {
        display: block !important;
        white-space: normal !important;
        overflow-wrap: break-word;
        line-height: 1.6;
    }

    /* Keep Select2 and text fields inside the available width */

    .md-auction-edit-page .select2-container,
    .md-auction-edit-page .form-control,
    .md-auction-edit-page textarea {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box;
    }
}


/* Mazadat video type radio cards v1 */

.md-auction-edit-page .auction-video-field {
    min-width: 0;
}

.md-auction-edit-page .auction-video-options {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 12px !important;
    width: 100% !important;
    margin-top: 10px;
}

.md-auction-edit-page .auction-video-option {
    display: flex !important;
    flex-direction: row !important;
    align-items: flex-start !important;
    justify-content: flex-start !important;
    gap: 10px !important;
    min-width: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    min-height: 82px;
    margin: 0 !important;
    padding: 14px !important;
    border: 1px solid #dce6df;
    border-radius: 12px;
    background: #fff;
    box-sizing: border-box;
    direction: rtl;
    text-align: right;
    cursor: pointer;
    white-space: normal !important;
    transition: border-color .2s, background-color .2s;
}

.md-auction-edit-page .auction-video-option:hover {
    border-color: #3d5f45;
    background: #f8fbf8;
}

.md-auction-edit-page .auction-video-option:has(input:checked) {
    border: 2px solid #3d5f45;
    background: #edf5ef;
}

.md-auction-edit-page .auction-video-option input[type="radio"] {
    position: static !important;
    float: none !important;
    display: block;
    flex: 0 0 18px !important;
    width: 18px;
    height: 18px;
    min-width: 18px;
    margin: 3px 0 0 !important;
    accent-color: #3d5f45;
}

.md-auction-edit-page .auction-video-option > span {
    display: flex !important;
    flex: 1 1 0 !important;
    flex-direction: column !important;
    gap: 5px;
    min-width: 0 !important;
    width: auto !important;
    text-align: right;
    white-space: normal !important;
}

.md-auction-edit-page .auction-video-option strong {
    display: block !important;
    font-size: 15px;
    font-weight: 700;
    line-height: 1.5;
    color: #203c2c;
    white-space: normal !important;
}

.md-auction-edit-page .auction-video-option small {
    display: block !important;
    font-size: 12px;
    font-weight: 400;
    line-height: 1.6;
    color: #718078;
    white-space: normal !important;
    overflow-wrap: break-word;
}

/* Tablet and mobile: one full-width card per choice */

@media (max-width: 1199.98px) {
    .md-auction-edit-page .auction-video-options {
        grid-template-columns: minmax(0, 1fr) !important;
    }

    .md-auction-edit-page .auction-video-option {
        min-height: 70px;
    }
}

@media (max-width: 767.98px) {
    .md-auction-edit-page .auction-video-field {
        flex: 0 0 100% !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .md-auction-edit-page .auction-video-option {
        padding: 12px !important;
    }
}

</style>
@endsection

@section('content')
<div class="row justify-content-center md-auction-edit-page">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="page-title mb-1">{{ TranslationHelper::translate('Edit Auction') }}</h3>
                        <ul class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.videos.index') }}">{{ TranslationHelper::translate('Auction') }}</a></li>
                            <li class="breadcrumb-item active">{{ TranslationHelper::translate('Edit Auction') }}</li>
                        </ul>
                    </div>
                    <span class="md-page-icon"><i class="fa-solid fa-gavel"></i></span>
                </div>
                <hr>

                {!! Form::model($data, ['route' => ['admin.videos.update', $data->id], 'method' => 'PUT', 'files' => true, 'id' => 'kt_form_1', 'data-toggle' => 'validator']) !!}
                    @include('dashboard.pages.videos._form')
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
