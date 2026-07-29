<div class="d-flex align-items-center gap-2">
    @if (Auth::guard('admin')->user()->can('view partners'))
        <a class="md-icon-btn" href="{{ route('admin.partners.show', $item->id) }}" title="{{ TranslationHelper::translate('view') }}">
            <i class="fas fa-eye"></i>
        </a>
    @endif

    @if (Auth::guard('admin')->user()->can('edit partner'))
        <a class="md-icon-btn" href="{{ route('admin.partners.edit', $item->id) }}" title="{{ TranslationHelper::translate('edit') }}">
            <i class="fas fa-pen"></i>
        </a>
    @endif

    <div class="dropdown">
        <button type="button" class="md-icon-btn" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
        <div class="dropdown-menu">
            @if (Auth::guard('admin')->user()->can('edit partner'))
                <a class="dropdown-item" href="{{ route('admin.partners.change-password', $item->id) }}">
                    <i class="fas fa-key"></i> {{ TranslationHelper::translate('change_password') }}
                </a>
                <a class="dropdown-item {{ ($item->user->is_active ?? false) ? 'text-danger' : '' }}" href="#" data-toggle-url="{{ route('admin.partners.active_toogler', $item->id) }}" onclick="event.preventDefault(); $.post(this.dataset.toggleUrl, {_token: '{{ csrf_token() }}'}).done(function(){ $('#data-table').DataTable().draw(false); });">
                    <i class="fas fa-ban"></i>
                    {{ ($item->user->is_active ?? false) ? TranslationHelper::translate('deactivate') : TranslationHelper::translate('activate') }}
                </a>
            @endif

            @if (Auth::guard('admin')->user()->can('delete partner'))
                <a class="dropdown-item text-danger" href="#deleteModal-{{ $item->id }}" data-bs-toggle="modal">
                    <i class="fas fa-trash"></i> {{ TranslationHelper::translate('delete') }}
                </a>
            @endif
        </div>
    </div>
</div>

@if (Auth::guard('admin')->user()->can('delete partner'))
  <!-- Modal -->
  <div class="modal fade" id="deleteModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">{{ TranslationHelper::translate('delete_partner') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form role="form" action="{{ url('/admin/partners/'.$item->id) }}" class="" method="POST">
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
