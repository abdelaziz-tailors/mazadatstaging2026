# Live auction financial concurrency audit

Scope: award, scheduled selection, order attachment/numbering, and settlement.
The verified bid controller/service were not edited. Only disposable PHP 8.4.25 /
MariaDB 12.2.2 databases were used; no development/production data was accessed.

## Confirmed defects and fixes

- POST /api/live/items/auction-award/{id} previously authorized any authenticated
  user and selected any comment ID, without matching the item or highest bid.
  Winner fields committed before order creation. Original-baseline tests proved
  unauthorized/foreign selection and an awarded item left after line failure.
- Two original attachment workers reproduced a duplicate-key error on the
  buyer/auction key. Different-auction workers reproduced a collision on the
  daily order number. Unique indexes prevented duplicate committed rows; they
  did not make award/order creation atomic.
- Attachment could move/reprice an existing item line, rewrite paid totals, or
  add lines after settlement. It now rejects conflicts and treats identical
  existing lines as no-ops.
- Scheduled closure selected video_comments.video_id = item.id, reproducing
  another item's winner. Parent closure and child orders committed separately,
  potentially excluding partially completed auctions from future scans.

Six original-implementation reproductions: 6 tests, 59 assertions, 6 expected
failures (authorization, foreign selection, partial-write rollback, concurrent
buyer-order creation, cross-auction numbering, scheduled winner selection).

Affected implementation:
- New AuctionAwardService: lock item then parent; authorize organizer; use a
  locking current read of the greatest numeric, nondeleted, top-level bid for
  the exact item/auction; reject stale/foreign selection; freeze the winner;
  commit winner, order/line and totals together. Identical retries return
  success without publishing again. UserAuctions publishes only after commit.
- LiveVideoItemController::auctionAward delegates to that service. Existing
  success resource remains; unauthorized callers get 403, and stale/business
  conflicts use HTTP 200 / success:false with a clear message.
- OrderService::attachWonItem re-reads and locks item, parent, order and line.
  Same-auction creation is serialized. New unpaid lines and totals commit
  together. Existing lines cannot move buyer/order or change price/seller.
  Archived orders and new lines on paid/settled orders fail closed. Daily number
  formatting is preserved; allocation uses a locking current maximum within
  these transactions.
- CheckLiveVideoEnd locks items in ID order then parent and rechecks closure.
  Parent closure, winners and all orders commit in one transaction. Shared
  item-scoped selection fixes the wrong-ID query; external work is after commit.
  Injected failure rolls back the parent, winners, orders and callbacks.
- These top-level transactions have three Laravel concurrency-error attempts.
  Nested deadlocks require the outermost transaction to retry as a whole.

No bid implementation, settlement formula, fee rule, ledger type, migration,
transfer/favorite or payment calculation changed. Additional lots cannot be
added to an already-paid buyer/auction order: they are rejected rather than
silently modifying settled money. Supporting another paid/unpaid order for
that same buyer/auction would need a separately designed schema change.

## Verification

New opt-in AuctionFinancialConcurrencyTest requires PHP 8.4+, testing
environment, MySQL driver, database mazadat_bid_test and
FINANCIAL_CONCURRENCY_TESTS=1. Fixtures are committed without an outer test
transaction. Independent PHP processes run real HTTP requests or the actual
attachment/settlement services. A separate connection holds row locks until
InnoDB proves both worker connection IDs are in LOCK WAIT.

Genuine races: identical awards, bid versus award, two items for one buyer,
cross-auction numbering, and repeated settlement. Publication checks use an
independent connection to verify committed winner/line visibility. Additional
tests cover stale/foreign bids, permissions, insert/ledger failure, paid retry,
immutable existing lines, post-commit failure, outer rollback, and correct/
atomic scheduled closure. Expanded focused run: 14 tests, 193 assertions pass.

Final combined verification: **74 tests, 764 assertions, zero errors/failures/skips.**
This includes all prior 60 tests / 571 assertions and 14 new financial tests /
193 assertions. PHP 8.4 syntax checks on all six changed/new PHP files and
git diff --check pass. The initial combined run exposed an ID collision with
the favorites fixture; the new financial tests now delete only their own
committed fixtures in the disposable database after workers finish. Favorites
code/tests and assertions were unchanged. The successful rerun used another
fresh database. Temporary containers, network and keys were removed afterward.

Read-only source mount, /tmp app/storage/cache, private Docker network, no
published DB ports, fresh tmpfs databases, temporary Passport keys and GD/JPEG
only in the disposable runner. Original-baseline failures and final regression
verification use separate databases. No repository secrets were loaded.

## Remaining risks and Flutter

- Firebase/FCM are best-effort after commit: crashes/network errors can lose
  events, events can reorder, and no durable outbox exists. Failed delivery
  cannot undo committed money. No live Firebase was contacted.
- Same-amount bid retries remain stale. Same-winner award retries return success
  without a second event. Timeout/uncertain commit requires refreshing server
  winner/order/history state; no idempotency-key response store was introduced.
- Settlement already locks order, line and wallets and checks settled_at. Two
  real simultaneous workers posted one debit and one seller credit. Injected
  failure restored wallet, ledger and markers. This verifies that path only:
  cross-order wallet lock cycles remain untested, and settlement defaults to
  one transaction attempt.
- PieceServiceService checks editability before its transaction without locking
  the order, then changes service fees/totals. This remains an untested payment/
  settlement race window. Live fee/seller edits, payment reversals and other
  lifecycle authorization paths are not proven safe by this work.
- Direct Order::create/number allocation outside these transactions is not
  made safe by a locking read alone. Award/backfill attachment must use the
  transactional service. Conflicting existing order/award state fails closed;
  nothing was repaired in existing databases.

Flutter must award as organizer using the current item bid ID. On success:false
refresh and display the message; handle 403 as denied. Do not auto-select a new
winner, alter a price, or infer rejection from a missing event. Reconcile after
timeouts and treat Firebase as a display feed. Paid-order conflicts require
explicit organizer resolution, not silent buyer/price changes.

Status: **VERIFIED focused award/order fixes and required 74-test regression set.**
Remaining review-only risks are not verified safe. No full-system safety claim.

Latest fee/payment and cross-order settlement follow-up: [FINAL_FINANCIAL_CONCURRENCY.md](FINAL_FINANCIAL_CONCURRENCY.md). This supersedes the earlier review-only fee-edit and single-attempt settlement findings within its tested scope.
