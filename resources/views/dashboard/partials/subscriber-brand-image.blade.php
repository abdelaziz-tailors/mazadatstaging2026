@php
    $subscriber = Auth::guard('admin')->user();
    // Match the profile avatar's public-storage path handling.
    $normalizedPath = ltrim(str_replace(['/storage/', 'storage/'], '', (string) $subscriber->image), '/');
    $hasImage = !empty($normalizedPath) && Storage::disk('public')->exists($normalizedPath);
    $brandSize = ($compact ?? false) ? 30 : 48;
    $defaultAvatar = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><rect width="48" height="48" rx="24" fill="#e5eee8"/><circle cx="24" cy="18" r="8" fill="#527361"/><path d="M9 43a15 15 0 0 1 30 0" fill="#527361"/></svg>');
@endphp
<img src="{{ $hasImage ? asset('storage/'.$normalizedPath) : $defaultAvatar }}"
    alt="{{ $subscriber->name }}"
    width="{{ $brandSize }}" height="{{ $brandSize }}"
    style="width: {{ $brandSize }}px; height: {{ $brandSize }}px; object-fit: cover; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(35, 77, 52, 0.14); background-color: #e5eee8;"
    data-default-avatar="{{ $defaultAvatar }}"
    onerror="this.onerror=null; this.src=this.dataset.defaultAvatar;">
