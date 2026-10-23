# Final fee/payment and cross-order settlement audit — 2026-10-23

Scope: unresolved service-fee/payment races and shared-wallet settlement.
Earlier bid/award implementations, financial formulas, migrations and existing
74-test fixtures were not rewritten. No production/development data was read.

## Trace and confirmed defects

API PieceServiceController and dashboard OrderPieceServiceController perform
buyer/partner ownership checks and call PieceServiceService add/update/delete.
Previously editability was checked before the write transaction, sometimes
using a cached unpaid order relation. Service rows and recalculated totals
could therefore change after payment had settled.

Payment is Dashboard OrderController::update -> OrderService::updateOrderStatus
-> AuctionWalletSettlement. Settlement locks the order/line, logs the purchase
without debiting buyer wallet funds, credits seller/partner using the existing
formulas, then marks line/order settled.

Controlled baseline workers paused each fee operation immediately before its
SQL write, let a second process commit payment, then resumed editing. Both
requests succeeded, but seller credits no longer matched service charges:
- Add 200 to a 1,000 sale: seller remained credited 1,000 instead of 800.
- Change 100 to 200: seller remained credited 900 instead of 800.
- Delete 100: seller remained credited 900 instead of 1,000.
A separate cached-unpaid-model check also wrongly allowed writing after payment.

Two orders with buyer A/seller B and buyer B/seller A acquired wallets by role
(buyer first, seller next). Gates held both buyer locks before release.
Both the actual dashboard payment path and direct settlement reproduced real
SQLSTATE 40001 / InnoDB 1213 deadlock failures. A same-role shared-wallet case
also failed with invalid SAVEPOINT trans2. The original lower-level cause of
that savepoint error was not retained; it is not presented as independently
proven corruption. No double credit was established by those failed requests.

Baseline: 9 tests, 106 assertions, 7 expected failures. Existing transactions
and settled markers were useful protections, but did not prevent these fee
inconsistencies or make single-attempt payment/settlement reliable.

## Minimal changes

- PieceServiceService: inside each write transaction, lock and reload current
  order, then line; verify binding and editability from that locked order.
  Update/delete also re-read and lock the specific service. Fee mutation and
  total recalculation now serialize with payment. Existing buyer/admin ownership,
  catalog availability, prices and HTTP paid-order rejection remain unchanged.
- AuctionWalletSettlement: lock current lines in ID order, stabilize participant
  mapping using shared item/auction/admin locks, and acquire all candidate user
  wallets in ascending primary-key order before any ledger writes. This removes
  the reproduced opposite-role wallet cycle. Existing seller/partner resolution,
  amounts, own-piece special case, buyer balance behavior and ledger fields are
  unchanged. Existing settled_at checks still prevent repeat posting.
- Dashboard OrderController: lock/reload the order and recheck partner scope and
  paid transition within each attempt. Payment and standalone settlement allow
  three whole-transaction concurrency-error attempts. Nested deadlocks must
  bubble to the outer transaction; a stale Eloquent instance is never reused
  for a payment retry.
- OrderService::updateOrderStatus: backward-compatible optional refresh argument,
  default true. Payment passes false to defer nonlocking eager reads until
  participant locks are held. All other callers keep the existing default.

Current locking reads precede wallet snapshot reads, including under the tested
MariaDB snapshot-isolation setting. Mapping locks keep participant IDs stable
while acquiring wallets. These changes do not claim that every metadata/order
lock cycle elsewhere is impossible; remaining deadlocks have bounded retries.

## Verification and isolation

New FinancialMutationConcurrencyTest plus financial_mutation_worker use
independent PHP processes and committed fixtures. Gates pause actual Eloquent
write events, while payment executes the actual dashboard controller with a
test admin. Fee requests use the real authenticated API HTTP kernel. Database
contention is verified from InnoDB LOCK WAIT for the worker connection IDs.

Fee/payment interleavings verify balances, fee rows/totals and settled state.
Cross-order cases cover opposite-role wallets, direct versus nested payment
settlement, shared same-role wallets, repeat requests and exactly one ledger
posting per line/type. The fixed opposite-role cases assert one attempt per
worker, showing ordered locking avoids the original cycle rather than merely
hiding it with retries.

Additional PDO deadlock fault injection after partial ledger writes verifies
both standalone and nested payment retries restore state and post once. This
is explicitly separate from the genuine parallel-worker tests. Buyer ownership,
paid-order rejection, cached relation rejection and fee-write rollback are
also checked. New fixtures clean up only their own disposable rows after all
workers finish; no existing tests or assertions were weakened.

- Focused new suite: 11 tests, 187 assertions PASS.
- Existing DashboardServicePriceTest: 24 tests, 301 assertions PASS under PHP
  8.4 with isolated in-memory SQLite. These preserve price behavior and are
  not evidence of concurrency safety.
- Combined MariaDB regression selection: **85 tests, 951 assertions PASS;
  zero errors, failures or skips.** All previous 74 tests / 764 assertions
  and the 11 new tests / 187 assertions executed successfully on a fresh DB.
- PHP 8.4 syntax checks on all six changed/new PHP files and git diff --check
  pass. The 24 price tests are additional to the 85-test MariaDB selection.

PHP 8.4.25; MariaDB 12.2.2; REPEATABLE READ;
innodb_snapshot_isolation=ON. Read-only repository mount, /tmp application base
and storage/cache without repository .env, private Docker network without
published database ports, fresh tmpfs databases. Passport keys and GD/JPEG exist
only in the disposable runner. Original baseline and final integration use
separate databases. No project dependency or migration changes.

## Limits and client behavior

- Ledger idempotency relies on transactional order/line locks and settled_at
  through this service. There is no new global ledger uniqueness migration or
  idempotency-key store. Direct SQL/bypassing these services is not covered.
- Fee creation remains a normal non-idempotent create: do not blindly replay
  an add request after a timeout. Refresh service rows first. Paid-order edits
  still return 422. Payment retries after a committed success do not post again.
- Three retries are bounded, not a guarantee of progress under arbitrary
  contention. Other mixed metadata, award, profile or external writer paths
  were not exhaustively scheduled. Existing refund/payment-reversal policy and
  inconsistent historical paid records were not changed or repaired.
- Firebase/FCM remain separately best-effort after commit, with possible loss
  or reordering and no durable outbox. No live notification service was
  contacted or redesigned. A missing event never proves payment rejection.
- Flutter/API clients must refresh authoritative order/fees after timeout or
  edit rejection; no financial formula or success contract changed here.

Status: **VERIFIED focused financial concurrency fixes and the required
85-test regression selection.** Temporary containers, database volumes/network
and keys were removed after verification. No full-system safety claim.