<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="{{ $user->name ?: $user->user_name }}">
    <meta property="og:description" content="Open profile in Mazadat app">
    <meta property="og:url" content="{{ $profileUrl }}">
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 24px; text-align: center;">
    <h2 style="margin-bottom: 8px;">{{ $user->name ?: $user->user_name }}</h2>
    <p style="color: #444;">Opening profile in app...</p>
    <p style="margin-top: 20px;">
        <a href="{{ $appDeepLink }}" style="margin-right: 12px;">Open in App</a>
        @if($fallbackUrl)
            <a href="{{ $fallbackUrl }}">Open in Store</a>
        @endif
    </p>

    <script>
        (function () {
            var appUrl = @json($appDeepLink);
            var fallbackUrl = @json($fallbackUrl);
            var shouldAutoRedirect = @json($shouldAutoRedirect);
            var hasOpenedApp = false;

            var timer = null;
            if (shouldAutoRedirect && fallbackUrl) {
                timer = setTimeout(function () {
                    if (!hasOpenedApp) {
                        window.location.href = fallbackUrl;
                    }
                }, 1500);
            }

            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    hasOpenedApp = true;
                    if (timer) {
                        clearTimeout(timer);
                    }
                }
            });

            window.location.href = appUrl;
        })();
    </script>
</body>
</html>
