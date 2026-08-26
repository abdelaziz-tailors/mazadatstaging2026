# AI Handoff

## 2026-08-26 — Production hotfix: transfer persistence and live Agora

- Bug 1 root cause/fix: existing-auction transfer now persists each cloned item
  through the destination auction relation and verifies that the saved row
  points to the destination before returning success.
- Bug 2 root cause/fix: transfer-created `live` auctions now reuse the existing
  `AgoraService` initialization path and fail/rollback if app ID, channel, or
  token cannot be generated.
- Files changed: `app/Services/AuctionItemTransferService.php` and
  `tests/Feature/Services/AuctionItemTransferServiceTest.php`.
- API endpoints affected: `/api/live/transferable/{id}`, `/api/live/transfer`,
  `/api/live/items/transferable/{id}`, and `/api/live/items/transfer`.
- Tests: hotfix service tests passed 2/2; related regression tests passed 20,
  with 2 pre-existing Passport-key environment failures.
- Production note: later Agora Cloud Recording / 48-hour replay work is not
  included in this hotfix.
- Commit: see the final hotfix commit in Git history and the deployment handoff.

## 2026-08-26 — Mobile existing-auction transfer only

- Root cause/fix: mobile existing-auction transfer now validates and verifies
  the selected target auction, persists through its relation, verifies the
  created row and copied pieces, and confirms the target response contains the
  transferred item before success is returned.
- Request contract: `item_id` is normalized into `item_ids`; the selected
  `target_auction_id` must be a `live_videos.id`. `target_location_id` remains
  only a validated auction-ID compatibility alias.
- Files changed: `app/Http/Controllers/api/Live/LiveVideoItemTransferController.php`,
  `app/Services/AuctionItemTransferService.php`,
  `tests/Feature/Api/Live/MobileExistingAuctionTransferTest.php`,
  `docs/HOTFIX_MOBILE_EXISTING_AUCTION_TRANSFER_API_HANDOFF.md`, and this file.
- Tests: `MobileExistingAuctionTransferTest` passed 5/5.
- No Agora-related file was changed, staged, committed, or included in this
  hotfix scope.
- Commit: see the final hotfix SHA in Git history and the delivery report.

## 2026-08-26 — Complete Postman API collection & documentation refresh

- Postman collection: `docs/postman/Mazadat_API.postman_collection.json`
  (Postman Collection Schema v2.1).
- Postman environment: `docs/postman/Mazadat_Production.postman_environment.json`.
- API guide: `docs/API_README.md`.
- Source of truth: the current Laravel route files (`routes/api.php`,
  `routes/api/user.php`, and everything they `include`), read via
  `php artisan route:list --path=api --json`. 166 application endpoints were
  found and every one is included in the collection (Telescope's internal
  debug-package routes were excluded — see the delivery report for the
  count and reasoning).
- Major API groups (Postman folders): Auth, User Profile, Notifications,
  Subscription, Auctions / Live Auctions, Auction Item Transfer, Auction
  Items, Auction Pieces, Bidding, Seller Submissions, Wallet, Invoices,
  Piece Services, Followers / Friends, Home, Videos, Stories, Shared /
  Lookups, Nafath (Identity Verification).
- Common headers: `Accept: application/json`, `Accept-Language: ar|en`,
  `x-api-key: {{api_key}}` (required by the `APISettings` middleware on
  every route except the two Nafath routes), and, on routes protected by
  `auth:api` (Passport), `Authorization: Bearer {{access_token}}`.
- Auction transfer clarification (documented in the collection and the
  README): `GET /live/transferable/{id}` (and its `/live/items/transferable/{id}`
  alias) only previews/validates transferable items — it performs no
  transfer. Only `POST /live/transfer` (and its `/live/items/transfer`
  alias) actually performs the transfer.
- Current transfer bug status: the mobile app was previously observed
  reading/using the wrong item ID in this flow. As reported by the team, a
  production `POST /live/transfer` call was verified to create the new item
  under the selected target auction:
  ```text
  source item 442
  source auction 393
  created item 443
  target auction 394
  transferred_from_item_id = 442
  created_item.live_video_id = 394
  ```
  This session did not have production DB/SSH access to independently
  re-verify this example; it is recorded here as reported. See
  `docs/HOTFIX_MOBILE_EXISTING_AUCTION_TRANSFER_VALIDATION_V2.md` (branch
  `hotfix/mobile-existing-auction-transfer-validation-v2`) for the local
  regression coverage of this exact scenario shape (ended source → existing
  not-started target, both `live` and `recorded` types).
- Temporary diagnostic logging (`MOBILE_TRANSFER_REQUEST` /
  `_NORMALIZED` / `_SUCCESS` / `_FAILED`, branch
  `hotfix/mobile-transfer-diagnostic-log`) exists only for troubleshooting
  and is intentionally **not** documented as part of the public API
  contract in the Postman collection or README.
- Agora note: no Agora, Cloud Recording, Replay, or 48-hour-recording work
  is part of this documentation task — none of those endpoints exist in the
  current route table, so none are documented.
- Files changed: `docs/postman/Mazadat_API.postman_collection.json`,
  `docs/postman/Mazadat_Production.postman_environment.json`,
  `docs/API_README.md`, and this file. No `app/` code was changed.
- Branch: `docs/complete-postman-api-handoff`. Commit: see Git history /
  delivery report.
