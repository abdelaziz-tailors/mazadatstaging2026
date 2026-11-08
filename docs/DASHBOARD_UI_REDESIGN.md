# Dashboard UI redesign

The Admin and Partner dashboards now share a responsive green workspace with a quieter sidebar, a searchable header, clear page hierarchy, soft content surfaces, consistent fields and tables, and accessible feedback. All screens extending `dashboard.layouts.app` receive the design system. Login uses the same tokens through `dashboard.layouts.auth`.

## Files and responsibilities

- `public/dashboard/css/workspace.css`: color, typography, spacing, radius, shadow and motion tokens; shared navigation, KPIs, charts, tables, DataTables, filters, fields, Select2, dialogs, dropdowns, profiles and login styles. Includes logical positioning for RTL/LTR, mobile and tablet breakpoints, reduced motion, forced colors and printing.
- `public/dashboard/js/workspace.js`: keyboard navigation, mobile drawer focus handling, mobile search, accessible names and table scrolling regions, validation links, dialog titles, DataTables loading/error announcements and password visibility. Page-specific JavaScript continues to own requests and submissions.
- `public/dashboard/js/script.js`: shorter menu transitions respecting reduced motion; guarded calls to optional legacy charts when toggling the sidebar.
- `resources/views/dashboard/layouts/{app,auth,sidebar,sidebar_subscriber}.blade.php`: shared assets, semantic page landmarks, navigation controls, workspace identity and login composition. The existing role/permission branches and destinations remain intact. Unused Morris demo chart scripts were removed from the application layout; no dashboard view references their target elements.
- `resources/views/dashboard/partials/{workspace-assets,navigation-brand,validation-summary,empty-state,page-header,stat-card,stat-row}.blade.php`: reusable presentation components.
- `resources/views/dashboard/home.blade.php`: overview banner, readable chart labels and grid, empty states and reduced-motion chart settings. Existing report values and chart series are preserved.
- `resources/views/dashboard/auth/login.blade.php` and `pages/admin-profile/profile.blade.php`: visible labels, autocomplete, password visibility and a keyboard-accessible photo picker. Profile labels are localized. The photo clear control now describes its existing behavior: clearing the pending file selection.
- `resources/views/dashboard/pages/videos/_form.blade.php`: removed the duplicate error summary and fixed-position error styling; errors now use the shared summary and existing field messages.
- `resources/lang/{ar,en}/dashboard-ui.php`: new presentation strings, including translations for previously hardcoded sidebar labels. Existing `app.php` translations are unchanged.
- `tools/check-dashboard-ui.{php,cjs}`: isolated template rendering and browser verification without running Laravel HTTP endpoints.

There are no changes to routes, controllers, models, database schema, permission rules, APIs, payment/settlement logic or auction operations. No frontend packages were added, and no asset compilation is needed. New assets use modification-time cache versions.

## Initial redesign verification

The PHP checker bypasses the application's cached configuration and uses testing configuration with SQLite `:memory:`, array sessions/cache, and temporary compiled views. It throws if any view attempts a database query. It uses existing language dictionaries through a read-only preview adapter, avoiding TranslationHelper's normal missing-key writes.

```powershell
php tools/check-dashboard-ui.php "$env:TEMP/mazadat-dashboard-ui"
node tools/check-dashboard-ui.cjs "$env:TEMP/mazadat-dashboard-ui" "<path-to-installed-playwright-module>"
```

Playwright is a verification dependency only. The browser checker serves static previews and existing public assets on loopback. DataTables requests use fixture rows; no actual CRUD endpoints are called. Reports and screenshots are written to the temporary preview folder. `--interactions-only` runs just the interaction checks.

- All 209 dashboard Blade templates compile.
- The final browser pass completed 216 page/viewport checks and 1,106 assertions without failures. An additional settled-animation check verifies that normal-motion dialogs finish fully opaque.
- 64 real-template previews render: 16 representative screens × Admin/Partner × English/Arabic. These include overview, login, lists, filters, wallet, create forms, permission groups, profile, validation, and real row-action partials.
- Browser coverage uses 1440px, 834px and 390px, plus 320px and 1024px for the overview, login and action/dialog previews. It checks direction, page overflow, content/sidebar geometry, JavaScript exceptions and reduced motion.
- Interaction checks cover mobile navigation and focus wrapping, Escape, sidebar collapse, submenu keyboard actions, mobile search, notification/account menus, modal opening/closing and mobile sizing, validation-field focus, password visibility, permission dependencies, Select2, table dropdowns and empty results. Normal-motion dialogs are checked separately to catch transformed containing blocks.
- JavaScript syntax, PHP syntax and `git diff --check` pass.

## Verification limits and existing issues

- The dashboard's existing locale middleware still forces the configured dashboard locale (currently Arabic). Both directions are verified through isolated templates; no misleading language switcher or middleware change was introduced.
- Offline browser checks substitute existing local jQuery/Select2 assets for CDN requests and stub Toastr delivery. Google fonts and live notification/CRUD/backend responses are outside this preview check. Their existing production integrations remain in place.
- The safe legacy presentation PHPUnit selection passed 15 of 20 tests (52 assertions evaluated). Five `MobileDataTablesCssTest` methods fail because their helper extracts the last `@media (max-width: 991px)` block from the legacy `theme.css`, which is a navbar block. That stylesheet is byte-identical to HEAD; these are pre-existing source-inspection failures. The new browser checker validates rendered mobile tables. The full database-dependent suite was not run.
- No deployment, real account mutation or production operation was performed.

## Follow-up visual QA and refinements — 2026-11-08

### Implemented refinements

- Overview: balanced the seven-KPI layout into two complete rows on desktop; replaced zero-series sales/registration charts with localized empty states; reduced daily tick density and marker clutter; localized sales tooltips for English previews.
- Auction form: removed its second jQuery and Select2 downloads and moved initialization into the existing script stack after shared dependencies. City/partner selectors, partner visibility and the terms counter retain their existing behavior. Terms now occupy the complete form row and section dividers use the shared soft border token.
- Profile: aligned the photo controls with the form's reading direction and removed the nested border/focus ring inside the phone field. Existing phone normalization and file-selection behavior remain intact.
- Tables: added a lightweight processing indicator that stops animating under reduced motion. Pagination arrows now have translated accessible names, disabled controls leave the tab order, and the current page is announced.

Changes are confined to frontend views, shared CSS/JS, new UI strings and QA tools. No backend files, permissions, locale middleware, existing language dictionaries, or legacy tests were changed in this follow-up.

### Real local application testing

`tools/dashboard-local-qa.php` runs the actual Laravel HTTP kernel, controllers, views, permissions and existing local database. It verifies a loopback MySQL configuration, enforces database-session read-only transactions and rejects non-SELECT application queries. Temporary file sessions are kept outside the repository. Its HTTP mode is restricted to loopback, a QA header, read requests and DataTables read endpoints; action submissions are blocked.

The existing local Admin account was authenticated through a temporary QA session. **Credential-based login was not tested:** the normal controller enables remember-me, which can update the account record. There were no role/permission overrides, fixture account replacements or database mutations.

- Six real screens passed at 1440px, 834px and 390px: overview, auction creation, Orders, wallet, profile and role creation. This produced **18 successful screen/viewport checks and 65 assertions**, with no JavaScript exceptions, network failures or page overflow.
- The existing Select2, font and other CDN resources loaded without substitution in the successful real-app run. Earlier sandbox network denials were resolved by running the browser with approved network access.
- Six real Orders DataTables responses returned HTTP 200 and JSON, including filter requests. Local Orders data is empty; no populated live pagination or live row-action modals are claimed.
- A final real mobile Orders pass completed nine additional assertions, including translated pagination names and correctly disabled controls, with two successful DataTables responses and no JavaScript/network errors.
- Real interactions included sidebar collapse/mobile opening and Escape, notifications, Select2 opening, terms-counter updates, phone normalization, table filtering and visible page-length controls. No forms were submitted.
- Users, auction listing and Packages each returned **403** for the available account. These permission restrictions were preserved.
- **No local Partner account exists.** No account was created, and no real authenticated Partner coverage is claimed. English remains preview-only because the existing locale middleware forces Arabic.

`tools/check-dashboard-real.cjs` records real-screen results and private screenshots in `%TEMP%/mazadat-real-dashboard-qa`. Its `--table-only` mode supports a final focused pagination accessibility check. These tools do not add application routes or authentication endpoints.

### Isolated and safe checks

- All **209** dashboard Blade templates compile, and **68** previews render without database queries.
- **228** isolated page/viewport checks and **1,183 assertions** passed. These use the documented fixture data, local CDN substitutions and Toastr stub; they are separate from real application results.
- A subsequent **57-assertion** focused preview run passed, including real row-action partials, modal focus, permission-checkbox behavior, page-two navigation with fixture rows, loading announcements, recovery after loading and reduced-motion indicators.
- Focused existing presentation PHPUnit checks passed: **14 tests, 38 assertions**, with SQLite `:memory:` and cached application configuration bypassed. The unrelated legacy tests noted above were neither altered nor rerun.
- JavaScript/PHP syntax checks and `git diff --check` passed.

Remaining limits: no real Partner session, no credential login, no live populated-table pagination or row modals, and no create/update/delete/payment actions. No deployment or production operations were performed.

The temporary QA HTTP server was stopped and its authentication state/session files were removed after verification. Reports and screenshots remain in the temporary output folders.
