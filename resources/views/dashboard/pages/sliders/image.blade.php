@if (Storage::disk('public')->exists($item->image))
    <img src="{{ Storage::disk('public')->url($item->image) }}" alt="slider" class="img-thumbnail" style="max-width: 80px;">
@else
    -
@endif
