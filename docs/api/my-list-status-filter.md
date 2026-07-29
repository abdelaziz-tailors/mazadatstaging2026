# My Auctions — Status Filter

`GET /api/live/my-list?status={inprogress|upcoming|archive}` — existing endpoint (the organizer's own auctions), extended with an optional status filter.

Requires an authenticated user (this route sits inside the `auth:api` middleware group in `routes/api/user.php`, unlike `GET /api/user/balance`'s manual `auth('api')->user()` check — an unauthenticated request gets a `401` before it ever reaches the controller).

Scope: strictly the caller's own auctions (`LiveVideo.user_id = auth('api')->user()->id`) — never another organizer's.

## Status buckets

Same bucket semantics as the existing public `GET /api/home/auctions/filter` endpoint (`App\Http\Controllers\api\Home\AuctionSearchController::filter()`), based on the raw `live_videos.status` column:

| `status` value | Matches |
|---|---|
| `inprogress` | `status = 'start'` — live right now. |
| `upcoming` | `status IS NULL` or any value other than `start`/`end` — scheduled, not started yet. |
| `archive` | `status = 'end'` — the auction has ended. |

`status` is **optional** — omit it to get all of the caller's auctions regardless of status (the endpoint's original behavior, unchanged).

## Request

```
GET /api/live/my-list?status=inprogress
Authorization: Bearer {token}
x-api-key: {api key}
Accept-Language: ar|en
```

| Query param | Required | Notes |
|---|---|---|
| `status` | No | One of `inprogress`, `upcoming`, `archive`. Omit for no filtering. |

## Response — success

Unchanged shape (`MyLiveVideoResource::collection(...)`), just a narrower result set when `status` is given:

```json
{
  "success": true,
  "code": 200,
  "message": " Added Successfully ",
  "data": [
    { "id": 12, "title": "...", "status": "start", "sales": 4000, "...": "..." }
  ]
}
```

## Response — invalid status value

```json
{
  "success": false,
  "code": 200,
  "message": "please choose a valid status (inprogress, upcoming, archive)"
}
```

## Response — not authenticated

HTTP `401` (middleware-level, no JSON body from the controller).

## Relevant source

- Controller: `App\Http\Controllers\api\Live\LiveVideoController::myList()` (`app/Http/Controllers/api/Live/LiveVideoController.php`).
- Route: `routes/api/user.php`, inside the `auth:api` group, `prefix('live')`.
- Sibling endpoint sharing the same bucket logic: `App\Http\Controllers\api\Home\AuctionSearchController::filter()` — see `docs/api/auctions-search-filter.md`.
- Tests: `tests/Feature/Api/Live/MyListStatusFilterTest.php` (7 tests: each bucket in isolation, no-filter behavior unchanged, invalid value rejected, cross-organizer isolation, authentication required). Pre-existing coverage for this endpoint's other behavior (sales totals) is untouched in `tests/Feature/Api/Live/MyAuctionsSalesTest.php`.
