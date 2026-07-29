@extends('dashboard.layouts.app')

@php
    $names = json_decode($packages->name, true);
    $descriptions = json_decode($packages->description, true);
    $name = (is_array($names) && array_key_exists('ar', $names)) ? $names['ar'] : ($packages->name ?? '');
    $description = (is_array($descriptions) && array_key_exists('ar', $descriptions)) ? $descriptions['ar'] : null;
@endphp

@section('title') {{ $name }} @endsection

@section('content')
@include('dashboard.partials.page-header', [
    'title' => $name,
    'icon' => 'fa-solid fa-box',
    'breadcrumbs' => [
        ['label' => TranslationHelper::translate('Packages'), 'route' => route('admin.packages.index')],
        ['label' => $name],
    ],
])

<div class="card">
    <div class="card-body">
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('name') }}</p>
            <p class="col-sm-10">{{ $name ?: '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('description') }}</p>
            <p class="col-sm-10">{{ $description ?: '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('subscription_type') }}</p>
            <p class="col-sm-10">
                {{ $packages->subscription_type ? TranslationHelper::translate($packages->subscription_type == 'monthly' ? 'Monthly' : 'Annual') : '-' }}
            </p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('auctions_limit') }}</p>
            <p class="col-sm-10">{{ $packages->auctions_limit ?? '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('monthly_price') }}</p>
            <p class="col-sm-10">{{ $packages->monthly_price !== null ? number_format($packages->monthly_price, 2) : '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('annual_price') }}</p>
            <p class="col-sm-10">{{ $packages->annual_price !== null ? number_format($packages->annual_price, 2) : '-' }}</p>
        </div>
        <div class="row">
            <p class="col-sm-2 text-muted mb-3">{{ TranslationHelper::translate('Status') }}</p>
            <p class="col-sm-10">{{ $packages->is_active ? TranslationHelper::translate('Active') : TranslationHelper::translate('Inactive') }}</p>
        </div>
    </div>
</div>
@endsection
