
<audio controls>
    <source src="{{ Storage::disk('public')->url($item->sound) }}" type="audio/ogg">
    <source src="{{ Storage::disk('public')->url($item->sound) }}" type="audio/mpeg">
    Your browser does not support the audio element.
</audio>

