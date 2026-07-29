# User package subscription — plan, status, and renewal

This is about the **user's package/plan subscription** (اشتراك المستخدم بالباقة) — the plan that grants their account an auction-creation limit — not "subscribing" to a specific auction.

> Route/file naming note: the underlying controller class is still `AuctionSubscriptionController` (a pre-existing name from before this doc) and the model is `UserSubscription`/`Package` — renaming those would touch a wider surface (namespaces, other references) than was asked here. The **routes** were renamed to `subscription-*` (dropping the misleading `auction-` prefix) since that's the part API consumers actually see.

All endpoints below are under `/api/user/subscription*`. Every request needs `x-api-key` (required on all `/api/*` routes) and `Accept-Language` (`ar`/`en`). The four endpoints other than `subscription-plans` also sit behind the `auth:api` route-group middleware (`routes/api/user.php`) — an unauthenticated request gets Laravel's own `401` before the controller even runs, so no `Authorization: Bearer <token>` means no response body to show, just a 401.

Controller: `App\Http\Controllers\api\User\Profile\AuctionSubscriptionController`.

---

## `GET /api/user/subscription-plans`

Public (no auth). Lists active packages (`packages.is_active = 1`), each with its localized feature bullet list.

### Request
```bash
curl "https://mazadat.anaamworld.com.sa/api/user/subscription-plans" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar"
```

### Response
```json
{
  "success": true,
  "code": 200,
  "message": "Successfully",
  "data": [
    {
      "id": 3,
      "name": "الباقة الذهبية",
      "description": "اشتراك سنوي بمزايا كاملة",
      "features": ["إنشاء عدد غير محدود من المزادات", "بث مباشر عالي الجودة"],
      "auctions_limit": 20,
      "monthly_price": 300,
      "annual_price": 2999,
      "image": "packages/xxx.png"
    },
    {
      "id": 5,
      "name": "الباقة الفضية",
      "description": "اشتراك شهري",
      "features": [],
      "auctions_limit": 5,
      "monthly_price": 100,
      "annual_price": 900,
      "image": "admins/default.png"
    }
  ]
}
```
`features` is `[]` for any package the admin hasn't filled in yet (see `docs/user-types-and-roles.md` and the admin form note at the bottom of this doc) — it's never missing/null, always an array.

---

## `GET /api/user/subscription-status`

Returns the user's current (most recent) subscription — expiry, package details, and package features. This is the endpoint the "my subscription" screen should call.

### Request
```bash
curl "https://mazadat.anaamworld.com.sa/api/user/subscription-status" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer <token>"
```

### Response — user has an active subscription
```json
{
  "success": true,
  "code": 200,
  "message": "Successfully",
  "data": {
    "has_subscription": true,
    "subscription": {
      "id": 42,
      "status": "approved",
      "subscription_type": "annual",
      "price": 2999,
      "auctions_limit": 20,
      "remaining_auctions": 15,
      "started_at": "2026-03-15",
      "expires_at": "2027-03-15 00:00:00",
      "package": {
        "id": 3,
        "name": "الباقة الذهبية",
        "description": "اشتراك سنوي بمزايا كاملة",
        "features": ["إنشاء عدد غير محدود من المزادات", "بث مباشر عالي الجودة"],
        "coin": 0,
        "price": 2999,
        "image": "packages/xxx.png",
        "subscription_type": "annual",
        "auctions_limit": 20,
        "monthly_price": 300,
        "annual_price": 2999
      },
      "rejection_reason": null
    },
    "message": "Your subscription is active"
  }
}
```

### Response — user never subscribed
```json
{
  "success": true,
  "code": 200,
  "message": "Successfully",
  "data": {
    "has_subscription": false,
    "message": "You need to subscribe to create auctions"
  }
}
```
Note there's no `subscription` key at all in this case.

### Response — rejected subscription (example of the other `data.message` states)
```json
{
  "success": true,
  "code": 200,
  "message": "Successfully",
  "data": {
    "has_subscription": true,
    "subscription": {
      "id": 41,
      "status": "rejected",
      "subscription_type": "monthly",
      "price": 300,
      "auctions_limit": 5,
      "remaining_auctions": 5,
      "started_at": "2026-07-01",
      "expires_at": "2026-08-01 00:00:00",
      "package": { "...": "..." },
      "rejection_reason": "Invalid payment proof"
    },
    "message": "Your subscription was rejected"
  }
}
```

`data.message` (the standing top-level `message` field is always `"Successfully"` — the human-readable status is inside `data.message`) reflects the subscription's actual state: pending approval, rejected (with `subscription.rejection_reason`), active, or "not active" (approved but expired or no auctions remaining).

`started_at` is the subscription row's `created_at` date (there's no separate "approved at" timestamp on `user_subscriptions`).

---

## `POST /api/user/subscription` (subscribe)

Unchanged existing endpoint (only its route path was renamed, from `auction-subscription`) — creates a new `pending` subscription awaiting admin approval.

### Request

| Field | Type | Required | Notes |
|---|---|---|---|
| `package_id` | int | Yes | Must exist in `packages`. |
| `subscription_type` | `monthly` \| `annual` | Yes | Which price/duration to use. |
| `transaction_image` | file (jpeg/png/jpg, max 2MB) | No | Payment proof, reviewed by the admin before approval. |

```bash
curl -X POST "https://mazadat.anaamworld.com.sa/api/user/subscription" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer <token>" \
  -F "package_id=3" \
  -F "subscription_type=annual" \
  -F "transaction_image=@/path/to/receipt.jpg"
```

### Response
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription created successfully  Waiting for admin approval ",
  "data": {
    "id": 43,
    "user_id": 106,
    "package_id": 3,
    "subscription_type": "annual",
    "auctions_limit": 20,
    "remaining_auctions": 20,
    "expires_at": "2027-07-09T00:00:00.000000Z",
    "price": "2999.00",
    "image": "user/transaction_image/54321_receipt.jpg",
    "status": "pending",
    "updated_at": "2026-07-09T12:00:00.000000Z",
    "created_at": "2026-07-09T12:00:00.000000Z"
  }
}
```
(That double-space in the message is a pre-existing translation-string artifact from period characters being stripped — see `TranslationHelper::translate`, not something introduced here.)

---

## `POST /api/user/subscription/renew` (new)

Renews the subscription: creates a new `pending` `UserSubscription` — same admin-approval workflow as a first-time subscribe, just without having to re-look-up which package/billing-cycle the user is already on.

### Request
All fields optional — default to the user's most recent subscription's package and billing cycle:

| Field | Type | Notes |
|---|---|---|
| `package_id` | int | Pass to switch plans while renewing. Defaults to the current package. |
| `subscription_type` | `monthly` \| `annual` | Pass to switch billing cycle. Defaults to the current cycle. |
| `transaction_image` | file | Optional payment proof, same as `subscribe`. |

**Renew on the same plan** (simplest call, no body needed):
```bash
curl -X POST "https://mazadat.anaamworld.com.sa/api/user/subscription/renew" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer <token>"
```

**Renew while switching plans:**
```bash
curl -X POST "https://mazadat.anaamworld.com.sa/api/user/subscription/renew" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer <token>" \
  -F "package_id=5" \
  -F "subscription_type=monthly"
```

### Response — success
Same shape as `subscribe` — the newly created (pending) `UserSubscription` row:
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription renewal requested successfully  Waiting for admin approval ",
  "data": {
    "id": 44,
    "user_id": 106,
    "package_id": 3,
    "subscription_type": "annual",
    "auctions_limit": 20,
    "remaining_auctions": 20,
    "expires_at": "2027-07-09T00:00:00.000000Z",
    "price": "2999.00",
    "image": null,
    "status": "pending",
    "updated_at": "2026-07-09T12:05:00.000000Z",
    "created_at": "2026-07-09T12:05:00.000000Z"
  }
}
```

### Response — nothing to renew
Fails when the user has never subscribed at all (they should use `subscribe` instead):
```json
{
  "success": false,
  "code": 200,
  "message": "No subscription to renew"
}
```

---

## `GET /api/user/subscription-history`

Unchanged existing endpoint (only its route path was renamed) — all of the user's past subscriptions, newest first.

### Request
```bash
curl "https://mazadat.anaamworld.com.sa/api/user/subscription-history" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer <token>"
```

### Response
```json
{
  "success": true,
  "code": 200,
  "message": "Successfully",
  "data": [
    {
      "id": 44,
      "user_id": 106,
      "package_id": 3,
      "subscription_type": "annual",
      "auctions_limit": 20,
      "remaining_auctions": 20,
      "expires_at": "2027-07-09T00:00:00.000000Z",
      "price": "2999.00",
      "status": "pending",
      "created_at": "2026-07-09T12:05:00.000000Z",
      "package": {
        "id": 3,
        "name": "الباقة الذهبية",
        "description": "اشتراك سنوي بمزايا كاملة",
        "features": ["إنشاء عدد غير محدود من المزادات", "بث مباشر عالي الجودة"],
        "coin": 0,
        "price": 2999,
        "image": "packages/xxx.png",
        "subscription_type": "annual",
        "auctions_limit": 20,
        "monthly_price": 300,
        "annual_price": 2999
      }
    },
    {
      "id": 41,
      "user_id": 106,
      "package_id": 3,
      "subscription_type": "monthly",
      "status": "rejected",
      "created_at": "2026-07-01T09:00:00.000000Z",
      "package": { "...": "..." }
    }
  ]
}
```

---

## Relevant source

- Controller: `app/Http/Controllers/api/User/Profile/AuctionSubscriptionController.php` (`getPlans`, `getStatus`, `subscribe`, `renew`, `createPendingSubscription`, `getHistory`)
- Models: `App\Models\Package` (`featuresList()`), `App\Models\UserSubscription` (`package()` relation — now also selects the raw `features` JSON column)
- Migration: `database/migrations/2026_07_09_090000_add_features_to_packages_table.php`
- Routes: `routes/api/user.php` (`subscription-plans`, `subscription-status`, `subscription-history`, `subscription`, `subscription/renew`)
- Admin side: package `features` are edited via one Arabic-only textarea (one bullet per line), mirrored to `en` automatically — same pattern as `name`/`description` on this form. See `resources/views/dashboard/pages/packages/_form.blade.php`, `App\Http\Requests\Dashboard\Package\{Store,Update}PackageRequest`.
- Tests: `tests/Feature/Api/User/Profile/AuctionSubscriptionControllerTest.php`
- Postman: `docs/postman/Mazadat-User-Auth.postman_collection.json` → "Auction Subscription" folder
