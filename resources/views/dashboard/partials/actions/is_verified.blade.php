<div class="form-check form-switch">
    <input class="form-check-input is_verified_toggler" type="checkbox" id="isVerifiedToggler-{{ $item->id }}"
    @if($item->is_verified == 1) checked @endif data-url="{{ $action }}" data-num="{{ $item->id }}">
    <label class="form-check-label" for="isVerifiedToggler-{{ $item->id }}"></label>
  </div>
