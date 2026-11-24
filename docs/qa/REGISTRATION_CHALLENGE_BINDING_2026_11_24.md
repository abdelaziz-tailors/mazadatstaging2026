# Registration challenge binding — 2026-11-24

Superseded by the approved backend-only legacy contract correction:
[current implementation and verification](BACKEND_LEGACY_REGISTRATION_2026_11_24.md).
Mandatory challenge binding and the Flutter integration blocker below are historical.

## Delivery status

Backend correction implemented locally. The original replay acceptance gate now
passes. Mobile integration remains blocked on the actual Flutter repository path,
requested during this session. No Flutter files, UI, navigation or screens changed;
no Flutter tests or static analysis were executed. This is not a complete coordinated
release candidate until mobile integration and release checks are complete.

Repository main at a3a89fa596c69e0965ba26fef5c1534abd8c036b was inspected before edits.
Its 20 modified tracked files and six untracked previous artifacts were preserved.
No repository AGENTS.md was found. The available skills were reviewed; none is needed
for this repository-based Laravel correction. Git, PowerShell, PHP 8.2.12, PHPUnit,
and a disposable MariaDB server were used. No production connection was made.

## Cause and correction

Previously, phone and the fixed code resolved whatever registration was pending at
verification time. After consuming Buyer, the identical request selected a later
Seller. Serialization does not distinguish these requests.

RegisterController now generates `bin2hex(random_bytes(32))`: 256 random bits rendered
as 64 lowercase hexadecimal characters. Only its SHA-256 hash is stored in nullable,
uniquely indexed `user_otps.registration_challenge_hash`. The raw reference is returned
as `data.challenge_id` alongside the existing phone/expire_at fields. OTP is not
returned. UserOtp hides the hash from normal model serialization.

VerifyAccountRequest requires the reference. VerifyAccountController selects by its
unique hash, canonical phone, workflow `register`, unverified state and soft-delete
scope under `FOR UPDATE`. Optional user_type remains supported as an additional
consistency check; it is not required. No missing/invalid/expired/consumed reference
falls back to another challenge or User. Account creation, vendor partner creation,
challenge consumption and token issuance remain inside the outer transaction.
Consumption clears code/expiry, marks verified and soft deletes the pending row.
The old hash remains reserved until the existing registration refresh reuses that
row with a new hash; an old reference never resolves the refreshed operation.

Public `/user/verify-account` no longer performs phone-only legacy User activation
or role_access membership attachment. Keeping either fallback would defeat the
required registration binding. Their records and existing memberships are preserved;
role_access tokens are rejected even if a hash is present. Existing accounts can
still log in under the existing login rules. An outstanding legacy unverified User
that cannot re-register because its type/phone is reserved needs separately reviewed
recovery; do not silently activate it or delete its records. This compatibility
restriction must be assessed before release.

## Refresh/resend lifecycle and API consumers

No public registration resend route was found in routes/api/user.php or other route
references. Repeating registration already refreshes the same phone/type pending row,
restores it if soft deleted and resets the ten-minute expiry. Every successful issue
or refresh now rotates the hash. The old reference becomes invalid atomically when
the update commits. A failed refresh must not replace the client reference.

SendOtpController is an unrouted legacy User phone-verification entry point, not a
registration resend endpoint. It and password recovery/reset were left unchanged.
Their fixed codes can have equivalent successive-challenge replay risks; this change
does not extend challenge_id to those workflows or claim to fix them.

Required verification payload: existing phone and otp plus `challenge_id` from the
latest successful registration response. Existing success/error envelopes are kept;
missing/malformed fields use the existing HTTP 200/code 200/success false validation
convention, invalid references use the generic wrong_otp failure, and a valid expired
reference returns the existing 403 expiry failure. No extra account-type field added.
The Postman verification request and API README now document this contract.

Flutter work still required in the real repository:

1. Add challenge_id to its registration response DTO and current registration state.
2. Capture response phone/reference together; submit the exact reference on verification.
3. Fail locally without sending verification if the reference is absent.
4. Replace it after successful registration refresh/resend, and ignore late responses
   from an older registration operation so they cannot overwrite current state.
5. Clear it on success, cancellation or invalidation; retain it only for that operation.
6. Do not log it, persist it unnecessarily or modify UI/navigation.
7. Test DTO parsing, serialization, state replacement/cleanup and stale response handling.

No actual Flutter repository or client resend implementation was available to verify
these steps, its subscriber/vendor mapping, or its current Git state.

## Database impact

New additive migration:
`2026_11_24_000002_add_registration_challenge_hash_to_user_otps.php`.
It adds nullable CHAR(64) and a named unique index. Existing rows retain null and
are not backfilled, deleted or reset. MySQL/MariaDB user_otps must be InnoDB; migration
refuses another engine before DDL and does not convert it. Transactional users/admins
and Passport tables must also be verified as deployment prerequisites.

Migration created: YES. Executed against guarded SQLite memory and freshly initialized
disposable MariaDB 10.4.32: YES. Executed against any application/persistent or production
database: NO. Test data resides in a generated temporary directory and was retained.

Representative pre-release challenges survived the MariaDB migration unchanged with
null hash. Multiple nulls remain allowed; duplicate non-null hashes are rejected.
Pre-release pending registrations must repeat registration to receive a usable reference.
Do not mass-delete or reset them. The earlier phone/type uniqueness migration remains
a separate pre-existing dependency for independent registrations; it was not rewritten.

MySQL DDL is not transactionally reversible. If interrupted between column and unique
index creation, inspect schema/migration metadata before retrying; do not blindly run
down/up. No idempotent partial-DDL repair is claimed. Automatic down deliberately refuses
because removing binding is unsafe for active clients/challenges; use reviewed forward
fixes. Index creation can lock the table and needs a maintenance plan.

## Security and concurrency

- Unique hash lookup and canonical phone/workflow checks prevent cross-challenge,
  cross-phone and cross-workflow fallback. Optional type mismatch also fails.
- Hash storage limits exposure from a read of pending records; the raw token is a
  short-lived bearer secret and must use HTTPS. The fixed code does not establish
  phone ownership: anyone able to initiate a registration receives its challenge.
- `FOR UPDATE` serializes verification and refresh on the pending row. Expiry/code
  are rechecked under the lock. Consumption and creation roll back if token issuance
  fails. Existing database identity constraints remain necessary for registration races.
- Existing 60/minute per-user/IP API limiter and middleware were unchanged. Changing
  challenge references does not create a new rate-limit key.
- Handler excludes challenge_id/otp from flashed inputs. Telescope masks challenge,
  password and OTP request parameters even locally and excludes register/verify request
  entries, whose response bodies would otherwise expose bearer references/tokens.
  No new raw-reference logging was added. External proxies/APM/mobile logging still
  need operational review and must not capture these bodies.
- Simultaneous pending sibling challenges are covered through real HTTP tests. The
  MariaDB harness runs two independent verification controller processes with real
  overlapping queries and row-lock contention, then asserts exactly one success and
  one rejection, one Buyer row, consumed Buyer challenge and unchanged Seller challenge.
  Tokens are mocked; live Passport JWT signing and actual mobile behavior are unverified.
- Concurrent sibling creation and resend/verification races were not exhaustively
  process-tested; database locks/unique constraints and sequential lifecycle tests
  cover their core mechanism. Do not claim a complete production concurrency audit.

## Verification evidence

Before implementation, reran the original acceptance test: FAIL, 1 test, 6 assertions;
the consumed Buyer request created Seller. Prior baseline was 266 tests/2,041 assertions.

```text
php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml tests/Feature/Api/User/Auth/RegistrationReplayContractTest.php --filter 'test_token_is|test_invalid_references|test_cross_phone|test_same_type_refresh|test_pre_release|test_simultaneous_sibling|test_consumed_buyer'
```

PASS: 14 focused tests, 106 assertions. Coverage includes Buyer A consumed, Seller B
pending, replay A rejected with B unchanged, then B activates; malformed/missing/wrong
references; wrong phone/type/workflow/code; exact expiry; reuse; same-type refresh
rotation; pre-release null rows; simultaneous pending sibling registrations.
Consumed same-type account re-registration is rejected by existing uniqueness rules.

```text
php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml
```

PASS: 379 tests, 2,891 assertions, 73.540 seconds. The new class inherits existing
fixture tests, so this count includes repeated inherited cases, not 379 distinct new
scenarios. Existing Buyer/Seller/Vendor/buyer_vendor, no-registration-delivery, OTP,
recovery, expiry, account/token isolation and rate-limit coverage passed. Tests formerly
expecting phone-only legacy activation/role attachment now assert fail-closed preservation;
token failure rollback is exercised on actual challenge-bound registration instead.

```text
php tests/Support/registration-challenge-mariadb.php
```

The initial harness required two InnoDB transaction wait entries and intermittently
failed that instrumentation assertion although worker outcomes were success/rejected.
Inspection showed a contender can remain in optimizer `Statistics` while the other
waits on the row. The corrected barrier requires two active DB queries plus observed
row-lock contention before releasing the coordinator lock. Its process-state check
also accounts for native prepared statements reported as `Execute`, not only `Query`.
Final PASS: MariaDB 10.4.32, 15 assertions, two active queries, one observed row-lock
wait; exactly one controller success and one rejection. The earlier instrumented run
also passed with two row-lock waits. These are harness instrumentation corrections,
not changes weakening the one-success/one-rejection acceptance condition.
All runs used the fresh disposable server, never application database credentials.
The disposable server was shut down with the harness's `--shutdown` option, which
checks its actual datadir against the generated test directory before issuing SHUTDOWN.
The temporary test files/databases remain intact. A Windows process metadata query
was denied; shutdown instead used this database identity check without escalation.

PHP lint on changed runtime/new test files and `git diff --check` passed; Postman JSON
parsed successfully. Full backend Git diff reviewed with pre-existing work distinguished.

## Files changed in this follow-up

- app/Http/Controllers/api/User/Auth/RegisterController.php: generate/hash/return reference.
- app/Http/Controllers/api/User/Auth/VerifyAccountController.php: exact locked resolution;
  remove unbound legacy fallbacks.
- app/Http/Requests/api/User/Auth/VerifyAccountRequest.php: require opaque reference.
- app/Models/UserOtp.php: hide stored hash.
- app/Exceptions/Handler.php: prevent sensitive input flashing.
- app/Providers/TelescopeServiceProvider.php: prevent request/response reference capture.
- database/migrations/2026_11_24_000002_add_registration_challenge_hash_to_user_otps.php:
  nullable unique hash and engine preflight; unsafe down refusal.
- tests/Feature/Api/User/Auth/OptionalRegistrationEmailTest.php: run additive migration,
  capture response references for explicit request payloads and update legacy/rollback tests.
- tests/Feature/Api/User/Auth/BuyerSellerAccessTest.php: bound requests and legacy refusal.
- tests/Feature/Api/User/Auth/RegistrationReplayContractTest.php: preserve and extend the
  original acceptance test, add isolation/refresh/compatibility cases.
- tests/Support/registration-challenge-mariadb.php: guarded migration/concurrent controller test.
- phpunit.otp.xml: include replay acceptance tests in regular OTP verification.
- docs/API_README.md and docs/postman/Mazadat_API.postman_collection.json: new API contract.
- docs/qa/SHARED_CREDENTIALS_CONTRACT_STOP_2026_11_24.md: historical stop supersession link.
- docs/qa/REGISTRATION_CHALLENGE_BINDING_2026_11_24.md: this evidence and handoff.

No Flutter files changed. Other pre-existing modifications remain user work, including
the phone/type migration and prior runtime patch. That patch predates challenge binding
and must not be deployed as if it contains this correction. Shared credential group,
email-sharing and password propagation gaps remain separate, as previously reported;
this scoped OTP correction does not complete or redesign them.

## Coordinated release prerequisites — not executed

Finish and test the actual Flutter DTO/state integration before release. Review the
legacy unverified/role_access restriction above and inventory other API consumers.
Older clients cannot verify without the new reference; no insecure fallback is allowed.
Use a coordinated registration maintenance window, separately reviewed schema changes,
backend activation and compatible mobile version rollout/minimum-version policy.
Verify existing clients can obtain latest references, restart pre-release pending
registrations safely, and test no-WhatsApp behavior on controlled release identities.
Keep rollback plans forward-compatible with challenge binding; do not revert to
phone/code-only verification. No mobile/backend deployment, commit or push occurred.

Production verified: NO. Ready for complete deployment review: NO, pending mobile
integration, consumer compatibility, legacy recovery decisions and environment checks.
