# Buyer and Seller access with one mobile number

Implemented on 2026-11-18. Backend implementation is verified; external customer frontend integration remains outstanding.

## Findings and architecture

Confirmed Laravel 8.83.29, Blade and Laravel Mix 6. Repository root: C:/xampp/htdocs/mazadatstaging. No AGENTS.md found in the repository or checked ancestors. Available skills and engineering tools were reviewed; no specialized workflow was applicable to this Laravel authentication change. Exploration used ripgrep, PowerShell, PHP, Git and PHPUnit. PHP PDO supports SQLite and MariaDB. No dependencies were added.

Users are one identity in users, with one enum user_type: buyer, vendor, buyer_vendor or seller. Seller means consignor; vendor means organizer and has a linked admins partner account. buyer_vendor does not authorize consignor access. Standard registration stages user_otps records and creates users only after OTP verification. Login authenticates through the web user provider and issues Laravel Passport account tokens for auth:api. Relationships and financial ownership use the same users.id.

Root cause: RegisterRequest globally rejects matching normalized phones; VerifyAccountController also rejects existing identities; LoginController requires the selected type to match user_type, except the existing buyer_vendor exception. There was no Buyer/Seller membership representation. Removing uniqueness alone would create ambiguous identities.

Read-only SHOW COLUMNS / SHOW INDEX inspection of the configured local database confirmed the user_type enum, is_active/is_verified fields, and user_otps unique(phone,type). That installation does not have the generated normalized identity unique indexes supplied by the earlier 2026_10_20 migration. No persistent records were read into output or changed. Login now rejects ambiguous normalized phone and case-insensitive email results. Existing data needs the earlier migration's reviewed audit before installing its identity indexes; this task does not silently install that migration or merge duplicates.

## Behavior and security

New registrations keep the existing registration and OTP flow. To add Buyer or Seller access, submit the existing /api/user/register payload, selected user_type (buyer or seller), and the account's CURRENT password and confirmation. Existing name/username/email fields remain required/validated as before; fields belonging to the authenticated identity are allowed through uniqueness validation. Supplied profile fields do not overwrite the existing account.

Only an active, verified buyer or seller with the correct existing password can stage role_access OTP verification. /api/user/verify-account consumes that OTP after checking expiry, identity ambiguity, account status, allowed role and a fingerprint binding the immutable user ID and unchanged password hash. A password reset invalidates pending access. The existing identity, credentials, original user_type and relationships are retained. Vendor/admin access cannot be obtained through this flow. OTP testing configuration remains the existing environment-controlled behavior; delivery is not bypassed in normal environments.

A transaction locks the pending OTP and user, inserts only missing membership and consumes the OTP. Unique(user_id,role) prevents duplicate membership. Replay is rejected. Existing unverified accounts must finish account verification first. Login now enforces active and verified status, including for pre-existing accounts; assess legacy unverified accounts before rollout rather than bypassing verification.

Login accepts either authorized role after membership is provisioned. Responses retain user_type and add available_roles and active_role. Tokens remain account-scoped Passport tokens: authorized account capabilities are cumulative, and active_role is response/UI context, not a restrictive token scope or a persistent setting. Profile responses default active_role to the original type. Clients retain their selected role locally and use available_roles for navigation. No role-switch endpoint or client-supplied permission grant is introduced.

Seller invoice access recognizes added seller membership and retains identity ownership filtering. Seller selection recognizes membership but still requires active status and an approved submission for that organizer. Seller submission ownership/review-state gates remain. Buyer financial checks recognize added Buyer membership; wallet calculations continue to follow the existing account/activity-based rules, rather than a new per-token wallet view. No partner/admin permission changes.

Mobile normalization reuses MobileNumber's existing Saudi, Egyptian and international conventions. Stored legacy numbers are not rewritten.

## Files changed for this task

- app/Models/User/User.php: effective membership, membership checks and query scope, retaining the legacy type.
- app/Http/Requests/api/User/Auth/RegisterRequest.php: password-authenticated eligibility and uniqueness exceptions for the same identity.
- app/Http/Requests/api/User/Auth/LoginRequest.php: string validation for phone/password.
- app/Http/Requests/api/User/Auth/VerifyAccountRequest.php: normalized role OTP lookup.
- app/Http/Controllers/api/User/Auth/RegisterController.php: stage separate role_access OTP using the existing OTP delivery workflow.
- app/Http/Controllers/api/User/Auth/VerifyAccountController.php: transactional role provisioning after OTP verification.
- app/Http/Controllers/api/User/Auth/LoginController.php: unambiguous identity resolution, status and membership validation.
- app/Http/Resources/User/UserResource.php: additive available_roles/active_role fields.
- app/Support/AuctionSellerEligibility.php and app/Http/Controllers/api/User/SellerUserController.php: include additional sellers while retaining approval scoping.
- app/Http/Controllers/api/User/Invoice/UserAuctionController.php: membership-based seller invoice gate.
- app/Http/Controllers/api/User/Profile/UserProfileController.php: membership-based buyer financial checks.
- database/migrations/2026_11_18_000001_create_user_access_roles.php: additive membership table, user FK, allowed Buyer/Seller enum and composite unique index. Rollback deliberately requires a reviewed compatibility plan.
- tests/Feature/Api/User/Auth/BuyerSellerAccessTest.php: complete registration/OTP/login flows, security and integrity regression coverage; reuses and inherits the existing optional-email suite.
- tests/Feature/Api/User/Auth/OptionalRegistrationEmailTest.php: explicitly verify the legacy-login fixture to satisfy enforced login verification.
- tests/Unit/Http/Requests/Api/User/Auth/LoginRequestTest.php: update stale rule expectations to current email/phone contract and string validation.
- tests/Feature/Bugfixes/BuyerSellerRoleMysqlTest.php and tests/Support/role-membership-worker.php: MariaDB migration, foreign key, unique membership and concurrent writer checks.
- docs/qa/BUYER_SELLER_SHARED_MOBILE.md: this implementation report.

Pre-existing optional seller-price changes were preserved and are not part of this task.

## Verification executed

From the repository root:

```powershell
php vendor/phpunit/phpunit/phpunit -c phpunit.bugfixes.xml tests/Feature/Api/User/Auth/BuyerSellerAccessTest.php
# PASS: 73 tests, 432 assertions (includes inherited optional-registration regressions).
php vendor/phpunit/phpunit/phpunit -c phpunit.bugfixes.xml tests/Unit/Http/Requests/Api/User/Auth/LoginRequestTest.php
# PASS: 6 tests, 11 assertions.
php vendor/phpunit/phpunit/phpunit -c phpunit.bugfixes.xml --filter 'AuctionSellerSelectionTest|SellerSubmissionWorkflowTest::test_(owner_resubmits|other_seller|other_states)'
# PASS: 8 tests, 97 assertions.
$env:NOTIFICATIONS_MYSQL_CONFIG = 'storage/framework/testing/optional-price-mysql.json'
php vendor/phpunit/phpunit/phpunit -c phpunit.bugfixes.xml --filter BuyerSellerRoleMysqlTest
# PASS: 3 tests, 33 assertions.
```

MariaDB 10.4.32 ran only against the previously initialized disposable instance under storage/framework/testing/optional-price-mysql, bound to 127.0.0.1:33328, database mazadat_notifications_test. The allow-listed harness creates/removes disposable fixtures; new migration was executed only in those fixtures and SQLite memory. Initial MariaDB attempts failed because that disposable server was stopped; starting it in the same invocation produced the passing final result. Early API test failures were fixture snapshot/default-verification mismatches and were corrected before the final passing run.

Coverage includes new Buyer and Seller, both linking directions, both selected-role logins, wrong credentials, wrong/expired OTP, inactive/unverified users, password changes, vendor denial, repeats/replay, unchanged account snapshots, normalized legacy mobile lookup, API key enforcement, seller-invoice denial, FK/unique enforcement and concurrent membership creation. The concurrent worker test checks membership locking/idempotency; simultaneous full HTTP OTP requests are not exercised. HTTP middleware, validators, controllers, database persistence and resources run in-process. SMS transport and Passport token signing are mocked by the reused harness; real SMS delivery and a bearer-token round trip were not tested. Existing seller selection/workflow tests exercise approval and ownership gates.

PHP syntax and git diff whitespace checks were run. No static-analysis tool is configured. No frontend build/browser flow was run because the customer frontend is outside this repository. No claim is made that all repository tests or external clients were tested.

## Rollout and remaining integration

Deploy the new additive role-table migration before application code. Do not run the full migration history blindly: the local installation has unapplied identity-hardening work requiring its own audit. No application migration was executed, existing account data was not changed, and nothing was committed, pushed or deployed.

The customer frontend must send the current account password when requesting the other role, follow the normal OTP screen, preserve the chosen role locally, route by active_role/available_roles rather than only user_type, and handle verification/status failures. Verify Buyer/Seller navigation, profile refresh, account token use and financial screens in that client before calling the customer-facing feature complete. Organizer/vendor registration remains its separate existing onboarding flow.
