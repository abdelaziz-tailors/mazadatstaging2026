# Mazadat bug fixes and verification — 2026-11-10

> Historical report for the initial seven fixes. The H1–H5 follow-up supersedes its timezone-fallback, item-update, and push-delivery descriptions. See [Critical follow-up verification](CRITICAL_FOLLOWUP_VERIFICATION_2026_11_10.md) for the current implementation, new test results, migration, and release conditions.

The working tree was clean when investigation began. All changes remain local. No deployment, production data operation, application-database migration, dependency installation, or application configuration change was performed.

## Findings and implementation

| Issue | Evidence / root cause | Result |
|---|---|---|
| 1. Sale approval recipient | `LifecycleNotificationService::transitioned()` correctly selects only `SellerSubmission.user_id`, the original seller column in this schema. There is no `seller_submissions.seller_id` column. However, the real update-FCM endpoint lets seller and subscriber retain the same device token after account switching. The job previously attempted to send a private seller receipt to that shared device. This was reproduced using both accounts' update-FCM calls and approval. | Keep the existing seller-only inbox recipient. `SendStoredNotification` now suppresses seller-request pushes when another account retains the same token, preserving the seller's inbox receipt. Unique seller devices still receive the push after seller logout. Tests cover subscriber API sessions, logout, queued delivery, duplicate approvals, and shared-device suppression. The precise production trigger remains unverified. |
| 2. Missing payer | Dashboard `VideoController::store()` allowed `nullable` and explicitly inserted NULL into the non-null commission-payer enum. API requiredness depended on linked-admin eligibility. | Both primary creation boundaries now require `buyer` or `seller`; the dashboard select is required too. Dashboard JSON validation returns 422. Mobile retains its established HTTP 200 `success:false` validation envelope. |
| 3. Past Live dates | Dashboard future-start validation applied only to `recorded`. Mobile validation also used a hardcoded Cairo fallback while application configuration specifies UTC. An invalid submitted timezone could reach parsing inside the validation callback. | Dashboard creation checks Live and recorded start instants after converting the validated submitted timezone to UTC. Mobile uses the explicit timezone or configured application timezone; Live persistence uses the same fallback. Invalid timezone input remains a validation error. |
| 4. Seller resubmission | Seller API had create/list/show routes but no update/resubmit route. Review metadata had only the latest `review_note`. | Authenticated POST or PUT `/api/user/seller-submissions/{id}` permits only the stored original seller and only `needs edit` status. It returns the request to `pending`, retains review_note and existing media, and appends review history. Only seller-editable fields are copied. Destination reassignment is rejected; ownership/status/review spoofing is ignored. Dashboard modification requests append history under a row lock. |
| 5. Seller selection | Dashboard item create/edit queried every seller; submitted IDs only needed to exist in users. Mobile item requests also accepted arbitrary existing seller IDs. The mobile seller-list endpoint already had appropriate approval filtering. | Shared `AuctionSellerEligibility` filters active, non-deleted sellers with approved, non-deleted submissions addressed to the auction organizer's user ID. Dashboard dropdowns and dashboard/mobile item writes enforce that scope. Item creation/update also enforce auction ownership, including partner dashboard isolation. |
| 6. Finance icon | Both sidebar variants used the generic `fa-file-invoice` glyph. No missing-font failure was found. | Both finance invoice links now use `fa-file-invoice-dollar`; the local Font Awesome distribution contains the glyph. Both sidebar variants are rendered in regression tests. |
| 7. Dashboard animal creation | A reproducible identity mismatch: mobile-created auctions can store an admin ID in `partner_id`, but the dashboard item form submitted that value as `user_id`. With differing IDs, posting the form returned HTTP 422, `user_id: The selected user id is invalid.` Before the fix, the rendered field contained 90001 while the organizer's user ID was 1. A second error summary in the form duplicated the layout's summary. Product creation also published the same Firebase item twice. | The form displays/submits the auction organizer (`live_videos.user_id`). Single-owner item create/update derive that ID server-side. Unknown auction IDs receive validation errors. The duplicate summary and duplicate Firebase call were removed; inline errors and the accessible shared summary remain. Valid creation now persists the lot and its pieces in regression tests. |

The unreadable screenshot's exact text could not be confirmed. The issue-7 failure above was reproduced from the real Blade form and HTTP controller boundary before correction; it is not a transcription of the screenshot. Available historical Laravel logs showed unrelated missing-asset `filemtime` failures, not evidence identifying that screenshot's validation field. Validation rejection itself does not require a Laravel exception-log entry.

## Modified files

### Runtime

- `app/Jobs/SendStoredNotification.php`: prevent private seller-request pushes to a token shared with another account; keep the original seller inbox receipt.
- `app/Http/Controllers/Dashboard/ProductController.php`: approved seller filtering and validation, ownership checks, organizer ID normalization, one injectable Firebase publication.
- `app/Http/Controllers/Dashboard/VideoController.php`: mandatory payer, future Live start, injectable Firebase publication for isolated verification.
- `app/Http/Controllers/api/Live/LiveVideoController.php`: application-timezone fallback matching validation.
- `app/Http/Requests/api/Video/AddVideoRequest.php`: payer required independently of account branch.
- `app/Http/Requests/api/Video/ValidatesAuctionSchedule.php`: application-timezone fallback and invalid-timezone guard.
- `app/Http/Requests/api/Video/item/StoreItemRequest.php` and `updateItemRequest.php`: organizer authorization and scoped seller validation.
- `app/Support/AuctionSellerEligibility.php`: shared seller eligibility query/validation closure.
- `app/Http/Controllers/api/User/SellerSubmissionController.php`: resubmission transaction; existing upload logic shared with creation.
- `app/Http/Requests/api/SellerSubmission/UpdateSellerSubmissionRequest.php`: seller ownership, partial field validation, immutable destination.
- `app/Http/Controllers/Dashboard/SellerSubmissionController.php`: append modification history without overwriting earlier notes.
- `app/Models/SellerSubmission.php`: JSON history cast.
- `app/Http/Resources/User/SellerSubmissionResource.php`: additive `review_history` response field.
- `routes/api/user.php`: authenticated resubmission routes; creation now explicitly requires the API guard.
- `database/migrations/2026_11_10_000000_add_review_history_to_seller_submissions.php`: nullable JSON history column.
- `resources/views/dashboard/pages/products/_form.blade.php`: correct organizer identity and one error summary.
- `resources/views/dashboard/pages/videos/_form.blade.php`: required payer select.
- `resources/views/dashboard/layouts/sidebar.blade.php` and `sidebar_subscriber.blade.php`: financial invoice icon.

### Regression infrastructure

- `tests/Feature/Bugfixes/AuctionInputRegressionTest.php`
- `tests/Feature/Bugfixes/AuctionSellerSelectionTest.php`
- `tests/Feature/Bugfixes/SellerSubmissionWorkflowTest.php`
- `tests/Feature/Bugfixes/FinanceNavigationTest.php`
- `tests/Support/AuctionBugfixTestCase.php`: isolated schema fixtures; external publication mocked, pushes queued/faked.
- `tests/Support/NotificationTestCase.php`: applies the new history migration to its isolated schema.
- `tests/Feature/Api/AuctionCreationValidationTest.php`: updates payer expectations and fixes stale isolated fixtures/assertions so the real endpoints are exercised. Fixtures include tables needed by current resources, quota logic, and piece schema. Optional-gender coverage retains a meaningful head row when gender is omitted.
- `phpunit.bugfixes.xml`: isolated targeted regression suite.

Auction bidding, award, order calculation, payment, wallet settlement, transfer, and notification-service implementations were not modified.

## Executed checks

PHP 8.2.12, PHPUnit 9.6.29. Sodium and ZIP were enabled only for the CLI invocations using `-d extension=sodium -d extension=zip`. Testing used SQLite memory databases or the existing subscription suite's temporary SQLite file; no application database was used. Config/routes cache paths were overridden before boot, with array cache/session/mail and faked or synchronous test queues.

| Check | Actual result |
|---|---|
| New bug-fix suite | 18 tests, 231 assertions, passed |
| Existing `AuctionCreationValidationTest` | 10 tests, 202 assertions, passed |
| Existing notification suite (`phpunit.notifications.xml`) | 116 tests, 1,257 assertions; 7 skipped; no failures |
| Existing `SellerUserControllerTest` | 9 tests, 43 assertions, passed |
| Existing `RecordedAuctionItemVideoTest` | 8 tests, 35 assertions, passed |
| Existing `AuctionBidWithdrawalTest` | 39 tests, 416 assertions, passed |
| Existing `PieceGenderTest` | 2 tests, 22 assertions, passed |
| Broader existing `SubscriptionManagementTest` | 32 tests, 262 assertions; 2 failures, 3 skipped |
| PHP lint for changed/new PHP files | Passed |
| `git diff --check` | Passed |
| `tools/check-dashboard-ui.php` | 211 Blade templates compile; 88 template previews render without database queries |

The two broader subscription failures are pre-existing Arabic rejection-wording mismatches: tests expect `تم رفض الاشتراك بنجاح`, whereas the unchanged HEAD translation `subscription_rejected_successfully` is `تم رفض الاشتراك`. `SubscriptionService.php` and that Arabic dictionary were not changed. No subscription logic was changed to satisfy an unrelated wording assertion.

Reproduce the focused suite from PowerShell:

```powershell
$env:APP_ENV = 'testing'
$env:APP_CONFIG_CACHE = 'storage/framework/testing/bugfixes-config.php'
$env:APP_ROUTES_CACHE = 'storage/framework/testing/bugfixes-routes.php'
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
$env:DB_URL = ''
$env:CACHE_DRIVER = 'array'
$env:SESSION_DRIVER = 'array'
$env:MAIL_MAILER = 'array'
$env:QUEUE_CONNECTION = 'sync'
$env:TELESCOPE_ENABLED = 'false'
php -d extension=sodium -d extension=zip vendor/bin/phpunit -c phpunit.bugfixes.xml
```

The existing isolated test files can be run individually with the same environment and `-c phpunit.notifications.xml --do-not-cache-result <test-file>`. The notification suite runs without a test-file argument. Several other repository suites require migrated MySQL fixtures; no claim is made that the entire repository suite passed.

## Database and release requirements

1. Apply the additive history migration through the normal release process before using the updated seller-review/resubmission code. It was exercised only in isolated test schemas here. It does not rewrite historical rows. Existing review_note is retained and captured when a legacy request is first processed. Notes already overwritten before this change cannot be reconstructed.
2. The seller client must invoke authenticated POST or PUT `/api/user/seller-submissions/{id}` for resubmission. Multipart clients can use POST. Existing images/video remain; new uploads are appended. The mobile client source is absent from this repository, so a client-side edit button was not implemented here.
3. Normal payload fields are `sheep_type`, `age`, `expected_price`, `description`, `notes`, optional `images[]`, and optional `video`. Do not submit partner_id. Non-owners receive 403, unsupported states 409, invalid fields 422. A successful response retains the existing success/data envelope and adds review_history.
4. Live mobile requests without timezone now use configured `app.timezone` (currently UTC), rather than the previous hardcoded Cairo fallback. Clients should send their intended IANA timezone explicitly. No timezone configuration or legacy auction data was changed.
5. No routes/cache rebuild, migration, worker restart, push verification, or deployment was executed against an application environment. Follow the normal release procedure for route/view caches and long-running workers.
6. A seller-request push to a token retained by multiple accounts is intentionally suppressed and marked complete, rather than sent to a potentially wrong account or retried repeatedly. The original seller's inbox receipt remains available regardless of login state. This change does not reassign tokens, clear stored devices, or modify unrelated notification types.

## Limits

- No production or staging reproduction, real-device push delivery, or mobile UI verification.
- No opt-in MySQL/MariaDB race suites were executed. Existing isolated notification/withdrawal coverage passed; database-engine concurrency claims are unchanged.
- The invoice icon was verified through rendered markup and the bundled glyph definition; interactive browser appearance was not inspected.
- The exact screenshot validation message remains unverified, as distinguished from the reproduced organizer/admin-ID failure.
