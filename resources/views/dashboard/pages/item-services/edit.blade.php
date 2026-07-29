@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('edit_item_service') }} @endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-6 col-lg-8 col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="page-title mb-1">{{ TranslationHelper::translate('edit_item_service') }}</h3>
                        <ul class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.item-services.index') }}">{{ TranslationHelper::translate('item_services') }}</a></li>
                            <li class="breadcrumb-item active">{{ TranslationHelper::translate('edit') }}</li>
                        </ul>
                    </div>
                    <span class="md-page-icon"><i class="fa-solid fa-wrench"></i></span>
                </div>
                <hr>

                {!! Form::model($data, ['route' => ['admin.item-services.update', $data->id], 'method' => 'PUT']) !!}
                    @include('dashboard.pages.item-services._form', [
                        'partners' => $partners ?? collect(),
                        'showPartnerSelect' => $showPartnerSelect ?? false,
                        'selectedPartnerId' => $selectedPartnerId ?? null,
                    ])
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
