# Centralized OTP policy — 2026-11-22

## Implementation

`App\Services\OtpService` owns environment eligibility, QA phone matching, generation,
ten-minute expiry, stored-code comparison and consumption. All OTP controllers and the
two legacy generation helpers delegate to it. There are no client-controlled policy flags.

| Environment | Phone | Behavior |
| --- | --- | --- |
| `local`, `development`, `testing` | Any supported phone | Fixed `1111`; no provider delivery |
| `production` | Explicit normalized QA allowlist match | Fixed `1111`; no provider delivery |
| `production` | All other phones | Cryptographically random four-digit code; Infobip delivery |
| Any other environment | Any phone | Random code; Infobip delivery |

Configure only `OTP_QA_PHONES` as a comma-separated list in server configuration.
`.env.example` leaves it empty. There are no real QA numbers in source or this report.
`MobileNumber` provides the existing canonical normalization used for both configured
numbers and submitted phones. Missing/empty lists disable production fixed OTP. Invalid
entries or malformed list structures disable the entire list. Unknown environments fail
closed. The old `OTP_TESTING`, `OTP_TEST_CODE`, and `PASSWORD_RECOVERY_OTP_TESTING`
settings are ignored and removed from application configuration; the real `.env` was untouched.

Ordinary random codes explicitly exclude `1111`. Verification also rejects previously
persisted `1111` for non-QA production accounts, removing the previous global recovery
exposure. Eligible users must still have an actual matching persisted, unexpired challenge;
the service never accepts `1111` without issuance. Four-digit string comparison uses
`hash_equals`. Expiry is rejected at the exact boundary; null/malformed expiry fails closed.
Successful consumption clears the code and expiry; pending records are marked verified
and soft-deleted. Expiry and replay checks remain tied to existing persistence.

## Discovered flows

- `POST /api/user/register`: new registration and resubmission, including restoration of
  soft-deleted pending records. Buyer/seller role access uses the same issuance policy
  and retains account eligibility/password fingerprint checks.
- `POST /api/user/verify-account`: pending registration, buyer/seller role grant, and
  backward-compatible phone verification for existing accounts. All use the common
  matching/expiry/consumption policy.
- `POST /api/user/forgot-password`: recovery issuance/resend, existing phone lookup,
  verified-account checks and locked transaction retained.
- `POST /api/user/reset-password`: shared verification and consumption, with same-password
  rejection, Passport issuance and transaction rollback preserved.
- No separate active resend route exists; registration/recovery requests reissue challenges.
- Unrouted `SendOtpController::__invoke` and `approvedMobile`: former dead code referenced
  a missing Patient model/resource, embedded SMS credentials and exposed a resource that
  does not exist. It now uses the real User model, shared policy and Infobip, clears consumed
  challenges and returns only phone/expiry on issuance. No route was added or enabled.
- `HelperTrait::generate_otp()` and `generate_password_otp()`: compatibility wrappers
  delegate to the central policy; no remaining independent random/fixed OTP generator.
- Profile phone changes currently do not use OTP. Social registration/login do not use
  OTP. These separate behaviors were inspected and left unchanged.

## Transactions, delivery and contracts

Registration issuance now locks existing pending rows inside a transaction. Verification
locks pending/user records and consumes challenges inside the transaction containing
account creation and Passport token issuance, including the legacy existing-account path.
Existing pending identity uniqueness constraints protect first-creation collisions.
Recovery retains its transaction and account lock. These locks serialize competing
operations on existing rows on database engines supporting row locks.

Provider requests remain inside issuance transactions to preserve the existing recovery
locking model and serialize issuance with verification. This can hold locks for the provider
timeout. No retry was added. Delivery failures or exceptions invalidate the current code
and expiry while preserving account/registration details. Provider payloads, OTPs and
phone numbers are not logged by the OTP controllers. Logs contain only fixed event names.

The historical Infobip WhatsApp template implementation and separate generic SMS service
were not modified in this task. WhatsApp Sender remains removed. Public routes, methods,
request fields, response envelopes, localization and the existing 60/minute API rate limit
remain intact. No OTP values or policy configuration are returned through public APIs.

## Files changed in this task

Added:

- `app/Services/OtpService.php`: common policy and persistence/consumption operations.
- `tests/Unit/Services/OtpServiceTest.php`: environment/allowlist matrix, comparison and expiry tests.
- `docs/qa/OTP_STANDARDIZATION_2026_11_22.md`: this report and configuration guidance.

Modified:

- `.env.example`: document the empty QA allowlist.
- `config/services.php`: replace legacy test flags with QA phone list parsing.
- `app/Traits/HelperTrait.php`: delegate legacy generator methods.
- `app/Http/Controllers/api/User/Auth/RegisterController.php`: policy, lock, delivery failure invalidation and sanitized logging.
- `app/Http/Controllers/api/User/Auth/ForgetPasswordController.php`: policy, exception handling and sanitized logging.
- `app/Http/Controllers/api/User/Auth/ResetPasswordController.php`: central matching, expiry and consumption.
- `app/Http/Controllers/api/User/Auth/VerifyAccountController.php`: central verification/consumption and transactional locking.
- `app/Http/Controllers/api/User/Auth/SendOtpController.php`: remove dormant broken implementation and embedded credentials; use shared user OTP policy without adding routes.
- `tests/Feature/Api/User/Auth/OptionalRegistrationEmailTest.php`: production QA/random behavior, client flag rejection, failure invalidation, legacy verification/resend and token rollback.
- `tests/Feature/Api/User/Auth/PasswordRecoveryTest.php`: production ordinary/QA policy, legacy-code rejection, provider exception privacy and updated fixtures.
- `phpunit.otp.xml`: add policy tests, force an empty QA list and remove obsolete test flags.
- `docs/qa/OTP_SELECTIVE_ROLLBACK_2026_11_22.md`: mark the prior historical behavior as superseded, retaining audit evidence.

The workspace already contained the previous selective rollback as uncommitted changes,
including service/config/controllers, language files, old provider test deletions, empty root
artifact deletions and provider documentation. That work was preserved; overlapping OTP
behavior was deliberately superseded by this request. A language key generated by the
legacy translation helper during testing was removed; the controller now uses an existing
localized message. The auction visibility test and
unrelated business logic were untouched. No index edits, reset, clean, commit, push or deployment.

## Executed verification

Versions verified: PHP 8.2.12 (`php -v`), PHPUnit 9.6.29 (`php vendor/phpunit/phpunit/phpunit --version`),
Laravel 8.83.29 and Passport 10.4.2 (installed Composer metadata).

The dedicated configuration retains `tests/Support/otp-bootstrap.php`, which refuses
unsafe requested/effective database settings and validates the connected SQLite `:memory:`
database before test schema operations. Configuration/routes caches are isolated; cache,
mail and sessions are in memory and logging is null. External HTTP is faked or services
mocked. No real provider request was made.

Commands and observed results:

- `php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml --filter OtpServiceTest`:
  **15 tests, 118 assertions passed**.
- `php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml`:
  initial **196 tests, 1161 assertions passed**; final run after the interleaved-reset
  test and localization cleanup: **197 tests, 1167 assertions passed**, 27.939 seconds.
- `php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml --filter 'BuyerSellerAccessTest|AuthenticationErrorResponseTest'`:
  **81 tests, 471 assertions passed**, 14.997 seconds.
- `php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml --filter test_interleaved_reset_attempt`:
  **1 test, 6 assertions passed**. A second reset is invoked during the first request's
  token issuance, before its transaction commits, and cannot reuse the consumed code.
- `php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml --filter 'test_legacy_phone_resend|test_interleaved_reset_attempt'`:
  **3 tests, 24 assertions passed** after the legacy localization cleanup.
- `php -l <file>` for the new service, helper, configuration, five controllers, unit test
  and two feature tests: all passed.
- `git -c core.safecrlf=false diff --check`: passed.
- `git -c core.safecrlf=false diff --quiet HEAD -- tests/Feature/Api/BiddingPercentageVisibilityTest.php`: exit 0.
- Targeted route/controller/service searches confirmed all discovered OTP implementations
  delegate to the policy and no active WhatsApp Sender code was reintroduced.

Existing tests cover optional email, user creation, resubmission, soft-delete restoration,
wrong/expired codes, successful reset, consumption, token issuance/failure, password reuse,
Infobip acceptance/rejection, generic SMS normalization, rate limiting and role access.

## Database impact, security and limits

No persistent data or schema changed. No migration or production database operation ran.
All test schema/data operations were confined to disposable in-memory SQLite connections.
The real `.env`, production credentials and production configuration were not modified.

The global production fixed-code default is removed. Explicit QA exceptions still weaken
phone-possession proof for those accounts, so the allowlist should contain only controlled
QA accounts without valuable production privileges. Fixed codes are deliberate environment/
server-policy exceptions, not universal verifier bypasses. Existing account ownership,
role/password checks, expiry, consumption and rate limiting remain applicable.

No deployment or real provider delivery verification occurred. SQLite verifies transactional
rollback and replay behavior but cannot establish actual concurrent MySQL row-lock scheduling;
that requires a separate disposable database concurrency exercise. No claim of real parallel
race testing is made. Legacy schema-dependent LoginControllerTest/RegisterControllerTest
and opt-in MySQL tests were inspected but not run: they require schemas beyond the isolated
fixtures or a separate database. The dedicated suite covers their relevant registration/login
contracts. Full-repository regressions were not run.

Existing unknown-phone and unverified-account error messages are distinguishable; they
were preserved for API/localization compatibility. This task adds no account enumeration
data, but eliminating that inherited distinction requires a separate response-contract change.
Existing profile phone changes without OTP also remain a separate security limitation.
