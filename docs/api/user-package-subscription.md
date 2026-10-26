# User package subscription — plan, status, and renewal

This is about the **user's package/plan subscription** (اشتراك المستخدم بالباقة) — the plan that grants their account an auction-creation limit — not "subscribing" to a specific auction.

> Route/file naming note: the underlying controller class is still `AuctionSubscriptionController` (a pre-existing name from before this doc) and the model is `UserSubscription`/`Package` — renaming those would touch a wider surface (namespaces, other references) than was asked here. The **routes** were renamed to `subscription-*` (dropping the misleading `auction-` prefix) since that's the part API consumers actually see.

All endpoints below are under `/api/user/subscription*`. Every request needs `x-api-key` (required on all `/api/*` routes) and `Accept-Language` (`ar`/`en`). The four endpoints other than `subscription-plans` also sit behind the `auth:api` route-group middleware (`routes/api/user.php`) — an unauthenticated request gets Laravel's own `401` before the controller even runs, so no `Authorization: Bearer <token>` means no response body to show, just a 401.

Controller: `App\Http\Controllers\api\User\Profile\AuctionSubscriptionController`.

---

## `GET /api/user/subscription-plans`

Public (no auth). Lists active packages (`packages.is_active = 1`), each with its localized feature bullet list.

### Request
```bash
curl "https://mazadat.anaamworld.com.sa/api/user/subscription-plans" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar"
```

### Response
```json
{
  "success": true,
  "code": 200,
  "message": "Successfully",
  "data": [
    {
      "id": 3,
      "name": "الباقة الذهبية",
      "description": "اشتراك سنوي بمزايا كاملة",
      "features": ["إنشاء عدد غير محدود من المزادات", "بث مباشر عالي الجودة"],
      "auctions_limit": 20,
      "monthly_price": 300,
      "annual_price": 2999,
      "image": "packages/xxx.png"
    },
    {
      "id": 5,
      "name": "الباقة الفضية",
      "description": "اشتراك شهري",
      "features": [],
      "auctions_limit": 5,
      "monthly_price": 100,
      "annual_price": 900,
      "image": "admins/default.png"
    }
  ]
}
```
`features` is `[]` for any package the admin hasn't filled in yet (see `docs/user-types-and-roles.md` and the admin form note at the bottom of this doc) — it's never missing/null, always an array.

---

## `GET /api/user/subscription-status`

Returns the current approved subscription, including expired/exhausted subscriptions, with expiry, package details, and package features. A newer Pending request appears separately in `data.pending_request`. When there is no approved subscription, `data.subscription` falls back to the latest request. This is the endpoint the "my subscription" screen should call.

### Request
```bash
curl "https://mazadat.anaamworld.com.sa/api/user/subscription-status" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer <token>"
```

### Response — user has an active subscription
```json
{
  "success": true,
  "code": 200,
  "message": "Successfully",
  "data": {
    "has_subscription": true,
    "subscription": {
      "id": 42,
      "status": "approved",
      "subscription_type": "annual",
      "price": 2999,
      "auctions_limit": 20,
      "remaining_auctions": 15,
      "started_at": "2026-03-15",
      "expires_at": "2027-03-15 00:00:00",
      "package": {
        "id": 3,
        "name": "الباقة الذهبية",
        "description": "اشتراك سنوي بمزايا كاملة",
        "features": ["إنشاء عدد غير محدود من المزادات", "بث مباشر عالي الجودة"],
        "coin": 0,
        "price": 2999,
        "image": "packages/xxx.png",
        "subscription_type": "annual",
        "auctions_limit": 20,
        "monthly_price": 300,
        "annual_price": 2999
      },
      "rejection_reason": null
    },
    "message": "Your subscription is active"
  }
}
```

### Response — user never subscribed
```json
{
  "success": true,
  "code": 200,
  "message": "Successfully",
  "data": {
    "has_subscription": false,
    "message": "You need to subscribe to create auctions"
  }
}
```
Note there's no `subscription` key at all in this case.

### Response — rejected subscription (example of the other `data.message` states)
```json
{
  "success": true,
  "code": 200,
  "message": "Successfully",
  "data": {
    "has_subscription": true,
    "subscription": {
      "id": 41,
      "status": "rejected",
      "subscription_type": "monthly",
      "price": 300,
      "auctions_limit": 5,
      "remaining_auctions": 5,
      "started_at": "2026-07-01",
      "expires_at": "2026-08-01 00:00:00",
      "package": { "...": "..." },
      "rejection_reason": "Invalid payment proof"
    },
    "message": "Your subscription was rejected"
  }
}
```

`data.message` (the standing top-level `message` field is always `"Successfully"` — the human-readable status is inside `data.message`) reflects the subscription's actual state: pending approval, rejected (with `subscription.rejection_reason`), active, or "not active" (approved but expired or no auctions remaining).

`started_at` is the subscription row's `created_at` date (there's no separate "approved at" timestamp on `user_subscriptions`).

---

## `POST /api/user/subscription` (subscribe)

Unchanged existing endpoint (only its route path was renamed, from `auction-subscription`) — creates a new `pending` subscription awaiting admin approval.

### Request

| Field | Type | Required | Notes |
|---|---|---|---|
| `package_id` | int | Yes | Must exist in `packages`. |
| `subscription_type` | `monthly` \| `annual` | Yes | Which price/duration to use. |
| `transaction_image` | file (jpeg/png/jpg, max 2MB) | No | Payment proof, reviewed by the admin before approval. |

```bash
curl -X POST "https://mazadat.anaamworld.com.sa/api/user/subscription" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer <token>" \
  -F "package_id=3" \
  -F "subscription_type=annual" \
  -F "transaction_image=@/path/to/receipt.jpg"
```

### Response
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription created successfully  Waiting for admin approval ",
  "data": {
    "id": 43,
    "user_id": 106,
    "package_id": 3,
    "subscription_type": "annual",
    "auctions_limit": 20,
    "remaining_auctions": 20,
    "expires_at": null,
    "price": "2999.00",
    "image": "user/transaction_image/54321_receipt.jpg",
    "status": "pending",
    "updated_at": "2026-07-09T12:00:00.000000Z",
    "created_at": "2026-07-09T12:00:00.000000Z"
  }
}
```
(That double-space in the message is a pre-existing translation-string artifact from period characters being stripped — see `TranslationHelper::translate`, not something introduced here.)

---

## `POST /api/user/subscription/renew` (new)

Renews the subscription: creates a new `pending` `UserSubscription` — same admin-approval workflow as a first-time subscribe, just without having to re-look-up which package/billing-cycle the user is already on.

### Request
All fields optional — default to the user's most recent subscription's package and billing cycle:

| Field | Type | Notes |
|---|---|---|
| `package_id` | int | Pass to switch plans while renewing. Defaults to the current package. |
| `subscription_type` | `monthly` \| `annual` | Pass to switch billing cycle. Defaults to the current cycle. |
| `transaction_image` | file | Optional payment proof, same as `subscribe`. |

**Renew on the same plan** (simplest call, no body needed):
```bash
curl -X POST "https://mazadat.anaamworld.com.sa/api/user/subscription/renew" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer <token>"
```

**Renew while switching plans:**
```bash
curl -X POST "https://mazadat.anaamworld.com.sa/api/user/subscription/renew" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer <token>" \
  -F "package_id=5" \
  -F "subscription_type=monthly"
```

### Response — success
Same shape as `subscribe` — the newly created (pending) `UserSubscription` row:
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription renewal requested successfully  Waiting for admin approval ",
  "data": {
    "id": 44,
    "user_id": 106,
    "package_id": 3,
    "subscription_type": "annual",
    "auctions_limit": 20,
    "remaining_auctions": 20,
    "expires_at": null,
    "price": "2999.00",
    "image": null,
    "status": "pending",
    "updated_at": "2026-07-09T12:05:00.000000Z",
    "created_at": "2026-07-09T12:05:00.000000Z"
  }
}
```

### Response — nothing to renew
Fails when the user has never subscribed at all (they should use `subscribe` instead):
```json
{
  "success": false,
  "code": 200,
  "message": "No subscription to renew"
}
```

---

## `GET /api/user/subscription-history`

Unchanged existing endpoint (only its route path was renamed) — all of the user's past subscriptions, newest first.

### Request
```bash
curl "https://mazadat.anaamworld.com.sa/api/user/subscription-history" \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Authorization: Bearer <token>"
```

### Response
```json
{
  "success": true,
  "code": 200,
  "message": "Successfully",
  "data": [
    {
      "id": 44,
      "user_id": 106,
      "package_id": 3,
      "subscription_type": "annual",
      "auctions_limit": 20,
      "remaining_auctions": 20,
      "expires_at": "2027-07-09T00:00:00.000000Z",
      "price": "2999.00",
      "status": "pending",
      "created_at": "2026-07-09T12:05:00.000000Z",
      "package": {
        "id": 3,
        "name": "الباقة الذهبية",
        "description": "اشتراك سنوي بمزايا كاملة",
        "features": ["إنشاء عدد غير محدود من المزادات", "بث مباشر عالي الجودة"],
        "coin": 0,
        "price": 2999,
        "image": "packages/xxx.png",
        "subscription_type": "annual",
        "auctions_limit": 20,
        "monthly_price": 300,
        "annual_price": 2999
      }
    },
    {
      "id": 41,
      "user_id": 106,
      "package_id": 3,
      "subscription_type": "monthly",
      "status": "rejected",
      "created_at": "2026-07-01T09:00:00.000000Z",
      "package": { "...": "..." }
    }
  ]
}
```

---

## Relevant source

- Controller: `app/Http/Controllers/api/User/Profile/AuctionSubscriptionController.php` (`getPlans`, `getStatus`, `subscribe`, `renew`, `createPendingSubscription`, `getHistory`)
- Models: `App\Models\Package` (`featuresList()`), `App\Models\UserSubscription` (`package()` relation — now also selects the raw `features` JSON column)
- Migration: `database/migrations/2026_07_09_090000_add_features_to_packages_table.php`
- Routes: `routes/api/user.php` (`subscription-plans`, `subscription-status`, `subscription-history`, `subscription`, `subscription/renew`)
- Admin side: package `features` are edited via one Arabic-only textarea (one bullet per line), mirrored to `en` automatically — same pattern as `name`/`description` on this form. See `resources/views/dashboard/pages/packages/_form.blade.php`, `App\Http\Requests\Dashboard\Package\{Store,Update}PackageRequest`.
- Tests: `tests/Feature/Api/User/Profile/AuctionSubscriptionControllerTest.php`
- Postman: `docs/postman/Mazadat-User-Auth.postman_collection.json` → "Auction Subscription" folder

## Subscription management enhancement (2026-10-26)

Requests continue to use the existing `user_subscriptions` table and API routes.
An active, expired, or exhausted subscriber can request any available package or
renew at any time. Only one Pending request is accepted; another returns Laravel's
422 validation response. Pending requests cannot be cancelled or deleted.

Request submission saves `package_snapshot` (the original multilingual package
attributes), `duration_months`, price, and auction quota. New Pending requests have
no start or expiry date. Pending requests return `expires_at: null`.
Approval uses only saved request values, including when a package is edited or
soft-deleted after submission. Legacy requests have no reconstructable package
snapshot; their already-saved cycle, price, and quota are retained and used.

Only Pending requests can transition to Approved or Rejected (the existing API
value for Declined). Approval starts immediately, with calendar-month duration
clamped at month end, and restores the requested full quota. Existing approved
rows retain their status, original dates, and unused balance for history, but
receive `replaced_at` and cease to grant entitlement. Their unused time/quota is
forfeited. Retrying either decision is a no-op; a rejected or replaced request
cannot be reactivated. Decline leaves the current subscription unchanged.

`GET /api/user/subscription-status` preserves its existing response envelope and
subscription fields. It adds `pending_request`, `used_auctions`, `is_active`,
`replaced_at`, and `package_snapshot`. `started_at` retains its date-only format;
new Pending requests return null. The authenticated history endpoint
`GET /api/user/subscription-history` remains separate and returns only the
caller's records, including full timestamp and snapshot fields. The live
`package` relation remains for client compatibility; use `package_snapshot` for
historical package details. `GET /api/user/subscription-plans` adds `is_current`
when a valid API bearer token is supplied, while retaining public access.

Partner Dashboard users use the existing subscriptions page to see available
packages, the current package, dates, quota, and history, and submit requests via
`POST /admin/user-subscriptions`. Approval and rejection require their existing
`approve user subscription` / `reject user subscription` permissions and are
always forbidden to partner admins. Review pages require `view user subscriptions`.
Ensure intended reviewers have these permissions; formerly commented-out checks
are now enforced. Historical subscription deletion is forbidden for all users.

All existing auction creation paths call `SubscriptionService`: Dashboard
`VideoController::store`, Mobile `LiveVideoController::add`, and new-auction item
transfers. Quota decrement and insertion share a transaction and subscriber-row
lock. Failed inserts and rolled-back transfers spend zero; updates/deletion
never return quota. Transfers into an existing auction spend zero. Expired or
exhausted subscriptions cannot fall back to an older approved balance. Decision
notifications use `NotificationService` and commit atomically with the decision;
external payments, auction prices, orders, wallets, and invoices are unchanged.

### Migration and verification instructions

Do not run these commands against Production as part of this task. No application
migrations, push, or deployment were performed. The test suite applies the new
migration only to disposable SQLite test files.

1. On an isolated local/staging database with the existing schema, apply only the
   additive migration before enabling the changed application code:
   `php artisan migrate --path=database/migrations/2026_10_26_000001_add_subscription_request_snapshots.php`.
2. The migration adds four nullable columns and a `(user_id, status)` index. It
   does not backfill, reset, or delete historical records. Rollback refuses populated snapshot/lifecycle fields and preserves the FK index;
   see the MariaDB audit below before considering any code or schema rollback.
3. Use PHP 8.2 with the required extensions. The runtime audit below fixes the
   two incompatible locked dependencies. Run
   `php vendor/phpunit/phpunit/phpunit tests/Feature/Services/SubscriptionManagementTest.php --do-not-cache-result`.
   The suite builds its own disposable SQLite schema and never connects to the
   configured application database. Tests cover renew/switch, active/expired/
   exhausted entitlement, Pending restrictions, snapshots, idempotent decisions,
   notifications and rollback, failed creation, deletion, Dashboard/Mobile
   consistency, rendered Dashboard content, history authentication/ownership,
   and independent-process races for requests, approval, and the last quota unit.
4. On an isolated migrated application test database, also run the existing
   `AuctionSubscriptionControllerTest`, `AuctionItemTransferServiceTest`, and
   Mobile transfer/creation suites. Existing new-auction transfer fixtures now
   include a subscriber; fixture-only direct model saves remain unchanged.

Initial implementation validation: 16 isolated tests / 102 assertions passed using PHP 8.4,
including the process races. PHP reports an existing Laravel dependency
nullability deprecation. That initial run did not cover MariaDB; the follow-up
audit below supersedes that limitation with isolated MariaDB integration tests. Use
transactional database tables (InnoDB on MySQL) and validate on the deployment
engine in staging. Existing duplicate Pending requests are deliberately not
modified by the migration; new requests remain blocked until an admin resolves
all existing Pending requests for that subscriber. Legacy exact approval times
and historical package attributes cannot be reconstructed; the existing status
API's legacy start-date fallback remains intact. Future auction creation code
must use the same service rather than inserting directly through the model.

Changed files:

- `app/Services/SubscriptionService.php`: shared requests, decisions, quota, locking.
- `app/Models/UserSubscription.php`: lifecycle casts, quota usage, current entitlement.
- `app/Console/Commands/SendSubscriptionExpiryReminders.php`: exclude replaced subscriptions.
- `app/Http/Controllers/api/User/Profile/AuctionSubscriptionController.php`: existing API integration and additive status fields.
- `app/Http/Controllers/Dashboard/UserSubscriptionController.php`: partner requests and authorized admin decisions.
- `app/Http/Controllers/Dashboard/VideoController.php`, `app/Http/Controllers/api/Live/LiveVideoController.php`, `app/Http/Controllers/api/Live/LiveVideoItemTransferController.php`, `app/Services/AuctionItemTransferService.php`: shared transactional quota handling.
- `routes/admin.php`: enable the existing resource's store action.
- `resources/views/dashboard/pages/user-subscriptions/{index,show,actions}.blade.php`, `resources/views/dashboard/layouts/sidebar.blade.php`: existing Dashboard components and navigation.
- `resources/lang/{ar,en}/app.php`: subscription labels.
- `database/migrations/2026_10_26_000001_add_subscription_request_snapshots.php`: additive snapshot/lifecycle fields and index.
- `tests/Feature/Services/SubscriptionManagementTest.php`, `tests/Feature/Services/AuctionItemTransferServiceTest.php`: isolated coverage and subscribed transfer fixtures.
- `docs/api/user-package-subscription.md`: API, migration, validation, and risk handoff.

## MariaDB / PHP 8.2 audit (2026-10-26)

This audit continues the implementation above. It adds no subscription tables,
parallel services, or API routes, and changes no auction price, order, wallet,
invoice, payment, or settlement logic. No Production database was contacted.

### Findings and minimal fixes

1. The actual runtime blockers were locked dependencies, not subscription syntax:
   `lcobucci/clock` 3.6.0 requires PHP 8.4; `maennchen/zipstream-php` 3.2.0 requires
   64-bit PHP 8.3. `composer.json` now resolves for PHP 8.2.0, and only these two
   packages changed: clock 3.3.1 and zipstream 3.1.2. PHP 8.2.12 boots the application
   and passes every installed Composer platform requirement. Local XAMPP required
   sodium and zip to be enabled per command; verify these in both CLI and PHP-FPM.
   No platform checks were bypassed and no vendor files are part of the patch.
2. A fresh, separate MariaDB 10.4.32 instance used a new temporary data directory,
   TCP 127.0.0.1:33317, and `mazadat_subscription_test`. All 135 discovered
   application/vendor migrations completed. `users`, `user_subscriptions`,
   `packages`, `live_videos`, `notifications`, `live_video_items`, and
   `live_video_item_pieces` were all InnoDB. Server isolation was REPEATABLE READ;
   the Laravel connection keeps its existing non-strict SQL-mode configuration.
   Production engines/version/isolation remain unverified because Production
   operations are explicitly outside this task.
3. The migration's composite index covered the user foreign key, so InnoDB removed
   the redundant original single-column FK index. A plain rollback could then
   fail while dropping the composite index. The migration now restores a leading
   user index when necessary. It also refuses rollback if any new snapshot or
   lifecycle field is populated. Roundtrip tests preserved a legacy row's price,
   quota, dates, and status without backfilling its new nullable fields.
4. Migration preflight now refuses missing/non-InnoDB required tables, partial or
   already-present snapshot columns, or an existing equivalent user/status index.
   Checks run before additive DDL. Separate empty probe schemas proved both the
   non-InnoDB and equivalent-index guards stopped before adding any columns.
   There is no automatic engine conversion, index cleanup, unique-index creation,
   historical data rewrite, or duplicate-request cleanup.
5. Requests, decisions, and quota consumption serialize on the same existing
   subscriber row, with locking reads of Pending/current records inside the same
   transaction. These reads avoid stale REPEATABLE READ snapshots. Actual process
   races held that user row in a third connection and verified BOTH workers were
   waiting on it in `SHOW ENGINE INNODB STATUS` before release. Request/request,
   approve/approve, approve/decline, and last-unit auction/auction races passed.
6. Existing Dashboard code treated NULL status as Pending, while the service did
   not. The existing model now supplies a shared Pending scope/predicate used by
   submission, decisions, status API, and the Dashboard request button. Stock
   MariaDB status is NOT NULL; custom legacy nullable status is covered in SQLite.
7. Legacy duplicate Pending requests remain intact and block both new submissions
   and approval. An admin must inspect the originals, explicitly decline unwanted
   duplicates with a reason, then approve the one remaining intended request.
   Declining every duplicate also permits a fresh request. Approval is refused
   when saved duration/cycle or quota is missing/invalid; no current package values
   are invented and the existing subscription remains untouched. Valid legacy
   requests still use their saved cycle, price, and quota with a NULL snapshot.

InnoDB row locks require a transaction, as described in MariaDB's
[FOR UPDATE documentation](https://mariadb.com/docs/server/reference/sql-statements/data-manipulation/selecting-data/for-update).
MariaDB DDL causes implicit commits; it cannot be made safe by wrapping migration
statements in `DB::transaction`. MariaDB 10.4 also predates the 10.6 atomic DDL
improvements. Drain writers and plan for metadata locks, backups, and partial-DDL
recovery. See [implicit commits](https://mariadb.com/docs/server/reference/sql-statements/transactions/sql-statements-that-cause-an-implicit-commit)
and [atomic DDL](https://mariadb.com/docs/server/reference/sql-statements/data-definition/atomic-ddl).

### Test evidence and limits

- PHP 8.2.12 / isolated migrated MariaDB: **59 tests, 808 assertions; 58 passed,
  1 expected skip** for the custom NULL-status schema. Suites:
  `SubscriptionManagementTest`, `AuctionSubscriptionControllerTest`,
  `AuctionItemTransferServiceTest`, `MobileExistingAuctionTransferTest`, and
  `SendSubscriptionExpiryRemindersTest`.
- PHP 8.2.12 / disposable SQLite: **24 tests, 138 assertions; 21 passed,
  3 expected skips** for MariaDB DDL/full HTTP/transfer schema tests.
- Coverage includes snapshot edits, approval retries, concurrent decisions,
  failed insert and transaction retry, notification failure rollback, expired and
  exhausted entitlement, renewal/switch, duplicate legacy requests, immutable
  legacy migration data, deletion, history ownership/authentication, and actual
  Dashboard/Mobile creation. HTTP tests mock Firebase/FCM and fake the queue;
  authentication, form validation, database persistence, and quota remain real.
  The Dashboard's pre-existing locale redirect is bypassed in that quota test.
- A wider exploratory 68-test run found six unrelated failures in
  `AuctionCreationValidationTest`: its private SQLite schema lacks `live_videos`.
  That test switches away from MariaDB itself; no product source was changed for
  those failures. A missing unique username in the subscription API fixture was
  fixed, and the relevant suite passed. Twenty-three pre-existing opt-in
  authorization compatibility cases were skipped by their original separate-DB
  guards. This audit does not claim the entire repository suite is green.
- Composer platform checks, changed PHP-file lint, and whitespace checks passed.
  No dependency runtime guards were disabled. PHP 8.4's pre-existing Laravel
  deprecations are not the PHP 8.2 compatibility issue.

### Reproduce on a separate Windows MariaDB test instance

Use a fresh data directory, not an existing application server. These commands
are only for an explicitly disposable local server. The opt-in test suite refuses
any other database name, host, port, nonempty URL/socket, or non-testing app.
Check that port 33317 is free before starting; do not connect to a different
server already occupying it.

```powershell
$testRoot = Join-Path $env:TEMP ('mazadat-subscription-mariadb-' + [guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $testRoot
& C:/xampp/mysql/bin/mysql_install_db.exe --datadir=$testRoot --port=33317 --silent
Start-Process -FilePath C:/xampp/mysql/bin/mysqld.exe -WindowStyle Hidden -ArgumentList @(
  '--no-defaults', ('--datadir=' + $testRoot), '--port=33317',
  '--bind-address=127.0.0.1', '--default-storage-engine=InnoDB', '--skip-log-bin'
)
# Wait for this new server to report ready, and verify @@datadir matches $testRoot.
& C:/xampp/mysql/bin/mysql.exe --no-defaults --protocol=TCP --host=127.0.0.1 --port=33317 --user=root -e 'SELECT VERSION(), @@datadir; CREATE DATABASE mazadat_subscription_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
$env:APP_CONFIG_CACHE = $testRoot + '/absent-config.php'
$env:APP_ROUTES_CACHE = $testRoot + '/absent-routes.php'
$env:APP_ENV = 'testing'
$env:DB_CONNECTION = 'mysql'
$env:DB_HOST = '127.0.0.1'
$env:DB_PORT = '33317'
$env:DB_DATABASE = 'mazadat_subscription_test'
$env:DB_USERNAME = 'root'
$env:DB_PASSWORD = '(empty)'
$env:DB_SOCKET = '(empty)'
$env:DATABASE_URL = '(null)'
$env:TELESCOPE_ENABLED = 'false'
$env:CACHE_DRIVER = 'array'
$env:SESSION_DRIVER = 'array'
$env:QUEUE_CONNECTION = 'sync'
# Fresh isolated schema only; never run the full historical migration set on Production.
& C:/xampp/php/php.exe -d extension=php_sodium.dll -d extension=php_zip.dll artisan migrate --force --no-interaction
$env:SUBSCRIPTION_MARIADB_TESTS = '1'
& C:/xampp/php/php.exe -d extension=php_sodium.dll -d extension=php_zip.dll vendor/phpunit/phpunit/phpunit --filter 'SubscriptionManagementTest|AuctionSubscriptionControllerTest|AuctionItemTransferServiceTest|MobileExistingAuctionTransferTest|SendSubscriptionExpiryRemindersTest' tests --do-not-cache-result
# Default SQLite mode:
Remove-Item Env:SUBSCRIPTION_MARIADB_TESTS
& C:/xampp/php/php.exe -d extension=php_sodium.dll -d extension=php_zip.dll vendor/phpunit/phpunit/phpunit tests/Feature/Services/SubscriptionManagementTest.php --do-not-cache-result
& C:/xampp/mysql/bin/mysqladmin.exe --no-defaults --protocol=TCP --host=127.0.0.1 --port=33317 --user=root shutdown
```

These are test-only root credentials on a fresh loopback server. Do not copy them
into application environment files or deployments. Test cleanup deletes only its
own fixture IDs; it never truncates or resets an existing application database.
After shutdown, remove only the temporary directory you created, after verifying
its resolved path. No database dump or temporary helper belongs in Git.

### Exact safe deployment sequence (operator-run, not performed here)

1. In staging, use the intended PHP 8.2 CLI and web runtime with required native
   extensions. Install from this exact lock file; do not run `composer update`:
   `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction`,
   then `composer check-platform-reqs --no-dev`. Ensure Composer itself uses the
   intended PHP binary. Verify CLI and PHP-FPM separately.
2. Obtain a verified backup/recovery point through the normal DBA process, outside
   Git. Record migration status and run the following **read-only** preflight on
   the operator-selected database. Production execution needs separate operator
   authorization; none was performed during this audit.

```sql
SELECT DATABASE(), VERSION(), @@default_storage_engine;
SHOW VARIABLES WHERE Variable_name IN ('transaction_isolation', 'tx_isolation');
SELECT TABLE_NAME, ENGINE
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('users','user_subscriptions','packages','live_videos',
                    'notifications','live_video_items','live_video_item_pieces');
SHOW CREATE TABLE user_subscriptions;
SHOW INDEX FROM user_subscriptions;
SELECT user_id, COUNT(*) AS pending_count
FROM user_subscriptions
WHERE deleted_at IS NULL AND (status = 'pending' OR status IS NULL)
GROUP BY user_id HAVING COUNT(*) > 1;
SELECT id, user_id, subscription_type, auctions_limit, price, status
FROM user_subscriptions
WHERE deleted_at IS NULL AND (status = 'pending' OR status IS NULL)
  AND (subscription_type IS NULL OR auctions_limit <= 0);
```

3. Stop if a required table is absent/non-InnoDB, the snapshot migration is partly
   applied, or an equivalent index already exists. Do not convert engines or alter
   history automatically. A DBA must review a separate plan. The migration also
   enforces these schema checks when first applied; if an older copy is already
   recorded as applied, the explicit preflight is still required.
4. Drain HTTP writers, queue workers, scheduled jobs, and open transactions under
   the normal maintenance procedure. Do not mix older writers with the new
   service: there is deliberately no new UNIQUE index that would invalidate
   legacy duplicate Pending rows. All application instances must use the shared
   service before writes resume.
5. Apply **only** this additive migration, using the reviewed database config:
   `php artisan migrate --path=database/migrations/2026_10_26_000001_add_subscription_request_snapshots.php --force --no-interaction`.
   Do not run `migrate:fresh`, rollback unrelated batches, or the full pending
   historical migration set on an existing deployment. DDL is not transactional;
   after any interruption inspect columns, indexes, and the migration repository
   before retrying. Preflight intentionally refuses a partial schema.
6. Activate the reviewed release, refresh normal application caches, and restart
   workers on that same release. Grant intended reviewers the existing view,
   approve, and reject subscription permissions; partner admins cannot decide.
   Smoke-test approval/decline notifications, current versus Pending status,
   available plans, history ownership, and Dashboard/Mobile quota consistency in
   staging before an independently authorized Production rollout.
7. Resolve legacy duplicates through explicit audited admin declines, then approve
   the intended surviving request or submit a new one. Never issue automatic SQL
   cleanup, inferred quota/price updates, or historical resets.
8. After use, **retain the additive schema**. The `down()` method refuses populated
   lifecycle/snapshot fields. Pre-feature application code ignores `replaced_at`
   and can revive forfeited entitlement, so a blind code rollback is also unsafe.
   Use a compatible forward fix; any rollback release must preserve replacement,
   snapshot, Pending, and quota semantics. No rollback or recovery data is inferred.

### Files changed by this follow-up audit

- `composer.json`, `composer.lock`: PHP 8.2 platform and the two incompatible packages.
- `app/Services/SubscriptionService.php`, `app/Models/UserSubscription.php`: shared legacy Pending handling and explicit guards for duplicate/incomplete requests.
- `app/Http/Controllers/api/User/Profile/AuctionSubscriptionController.php`, `resources/views/dashboard/pages/user-subscriptions/index.blade.php`: reuse that shared Pending predicate.
- `database/migrations/2026_10_26_000001_add_subscription_request_snapshots.php`: engine/schema/index preflight and guarded FK-safe rollback.
- `resources/lang/{ar,en}/app.php`: the two validation messages.
- `tests/Feature/Services/SubscriptionManagementTest.php`: extend the existing tests with guarded migrated MariaDB mode, real lock-wait barriers, HTTP/transfer integration, legacy and migration cases.
- `tests/Feature/Api/User/Profile/AuctionSubscriptionControllerTest.php`, `tests/Feature/Services/AuctionItemTransferServiceTest.php`: valid unique-username fixtures for the real migrated schema.
- `AI_HANDOFF.md`, this document: evidence, limits, and deployment instructions.

No push, merge, deployment, Production operation, engine conversion, historical
cleanup, secret, database dump, `.rnd`, or `.claude` artifact is included.
