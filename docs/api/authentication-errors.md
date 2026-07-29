# Authentication error responses (`auth:api`)

## The shared API key

Every `/api/*` route requires the `x-api-key` header (checked by `App\Http\Middleware\APISettings`), regardless of whether the route also needs a bearer token — **with one deliberate exception**, `seller-invoice/{id}/pdf` (see below):

```
x-api-key: SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM
```

Missing or wrong key → HTTP `200` with `{"success": false, "code": 403, "message": "unauthorized_access"}` in the body (this middleware doesn't set a real HTTP error status — the `code` field carries the logical status). This is a static, hardcoded value in `APISettings.php` (not per-environment) — same key on local, staging, and production.

## Fixed bug: unauthenticated `auth:api` requests used to 500

Routes wrapped in `Route::group(['middleware' => ['auth:api']], ...)` (`routes/api/user.php`) also need `Authorization: Bearer <token>`. Without one, the correct response is a `401`:
```json
{ "success": false, "code": 401, "message": "دخول غير موثق" }
```

**This used to 500 instead**, for any client that didn't send an explicit `Accept: application/json` header — which includes Postman by default, a browser tab opened directly on a download link (e.g. an invoice PDF `pdf_url`), and plenty of real non-browser HTTP clients.

### Root cause
`App\Http\Middleware\Authenticate::redirectTo()` (this app's override of Laravel's base auth middleware) did:
```php
protected function redirectTo($request)
{
    if (! $request->expectsJson()) {
        return route('login');
    }
}
```
This app is API-only wherever the `auth` guard alias is used (only ever `auth:api` — the admin dashboard has its own completely separate `AuthAdmin` middleware that never touches this class) and has **no route named `login`**. So for any request where `expectsJson()` was false, this threw `Symfony\Component\Routing\Exception\RouteNotFoundException` — not an `AuthenticationException` — which bypassed `App\Exceptions\Handler::unauthenticated()`'s JSON-401 handling entirely and surfaced as a raw, unhandled 500.

### Fix
- `Authenticate::redirectTo()` now always returns `null` — there's nothing to redirect to in an API-only context, so the base middleware just throws a plain `AuthenticationException`.
- `Handler::unauthenticated()` now always returns the JSON 401 body, instead of branching on `expectsJson()` and falling back to `redirect('/')` for requests that didn't set that header. That branch was also wrong for the same reason: every caller reaching this guard is an API client, not a browser session expecting an HTML redirect.

### Practical implication for `pdf_url`-style download links
The **buyer** invoice (`user-invoice/{id}/pdf`) is still behind `auth:api` and still requires `Authorization: Bearer <token>` to download — a bare browser tab/address-bar navigation can't attach that header, so opening it directly correctly gets a `401` JSON response (not a file, not a 500). Any client fetching it must attach the header, same as any other authenticated endpoint.

The **seller** invoice (`seller-invoice/{id}/pdf`) was deliberately moved *outside* `auth:api` **and** given `->withoutMiddleware('APISettings')` for exactly this reason — see `docs/api/invoice-pdf-download.md` — it needed to be a plain clickable link with zero custom headers (a bare browser click can't attach `Authorization` *or* `x-api-key`), so it's protected entirely by a signed URL (`seller_id`/`expires`/`signature` query params) instead. The shared API key isn't embedded in the link either, since doing so would defeat its purpose as a secret — the cryptographic signature is the real security boundary for this one route.

## Relevant source

- `app/Http/Middleware/Authenticate.php`
- `app/Exceptions/Handler.php` (`unauthenticated()`)
- `app/Http/Middleware/APISettings.php` (the `x-api-key` check)
- Tests: `tests/Feature/Api/AuthenticationErrorResponseTest.php`
