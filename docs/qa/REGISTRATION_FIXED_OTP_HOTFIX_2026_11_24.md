> **SUPERSEDED: scope expanded to every OTP workflow.** See [the global hotfix report](GLOBAL_FIXED_OTP_HOTFIX_2026_11_24.md). The historical deployment steps and three-file package below must not be used. The runtime patch at the existing filename now contains the cumulative five-file global hotfix.

﻿# Registration fixed OTP hotfix - 2026-11-24

## Findings and scope

Initial working tree was clean at c0227b9. No applicable AGENTS.md was found.
Current OtpService uses fixed 1111 locally, but Production only for OTP_QA_PHONES.
Unlisted Production registrations generate random codes and invoke Infobip WhatsApp;
failed delivery clears the challenge and returns HTTP 500. OTP_TESTING and
OTP_TEST_CODE are obsolete and have no active config mapping. Recovery shares this
service, so globally changing usesFixedCode would also change password recovery.
The local .env has APP_ENV=local and legacy OTP settings; bootstrap/cache/config.php
exists. Runtime configuration may therefore differ from .env until rebuilt.
No Production log evidence or authenticated server connection was available. The
policy mismatch is confirmed in source; the exact live provider failure is unconfirmed.
The repository .cpanel.yml names /home/dev02/public_html/oun, but this is NOT verified
as the current Production directory. Its composer-update/migrate tasks are unsuitable
for this hotfix and must not be run.

## Implementation and effective configuration

Only three runtime files are needed:
- app/Services/OtpService.php: single fixed-code constant, registration challenge and
  stored-code matcher independent of environment/phone policy.
- app/Http/Controllers/api/User/Auth/RegisterController.php: registration challenge;
  removes delivery service dependency and entire delivery branch.
- app/Http/Controllers/api/User/Auth/VerifyAccountController.php: registration and
  role-access pending records use registration matching, including locked recheck.

Tests: tests/Feature/Api/User/Auth/OptionalRegistrationEmailTest.php.
No RegisterRequest, schema, routes, dependency or credential changes.
No environment variables are required or changed: registration is always fixed,
1111, delivery disabled in application code. OTP_QA_PHONES and recovery policy remain
unchanged. Do not enable obsolete global testing flags. Existing pending challenges
are untouched; their stored codes continue to work until expiry. A new submission
uses 1111. Expiry, locks, single-use consumption, activation, response conventions,
and existing rate limiting are preserved. No queue job or provider call is created.

## Manual deployment (Bash on the confirmed server)

First obtain the exact Production directory, deployed version and authorized test
phone/username from the operator. Do not assume the historical cPanel path is correct.
Copy only the three reviewed runtime files into a private release directory, preserving
relative paths. Do not upload .env, tests, dependencies, or unrelated working files.

```bash
set -euo pipefail
read -r -p 'Confirmed Production absolute directory: ' APP_DIR
read -r -p 'Private release absolute directory: ' RELEASE_DIR
cd "$APP_DIR"
test -f artisan && test -f .env
pwd -P
git rev-parse HEAD
git status --short
# Review diffs for these files; reconcile any deployed changes before replacement.
git diff -- app/Services/OtpService.php app/Http/Controllers/api/User/Auth/RegisterController.php app/Http/Controllers/api/User/Auth/VerifyAccountController.php
umask 077
BACKUP="$HOME/mazadat-private-backups/registration-$(date -u +%Y%m%dT%H%M%SZ)"
mkdir -p "$BACKUP"
chmod 700 "$HOME/mazadat-private-backups" "$BACKUP"
# Confirm this resolved backup directory is outside ALL configured public web roots.
realpath "$BACKUP"
cp -p .env "$BACKUP/environment"
FILES=(app/Services/OtpService.php app/Http/Controllers/api/User/Auth/RegisterController.php app/Http/Controllers/api/User/Auth/VerifyAccountController.php)
for f in "${FILES[@]}"; do
  mkdir -p "$BACKUP/$(dirname "$f")"
  cp -p "$f" "$BACKUP/$f"
  php -l "$RELEASE_DIR/$f"
  diff -u "$f" "$RELEASE_DIR/$f" || test "$?" -eq 1
done
# Continue only after reviewing each diff for unrelated deployed modifications.
# Deploy during a controlled maintenance window to avoid mixed-version requests.
php artisan down
for f in "${FILES[@]}"; do cp "$RELEASE_DIR/$f" "$f"; done
# Required environment updates: NONE. Leave .env unchanged.
php artisan config:clear
php artisan config:cache
php artisan up
```

Reload PHP-FPM/OPcache via the host's established deployment mechanism if needed;
reload any long-lived application workers. No migration, composer update, database
cleanup, reset or existing-user modification is needed. If deployment fails while
down, restore the backed-up files and rebuild cache before bringing the app up.

Verify effective behavior without printing environment secrets (run in project root):

```bash
php <<'PHP'
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$otp = $app->make(App\Services\OtpService::class);
$c = $otp->registrationChallenge();
$ok = $app->environment('production') && $app->configurationIsCached()
    && $c['otp'] === '1111' && $otp->matchesRegistration($c['otp'], '1111')
    && !$otp->matchesRegistration($c['otp'], '2222')
    && $c['expire_at']->equalTo(now()->startOfSecond()->addMinutes(10));
echo json_encode(['environment' => $app->environment(),
    'configuration_cached' => $app->configurationIsCached(),
    'fixed_registration_code_matches_1111' => $c['otp'] === '1111',
    'checks_pass' => $ok]), PHP_EOL;
exit($ok ? 0 : 1);
PHP
# Confirm no delivery service, provider, or job dispatch remains in the controller:
if grep -En 'SmsService|sendRegistrationOtp|sendWhatsappOtp|dispatch\(' app/Http/Controllers/api/User/Auth/RegisterController.php; then exit 1; fi
```

## Controlled API verification

Use ONLY an explicitly authorized test account. No live account was created here.
Set BASE_URL, API_KEY, TEST_PHONE and TEST_USERNAME privately (do not log credentials).
Use a new controlled phone and username. The following keeps password out of arguments:

```bash
read -r -s -p 'Test password: ' TEST_PASSWORD; echo
export TEST_PHONE TEST_USERNAME TEST_PASSWORD
PAYLOAD=$(php -r 'echo json_encode(["name"=>"Authorized Test", "phone"=>getenv("TEST_PHONE"), "user_name"=>getenv("TEST_USERNAME"), "user_type"=>"buyer", "password"=>getenv("TEST_PASSWORD"), "password_confirmation"=>getenv("TEST_PASSWORD")]);')
printf '%s' "$PAYLOAD" | curl --silent --show-error --write-out '\nHTTP %{http_code}\n' "$BASE_URL/api/user/register" -H "x-api-key: $API_KEY" -H 'Accept: application/json' -H 'Content-Type: application/json' --data-binary @-
unset PAYLOAD TEST_PASSWORD
```

Require HTTP 200 and success=true. Before verifying, use the approved admin/database
read-only tool to confirm exactly one unverified type=register pending row for the
returned canonical phone, ten-minute expiry, and no new users row. Do not print its
password hash or other personal data. Then:

```bash
php -r 'echo json_encode(["phone"=>getenv("TEST_PHONE"),"otp"=>"1111"]);' | curl --silent --show-error --write-out '\nHTTP %{http_code}\n' "$BASE_URL/api/user/verify-account" -H "x-api-key: $API_KEY" -H 'Accept: application/json' -H 'Content-Type: application/json' --data-binary @-
```

The verification response contains an access token: keep it private. Require success,
then confirm that intended user is verified/active, pending record is consumed, and
code/expiry cleared. Check provider outbound audit records over this test interval
and application job monitoring for zero registration delivery requests/jobs. Keep the
authorized account; do not delete Production data. Do not run automated tests there.
Invalid, expired, reused and isolation cases belong to the isolated test suite.

## Rollback

In the confirmed project directory with BACKUP set to the exact backup above:

```bash
php artisan down
for f in app/Services/OtpService.php app/Http/Controllers/api/User/Auth/RegisterController.php app/Http/Controllers/api/User/Auth/VerifyAccountController.php; do cp -p "$BACKUP/$f" "$f"; done
php artisan config:clear
php artisan config:cache
php artisan up
```

Reload PHP/long-lived workers using the host procedure. No .env restoration is needed
because it was unchanged; retain its backup securely. Rollback restores the QA-only
policy and delivery dependency. Newly issued fixed pending codes for non-QA phones
will then fail verification; users must resubmit registration under restored policy.
Do not change or delete pending rows to roll back.

## Status and risk

Production deployment, cache verification, live registration/activation and provider
outbound audit are NOT performed. Server directory/version and test identity remain
unconfirmed. Local changes are not committed or pushed. No persistent database or
existing records were touched.

A globally known OTP does not prove phone ownership. Anyone able to submit a valid
registration can verify that supplied phone using 1111. Expiry and single-use checks
remain, but they do not restore ownership proof. This is the requested behavior.

## Final local verification

Command: php vendor/phpunit/phpunit/phpunit -c phpunit.otp.xml
PASS: 247 tests, 1,767 assertions, 46.727 seconds. PHP lint on all changed PHP
files and git diff --check passed. Against the supplied 251 / 1,883 baseline,
four tests and 116 assertions fewer: two obsolete provider-failure registration
tests were removed and inherited twice; provider-dependent assertions were replaced
with no-dispatch assertions. Production lifecycle and actual registration for buyer,
seller, vendor (with uploaded document), and buyer_vendor pass with no QA allowlist.
Recovery regression, generic OTP policy, API errors, account isolation and
invalid/expired/reused codes pass. All external provider requests were mocked.

Minimal runtime patch: docs/qa/registration-fixed-otp-runtime.patch (three files only).
It was generated against c0227b9; on the confirmed server inspect and run
'git apply --check /private/path/registration-fixed-otp-runtime.patch' before applying.
If it does not apply cleanly, reconcile deployed changes manually; never force it.
Retain the backups and maintenance/cache steps above. No commit/push was performed.
