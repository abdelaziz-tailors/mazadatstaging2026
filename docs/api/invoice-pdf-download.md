# Invoice PDF Download

`GET /api/user/auction/user-invoice/{id}/pdf`

Downloads a buyer's own order as a formatted PDF invoice (Arabic, RTL). Requires an authenticated user (`auth:api` — Passport bearer token); the order must belong to the requesting user.

## Headers

| Header | Required | Notes |
|---|---|---|
| `x-api-key` | Yes | Shared API key required by the `APISettings` middleware. |
| `Authorization` | Yes | `Bearer <token>` from `/api/user/login`. |
| `Accept-Language` | Yes | `ar` or `en`. |

## Path params

| Param | Notes |
|---|---|
| `id` | The `Order` id (buyer's own order/cart checkout, not the auction id). |

## Request

```bash
curl "https://mazadat.anaamworld.com.sa/api/user/auction/user-invoice/128/pdf" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer <token>" \
  --output invoice.pdf
```

## Response

### 200 — success
Not JSON — a binary PDF stream:

```
HTTP/1.1 200 OK
Content-Type: application/pdf
Content-Disposition: attachment; filename="INV-128.pdf"

%PDF-1.7
...binary PDF bytes...
```
Saved via `--output invoice.pdf` above (or however the mobile client handles a download response) rather than printed — it renders as the Arabic RTL invoice described below.

### 200 (JSON) — order not found / not yours
Same response for both cases, so a caller can't probe which order ids exist for other users:
```json
{
  "success": false,
  "code": 200,
  "message": "invoice not found"
}
```

### 401 — missing/invalid bearer token
```json
{
  "message": "Unauthenticated."
}
```
(Laravel's own default 401 body — this endpoint sits behind the `auth:api` route-group middleware, so an unauthenticated request never reaches the controller.)

## Invoice contents

| Field | Source |
|---|---|
| Invoice number (`INV-####`) | `INV-` + the `Order` id. Distinct from `order_number` (`ORD-YYYYMMDD-#####`), which still appears in the footer. |
| Auction / item title | `Order.liveVideo.title_ar`, first `OrderItem.liveVideoItem.title_ar`. |
| قيمة المزايدة (bid value) | `Order.subtotal`. |
| عمولة المشتري (buyer commission) | `Order.commission_value` (as already stored/charged on the order). |
| عمولة البائع (seller commission) | **Computed fresh** as `LiveVideo.commission_amount% × Order.subtotal` — shown for transparency alongside the buyer's commission, independent of which party actually pays it (`commission_payer`). |
| حق الرعاية (sponsorship/hosting fee) | `Order.service_fee_total`. |
| ضريبة القيمة المضافة (VAT) | `Order.tax_value`. |
| الإجمالي (total) | `Order.total` — **not** recomputed from the lines above; this is what the buyer actually owes/paid, independent of the informational seller-commission line. |
| Status badge | `Order.payment_status` (`paid` → مدفوعة, else → مستحقة). |
| Due date | Not implemented — `Order` has no due-date column yet. Omitted from the PDF. |

## Fonts / rendering

Uses [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) (newly added dependency). Arabic text requires an embedded font with Arabic glyph coverage — dompdf's bundled fonts don't have any, so the Cairo font (OFL-1.1 licensed, via Google Fonts) is vendored directly in `resources/fonts/` (`Cairo-Regular.ttf`, `Cairo-Bold.ttf` for Arabic text, `Cairo-Latin-Regular.ttf`/`Cairo-Latin-Bold.ttf` for numbers/currency/dates — the Arabic-script subset has no digit glyphs, so numeric spans use a separate `@font-face` in `resources/views/pdf/invoice.blade.php`).

dompdf caches generated font metrics/embeds under `storage/fonts/` (created automatically on first render — make sure it's writable on deploy, same as any other `storage/` subfolder).

## Relevant source

- Controller: `app/Http/Controllers/api/User/Invoice/UserAuctionController.php` (`downloadInvoicePdf`, `invoicePdfData`)
- View: `resources/views/pdf/invoice.blade.php`
- Fonts: `resources/fonts/Cairo-*.ttf`
- Route: `routes/api/user.php` (`user/auction` prefix, `auth:api` group)
- Tests: `tests/Feature/Api/User/Invoice/InvoicePdfControllerTest.php`

## Known gaps (pre-existing, unrelated to this feature)

- `UserAuctionController::list()` has a leftover `dd(...)` debug call that will halt that endpoint — not touched by this change, flagging for a separate fix.
- No due-date field exists on `Order` yet, so the invoice doesn't show one (see table above).

---

# Seller invoice list + PDF download

## `GET /api/user/auction/seller-invoice-list`

Existing endpoint — lists the authenticated seller's own invoices (one row per order they have items in), now **extended** with a `pdf_url` per row.

### Headers
Same as above: `x-api-key`, `Accept-Language`, `Authorization: Bearer <token>`. Requires `user_type = 'seller'` — any other type gets a `403`.

### Request
```bash
curl "https://mazadat.anaamworld.com.sa/api/user/auction/seller-invoice-list" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer <token>"
```

### Response — 200
```json
{
  "success": true,
  "code": 200,
  "message": null,
  "data": [
    {
      "order_id": 87,
      "order_number": "ORD-20260709-00012",
      "invoice_id": "ORD-20260709-00012",
      "payment_status": "paid",
      "status": "confirmed",
      "auction": {
        "id": 55,
        "title": "مزاد نخبة الحلال",
        "title_en": "Sheep auction",
        "title_ar": "مزاد نخبة الحلال",
        "end_at": null
      },
      "seller": { "id": 108, "name": "زيزو بائع" },
      "totals": {
        "gross": 1000,
        "commission": 0,
        "service_fee": 20,
        "piece_services": 0,
        "net": 980
      },
      "items_count": 1,
      "items": [
        {
          "order_item_id": 201,
          "live_video_item_id": 340,
          "title": "خروف نجدي",
          "price": 1000,
          "commission": 0,
          "service_fee": 20,
          "piece_services": 0,
          "net": 980,
          "pieces": []
        }
      ],
      "pdf_url": "https://mazadat.anaamworld.com.sa/api/user/auction/seller-invoice/87/pdf?seller_id=108&expires=1783670400&signature=abc123..."
    }
  ]
}
```

### Response — 403 (not a seller account)
```json
{
  "message": "unauthorized_access"
}
```

## `GET /api/user/auction/seller-invoice/{id}/pdf` (new — public, signed URL)

Downloads **one** seller invoice — one seller's own line items within order `{id}` — as a formatted Arabic RTL PDF, same font/rendering setup as the buyer invoice above.

**This route needs no `Authorization` header at all** — unlike every other endpoint in this app, it sits *outside* the `auth:api` middleware group entirely. It's meant to be a directly-clickable link (tapped from a notification, opened in a plain browser tab, etc.), and a bare browser navigation can't attach a bearer token. Instead it's protected by Laravel's own **request signing**: the full `pdf_url` returned by `seller-invoice-list` already includes `seller_id`, `expires`, and `signature` query parameters, generated with `URL::temporarySignedRoute()` and valid for **24 hours**. Do not construct this URL by hand — always use the `pdf_url` string exactly as returned.

**No cross-seller data leakage, without relying on a logged-in user**: `{id}` is an *order* id, and an order can have items from several different sellers (e.g. a multi-consignor auction). The `seller_id` baked into the signature is what scopes the summary (`OrderService::sellerInvoiceSummariesForOrder($order, $sellerId)`), not "whichever seller owns items in this order" — so:
- A seller's link only ever shows their own lines/totals, even for an order they both partly sold into with another seller.
- Editing `seller_id` (or the order id, or `expires`) in the URL invalidates the signature — rejected with `403` before the controller ever runs, it doesn't just fall through to "invoice not found".
- An expired link (past `expires`) is rejected the same way.
- A link for an order where that seller has zero items returns the same "invoice not found" as a nonexistent order id — a caller can't tell the difference, so they can't probe which orders exist.

### Request
Just the URL exactly as returned in `pdf_url` — no extra headers needed:
```bash
curl "https://mazadat.anaamworld.com.sa/api/user/auction/seller-invoice/87/pdf?seller_id=108&expires=1783670400&signature=abc123..." \
  --output seller-invoice.pdf
```
(`x-api-key`/`Accept-Language` aren't required either — this route is outside all the usual API middleware. They're harmless to include if a shared HTTP client always sends them.)

### Response

**200 — success** (binary PDF, not JSON):
```
HTTP/1.1 200 OK
Content-Type: application/pdf
Content-Disposition: attachment; filename="INV-87-S108.pdf"

%PDF-1.7
...binary PDF bytes...
```
The invoice number is `INV-{order_id}-S{seller_id}` — distinct from the buyer invoice's plain `INV-{order_id}` — since the same order can have one PDF per seller.

**200 (JSON) — order not found, or that seller has no items in it:**
```json
{
  "success": false,
  "code": 200,
  "message": "invoice not found"
}
```

**403 — invalid, tampered, or expired signature** (Laravel's `signed` route middleware, `Illuminate\Routing\Middleware\ValidateSignature` — rejects before the controller runs).

### PDF contents

| Field | Source |
|---|---|
| Invoice number | `INV-{order_id}-S{seller_id}` |
| Auction title | `Order.liveVideo.title_ar` |
| Seller name | `sellerSummary['seller_name']` |
| Item lines table | One row per sold item belonging to this seller: القطعة (title), السعر (`finished_price`), العمولة, رسوم الخدمة, الصافي — same per-line math as `OrderService::sellerInvoiceSummariesForOrder()`. |
| إجمالي المبيعات / العمولة / رسوم الخدمة / خدمات القطع / الصافي المستحق | The seller-scoped totals (`gross`/`commission`/`service_fee`/`piece_services`/`net`) from the same summary. |
| Status badge | `Order.payment_status` (`paid` → مدفوعة, else → مستحقة). |

## Relevant source (seller invoice)

- Controller: `app/Http/Controllers/api/User/Invoice/UserAuctionController.php` (`sellerInvoiceList`, `formatSellerInvoiceOrder`, `downloadSellerInvoicePdf`, `sellerInvoicePdfData`)
- Service: `App\Services\OrderService::sellerInvoiceSummariesForOrder()`
- View: `resources/views/pdf/seller-invoice.blade.php`
- Route: `routes/api/user.php` — `seller-invoice/{id}/pdf` is registered in the **public** block (named `api.seller-invoice.pdf`, `signed` middleware), separate from the `auth:api`-protected `seller-invoice-list`
- Tests: `tests/Feature/Api/User/Invoice/SellerInvoicePdfControllerTest.php`

### Known data-consistency edge case
`OrderItem.seller_id` and `LiveVideoItem.seller_id` are normally kept in sync at order-creation time (`OrderService::attachWonItem`), but `sellerInvoiceList`'s own query filters on the `OrderItem` side while the PDF/summary groups by the `LiveVideoItem` side. If they ever diverged for a row, that order is silently omitted from the list (and the PDF endpoint returns "invoice not found" for it) rather than showing incomplete/wrong data — covered by `test_seller_invoice_list_omits_an_order_with_no_matching_summary_line` and `test_returns_failure_when_order_item_seller_id_and_live_video_item_seller_id_disagree`.
