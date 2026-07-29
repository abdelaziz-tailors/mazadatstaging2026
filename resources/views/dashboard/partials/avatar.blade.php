{{--
    Unified avatar/thumbnail: fixed size, graceful placeholder when the image
    is missing or the file doesn't exist on disk.

    Usage:
        @include('dashboard.partials.avatar', ['path' => $user->image, 'name' => $user->name])
        @include('dashboard.partials.avatar', ['path' => $user->image, 'name' => $user->name, 'size' => 64])
        @include('dashboard.partials.avatar', ['path' => $auction->image, 'name' => $auction->title, 'placeholderIcon' => 'fa-solid fa-image'])
--}}
@php
    $size = $size ?? 40;
    $placeholderIcon = $placeholderIcon ?? 'fa-solid fa-user';
    $exists = !empty($path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
@endphp
@if ($exists)
    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}"
        alt="{{ $name ?? '' }}"
        class="md-avatar"
        style="width: {{ $size }}px; height: {{ $size }}px;">
@else
    <span class="md-avatar-placeholder" style="width: {{ $size }}px; height: {{ $size }}px; font-size: {{ round($size * 0.45) }}px;">
        <i class="{{ $placeholderIcon }}"></i>
    </span>
@endif
