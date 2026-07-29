@if (Storage::disk('public')->exists($item->image))
    <img src="{{ Storage::disk('public')->url($item->image) }}" alt="slider" class="md-thumb">
@else
    -
@endif
