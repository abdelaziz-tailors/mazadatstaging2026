# Selective OTP rollback — 2026-11-22

> **Superseded by OTP standardization later on 2026-11-22.** This report records the
> historical rollback only. Current environment/QA policy and verification results are
> documented in `OTP_STANDARDIZATION_2026_11_22.md`; the global production fixed-code
> default described below is no longer the active implementation.

## Historical findings and scope

Repository: `D:/NewSorted/4- WorkFolderCenter/Tailors/Mazadat/Mazadat-dev`.
Initial branch `main`, HEAD `6198c47`; index and working tree were clean, including untracked files.
Target `68730c027ef53b4fd19274519a55cec42e678192` has parent
`ce446c5cc8884f00ec04fab64765ff26ad271138`, the authoritative baseline.
`0abc932` is an ancestor of the baseline; the target is an ancestor of HEAD.
Subsequent commits `f63eaa1` and `6198c47` affected registration/authentication.
History, target-versus-parent changes, later changes, callers, configuration, routes,
verification/reset code and tests were inspected. No applicable AGENTS.md or specialized
skill was found. An independent agent reviewed security, scope and regression overlap.

Actual installed versions: PHP CLI 8.2.12, Laravel 8.83.29, Passport 10.4.2,
PHPUnit 9.6.29. Dependencies were not changed.

The baseline OTP delivery provider was **Infobip WhatsApp templates**, not plain SMS.
`sendRegistrationOtp()` delegates to `sendWhatsappOtp()` using Infobip. The separate
generic SMS implementation is preserved. No WhatsApp Sender dependency, wallet
diagnostic, request interpreter, credentials or configuration remain in active OTP code.

Registration generates `random_int(1000, 9999)` unless `OTP_TESTING` is enabled
(default false); then it uses `OTP_TEST_CODE` (default `1111`). There is no historical
environment gate. Pending registration is created or updated, including resubmission
and restoration of a soft-deleted pending record. Expiry is ten minutes. Historical
delivery failure returns the generic failure but retains pending OTP and expiry.

Recovery uses hardcoded `1111` when `PASSWORD_RECOVERY_OTP_TESTING` is enabled,
default **true**, without an environment gate. Otherwise it generates a code and calls
Infobip inside the existing transaction. Delivery failure clears OTP and expiry.
Original account lookup, locking, ten-minute expiry and response fields are restored.
Reset validation, same-password rejection, transaction, Passport token issuance and
OTP consumption remain intact. Wrong, absent, expired and unverified-account codes
remain rejected; successful reset clears OTP and expiry. Token failure rolls back reset.

The service, recovery controller and service configuration have zero Git diff from the
baseline. Registration has zero diff from `f63eaa1`: baseline OTP behavior plus the later
buyer/seller role additions. Two missing variable definitions (`$existing`, `$otpType`)
were restored from that commit because `6198c47` removed them while retaining their
uses. Later role verification, requests, login, models and routes were preserved.

## Files changed

Modified:

- `.env.example`: remove Sender/test-mode additions; keep the Infobip key blank instead of restoring a historical embedded credential.
- `app/Services/SmsService.php`: restore historical provider and generic SMS service.
- `app/Http/Controllers/api/User/Auth/RegisterController.php`: restore historical OTP behavior and retain buyer/seller compatibility.
- `app/Http/Controllers/api/User/Auth/ForgetPasswordController.php`: restore historical recovery behavior.
- `config/services.php`: remove Sender settings; restore recovery default true.
- `resources/lang/en/app.php` and `resources/lang/ar/app.php`: remove Sender message templates.
- `tests/Feature/Api/User/Auth/OptionalRegistrationEmailTest.php`: restore Infobip tests, retain later verified-account fixture change, add production test-mode and failure-persistence tests.
- `tests/Feature/Api/User/Auth/PasswordRecoveryTest.php`: restore Infobip tests, add absent-flag default and production fixed-code issuance verification.
- `docs/qa/WHATSAPP_SENDER_OTP_INTEGRATION_2026_11_17.md`: mark obsolete; preserve historical audit text.

Deleted:

- `tests/Unit/Services/WhatsappSenderTest.php`
- `tests/Support/whatsapp-bootstrap.php`
- `phpunit.whatsapp.xml`
- `!empty(config(services.whatsapp_sender.api_token))` and `config(services.whatsapp_sender.base_url)`: empty root artifacts introduced by the target commit.

Added:

- `phpunit.otp.xml`
- `tests/Support/otp-bootstrap.php`: retained isolation checks under provider-independent names.
- `docs/qa/OTP_SELECTIVE_ROLLBACK_2026_11_22.md` (this report).

## Safety and verification

No reset, clean, whole-commit revert, commit, push or deployment was performed.
The index was not changed. `tests/Feature/Api/BiddingPercentageVisibilityTest.php`
and unrelated auction, subscription, wallet, payment, Agora and authentication code
remain untouched. No migration, persistent database, schema, user, OTP or password
data was modified. The real `.env` was not modified.

`phpunit.otp.xml` forces testing, SQLite `:memory:`, empty database URLs, uncached
configuration/routes, array cache/session/mail, null logging and synchronous queues.
The bootstrap verifies the requested environment, effective database configuration and
connected SQLite in-memory database before test schema operations. HTTP clients are
faked and OTP service callers mocked. No real SMS, WhatsApp or provider request was made.

Commands executed:

- `php -v`: PHP 8.2.12.
- `php vendor/phpunit/phpunit/phpunit --version`: PHPUnit 9.6.29.
- Installed Composer metadata inspected for Laravel and Passport versions.
- `php -l <file>` for every changed PHP file and the new bootstrap: passed.
- `php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml`: **PASS, 172 tests, 983 assertions**, 25.439 seconds.
- `git diff --check`: passed.
- `git diff --quiet ce446c5 -- app/Services/SmsService.php app/Http/Controllers/api/User/Auth/ForgetPasswordController.php config/services.php`: exit 0.
- `git diff f63eaa1 -- app/Http/Controllers/api/User/Auth/RegisterController.php`: empty.
- `git diff --quiet HEAD -- tests/Feature/Api/BiddingPercentageVisibilityTest.php`: exit 0.
- `rg -n 'whatsapp_sender|WHATSAPP_SENDER|whatsappWalletStatus|api/external' app config routes tests .env.example`: no matches.

The initial suite had 172 tests, 979 assertions and one new-test failure caused by
PHPUnit exposing a forced boolean environment value as string `1`. The test was
corrected to remove the flag temporarily and verify the actual absent-variable fallback,
then restore its previous process and superglobal values. Application code was unchanged.

## Security and remaining limits

**CRITICAL: unsafe for production. Do not deploy this rollback.** Recovery defaults to
a known fixed OTP, allowing an attacker who knows a verified account's phone to request
recovery and reset its password with `1111` during the validity window. Registration's
fixed code is also honored in production if its test flag is enabled, bypassing proof of
phone possession. These historical policies were intentionally restored as requested.

`1111` is not a universal verifier bypass: it must match the persisted code and satisfy
the applicable expiry/account checks. Recovery consumes it on successful reset;
registration marks the pending record verified and soft-deletes it after account creation.
Historical provider failure logging may include raw responses/exception text and
registration test-mode logging includes the phone; this privacy risk is retained for
source fidelity. Tests use null logging. No OTPs, credentials or phone numbers were
written into this report.

Real provider delivery, production operation, Flutter execution, production database
locking/concurrency and full-repository regressions were not exercised. No Flutter source
was assumed. API routes and response envelopes were preserved; isolated registration,
recovery, role-access and authentication regressions provide the compatibility evidence.
