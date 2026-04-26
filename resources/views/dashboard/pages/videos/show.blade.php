@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate($video->title) }} @endsection

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
                {{ $video->title }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.videos.index')}}">{{ TranslationHelper::translate('Auctions') }}</a></li>
                <li class="breadcrumb-item active">{{$video->title}}</li>
            </ul>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="profile-header">
                    <div class="row align-items-center">
                        <div class="col-auto profile-image">
                            <a href="#">
                                <img class="rounded-circle" alt="User Image" src="{{ (Storage::disk('public')->exists($video->user_Video->image)) ? Storage::disk('public')->url($video->user_Video->image) : asset('images/logo.png')}}">
                            </a>
                        </div>
                        <div class="col ml-md-n2 profile-user-info">
                            <h4 class="user-name mb-0">{{$video->user_Video->name}}</h4>
                            <div class="user-Location"><i class="fa fa-phone"></i> {{$video->user_Video->phone}}</div>
                            <div class="user-Location"><i class="fa fa-envelope"></i> {{$video->user_Video->email}}</div>
                        </div>
                    </div>
                </div>
                <div class="profile-menu">
                </div>
                <div class="tab-content profile-tab-cont">

                    <div class="tab-pane fade show active" id="per_details_tab">

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title d-flex justify-content-between">
                                            <span>{{ TranslationHelper::translate('Auctions Details') }} </span>
                                        </h5>
                                        <div class="row">
                                            <p class="col-sm-2 text-muted text-sm-end mb-0 mb-sm-3">{{ TranslationHelper::translate('Auctions Title') }}</p>
                                            <p class="col-sm-10">{{$video->title}}</p>
                                        </div>
                                        <div class="row">
                                            <p class="col-sm-2 text-muted text-sm-end mb-0 mb-sm-3">{{ TranslationHelper::translate('Auctions Type') }} </p>
                                            <p class="col-sm-10">{{$video->categoryData->name??'-'}}</p>
                                        </div>
                                        <div class="row">
                                            <p class="col-sm-2 text-muted text-sm-end mb-0 mb-sm-3">{{ TranslationHelper::translate('Auctions Quantity') }} </p>
                                            <p class="col-sm-10">{{$video->quantity}}</p>
                                        </div>
                                        <div class="row">
                                            <p class="col-sm-2 text-muted text-sm-end mb-0 mb-sm-3">{{ TranslationHelper::translate('Auctions start price') }} </p>
                                            <p class="col-sm-10">{{$video->start_price}}</p>
                                        </div>
                                        <div class="row">
                                            <p class="col-sm-2 text-muted text-sm-end mb-0 mb-sm-3">{{ TranslationHelper::translate('Auction fees') }} </p>
                                            <p class="col-sm-10">
                                                {{ TranslationHelper::translate('Tax') }}: {{ $video->tax_amount ?? '—' }},
                                                {{ TranslationHelper::translate('commission_amount') }}: {{ $video->commission_amount ?? '—' }},
                                                {{ TranslationHelper::translate('commission_payer') }}: {{ $video->commission_payer ?? '—' }},
                                                {{ TranslationHelper::translate('service_fee') }}: {{ $video->service_fee ?? '—' }}
                                            </p>
                                        </div>
                                        <div class="row">
                                            <p class="col-sm-2 text-muted text-sm-end mb-0 mb-sm-3">{{ TranslationHelper::translate('Auctions time') }} </p>
                                            <p class="col-sm-10">{{$video->auction_time}}</p>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>

                    </div>



                </div>
            </div>
        </div>
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->

@endsection

