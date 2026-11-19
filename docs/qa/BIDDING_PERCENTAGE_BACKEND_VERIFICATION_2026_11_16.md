# Bidding percentage backend investigation — 2026-11-16

## Finding and scope

The required visibility matrix is already implemented in the checked-out backend
by commit `b2ddd1d`. No additional production-code change is justified by the
available evidence. The reported failing response cannot be attributed to a
specific backend defect without its stored values and authenticated identity.
This investigation does not claim to reproduce that external failure or verify
the mobile client or a deployed environment.

The worktree was initially clean on `main`. No applicable AGENTS.md was found.
The project is Laravel 8 / PHP with Passport API authentication and PHPUnit 9.
The mobile Auction Details client is not present in this repository.

## Route-to-response trace

`RouteServiceProvider` registers `routes/api.php` under `/api` and the API
middleware group. That file wraps `routes/api/user.php` in `APISettings`.

* `GET /api/home/video/{id}` is public (API key required, bearer identity optional).
  It calls `Home\HomeVideoController::show`, which retrieves
  `LiveVideo::find($id)` and returns `HomeVideoResource`.
* `GET /api/live/video/{id}` additionally requires `auth:api`. It calls
  `Live\LiveVideoController::SingleVideo`, which retrieves
  `LiveVideo::with('currentVideoItems')->find($id)` and returns
  `SingleLiveVideoResource`.
* Both resources, and `MyLiveVideoResource` used by owner lists/update responses,
  delegate `auction_fees` to the same `AuctionFeesResource`. There is no detail
  service or column-restricting select in either detail query.

`LiveVideo` uses the `live_videos` table, guards only `id`, and defines no
commission casts/accessors. The fee attributes are read directly from that
model. `video_items()` is a has-many relation to `LiveVideoItem` on
`live_video_id`; its `seller_id` identifies the actual consignor. Item `user_id`
identifies the creator and is not used as a substitute for the consignor.
An owner-owned item can have null `seller_id`, with access retained through
the auction's `user_id`.

`AuctionFeesResource::canViewCommission` resolves the viewer through
`$request->user('api')->getAuthIdentifier()` (Passport), not a request-supplied
ID, global `user_type`, `isHost`, or Agora UID. It rejects guests, non-auction
models and unrecognized payers. For recognized payers it permits buyer-paid
commission for authenticated viewers; seller-paid commission additionally
requires auction ownership or a matching item `seller_id` in that auction.
The resource returns the existing keys, replacing restricted commission values
and payer with null. `service_fee` is always passed through unchanged.

## Interpretation of the partial screenshot

In `HomeVideoResource`, `agora.uid` is the auction model's `user_id`, not the
authenticated viewer ID. The value `"145"` therefore does not establish who
requested the response or whether that person is a consignor.

`HomeVideoController::show` sets `isHost` through a separate admin-related
condition, or null when that condition is absent. Fee authorization does not
read it. A linked seller without that admin condition receives `isHost: null`
and still receives the configured commission.

All-null `auction_fees` can be reproduced on the public detail endpoint by a
guest viewing a seller-paid auction with null stored `service_fee`, even when
the stored commission is non-null. The same record returns the commission to
its authenticated linked seller, with the same Agora UID and null `isHost`.
This is a verified possible explanation, not a claim about the screenshot's
actual request.

Other null paths are an unrecognized/null stored payer (fails closed), an
authenticated viewer unrelated to a seller-paid auction, or optional null
stored amount fields. Since `service_fee` is unfiltered, its null value cannot
be caused by commission visibility filtering alone. An authorized seller with
a valid payer and null stored amounts receives that payer string, not a
fabricated percentage or zero.

## Creation and update trace

The existing fee migration defines nullable integer `commission_amount`,
`commission_payer` enum (`buyer`, `seller`, default `buyer`), and nullable
integer `service_fee`. It was inspected, not changed or run against application
data.

Mobile creation (`POST /api/live/add`) validates payer as required and restricted
to `buyer,seller`. `AddVideoRequest` requires a numeric commission for subscriber
account types and allows it to be nullable for other account types. The
controller passes both fee fields and service fee explicitly to
`SubscriptionService::createAuction`. That service constructs the model and
saves it transactionally after quota checks without rewriting the fees.
Dashboard `VideoController::store` also requires a recognized payer and passes
the submitted fee fields explicitly to the same service. Optional commission
and service amounts may legitimately be null.

Mobile update validates through `UpdateVideoRequest`; the controller restricts
the record to the authenticated owner and pending status, then persists only
validated submitted fields. Fee updates survive a subsequent title-only update,
as verified through the real update and detail routes for both payer values.
Dashboard update likewise explicitly includes the submitted fee fields.
Its full-form update semantics were preserved. The update request permits null
payer at validation level; whether that can be persisted depends on database
constraints. It is not evidence of the screenshot's cause and was not changed.

## Expected response behavior

For a stored percentage of 5 and service fee of 20:

| Viewer | Stored payer | commission_amount | commission_payer | service_fee |
|---|---|---|---|---|
| Linked seller / auction owner | seller | 5 | seller | 20 |
| Buyer without seller/owner relationship | seller | null | null | 20 |
| Linked seller / auction owner | buyer | 5 | buyer | 20 |
| Buyer | buyer | 5 | buyer | 20 |

Existing authenticated non-participant behavior for buyer-paid auctions is
preserved. Global seller account type alone never grants seller-paid visibility.
Guests and unknown payers do not expose the commission. No diagnostic identity
fields or response-contract changes were added.

## Changes and verification

Only `tests/Feature/Api/BiddingPercentageVisibilityTest.php` and this report were
changed. Added cases cover account-type independence, null consignor ownership,
null/invalid payer responses, the screenshot-like UID/host/null-fee combination,
and fee persistence through real partial-update routes. Existing coverage checks
the four-case matrix across detail/list/nested resources, recursive response
leaks, forged viewer fields, cross-auction membership, invoices and calculations.

All database writes during tests use disposable SQLite `:memory:` fixtures with
isolation assertions. No existing auction records, schema, migrations, payer
values or application data were changed. No seed/reset, commit, push or deployment
was performed. No frontend verification is claimed.

Executed with local PHP 8.2.12:

```text
php vendor/bin/phpunit -c phpunit.visibility.xml
OK (106 tests, 1780 assertions)

php vendor/bin/phpunit -c phpunit.visibility.xml tests/Feature/Bugfixes/AuctionInputRegressionTest.php --filter 'test_dashboard_and_mobile_reject_missing_or_invalid_payer|test_valid_payer'
OK (3 tests, 64 assertions)

php vendor/bin/phpunit -c phpunit.visibility.xml tests/Feature/Api/AuctionCreationValidationTest.php --filter test_commission_validation_and_legacy_response_contract
OK (1 test, 24 assertions)

php -l tests/Feature/Api/BiddingPercentageVisibilityTest.php
No syntax errors detected

git diff --check
Passed (Git emitted only a line-ending normalization warning)
```

The final diff was reviewed. No client code changed, so no frontend build was
run. These results verify the repository implementation, not the inaccessible
deployed environment.

Remaining external verification, when access is available: confirm that the
runtime uses this resource implementation, the request resolves the intended
Passport user, and the auction has the expected stored fees and seller link;
then verify mobile rendering of the returned authorized values. These checks do
not justify widening backend visibility or inferring identity from Agora UID.

## Comprehensive authenticated follow-up

### Repository and environment independently rechecked

HEAD: `31e7fa97609598e866f259e0e57af1727406b468`, branch `main`.
`git merge-base --is-ancestor b2ddd1d HEAD` returned exit 0. At follow-up start
the only changes were the test file and this report from the preceding
investigation. They were retained. The final working tree has the same two paths
changed; this report is untracked. No applicable AGENTS.md was found in the
repository or ancestor directories.

Installed versions: PHP CLI 8.2.12, Laravel 8.83.29, Passport 10.4.2, PHPUnit
9.6.29. `config/auth.php` maps `api` to Passport and the users provider.
Application database configuration defaults to MySQL with `strict: false`;
configuration was inspected without connecting to application data. The
visibility test configuration forces SQLite `:memory:`, clears database URL
variables and the optional MySQL test configuration, and avoids production
config/route caches. Test setup additionally asserts memory isolation.

The default UserFactory targets Laravel's generic user shape. Auction tests
instead create `App\Models\User\User` fixtures through
`AuctionBugfixTestCase::user`, matching the project's model and disposable
schema. That existing convention was retained.

### Authentication gap closed

The previous 106-test suite exercised HTTP routes, controller/model queries and
resources, but authenticated tests used `Passport::actingAs`. That bypasses OAuth
bearer validation. New tests use real `User::createToken`, Passport's personal
access token factory and OAuth authorization server, then send the issued JWT
to `GET /api/live/video/{id}`. No actingAs is used in these new bearer tests.

Passport's own token/client migrations run only on the disposable connection.
RSA keys are generated in memory using the already installed phpseclib library;
application Passport keys are untouched. Tokens and keys are not printed or
saved to credential files. Real APISettings, routing middleware and auth:api are
enabled. Guards are forgotten between requests to prevent cached identities.

Passport 10.4.2 wraps its TokenGuard callback in Illuminate RequestGuard. An
initial test expected the outer guard to be TokenGuard; this test assertion was
corrected after inspecting the installed provider. It was a harness issue, not
a backend defect. Final tests verify the issued token ID and capture
`Request::user('api')->getAuthIdentifier()` after request handling, asserting
that it matches the intended database user.

### Complete test matrix

All cases below passed. Visible values are stored percentage 5 and payer string,
with independent service fee 20 unless a null is explicitly tested. Hidden
commission fields remain null in HTTP 200 responses. Authentication failures
receive HTTP 401 without a data envelope.

| Scenario | Result |
|---|---|
| Seller payer + owner, real bearer | PASS: visible |
| Seller payer + linked consignor, real bearer | PASS: visible |
| Seller payer + buyer, real bearer | PASS: hidden |
| Seller payer + unrelated seller, real bearer | PASS: hidden |
| Buyer payer + owner, real bearer | PASS: visible |
| Buyer payer + linked seller, real bearer | PASS: visible |
| Buyer payer + buyer, real bearer | PASS: visible |
| Buyer payer + unrelated viewer, real bearer | PASS: visible, preserving existing contract |
| Missing bearer with valid API key | PASS: 401 |
| Malformed bearer with valid API key | PASS: 401 |
| Revoked Passport-issued token | PASS: 401 |
| Expired correctly signed Passport-issued token | PASS: 401 |
| Item creator differs from consignor | PASS: creator alone has no seller access |
| Creator is a consignor in another auction | PASS: hidden for this auction |
| Forged uid/user_id/seller_id/isHost/user_type | PASS: cannot override bearer identity |
| Null seller_id + legitimate owner, real bearer | PASS: visible |
| Linked seller with buyer account type | PASS: relationship grants access |
| Valid payer + null amount | PASS: stored null preserved, payer retained for authorized seller |
| Null service fee | PASS: independent of commission filtering |
| Null/unexpected payer in permissive legacy fixture | PASS: fails closed for every viewer |
| Full JSON/nested resources | PASS: existing recursive leak/contract checks |
| Creation with seller payer, real bearer and SubscriptionService | PASS: stored fees returned by detail endpoint |
| Creation with buyer payer, real bearer and SubscriptionService | PASS: stored fees returned by detail endpoint |
| Quota transaction | PASS: creation consumes one unit, unrelated update consumes none |
| Fee update followed by title-only update | PASS: both configurations preserve updated fees |
| Invoice/award/calculation regressions | PASS: full visibility suite |

The actual fee migration was also executed on a second SQLite `:memory:` schema.
PRAGMA confirmed integer nullable commission/service amounts, non-null payer and
default buyer. The generated enum constraint rejects null and invalid payers.
This distinguishes real migration constraints from permissive legacy fixtures
used for fail-closed serialization tests. Deployed schema and MySQL behavior in
non-strict mode were not verified; no persistent database was inspected.

### Classification, results and impact

Classification **G: no reproducible backend defect**. No authentication or
identity failure (A/B), relationship defect (C), authorization defect (E) or
serialization defect (F) was demonstrated. Controlled missing stored amounts
(D) and intentional filtering produce nulls through distinct tested paths;
neither establishes the external screenshot's cause.

Final verification after the last test-code change:

```text
php vendor/bin/phpunit -c phpunit.visibility.xml
OK (120 tests, 2002 assertions)

php vendor/bin/phpunit -c phpunit.visibility.xml tests/Feature/Bugfixes/AuctionInputRegressionTest.php
OK (5 tests, 101 assertions)

php vendor/bin/phpunit -c phpunit.visibility.xml tests/Feature/Api/AuctionCreationValidationTest.php --filter 'test_commission_validation_and_legacy_response_contract|test_partial_scheduling_updates_use_stored_values'
OK (2 tests, 37 assertions)

php -l tests/Feature/Api/BiddingPercentageVisibilityTest.php
No syntax errors detected

git diff --check
Passed (line-ending normalization warning only)
```

Focused bearer, expiry, schema and creation tests also passed. No configured PHP
static analyzer was identified in composer scripts; syntax/diff checks were
used. No dependencies were installed or upgraded. Production logic and API
contracts are unchanged. Final diff review confirmed changes only to the test
file and this report.

Database impact is limited to disposable in-memory fixtures and schema tests.
No application records, schema or migration files changed. No application reset,
seeding, commit, push or deployment occurred. These are local repository and
isolated integration results, not production or Flutter verification.

Future external evidence would need to associate the failing response with the
runtime resource version, resolved Passport user and stored auction/seller
fields, followed by checking Flutter rendering of authorized values. Work was
completed without waiting for that inaccessible evidence; the original Flutter
failure is not claimed to have been reproduced.
