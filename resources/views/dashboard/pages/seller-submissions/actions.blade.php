<div class="btn-group">
    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-cogs"></i>
    </button>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('admin.seller-submissions.show', $item->id) }}">
            <i class="fas fa-eye"></i> {{ TranslationHelper::translate('view') }}
        </a>

        @if($item->status !== 'approved')
            <form action="{{ route('admin.seller-submissions.approve', $item->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ TranslationHelper::translate('are_you_sure') }}')">
                {{ csrf_field() }}
                <button type="submit" class="dropdown-item text-success" style="border: none; background: none; width: 100%; text-align: left;">
                    <i class="fas fa-check"></i> {{ TranslationHelper::translate('Approve') }}
                </button>
            </form>
        @endif

        <a class="dropdown-item text-warning" href="#editRequestModal-{{ $item->id }}" data-bs-toggle="modal">
            <i class="fas fa-pen"></i> {{ TranslationHelper::translate('Request Edit') }}
        </a>

        <a class="dropdown-item text-danger" href="#rejectSubmissionModal-{{ $item->id }}" data-bs-toggle="modal">
            <i class="fas fa-times"></i> {{ TranslationHelper::translate('Reject') }}
        </a>
    </div>
</div>

<div class="modal fade" id="editRequestModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ TranslationHelper::translate('Request Edit') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.seller-submissions.request-edit', $item->id) }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label>{{ TranslationHelper::translate('notes') }}</label>
                        <textarea name="review_note" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning mt-2">{{ TranslationHelper::translate('save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectSubmissionModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ TranslationHelper::translate('Reject') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.seller-submissions.reject', $item->id) }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label>{{ TranslationHelper::translate('notes') }}</label>
                        <textarea name="review_note" class="form-control" rows="4"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger mt-2">{{ TranslationHelper::translate('Reject') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
