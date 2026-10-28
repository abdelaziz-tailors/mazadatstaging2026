# Notification lifecycle implementation and final QA audit — 2026-10-28

Implemented in the existing Laravel backend. No deployment, production connection, production data changes, dependency changes, or Flutter changes were performed.

**Final release verdict: BLOCKED for full AC01–AC10 acceptance.** AC01–AC06, AC08, and AC09 pass the backend audit. AC07 and AC10 are partial: entity-detail navigation has not been validated in Flutter, and device-level duplicate suppression after ambiguous FCM delivery remains unverified. The confirmed backend retry defect found during this audit is fixed and its regression passes. Passing backend tests is not evidence of delivery to a real device.

## Existing versus new

| Area | Existing behavior | Result |
|---|---|---|
| Upcoming auction | Inbox-only, NULL status, broad active-buyer broadcast, marker after sends | Same one-hour timing/five-minute schedule and marker; supports `pending` and NULL; targeted, localized, transactional inbox plus push |
| Auction start/end | Separate broad FCM sends; repeated calls could resend; no inbox history | One shared transition path for model updates, API and scheduler; eligible recipients; durable inbox and push |
| Other auction status | No shared trigger | Actual changes to existing `pending` supported; no invented pause/cancel states |
| Seller requests | No decision notifications | Submitter receives actual `approved/rejected/needs edit/pending` transitions |
| Purchased animals | No fulfillment notification trigger | Actual order buyer receives the six existing order states and `unpaid/paid` changes; no invented per-animal status |
| Scheduled winner congratulations | Winner-only push after award commit | Preserved; no second winner trigger introduced |
| Identity, subscription decisions/expiry, new-auction/manual broadcasts | Existing notification paths | Preserved, including legacy API contracts and broadcast visibility |

## Root causes and decisions

1. `NotificationService::notify()` only wrote history; auction FCM code lived separately in controllers/commands. The additive `notifyOnce()` path writes an atomic recipient receipt and reuses `FCMService` for delivery. Existing `notify()` callers keep their behavior.
2. Reminder selection missed current `pending` auctions, used the admin/process locale, broadcast to unrelated buyers, and could partially send before stamping the marker. The existing command now locks/rechecks and commits targeted receipts and marker together.
3. Model status writes had no common notification hook. A narrowly scoped `performUpdate()` trait locks the persisted entity and compares dirty status fields against the locked row. It wraps the write and receipts in one transaction, covers all traced writers, ignores repeated/stale saves, and permits legitimate repeated transition cycles. Each committed occurrence gets an event UUID and unique recipient key. Transaction retries must reload their models, as the existing financial code already does.
4. Recipients come from actual relationships: organizer/linked partner, current-item sellers/winners, organizer followers, item favorites, live viewers, and same-auction item participants. Active and non-deleted users only. The ambiguous `video_favorites` table belongs to `Video`, so coincidental IDs are not treated as LiveVideo interest. Buyer/order and seller-request notifications use only their persisted owners.
5. Each recipient's `app_lang` controls stored and pushed Arabic/English text independently of admin locale; missing/unsupported language falls back to Arabic. Lifecycle translations use normal Laravel translation lookup without runtime translation-file writes.
6. API start/end now enforce the organizer ownership rule already used by auction editing. The dashboard broadcast manager cannot list, view, or delete private inbox receipts. Its list action now checks the existing view permission.
7. Seller rejection referenced `reviewed_by/reviewed_at`, which repository migrations do not define. These optional audit writes are retained when present, allowing existing fresh-schema rejection workflows to work without another migration. Existing status and review-note behavior is retained.

## Changed files and impact

- Shared implementation: `app/Services/NotificationService.php`, new `app/Services/LifecycleNotificationService.php`, new `app/Models/Concerns/NotifiesLifecycleTransitions.php`; trait enabled on `LiveVideo`, `SellerSubmission`, and `Order`.
- Delivery/scheduling: new `app/Jobs/SendStoredNotification.php`, new `app/Console/Commands/RetryNotificationPushes.php`, `app/Console/Kernel.php`, `CheckRecordedVideoStart.php`, `SendUpcomingAuctionReminders.php`.
- Integration/security: `app/Http/Controllers/api/Live/LiveVideoController.php`, dashboard `SellerSubmissionController.php` and `NotificationsController.php`, `app/Http/Resources/User/NotificationResource.php`.
- Translations: new `resources/lang/{ar,en}/notifications.php`; two missing existing seller-action UI labels in each `app.php`.
- Schema: new `database/migrations/2026_10_28_000001_add_notification_delivery_ledger.php` adds nullable unique `notifications.event_key`, nullable `push_completed_at`, and a pending-delivery index. No new tables, foreign keys, backfill, status, or financial fields. Legacy rows remain untouched. Migration checks InnoDB and partial application before DDL; rollback refuses to discard used deduplication receipts.
- Tests: `phpunit.notifications.xml`, new `tests/Support/NotificationTestCase.php` and `notification_worker.php`, new service `LifecycleNotificationTest.php` and `NotificationConcurrencyTest.php`; existing inbox API and upcoming reminder tests now use the isolated fixture.
- Documentation: this report and `docs/api/notifications.md`.

No route or response-envelope changes. Existing `live_video_id` and applicable order/submission IDs are included alongside event/transition/time metadata. The existing Flutter inbox convention (`click_action=FLUTTER_NOTIFICATION_CLICK`, `screen=notification`) is reused. `navigation` contains entity metadata, not invented client route names. Push payloads additionally contain stable `notification_id` and `type`, with nested values JSON encoded for FCM.

## AC01–AC10 compliance matrix

The numbered definitions supplied for this final audit supersede the earlier implementation report's provisional coverage mapping. **PASS below means the stated backend behavior is demonstrated by source and isolated automated tests.** Actual Firebase acceptance, device receipt/display, deployed cron/worker operation, and Flutter behavior are **UNVERIFIED**; no row claims an end-to-end mobile PASS.

Test references: [LifecycleNotificationTest.php](../tests/Feature/Services/LifecycleNotificationTest.php) (**LNT**), [NotificationDeliveryAuditTest.php](../tests/Feature/Services/NotificationDeliveryAuditTest.php) (**NDAT**), [NotificationConcurrencyTest.php](../tests/Feature/Services/NotificationConcurrencyTest.php) (**NCT**), [SendUpcomingAuctionRemindersTest.php](../tests/Feature/Console/SendUpcomingAuctionRemindersTest.php) (**reminder tests**), and [NotificationControllerTest.php](../tests/Feature/Api/User/NotificationControllerTest.php) (**inbox tests**). All referenced cases passed on disposable MariaDB in this audit.

| AC | Status | Source trace, test evidence, and limits |
|---|---|---|
| **AC01 — Starting soon at configured time** | **PASS (backend)** | [SendUpcomingAuctionReminders::handle](../app/Console/Commands/SendUpcomingAuctionReminders.php) selects `pending`/NULL, locks/rechecks, and uses the existing inclusive now-to-one-hour window; [Kernel::schedule](../app/Console/Kernel.php) registers it every five minutes. Marker and receipts commit together. Reminder tests cover eligibility, future/started exclusion and repeats; LNT `test_existing_reminder_timing_pending_state_localization_and_rollback` checks 0/60/61/−1 minute boundaries and rollback; NCT `reminder` proves two competing processes serialize. NDAT verifies cron registration and SDK payload. This is the existing polling window, not an exact-to-the-second delivery SLA. |
| **AC02 — Auction started** | **PASS (backend)** | [LiveVideoController::start](../app/Http/Controllers/api/Live/LiveVideoController.php), [CheckRecordedVideoStart::handle](../app/Console/Commands/CheckRecordedVideoStart.php), the [model transition hook](../app/Models/Concerns/NotifiesLifecycleTransitions.php), and [LifecycleNotificationService::transitioned](../app/Services/LifecycleNotificationService.php) produce `auction_started` only on actual changes. LNT transition datasets, `test_scheduled_start_and_end_use_the_same_inbox_once`, relationship/exclusion tests, and non-owner rejection pass. NCT `auction` proves concurrent duplicate suppression; NDAT covers both languages through the SDK boundary. |
| **AC03 — Auction ended** | **PASS (backend)** | [LiveVideoController::end](../app/Http/Controllers/api/Live/LiveVideoController.php), [CheckLiveVideoEnd::handle](../app/Console/Commands/CheckLiveVideoEnd.php), and the same hook/service create `auction_ended`. The legacy [NULL-status expiry command](../app/Console/Commands/CheckLiveVideoStatus.php) also updates this model. LNT covers transitions including NULL→end, scheduler start/end/repeated runs, rollback, recipients and authorization. NDAT verifies the actual FCM message construction. Scheduler integration uses an auction without awardable items; the financial award/winner flow is source-traced, not end-to-end retested here. |
| **AC04 — Seller approval** | **PASS (backend)** | [SellerSubmissionController::approve](../app/Http/Controllers/Dashboard/SellerSubmissionController.php) updates the model in its existing transaction; `transitioned()` targets persisted `seller_submissions.user_id`, never the partner/admin. LNT `test_seller_dashboard_decisions_notify_only_the_submitter`, approval transition datasets, wrong-partner rejection, rollback/retry and NCT `seller` pass. NDAT covers approval payloads in Arabic/English. |
| **AC05 — Seller rejection** | **PASS (backend)** | [SellerSubmissionController::reject](../app/Http/Controllers/Dashboard/SellerSubmissionController.php) and the same hook/service send `seller_request_status_changed` with `to_status=rejected` to the submitter. LNT dashboard-decision and rejection datasets pass; `test_push_failure_retry_and_duplicate_jobs_keep_one_receipt` checks the Arabic rejection body/title against push arguments and retry. NDAT exercises rejection through a real database queue worker. Optional review-audit columns retain the existing compatibility handling; no rejection workflow change was made in this audit. |
| **AC06 — Actual purchased animal/order statuses** | **PASS (backend)** | [OrderService::updateOrderStatus](../app/Services/OrderService.php), [OrderController::update](../app/Http/Controllers/Dashboard/OrderController.php), the [orders migration](../database/migrations/2026_06_22_100000_create_orders_table.php), and `transitioned()` agree on `pending`, `confirmed`, `preparation`, `ready_for_delivery`, `shipping`, `delivered`, plus separate `unpaid/paid` events. [OrderItem](../app/Models/OrderItem.php) has no independent fulfillment state. LNT covers all 30 distinct six-state pairs, both payment directions, actual buyer selection/privacy and existing paid+pending→confirmed normalization. NCT `order` and NDAT order/payment payload cases pass. These tests do not alter or certify financial settlement calculations. |
| **AC07 — Navigation to correct entity details** | **PARTIAL** | [LifecycleNotificationService::send](../app/Services/LifecycleNotificationService.php) supplies correct `live_video_id`, `seller_submission_id`/`order_id` and `navigation.entity_type/entity_id`; [SendStoredNotification::handle](../app/Jobs/SendStoredNotification.php) preserves those fields for FCM. LNT checks IDs and the own-user API; NDAT checks actual SDK serialization. However, `screen=notification` is the existing **inbox** destination, not a verified entity-details route. Repository search found no `.dart` files; no Flutter runtime, tap handler or device test was available. Detail navigation and protection when opening the target screen are **UNVERIFIED**. This AC cannot be marked PASS. |
| **AC08 — Recipient Arabic/English independent of Admin** | **PASS (backend)** | `LifecycleNotificationService::send()` explicitly uses each user's `app_lang`; [Arabic](../resources/lang/ar/notifications.php) and [English](../resources/lang/en/notifications.php) provide titles, bodies and real status labels. LNT checks the opposite admin locale and missing-language Arabic fallback. NDAT's 14 localized event cases assert literal titles, language-appropriate bodies, resolved placeholders and stored/SDK title/body equality for all seven event types. Device font/rendering and runtime language changes remain unverified. |
| **AC09 — History, content and timestamps** | **PASS (backend)** | [NotificationService::notifyOnce](../app/Services/NotificationService.php) persists content and event time; [NotificationResource](../app/Http/Resources/User/NotificationResource.php) exposes existing history fields; [NotificationController](../app/Http/Controllers/api/User/NotificationController.php) retains pagination/read APIs and own-user scoping. Inbox tests pass. LNT checks immutable replay/read time, private-record access, rollback, legacy migration roundtrip, and dashboard broadcast isolation. NDAT `test_api_creation_event_and_read_timestamps_survive_push_and_replay` asserts exact `created_at`/`occurred_at` and stable first-read time; its real worker test asserts push retries do not rewrite content, `created_at`, `updated_at`, data or read state. |
| **AC10 — Per-event/recipient deduplication, retries/concurrency** | **PARTIAL** | **Backend receipt and completed-job deduplication PASS:** the locked transition hook compares persisted state, event UUIDs distinguish actual occurrences, and [the migration](../database/migrations/2026_10_28_000001_add_notification_delivery_ledger.php) enforces unique recipient event keys. The delivery job locks/rechecks `push_completed_at`. LNT verifies repeated/stale saves, explicit replay, legitimate cycles, rollback and transaction retry. All five NCT races demonstrate both processes waiting on the same InnoDB row before release and only one receipt/send. NDAT verifies real sync commit handling, database-worker failure/backoff/retry and recovery past a failed recipient. **Device-level deduplication UNVERIFIED:** an accepted FCM send followed by a crash before DB completion can be sent again; stable `notification_id` is supplied, but client suppression is untested. No exactly-once FCM guarantee is made. |

## Final automated results

PHP 8.2.12; all database work used isolated fixtures. These final audit runs supersede the earlier 97-case implementation run:

| Run | Passed | Failed | Skipped | Assertions |
|---|---:|---:|---:|---:|
| Notification suite, MariaDB 10.4.32 / InnoDB | 116 | 0 | 0 | 2,021 |
| Notification suite, SQLite memory | 109 | 0 | 7 | 1,257 |
| Existing subscription regression suite, SQLite | 29 | 0 | 3 | 290 |

SQLite's seven skips are the five real-process InnoDB races, the MySQL-specific scheduler integration, and migration column-removal roundtrip; all seven pass on MariaDB. Subscription skips are its existing engine-specific guards. All 27 implementation/audit PHP files passed syntax checks; `git diff --check` passed. Final JUnit summaries reported zero errors and failures for all three runs. The disposable MariaDB server was shut down after verification.

The added **19 audit cases** close gaps left by Queue/FCM service fakes: they execute real Laravel sync dispatch, database queue serialization/reservation/release and `Worker::runNextJob()` with the job's 60-second backoff. Fourteen cases execute the actual `FCMService::sendToDevice()` and Kreait `CloudMessage` construction against a mocked `Messaging::send()` boundary, checking all seven types in both languages, string-only FCM data, nested navigation JSON and the recipient's current token. **No request was sent to Firebase and no real device was used.** These results do not certify a deployed worker service, FCM credentials, network acceptance or delivery/display/tap behavior.

## Confirmed audit defect and minimal fix

**QA-NOTIF-01 — failed recovery send starved later recipients (fixed).** With the supported/default `sync` queue, the first FCM failure threw out of `RetryNotificationPushes::handle()`. The same oldest pending record could abort each sweep before subsequent users were reached. The new two-recipient regression initially failed with `RuntimeException: Notification push failed` at that command's dispatch; the valid recipient was not attempted.

[RetryNotificationPushes.php](../app/Console/Commands/RetryNotificationPushes.php) now catches a send/enqueue exception per record, logs only its notification ID, leaves it pending and continues. `test_recovery_continues_after_one_synchronous_push_fails` proves the failed receipt stays pending, the valid recipient completes, and there are still exactly two receipts. It passes on SQLite and MariaDB. No status, recipient, financial, API or schema behavior was changed.

Audit-only changed files:

- `app/Console/Commands/RetryNotificationPushes.php`: per-record recovery exception isolation.
- `tests/Feature/Services/NotificationDeliveryAuditTest.php`: 19 focused audit/regression cases.
- `phpunit.notifications.xml`: include the new isolated test class.
- `docs/NOTIFICATION_LIFECYCLE_REPORT.md`: this AC matrix, evidence, results and release verdict.

No further incorrect recipient, localization, duplicate database event, API contract or subscription regression was found in the exercised paths. A simultaneous order fulfillment/payment change legitimately produces two different event types; an auction-ended notice and the retained winner-congratulations push are also different events, not duplicate receipts for one event. The legacy winner push path was preserved and is not upgraded to the new ledger by this audit.

Run the isolated SQLite suite:

```powershell
php vendor/phpunit/phpunit/phpunit -c phpunit.notifications.xml
```

The config forces testing/SQLite and bypasses existing application config/route caches. The fixture uses `:memory:` and selected real notifications/orders/submissions migrations; it never runs `migrate:fresh` against an application database.

The existing subscription compatibility run used the same safe config with an explicit isolated test file:

```powershell
php vendor/phpunit/phpunit/phpunit -c phpunit.notifications.xml tests/Feature/Services/SubscriptionManagementTest.php
```

For engine/race checks, explicitly provide `NOTIFICATIONS_MYSQL_CONFIG` pointing to a JSON connection for a **new disposable MariaDB instance** at `127.0.0.1:33328`, database `mazadat_notifications_test`, engine `InnoDB`. These exact values are checked before schema work; URL/socket overrides are rejected. The suite creates/drops only its fixture tables in that dedicated schema. During this implementation MariaDB 10.4.32 was initialized in a fresh temporary data directory, independent of the installed application database. Workers mock queue/FCM/Firebase effects and verify both independent processes actually wait on the held InnoDB row before release.

```powershell
$env:NOTIFICATIONS_MYSQL_CONFIG = '<disposable-connection-json-path>'
php vendor/phpunit/phpunit/phpunit -c phpunit.notifications.xml
```

## Limits and operational dependencies

- Actual Flutter navigation, detail-screen handling of the new event types/entity metadata, and client-side deduplication were not tested. The existing inbox destination is reused; specific auction/order/request detail routing may need client work. No actual device push was sent.
- FCM has no atomic transaction with this database. Receipts and completed jobs are idempotent, but a process crash/ambiguous network failure after FCM accepts a send and before `push_completed_at` commits can repeat a device push. Exactly-once transport cannot be guaranteed; the stable notification ID is supplied for client deduplication.
- Existing winner-only congratulations pushes remain on their existing path; `auction_won` inbox events and `outbid` are outside this change. Initial entity creation is not a status transition.
- Bulk SQL/query-builder updates bypass model hooks; none of the traced application status writers use them. Existing scheduler/queue infrastructure must run. Missing/inactive/deleted recipients or absent device tokens do not receive pushes; a missing token leaves inbox history intact.
- The main application-wide test configuration still permits application DB defaults. The entire unisolated suite was intentionally not run. SQLite migration-column rollback is incompatible with the existing Laravel 8/DBAL 4 combination; migration roundtrip is tested on MariaDB instead. Dependencies were left unchanged.
- `push_completed_at` means the job completed, including a no-device skip; it does not prove Firebase or device delivery. A successful real `FCMService` response means provider acceptance, not display or user interaction. This audit mocked that provider boundary.
- The five process races cover auction start, seller approval, order change, reminder and stored push. They do not simulate a process kill after real FCM acceptance, network partitions, every competing mixed-status combination, or every possible deployment engine/version. The transition matrix exercises states via models; it is not proof that every theoretical pair is exposed as an authorized business workflow.
- Legacy identity/new-auction/manual/winner paths were source-traced and left unchanged by the audit; subscription and inbox compatibility were rerun. No full financial/award integration suite or real admin-session middleware/browser test was run. API fixture tests bypass the shared static API-key middleware and use Passport test authentication; they do not certify production credentials or app login.
- The job allows five attempts with 60-second backoff, but recovery can enqueue an unfinished receipt again in a later sweep. There is no global lifetime retry limit or claim of bounded queue growth for permanently invalid tokens. Monitor pending age/failures and maintain tokens; the fix prevents one failed record from starving unrelated records.

## Remaining release gates and deployment prerequisites

**Verdict: BLOCKED for the complete AC01–AC10 release.** No confirmed backend defect remains in the exercised scope after QA-NOTIF-01, but that alone cannot close the mobile acceptance gates.

1. **Close AC07 in a non-production Flutter build:** inspect the actual tap handler and verify opening the matching auction, seller request and purchased order details using these payloads. Cover foreground/background/terminated launch, old/new inbox entries, correct IDs, deleted/unavailable entities and a different logged-in account. The current `screen=notification` fallback reaches the inbox by convention; whether entity metadata is consumed is unknown. Do not invent a detail route in the backend to hide this gap.
2. **Validate real delivery and duplicate handling in staging:** use staging Firebase credentials and opted-in test devices. Confirm receipt/display/content in Arabic and English, current-token behavior, permissions, and stable-ID client suppression when the same `notification_id` is delivered again. Document at-least-once transport limitations rather than requiring or claiming exactly-once FCM delivery. AC10 remains partial until client behavior and the accepted transport boundary are evidenced.
3. **Apply the additive migration through the approved release process:** confirm staging backups, existing required columns and InnoDB on `live_videos`, `seller_submissions`, `orders` and `notifications`; apply before code/worker execution needs the new fields. The migration refuses partial schema/non-InnoDB tables and refuses rollback after deduplication receipts exist. Do not auto-convert engines, backfill production, or downgrade to code that discards receipt state.
4. **Verify operational infrastructure in staging:** Laravel scheduler cron every minute, application/auction timezone, five-minute reminder cadence, one-minute recovery, configured queue backend and workers. A database queue needs its existing jobs/failed-jobs infrastructure. Verify worker timeouts are shorter than queue reservation retry intervals and compatible with actual FCM request duration. Restart long-running workers through the normal release process and observe pending receipts/retries before approval.

None of these production or staging deployment actions was performed by this audit. The isolated MariaDB fixture uses selected real feature migrations, not a complete fresh deployment or a read of the live schema. No approval, deployment, production data mutation, real Firebase send or Flutter validation is implied by this report.
