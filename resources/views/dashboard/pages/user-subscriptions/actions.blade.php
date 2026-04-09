<div class="btn-group">
    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-cogs"></i>
    </button>
    <div class="dropdown-menu">
        @if (Auth::guard('admin')->user()->can('view user subscriptions'))
            <a class="dropdown-item" href="{{ route('admin.user-subscriptions.show', $item->id) }}">
                <i class="fas fa-eye"></i> {{ TranslationHelper::translate('view') }}
            </a>
        @endif

        @php
            $status = $item->status ?? 'pending';
        @endphp
        @if ($status === 'pending')
            <form action="{{ route('admin.user-subscriptions.approve', $item->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ TranslationHelper::translate('are_you_sure_you_want_to_approve_this_subscription_') }}')">
                {{ csrf_field() }}
                <button type="submit" class="dropdown-item text-success" style="border: none; background: none; width: 100%; text-align: left;">
                    <i class="fas fa-check"></i> {{ TranslationHelper::translate('Approve') }}
                </button>
            </form>
            <a class="dropdown-item text-danger" href="#rejectSubscriptionModal-{{ $item->id }}" data-bs-toggle="modal">
                <i class="fas fa-times"></i> {{ TranslationHelper::translate('Reject') }}
            </a>
        @elseif ($status === 'approved')
            <a class="dropdown-item text-warning" href="#rejectSubscriptionModal-{{ $item->id }}" data-bs-toggle="modal">
                <i class="fas fa-times"></i> {{ TranslationHelper::translate('Reject') }}
            </a>
        @elseif ($status === 'rejected')
            <form action="{{ route('admin.user-subscriptions.approve', $item->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ TranslationHelper::translate('are_you_sure_you_want_to_approve_this_subscription_') }}')">
                {{ csrf_field() }}
                <button type="submit" class="dropdown-item text-success" style="border: none; background: none; width: 100%; text-align: left;">
                    <i class="fas fa-check"></i> {{ TranslationHelper::translate('Approve') }}
                </button>
            </form>
        @endif

    @if (Auth::guard('admin')->user()->can('delete user subscription'))
        <a class="dropdown-item" href="#deleteSubscriptionModal-{{ $item->id }}" data-bs-toggle="modal">
          <i class="fas fa-trash"></i> {{ TranslationHelper::translate('delete') }}
        </a>
    @endif
</div>

@php
    $status = $item->status ?? 'pending';
@endphp
@if (in_array($status, ['pending', 'approved']))
  <!-- Reject Modal -->
  <div class="modal fade" id="rejectSubscriptionModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">{{ TranslationHelper::translate('Reject Subscription') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form role="form" action="{{ route('admin.user-subscriptions.reject', $item->id) }}" class="" method="POST">
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

@if (Auth::guard('admin')->user()->can('delete user subscription'))
  <!-- Modal -->
  <div class="modal fade" id="deleteSubscriptionModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">{{ TranslationHelper::translate('Delete Subscription') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form role="form" action="{{ url('/admin/user-subscriptions/'.$item->id) }}" class="" method="POST">
                <input name="_method" type="hidden" value="DELETE">
                {{ csrf_field() }}
                <p>{{ TranslationHelper::translate('are_you_sure') }}</p>
                <button type="submit" class="btn btn-danger" name='delete_modal'><i class="fa fa-trash" aria-hidden="true"></i> {{ TranslationHelper::translate('delete') }}</button>
            </form>
        </div>
      </div>
    </div>
  </div>
@endif

