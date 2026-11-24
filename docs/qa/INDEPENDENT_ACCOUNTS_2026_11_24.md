# Independent accounts and role-specific authentication - 2026-11-24

## Status and confirmed evidence

Backend implementation and isolated verification are complete. Mobile integration and
live acceptance remain unverified because the mobile repository, screenshots and
Subscriber API mapping are unavailable. No Production deployment/migration, commit,
push, credentials change or persistent-data correction was performed.

Repository: D:/NewSorted/4- WorkFolderCenter/Tailors/Mazadat/Mazadat-dev.
Initial HEAD a3a89fa (global OTP hotfix), clean working tree. No applicable AGENTS.md
was found in the repository or ancestor directories. AI_HANDOFF.md and relevant
schema/authentication/OTP documentation were inspected. No available specialist
skill was needed for this Laravel code change. Stack: Laravel 8, PHP 8.2, Passport;
web assets use Laravel Mix/Blade. No Flutter, React Native or native mobile source
was found in this repository or its immediate parent. No mobile UI was modified.

Confirmed SOURCE causes:
1. RegisterRequest.roleAccessUser identified an existing buyer/seller by phone and
   password; RegisterController created a role_access challenge, using that user's
   password fingerprint. VerifyAccountController attached a role and returned the
   ORIGINAL user ID. The second registration did not create a separate account.
2. MobileNumber.rules enforced phone uniqueness across all users. The original users
   migration has users_phone_unique; user_otps has unique(phone,type). Existing test
   fixtures also include globally unique generated canonical phone keys. These prevent
   independent accounts or overwrite/block pending registration across types.
3. LoginController resolved identifier first, required exactly one record, then checked
   hasAccessRole. A single multi-role record authenticated as the original ID. With two
   independent matching records, current source rejected ambiguity; it did NOT pick
   the first user. MobileNumber.find did have an exact-phone first() shortcut used in
   other verification/recovery lookups; it is now ambiguity-safe too.
4. The original users enum includes buyer/vendor/buyer_vendor but not seller, despite
   current API validation accepting seller. The migration extends the enum if needed.

Production records/indexes could not be independently inspected: no confirmed live
connection or deployed directory/version was supplied. Source and isolated DB evidence
establish the failure mechanisms; they do not establish the contents of live accounts.
Do not claim two separate Production accounts existed or that the frontend sent a
particular value. No existing membership was split, duplicated, converted or deleted.

## Supported types and account structure

The verified API values are buyer, seller, vendor, buyer_vendor. No subscriber value
is accepted by RegisterRequest/LoginRequest, and no confirmed mapping for the Arabic
Subscriber card was found. Subscription functionality is not proof of a registration
user_type. Mobile integration must confirm this before shipping that card.

Each new phone/type pair has its own users row, ID, bcrypt password, profile and
relationships. Registration always stages type=register; it never automatically adds
role_access to another account. Same phone/same type remains reserved even for a
soft-deleted account. Usernames, national IDs and active-email validation retain their
existing global policies. Different accounts may omit email; a second account must
use a distinct username and, when supplied, an allowed distinct email/national ID.
Existing admin phone uniqueness remains intact (only one vendor account per phone).

Existing user_access_roles, authorization helpers and pending role_access validation
remain for legacy authorization functionality. They do not cause login to select a
user of another stored user_type. Old combined buyer_vendor/attached-role accounts
are not silently converted into independent buyer/vendor/seller accounts. Login now
requires the exact stored account type. Client compatibility for those identities
must be reviewed before rollout; any conversion is a separate approved data operation.

## Request/response integration

No endpoint or response envelope was added. Login already REQUIRED user_type, so it
still does; missing/invalid types use the existing code=422 failure convention (Login
FormRequest uses HTTP 200). Registration defaults omitted type to buyer as before.
Phone+type/password and normalized email+type/password resolve exactly one account;
zero or multiple matches fail generically. Password verification, activation,
verification checks and Passport issuance operate on that exact account ID.

Examples (API key and private password omitted here):

```json
{"phone":"0512345678","user_type":"seller","password":"seller-account-password"}
```

```json
{"email":"seller@example.com","user_type":"seller","password":"seller-account-password"}
```

verify-account, forgot-password, reset-password and legacy phone request classes now
accept an optional validated user_type. Send the selected registration/login type
through every step. Verification resolves canonical phone/type/workflow under locks.
If multiple pending records match without a type it fails safely. Recovery and legacy
phone lookup permit omitted type ONLY when the identifier resolves uniquely across
accounts; exact string phone formatting never overrides canonical ambiguity.

```json
{"phone":"0512345678","user_type":"seller","otp":"1111"}
```

Reset also includes password/password_confirmation as before. A buyer's verification,
consumption, profile completion or password reset cannot select the independent seller
when the buyer type is supplied. Profile completion retains the authenticated user's
stored type when checking phone uniqueness. It ignores a supplied other-account ID.

Mobile work requiring its repository:
- Preserve selected role across existing cards, phone/email tab changes, registration,
  verification and recovery; include user_type in each request above.
- Confirm Subscriber's actual supported mapping; do not invent subscriber.
- On normal logout, revoke via the existing endpoint, then clear stored token/profile,
  account-specific cache and navigation state; show the existing login screen.
- Store the next response's token/profile and load resources for that new ID.
- Test remember-me, failed logout handling, RTL and existing visual interactions.
No new switching endpoint, simultaneous-login feature or UI redesign was implemented.
Backend logout still unregisters push ownership for the authenticated ID and revokes
only its current Passport token. Token permission/authorization architecture is unchanged.

## Migration: impact and safeguards

New migration: 2026_11_24_000001_scope_account_phone_uniqueness_by_type.php.
Read-only audit inspects columns/indexes for users and user_otps, rejects missing types
or same canonical-phone/type conflicts (including soft-deleted rows), and prepares DDL.
It adds generated multi_account_phone_key without rewriting existing phone values,
then creates unique indexes before removing recognized global phone constraints:

- users_registration_phone_role_unique: canonical phone, user_type.
- user_otps_registration_phone_role_unique: canonical phone, user_type, workflow type.

It preserves unrelated indexes and existing generated columns. MySQL enum expansion
adds seller while retaining existing values; unexpected nullability/default causes a
preflight refusal. SQLite generated-column detection uses table_xinfo; SQLite inline
unique constraints requiring table rebuilds are refused. The repository's named-index
fixtures and original Laravel-style schema are supported. No tables/databases/rows
are dropped, truncated or recreated. down() deliberately refuses unsafe restoration
of global uniqueness. Concurrent registration collisions still map to compatible API
identity errors. Existing transaction/record locks are preserved.

Canonical SQL whitespace replacement now has explicit MySQL UTF-8 binary collation.
The real MariaDB test caught an illegal-collation mix while indexing generated keys;
this adjustment fixes that without changing phone values or SQLite normalization.

DDL can lock tables and MySQL DDL is not transactionally rolled back. Deploy in a
controlled maintenance window with a reviewed snapshot and sufficient disk capacity.
The audit detects data problems before starting DDL. Interrupted partial migrations
must be reviewed and retried using their idempotent audit, never blindly rolled back.

## Isolated validation

`php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml`
PASS: 266 tests, 2,041 assertions, 50.717 seconds.
Previous global-OTP baseline: 259 tests / 1,991 assertions. Existing multi-role tests
were revised to assert independent identities and legacy role challenges separately.
Coverage includes all four account types on one phone, distinct IDs/passwords,
simultaneous pending records, role/password mismatch, missing/invalid role, ambiguous
phone/email, inactive/unverified users, exact token ownership, logout revocation and
subsequent independent login, authenticated profile isolation, recovery isolation,
duplicate constraints, idempotent/conflict-refusing migration audit and existing OTP
expiration, single use, rollback/interleaving, no-registration-delivery and rate limits.

`php tests/Support/multi-account-mariadb.php`
PASS: 10 assertions on fresh isolated MariaDB 10.4, 127.0.0.1:33324. Tested original
raw indexes PLUS generated canonical global indexes, enum extension, cross-role
inserts, same-role canonical rejection in users/pending challenges, unchanged original
rows, idempotent preflight, conflict refusal and unsafe-down refusal. The temporary
server was shut down; its test databases remain in the generated temporary directory.
The script verifies the server's datadir matches that temporary directory before any
schema operation and never uses application database credentials. It is a manual
migration test, not an automatically invoked production command.

External integrations were mocked/faked. SQLite tests cannot prove real simultaneous
Production lock scheduling; no new locking algorithm was introduced. Real Passport
JWT signing/mobile persistence were not end-to-end tested; tests assert exact factory
owner IDs, real stored-token revocation, and API account isolation. No frontend build
was applicable because mobile source is absent and web assets were not changed.

## Files and minimal package

Runtime changes:
- app/Helpers/MobileNumber.php: scoped uniqueness/lookup and safe SQL collation.
- app/Http/Requests/api/User/Auth/{RegisterRequest,LoginRequest,VerifyAccountRequest,ForgetPasswordRequest,ResetPasswordRequest,SendOtpRequest,SendPhoneOtpRequest,ProfileCompleteRequest}.php
- app/Http/Controllers/api/User/Auth/{RegisterController,LoginController,VerifyAccountController,ForgetPasswordController,ResetPasswordController,SendOtpController}.php
- app/Traits/HelperTrait.php: scoped recovery lookup.
- app/Exceptions/Handler.php: scoped unique-index conflict recognition.
- app/Models/User/User.php: hide generated identity key from raw serialization.
- database/migrations/2026_11_24_000001_scope_account_phone_uniqueness_by_type.php.

Tests: OptionalRegistrationEmailTest.php, BuyerSellerAccessTest.php and
 tests/Support/multi-account-mariadb.php. This report is the deployment handoff.
`docs/qa/multi-account-runtime.patch` includes only runtime files and the new migration,
against a3a89fa. It excludes tests/docs/config/credentials and all unrelated changes.

OtpService, services configuration, routes, rate-limit middleware and logout code have
NO diff from a3a89fa. The previously committed global fixed-1111 hotfix is preserved.
No random OTP generation was introduced. Registration still never calls WhatsApp;
other OTP delivery retains the previous notification behavior. Known fixed recovery
codes still do not prove phone ownership; that pre-existing explicitly requested risk
is unchanged.

## Production preflight and deployment requirements (NOT executed)

First confirm exact server directory/version, secure backup outside public roots,
working-tree/deployed differences and authorized controlled test identities. Obtain
explicit approval for migration execution; this task does not authorize Production
DDL or deployment. Keep the app in maintenance through migration and code activation.
Do not run composer update, migrate:fresh, rollback, reset, truncation or data cleanup.

After copying reviewed code/migration to an isolated candidate release on the server,
the following PHP snippet performs ONLY metadata reads and a duplicate audit. Run
from that candidate release with its confirmed environment. It prints counts and DDL,
not credentials or user identifiers. Query scans may be expensive; schedule the audit.

```bash
php <<'PHP'
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo 'Deployed account counts by stored type:', PHP_EOL;
foreach (Illuminate\Support\Facades\DB::table('users')->selectRaw('user_type, COUNT(*) AS total')->groupBy('user_type')->get() as $row) {
    echo json_encode($row), PHP_EOL;
}
$migration = require 'database/migrations/2026_11_24_000001_scope_account_phone_uniqueness_by_type.php';
foreach ($migration->audit() as $sql) { echo $sql, ';', PHP_EOL; }
PHP
```

Use `git apply --check /private/path/multi-account-runtime.patch` against the confirmed
compatible release; review every diff and back up every affected file, .env and schema
securely. Only AFTER separate explicit approval, the required migration is:

```bash
php artisan migrate --path=database/migrations/2026_11_24_000001_scope_account_phone_uniqueness_by_type.php --force
php artisan config:clear
php artisan config:cache
```

No environment-variable changes. Reload PHP/OPcache and long-lived workers using the
host procedure. Validate with explicitly authorized accounts only: distinct buyer and
seller registrations/IDs, selected-type phone/email login, logout, second login, and
role-specific recovery. Confirm no registration provider calls. Mobile integration
must be ready before enabling the feature for shared-phone users.

Rollback: before any cross-role accounts are created, an operator can review restoring
prior code and global indexes after an empty-conflict audit. After usage, old code is
not compatible with independent shared-phone accounts. Keep the scoped schema and
use a forward fix; do not merge/delete users or blindly restore a pre-change database.
A migration's safe down refusal is intentional. Legacy role memberships remain as-is;
any requested identity conversion needs its own approved data plan.
