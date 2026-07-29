<div class="d-flex align-items-center gap-2">
    <a class="md-icon-btn" href="{{ route('admin.ages.edit', $item->id) }}" title="{{ TranslationHelper::translate('edit') }}">
        <i class="fas fa-pen"></i>
    </a>

    <div class="dropdown">
        <button type="button" class="md-icon-btn" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="#deleteCityModal-{{ $item->id }}" data-bs-toggle="modal">
                <i class="fas fa-trash"></i> {{ TranslationHelper::translate('delete') }}
            </a>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="deleteCityModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">{{ TranslationHelper::translate('delete age') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form role="form" action="{{ route('admin.ages.destroy', $item->id) }}" class="" method="POST">
            <input name="_method" type="hidden" value="DELETE">
            {{ csrf_field() }}
            <p>{{ TranslationHelper::translate('are_you_sure') }}</p>
            <button type="submit" class="btn btn-danger" name='delete_modal'><i class="fa fa-trash" aria-hidden="true"></i> {{ TranslationHelper::translate('delete') }}</button>
          </form>
        </div>
      </div>
    </div>
</div>
