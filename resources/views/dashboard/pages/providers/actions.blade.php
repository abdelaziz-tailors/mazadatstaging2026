<div class="btn-group">
        @if (Auth::guard('admin')->user()->can('edit user'))
            <a class="dropdown-item" href="">
                <i style="color: #4caf50" class="fas fa-eye"></i>
            </a>
        @endif
            @if (Auth::guard('admin')->user()->can('edit user'))
                <a class="dropdown-item" href="">
                    <i style="color: #2196f3" class="fas fa-video-camera"></i>
                </a>
            @endif

        @if (Auth::guard('admin')->user()->can('delete user'))
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#deleteModal-{{ $item->id }}" data-bs-toggle="modal">
                <i style="color: #e42f2f" class="fas fa-trash"></i>
            </a>
        @endif
</div>

@if ($item->provider_profile_completed &&
    (optional($item->provider_profile)->status == 'approved' || optional($item->provider_profile)->status == 'under_review')
)
  <!-- Modal -->
  <div class="modal fade" id="suspensionModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">{{ TranslationHelper::translate('Suspend Provider') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form role="form" action="" class="provider_suspension_form" data-provider="{{ $item->id }}" method="POST">
                <div id="provider_suspension_form-{{ $item->id }}"></div>
                {{ csrf_field() }}
                <div class="form-group">
                    {!! Form::label('suspension_reason', TranslationHelper::translate('Reason'), ['class'=>'form-label']) !!}
                    {!! Form::textarea('suspension_reason', optional($item->provider_profile)->suspension_reason, ['class' => 'form-control', 'rows' => 10]) !!}
                </div>
                <button type="submit" class="btn btn-info">{{ TranslationHelper::translate('Send') }}</button>
            </form>
        </div>
      </div>
    </div>
  </div>
@endif
@if (Auth::guard('admin')->user()->can('delete provider'))
  <!-- Modal -->
  <div class="modal fade" id="deleteModal-{{ $item->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">{{ TranslationHelper::translate('Delete Provider') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form role="form" action="{{ url('/admin/providers/'.$item->id) }}" class="" method="POST">
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
