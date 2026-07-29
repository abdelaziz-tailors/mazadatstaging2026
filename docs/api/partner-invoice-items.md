# Partner Invoice Items

`GET /api/user/auction/partner-invoice-items` — one consolidated invoice per **auction** for the authenticated organizer, grouped by consignor seller within it, with a full per-item breakdown. Used by the organizer/vendor app to review what's owed to each seller, and what the organizer himself earns.

Requires an authenticated user (Passport, `auth:api` middleware). Restricted to organizer accounts (`user_type` of `vendor` or `buyer_vendor`) — anything else gets a `403`.

Scope: auctions (`LiveVideo`) where the authenticated user is the auction's own organizer (`LiveVideo.user_id`) or its partner (`LiveVideo.partner_id`) — never another organizer's auctions.

## One invoice per auction, not per buyer order

A single auction can have several different buyers, each ending up with their own `Order` row (e.g. buyer A wins item 1, buyer B wins item 2, both in the same live auction). Per explicit request, this endpoint merges **all of an auction's orders into a single invoice**, instead of returning one invoice per buyer order — the organizer only ever sees one invoice per auction, with every seller/piece from every buyer's order rolled into it.

Because of this, the invoice is no longer keyed by a single `Order`:

- `auction_id` / `invoice_id` — the auction's own id (`invoice_id` is this same id, as a string). There is no `order_id`, `order_number` at this level anymore.
- `payment_status` / `status` are **dropped** from the top level — those are per-`Order` concepts, and a single auction's merged orders can be in different payment states at once. `orders_count`, `paid_orders_count`, and `unpaid_orders_count` are exposed instead for transparency.
- Each line item still carries its own `order_id` / `order_number` (which buyer order it actually came from), since that detail is still meaningful at the line level.

## `partner_earnings` — what the auction owner earns

Every level this endpoint returns totals at — the line item, the seller totals, and the auction (invoice) totals — includes a `partner_earnings` field alongside the existing flat `commission` / `service_fee` / `piece_services` / `net` keys. This is the field the app shows as "الإجمالي المستحق" (total amount due to the organizer).

- **Normal consignor's line** (someone other than the organizer sold the piece): `partner_earnings = commission + service_fee + piece_services` — what the platform credits the organizer for running the auction on that piece.
- **The organizer's own piece** (he is also the `seller_id` on that lot): he doesn't pay himself a commission — he keeps the full sale `price`, **plus** the `service_fee` and `piece_services` that would otherwise have been deducted from him as if he were a regular consignor (added on top of the price, not subtracted from it):
  `partner_earnings = price + service_fee + piece_services`.
- Seller-level and auction-level `partner_earnings` are always the sum of their lines'/sellers' own `partner_earnings` — the same bottom-up total, never recomputed independently.

```json
"totals": {
  "gross": 1200.0,
  "commission": 60.0,
  "service_fee": 10.0,
  "piece_services": 0.0,
  "partner_earnings": 460.0,
  "net": 1130.0
}
```

This reshaping is **scoped to this endpoint only**. The sibling `seller-invoice-items` endpoint and the admin dashboard's invoice view still consume `OrderService::sellerInvoiceSummariesForOrder()`'s original per-order flat shape, untouched.

## `seller_name` marks the organizer's own piece

When a "seller" group in `sellers[]` is actually the auction owner's own piece (`seller_id` equals the authenticated organizer's own id), `seller_name` has `" (صاحب المزاد)"` ("Auction Owner") appended, e.g. `"عبدالعزيز جمال (صاحب المزاد)"` — so the app can visually tell it apart from a real consignor's group without a separate flag. A normal consignor's `seller_name` is untouched.

## Request

```
GET /api/user/auction/partner-invoice-items
Authorization: Bearer {token}
x-api-key: {api key}
Accept-Language: ar|en
```

No parameters.

## Response — success

```json
{
  "success": true,
  "code": 200,
  "message": null,
  "data": [
    {
      "auction_id": 7,
      "invoice_id": "7",
      "auction": {
        "id": 7,
        "title": "مزاد الأغنام",
        "title_en": "Sheep auction",
        "title_ar": "مزاد الأغنام",
        "end_at": "2026-07-20 18:00:00"
      },
      "orders_count": 2,
      "paid_orders_count": 1,
      "unpaid_orders_count": 1,
      "totals": {
        "gross": 1400.0,
        "commission": 60.0,
        "service_fee": 30.0,
        "piece_services": 0.0,
        "partner_earnings": 460.0,
        "net": 1310.0
      },
      "sellers_count": 2,
      "items_count": 3,
      "sellers": [
        {
          "seller_id": 108,
          "seller_name": "حمزة بائع",
          "totals": {
            "gross": 500.0,
            "commission": 25.0,
            "service_fee": 15.0,
            "piece_services": 0.0,
            "partner_earnings": 40.0,
            "net": 460.0
          },
          "items_count": 1,
          "items": [
            {
              "order_id": 42,
              "order_number": "ORD-000042",
              "order_item_id": 91,
              "live_video_item_id": 399,
              "title": "قطعة ضاني 1",
              "price": 500.0,
              "commission": 25.0,
              "service_fee": 15.0,
              "piece_services": 0.0,
              "partner_earnings": 40.0,
              "net": 460.0,
              "pieces": [
                {"id": 113, "piece_number": 1, "age": "تام", "weight": 80, "identifier": "ضاني 1", "baham_count": 3}
              ]
            }
          ]
        },
        {
          "seller_id": 55,
          "seller_name": "عبدالعزيز جمال",
          "totals": {
            "gross": 900.0,
            "commission": 35.0,
            "service_fee": 15.0,
            "piece_services": 0.0,
            "partner_earnings": 420.0,
            "net": 850.0
          },
          "items_count": 2,
          "items": [
            {
              "order_id": 43,
              "order_number": "ORD-000043",
              "order_item_id": 92,
              "live_video_item_id": 401,
              "title": "قطعة ضاني 2",
              "price": 900.0,
              "commission": 35.0,
              "service_fee": 15.0,
              "piece_services": 0.0,
              "partner_earnings": 420.0,
              "net": 850.0,
              "pieces": []
            }
          ]
        }
      ]
    }
  ]
}
```

## Response — not authenticated

```json
{"success": false, "code": 401, "message": "Un Authenticated"}
```

## Response — authenticated, but not an organizer account

HTTP `403`.

## Fixed: organizer's own piece silently missing

Grouping/filtering used to key off `LiveVideoItem.seller_id`, which is `null` for a self-owned piece that was never given an explicit seller — so an auction with, say, 3 pieces (2 sold by consignors, 1 the organizer's own) would only return 2 lines, silently dropping the organizer's own piece from every total. Fixed to key off `OrderItem.seller_id` instead — the field `OrderService::attachWonItem()` always resolves (falling back to the item's own `user_id`, the organizer, when no consignor was set). A genuine mismatch between the two (both explicitly set, but disagreeing) is still treated as a data anomaly and excluded, unchanged from before.

## Relevant source

- Controller: `App\Http\Controllers\api\User\Invoice\UserAuctionController::partnerInvoiceItemList()` / `formatPartnerInvoiceAuction()` / `sumSellerSummaries()` / `partnerEarningsForLine()` (`app/Http/Controllers/api/User/Invoice/UserAuctionController.php`).
- Underlying numbers: `App\Services\OrderService::sellerInvoiceSummariesForLiveVideo()` (`app/Services/OrderService.php`) — consolidates every `Order` belonging to the auction. Mirrors the per-line math of `sellerInvoiceSummariesForOrder()` (still used, unchanged, by the seller-invoice-items endpoint and the admin dashboard invoice view) but iterates across all of an auction's orders instead of a single one.
- Piece services: `App\Services\PieceServiceService::sumItemServicesForOrderItem()`.
- Route: `routes/api/user.php`, inside the `auction`/`Invoice` group.
- Tests: `tests/Feature/Api/User/Invoice/PartnerInvoiceItemsTest.php`.
