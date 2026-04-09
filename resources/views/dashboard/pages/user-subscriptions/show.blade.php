@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('Subscription Details') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title" >
                {{ TranslationHelper::translate('Subscription Details') }}
            </h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.user-subscriptions.index')}}">{{ TranslationHelper::translate('User Subscriptions') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('Subscription Details') }}</li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{ TranslationHelper::translate('Subscription Information') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">{{ TranslationHelper::translate('ID') }}</th>
                                <td>{{ $subscription->id }}</td>
                            </tr>
                            <tr>
                                <th>{{ TranslationHelper::translate('User') }}</th>
                                <td>{{ $subscription->user ? $subscription->user->name : '-' }}</td>
                            </tr>
                            <tr>
                                <th>{{ TranslationHelper::translate('Package') }}</th>
                                <td>
                                    @if($subscription->package)
                                        @php
                                            $packageName = json_decode($subscription->package->name, true);
                                        @endphp
                                        {{ $packageName[app()->getLocale()] ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ TranslationHelper::translate('Subscription Type') }}</th>
                                <td>
                                    @if($subscription->subscription_type)
                                        <span class="badge bg-info">
                                            {{ $subscription->subscription_type == 'monthly' ? TranslationHelper::translate('Monthly') : TranslationHelper::translate('Annual') }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ TranslationHelper::translate('Price') }}</th>
                                <td>{{ $subscription->price ? number_format($subscription->price, 2) : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">{{ TranslationHelper::translate('Auctions Limit') }}</th>
                                <td>{{ $subscription->auctions_limit ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>{{ TranslationHelper::translate('Remaining Auctions') }}</th>
                                <td>
                                    <span class="badge bg-{{ $subscription->remaining_auctions > 0 ? 'success' : 'danger' }}">
                                        {{ $subscription->remaining_auctions ?? 0 }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ TranslationHelper::translate('Expires At') }}</th>
                                <td>
                                    @if($subscription->expires_at)
                                        @php
                                            $expiresAt = \Carbon\Carbon::parse($subscription->expires_at);
                                            $isExpired = $expiresAt->isPast();
                                            $badge = $isExpired ? 'danger' : ($expiresAt->diffInDays(now()) <= 7 ? 'warning' : 'success');
                                        @endphp
                                        <span class="badge bg-{{ $badge }}">
                                            {{ $expiresAt->format('Y-m-d H:i') }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ TranslationHelper::translate('Approval Status') }}</th>
                                <td>
                                    @php
                                        $status = $subscription->status ?? 'pending';
                                        $statusBadge = match($status) {
                                            'approved' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger',
                                            default => 'secondary'
                                        };
                                        $statusText = match($status) {
                                            'approved' => TranslationHelper::translate('Approved'),
                                            'pending' => TranslationHelper::translate('Pending'),
                                            'rejected' => TranslationHelper::translate('Rejected'),
                                            default => TranslationHelper::translate('Pending')
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusBadge }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                            </tr>
                            @if($subscription->rejection_reason)
                            <tr>
                                <th>{{ TranslationHelper::translate('Rejection Reason') }}</th>
                                <td>{{ $subscription->rejection_reason }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>{{ TranslationHelper::translate('Status') }}</th>
                                <td>
                                    @php
                                        $isActive = $subscription->isActive();
                                    @endphp
                                    <span class="badge bg-{{ $isActive ? 'success' : 'danger' }}">
                                        {{ $isActive ? TranslationHelper::translate('Active') : TranslationHelper::translate('Inactive') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ TranslationHelper::translate('Created At') }}</th>
                                <td>{{ \Carbon\Carbon::parse($subscription->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($subscription->image)
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h6>{{ TranslationHelper::translate('Transaction Image') }}</h6>
                        <img src="{{ Storage::disk('public')->url($subscription->image) }}" alt="Transaction Image" class="img-fluid" style="max-width: 300px;">
                    </div>
                </div>
                @endif

                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('admin.user-subscriptions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ TranslationHelper::translate('Back') }}
                        </a>
                        
                        @php
                            $status = $subscription->status ?? 'pending';
                        @endphp
                        @if($status === 'pending')
                            <form action="{{ route('admin.user-subscriptions.approve', $subscription->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ TranslationHelper::translate('are_you_sure_you_want_to_approve_this_subscription_') }}')">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> {{ TranslationHelper::translate('Approve') }}
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectSubscriptionModal">
                                <i class="fas fa-times"></i> {{ TranslationHelper::translate('Reject') }}
                            </button>
                        @elseif($status === 'approved')
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectSubscriptionModal">
                                <i class="fas fa-times"></i> {{ TranslationHelper::translate('Reject') }}
                            </button>
                        @elseif($status === 'rejected')
                            <form action="{{ route('admin.user-subscriptions.approve', $subscription->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ TranslationHelper::translate('are_you_sure_you_want_to_approve_this_subscription_') }}')">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> {{ TranslationHelper::translate('Approve') }}
                                </button>
                            </form>
                        @endif
                        
                        @if(in_array($status, ['pending', 'approved']))
                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectSubscriptionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">{{ TranslationHelper::translate('Reject Subscription') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form role="form" action="{{ route('admin.user-subscriptions.reject', $subscription->id) }}" class="" method="POST">
                                                {{ csrf_field() }}
                                                <div class="form-group">
                                                    <label>{{ TranslationHelper::translate('Rejection Reason') }} ({{ TranslationHelper::translate('Optional') }})</label>
                                                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="{{ TranslationHelper::translate('Enter rejection reason...') }}"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-danger" name='reject_modal'><i class="fa fa-times" aria-hidden="true"></i> {{ TranslationHelper::translate('Reject') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

