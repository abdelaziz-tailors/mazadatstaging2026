> **OBSOLETE after selective OTP rollback (2026-11-22).** Retained as historical audit evidence. Provider-readiness instructions and test commands below no longer apply. See OTP_SELECTIVE_ROLLBACK_2026_11_22.md.

# WhatsApp Sender OTP integration — 2026-11-17

Implemented the confirmed request contract in the existing `SmsService` and connected both active OTP issuance flows. Automated verification passes. **Provider acceptance, wallet response semantics, actual WhatsApp delivery, and production readiness remain unverified.**

The user confirmed that additional response documentation/examples are unavailable and instructed implementation to proceed without inventing response fields. Consequently, a real HTTP 2xx response does **not** currently complete an OTP request successfully: the service reports acceptance as unverified, the controller returns the existing generic failure response, and that attempt's OTP is invalidated. A message might still reach WhatsApp with a code that has been invalidated. Do not enable this provider for end users until its response contract has been confirmed and the isolated interpreter has been completed.

**Repository investigation.** Repository root was `D:/NewSorted/4- WorkFolderCenter/Tailors/Mazadat/Mazadat-dev`, branch `main`. No applicable `AGENTS.md` was found in the repository or its ancestor directories. `AI_HANDOFF.md`, project configuration, complete messaging service, callers, requests, models, resources, migrations, routes, translations, HTTP client implementation, and test fixtures were inspected. Available skills were assessed; no specialized skill matched this Laravel integration. Independent agents reviewed the failure strategy and test-isolation approach from supplied code/architecture details; their child environments could not access filesystem tools.

Existing changes in `tests/Feature/Api/BiddingPercentageVisibilityTest.php` and the two pre-existing untracked bidding/home verification reports were preserved. No Git reset, stash, clean, commit, push, or deployment was performed.

**Architecture and behavior.**

- `sendRegistrationOtp()` delegates to `sendWhatsappOtp()`. Both retain their existing two-argument contracts and accept an optional third `DateTimeInterface` containing the actual expiry. All active controller callers supply it.
- One private request method constructs the URL and performs JSON POST `/api/external/messages/send` or diagnostic GET `/api/external/wallet`. Authentication is exclusively the `X-API-Token` header. Requests accept JSON. Existing phone validation and canonical normalization are reused, including Saudi, Egyptian, and international formats; the new provider receives the leading `+`. Generic SMS delivery retains its prior behavior.
- Credentials are environment-driven. Configuration validation requires an HTTPS URL, no URL credentials/query/fragment, a nonempty header-safe token, and a positive integer request timeout. Redirects are disabled to avoid forwarding the token to another destination. TLS verification remains enabled. No automatic POST retries are added.
- Request timeout defaults to 15 seconds. The connection timeout is `min(5, request timeout)`, passed through `withOptions()` because the installed Laravel 8 client does not expose `connectTimeout()`.
- Non-2xx responses, connection exceptions/timeouts, unexpected exceptions, malformed JSON, and unknown response semantics return controlled internal error identifiers. No raw send response or exception text is returned. No undocumented `success`, `status`, message-ID, balance, or quota fields are interpreted.
- `transport_success=true` means HTTP 2xx only, including when response content is unusable. `provider_accepted=null` means unknown; `delivery_confirmed=false` means delivery has not been confirmed. `success` remains false while the acceptance schema is unknown. The sole response interpretation gate is `SmsService::interpretWhatsappResponse()`.
- `whatsappWalletStatus()` is an internal service method with no route and no invocation from OTP requests. For an HTTP 2xx valid JSON response it returns opaque diagnostic `data` with `whatsapp_wallet_schema_unverified`, rather than claiming balance/quota interpretation succeeded. No JSON shape or field is assumed. Raw diagnostic data must remain internal and must not be logged or returned by a public API.

**OTP lifecycles assessed.**

| Flow | Issuance and persistence | Delivery and failure | Verification and completion |
| --- | --- | --- | --- |
| Registration, including resubmission of pending registration | Existing random four-digit code; existing `UserOtp` record created or updated; ten-minute expiry | Shared WhatsApp Sender call; unsuccessful or unverified acceptance clears only matching pending record's `expire_at`, preserving its registration details | Existing verifier rejects null/elapsed expiry; successful application-contract stub and existing local test mode still create the user/vendor and issue the token |
| Forgot password | Existing secure four-digit generation, including leading zeroes; `users.password_otp` and ten-minute `expire_at`; issuance uses a short transaction and existing row lookup lock | Shared WhatsApp Sender call outside the transaction; failed/unverified attempts conditionally clear matching code and expiry, retaining the account/password | Existing reset verifier requires verified account, exact four-digit OTP and future expiry; successful application-contract stub still changes password, consumes code, and issues token |
| Legacy existing-account registration verification | Existing `users.otp` / `expire_at` verifier retained | No additional active issuance caller was found | Existing null/elapsed-expiry checks retained |
| Dormant `SendOtpController` / `approvedMobile` | Old Patient-based SMS code, ten-minute expiry | No route or external caller found; referenced `App/Models/Patient.php` is absent | Assessed as unreachable legacy code and left untouched |

Both active TTLs remain ten minutes. Persistence uses whole-second precision to match the existing database columns. English and Arabic Mazadat messages include the stored absolute expiry with explicit UTC, avoiding inaccurate fixed remaining-minute text when processing or delivery is delayed. No verification rules were redesigned.

Failure logs contain only fixed event names, an integer HTTP status (when available), and a boolean transport result. Success logs use `ACCEPTED`, not `SENT` or `DELIVERED`. OTPs, recipient phones, credentials, provider responses/IDs, and arbitrary exception/error strings are absent from these log contexts. Public error bodies retain the generic existing application message; successful application-contract/test-mode responses expose phone and expiry, never the OTP.

Registration test mode remains controlled by `OTP_TESTING` / `OTP_TEST_CODE`. Password recovery preserves its existing `1111` test code when explicitly enabled by `PASSWORD_RECOVERY_OTP_TESTING`. Both bypasses are now honored only in `local` or `testing`; recovery testing defaults to false instead of true. Production ignores enabled test flags for both code generation and delivery bypass.

**Configuration.** Add these through the deployment's secret/configuration mechanism after response-contract verification:

```dotenv
WHATSAPP_SENDER_BASE_URL=
WHATSAPP_SENDER_API_TOKEN=
WHATSAPP_SENDER_TIMEOUT=15
```

The base URL is the HTTPS application base, without the final `/api/external/...` endpoint. No new variables were written to the actual `.env`, and no production secret was modified. `.env.example` contains blank provider credentials and disabled OTP test flags. An existing credential literal was removed from its Infobip example entry; if that old credential is live, its owner should rotate it separately. Existing Infobip configuration entries remain for compatibility; OTP sending no longer uses them. Configuration changes must be incorporated into the normal deployment configuration-cache procedure; no live cache was cleared or rebuilt in this work.

**Files changed.**

- `app/Services/SmsService.php`
- `app/Http/Controllers/api/User/Auth/RegisterController.php`
- `app/Http/Controllers/api/User/Auth/ForgetPasswordController.php`
- `config/services.php`
- `.env.example`
- `resources/lang/en/app.php`
- `resources/lang/ar/app.php`
- `tests/Unit/Services/WhatsappSenderTest.php` (new)
- `tests/Feature/Api/User/Auth/OptionalRegistrationEmailTest.php`
- `tests/Feature/Api/User/Auth/PasswordRecoveryTest.php`
- `tests/Support/whatsapp-bootstrap.php` (new)
- `phpunit.whatsapp.xml` (new)
- This report (new).

**Database impact.** No schema changes, dependency changes, migration edits, persistent-data migrations, resets, truncation, reseeding, or deletion were performed. All automated database work used isolated SQLite `:memory:` fixtures. The test configuration forces testing/in-memory settings and uncached configuration/routes; its bootstrap refuses unsafe environment/effective database settings before tests create tables. This verifies Laravel application behavior, not production MySQL concurrency or provider delivery.

**Validation results.** PHP 8.2.12, Laravel 8, PHPUnit 9.6.29.

```text
php vendor/phpunit/phpunit/phpunit -c phpunit.whatsapp.xml
OK (145 tests, 1633 assertions)
```

The suite includes request-contract and timeout-option checks, number normalization, Arabic/English content, exact persisted expiry, missing/invalid configuration, HTTP 3xx/4xx/5xx (including authentication/balance/quota-shaped failures), hypothetical application-level responses, malformed JSON, exceptions, no retries, wallet requests and isolation, real controller paths with HTTP fakes, failed-code invalidation, pending-registration preservation/retry, newer-code cleanup protection, production bypass restrictions, sanitized responses/logging, OTP verification/reset compatibility through application-service stubs, existing optional-email/login behavior, and API authentication/rate-limit regressions. Hypothetical JSON fixtures do not assert an actual provider response schema. Service stubs reporting success prove application lifecycle compatibility only.

`php -l` passed for all ten changed/new PHP files (service, two controllers, configuration, two translation files, three test classes, bootstrap). `git diff --check` passed. No local PHPStan, Psalm, Pint, or PHP-CS-Fixer command was present; the existing StyleCI preset is hosted configuration, so no hosted analysis result is claimed. The full repository suite was not run: many legacy tests require a separately provisioned database/schema.

A broader authentication check also found an unrelated existing failure, reproduced separately without changing its files:

```text
php vendor/phpunit/phpunit/phpunit -c phpunit.whatsapp.xml tests/Unit/Http/Requests/Api/User/Auth/LoginRequestTest.php
Tests: 6, Assertions: 9, Failures: 1
```

`test_rules_requires_phone_password_and_user_type` expects phone rule `required`; the current unchanged `LoginRequest` supports email login and returns `nullable|required_without:email`. It is excluded from the scoped WhatsApp suite and remains unresolved in this task.

**Remaining limitations.** The existing general throttle is 60 API requests per IP/authenticated user per minute. No new per-phone/per-OTP attempt limiter was introduced. Four-digit codes, existing account-existence error behavior, and general verification policy remain unchanged. Concurrent resends are not serialized over network I/O; messages can arrive out of order. Conditional cleanup matches row, code, and expiry, protecting a different newer tuple, but the existing second-precision schema cannot uniquely distinguish repeated identical code/expiry tuples. The simulated newer-code tests do not constitute cross-process concurrency verification. Review stronger issuance identity/serialization separately before claiming robust resend behavior across workers.

**Exact inputs and work required for live verification.**

1. Provide the actual HTTPS provider base URL and a valid API token through the server's environment/secret store, plus explicit authorization for a specific test recipient controlled by the team. None was available or used in this session.
2. Obtain provider documentation or redacted real response examples for accepted sends and application-level rejection, including invalid token, insufficient wallet balance, daily quota, and any queued/failed states. Confirm which HTTP/body combination definitively means acceptance. No undocumented field is currently trusted.
3. Update only the isolated response interpreter and its fixture tests using that evidence. If the provider supplies message identifiers/statuses, document what each proves. Confirm the wallet's fields, types, currency/unit, and daily-limit semantics before mapping diagnostics.
4. With the authorized recipient, perform one controlled send, record a redacted acceptance result separately from handset receipt, verify the generated code and expiry in the application, then verify completion and one-time consumption. Do not log the OTP, phone, token, or raw response.
5. If actual delivery confirmation is required beyond an observed test handset, obtain the provider's delivery-receipt/status contract. HTTP 2xx and queue acceptance alone cannot verify handset delivery.

No real provider request or WhatsApp message was sent. Provider API acceptance and WhatsApp delivery were not verified. Production readiness is not claimed.
