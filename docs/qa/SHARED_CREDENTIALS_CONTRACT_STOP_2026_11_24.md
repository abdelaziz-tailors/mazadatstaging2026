# Shared credentials: contract safety gate, 2026-11-24

Superseded by [the backend-only shared credential implementation](BACKEND_LEGACY_REGISTRATION_2026_11_24.md).
The earlier stop decision and mandatory challenge requirement below are historical.

Follow-up: the required registration challenge field was subsequently authorized.
See [challenge binding implementation](REGISTRATION_CHALLENGE_BINDING_2026_11_24.md).
The replay blocker recorded below is historical; shared-credential gaps remain separate.

## Decision

STOP before runtime implementation under task sections 8.1, 13 and Phase 2.
The requested Architecture B remains the target. The current candidate does not
implement its shared credential lifecycle and is not ready for deployment review.
This report qualifies the earlier INDEPENDENT_ACCOUNTS report; that report's passing
tests do not establish compliance with the new shared-credential requirements.

## Blocking evidence

Historical commit f63eaa11196182ba22df47f51f55014edb48faa2's VerifyAccountRequest
requires only phone and otp. Current VerifyAccountRequest still permits that payload;
its user_type is optional. The checked-in Postman verify-account body also contains
only phone and otp (its illustrative OTP value is stale). Registration returns phone
and expire_at, but verification does not require either a challenge ID or expiry.
No client source was requested or searched for.

Reproduction executed against the actual HTTP controllers in isolated SQLite memory:

1. Register Buyer with phone X; verify with `{phone: X, otp: "1111"}`.
2. Register Seller with phone X only after Buyer verification succeeds.
3. Replay the identical consumed Buyer verification request.
4. Current controller creates Seller and returns success with data.user_type=seller.

The new acceptance test requires no Seller creation and fails on that assertion.
It uses distinct usernames and null emails to reach the OTP defect independently of
the existing global email restriction. Passwords are the same in both registrations.
Tokens and external integrations use the existing isolated fixture's mocks.

Serialization alone cannot solve this: at step 3 a legitimate Seller verification
and the old Buyer request have the same observable phone/code and server state.
Locks, transaction isolation, expiration and single-use consumption of the old row
cannot distinguish them. Rate limits or a time delay cannot provide the required
guarantee either. Requiring user_type would change the documented minimum contract;
it would distinguish sibling types but not repeated challenges for the same type.
A challenge-bound value or an already established challenge-bound client session
would be needed. No such binding is established by the inspected contract. Permanent
rejection of all later challenges would prevent the required sibling registration.
No workaround weakening replay isolation was implemented.

## Other confirmed gaps in the existing candidate

- RegisterController hashes each submitted password independently and does not
  authenticate a shared credential identity before staging another account.
- MobileNumber::emailRules enforces active-user email uniqueness across types;
  VerifyAccountController separately rejects an existing email across users.
- ChangePasswordController updates only the authenticated User row.
- ResetPasswordController resolves one User and updates only that row.
- The prior BuyerSellerAccessTest explicitly accepts different passwords on sibling
  types and tests account-scoped recovery. Those expectations conflict with this task.
- The new phone/type uniqueness migration is insufficient to provide an explicit,
  trustworthy credential association. Linking historical duplicates automatically
  would violate the prohibition on inferred identity merging.
- Current login resolves normalized identifier plus exact stored account type and
  authenticates that User ID; this is useful for account isolation, but does not
  establish shared credentials. Legacy attached roles are not independent IDs.

## Architecture and next implementation gate

Keep separate users, ownership relationships, profiles and Passport token owners.
After the contract gate is resolved, evaluate an additive credential-group relation
with explicit authenticated linking for new sibling registrations. Do not group old
records from duplicate identifiers or conflicting hashes. Group-level credential
changes/recovery and identifier updates must lock the group and update atomically.
Canonical email/phone conflicts, soft-deleted reservations and historical credentials
need a read-only reconciliation process before migration design is finalized.
No new schema design is represented as approved or fully audited here.

The supported request values are buyer, seller, vendor and buyer_vendor; subscriber
is not accepted by these FormRequests. Existing buyer_vendor and role memberships
must remain intact. This investigation did not complete the broader financial,
ownership, live Passport, concurrency or legacy regression matrix after the stop gate.

## OTP and security

OtpService currently fixes all generated codes to 1111, expires challenges at ten
minutes and clears stored code/expiry on consumption. Registration does not call a
delivery service. These files/behaviors were not changed in this investigation.
Single-use row consumption is not cross-challenge replay protection, as reproduced.
The public code does not prove phone ownership. A future credential link must require
the existing shared password and reject conflicting identifier groups; identifiers
or this fixed code alone must not authorize linking.

## Repository and change control

Branch main; HEAD a3a89fa596c69e0965ba26fef5c1534abd8c036b. At entry there were
20 modified tracked files and four untracked prior implementation artifacts. They
were preserved. No AGENTS.md was found by repository search or the inspected parent
directories. Available skills were reviewed; no listed specialist skill applies to
this Laravel investigation. Shell/Git/PHPUnit provide the necessary local tools.
No parallel agents were used. PHP executable reports 8.2.12; composer requires
Laravel ^8.75 and Passport ^10.4. No live database version was queried.

Only files added in this investigation:

- tests/Feature/Api/User/Auth/RegistrationReplayContractTest.php: isolated failing
  acceptance gate for serialized sibling registration with the existing contract.
- docs/qa/SHARED_CREDENTIALS_CONTRACT_STOP_2026_11_24.md: this report.

The new test is deliberately not added to the existing phpunit.otp.xml suite: run
it explicitly as shown below. It records an unresolved acceptance failure, not a
passing characterization of insecure behavior. Existing tests were not rewritten.

## Database impact

No migration file created or modified in this investigation. The existing untracked
2026_11_24_000001 migration was preserved. No MySQL/MariaDB migration execution,
persistent database access, data audit, data correction or rollback was performed.
The PHPUnit fixture creates its own schema and invokes its fixture migrations only
in guarded SQLite :memory:. It cannot prove MariaDB DDL or lock scheduling safety.
No production connections, Flutter changes, commits, pushes or deployments occurred.

## Executed verification

Acceptance command:

```text
php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml tests/Feature/Api/User/Auth/RegistrationReplayContractTest.php --filter test_consumed_buyer_request_cannot_verify_later_seller_challenge
```

Result: FAIL, 1 test, 6 assertions, 1 failure. Actual Seller count 1; expected 0.
Failure message: "A consumed Buyer request created Seller; response type: seller".
An initial test-authoring run failed because the fixture payload method is private;
the new test was corrected to build its own payload before the above reproduction.

Existing baseline suite command:

```text
php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml
```

Result: PASS, 266 tests, 2,041 assertions, 62.207 seconds. A green baseline cannot
override the failing new requirement. Full acceptance matrix and deployment readiness
are not achieved.
