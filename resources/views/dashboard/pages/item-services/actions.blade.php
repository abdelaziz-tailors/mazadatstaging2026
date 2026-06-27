<div class="btn-group">
    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
        <i class="fas fa-cogs"></i>
    </button>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('admin.item-services.edit', $item->id) }}">
            <i class="fas fa-edit"></i> {{ TranslationHelper::translate('edit') }}
        </a>
        <a class="dropdown-item" href="#deleteItemServiceModal-{{ $item->id }}" data-bs-toggle="modal">
            <i class="fas fa-trash"></i> {{ TranslationHelper::translate('delete') }}
        </a>
    </div>
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
