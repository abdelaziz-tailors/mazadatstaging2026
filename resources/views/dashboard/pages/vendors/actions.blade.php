<div class="btn-group">
        <a class="dropdown-item" href="{{ route('admin.vendors.edit', $item->id) }}">
            <i style="color: #2196f3" class="fas fa-edit"></i>
        </a>

        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#deleteModal-{{ $item->id }}" data-bs-toggle="modal">
            <i style="color: #e42f2f" class="fas fa-trash"></i>
        </a>
</div>
  <!-- Modal -->
  <div class="modal fade" id="deleteModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">{{ TranslationHelper::translate('Delete Vendor') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form role="form" action="{{ url('/admin/vendor/'.$item->id) }}" class="" method="POST">
            <input name="_method" type="hidden" value="DELETE">
            {{ csrf_field() }}
            <p>{{ TranslationHelper::translate('are_you_sure') }}</p>
            <button type="submit" class="btn btn-danger" name='delete_modal'><i class="fa fa-trash" aria-hidden="true"></i> {{ TranslationHelper::translate('delete') }}</button>
          </form>
        </div>
      </div>
    </div>
  </div>
