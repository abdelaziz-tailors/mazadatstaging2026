# Organizer Mobile Dashboard Stats

`GET /api/user/organizer-stats` — summary stats for the mobile app's organizer dashboard screen: total distinct buyers, pending commissions, this month's profit, distinct sellers, auctions, auction products, and total sales.

Requires an authenticated user (checked manually in the controller, same convention as `GET /api/user/balance`). Restricted to organizer accounts (`user_type` of `vendor` or `buyer_vendor`) — a `buyer`/`seller`-only account gets a `403`.

Scope: strictly the authenticated organizer's own auctions (`LiveVideo.user_id = auth('api')->user()->id`) and their items — never platform-wide.

## Metrics

| Field | Meaning |
|---|---|
| `total_buyers` | Distinct count of buyers (`Order.buyer_id`) who have ordered from this organizer's own auctions. |
| `pending_commissions` | `SUM(commission_value)` on this organizer's orders where `payment_status = 'unpaid'`. |
| `monthly_profit` | `SUM(commission_value)` on this organizer's orders where `payment_status = 'paid'` **and** `created_at` falls within the current calendar month. |
| `total_sellers` | Distinct count of sellers/consignors (`LiveVideoItem.seller_id`) who have supplied items to this organizer's own auctions. Items with no `seller_id` set are excluded. |
| `total_auctions` | Count of auctions (`LiveVideo`) created by this organizer. |
| `total_products` | Count of items (`LiveVideoItem`) across all of this organizer's auctions. |
| `total_sales` | `SUM(finished_price)` across all items (`LiveVideoItem`) in this organizer's auctions — unsold items have a null `finished_price`, which `SUM` ignores. |

`orders` has no `paid_at` column, so "this month" is based on `created_at` (the order-creation date), matching the same fallback used elsewhere in this app (e.g. subscription `started_at`).

**Note:** a "categories" metric (`التصنيفات`) was requested but is not included — `category_id` was removed from `live_video_items` as a legacy column (migration `2026_06_22_110000_cleanup_legacy_columns.php`) and never existed on `live_videos`, so there is currently no real data linking an organizer's auctions/products to a category.

## Request

```
GET /api/user/organizer-stats
Authorization: Bearer {token}
x-api-key: {api key}
Accept-Language: ar|en
```

No parameters.

## Response — success (organizer account)

```json
{
  "success": true,
  "code": 200,
  "message": null,
  "data": {
    "total_buyers": 12,
    "pending_commissions": 450.75,
    "monthly_profit": 1230.5,
    "total_sellers": 5,
    "total_auctions": 8,
    "total_products": 42,
    "total_sales": 15230.75
  }
}
```

## Response — not authenticated

```json
{
  "success": false,
  "code": 401,
  "message": "Un Authenticated"
}
```

## Response — authenticated, but not an organizer account (buyer/seller)

HTTP 403:

```json
{
  "success": false,
  "code": 403,
  "message": "Un-Authorized Access"
}
```

## Relevant source
- Controller: `App\Http\Controllers\api\User\Profile\UserProfileController::organizerStats()` (`app/Http/Controllers/api/User/Profile/UserProfileController.php`) — mirrors the existing `balance()` method's style.
- Resource: `App\Http\Resources\User\OrganizerStatsResource` (`app/Http/Resources/User/OrganizerStatsResource.php`).
- Route: registered in `routes/api/user.php`, right next to `balance`, inside the `Profile`-namespaced group.
- Tests: `tests/Feature/Api/User/Profile/OrganizerStatsControllerTest.php`
