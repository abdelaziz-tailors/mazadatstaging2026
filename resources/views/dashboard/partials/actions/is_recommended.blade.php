<div class="form-check form-switch">
    <input class="form-check-input is_recommended_toggler" type="checkbox" id="isRecommendedToggler-{{ $item->id }}"
    @if($item->is_recommended == 1) checked @endif data-url="{{ $action }}" data-num="{{ $item->id }}">
    <label class="form-check-label" for="isRecommendedToggler-{{ $item->id }}"></label>
  </div>