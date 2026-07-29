# Organizer's "My Auctions" — Sales Figure

`GET /api/live/my-list` (existing endpoint — no new routes, no filters added; this is a pure additive field on an already-existing response).

Requires an authenticated user (`auth:api`). Lists the auctions created by the currently logged-in organizer ("مشترك" — the account with a commercial registration + tax card that's allowed to create auctions), filtered to `LiveVideo.user_id = auth()->id`.

## What changed
Each auction in the response now includes a `sales` field:

```json
{
  "success": true,
  "code": 200,
  "message": " Added Successfully ",
  "data": [
    {
      "id": 353,
      "title": "...",
      "sales": 4000,
      "video_items": [ ... ],
      "...": "..."
    }
  ]
}
```

`sales` = the sum of `finished_price` across every item (`LiveVideoItem`) belonging to that auction — i.e. every piece that's actually been sold. Example from the request: 4 items sold at 1,000 SAR each → `sales: 4000`. Unsold items have a `null` `finished_price`, which `SUM()` ignores, so they don't need a separate status check.

No existing field, filter, status value, or response shape was changed — `sales` is purely additive.

## Relevant source
- Model: `App\Models\LiveVideo::totalSales()` (`app/Models/LiveVideo.php`) — `video_items()->sum('finished_price')`. Mirrors the existing `sub_total(?int $buyerUserId)` method's style but without the per-buyer filter, since this is an auction-wide total across all buyers.
- Resource: `App\Http\Resources\User\MyLiveVideoResource` (`app/Http/Resources/User/MyLiveVideoResource.php`) — added `'sales' => $this->totalSales()`.
- Controller/route (unchanged): `App\Http\Controllers\api\Live\LiveVideoController::myList()`, route `GET /api/live/my-list` (registered directly under `routes/api/user.php`'s `live` prefix group, **not** nested under `/user` — the actual path is `/api/live/my-list`, not `/api/user/live/my-list`).
- Tests: `tests/Feature/Api/Live/MyAuctionsSalesTest.php`
