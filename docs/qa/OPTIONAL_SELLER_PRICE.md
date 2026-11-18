# Optional seller submission price

## Scope and behavior

This repository is Laravel 8.83.29 with Blade dashboard views and Laravel Mix 6.
No AGENTS.md was found. The initial working tree was clean on main. Available
skills were reviewed; no specialized skill is needed for this backend change.
PHP/PHPUnit, ripgrep, local MariaDB and computer-use tools are available.

The customer “List Your Item for Sale” form is not in this repository. Its API is
POST /api/user/seller-submissions, protected by the existing auth:api and APISettings
middleware. StoreSellerSubmissionRequest now accepts omitted, null and empty
expected_price values. Global TrimStrings/ConvertEmptyStringsToNull middleware
normalizes blank input. Numeric and min:0 rules are unchanged, including explicit
zero. Existing DECIMAL(12,2) storage precision is unchanged.

The controller stores the request value directly; no model default/cast converts
null to zero. SellerSubmissionResource returns null unchanged on creation,
detail and my-list responses. The dashboard detail view already displays null
as “-”. There are no active price calculations, filters or sorts on this field.
The shared update request inherits nullable validation: omission preserves the
existing value, and null/empty clears it. Ownership and review-state gates remain.

## Database and deployment

New migration: 2026_11_18_000000_allow_null_seller_submission_expected_price.php.
The original column is NOT NULL; the migration changes only that column to nullable
DECIMAL(12,2), following the project's MySQL/MariaDB ALTER conventions. It does not
rewrite prices. Automatic rollback refuses to impose NOT NULL on valid unpriced
listings. A rollback needs a reviewed compatibility plan.

The migration was executed only on a newly initialized, disposable MariaDB 10.4.32
instance: 127.0.0.1:33328, database mazadat_notifications_test, data directory
storage/framework/testing/optional-price-mysql. No application/production database,
environment file, deployed migration or existing application records were changed.

Deploy the nullable schema before the application validation change. Old clients
that still require a price remain compatible but cannot use the optional-price
feature until updated. The external customer form must remove its required marker
and validator, send null or omit a blank price, and render null without a zero
fallback. That client work and browser verification cannot be completed here.
Nothing was committed, pushed or deployed.

## Verification

The new integration tests reuse NotificationTestCase's allow-listed disposable
MariaDB harness. They skip without NOTIFICATIONS_MYSQL_CONFIG. Its JSON connection
config must specify mysql, host 127.0.0.1, port 33328 and database
mazadat_notifications_test, with no URL/socket. Never point it at a persistent DB.
The harness creates and removes only disposable test fixtures.

Commands executed from the repository root:

```powershell
$env:NOTIFICATIONS_MYSQL_CONFIG = 'storage/framework/testing/optional-price-mysql.json'
php vendor/phpunit/phpunit/phpunit -c phpunit.bugfixes.xml --filter OptionalSellerSubmissionPriceTest
```

Passed: 10 integration tests covering missing/empty/null/decimal/zero creation,
actual database persistence, detail/list retrieval, rendered dashboard price rows,
invalid numeric/negative/array input, other required fields, clearing/preserving
prices through editing, existing-price preservation across migration and guarded
rollback. Filesystem upload movement alone is stubbed; HTTP middleware, validation,
controllers, models, media records, database and Blade rendering run normally.

```powershell
# Run without NOTIFICATIONS_MYSQL_CONFIG for the existing in-memory suites:
php vendor/phpunit/phpunit/phpunit -c phpunit.bugfixes.xml tests/Feature/Api/AuctionRequestRegressionTest.php --filter expected_price
php vendor/phpunit/phpunit/phpunit -c phpunit.bugfixes.xml --filter 'SellerSubmissionWorkflowTest::test_(owner_resubmits|other_seller|other_states)'
```

Passed: 3 existing price serialization/retrieval tests (12 assertions) and 3 existing
editing/authorization/state tests (40 assertions). The full SellerSubmissionWorkflowTest
run had 5 passing tests and one logout notification mock error: sendToDevice was
expected once but called zero times. Re-running its approval tests with the original
required-price validation reproduced the same error, confirming it predates this change.

PHP syntax checks on all three changed PHP files and git diff --check passed.
No static-analysis tool is configured. A frontend build was not run: node_modules
is absent and the customer form is not present. Dashboard HTML was rendered by the
integration tests; no interactive customer browser flow could be verified.
