# Bid concurrency verification — 2026-10-22

## Confirmed cause and trace

`routes/api.php` includes `routes/api/user.php`. Inside `auth:api`,
`POST /api/video/auctions/add` calls `AuctionVideoController::add`, using
`AddVideoCommentRequest` (previously only required comment and existing item).
Previously the controller read the item without a lock, inserted any submitted
amount into `VideoComment` / `video_comments`, and called `FirebaseController::AddAuctions`.
There was no server-side price, increment, or active-status validation; the item
status check was commented out. A and B could both read item 1 at 1,000, both
insert 1,100, both publish, and both return success. No intervening server check
could reject B. This is confirmed directly from the original implementation.

Firebase writes `live/{auction}/item/{item}/auctions/{comment_id}` with the bid,
date, and user. No separate bid WebSocket publisher was found. Item last-auction
reads `video_comments` by descending ID. Item award copies a selected comment
into `finished_price` / `user_finished_id`, attaches it through `OrderService`,
and publishes `UserAuctions`. These award/financial operations are unchanged.

Other `VideoComment::create` paths are reply writers: they set `comment_id`
and do not set `live_video_item_id`. Ordinary item chat writes
`live_video_comments`, not bid history. No other top-level item bid writer was
found in application controllers/services. Dashboard numeric bid deletion
protections remain unchanged.

## Change and guarantees

`AuctionBidService::place` owns one transaction with up to three Laravel
deadlock/lock-timeout attempts. It first locks the item with `FOR UPDATE`, then
holds a shared parent-auction lock. All authoritative reads inside the
transaction are locking current reads. This also avoids a pre-lock snapshot
under MariaDB 12 snapshot isolation. Auction/item closure updates contend with
these locks. Any future transaction locking parent and item in reverse order
must account for deadlocks; bounded database retries remain necessary.

Under the item lock, it reads the greatest numeric, nondeleted, top-level bid,
excluding replies and legacy text, then validates auction `start`, item
`working`, deletion, unset award state, authentication, amount, and increment.
The existing permission contract is authentication; no new role, wallet,
subscription, or self-bidding policy is invented.

The opening price uses the auction start price, falling back to the legacy
item start price. The configured item `bidding` amount is the minimum increase;
larger jumps remain allowed. Money uses integer minor units, accepting plain
nonnegative decimal amounts with up to two decimal places and twelve integer
digits. Zero configured increment still requires a strictly higher amount
(one minor unit). Malformed configuration or unsupported historical numeric
precision fails closed rather than rewriting history.

There is no separate current-price/highest-user column to update. The accepted
history row is authoritative state; persisting it atomically avoids introducing
a second source of truth. Award fields remain award-only. After A commits 1,100,
B acquires the lock, reads 1,100, and receives HTTP 200 with `success:false`, a
clear message, and `data.current_price:"1100.00"`,
`data.next_minimum:"1200.00"`. Existing success response remains unchanged.
Rejected requests write no bid or winner state and register no publication.

Firebase publication is registered with `DB::afterCommit`, including when a
caller supplies an outer transaction. Rollback discards the callback. A repeat
request at an already accepted amount is stale, without another insertion or
publication. No idempotency-key response replay mechanism previously existed;
this change does not introduce one.

## Executed verification

Used disposable Docker containers, PHP **8.4.25**, MariaDB **12.2.2**, InnoDB,
REPEATABLE READ with `innodb_snapshot_isolation=ON`. The database was newly
created as `mazadat_bid_test` on tmpfs, on a separate Docker network with no
published database port. Repository source was mounted read-only; application
base/storage/cache were under `/tmp/mazadat`, without loading the repository
`.env`. Existing migrations were applied only to this database. No development
or production database was accessed or modified. No dependency or migration
changes were made.

Run the new suite only with `BID_CONCURRENCY_TESTS=1`, `APP_ENV=testing`, PHP
8.4+, MySQL driver and database `mazadat_bid_test`. The guard refuses other
database names before fixtures are written. Supply isolated DB credentials
through environment variables. Example PHPUnit invocation:

```text
php -d error_reporting=8191 vendor/bin/phpunit --do-not-cache-result tests/Feature/Api/Live/AuctionBidConcurrencyTest.php
```

**PASS: 9 tests, 138 assertions.** Two independent PHP processes boot the actual
HTTP kernel, authenticate distinct fixture users, and submit the real bid route
through routing, middleware, FormRequest and controller. A third independent
connection holds the item lock. The barrier releases only after InnoDB's engine
monitor proves both worker connection IDs are in LOCK WAIT simultaneously.
MariaDB 12 omitted these waiters from `INNODB_LOCK_WAITS`, so that table is not
used as the proof. Each successful Firebase test double verifies visibility
through another independent connection after commit.

Assertions cover equal competing bids (exactly one success), 1,100 versus 1,200
(final highest 1,200 regardless of arrival order), stale retry, subincrement and
invalid amounts, closed/awarded items, unauthenticated service calls, fractional
increments, historical maxima and reply exclusion, outer rollback, injected
insert failure, injected deadlock retry (one persisted row/publication), and
publication failure after commit. Deadlock fault injection is distinct from
the genuinely overlapping request tests.

**PASS: existing favorites + transfer service: 9 tests, 132 assertions.**

Broader focused selection:

```text
php -d error_reporting=8191 vendor/bin/phpunit --do-not-cache-result --filter 'AuctionItemTransferServiceTest|MobileExistingAuctionTransferTest|FavoritesIsolationTest|WalletControllerTest|OrderVisibleAfterConfirmationTest|CartAddressAndPaymentProofTest' tests/Feature
```

**BLOCKED full regression sign-off:** 51 tests, 133 assertions, 41 errors,
1 failure. The same invocation with the original HEAD bid controller loaded
instead of the changed controller produced exactly the same counts. The 41
errors are fixtures omitting required unique `users.user_name`, colliding on
the implicit empty string. The remaining unauthenticated-wallet test fails
with `LogicException: Invalid key supplied` because the isolated environment
has no Passport signing keys. These existing fixture/setup problems were not
changed as part of this bid fix.

All four changed PHP files pass PHP 8.4 `php -l`. `git diff --check` passes.
PHP 8.4 dependency deprecation notices were suppressed for test output only;
dependencies were not changed.

## Remaining limits and Flutter

- Firebase is best-effort after commit, without a durable outbox. Process death
  or network failure after commit can lose delivery; publication order between
  processes is not guaranteed. Consume bid IDs/current server state rather than
  treating arrival order as price authority. Live Firebase was not contacted.
- InnoDB/transactional tables are required. The historical numeric maximum
  query may become expensive for very large item histories; no schema/index
  migration was introduced.
- The legacy migrations incorrectly make `video_comments.video_id` reference
  `videos`, while this existing live API stores an auction ID. New fixtures
  create a corresponding legacy video ID to satisfy this FK without disabling
  constraints. Deployment schema was not inspected or altered.
- Manual award selection and financial calculations remain unchanged; a wider
  award/settlement concurrency audit is outside this duplicate-bid fix.
- Flutter must inspect `success`, even on HTTP 200. On rejection, show the
  message and replace displayed price/minimum from `data.current_price` and
  `data.next_minimum` (decimal strings). Do not show accepted/winning state
  optimistically or auto-increase/resubmit a rejected bid. A timeout retry at
  the same amount may return stale after the original request committed;
  refresh history to reconcile it. Keep Firebase as a display feed, with the
  backend authoritative for acceptance.

Initial status (superseded by follow-up below): **VERIFIED duplicate-bid fix; BLOCKED broader regression sign-off by
unchanged fixture/Passport setup failures.**

## Regression unblock follow-up

**VERIFIED: all 60 tests in the required focused regression set executed,
571 assertions, zero failures/errors/skips.** This is not a claim that the
entire repository test suite was run. The bidding controller/service,
concurrency tests, migrations, API, and financial implementation were not
changed during this follow-up.

The original-controller baseline on a fresh migrated database, before fixture
or environment corrections, executed 51 tests / 411 assertions with 6 errors
and 3 failures. The earlier 41-error result was amplified by a retained
empty-username user from the initial failed concurrency fixture attempt;
it was not 41 independent application regressions. The fresh run exposed
additional setup/stale-fixture issues hidden by those early collisions.

Only four existing fixture files changed:

- `MobileExistingAuctionTransferTest`, `CartAddressAndPaymentProofTest`,
  `OrderVisibleAfterConfirmationTest`, and `WalletControllerTest` now supply
  unique usernames, respecting the existing required unique schema column.
- The cart's 750-price fixture explicitly supplies `subtotal:750` as well as
  `total:750`, because the existing resource exposes subtotal. No expected
  response value changed.
- The seller pending-balance fixture now marks its sale unpaid, matching
  `OrderService::sellerWalletSummary`. A paid sale belongs in available earnings.
- The buyer available-balance fixture supplies a paid purchase with subtotal
  100. Its wallet funds are deliberately 999, so the unchanged expected 100
  verifies paid-purchase semantics rather than accidentally testing wallet funds.

No assertions were removed or weakened. The seller pending fixture mismatch
became observable only after unique usernames allowed that test to proceed.

Disposable runner configuration only:

- Installed `pdo_mysql` and GD with PNG/JPEG support. Missing GD caused two
  `UploadedFile::fake()->image()` errors. Build prerequisites were installed
  in the temporary container, without project dependency changes.
- Ran `php artisan passport:keys --length=2048 --no-interaction` with
  `APP_BASE_PATH=/tmp/mazadat`, `APP_ENV=testing`, and isolated DB environment
  variables. Keys lived only at `/tmp/mazadat/storage/oauth-{private,public}.key`;
  the private key used mode 600. No repository keys or `.env` were read,
  copied, changed, printed, or committed. This command is for a disposable
  application base only; do not run it against development or production.

Final comparison, same PHP 8.4.25 / MariaDB 12.2.2 isolated configuration:

| Run | Tests | Assertions | Errors / failures / skips |
| --- | ---: | ---: | --- |
| Original HEAD controller + corrected fixtures/environment | 51 | 433 | 0 / 0 / 0 |
| Current controller + corrected fixtures/environment + concurrency | 60 | 571 | 0 / 0 / 0 |

The final current run used the documented focused filter plus
`AuctionBidConcurrencyTest`, with `BID_CONCURRENCY_TESTS=1`; the original
controller was loaded explicitly only in the baseline run. Thus all 51 common
regressions pass under both implementations, and the 9 concurrency tests retain
their 138 assertions. PHP 8.4 syntax checks on all four modified fixture files
and `git diff --check` pass. Temporary containers/network and their keys were
removed after verification. No production/development data was accessed.

Remaining concurrency review (no implementation changes):

- Bid transactions have three bounded Laravel concurrency-error attempts.
  Retrying the same accepted amount rejects it as stale; response replay by
  idempotency key remains unsupported. Exhausted retries propagate an error;
  a timeout/uncertain commit requires reconciliation from bid history.
  Laravel does not locally retry a deadlock inside an existing outer
  transaction: the outer caller must retry that whole transaction.
- Firebase runs after commit, so rejection/rollback cannot publish success.
  Delivery still has no durable outbox: process death or network failure can
  lose an event, and competing processes can publish out of order. Clients
  must reconcile by bid ID/server state rather than event arrival order.
- `LiveVideoItemController::auctionAward` reads the selected comment before
  updating the item and attaches the order outside a single encompassing
  transaction. It does not bind that comment to the target item or verify
  the highest eligible bid under a lock. Concurrent awards or award versus
  bid can therefore select/overwrite a stale or inconsistent winner; the
  bid lock does not make that whole award flow atomic.
- Settlement locks the order, order lines, and user wallets and checks
  `settled_at`, protecting repeated processing through that path. It does
  not lock/freeze the auction item's winner alongside award selection.
  Multiple user-lock orders can also deadlock; settlement's own transaction
  has no explicit retry count beyond Laravel's default one attempt.
  These are existing risks, not covered by a genuine simultaneous
  award/settlement test here. A separate authorized change is required to
  close those gaps; no financial logic or winner-selection behavior was altered.

Status: **VERIFIED required 60-test regression set; no remaining execution
blocker for that set. Manual award/settlement concurrency and durable Firebase
delivery remain outstanding risks, not verified guarantees.**


Follow-up financial award/order audit: see [AUCTION_FINANCIAL_CONCURRENCY.md](AUCTION_FINANCIAL_CONCURRENCY.md) for newer findings, fixes, and verification scope.
