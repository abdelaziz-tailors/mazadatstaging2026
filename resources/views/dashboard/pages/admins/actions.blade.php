<div class="d-flex align-items-center gap-2">
    <a class="md-icon-btn" href="{{ route('admin.admins.show', $item->id) }}" title="{{ TranslationHelper::translate('view') }}">
        <i class="fas fa-eye"></i>
    </a>

    @if (Auth::guard('admin')->user()->can('edit admin'))
        <a class="md-icon-btn" href="{{ route('admin.admins.edit', $item->id) }}" title="{{ TranslationHelper::translate('edit') }}">
            <i class="fas fa-pen"></i>
        </a>
    @endif

    @if (Auth::guard('admin')->user()->can('edit admin') || Auth::guard('admin')->user()->can('delete admin'))
        <div class="dropdown">
            <button type="button" class="md-icon-btn" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-vertical"></i>
            </button>
            <div class="dropdown-menu">
                @if (Auth::guard('admin')->user()->can('edit admin'))
                    <a class="dropdown-item" href="{{ route('admin.admins.change-password', $item->id) }}">
                        <i class="fas fa-key"></i> {{ TranslationHelper::translate('change_password') }}
                    </a>
                @endif
                @if (Auth::guard('admin')->user()->can('delete admin'))
                    <a class="dropdown-item text-danger" href="#deleteModal-{{ $item->id }}" data-bs-toggle="modal">
                        <i class="fas fa-trash"></i> {{ TranslationHelper::translate('delete') }}
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>

@if (Auth::guard('admin')->user()->can('delete admin'))
  <!-- Modal -->
  <div class="modal fade" id="deleteModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">{{ TranslationHelper::translate('delete_admin') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form role="form" action="{{ url('/admin/admins/'.$item->id) }}" class="" method="POST">
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
