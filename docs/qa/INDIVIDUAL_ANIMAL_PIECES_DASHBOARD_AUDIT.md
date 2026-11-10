    # Individual Animal Pieces — Dashboard Discovery Audit

    Audit date: 2026-11-10 (Africa/Cairo). Target: `/ar/admin/products/create/461`.

    ## 1. Executive Summary

    **Decision: conditionally ready for a small extension, not ready for a blind UI-only toggle.** Existing persistence can be reused: `App\Services\LiveVideoItemPieceService`. Dashboard already renders individual rows and both mobile and Dashboard call its bulk replacement method. They do **not** share validation, orchestration, authorization identity, or empty-input behavior.

    Principal findings:

    | Priority | Finding | Source evidence |
    |---|---|---|
    | High | Dashboard saves replace every child, including on unrelated edits; omitted pieces delete all children. | `app/Http/Controllers/Dashboard/ProductController.php:489–527`, `update()`; `app/Services/LiveVideoItemPieceService.php:10–31`, `syncPieces()` |
    | High | Bulk replacement destroys child IDs and historical weight/per-piece multiplier values. | `app/Services/LiveVideoItemPieceService.php:12–24`; child schema `database/migrations/2026_06_18_130000_create_live_video_item_pieces_table.php:15–19` |
    | High | Dashboard creation and mobile bulk/single-piece mutations lack an encompassing transaction; partial persistence and concurrent add conflicts are possible. | Dashboard `ProductController::store():335–390`; API `LiveVideoItemController::add():54–137`, `update():229–275`, `addPiece():373–393`; service `addPiece():39–55` |
    | High | Migration/controller schema disagreement must be resolved before implementation validation. Cleanup removes columns still written by both controllers. | `database/migrations/2026_06_22_110000_cleanup_legacy_columns.php:9–38,77–100`; Dashboard controller `363–387,505–522`; API controller `86–108,198–211` |
    | Medium | Quantity is not an invariant: normalization drops blank rows, zero-row bulk sync leaves quantity unchanged, last-piece deletion sets zero. | service `26–29,75–91,107–146` |
    | Medium | Dashboard quantity input rebuilds rows from initial values and discards unsaved edits. | `resources/views/dashboard/pages/products/_form.blade.php:248,268–290,313–314` |
    | Medium | Existing mobile documentation describes weight that current requests/service/resources no longer support. | `docs/api/live-video-item-pieces.md:3–33`; `AddPieceRequest::rules():27–34`; `VideoItemPieceResource::toArray():11–19` |

    Scope and limits: source-only review; no application boot, Artisan command, tests, database connection, migration, deployment, or external request. Initial `git status --short` and subsequent porcelain status were empty. No applicable `AGENTS.md` was found in repository file inventory or ancestor inspection. Only this requested deliverable is created; implementation remains untouched. The explicit report request is treated as the sole exception to the otherwise no-file-creation rule. No screenshot attachment was available in this conversation; its stated label is reference context, not evidence of behavior. Auction 461's existence, type, owner, schema, and records were not inspected at runtime.

    ## 2. Existing Feature Behavior

    There is **no persisted enable/disable flag** in the inspected models, feature migrations, controller payloads, or shared form. There is no existing Dashboard toggle corresponding to the requested label. Current behavior is quantity-driven, not mode-driven (`_form.blade.php:115–179,268–290`).

    | Operation | Actual behavior | Evidence |
    |---|---|---|
    | Dashboard create | Defaults quantity to at least one; accepts omitted pieces; always calls bulk sync. | `ProductController::store():291,307–322,385–390` |
    | Dashboard normal form | Always shows pieces table; generates quantity rows; age displays a required marker but is optional server-side. | `_form.blade.php:131–177`; controller `318–322` |
    | Dashboard edit | Replaces entire set, renumbers from one, gives new IDs. Omitted pieces becomes empty set. | controller `525`; service `10–29` |
    | Mobile bulk create | For quantity > 1 with nonempty nested pieces, syncs those rows. Otherwise constructs a single row from legacy top-level arrays. | API controller `113–125` |
    | Mobile bulk update | Nonempty pieces replaces set; otherwise legacy age/gender/identifier/age_type input triggers single-piece replacement; otherwise keeps children. | API controller `264–275` |
    | Mobile individual add | Adds next max number + 1; all four fields optional; recomputes quantity. | service `39–55`; `AddPieceRequest:27–34` |
    | Mobile individual edit | Updates present whitelisted fields only; leaves numbering/quantity untouched. | API controller `412–419`; service `62–69` |
    | Mobile individual delete | Hard deletes one row; keeps remaining numbers; quantity may reach zero. | service `75–91` |

    There is no code-defined toggle OFF contract. In particular, hiding inputs without changing Dashboard update handling would delete existing children; hiding without disabling inputs would continue submitting them (`ProductController::update():525`). This must be explicitly designed, not inferred from a screenshot.

    ## 3. Database Tables, Migrations, and Relationships

    ### Exact source of truth

    The relational source of truth for head details is `live_video_item_pieces`, linked by `live_video_item_id` to `live_video_items.id`; the parent links to `live_videos.id`. No JSON pieces column is involved. Parent `quantity` remains a separate stored value and can disagree with child count. Firebase `create_item()` publishes only parent status (`app/Http/Controllers/FirebaseController.php:45–52`), not the canonical heads collection.

    ### Child schema

    Evidence: `database/migrations/2026_06_18_130000_create_live_video_item_pieces_table.php`, `CreateLiveVideoItemPiecesTable::up():11–23`; gender addition `database/migrations/2026_11_04_000000_add_gender_to_live_video_item_pieces.php:9–14`.

    | Column | Definition / constraints |
    |---|---|
    | id | Auto-increment primary ID |
    | live_video_item_id | Required foreign ID; references parent; cascade on hard delete |
    | piece_number | Required unsigned small integer; no explicit default |
    | age | Nullable string, default Laravel length 255 |
    | gender | Nullable string, added later; historical weight intentionally preserved |
    | weight | Nullable double; retained legacy storage, not written by current piece service |
    | piece_multiplier_number | Nullable string; legacy child column, not written by current piece service |
    | identifier | Nullable string, default length 255 |
    | baham_count | Nullable string, default length 255; not an integer DB column |
    | created_at / updated_at | Laravel timestamps |

    Composite unique constraint `(live_video_item_id, piece_number)` prevents duplicate sequence positions per parent. There is no identifier uniqueness, quantity/count constraint, field completeness constraint, direct owner FK, deleted_at, or mode column in these child migrations. FK/index implementation details depend on the database engine; no live indexes were queried.

    ### Historical migration chain

    Paths below are relative to `database/migrations/`; listed line references identify `up()` schema changes unless noted.

    | Migration | Relevant history |
    |---|---|
    | `New folder/2025_03_20_042602_create_live_video_items_table.php:16–36` | Parent: nullable title, auction/category/winner FKs with cascade deletion, pending/working/finished nullable enum, nullable image/information, double age/weight/quantity/prices/bidding, end_at, timestamps and deleted_at. Quantity has no declared default. |
    | `New folder/2025_05_05_155010_add_new_rows_live_video_items_table.php:16–28` | Arabic title/information; required date_barth; nullable male/female type; nullable color/age/animal-pen FKs with cascade deletion. |
    | `New folder/2025_05_11_161034_add_user_id_live_video_items_table.php:16–19` | Nullable user_id with user deletion cascading to item. |
    | `New folder/2025_08_17_151449_add_rows_to_live_video_items_table.php:16–24` | Nullable age_type, address, certificate, terms/terms_ar, video. |
    | `2026_01_28_142301_add_payment_status_and_status_cart_to_live_video_items_table.php:16–18` | Nullable payment_status and status_cart. |
    | `2026_02_03_231739_change_age_to_string_in_live_video_items_table.php:17` | MySQL ALTER changes age to nullable VARCHAR(255). |
    | `2026_02_12_143752_add_show_partner_name_to_live_video_items_table.php:16–17` | Nullable boolean show_partner_name, default false. |
    | `2026_02_24_213756_add_identifier_and_baham_count_to_live_video_items_table.php:16–19` | Nullable string multiplier, identifier and baham_count on parent. |
    | `2026_03_14_000000_add_winner_video_to_live_video_items_table.php:16–17` | Nullable winner_video. |
    | `2026_04_02_173341_add_payment_proof_to_live_video_items_table.php:16–17` | Nullable payment_proof. |
    | `2026_04_26_100000_add_seller_id_to_live_video_items.php:14–16` | Nullable seller FK; user deletion sets seller_id null. |
    | `2026_06_18_130000_create_live_video_item_pieces_table.php:11–23` | Introduces child table, FK and sequence uniqueness. |
    | `2026_06_19_100000_move_piece_fields_to_live_video_item_pieces.php:12–24` | Drops parent age_id, age, weight, identifier, age_type, baham_count if present. Despite its name, contains **no data backfill**. Down restores only a subset (`27–41`). |
    | `2026_06_22_110000_cleanup_legacy_columns.php:9–38,77–100` | Removes category_id, start_price, payment/status/cart/proof and multiple animal metadata fields still used in controllers. Also drops legacy address table. |
    | `2026_06_22_110000_cleanup_legacy_order_columns.php:9–34,60–69` | Another cleanup with overlapping legacy parent columns; schema presence checks do not reconcile application writes. |
    | `2026_08_06_000000_add_transfer_columns_to_live_video_items_table.php:12–38` | Origin/source self-links, foreign keys, unique target auction + origin; transfer duplication protection is distinct from normal creation. |
    | `2026_11_04_000000_add_gender_to_live_video_item_pieces.php:11–14` | Nullable gender; no conversion of historical weights. |
    | `2026_11_09_010000_add_bid_state_versions_to_live_video_items.php:11–13` | Two unsigned bigint bid state counters, default zero. |

    The `New folder` migrations are **not automatically irrelevant archives**: `App\Providers\AppServiceProvider::boot()` loads migration subdirectories (`app/Providers/AppServiceProvider.php:33–40`). Actual deployed migration history and schema remain unverified. Fresh-schema compatibility cannot be certified from these files: current Dashboard writes removed parent fields even with null values. Do not run or repair these migrations as part of introducing a toggle.

    ## 4. Models and Modules

    | Module | Responsibilities and evidence |
    |---|---|
    | `App\Models\LiveVideoItem` | `app/Models/LiveVideoItem.php:10–113`: guards only id, always eager-loads pieces (`13–14`); category/video/color/user/seller/winner and order relationships; `pieces()` is ordered hasMany (`76–79`); transfer relationships/scoping (`26–29,81–94`); `primaryPiece()` and `resolvedPieces()` use loaded relation or query (`96–112`). No declared casts, accessors, boot hooks or SoftDeletes trait. |
    | `App\Models\LiveVideoItemPiece` | `app/Models/LiveVideoItemPiece.php:8–20`: HasFactory, guards only id, `item()` belongsTo using explicit FK. No casts, ownership scope, hooks or soft deletion. |
    | `App\Models\LiveVideo` | `app/Models/LiveVideo.php:32–58,104–106`: owner relation, parent items relationship, currentVideoItems excludes transferred-away lots. |
    | `LiveVideoItemPieceService` | `app/Services/LiveVideoItemPieceService.php:10–146`: reusable bulk replacement, single CRUD, normalization and count reconciliation. Does not authorize, validate, transact, or lock. |
    | API resources | `app/Http/Resources/User/VideoItemResource.php:72–76` exposes stored quantity and resolved pieces; `VideoItemPieceResource.php:11–19` exposes current per-head shape. |
    | Transfer service | `app/Services/AuctionItemTransferService.php:101–152` replicates parent then copies unique numbered children, checking destination persistence. Copies current fields, not historical child weight/multiplier. |
    | Financial guard | `app/Support/AuctionItemFinancialGuard.php:10–15`: committed when winner, finished price, paid flag, or order line exists. This protects seller identity/deletion in Dashboard, not head metadata. |

    Although the historical parent schema includes deleted_at, `LiveVideoItem` does not use SoftDeletes: model `delete()` is a hard delete and normal item queries do not automatically exclude deleted_at-marked records. Child lifecycle relies on the actual database FK, not a model deletion hook. No feature-specific registered observer/policy was found in provider searches; authorization lives in middleware, requests and controllers.

    `PieceServiceService` is a separate order-item extra-services domain (`app/Services/PieceServiceService.php:1–30`), not the animal-head persistence service. Likewise the Dashboard auctions “pieces” column counts parent lots (`tests/Feature/Dashboard/AuctionsPiecesColumnTest.php:17–23`), not child animals.

    ## 5. Complete Backend Request/Data Flow

    ### Dashboard create target

    1. `RouteServiceProvider::boot()` loads admin routes with web middleware (`app/Providers/RouteServiceProvider.php:46–48`). Locale/admin groups add localeViewPath/dashboardLocale/AuthAdmin (`routes/admin.php:34–37`). Web middleware supplies session/errors/CSRF (`app/Http/Kernel.php:32–39`).
    2. `GET products/create/{id}` maps to `ProductController::create()` (`routes/admin.php:104–106`); 461 is the **auction ID**, not item ID.
    3. `create():251–273` loads auction/owner, enforces partner admin ownership, loads lookups and eligible sellers, renders create wrapper.
    4. `resources/views/dashboard/pages/products/create.blade.php:22–23` opens multipart POST to `admin.products.store` and includes shared form. Resource route supplies POST store and PUT/PATCH update (`routes/admin.php:104`).
    5. `store():284–329` validates auction, checks partner scope, normalizes single-owner user_id and language fallbacks, validates fields and pieces count.
    6. `store():335–388` moves uploaded files and creates pending parent, preserving separate user/seller IDs and parent multiplier.
    7. `store():390–392` bulk syncs children and reloads relation. `syncPieces():12–29` first deletes existing children, filters input, inserts sequential children, possibly updates parent quantity.
    8. `store():395–409` best-effort Firebase status publication; exceptions are swallowed. Redirect is another create page for action add_product, otherwise videos index.

    **No encompassing DB transaction or submission token** exists here. A child insert failure can leave parent and earlier children persisted; a retry creates a new parent. Files moved before persistence can remain after failure. CSRF is not an idempotency mechanism.

    ### Dashboard edit/delete

    `edit():418–432` loads item/pieces and checks parent auction scope. Edit wrapper uses multipart PUT and the same partial (`resources/views/dashboard/pages/products/edit.blade.php:22–23`). `update():443–481` preserves historical parent user_id and validates outside transaction. `489–527` then locks item and auction, rechecks scope/seller constraints, updates parent and replaces children atomically with three transaction attempts. Child SQL failure rolls back those DB changes. File mutations occur later (`538–566`), outside that atomic unit; Firebase runs before those file updates (`529–534`).

    `destroy():578–591` locks item/auction in transaction, checks partner scope and committed financial state, hard deletes item; child deletion cascades if actual FK is installed. Dashboard has no separate child CRUD routes in `routes/admin.php:104–114`.

    ### Mobile

    `routes/api.php:17` applies APISettings; `routes/api/user.php:148` adds auth:api; nested live/items routes are `254–303`. Provider gives `/api` prefix/API middleware (`RouteServiceProvider.php:38–42`), throttle and bindings (`Kernel.php:42–45`), currently 60/minute (`RouteServiceProvider.php:61–64`).

    API bulk add/update first run their FormRequests (authorization then validation), then controller persistence described above. Individual add/update run dedicated FormRequests (authorize returns true), then controller resolves record, checks actual auction ownership, calls service, reloads pieces and returns full item. Delete performs controller checks directly. None of these piece mutations establishes an enclosing DB transaction/parent lock (`LiveVideoItemController.php:373–449`).

    ## 6. Existing Mobile Implementation and API Contract

    This repository contains Laravel mobile-facing APIs; no Dart/Kotlin/Swift client files were found in the file inventory. The actual mobile screen/toggle and client submission behavior cannot be established here.

    | Endpoint | ID meaning | Request / response |
    |---|---|---|
    | POST `/api/live/items/add/{id}` | Auction | Parallel arrays keyed by lot index: lineage_title_ar, quantity, age/gender/identifier/baham_count, image nested by lot; `pieces[lotIndex][headIndex][field]`. Returns MyLiveVideoResource with video_items. |
    | POST `/api/live/items/update/{id}` | Parent item | Scalar parent fields and `pieces[headIndex][field]`; legacy scalar details can replace children with one row. Returns VideoItemResource. |
    | POST `/api/live/items/pieces/add/{id}` | Parent item | Optional nullable strings age/gender/identifier/baham_count, each max 255; returns full parent resource. |
    | POST `/api/live/items/pieces/update/{id}` | Child piece | Same optional fields, updates only present keys; no move/renumber. Returns full parent resource. |
    | POST `/api/live/items/pieces/delete/{id}` | Child piece | No body required; returns full parent resource including revised quantity. |

    Evidence: `routes/api/user.php:277–303`; `StoreItemRequest::rules():35–82`; API controller `36–141,177–281,373–449`; dedicated requests `AddPieceRequest.php:27–34`, `UpdatePieceRequest.php:29–36`.

    Nested row output is `{id, piece_number, age, gender, identifier, baham_count}`. Identifier/baham_count fall back to empty string; age/gender do not. There are no explicit model/resource numeric casts. Do not promise strings versus numbers for quantity/baham_count based on documentation examples (`VideoItemPieceResource:11–19`; `VideoItemResource:72–76`; models above).

    Success envelope is `{success:true,code:200,message,data}` (`app/Traits/ResponseTrait.php:9–11`). Individual controller missing records return HTTP 404; ownership mismatch returns HTTP 403 (`API controller:379–385,402–409,433–440`). Validation returns HTTP 422 with first message plus errors map (dedicated requests `50–59` / `52–61`). APISettings checks x-api-key and ar/en Accept-Language (`app/Http/Middleware/APISettings.php:34–60`); its error JSON embeds code 403 without setting the HTTP status. Existing API docs are not authoritative for weight/type examples (`docs/api/live-video-item-pieces.md:3–33,94–101`).

    Retrieval is through auction detail `GET /api/live/video/{id}` (`routes/api/user.php:268`; `LiveVideoController::SingleVideo():550`) and full parent responses. `SingleLiveVideoResource:71` and `MyLiveVideoResource:69` serialize currentVideoItems via VideoItemResource. There is no standalone pieces-list route in the inspected live/items group.

    ## 7. Dashboard Creation/Edit Architecture

    The reusable entry point is `resources/views/dashboard/pages/products/_form.blade.php`, included by create/edit (both wrappers `22–23`). Another consumer is `resources/views/dashboard/pages/orders/create.blade.php:22–23`, even though orders resource excludes create/store (`routes/admin.php:96`); assess this consumer before changing required partial variables.

    The shared partial restores old pieces, otherwise loaded model pieces (`7–19`), renders quantity and parent multiplier (`115–128`), an always-visible responsive Bootstrap card/table (`131–162`) and a template with age select, free-text gender/identifier, numeric baham_count default zero (`164–179`). There is no add/delete row button or toggle. Changing quantity implicitly adds/removes trailing rows (`268–290,313–314`). Row fields are named `pieces[i][field]`; child IDs are never submitted.

    JavaScript is plain DOM code. Existing jQuery/select2 handling concerns the seller field (`250–265`), not the heads table. Apply-to-all copies all four fields, including identifier, from the first row (`294–309`); this can intentionally produce duplicate identifiers because no identifier uniqueness is enforced. Table uses existing `card`, `table-responsive`, `table-bordered`, `form-control` and Bootstrap spacing classes; no feature-specific CSS is required for current layout (`131–177`). Existing translations are `resources/lang/ar/app.php:1359–1361` and `resources/lang/en/app.php:1348–1350`.

    **Data loss risk:** renderPieceRows clears the DOM and reads only immutable initial existingPieces (`248,270,280`); changing quantity after editing heads loses unsaved edits. Free-text historical ages not present in current active Age options can also fail to select during hydration (`168–172,285–286`). Restoring validation input helps only for represented values; there is no per-row rendering of gender/identifier/baham errors, and the wildcard age @error is not an iteration over indexed errors (`156–161`). With JavaScript disabled, the table body remains empty (`151,268–290`).

    ## 8. Validation and Persistence Rules

    | Rule | Dashboard | Mobile |
    |---|---|---|
    | Piece shape | Optional array, raw size equals effective quantity; no explicit pieces.* array rule | Nested array rules in add; row array rule in update |
    | Age | Nullable string max 255 despite visible asterisk | Bulk create nullable string; bulk update requires each row age only if submitted quantity >1; legacy scalar update age integer; individual CRUD nullable string |
    | Gender | Nullable free string max 255 | Same; not restricted to male/female |
    | Identifier | Nullable string max 100 | Nullable string max 255 |
    | Baham count | Nullable integer min 0 | Nullable string max 255; negative/non-numeric strings not prohibited |
    | Quantity | Optional integer min 1; controller effective quantity at least 1 | Add quantity.* numeric (not integer/min); update quantity nullable with quantity.* numeric, not a scalar integer rule |
    | Count | Supplied pieces raw count must equal quantity | Create requires raw count for quantity >1; update count check only uses submitted quantity >1 |
    | Bounds | No explicit row maximum | No explicit row maximum |

    Evidence: Dashboard controller `291,300–329,452,461–481`; `StoreItemRequest.php:44–74,89–104`; `updateItemRequest.php:48–68,72–102`; dedicated requests above. Database sequence uses unsignedSmallInteger, so backend cardinality is ultimately bounded by engine storage even though no application maximum is specified (child migration `14`).

    Normalization keeps a row when any age/gender/identifier/baham_count is filled; zero counts as filled. It discards non-arrays and entirely blank rows; ignores weight, age_type and per-piece multiplier (`LiveVideoItemPieceService::normalizePieces()/pieceIsEmpty():107–146`). Raw validation count therefore does not guarantee persisted count. Bulk normalization runs **after deletion** (`12–14`). A nonzero normalized count overwrites quantity; zero leaves quantity unchanged (`26–29`). Individual add bypasses normalization and can create an entirely null-detail row (`43–49`); deletion synchronizes quantity even to zero (`75–91`).

    Consequences derived from those methods:

    - Dashboard quantity 3 with three blank rows passes optional-field checks but can persist zero children and quantity 3; its usual baham_count default 0 instead makes rows nonempty.
    - Three raw rows with only two nonempty persist two and quantity becomes 2, despite original size validation against 3.
    - Dashboard omitted pieces deletes children on update. Explicit null can pass nullable validation yet reach an array-typed service argument, raising a type error; Dashboard update rolls back, create has already persisted the parent (`390,525`; service `10`).
    - Mobile update pieces without quantity bypasses count/required-age checks; nonempty normalized rows determine quantity. Explicit empty pieces does not clear existing children unless another legacy trigger causes replacement (`updateItemRequest:78–101`; API controller `264–275`).
    - A baham_count-only scalar update does not trigger single-piece sync because hasAny excludes it. An age_type-only request can trigger replacement even though age_type is ignored by buildSinglePiecePayload (`API controller:266–274`; service `94–104`).

    Validation failures occur before controller DB writes. Dashboard update has rollback for parent/child SQL failures inside its transaction; creation/mobile do not. No feature-specific idempotency key, parent creation uniqueness, optimistic version for pieces, or duplicate-submit guard was found in these workflows. The unique child sequence constrains positions, not repeated content or parent creation.

    ## 9. Authorization and Ownership

    Dashboard authentication uses the admin guard (`AuthAdmin::handle():23–42`, `app/Http/Middleware/AuthAdmin.php`). Partner admins are scoped by `live_videos.admin_id`, not item user_id or live_videos.partner_id (`PartnerDashboardScope::ensureOwnLiveVideo():79–83`). Non-partner admins pass that scope helper; create/edit permission calls are commented out (`ProductController:254,420,445`). AuthAdmin contains a permission check for translations, not product creation (`31–35`). Do not equate authentication with explicit create/edit permission enforcement.

    Dashboard store normalizes user_id to auction owner only for partners_type single (`287–289`); multiple-owner user_id validation checks existence only (`301`). Edit preserves historical user_id (`448–450`). Seller selection uses `AuctionSellerEligibility`, separate from organizer authorization (`303,464–468,497–502`). Committed seller changes/deletions are protected but pieces can still change (`497–525,587–590`).

    Mobile bulk FormRequests authorize against `LiveVideo.user_id = auth(api).id` (`StoreItemRequest:19–23`; `updateItemRequest:20–25`). Individual requests authorize true, but controller `userOwnsItemsAuction():364–370` enforces that same organizer identity before service writes. Being the lot seller, historical item owner, or another organizer is insufficient (`384–385,408–409,439–440`). These guards intentionally differ from Dashboard admin identity and should stay in their adapters.

    Parent deletion via API checks auction ownership, pending status, absence of winner/finished price/orderItem, then directly hard deletes and best-effort removes Firebase state (`LiveVideoItemController::delete():287–312`). It does not use the Dashboard transaction/lock wrapper. Piece-specific methods do not call the financial guard or reject working/finished/ordered state (`373–449`). Broader start/end/award routes operate on parents, not individual child animals (`routes/api/user.php:283–286`).

    ## 10. Existing Tests and Coverage Gaps

    Static inspection only: no tests were executed and no pass rate or measured coverage is claimed.

    | Existing source | Coverage observed |
    |---|---|
    | `tests/Feature/Api/Live/LiveVideoItemPiecesTest.php:73–187` | Add persistence/count, next numbering, unauthenticated add, missing item, foreign organizer, invalid gender. |
    | Same file `189–272` | Partial update, quantity stability, missing piece, foreign organizer, invalid gender. |
    | Same file `274–330` | Single delete/count, final deletion quantity zero, missing piece and foreign organizer. |
    | `tests/Unit/PieceGenderTest.php:20–30,33–84` | Gender rules and SQLite migration/service behavior; preserves historical weight during gender migration and individual updates. Does not cover weight during bulk replacement. |
    | `tests/Feature/Api/AuctionCreationValidationTest.php:182–242,257–348` | Required images before writes, optional/invalid genders across creation shapes, real creation endpoint validation/persistence. |
    | `tests/Feature/Bugfixes/FinancialItemSafetyTest.php:35–83,85–165` | Dashboard updates with one piece across financial states; ownership/seller consistency and protected deletion. |
    | `tests/Unit/AuctionItemTransferPersistenceTest.php:71,95,142–178`; `tests/Feature/Api/Live/MobileExistingAuctionTransferTest.php:53–83`; `tests/Feature/Services/AuctionItemTransferServiceTest.php:115–119` | Child copying and parent transfer persistence/rollback behavior. |
    | `tests/Feature/Dashboard/AuctionsPiecesColumnTest.php:17–23` | Parent lots column/navigation; not individual-head form coverage. |

    Only `database/factories/UserFactory.php` was found in the factory directory; the piece API suite builds parent/child fixtures inline (`LiveVideoItemPiecesTest:40–68`). That suite uses DatabaseTransactions (`28`), not a self-contained schema. `phpunit.xml:24–25` leaves SQLite connection/database settings commented out; a future execution requires explicitly isolated DB configuration. This audit did not read .env or connect to its configured database.

    Missing targeted tests, based on inspected suites and searches:

    1. Dashboard toggle OFF create with legacy quantity-only and common-details scenarios; assert agreed parent/child result and unchanged ownership.
    2. Toggle ON with several pieces, persisted order/gender/count, count mismatch and per-row error retention.
    3. Browser add/remove/edit, quantity resizing retaining unsaved values, apply-all behavior and old input reconstruction.
    4. Edit unrelated parent field while preserving existing children, and explicit mode changes with the agreed retention/deletion policy.
    5. Empty/all-blank/mixed rows, zero baham_count, nullable payloads, invalid row shape and oversized row counts.
    6. SQL failure on second child during create/update, checking complete rollback; uploaded-file failure behavior separately.
    7. Concurrent single adds, concurrent replace versus CRUD, duplicate parent submissions and stale forms/child IDs.
    8. Actual migrated-schema FK/cascade/unique behavior; historical weight/multiplier preservation during replacement and transfer.
    9. Dashboard own/foreign partner and ordinary admin create/edit permission cases; unauthenticated update/delete piece routes and malformed-input authorization ordering.
    10. API legacy scalar versus nested contracts, quantity omitted versus included, empty pieces and null clearing; regressions against the Dashboard extension.
    11. Head edits after bidding/award/order, invoice/transfer serialization, and last-piece zero quantity displayed in Dashboard's minimum-one form.

    ## 11. Regression Risks

    | Risk | Practical consequence | Evidence / boundary |
    |---|---|---|
    | OFF implemented as hidden/disabled rows | Disabled/omitted edit fields currently clear all children; hidden active fields still save. | Dashboard update `525` |
    | Infer mode only from piece count | One child can be legacy singleton or an explicit individual head; zero can mean no details or last deletion. | API add `113–125`; service `75–104` |
    | Global validator consolidation | Enforcing Dashboard integer baham_count/max100 identifier on mobile breaks existing string/max255 contracts. | Section 8 request definitions |
    | Global service semantic change | Changing zero-row count or omission behavior affects mobile, Dashboard and historical data. | service `10–31`; callers above |
    | Destructive bulk replacement | New IDs invalidate stale mobile piece references; legacy weight/multiplier lost even on parent edit. | service `12–24`; resource `12` |
    | Concurrent mutation | max+1 can collide with unique index; count can race with another add/delete; Dashboard row locks do not protect against callers not taking those locks. | service `41–53,87`; Dashboard update `490–491` |
    | Partial batch/create | Parent and first children survive later failure; retries duplicate lots. | API add loop `54–137`; Dashboard store `363–390` |
    | Schema drift | Unknown-column failures may occur before any toggle-related logic. | cleanup migrations versus controller write sets, Section 3 |
    | Financial history metadata | Current guards protect money/seller identity but do not freeze pieces; order summaries read current children. | `AuctionItemFinancialGuard:10–15`; `OrderService:181,250–258,381–389` |
    | Auction domain confusion | Creating separate auction lots per head would change bidding/awards. Current awards and order lines target parent live_video_item_id. | `AuctionAwardService::award():12–53`; `OrderService::attachWonItem():31–74` |
    | Transfer compatibility | Child fields copied via explicit whitelist; adding a new field/mode needs deliberate propagation decision. | `AuctionItemTransferService:136–146` |
    | Vocabulary confusion | Order extra-services and auctions “pieces” column refer to different entities. | `PieceServiceService:1–30`; `AuctionsPiecesColumnTest:17–23` |

    No financial or auction business logic is changed by this audit. Existing bidding/award/order behavior must remain parent-lot based unless separately specified; there is no code evidence that enabling individual detail rows creates independently biddable heads.

    ## 12. Exact Files Likely to Require Changes

    This is a proposed change set, not work performed.

    | Path | Minimal likely responsibility |
    |---|---|
    | `resources/views/dashboard/pages/products/_form.blade.php:7–19,115–179,240–315` | Toggle and mode restoration; row visibility/submission rules; preserve live edits; explicit row controls if required; indexed errors. |
    | `app/Http/Controllers/Dashboard/ProductController.php:282–390,443–527` | Dashboard mode validation/adaptation, preserve omitted children on edit as agreed, atomic parent/child creation; retain ownership and financial guards. |
    | `app/Services/LiveVideoItemPieceService.php:10–31,107–146` | Reuse existing persistence. Change only if approved identity/history/count requirements cannot be met through the adapter; all callers then require regression review. |
    | `resources/lang/ar/app.php:1359–1361`; `resources/lang/en/app.php:1348–1350` | New mode labels/help/errors. |
    | `tests/Feature/Bugfixes/FinancialItemSafetyTest.php:35–83` and new focused Dashboard feature/browser tests | Extend parent-edit compatibility and mode-specific coverage. New test names/paths are an implementation proposal. |
    | `tests/Feature/Api/Live/LiveVideoItemPiecesTest.php`; `tests/Unit/PieceGenderTest.php` | Only if shared-service behavior changes: add normalization/concurrency/history cases, retain mobile contracts. |
    | `docs/api/live-video-item-pieces.md:3–33,94–101` | Correct stale weight contract when documentation maintenance is authorized. |

    Conditional only: a new migration/model field if a durable mode is explicitly required. Existing create/edit wrappers, routes and child models should need no change for a presentation-mode extension. No new Dashboard AJAX child endpoint is needed for submit-on-save rows. Do not alter `AuctionAwardService`, `AuctionBidService`, `OrderService`, wallet/settlement code or transfer semantics simply to add a toggle. Schema cleanup incompatibility needs a separately scoped resolution before rollout, not an unreviewed migration in this feature.

    ## 13. Recommended Minimal Implementation Approach

    1. Resolve OFF semantics, mode persistence and edit behavior first (Section 14). Treat these as product decisions, not already-established rules.
    2. Reuse the existing shared partial/table and `LiveVideoItemPieceService`; keep Dashboard and API guards/request shapes in their existing adapters. Do not call mobile controllers or create a second head-persistence implementation.
    3. Prefer an explicit Dashboard request mode so the server can distinguish OFF, ON, and legacy omitted-mode requests. Preserve backwards behavior for clients that do not submit the new field. Avoid a schema migration unless durable mode cannot be inferred safely under the agreed semantics.
    4. ON should submit normalized head rows with server-enforced agreed count/completeness rules. OFF should submit only the agreed common details/quantity contract. Preserve existing children on unrelated edits; destructive clearing must be an explicit agreed action, not a side effect of disabled inputs.
    5. Wrap Dashboard parent creation and child persistence in one DB transaction. Keep current update lock order/financial guards intact. File lifecycle needs separate handling because DB rollback cannot undo file moves. Assess child-ID/history retention before reusing destructive sync on existing records.
    6. Capture current row values before redraw, retain them when quantity grows/shrinks as agreed, restore validation state, and bind actual indexed errors. Existing Bootstrap styling is sufficient as a starting point.
    7. Validate with focused isolated tests for the approved modes and existing mobile/update/financial compatibility. Confirm schema through an approved non-production process before any later execution; do not infer deployed state from migration names.

    The steps above are recommendations only. No code, tests, migrations or external state were changed.

    ## 14. Unresolved Business Questions

    1. Does OFF mean quantity-only/no children, a shared description representing several animals, or one detail row? Must quantity remain independently configurable?
    2. On an existing item, does switching OFF retain children, archive details, or explicitly delete them? Is reversal expected to restore unsaved/saved details?
    3. Must the mode be persisted? A singleton/empty collection cannot reliably distinguish historical creation paths.
    4. Is age mandatory per head? Dashboard marks it required but validates nullable; mobile paths differ. Should gender remain arbitrary text?
    5. Is baham_count a nonnegative number or an arbitrary string? Which identifier maximum is intended? Are repeated identifiers allowed, particularly with apply-all?
    6. Are rows count-driven or independently addable/removable? What maximum and what policy for deleting the last piece apply?
    7. Must child IDs and historical weight/per-head multiplier survive Dashboard edits? Should retired fields remain visible/read-only or preserved invisibly?
    8. Can heads be changed after bids, award, payment or settlement? Existing code permits metadata changes; that is observed behavior, not a policy endorsement.
    9. What should happen to stale concurrent edits, duplicate submits and requests referencing replaced child IDs?
    10. Which deployed schema/migration history is authoritative for the parent columns removed by cleanup yet still used by controllers?
    11. Is ordinary admin create/edit authorization intentionally broader than deletion, or should explicit product permissions be required?
    12. Does the screenshot imply independent pricing/bidding per head? Current data flow supports parent-lot auctioning only; any independent-head auction behavior would be a separate feature.

    ## 15. Implementation Readiness Decision

    **Discovery complete; implementation requires decisions and compatibility checks.** A reusable table, relationships, resource contract and shared persistence service exist. The minimum safe feature is a Dashboard mode adapter over them, with explicit edit semantics and row-state preservation, rather than new storage/endpoints or duplicated business logic.

    Do not approve a cosmetic hide/show change alone: current omission deletes children, replacement loses IDs/legacy fields, and migration history disagrees with active parent writes. Resolve the OFF/edit/count policy and schema compatibility first; then implement and validate the narrowly scoped Dashboard extension. Production data health, actual schema and test execution remain outside this read-only audit.
