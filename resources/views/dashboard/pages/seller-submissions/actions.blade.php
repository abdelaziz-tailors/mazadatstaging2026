<div class="d-flex align-items-center gap-2">
    <a class="md-icon-btn" href="{{ route('admin.seller-submissions.show', $item->id) }}" title="{{ TranslationHelper::translate('view') }}">
        <i class="fas fa-eye"></i>
    </a>

    <a class="md-icon-btn" href="#editRequestModal-{{ $item->id }}" data-bs-toggle="modal" title="{{ TranslationHelper::translate('Request Edit') }}">
        <i class="fas fa-pen"></i>
    </a>

    @if($item->status !== 'approved')
        <form action="{{ route('admin.seller-submissions.approve', $item->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ TranslationHelper::translate('are_you_sure') }}')">
            {{ csrf_field() }}
            <button type="submit" class="md-icon-btn md-icon-btn-success" title="{{ TranslationHelper::translate('Approve') }}">
                <i class="fas fa-check"></i>
            </button>
        </form>
    @endif

    <a class="md-icon-btn md-icon-btn-danger" href="#rejectSubmissionModal-{{ $item->id }}" data-bs-toggle="modal" title="{{ TranslationHelper::translate('Reject') }}">
        <i class="fas fa-times"></i>
    </a>
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
