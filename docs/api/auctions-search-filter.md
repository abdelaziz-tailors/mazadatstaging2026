# Auction Search & Filter

Both endpoints are public (no authentication) home-screen endpoints for browsing auctions — e.g. the search bar and status tabs in the mobile app's home screen.

## Search — `GET /api/home/auctions/search`

Searches auctions by title (matches either the English `title` or Arabic `title_ar` column).

### Headers

Same as every `/api/*` route: `x-api-key` (required), `Accept-Language` (`ar`/`en`), `Content-Type: application/json`.

### Query params

| Param | Type | Required | Notes |
|---|---|---|---|
| `q` | string | Yes | Search keyword. Matched with `LIKE %q%` against `title` and `title_ar`. |
| `video_limit` | int | No | Page size. Defaults to 10. |
| `page` | int | No | Standard Laravel pagination page number. |

### Example

```bash
curl "https://mazadat.anaamworld.com.sa/api/home/auctions/search?q=%D8%A5%D8%A8%D9%84" \
  -H "x-api-key: <api-key>" -H "Accept-Language: ar"
```

## Filter — `GET /api/home/auctions/filter`

Filters auctions into one of three status buckets, based on the raw `live_videos.status` column (same mapping used by the admin "auction status" badge: `dashboard.pages.videos.status`):

| `status` value | Matches |
|---|---|
| `inprogress` | `status = 'start'` — the auction is live right now. |
| `upcoming` | `status IS NULL` or any value other than `start`/`end` — scheduled, not started yet. |
| `archive` | `status = 'end'` — the auction has ended. |

### Query params

| Param | Type | Required | Notes |
|---|---|---|---|
| `status` | string | Yes | One of `inprogress`, `upcoming`, `archive`. |
| `video_limit` | int | No | Page size. Defaults to 10. |
| `page` | int | No | Pagination page number. |

### Example

```bash
curl "https://mazadat.anaamworld.com.sa/api/home/auctions/filter?status=inprogress" \
  -H "x-api-key: <api-key>" -H "Accept-Language: ar"
```

## Response shape (both endpoints)

Same envelope and `HomeVideoResource` item shape as the existing `GET /api/home/video/list` endpoint:

```json
{
  "success": true,
  "code": 200,
  "message": "Successfully",
  "data": [
    {
      "id": 12,
      "title": "...",
      "title_ar": "...",
      "status": "start",
      "start_price": 3100,
      "image": [...],
      "partner": {...},
      "city": {...},
      "video_items": [...],
      "...": "..."
    }
  ],
  "pagination": {
    "total": 42,
    "count": 10,
    "per_page": 10,
    "current_page": 1,
    "total_pages": 5,
    "links": { "prev": null, "next": "https://.../auctions/filter?status=inprogress&page=2" }
  }
}
```

### Validation error (missing/invalid params) — `200`

```json
{ "success": false, "code": 200, "message": "please enter a search keyword" }
```

```json
{ "success": false, "code": 200, "message": "please choose a valid status (inprogress, upcoming, archive)" }
```

## Relevant source

- Controller: `app/Http/Controllers/api/Home/AuctionSearchController.php`
- Validation: `app/Http/Requests/api/Home/SearchAuctionsRequest.php`, `app/Http/Requests/api/Home/FilterAuctionsRequest.php`
- Routes: `routes/api/user.php` (public `home` group)
- Tests: `tests/Feature/Api/Home/AuctionSearchControllerTest.php`

---

# Wallet balance, dues & active auctions — `GET /api/user/balance`

Existing endpoint, extended with two new fields. Requires an authenticated user (Passport token); returns an "Un Authenticated" error if no valid token is sent.

### Response

```json
{
  "success": true,
  "code": 200,
  "message": null,
  "data": {
    "balance": 12450,
    "dues": 1250,
    "active_bids_count": 4
  }
}
```

| Field | Meaning |
|---|---|
| `balance` | `users.wallet_balance` — the account's current wallet balance, regardless of role. |
| `dues` | Role-dependent, computed live (not stored): <br>• **Buyer side** (`user_type` in `buyer`, `buyer_vendor`) — sum of `total` on all of the user's `Order`s still `payment_status = 'unpaid'` (`OrderService::unpaidBuyerTotal`): what they still owe for items they've bought. <br>• **Seller side** (`user_type` in `seller`, `vendor`, `buyer_vendor`) — sum of the net payout (`finished_price` − commission − service fee − piece services, same math as the seller invoice breakdown) for `OrderItem`s where they're the consignor (`seller_id`) and `settled_at` is still null (`OrderService::unsettledSellerNet`): what's owed to them for items they've sold but haven't been paid out for yet. <br>A `buyer_vendor` account sums both sides. |
| `active_bids_count` | Count of **this user's own** currently in-progress auctions (`status = 'start'`), role-dependent, computed fresh from the database on every request (no caching layer) via `BalanceResource::myActiveAuctionsCount()`: <br>• **Organizer** ("مشترك", `user_type` `vendor`/`buyer_vendor`) — auctions they created (`LiveVideo.user_id`). <br>• **Buyer** (`user_type` `buyer`/`buyer_vendor`) — auctions they've placed at least one bid in. A bid is a `VideoComment` row (the `POST /api/auctions/add` endpoint, `AuctionVideoController::add()`, is this app's actual bid-placement action — its `video_id` column is set directly to the `LiveVideo`'s own id, not a `LiveVideoItem` id). <br>• **Seller** (`user_type` `seller`) — auctions containing at least one item they're the consignor for (`LiveVideoItem.seller_id`). <br>A `buyer_vendor` account combines the organizer and buyer sides (deduplicated, in case the same auction ever matched both). <br><br>Two earlier, superseded versions of this field: it originally counted per-item wins (items where this user was the current winning bidder on a still-live auction) — effectively always 0 in production, since `CheckLiveVideoEnd` flips the auction's `status` to `'end'` *before* it assigns `user_finished_id` on the items. A second version made it a platform-wide count of all in-progress auctions regardless of user — not what was wanted; the field is meant to answer "how many of *my own* auctions are live right now." |

## Relevant source

- Resource: `app/Http/Resources/User/BalanceResource.php` (`myActiveAuctionsCount`)
- Service: `app/Services/OrderService.php` (`unsettledSellerNet`, `unpaidBuyerTotal`)
- Controller: `app/Http/Controllers/api/User/Profile/UserProfileController.php::balance`
- Bid placement: `app/Http/Controllers/api/Video/AuctionVideoController.php::add()`, model `App\Models\VideoComment`
- Tests: `tests/Feature/Api/User/Profile/BalanceControllerTest.php`
