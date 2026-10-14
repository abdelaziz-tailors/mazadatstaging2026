@extends('dashboard.layouts.app')

@section('title', $video->title_ar ?: $video->title)

@push('css')
<style>
.auction-show-page {
    direction: rtl;
    max-width: 1400px;
    margin: 0 auto;
    padding-bottom: 24px;
}

.auction-show-page .auction-page-title {
    font-size: 25px;
    font-weight: 700;
    color: #203c2c;
    margin-bottom: 8px;
}

.auction-show-page .auction-owner-card,
.auction-show-page .auction-details-card {
    background: #fff;
    border: 1px solid #e7ece9;
    border-radius: 16px;
    box-shadow: 0 4px 22px rgba(0, 0, 0, .035);
}

.auction-show-page .auction-owner-card {
    padding: 24px;
    margin-bottom: 22px;
}

.auction-show-page .auction-owner-layout {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 22px;
}

.auction-show-page .auction-owner-image {
    width: 100px;
    height: 100px;
    min-width: 100px;
    border-radius: 14px;
    object-fit: cover;
    border: 1px solid #e7ece9;
    display: block;
}

.auction-show-page .auction-owner-name {
    font-size: 21px;
    font-weight: 700;
    color: #243d2e;
    margin: 0 0 12px;
}

.auction-show-page .auction-owner-info {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 25px;
    color: #64746b;
    overflow-wrap: anywhere;
}

.auction-show-page .auction-owner-info i {
    color: #3d5f45;
    margin-left: 7px;
}

.auction-show-page .auction-details-card {
    padding: 26px;
}

.auction-show-page .auction-section-title {
    font-size: 19px;
    font-weight: 700;
    color: #203c2c;
    padding-bottom: 16px;
    margin-bottom: 22px;
    border-bottom: 1px solid #edf0ee;
}

.auction-show-page .auction-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.auction-show-page .auction-info-item {
    min-width: 0;
    padding: 18px;
    border: 1px solid #edf0ee;
    background: #fbfcfb;
    border-radius: 12px;
}

.auction-show-page .auction-info-label {
    font-size: 13px;
    color: #849087;
    margin-bottom: 10px;
}

.auction-show-page .auction-info-value {
    font-size: 16px;
    font-weight: 600;
    color: #263c2e;
    overflow-wrap: anywhere;
}

.auction-show-page .auction-status {
    display: inline-flex;
    border-radius: 20px;
    padding: 5px 13px;
    font-size: 13px;
}

.auction-show-page .auction-status-ended {
    background: #f0f1f2;
    color: #59616a;
}

.auction-show-page .auction-status-live {
    background: #e5f5e9;
    color: #267444;
}

.auction-show-page .auction-status-other {
    background: #fff4dc;
    color: #94620b;
}

@media (max-width: 767px) {
    .auction-show-page .auction-owner-card,
    .auction-show-page .auction-details-card {
        padding: 18px;
    }

    .auction-show-page .auction-info-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .auction-show-page .auction-owner-image {
        width: 78px;
        height: 78px;
        min-width: 78px;
    }

    .auction-show-page .auction-owner-name {
        font-size: 18px;
    }

    .auction-show-page .auction-owner-info {
        display: block;
    }

    .auction-show-page .auction-owner-info div {
        margin-bottom: 8px;
    }
}
</style>
@endpush

@section('content')

@php
    $owner = $video->user_Video ?: $video->partner;

    $ownerName = $owner->name ?? '-';
    $ownerPhone = $owner->phone ?? null;
    $ownerEmail = $owner->email ?? null;
    $ownerImage = $owner->image ?? null;

    $ownerImageExists = $ownerImage
        && Storage::disk('public')->exists($ownerImage);

    $ownerImageUrl = $ownerImageExists
        ? Storage::disk('public')->url($ownerImage)
        : asset('images/logo.png');

    $typeLabels = [
        'live' => 'مباشر',
        'recorded' => 'مسجل',
        'video' => 'فيديو',
        'photos' => 'صور',
    ];

    $auctionType = $typeLabels[$video->type]
        ?? ($video->type ?: '-');

    $statusLabels = [
        'start' => 'جارٍ الآن',
        'end' => 'منتهي',
    ];

    $auctionStatus = $statusLabels[$video->status]
        ?? ($video->status ?: 'لم تبدأ');

    $statusClass = $video->status === 'start'
        ? 'auction-status-live'
        : ($video->status === 'end'
            ? 'auction-status-ended'
            : 'auction-status-other');

    $formatAuctionDate = function ($date, $time) {
        if (!$date) {
            return '-';
        }

        $timestamp = strtotime((string) $date);

        if ($timestamp === false) {
            return '-';
        }

        $result = date('Y-m-d', $timestamp);

        if ($time) {
            $timeStamp = strtotime((string) $time);

            if ($timeStamp !== false) {
                $period = date('A', $timeStamp) === 'AM' ? 'ص' : 'م';

                $result .= ' — '
                    . date('h:i', $timeStamp)
                    . ' '
                    . $period;
            }
        }

        return $result;
    };

    $startAt = $formatAuctionDate(
        $video->date_start_at,
        $video->time_start_at
    );

    $endAt = $formatAuctionDate(
        $video->date_end_at,
        $video->time_end_at
    );
@endphp

<div class="auction-show-page">

    <div class="page-header">
        <div class="row">
            <div class="col">
                <h3 class="auction-page-title">
                    {{ $video->title_ar ?: $video->title }}
                </h3>

                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard.index') }}">
                            لوحة التحكم
                        </a>
                    </li>

                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.videos.index') }}">
                            المزادات
                        </a>
                    </li>

                    <li class="breadcrumb-item active">
                        تفاصيل المزاد
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Auction owner --}}
    <div class="auction-owner-card">
        <div class="auction-owner-layout">

            <img
                src="{{ $ownerImageUrl }}"
                alt="{{ $ownerName }}"
                class="auction-owner-image"
                style="{{ $ownerImageExists ? '' : 'object-fit: contain; padding: 8px;' }}"
            >

            <div>
                <div class="auction-owner-name">
                    {{ $ownerName }}
                </div>

                <div class="auction-owner-info">

                    @if($ownerPhone)
                        <div>
                            <i class="fa fa-phone"></i>
                            <span dir="ltr">{{ $ownerPhone }}</span>
                        </div>
                    @endif

                    @if($ownerEmail)
                        <div>
                            <i class="fa fa-envelope"></i>
                            {{ $ownerEmail }}
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

    {{-- Auction details --}}
    <div class="auction-details-card">

        <h4 class="auction-section-title">
            تفاصيل المزاد
        </h4>

        <div class="auction-info-grid">

            <div class="auction-info-item">
                <div class="auction-info-label">
                    عنوان المزاد
                </div>

                <div class="auction-info-value">
                    {{ $video->title_ar ?: $video->title }}
                </div>
            </div>

            <div class="auction-info-item">
                <div class="auction-info-label">
                    نوع المزاد
                </div>

                <div class="auction-info-value">
                    {{ $auctionType }}
                </div>
            </div>

            <div class="auction-info-item">
                <div class="auction-info-label">
                    حالة المزاد
                </div>

                <div class="auction-info-value">
                    <span class="auction-status {{ $statusClass }}">
                        {{ $auctionStatus }}
                    </span>
                </div>
            </div>

            <div class="auction-info-item">
                <div class="auction-info-label">
                    عدد القطع
                </div>

                <div class="auction-info-value">
                    {{ $video->video_items()->count() }}
                </div>
            </div>

            <div class="auction-info-item">
                <div class="auction-info-label">
                    سعر بدء المزاد
                </div>

                <div class="auction-info-value">
                    {{
                        $video->start_price !== null
                            ? number_format((float) $video->start_price, 2)
                            : '-'
                    }}
                    ر.س
                </div>
            </div>

            <div class="auction-info-item">
                <div class="auction-info-label">
                    تاريخ ووقت بداية المزاد
                </div>

                <div class="auction-info-value">
                    {{ $startAt }}
                </div>
            </div>

            <div class="auction-info-item">
                <div class="auction-info-label">
                    تاريخ ووقت نهاية المزاد
                </div>

                <div class="auction-info-value">
                    {{ $endAt }}
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
