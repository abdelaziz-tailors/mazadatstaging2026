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
