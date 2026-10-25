<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | Mazadat</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ $shareUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $shareUrl }}">
    @if($imageUrl)
        <meta property="og:image" content="{{ $imageUrl }}">
    @endif
</head>
<body style="font-family:Arial,sans-serif;max-width:640px;margin:40px auto;padding:24px;text-align:center">
    <h1>{{ $title }}</h1>
    @if($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $title }}" style="max-width:100%;height:auto">
    @endif
    <p>{{ $description }}</p>
    @if($ended)
        <p>This auction has ended.</p>
    @endif
    <p><a href="{{ $appUrl }}">Open in Mazadat</a></p>
    @if($iosStoreUrl)
        <p><a href="{{ $iosStoreUrl }}">Download for iOS</a></p>
    @endif
    @if($androidStoreUrl)
        <p><a href="{{ $androidStoreUrl }}">Download for Android</a></p>
    @endif
</body>
</html>
