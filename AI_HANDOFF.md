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
