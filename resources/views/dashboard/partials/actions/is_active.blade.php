<div class="form-check form-switch">
    <input class="form-check-input is_active_toggler" type="checkbox" id="isActiveToggler-{{ $item->id }}"
    @if($item->is_active == 1) checked @endif data-url="{{ $action }}" data-num="{{ $item->id }}">
    <label class="form-check-label" for="isActiveToggler-{{ $item->id }}"></label>
  </div>