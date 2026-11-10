# Mazadat H1–H5 follow-up verification — 2026-11-10

## Decision

**READY WITH CONDITIONS.** H1–H5 are addressed in local code and focused regression tests. Release requires the additive migrations, updated queue workers, and a mobile smoke test of generic push → authenticated inbox retrieval. The mobile application is absent from this repository, so that client behavior is not certified here. No deployment or application-database migration was executed.

Investigation began with existing unstaged changes from the seven fixes and untracked regression files. They were preserved. Nothing was staged, reset, stashed, cleaned, or committed. This report supplements and supersedes the affected descriptions in `BUGFIX_VERIFICATION_2026_11_10.md`; results below were executed during this follow-up, rather than copied from the earlier report.

## Root causes and corrections

### H1 — financial seller identity during dashboard edits

`ProductController::update()` normalized an existing item's `user_id` to its organizer. `OrderService::attachWonItem()` uses `seller_id ?? user_id` and compares that result with an existing order line, so editing a legacy owner-sold item could change its financial seller or make award retries fail.

Existing item updates now preserve their stored `user_id`. Organizer normalization remains on new single-owner item creation. Seller changes on uncommitted items still require the approved seller scope. Omitted seller controls preserve the current assignment. Retaining an existing external seller is allowed even if that seller is no longer eligible for a new selection; the edit form retains that exact historical option.

The update transaction rereads and locks the item, then its auction, matching award/order creation. It rechecks ownership and commitment under those locks. A winner, a finished price (including zero), a legacy item-level `payment_status=paid`, or an existing order line prevents changing `seller_id`. The order-line check also covers archived orders and inconsistent legacy items whose winner fields are absent. Item fields and piece synchronization commit together. Firebase publication occurs once after the transaction, through the injectable controller binding. Media upload handling otherwise retains its existing behavior.

Evidence: `app/Http/Controllers/Dashboard/ProductController.php:443`, `app/Support/AuctionItemFinancialGuard.php:11`. Tests exercise unawarded, awarded, ordered, paid, and settled owner-sold items through the real award, order, and settlement services. They verify the original seller receives one credit, the organizer receives none, and the buyer's wallet balance is unchanged.

### H2 — cross-partner deletion

`ProductController::destroy()` previously looked up any item ID and deleted it without ownership or financial checks.

Deletion now locks item → auction, enforces `PartnerDashboardScope`, and rejects financially committed items with a validation error. Ownership uses the auction's `admin_id`, so a legacy `partner_id` that coincides with another admin/user ID does not grant access. Partners can delete their own uncommitted items. Ordinary administrators require `delete video`; the existing `Super Admin` role retains administrator access. Commitment protection applies to every role.

Evidence: `app/Http/Controllers/Dashboard/ProductController.php:578`. Tests cover authorized partner deletion, colliding legacy IDs, cross-partner rejection, permission denial/grant, Super Admin access, and awarded/ordered/paid/settled/archived-order protection.

### H3 — token reassignment and delayed private pushes

The shared-device query was a snapshot before an external FCM call. Token ownership could change after that query or after FCM accepted a message. A row lock alone cannot control delayed delivery to a device.

The only live application write of `users.fcm_token` was the update-FCM endpoint. Password login, OTP registration/verification, and routed social login/registration do not register an FCM token. Logout revokes the Passport token and retains the device token; account deletion soft-deletes the user. Retaining a unique seller device after logout remains intentional. An account switch that calls update-FCM now explicitly transfers that installation to the new account.

`PushTokenOwnership` provides a durable SHA-256 token registry with one primary-key row per token. Registration and stored delivery acquire the same registry lock, then user locks in ID order. Registration clears the exact token from other accounts, including soft-deleted accounts. Candidate lookup does not lock the entire unindexed users table; only candidate primary keys are locked and their values rechecked. Registry ownership is checked using a current locking read. Historical ambiguous tokens are suppressed until registration resolves them; historical unique tokens can be claimed lazily. Registry entries for rotated tokens are harmless history: delivery also requires the user's current token to match.

An initial `INSERT IGNORE` approach produced a real shared-lock upgrade deadlock in MariaDB tests. It was replaced with an atomic upsert that takes the exclusive token-row lock immediately. Registration has transaction retry handling.

Every stored lifecycle push now contains only this public, constant payload:

```json
{
  "title": "Mazadat",
  "body": "Open the app to view your notifications.",
  "data": {
    "type": "inbox_updated",
    "screen": "notification",
    "click_action": "FLUTTER_NOTIFICATION_CLICK"
  }
}
```

No receipt ID, seller/request/order ID, status, financial details, localized private text, or navigation metadata is sent through that path. Original localized content and navigation data remain in the recipient's inbox. The existing notification API checks the API user and scopes private rows to that user. Generic alert delivery after an account switch therefore exposes no private content.

The trace also identified scheduled winner pushes that captured raw tokens in queued jobs. `CheckLiveVideoEnd` now stores one `auction_won` receipt for the winner using `notifyOnce`; it uses the same generic delivery path. Award selection and order creation were not changed. Legacy serialized `SendFCMNotification` jobs without the new public-broadcast marker are sanitized at execution, including jobs queued before deployment. New dashboard auction broadcasts explicitly opt into public content. Previously queued public broadcasts also become generic once; this is an intentional compatibility tradeoff because old jobs did not distinguish public and private content.

Public administrator broadcasts and public new-auction announcements retain their content. Unrouted clinic remnants reference a nonexistent notification method and do not register tokens; they were not activated or refactored. No other live token-registration write was found.

A missing, inactive, deleted, ambiguous, or reassigned device is terminal for that push attempt; its inbox receipt remains. Duplicate jobs serialize on the notification row and completed receipts are not resent. FCM rejection still leaves the outbox pending for retry. Exact-once delivery across FCM acceptance and database commit is not promised: a process crash in that existing external-service window can repeat a generic alert. Already accepted, pre-release FCM messages cannot be recalled.

Evidence: `app/Services/PushTokenOwnership.php:10`, `app/Jobs/SendStoredNotification.php:28`, `app/Jobs/SendFCMNotification.php:20`, `app/Console/Commands/CheckLiveVideoEnd.php:149`.

### H4 — pending Live dashboard updates

Dashboard update validated a future start only for recorded auctions. It now applies the existing UTC conversion and strict future-start check to Live and recorded updates, as creation already does. A rejected past schedule is not persisted. Existing pending-state restrictions still prevent modifying started/ended auctions. Recorded behavior remains covered.

Evidence: `app/Http/Controllers/Dashboard/VideoController.php:361`. Tests create a future auction, reject past date/time changes with unchanged stored attributes, accept a later time on the current local date, and check started/ended restrictions.

### H5 — omitted mobile timezones

The initial fix replaced the established Cairo interpretation with configured UTC. That changed the actual start instant for clients omitting timezone.

`AuctionScheduleUtc::mobileTimezone()` now resolves explicit timezone → stored timezone → legacy Cairo for Live auctions. Validation and Live persistence share that resolution for creation and partial updates, including legacy rows whose type/timezone is null. Explicit null/empty timezone falls back consistently. Timezone-only updates validate and recompute UTC fields rather than silently doing nothing. Existing recorded/photo omission behavior is retained; timezone remains optional. Invalid zones and nonexistent DST wall times fail validation.

Evidence: `app/Support/AuctionScheduleUtc.php:11`, `app/Http/Requests/api/Video/ValidatesAuctionSchedule.php:47`, `app/Http/Controllers/api/Live/LiveVideoController.php:150` and `:265`. Actual mobile controller tests verify omitted/Cairo/UTC requests in winter and summer. At 12:01 local, Cairo persists 10:01 UTC in November and 09:01 UTC in July; explicit UTC persists 12:01 UTC. Stored-zone partial updates and timezone-only updates are covered. `config/app.php` is unchanged.

## Complete local change inventory

The following includes the preserved original fixes as well as this follow-up. “Preserved” means it was already changed/untracked when this task began.

### Runtime and migrations

| File | Purpose / follow-up status |
|---|---|
| `app/Console/Commands/CheckLiveVideoEnd.php` | Move private scheduled winner delivery into the idempotent inbox/outbox. |
| `app/Http/Controllers/Dashboard/ProductController.php` | Preserve original filtering/create fixes; protect financial identity, transactional edits, scoped/authorized deletion, injectable update publication. |
| `app/Http/Controllers/Dashboard/SellerSubmissionController.php` | Preserved modification history under row lock. |
| `app/Http/Controllers/Dashboard/VideoController.php` | Preserve creation validation; validate future Live updates and mark new public broadcast jobs. |
| `app/Http/Controllers/api/Live/LiveVideoController.php` | Shared legacy Live timezone interpretation; timezone-only updates; injectable creation services for isolated endpoint tests. |
| `app/Http/Controllers/api/User/Profile/UserProfileController.php` | Register tokens through coordinated ownership service. |
| `app/Http/Controllers/api/User/SellerSubmissionController.php` | Preserved owner resubmission and media append logic. |
| `app/Http/Requests/api/User/Profile/UpdateFcmRequest.php` | Require a string token, maximum 4,096 characters; preserve response envelope. |
| `app/Http/Requests/api/Video/AddVideoRequest.php` | Preserved mandatory commission payer. |
| `app/Http/Requests/api/Video/ValidatesAuctionSchedule.php` | Shared timezone fallback, null compatibility, DST validation, timezone-only update validation. |
| `app/Http/Requests/api/Video/item/StoreItemRequest.php` | Preserved API ownership and scoped seller selection. |
| `app/Http/Requests/api/Video/item/updateItemRequest.php` | Preserved API ownership and scoped seller selection. |
| `app/Http/Requests/api/SellerSubmission/UpdateSellerSubmissionRequest.php` | Preserved new seller-only resubmission request. |
| `app/Http/Resources/User/SellerSubmissionResource.php` | Preserved additive review history field. |
| `app/Jobs/SendFCMNotification.php` | Generic delivery for old/unmarked jobs; explicit public-broadcast opt-in. |
| `app/Jobs/SendStoredNotification.php` | Coordinated ownership lookup and generic private-notification alert. |
| `app/Models/SellerSubmission.php` | Preserved history cast. |
| `app/Services/PushTokenOwnership.php` | New token claim/delivery synchronization. |
| `app/Support/AuctionItemFinancialGuard.php` | New shared committed-item check, used with item/auction locks. |
| `app/Support/AuctionScheduleUtc.php` | Shared mobile timezone resolver. |
| `app/Support/AuctionSellerEligibility.php` | Preserved approved/organizer-scoped seller query. |
| `database/migrations/2026_11_10_000000_add_review_history_to_seller_submissions.php` | Preserved additive nullable JSON history migration. |
| `database/migrations/2026_11_10_000001_create_push_token_owners_table.php` | New additive token registry; no existing-row rewrite or guessed ownership backfill. |
| `resources/views/dashboard/layouts/sidebar.blade.php` | Preserved finance icon. |
| `resources/views/dashboard/layouts/sidebar_subscriber.blade.php` | Preserved finance icon. |
| `resources/views/dashboard/pages/products/_form.blade.php` | Preserve organizer/error-summary fixes; retain historical selected seller on edit. |
| `resources/views/dashboard/pages/videos/_form.blade.php` | Preserved required payer input. |
| `routes/api/user.php` | Preserved authenticated seller resubmission routes. |

### Tests, configuration, and documentation

| File | Purpose / follow-up status |
|---|---|
| `phpunit.bugfixes.xml` | Preserved isolated suite; new regressions discovered through its directory. |
| `phpunit.notifications.xml` | Include new opt-in MariaDB token concurrency tests. |
| `tests/Feature/Api/AuctionCreationValidationTest.php` | Preserved earlier payer and isolated fixture corrections. |
| `tests/Feature/Services/LifecycleNotificationTest.php` | Expect generic push while preserving localized receipt/retry assertions. |
| `tests/Feature/Services/NotificationDeliveryAuditTest.php` | Verify actual mocked Firebase SDK payload excludes private data. |
| `tests/Feature/Services/PushTokenOwnershipConcurrencyTest.php` | New independent-process token race tests with demonstrable InnoDB waits. |
| `tests/Feature/Bugfixes/AuctionInputRegressionTest.php` | Preserve prior validation tests; expect Cairo fallback again. |
| `tests/Feature/Bugfixes/AuctionScheduleCompatibilityTest.php` | New dashboard update, actual mobile create/update, winter/summer/DST and partial-update coverage. |
| `tests/Feature/Bugfixes/AuctionSellerSelectionTest.php` | Preserve scope tests; assert one mocked Firebase call for each create/update. |
| `tests/Feature/Bugfixes/FinanceNavigationTest.php` | Preserved both-sidebar rendering and error summary tests. |
| `tests/Feature/Bugfixes/FinancialItemSafetyTest.php` | New real award/order/settlement, historical seller form, deletion ACL, and scheduled winner tests. |
| `tests/Feature/Bugfixes/PushTokenOwnershipTest.php` | New registration, deleted-account reuse, inbox isolation, invalid token, rotation, legacy serialized-job and public broadcast coverage. |
| `tests/Feature/Bugfixes/SellerSubmissionWorkflowTest.php` | Preserved seller receipt/logout/resubmission history coverage; runs with coordinated token registration. |
| `tests/Support/AuctionBugfixTestCase.php` | Add real order-items migration to isolated fixture. |
| `tests/Support/NotificationTestCase.php` | Apply/clean token registry only in isolated schemas; preserve history migration. |
| `tests/Support/push_token_worker.php` | New allow-listed MariaDB worker, barriers and mocked FCM. |
| `docs/qa/BUGFIX_VERIFICATION_2026_11_10.md` | Preserve initial report; add pointer to superseding follow-up. |
| `docs/qa/CRITICAL_FOLLOWUP_VERIFICATION_2026_11_10.md` | This report. |

No intentional translation dictionary change remains. The existing translation helper generated one English validation key during endpoint tests; only that test-generated line was removed. Git can still show the dictionary's pre-existing working-tree/index line-ending status even though its content diff is empty.

## Executed verification

PHP 8.2.12 / PHPUnit 9.6.29. Sodium and ZIP enabled only through CLI `-d` arguments. Tests used SQLite memory/temporary databases and a separately initialized MariaDB 10.4.32 instance under a unique Windows temporary directory, bound to `127.0.0.1:33328`, with only `mazadat_notifications_test`. Config/route cache paths were overridden before application boot. External publication and push delivery in the new tests were mocked; no real Firebase notifications were sent.

| Check | Actual result during follow-up |
|---|---|
| Final focused bugfix suite | **48 tests, 587 assertions, passed** |
| MariaDB token ownership + existing notification concurrency tests | **8 tests, 149 assertions, passed** |
| Notification suite using SQLite | **119 tests, 1,031 assertions; 10 skipped; no failures** |
| Existing auction creation validation | **10 tests, 202 assertions, passed** |
| Existing bid withdrawal | **39 tests, 416 assertions, passed** |
| Existing transfer persistence | **5 tests, 51 assertions, passed** |
| Existing piece gender | **2 tests, 22 assertions, passed** |
| Existing recorded item video | **8 tests, 35 assertions, passed** |
| Existing seller-list controller | **9 tests, 43 assertions, passed** |
| Existing subscription management | **32 tests, 262 assertions; 2 failures, 3 skipped** |
| Final PHP lint | **42 changed/new PHP/Blade files, no failures** |
| Dashboard UI checker | **211 templates compile; 88 previews render without database queries** |
| `git diff --check` | **Passed** |

The two subscription failures are the previously documented Arabic wording mismatch at `SubscriptionManagementTest.php:302`: expected `تم رفض الاشتراك بنجاح`, actual `تم رفض الاشتراك`. The same actual string is present in unchanged HEAD. Neither `SubscriptionService.php` nor the Arabic dictionary was modified. Earlier development runs exposed and corrected the new test fixture's cached Eloquent column-list problem, a missing subscription fixture, and the token-lock deadlock described above. The final passing runs supersede those intermediate failures.

The race tests used independent PHP processes and confirmed lock waits using InnoDB state/status. They cover two simultaneous claims of one token, registration blocked behind a mocked in-flight push, and a push blocked behind an uncommitted reassignment. Existing races also verify concurrent lifecycle changes and duplicate push jobs. The in-flight test checks the entire outbound payload, not merely the recipient ID. SQLite tests are not used as evidence of InnoDB equivalence.

Focused financial tests clear only Eloquent's test-process column cache after creating their extended isolated schema. This prevents a smaller schema from an earlier test causing `wallet_balance` to be silently excluded by mass-assignment column discovery. Runtime mass-assignment rules are unchanged.

Reproduce SQLite checks using the environment block in the original report and `php -d extension=sodium -d extension=zip vendor/bin/phpunit -c phpunit.bugfixes.xml --do-not-cache-result`. Use `-c phpunit.notifications.xml` for the notification suite. For MariaDB, set `NOTIFICATIONS_MYSQL_CONFIG` to a JSON connection file for a freshly initialized disposable database; the fixture and worker enforce driver `mysql`, host `127.0.0.1`, port `33328`, database `mazadat_notifications_test`, and empty URL/socket. Then run `-c phpunit.notifications.xml --filter 'PushTokenOwnershipConcurrencyTest|NotificationConcurrencyTest' --do-not-cache-result`. Do not point this destructive test fixture at an application database.

The disposable server was shut down after testing. Its temporary files/config remain outside the repository for inspection; no service was installed. No production configuration, dependencies, or production data were changed. Blade rendering can regenerate ignored compiled views under `storage/framework/views`; no application cache-clear command was run.

## Financial, security, and compatibility assessment

- Core bidding, award selection, order calculation, payments, wallet settlement, transfer, and subscription service implementations have no Git diff. The scheduler change affects winner notification delivery only. Actual award/order/settlement integration and existing withdrawal/transfer tests passed. The full repository suite and existing opt-in PHP 8.4 financial race suites were not executed on this PHP 8.2 environment.
- Existing original-seller receipts, payer validation/response envelopes, seller resubmission/history, scoped seller creation, both finance icons, and organizer/piece creation tests remain covered by the focused suite. The historical-seller exception applies only to retaining the exact existing assignment; new selections remain scoped.
- Mobile validation retains HTTP 200 `success:false`; dashboard JSON validation uses 422. Token registration's success envelope and logout behavior remain. Private inbox resources retain their original localized title/body/data. The new push payload intentionally requires fetching those details using the current authenticated account.
- All runtime token registration must continue through the coordinated endpoint/service. Direct database writes to `users.fcm_token` bypass coordination and should not be introduced. The registry stores only a token hash and user ID; raw tokens remain in the existing users column. Legacy-token duplicate discovery still performs a read scan of that unindexed column, but unrelated user rows are not locked.
- The prior review's separate resubmission-video ordering concern was not changed by this H1–H5 task. Original screenshot text, real-device pushes, mobile UI, and interactive sidebar appearance remain unverified as described in the original report.

## Release requirements

1. Apply both additive November 10 migrations through the normal release process **before** serving updated seller-review/token registration code or starting updated notification workers. The new registry migration was tested only in isolated schemas and does not rewrite historical data. It has a primary key on a 64-character hash and an indexed nullable user ID; no token-index alteration of the legacy text column is required. Rolling back the registry while updated code is running is unsafe; coordinate code rollback/worker shutdown first.
2. Ensure ordinary administrators intended to delete items have `delete video` through existing role management. Partners remain restricted to their own auctions, and the existing `Super Admin` role remains supported. No permissions or application rows were changed during this task.
3. Restart all long-running notification and scheduler workers with the new code. Old worker processes still running old code can expose old private payloads. Old queued token-addressed jobs must execute under the updated sanitizer. Already accepted FCM messages cannot be recalled. New public broadcast jobs preserve public content; old unmarked public jobs display a generic alert once. Historical winner jobs contained tokens rather than recipient IDs, so their missing inbox receipts are not reconstructed by guessing ownership; new winner events have durable receipts.
4. Verify the mobile app handles `type=inbox_updated`, `screen=notification`, and the existing click action by opening/refetching `/api/user/notifications` with the current API credentials. It must register its token after account switching, avoid reusing cached private receipt details across accounts, and must not depend on private entity IDs in FCM data. This smoke test is a release gate because client source/runtime was unavailable here.
5. Keep application timezone configuration unchanged. Existing timezone-less Live requests retain Cairo semantics; explicit valid IANA zones remain supported. No legacy auction schedule rows were rewritten. Recorded/photo omission behavior is retained.
6. Follow the existing release process for route/view caches and seller resubmission client integration. No cache rebuild, migration, deployment, or production worker restart was performed here.

No remaining H1–H5 code blocker was identified in this review. Readiness remains conditional on these release steps and mobile compatibility verification; the unrelated subscription wording failures remain visible rather than being changed to obtain a green full-suite claim.
