# User Wallet

`GET /api/user/wallet` — new endpoint. Data for the mobile app's "my wallet" screen: current balance, total deposits, total withdrawals, and a cursor-paginated transaction history.

Requires an authenticated user (checked manually in the controller, same convention as `GET /api/user/balance`). No account-type restriction — any authenticated user can call it; it only ever reflects their own transactions.

## Metrics

| Field | Meaning |
|---|---|
| `balance` | The user's real, current wallet balance (`User.wallet_balance`) — the same source of truth `GET /api/user/balance` uses. |
| `available_balance` | Explicit alias of `balance`, always the same value. Added so the app never has to derive "available" itself — it previously computed `balance - dues` client-side, which goes **negative** whenever pending dues exceed the current balance (pending dues aren't a deduction from the wallet; they simply haven't been credited to it yet, since settlement only happens once an order is paid — see `AuctionWalletSettlement`). |
| `pending_balance` | Explicit alias of the same "dues" figure `GET /api/user/balance` returns as `dues`: unpaid orders owed by a buyer, plus not-yet-settled seller/vendor net. Money the user is owed/owes but that hasn't moved through the wallet yet. |
| `total_deposits` | Real `SUM()` of every `wallet_transactions` row belonging to this user with a **positive** `amount`, across their entire history (not just the current page). |
| `total_withdrawals` | Real `SUM()` of the **absolute value** of every row with a **negative** `amount`, across their entire history. |

`wallet_transactions` has no separate status/direction column — the signed `amount` itself is the single source of truth for deposit vs. withdrawal, regardless of the free-form `type` field (`buyer_debit`, `seller_credit`, `partner_credit`, `adjustment`).

## Transactions list (cursor pagination)

Ordered newest first (`id` descending). Cursor pagination is used instead of offset pagination specifically for performance on long transaction histories.

| Field | Meaning |
|---|---|
| `id` | Transaction id. |
| `type` | `buyer_debit` \| `seller_credit` \| `partner_credit` \| `adjustment`. |
| `amount` | Signed amount (positive = deposit, negative = withdrawal). |
| `balance_after` | Wallet balance immediately after this transaction, if recorded. |
| `description` | Translated per `Accept-Language` (e.g. "Auction seller settlement" → "تسوية البائع للمزاد" for `ar`), `null` if none was recorded. Stored internally as literal English text (see `AuctionWalletSettlement::applyDelta()`) and translated at read time via `TranslationHelper`, same as every other user-facing string in this API. |
| `order_number` | The linked order's `order_number`, or `null` if this transaction isn't tied to an order. |
| `created_at` | ISO 8601 timestamp. |

## Request

```
GET /api/user/wallet?per_page=15&cursor={cursor}
Authorization: Bearer {token}
x-api-key: {api key}
Accept-Language: ar|en
```

| Query param | Required | Meaning |
|---|---|---|
| `per_page` | No | 1–100, defaults to 15. Any value outside that range silently falls back to 15. |
| `cursor` | No | Pass the previous response's `data.pagination.next_cursor` to fetch the next page. Omit for the first page. |

## Response — success

```json
{
  "success": true,
  "code": 200,
  "message": null,
  "data": {
    "balance": 12450.75,
    "available_balance": 12450.75,
    "pending_balance": 0,
    "total_deposits": 42800,
    "total_withdrawals": 18250,
    "transactions": [
      {
        "id": 24,
        "type": "partner_credit",
        "amount": 551,
        "balance_after": 5050.5,
        "description": "Auction partner settlement",
        "order_number": "ORD-20260630-00001",
        "created_at": "2026-07-02T21:55:41+03:00"
      }
    ],
    "pagination": {
      "per_page": 15,
      "next_cursor": "eyJpZCI6MTgsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0",
      "prev_cursor": null,
      "has_more_pages": true
    }
  }
}
```

On the last page, `next_cursor` is `null` and `has_more_pages` is `false`.

## Response — not authenticated

```json
{
  "success": false,
  "code": 200,
  "message": "Un Authenticated"
}
```

## Relevant source
- Controller: `App\Http\Controllers\api\User\Profile\UserProfileController::wallet()` (`app/Http/Controllers/api/User/Profile/UserProfileController.php`) — mirrors the existing `balance()` method's style.
- Resource: `App\Http\Resources\User\WalletTransactionResource` (`app/Http/Resources/User/WalletTransactionResource.php`).
- Route: registered in `routes/api/user.php`, right next to `balance`, inside the `Profile`-namespaced group.
- Tests: `tests/Feature/Api/User/Profile/WalletControllerTest.php` (20 tests — real balance, `available_balance`/`pending_balance` aliases, deposit/withdrawal sums, cross-user isolation, ordering, order-number linkage, cursor pagination behavior including no-overlap between pages, per-page bounds, description translation).
