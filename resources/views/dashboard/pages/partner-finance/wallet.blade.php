@extends('dashboard.layouts.app')

@section('title') {{ TranslationHelper::translate('subscriber_wallet') }} @endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ TranslationHelper::translate('subscriber_wallet') }}</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ TranslationHelper::translate('subscriber_wallet') }}</li>
            </ul>
        </div>
    </div>
</div>

<div class="md-wallet-card mb-4">
    <div class="md-wallet-card-top">
        <div class="md-wallet-headline">
            <div class="md-wallet-label">
                {{ $isPartner ? TranslationHelper::translate('current_wallet_balance') : TranslationHelper::translate('subscriptions_total_revenue') }}
            </div>
            <div class="md-wallet-amount">
                {{ number_format($currentBalance, 2) }}
                <span class="md-wallet-currency">{{ TranslationHelper::translate('sar_abbr') }}</span>
            </div>
        </div>
        <div class="md-wallet-icon">
            <i class="fa-solid fa-wallet"></i>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            @if ($isPartner)
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>{{ TranslationHelper::translate('date') }}</th>
                        <th>{{ TranslationHelper::translate('type') }}</th>
                        <th>{{ TranslationHelper::translate('amount') }}</th>
                        <th>{{ TranslationHelper::translate('subscriber_wallet_balance_after') }}</th>
                        <th>{{ TranslationHelper::translate('order_number') }}</th>
                        <th>{{ TranslationHelper::translate('description') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ optional($transaction->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $transaction->type ?? '-')) }}</td>
                            <td>{{ number_format((float) $transaction->amount, 2) }}</td>
                            <td>{{ number_format((float) $transaction->balance_after, 2) }}</td>
                            <td>{{ $transaction->order->order_number ?? '-' }}</td>
                            <td>{{ $transaction->description ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">{{ TranslationHelper::translate('nothing_found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $transactions->links() }}
                </div>
            @else
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>{{ TranslationHelper::translate('date') }}</th>
                        <th>{{ TranslationHelper::translate('name') }}</th>
                        <th>{{ TranslationHelper::translate('subscription_type') }}</th>
                        <th>{{ TranslationHelper::translate('price') }}</th>
                        <th>{{ TranslationHelper::translate('status') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td>{{ optional($subscription->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ $subscription->user->name ?? '-' }}</td>
                            <td>
                                @if ($subscription->subscription_type === 'monthly')
                                    {{ TranslationHelper::translate('Monthly') }}
                                @elseif ($subscription->subscription_type === 'annual')
                                    {{ TranslationHelper::translate('Annual') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ number_format((float) $subscription->price, 2) }}</td>
                            <td><span class="badge bg-success">{{ TranslationHelper::translate('Approved') }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">{{ TranslationHelper::translate('nothing_found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $subscriptions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
