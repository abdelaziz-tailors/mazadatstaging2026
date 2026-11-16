# Form route audit — 2026-11-16

Scope: inspected 108 form openings (HTML forms and Laravel Collective open/model calls) under resources/views, and searched first-party PHP templates elsewhere. This is a source and route audit, not a submission test of every business operation.

## Fixed

18 delete forms now use named routes, including the earlier buyer fix. Covered admins, animal pens, categories, colors, gifts, notifications, packages, pages, partners, products, product comments, user reports, reports, roles, the legacy sound action under settings, sounds, users and vendors.

The old hardcoded URLs omitted the locale. SetDashboardLocale redirects to the dashboard locale, which can turn a form submission into a GET and cause HTTP 405. Named routes generate the localized destination directly.

Also corrected invalid singular/misspelled delete paths (vendor, sound, colorss). The pages delete form now targets pages.destroy instead of deleting a package. The legacy settings action explicitly handles sounds and retains that target using sounds.destroy.

## Verification

Run: `php vendor/phpunit/phpunit/phpunit tests/Feature/Dashboard/DeleteFormRoutesTest.php`

Result: 1 test, 108 assertions. Checks all 18 source forms, POST plus DELETE method override, Arabic URL prefix, matching named DELETE route and absence of a dashboard locale redirect. The controller is never executed and no record is deleted. `git diff --check` passed.

## Separate existing findings

- dashboard/pages/providers templates reference admin.providers.store, update and save_password, but no providers routes are registered. Its delete form also targets /admin/providers/{id}, which has no route. No replacement was guessed because there is no corresponding registered endpoint.
- dashboard/pages/users/change_password.blade.php and vendors/change_password.blade.php reference the same unregistered admin.providers.save_password route. There are no registered users/vendors password routes either.
- front/home.blade.php contains nested contact forms and references the unregistered send-massage route. Restoring contact submission needs a real endpoint; changing the locale cannot fix it.
- payment/form.blade.php posts to /payment/initiate, which is registered outside the localized route groups. It does not have this locale redirect defect.
- Translation manager forms use controller-generated actions rather than hardcoded localized dashboard paths.

No live server deployment or real form submissions were performed.
