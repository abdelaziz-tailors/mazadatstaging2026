<div class="d-flex align-items-center gap-2">
    <a class="md-icon-btn" href="{{ route('admin.item-services.edit', $item->id) }}" title="{{ TranslationHelper::translate('edit') }}">
        <i class="fas fa-pen"></i>
    </a>

    <a class="md-icon-btn md-icon-btn-danger" href="#deleteItemServiceModal-{{ $item->id }}" data-bs-toggle="modal" title="{{ TranslationHelper::translate('delete') }}">
        <i class="fas fa-trash"></i>
    </a>
</div>

<div class="modal fade" id="deleteItemServiceModal-{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ TranslationHelper::translate('delete_item_service') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.item-services.destroy', $item->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <p>{{ TranslationHelper::translate('are_you_sure') }}</p>
                    <button type="submit" class="btn btn-danger">{{ TranslationHelper::translate('delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
