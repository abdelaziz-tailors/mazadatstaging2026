# Global fixed OTP Production hotfix - 2026-11-24

This scope supersedes the registration-only report from the same date. Do not deploy
until the final isolated regression suite passes. No deployment was performed.

## Source audit and affected workflows

All OtpService callers were read before changing shared behavior: RegisterController,
VerifyAccountController (pending registration, role access, legacy users),
ForgetPasswordController, ResetPasswordController, SendOtpController (issue/approve),
and HelperTrait's generate_otp / generate_password_otp. Searches covered app, routes,
config, OTP fields, verification-code names and random-generation functions. Social
login/registration do not implement an OTP challenge. No additional OTP workflow was
found. Public/index.php loads the audited app tree; public_old is not its application.

| Workflow | Storage / association | Generation and verification | Delivery |
|---|---|---|---|
| Buyer, seller, vendor, buyer_vendor registration | user_otps, type=register, phone | 1111; pending until verification | Never dispatched |
| Buyer/seller role access | user_otps, type=role_access, phone, requested role, password fingerprint | 1111; existing eligibility checks retained | Never dispatched, as part of registration |
| Password recovery/reset | users.password_otp, resolved verified user | 1111; reset clears password_otp | Existing WhatsApp delivery eligibility retained |
| Legacy phone resend/approval | users.otp, resolved unverified user | 1111; approval consumes otp | Existing WhatsApp delivery eligibility retained; controller currently has no public route |
| Legacy account verification | users.otp through verify-account fallback | 1111; verification consumes otp | No delivery at verification |
| Legacy helper generators | delegate to OtpService | Both always return 1111 | Helpers do not dispatch |

Original difference: Local generated fixed codes; Production generated random codes
except for QA phones. Shared usesFixedCode also controlled delivery suppression.
The revised policy separates notification eligibility from generation/verification.

OtpService.generate always returns its single FIXED_CODE constant. challenge retains
ten-minute expiry. matches and matchesRegistration require BOTH stored and supplied
code to be 1111. No environment or QA eligibility participates in generation or
verification. No random OTP generation remains in active application flows. Remaining
random calls in authentication create upload filenames, not codes.

shouldSendNotification preserves the previous inverse fixed-code eligibility solely
for NON-registration delivery: Local/development/testing skip delivery; valid configured
Production QA phones skip it; ordinary Production phones send WhatsApp. Unknown
environments and malformed QA configuration retain their previous dispatch behavior.
Provider failure/exception still invalidates the issued challenge and returns the
existing error response. It does not leave a usable 1111 challenge behind.

## Changes, data and contracts

Five runtime files, cumulatively against initial clean HEAD c0227b9:
- app/Services/OtpService.php
- app/Http/Controllers/api/User/Auth/RegisterController.php
- app/Http/Controllers/api/User/Auth/VerifyAccountController.php
- app/Http/Controllers/api/User/Auth/ForgetPasswordController.php
- app/Http/Controllers/api/User/Auth/SendOtpController.php

Regression files: OptionalRegistrationEmailTest, BuyerSellerAccessTest,
PasswordRecoveryTest and OtpServiceTest. Previous changes are preserved.
No schema, credentials, routes, middleware, API response format, existing account,
or persisted challenge was changed. No .env update is required. OTP_TESTING and
OTP_TEST_CODE remain obsolete. OTP_QA_PHONES now influences delivery only.

Existing transaction/row locks, consume behavior, account lookup, role/password
binding, ten-minute boundary rejection and rate limits are retained. Both stored code
and request code must match, so absent, expired and consumed challenges cannot be
bypassed using the known fixed code. Previously stored random codes are rejected,
including when the caller supplies their original value; request a new challenge.
No backfill or pending-record deletion is needed.

## Minimal deployment package and commands

The existing docs/qa/registration-fixed-otp-runtime.patch is updated IN PLACE to
contain the cumulative GLOBAL hotfix (five runtime files only). Its historical filename
does not limit its scope. No test or documentation files need deploying.

No server directory, deployed version or test account is confirmed. The historical
.cpanel.yml mentions /home/dev02/public_html/oun; do not assume it is current. Do not
run that file's composer-update/migrate tasks for this hotfix. On the confirmed server,
use Bash, a private copy of the patch, and a backup directory outside every configured
public web root. Inspect differences and preserve deployed modifications first.

```bash
set -euo pipefail
read -r -p 'Confirmed Production absolute directory: ' APP_DIR
read -r -p 'Private absolute patch path: ' PATCH_FILE
cd "$APP_DIR"
test -f artisan && test -f .env
pwd -P
git rev-parse HEAD
git status --short
FILES=(app/Services/OtpService.php app/Http/Controllers/api/User/Auth/RegisterController.php app/Http/Controllers/api/User/Auth/VerifyAccountController.php app/Http/Controllers/api/User/Auth/ForgetPasswordController.php app/Http/Controllers/api/User/Auth/SendOtpController.php)
git diff -- "${FILES[@]}"
git apply --stat "$PATCH_FILE"
git apply --check "$PATCH_FILE"
# Inspect the complete patch privately and reconcile any deployed modifications.
# Do not force apply or overwrite unrelated changes.
umask 077
BACKUP="$HOME/mazadat-private-backups/global-otp-$(date -u +%Y%m%dT%H%M%SZ)"
mkdir -p "$BACKUP"
chmod 700 "$HOME/mazadat-private-backups" "$BACKUP"
realpath "$BACKUP"
# Confirm the path above is outside ALL configured public roots before continuing.
cp -p .env "$BACKUP/environment"
for f in "${FILES[@]}"; do
  mkdir -p "$BACKUP/$(dirname "$f")"
  cp -p "$f" "$BACKUP/$f"
done
php artisan down
git apply "$PATCH_FILE"
for f in "${FILES[@]}"; do php -l "$f"; done
# Required .env updates: NONE. Do not change QA delivery or provider credentials.
php artisan config:clear
php artisan config:cache
php artisan up
```

Reload PHP-FPM/OPcache and any long-lived application workers using the established
hosting procedure. No composer commands or migrations. If a step fails while down,
restore backed-up files, lint and rebuild cache before bringing the application up.

Effective policy verification without secrets (in the confirmed project directory):

```bash
php <<'PHP'
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$otp = $app->make(App\Services\OtpService::class);
$now = now()->startOfSecond();
$c = $otp->challenge('+966599999999', 'password_otp');
$checks = [
  'production' => $app->environment('production'),
  'configuration_cached' => $app->configurationIsCached(),
  'registration_fixed' => $otp->registrationChallenge()['otp'] === '1111',
  'recovery_fixed' => $c['password_otp'] === '1111',
  'phone_fixed' => $otp->challenge('+966599999999')['otp'] === '1111',
  'fixed_accepted' => $otp->matches('', '1111', '1111'),
  'other_codes_rejected' => !$otp->matches('', '2222', '2222'),
  'absent_rejected' => !$otp->matches('', null, '1111'),
  'expiry_ten_minutes' => $c['expire_at']->betweenIncluded($now->copy()->addMinutes(10), $now->copy()->addMinutes(10)->addSecond()),
];
echo json_encode($checks), PHP_EOL;
exit(in_array(false, $checks, true) ? 1 : 0);
PHP
```

## Production verification and rollback

No live account, password reset or phone approval was performed. After authorized
deployment use only explicitly authorized controlled identities. Exercise register
and verify-account (1111), role access and verification, forgot-password and
reset-password (1111). Confirm intended accounts/roles only, consumed challenges,
no registration WhatsApp requests/jobs, and unchanged recovery delivery eligibility
using provider audit records. Never reset a real user's password as a smoke test.
The unrouted phone controller is covered in isolated tests; do not add a public route.
Keep API keys, passwords, access tokens and test responses private. No automated
suite or destructive tests should run against Production.

Rollback from the confirmed directory with BACKUP set to the exact secure backup:

```bash
php artisan down
for f in app/Services/OtpService.php app/Http/Controllers/api/User/Auth/RegisterController.php app/Http/Controllers/api/User/Auth/VerifyAccountController.php app/Http/Controllers/api/User/Auth/ForgetPasswordController.php app/Http/Controllers/api/User/Auth/SendOtpController.php; do
  cp -p "$BACKUP/$f" "$f"
  php -l "$f"
done
php artisan config:clear
php artisan config:cache
php artisan up
```

Reload PHP/workers as above. .env was unchanged; do not overwrite subsequent operator
changes. Rollback restores the pre-hotfix code/delivery policy, so new non-QA fixed
challenges may require reissuance. Never rewrite or delete records to roll back.

## Security and verification limits

A globally known fixed recovery code allows someone who knows a verified account's
phone to request recovery and reset its password with 1111 if delivery succeeds.
Likewise fixed phone verification does not prove ownership. Expiration, request
throttling, stored challenge association and single use remain, but do not restore
ownership proof. This is the explicitly requested global policy.

SQLite tests cannot establish real parallel row-lock behavior on the Production
engine; the existing locking code is preserved and interleaved reset/token rollback
regressions remain covered. Production root-cause logs, deployment, effective cache,
live API results and provider audit remain unverified. Nothing was committed/pushed.

## Final isolated regression results

PASS: `php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml`:
259 tests, 1,991 assertions, 39.038 seconds. This is +12 tests / +224 assertions
against the preceding registration-only run (247 / 1,767), and +8 tests / +108
assertions against the originally supplied 251 / 1,883 baseline.

All nine changed PHP files passed lint. `git diff --check` and
`git apply --reverse --check docs/qa/registration-fixed-otp-runtime.patch` passed.
The patch contains exactly the five runtime files listed above.

Coverage includes all registration account types, role access in both directions,
password recovery/reset, legacy account verification, unrouted phone resend/approval,
helper generators, environments/QA configuration matrix, wrong/malformed/absent codes,
exact expiry, reuse, account/workflow association, delivery success/failure and
exceptions, token rollback/interleaved reset, and the existing HTTP 429 rate limit.
All external HTTP/provider calls were faked or mocked. Persistent databases and
existing records were not accessed or modified. No Production deployment was made.
