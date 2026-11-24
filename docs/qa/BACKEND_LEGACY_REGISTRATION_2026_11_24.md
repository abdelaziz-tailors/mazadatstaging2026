# Backend-only legacy registration correction — 2026-11-24

## Delivery status

Implemented locally; no Flutter changes are needed or were made. No commit, push,
deployment, production connection, production migration, or persistent application
database mutation occurred. Production Buyer 171 was never accessed or modified.
Production deployed: **NO**. Production verified: **NO**.

The repository was clean on `main`, HEAD
`b7a35eb086ff1d45bad816a7dd0f93a086729e33`, before this work. No applicable AGENTS.md
was found in the repository or checked parent directories. Available skills/tools
were reviewed; this Laravel repository correction did not require a specialist
artifact or external-service skill. PHP 8.2.12, PHPUnit 9.6.29, SQLite in memory,
and a newly initialized disposable MariaDB 10.4.32 server were used.

## Cause and corrected API contract

The supplied production evidence showed `challenge_id` validation rejecting the
existing Flutter request. Locally, VerifyAccountRequest required it and the
controller selected exclusively by its hash. Global user email rejection and
account-specific credential changes separately prevented shared credentials.
The historical `f63eaa11196182ba22df47f51f55014edb48faa2` registration controller
also used role_access and conditional random OTP/delivery; those behaviors are
not restored.

The existing POST `/api/user/register` form fields and response envelope remain.
POST `/api/user/verify-account` now accepts exactly `{phone, otp}`. Neither
`challenge_id` nor `user_type` is mandatory. No new header is introduced; existing
API-key policy remains. The optional returned challenge remains usable as an
additional consistency check when supplied, never as a fallback selector.

Phone is canonicalized through MobileNumber. Under a stable canonical-phone row
lock, verification selects only non-deleted, unverified `type=register` records,
ordered by `created_at DESC, id DESC`. It then checks expiration and fixed code.
An expired newest record is rejected, without selecting an older record.

Registration explicitly soft-deletes older active registration state for that
phone before staging the latest submission. It reuses the existing phone/type
pending slot and refreshes its creation time. Submitted data remains stored in
the existing pending model; superseded slots are never verification fallback.
On successful verification, account creation, vendor admin creation where
applicable, consumption, supersession of any remaining legacy pending slots,
and token issuance share one transaction. Token failure rolls back account and
pending changes. Transactions retry deadlocks up to three attempts.

Successful consumption clears OTP/expiration, marks the selected pending row
verified and soft-deletes it. Ten-minute expiration remains. Failed verification
does not create an account. Per-type phone/email indexes and the phone lock
prevent duplicate accounts and competing consumption.

OtpService already used fixed `1111` for all workflows at inspected HEAD. It
remains the common implementation. No active random OTP generator was found;
remaining random calls concern file names, optional challenge references, or
unrelated media tokens. Registration invokes no SMS/WhatsApp provider or delivery
job. Existing non-registration notification policies are preserved.

## Independent accounts and shared credentials

Buyer, seller, buyer_vendor and vendor retain their mappings and separate User
records. Vendor still obtains its partner Admin. No registration uses role_access
or adds a UserAccessRole. Profiles, ownership, User IDs, and token owners remain
account-specific. Existing phone/email login resolves the requested user_type.

RegistrationIdentity requires matching canonical phone, normalized email, and
an explicit Hash::check against every matching existing verified active account.
An existing single ungrouped account can acquire an explicit credential group
after this proof. Multiple ungrouped historical accounts are rejected for manual
review, even if their identifiers and passwords happen to match. No migration
backfills, merges, or renumbers accounts. Existing account fields/timestamps stay
unchanged when assigning the new nullable group association.

Each group stores the shared credential snapshot. A sibling pending registration
records its group and verified hash snapshot. Activation rechecks that snapshot;
it cannot restore credentials changed after staging. The User save path propagates
credential edits within a transaction to explicitly linked users and their linked
Admin mirrors, and invalidates staged registration credentials. Password changes
also clear sibling password-reset codes. Existing ungrouped accounts retain
their separate credentials. Non-credential saves and account creation retain
their usual model path.

Authenticated password change reloads and locks current credentials before
checking the old password. Recovery without user_type resolves a deterministic
representative only when all matching users belong to the same trusted group;
ambiguous historical identities still fail. Typed recovery retains exact-type
selection. Profile updates now use the existing profile request with canonical,
group-aware phone/email validation. Identity changes propagate across siblings;
names and other profile fields remain independent. Stale model instances reload
the association before changing credentials.

## Schema and migration compatibility

Existing migrations `000001` (phone/type uniqueness) and `000002` (challenge hash)
are retained without edits, deletion, rollback, or dropped challenge columns.
Legacy verification does not query the challenge column unless the caller elects
to supply a challenge reference. Registration continues returning that optional
reference, so the existing challenge migration remains part of the schema order.

New `2026_11_24_000003_add_shared_registration_credentials.php`:

- Creates credential_groups and canonical-phone registration_locks.
- Adds nullable credential_group_id foreign keys to users and user_otps.
- Adds an active normalized email/type unique index before removing recognized
  global user email unique indexes. Existing email columns/generated keys remain.
- Checks supported engines, existing email/type conflicts, unsafe inline SQLite
  constraints, and partial installation state before proceeding.
- Does not modify historical account rows. Its down() refuses destructive rollback.

MariaDB tested all three migrations in order on representative isolated tables
with legacy phone/email indexes and a seeded historical account. All original
fields survived unchanged and its credential group remained null. SQLite tests
apply these migrations to fresh in-memory fixtures. No real application migration
history or production engine/index inventory was read or altered. MariaDB DDL is
not transactional: inspect partial DDL and migration history before any retry.

## Verification evidence

Baseline command, before edits:

`php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml`

Baseline: **379 tests, 2,891 assertions, passing**. The old suite included mandatory
challenge/multiple-pending expectations that contradict the newly approved policy.
Those assertions were replaced with legacy-payload, supersession, accepted replay,
shared-credential, recovery, profile, historical-isolation, and rate-limit gates;
the count is not an identical before/after suite.

Final command: the same command above. **377 tests, 3,008 assertions, passing**.
Coverage includes invalid/missing OTP, exact expiry boundary, no registration
delivery even under production environment flags, all supported types, independent
IDs, role-scoped phone/email login and token-factory owner arguments, wrong
password/identifier conflicts, optional challenge mismatch, null legacy challenge,
workflow isolation, deterministic legacy tie-breaking, no fallback, single-use
consumption, token-failure rollback, shared password recovery/change, profile
consistency, stale models, and existing 60-per-minute API throttling.

`php tests/Support/legacy-registration-mariadb.php`

**58 assertions passing**, MariaDB 10.4.32. Four races use actual independent PHP
controller workers: concurrent first registration, first verification, sibling
registration, and sibling verification. The harness observes two active contenders
and real InnoDB row-lock waiting before releasing each barrier. Registration races
leave one active pending slot; verification races yield one success and one
rejection. It also checks independent IDs, trusted group association, synchronized
credentials, scoped email-index enforcement after both legacy raw and generated
global email indexes, preserved historical data, and refused
destructive rollback. Fixtures use a random database on a fresh loopback-only
server at port 33331 and verify its exact generated temporary datadir. They never
use application database credentials. Test data is retained; the server is shut
down using `php tests/Support/legacy-registration-mariadb.php --shutdown`.

Broader command:

`php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml tests/Feature/Bugfixes`

80 tests, 776 assertions, 13 opt-in skips, **one unrelated existing error**:
SellerSubmissionWorkflowTest::test_approval_notifies_only_original_seller_with_or_without_login,
logged-out dataset, expects an FCM call that does not occur. An isolated rerun
reproduced it with the original User model loaded from HEAD using an ignored
temporary bootstrap:

`php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml --bootstrap "ABSOLUTE_REPO_PATH/storage/framework/testing/baseline-bootstrap.php" tests/Feature/Bugfixes/SellerSubmissionWorkflowTest.php --filter test_approval_notifies_only_original_seller_with_or_without_login`

That comparison also had the same error (2 tests, 15 assertions). No notification
module fix was included. Passing auth tests do not mean every repository suite is
green. PHP syntax checks and `git diff --check` also pass.

## Changed files

Runtime: RegisterController, VerifyAccountController, ResetPasswordController,
Profile/ChangePasswordController, Profile/UpdateProfileController, RegisterRequest,
VerifyAccountRequest, Profile/UpdateProfileRequest, MobileNumber, HelperTrait,
User, UserOtp, Exceptions/Handler, and the new RegistrationIdentity service.
Schema: the new 000003 migration. Tests: OptionalRegistrationEmailTest fixtures,
BuyerSellerAccessTest expectations, RegistrationReplayContractTest acceptance
tests, and the new legacy-registration-mariadb harness. Documentation: this report
and supersession notices on the earlier challenge-binding and contract-stop reports.
Earlier runtime patch artifacts are historical and do not contain this correction.

## Accepted tradeoffs and release prerequisites

Fixed `1111` does not prove phone ownership. An old identical `{phone, otp}`
request may activate the latest pending sibling. This is explicitly accepted
behavior, demonstrated by a test; it is not cryptographic binding or complete
replay protection. Locking, expiry, consumption, credentials proof and throttling
remain enforced.

Preexisting unverified Users without pending register state, legacy role_access
rows, and conflicting historical identities are not automatically converted or
linked. Existing legacy endpoints/tests retain their previous policies. Expired
or superseded registrations must be resubmitted; staged sibling credentials must
be resubmitted after a group credential update. Group identity keys remain reserved,
including when linked accounts are soft-deleted. Raw SQL credential writes bypass
the model synchronization path and must not be used for future account edits.

Before production: review a backup and actual migration history/engines/unique
indexes, run conflict audits, inspect representative legacy records, and apply
reviewed migrations in order during controlled registration maintenance before
activating this backend. Review existing pending recovery/re-registration cases.
Do not run migration rollback to recover; prepare a forward-compatible backend
rollback retaining optional legacy verification and credential consistency. Use a
controlled test identity to smoke-test registration, both login identifiers and
types, password change/reset, profile updates, and no delivery with the unchanged
Flutter build. Real Passport infrastructure, actual deployed middleware/provider
configuration, and real Flutter execution were not exercised locally. Resolve or
separately accept the existing notification regression before a broad release.
