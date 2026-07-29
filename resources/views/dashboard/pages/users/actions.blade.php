<div class="d-flex align-items-center gap-2">
    <a class="md-icon-btn" href="{{ route('admin.users.show', $item->id) }}" title="{{ TranslationHelper::translate('view') }}">
        <i class="fas fa-eye"></i>
    </a>

    @if (Auth::guard('admin')->user()->can('edit user'))
        <a class="md-icon-btn" href="{{ route('admin.users.edit', $item->id) }}" title="{{ TranslationHelper::translate('edit') }}">
            <i class="fas fa-pen"></i>
        </a>
    @endif

    @if (Auth::guard('admin')->user()->can('delete user'))
        <a class="md-icon-btn md-icon-btn-danger" href="#deleteModal-{{ $item->id }}" data-bs-toggle="modal" title="{{ TranslationHelper::translate('delete') }}">
            <i class="fas fa-trash"></i>
        </a>
    @endif
</div>

@if (Auth::guard('admin')->user()->can('delete user'))
  <!-- Modal -->
  <div class="modal fade" id="deleteModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">{{ TranslationHelper::translate('Delete User') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form role="form" action="{{ url('/admin/users/'.$item->id) }}" class="" method="POST">
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
