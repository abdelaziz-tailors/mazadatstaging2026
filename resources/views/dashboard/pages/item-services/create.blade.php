@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('new_item_service') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ TranslationHelper::translate('new_item_service') }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.item-services.index') }}">{{ TranslationHelper::translate('item_services') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('new_item_service') }}</li>
            </ul>
        </div>
    </div>
</div>

{!! Form::open(['route' => 'admin.item-services.store', 'id' => 'kt_form_1']) !!}
    @include('dashboard.pages.item-services._form', [
        'partners' => $partners ?? collect(),
        'showPartnerSelect' => $showPartnerSelect ?? false,
        'selectedPartnerId' => $selectedPartnerId ?? null,
    ])
{!! Form::close() !!}
@endsection
