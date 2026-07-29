# User types & roles — reference

This app has **two separate account systems** that are easy to confuse because the dashboard sidebar labels don't always match the underlying database value. This doc is the map between "what the menu says" and "what's actually queried."

## The two systems

| | Table | Auth guard | Who | Managed from |
|---|---|---|---|---|
| **Dashboard staff / organizers** | `admins` | `admin` | Anyone who can log into `/admin` | `Admin` model, Spatie roles/permissions |
| **Platform users** | `users` | `api` | Anyone using the mobile app (bidders, sellers, organizers' public-facing account) | `User\User` model |

An organizer ("مشترك") typically has **both**: a `users` row (`user_type` = `vendor` or `buyer_vendor`) for the mobile app, *and* a linked `admins` row (`type` = `partner`, `admins.user_id` → `users.id`) so they can log into their own restricted dashboard. See `App\Support\PartnerDashboardScope` — it's what limits a partner admin to only their own auctions/orders/vendors when they're logged in (`Auth::guard('admin')->user()->type === 'partner'`).

## `admins.type` (dashboard login)

| Value | Meaning | Scope |
|---|---|---|
| `admin` | Platform staff (Mazadat team) | Sees everything — no `PartnerDashboardScope` restriction |
| `partner` | An organizer's own dashboard login ("مشترك") | Scoped to their own auctions/orders/vendors only (`PartnerDashboardScope`) |

Sidebar → **"المشتركين"** (`admin.partners.index`, `PartnerController`) lists `Admin::where('type', 'partner')` — this is **not** the `users` table at all.

## `users.user_type` (platform accounts)

| Value | Arabic | Meaning | Can do |
|---|---|---|---|
| `buyer` | مشتري | Bidder | Browse/bid on live auctions, buy items. `GET /api/user/balance`'s `active_bids_count` for a buyer = auctions they've placed a bid in that are still live (`App\Http\Resources\User\BalanceResource`, see `docs/api/auctions-search-filter.md`) |
| `vendor` | تاجر / مشترك | Organizer — the account with a commercial registration + tax card that's allowed to **create auctions** (`POST /api/live/add`) | Creates/runs auctions (`LiveVideo.user_id`), subject to their package subscription's `auctions_limit`. Usually has a linked `admins` row (`type = partner`) for dashboard access. |
| `seller` | بائع | Consignor — requests to add items to an organizer's auction | Submits items (`SellerSubmissionController`), gets paid out via `OrderItem.seller_id` net settlement once sold |
| `buyer_vendor` | مشتري ومشترك | Combined account — can both bid *and* organize | Everything both `buyer` and `vendor` can do; balance/stats endpoints that are role-dependent (dues, active auctions count) combine both sides |

There is **no `admin` value** in `user_type` — dashboard staff live entirely in the separate `admins` table above.

## Dashboard sidebar → actual query (the confusing part)

This is the map that resolves "kam text tab3an mesh matching the real data" confusion:

| Sidebar label | Route | Controller | Actual filter |
|---|---|---|---|
| **المستخدمون** (Users) | `admin.users.index` (no `user_type` param) | `UserController@get_data` | **All** `users` rows, every type — this is the true grand total |
| **المشترين** (Buyers) | `admin.users.index?user_type=buyer` | `UserController@get_data` | `user_type = 'buyer'` only |
| **البائعين** (Sellers) ⚠️ | `admin.vendors.index` | `VendorController@get_data` | `user_type = 'vendor'` (**not** `seller`!) — the Arabic label says "sellers" but the query is actually for vendors/organizers. This mismatch predates this doc and hasn't been renamed to avoid a wider blast radius (route names, permission names, view paths all reference "vendor"); flagging it here so it isn't mistaken for a data bug. |
| **المشتركين** (Partners/Subscribers) | `admin.partners.index` | `PartnerController@get_data` | `admins.type = 'partner'` — a completely different table from `users` |

There is currently **no dedicated admin list page** for `user_type = 'seller'` accounts specifically — they're only visible via the "المستخدمون" (all users) list, the `UserController::index()` stat brief (`sellers` count), or indirectly through `ProductController`'s seller dropdowns when assigning an item.

## Known past bug (fixed)

`UserController::get_data()` used to hardcode `User::where('user_type', 'buyer_vendor')` **regardless of which sidebar link was clicked or what `user_type` was actually requested** — a leftover from a copy-pasted scaffold. Since there are usually few or zero `buyer_vendor` accounts, both "المستخدمون" and "المشترين" showed "لا توجد بيانات" (no data) even when the page's own stat brief above the table correctly showed real counts from the same table. Fixed to read `$request->user_type` and default to no filter (all users) when absent — see `tests/Feature/Dashboard/UserControllerStatsTest.php`.

The dashboard home page's "المستخدمين" stat card also used to exclude vendors (`user_type != 'vendor'`), showing a different number than the Users page's own total for the same label. Both now count all real account types consistently — see `tests/Feature/Dashboard/HomeChartsTest.php`.

## Relevant source

- Models: `App\Models\Admin`, `App\Models\User\User`
- Scoping: `App\Support\PartnerDashboardScope`
- Controllers: `app/Http/Controllers/Dashboard/{UserController,VendorController,PartnerController,DashboardController}.php`
- Sidebar: `resources/views/dashboard/layouts/sidebar.blade.php`
- Tests: `tests/Feature/Dashboard/UserControllerStatsTest.php`, `tests/Feature/Dashboard/HomeChartsTest.php`
