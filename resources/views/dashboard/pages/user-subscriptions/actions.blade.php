@php
    $status = $item->status ?? 'pending';
@endphp
<div class="d-flex align-items-center gap-2">
    @if (Auth::guard('admin')->user()->can('view user subscriptions'))
        <a class="md-icon-btn" href="{{ route('admin.user-subscriptions.show', $item->id) }}" title="{{ TranslationHelper::translate('view') }}">
            <i class="fas fa-eye"></i>
        </a>
    @endif

    @if (in_array($status, ['pending', 'rejected']))
        <form action="{{ route('admin.user-subscriptions.approve', $item->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ TranslationHelper::translate('are_you_sure_you_want_to_approve_this_subscription_') }}')">
            {{ csrf_field() }}
            <button type="submit" class="md-icon-btn md-icon-btn-success" title="{{ TranslationHelper::translate('Approve') }}">
                <i class="fas fa-check"></i>
            </button>
        </form>
    @endif

    @if (in_array($status, ['pending', 'approved']))
        <a class="md-icon-btn md-icon-btn-danger" href="#rejectSubscriptionModal-{{ $item->id }}" data-bs-toggle="modal" title="{{ TranslationHelper::translate('Reject') }}">
            <i class="fas fa-times"></i>
        </a>
    @endif

    @if (Auth::guard('admin')->user()->can('delete user subscription'))
        <a class="md-icon-btn md-icon-btn-danger" href="#deleteSubscriptionModal-{{ $item->id }}" data-bs-toggle="modal" title="{{ TranslationHelper::translate('delete') }}">
            <i class="fas fa-trash"></i>
        </a>
    @endif
</div>

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
