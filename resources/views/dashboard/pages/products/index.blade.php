@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Auction Products') }} @endsection

@push('css')
<!--begin::Page Vendor Stylesheets(used by this page)-->
<link href="{{asset('dashboard/plugins/datatables/datatables.min.css')}}" rel="stylesheet" type="text/css"/>
<!--end::Page Vendor Stylesheets-->
@endpush


@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{TranslationHelper::translate('Auctions') }}
{{--                @if(Auth::guard('admin')->user()->can('add video'))--}}
                    <a href='{{ route('admin.products.create',request()->id) }}' class='btn btn-primary float-end'><i class="fas fa-plus"></i> {{ TranslationHelper::translate('Add Product') }}</a>
{{--                @endif--}}
                @if($canTransferItems ?? false)
                    <button type="button" class="btn btn-secondary float-end me-2" data-bs-toggle="modal" data-bs-target="#transferUnsoldItemsModal">
                        <i class="fa-solid fa-right-left"></i>
                        {{ TranslationHelper::translate('transfer_unsold_items') }}
                    </button>
                @endif

            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.videos.index')}}">{{ TranslationHelper::translate('Auction') }}</a></li>

                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Auction Products') }}</li>
            </ul>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">

        <!--begin::Table-->
        <div class="table-responsive">
            <table id="data-table" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        {{-- <th>{{ TranslationHelper::translate('Title') }}</th> --}}
                        <th>{{ TranslationHelper::translate('Title ar') }}</th>
                        {{-- <th>{{ TranslationHelper::translate('Category') }}</th> --}}
                        <th>{{ TranslationHelper::translate('Age') }}</th>
                        <th>{{ TranslationHelper::translate('Status') }}</th>
                        {{-- <th>{{ TranslationHelper::translate('Star price') }}</th> --}}
                        <th>{{ TranslationHelper::translate('End price') }}</th>
                        <th>{{ TranslationHelper::translate('Buyer') }}</th>
                        <th>{{ TranslationHelper::translate('seller') }}</th>
                        <th>{{ TranslationHelper::translate('shipping address') }}</th>
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

@if($canTransferItems ?? false)
<style>
    /* Keep the transfer form usable on short screens: only the modal body scrolls. */
    #transferUnsoldItemsModal .modal-dialog {
        height: calc(100vh - 1rem);
        margin-top: .5rem;
        margin-bottom: .5rem;
    }

    #transferUnsoldItemsModal .modal-content {
        height: 100%;
        max-height: 100%;
        display: flex;
        overflow: hidden;
    }

    #transferUnsoldItemsModal .modal-content > form {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
        height: 100%;
    }

    #transferUnsoldItemsModal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: scroll !important;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 575.98px) {
        #transferUnsoldItemsModal .modal-dialog {
            height: calc(100vh - .5rem);
            margin-top: .25rem;
            margin-bottom: .25rem;
        }

        #transferUnsoldItemsModal .modal-content {
            max-height: 100%;
        }
    }
</style>
<div class="modal fade" id="transferUnsoldItemsModal" tabindex="-1" aria-labelledby="transferUnsoldItemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            {!! Form::open(['route' => ['admin.products.transfer-unsold', request()->id], 'method' => 'POST']) !!}
                <div class="modal-header">
                    <h5 class="modal-title" id="transferUnsoldItemsModalLabel">
                        {{ TranslationHelper::translate('transfer_unsold_items') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ TranslationHelper::translate('close') }}"></button>
                </div>
                <div class="modal-body">
                    @if($transferableItems->isEmpty())
                        <p class="text-muted mb-0">{{ TranslationHelper::translate('no_transferable_unsold_items') }}</p>
                    @else
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ TranslationHelper::translate('choose_unsold_items') }}</label>
                            <div class="md-transfer-items-list">
                                @foreach($transferableItems as $transferItem)
                                    <label class="md-transfer-item-option">
                                        <input type="checkbox" name="item_ids[]" value="{{ $transferItem->id }}">
                                        <span>
                                            <strong>{{ $transferItem->title_ar ?? $transferItem->title ?? '#' . $transferItem->id }}</strong>
                                            <small>
                                                {{ TranslationHelper::translate('quantity') }}: {{ $transferItem->quantity ?? 1 }}
                                                @if($transferItem->seller)
                                                    - {{ TranslationHelper::translate('seller') }}: {{ $transferItem->seller->name }}
                                                @endif
                                            </small>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ TranslationHelper::translate('transfer_destination') }}</label>
                            <div class="d-flex flex-wrap gap-3">
                                <label class="form-check">
                                    <input class="form-check-input md-transfer-mode" type="radio" name="transfer_mode" value="existing" checked>
                                    <span class="form-check-label">{{ TranslationHelper::translate('existing_auction') }}</span>
                                </label>
                                <label class="form-check">
                                    <input class="form-check-input md-transfer-mode" type="radio" name="transfer_mode" value="new">
                                    <span class="form-check-label">{{ TranslationHelper::translate('create_new_auction_and_transfer') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="md-transfer-existing-fields">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ TranslationHelper::translate('target_auction') }}</label>
                                <select name="target_auction_id" class="form-control">
                                    <option value="">{{ TranslationHelper::translate('select_auction') }}</option>
                                    @foreach($targetAuctions as $targetAuction)
                                        <option value="{{ $targetAuction->id }}">
                                            #{{ $targetAuction->id }} - {{ $targetAuction->title_ar ?? $targetAuction->title }}
                                            ({{ TranslationHelper::translate($targetAuction->type ?? 'live') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="md-transfer-new-fields d-none">
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('auction_type') }}</label>
                                    <select name="new_auction_type" class="form-control">
                                        <option value="live">{{ TranslationHelper::translate('live') }}</option>
                                        <option value="recorded">{{ TranslationHelper::translate('recorded') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('title_ar') }}</label>
                                    <input type="text" name="new_title_ar" class="form-control" value="{{ old('new_title_ar', ($live->title_ar ?? '') . ' - ' . TranslationHelper::translate('transferred_items')) }}">
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('date_start') }}</label>
                                    <input type="date" name="new_date_start_at" class="form-control" value="{{ old('new_date_start_at', optional(now())->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('date_end') }}</label>
                                    <input type="date" name="new_date_end_at" class="form-control" value="{{ old('new_date_end_at', optional(now())->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('time_start') }}</label>
                                    <input type="time" name="new_time_start_at" class="form-control" value="{{ old('new_time_start_at', '10:00') }}">
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('time_end') }}</label>
                                    <input type="time" name="new_time_end_at" class="form-control" value="{{ old('new_time_end_at', '12:00') }}">
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('start_price') }}</label>
                                    <input type="number" step="0.01" min="0" name="new_start_price" class="form-control" value="{{ old('new_start_price', $live->start_price ?? 0) }}">
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('city') }}</label>
                                    <select name="new_city_id" class="form-control">
                                        @php($riyadhCity = $cities->first(fn ($city) => mb_strtolower(trim((string) $city->name)) === 'الرياض'))
                                        <option value="{{ $riyadhCity?->id ?? '' }}" selected>
                                            {{ $riyadhCity->name ?? 'الرياض' }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('information') }}</label>
                                    <textarea name="new_information_ar" class="form-control" rows="2">{{ old('new_information_ar', $live->information_ar ?? $live->information ?? '') }}</textarea>
                                </div>
                                <div class="col-12 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('terms_conditions') }}</label>
                                    <textarea name="new_terms_conditions_ar" class="form-control" rows="2">{{ old('new_terms_conditions_ar', $live->terms_conditions_ar ?? $live->terms_conditions ?? '') }}</textarea>
                                </div>
                                <div class="col-12 mt-2 mb-2">
                                    <h6 class="fw-bold mb-1">{{ TranslationHelper::translate('auction_fees') }}</h6>
                                    <small class="text-muted">{{ TranslationHelper::translate('optional') }}</small>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('tax_amount') }}</label>
                                    <input type="number" step="0.01" min="0" name="new_tax_amount" class="form-control" value="{{ old('new_tax_amount', $live->tax_amount ?? '') }}">
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('commission_amount') }}</label>
                                    <input type="number" step="0.01" min="0" name="new_commission_amount" class="form-control" value="{{ old('new_commission_amount', $live->commission_amount ?? '') }}">
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('service_fee') }}</label>
                                    <input type="number" step="0.01" min="0" name="new_service_fee" class="form-control" value="{{ old('new_service_fee', $live->service_fee ?? '') }}">
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="form-label">{{ TranslationHelper::translate('commission_payer') }}</label>
                                    <select name="new_commission_payer" class="form-control">
                                        <option value="">{{ TranslationHelper::translate('commission_payer') }}</option>
                                        <option value="buyer" {{ old('new_commission_payer', $live->commission_payer ?? '') === 'buyer' ? 'selected' : '' }}>{{ TranslationHelper::translate('buyer') }}</option>
                                        <option value="seller" {{ old('new_commission_payer', $live->commission_payer ?? '') === 'seller' ? 'selected' : '' }}>{{ TranslationHelper::translate('seller') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ TranslationHelper::translate('cancel') }}</button>
                    <button type="submit" class="btn btn-primary" @if($transferableItems->isEmpty()) disabled @endif>
                        {{ TranslationHelper::translate('transfer') }}
                    </button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endif

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
            url : "{!! route('admin.products.getData',request()->id) !!}",
            data: {},
            type: "POST",
            dataType: "JSON"
        },
        order: [[0, 'desc']], // Sort by 'id' column in descending order (change to 'asc' for ascending)

        columns: [
            {data: 'id', 'searchable': true, 'orderable': true, 'exportable': true, 'printable': true},
            // {data: 'title', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'title_ar', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {{-- {data: 'category', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true}, --}}
            {data: 'ageData', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {data: 'status', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true},
            {{-- {data: 'start_price', 'searchable': false, 'orderable': true, 'exportable': true, 'printable': true}, --}}

            {data: 'finished_price', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'buyer', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'seller', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'shipping_address', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false},
            {data: 'action', 'searchable': false, 'orderable': false, 'exportable': false, 'printable': false}

        ],
        language: {
            "search": "{{ TranslationHelper::translate('search') }}",
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
        dom: '<"d-flex justify-content-between"<l><f>>rt<"d-flex justify-content-between"<"d-flex align-items-center"<><i>><p>>'
    });

    $('.md-transfer-mode').on('change', function () {
        const isNew = $('input[name="transfer_mode"]:checked').val() === 'new';
        $('.md-transfer-existing-fields').toggleClass('d-none', isNew);
        $('.md-transfer-new-fields').toggleClass('d-none', !isNew);
    });
</script>
@endsection
