# Live viewer count: audit, repair, and verification

Status: **VERIFIED** for explicit per-user join/leave, independent-worker database concurrency and synchronous publication ordering. **BLOCKED** for real device disconnects, overlapping-device accuracy, and ambiguous Firebase delivery. No application databases or live Firebase were accessed.

## 2026-10-24 reliability continuation

The user explicitly confirmed unique-user counting. Re-audit found no durable connection identity, event sequence, heartbeat/expiry, or publication recovery record. No runtime behavior was changed in this continuation: adding the necessary persistent state requires the separately approved migration/data-contract change requested by the user.

Two additional characterization tests strengthen the evidence without weakening existing assertions:

- A full day of elapsed server time followed by another user's join retains the original user's presence. No request-time expiry exists. This is not a real mobile disconnect test.
- An independent backend worker is actually terminated after publishing to the local sink, before committing SQL. Its transaction rolls back, leaving sink count 1 with no presence/history rows. A later explicit leave repairs the sink to 0. This proves the dual-write crash gap with real process termination, not actual Firebase or mobile termination behavior.

Latest run: **44 tests / 593 assertions**, all passing: presence **7/88**, concurrency **9/112**, existing service-price **24/301**, existing auction validation selection **4/92**. Fresh disposable MariaDB/InnoDB for concurrent workers; SQLite in-memory for the other selected tests. Syntax checks and `git diff --check` pass. The test database container was removed. The previous 42/572 results below remain historical evidence.

Concrete schema/API/lease/publication design, legacy-client limitations, Flutter/iOS changes and approval scope: [LIVE_VIEWER_RELIABILITY_PROPOSAL.md](LIVE_VIEWER_RELIABILITY_PROPOSAL.md). This is a proposal, not an implemented fix. No migration files, scheduler tasks or Firebase metadata have been added.

## Source of truth and counting semantics

| Data | Current source/path | Meaning |
| --- | --- | --- |
| Active presence | `live_video_users`, `leave=0`, distinct `user_id` | One active flag per authenticated user per auction |
| UI publication | `FirebaseController::LiveCount`, `live/{id}/view` | Absolute projection of active users; not independent presence |
| Historical analytics | `video_views`, key `video_id + user_id` | Historical unique users; never removed on leave |
| API `views_count` / `myVideoViewList` | `LiveVideo::all_views` / `VideoView` | History, not active presence |
| Agora | `AgoraService` | Stream token/channel creation; no membership reconciliation found |

Repository evidence: live add/leave routes in `routes/api/user.php`; presence relations in `app/Models/LiveVideo.php`; presence fields in the existing June/July 2024 migrations; historical `updateOrCreate` in `VideoController`. The available backend contract is user-based, without session identity. This does not establish the intended behavior when one of two devices leaves. No session-count policy was introduced.

No Dart/Swift project, Firebase rules/emulator setup, client listener, connection heartbeat, disconnect callback, or presence cleanup job was found. `app/Console/Kernel.php` schedules auction lifecycle jobs, not per-user presence expiry. The released UI's actual source and lifecycle cannot be confirmed from this repository. No count-response cache participates in the traced writes.

## Confirmed defects and minimal fixes

1. Original live join/leave routes targeted missing controller methods. Original-controller `callAction` reproduced `BadMethodCallException` for both. The previous repair restored these methods using existing presence flags.
2. The historical video endpoint published accumulated views into Firebase's active count. The previous repair removed that conflicting publication; historical recording remains intact.
3. **Newly reproduced:** a historical `video/add-view` call and live `live/add-view` call could both read an absent history row and insert it. Independent workers created **two rows for the same user**, making history overcount. `VideoController::addView` now takes the same auction row lock as live presence before recording history. The same test now retains exactly one row. No existing analytics rows were edited or deduplicated.

No new runtime files, roles, auction permissions, financial rules, bidding/award/order/transfer code, migrations, or persistent infrastructure changes. The previous implementation pass changed only the historical endpoint's transaction/lock; the earlier live repair remains. The lock is held briefly by historical recording; live presence still holds the existing repair's lock through publication, which can delay other operations needing that auction row during slow Firebase calls. No load/latency claim is made.

## Exact current behavior and remaining bugs

- Join/rejoin sets the user's flag active, records history once, and republishes the absolute distinct active count. Leave marks that user's rows inactive. Repeated/unknown leaves cannot subtract below zero. Duplicate legacy presence rows count once and are all marked inactive on leave.
- Different users count separately. Same-account devices count as one. Leaving either device marks the shared account flag inactive, even if the other still watches. A delayed old leave after a new join does the same. Both are reproduced; the API has no device/connection/generation field to distinguish them.
- No HTTP leave signal means no cleanup. Network loss, force kill, OS termination and power loss cannot be detected from these rows. The no-signal test verifies retained state, not a real device disconnect.
- Independent workers prove a second handler waits on the auction row while the first is paused in synchronous publication. Successful synchronous writes cannot overtake each other through these handlers.
- **Remaining delivery gap:** Firebase publication exceptions are logged but API success and database presence remain. There is no automatic reconciliation. SDK inspection confirms an unconditional HTTP PATCH, without count version/precondition. A local sink test models an old remote write completing after its caller times out: leave publishes 0, delayed old join publishes 1, while the database has 0 active users. A later repeated leave republishes 0. This is a demonstrated architectural counterexample, not proof of actual Firebase transport timing.
- Database commit and Firebase write are not atomic. Process failure between publication and commit also remains outside the guarantee. No distributed atomicity or delivery reliability is claimed.

## Flutter/iOS integration contract

Paths below are relative to the existing API base (`/api` in this repository). Keep current bearer authentication, API key and language headers and existing access controls.

| Trigger | Required client behavior |
| --- | --- |
| Successful viewing/channel entry | `POST /live/add-view/{auctionId}`, body `{}`. Inspect the existing `success` envelope as well as transport errors. `/video/add-view` records history only and is not a live join. |
| Back/close/switch auction | Route every intentional exit through one awaited handler: send `POST /live/leave-view/{auctionId}`, body `{}`, then perform existing Agora leave/disposal and listener teardown. Preserve existing auction-end handling. |
| Reconnect/foreground | Determine actual channel/screen membership. If viewing resumed, repeat the live join. Reattach one count listener and obtain its current value. Foreground alone is not proof the channel is joined. |
| Connection lost | Show reconnecting/stale state. A leave request can only be best effort while networking is available. Do not claim cleanup succeeded merely because a lifecycle callback ran. |
| Count refresh | Subscribe to Firebase RTDB `live/{auctionId}/view` value updates. Assign the received value, including zero; do not locally add/subtract or use API `views_count` as active. A missing auction node is not an authoritative zero while disconnected/ending; follow existing auction-status handling. |
| Switching/disposal | Cancel the old Firebase subscription and ignore callbacks belonging to an older auction/screen generation. Do not rely on Flutter `dispose` or Swift `deinit` to await HTTP requests. |

Flutter: put the shared awaited exit handler in the navigation/back/close flow; use the existing lifecycle observer to resume/reconcile channel state. iOS: use explicit view/channel entry and exit callbacks, and scene activation/reconnection to reconcile. Scene backgrounding, Flutter inactive/paused, and Agora remote-user callbacks do not independently define whether this product considers a viewer departed; preserve the established screen/channel policy.

Serialize presence calls per auction/account within each client. Cancel queued stale retries when viewing intent changes; an already-sent request cannot be revoked. After an uncertain old call completes, reassert the latest membership if possible. This reduces one-device reordering but cannot solve requests still executing after a timeout or coordinate independent devices. Do not directly write/decrement `view`, attach `onDisconnect` to that scalar, or infer all audience departures from Agora remote broadcaster callbacks.

Exact mobile file edits require the missing client project. Required device QA (not executed): two accounts; two devices sharing one account; back/close/switch; duplicate callbacks; airplane mode; Wi-Fi/mobile handover; force kill; OS background termination; rapid reconnect and delayed responses. Compare screen intent, API result, active flags and published count, while checking history stays unchanged.

## Necessary follow-up proposals — not implemented

The existing user flag cannot distinguish a dead connection from a healthy silent connection, or one device from another. Cleanup cannot be safely inferred from `updated_at`; ordinary viewing currently sends no refresh.

A compatible future design would keep **distinct-user counting**, add a per-connection identity/generation, and retain a user while any verified connection remains active. Use the existing Firebase presence mechanism only if client/rule inspection proves one exists; otherwise connection-presence integration or a renewable lease/heartbeat plus approved expiry processing is necessary. Specify background behavior, expiry delay, reconnect grace, legacy-client treatment and clock authority before implementation. Legacy clients without heartbeats must not be silently expired. This requires separate approval for schema/rules/infrastructure changes and cannot be solved by a migration-free scalar decrement.

Reliable projection after crashes/timeouts separately needs durable/versioned delivery or reconciliation and conditional stale-write rejection. A retry repairs only after older writes finish; it does not guarantee reliability. These are proposals, not changes to business policy.

## Test evidence and reproduction

All fixtures use minimal isolated schemas, not application migrations. New workers bootstrap only an in-process Illuminate container, never the application kernel or `.env`; Firebase is mocked. The concurrency suite requires explicit `MAZADAT_VIEWER_TEST_CONFIG`, refuses any database name other than `mazadat_viewer_test`, requires loopback and validates the expected disposable server hostname before fixture writes.

- `LiveViewerPresenceTest`: **6 tests / 78 assertions**, still passing.
- `LiveViewerConcurrencyTest`: **8 tests / 101 assertions**, independent PHP processes against disposable MariaDB/InnoDB. Five overlapping-call cases cover different-user joins, duplicate joins, repeated leave, leave/join and leave/reconnect. Other cases cover historical duplication, late old same-account leave, and simulated ambiguous timeout/delayed publication plus repair retry. Tests verify actual row-lock waiting through InnoDB diagnostics, not merely sleep timing.
- Existing `DashboardServicePriceTest`: **24 tests / 301 assertions**, including existing ownership and order-edit restrictions.
- Existing `AuctionCreationValidationTest` focused selection: **4 tests / 92 assertions**, schedule, partial update, commission envelope and other account types; SQLite in-memory. Full bid/award/payment/transfer suites were not run against their application fixtures.
- Total: **42 tests / 572 assertions**. PHP 8.4.26; new PHP syntax checks and `git diff --check` pass. No real Firebase/emulator, mobile device or production-engine deployment verification.

To rerun concurrency, start a new disposable MariaDB container with a random loopback port and `mazadat_viewer_test`; do not reuse an application server. Set the config JSON fields `host` (`127.0.0.1`), `port`, `database`, `username`, disposable `password`, and `expected_hostname` (the new container's actual hostname). With PHP 8.4+, pdo_mysql, mbstring and openssl enabled, set `MAZADAT_VIEWER_TEST_CONFIG` to that file and run `php vendor/phpunit/phpunit/phpunit tests/Unit/LiveViewerConcurrencyTest.php --do-not-cache-result`. The suite drops/recreates only its three fixture tables after identity checks. The disposable container used for this run was removed afterward.

Changed files in this continuation: `app/Http/Controllers/api/Video/VideoController.php`, `tests/Support/live_viewer_fixture.php`, `tests/Support/live_viewer_worker.php`, `tests/Unit/LiveViewerConcurrencyTest.php`, and this report. Earlier uncommitted live controller/presence test changes are preserved.
