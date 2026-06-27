@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Edit Order') }} {{ $order->order_number }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ TranslationHelper::translate('Edit Order') }} {{ $order->order_number }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">{{ TranslationHelper::translate('Orders') }}</a></li>
                <li class="breadcrumb-item active">{{ $order->order_number }}</li>
            </ul>
        </div>
    </div>
</div>

{!! Form::model($order, ['route' => ['admin.orders.update', $order->id], 'method' => 'PUT', 'id' => 'kt_form_1']) !!}
    @include('dashboard.pages.orders._form')
{!! Form::close() !!}

<div class="row mt-3">
    <div class="col-12 d-flex">
        <div class="card flex-fill">
            <div class="card-body">
                @include('dashboard.pages.orders._piece_services', ['order' => $order, 'itemServices' => $itemServices ?? collect()])
            </div>
        </div>
    </div>
</div>
@endsection
