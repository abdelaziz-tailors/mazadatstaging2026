# Presence reliability: proposed change requiring approval

2026-10-24. **Proposal only. No migration, schema, scheduler, Firebase metadata or runtime change has been introduced in this continuation.** Existing uncommitted repairs are preserved. Unique users remain the business definition: a user counts once while any of their viewing connections is active.

## Why the current state cannot supply the missing guarantees

The backend has one effective `(auction,user,leave)` flag. Requests contain no connection identity or event order. A reconnect followed by an old leave and two devices with one leaving are indistinguishable from a genuine last leave. There is no heartbeat, expiry job or connection-presence listener in the repository. Time alone cannot distinguish a silent viewer from a disconnected one. `updated_at` cannot safely expire existing clients because they do not renew it.

`FirebaseController::LiveCount` performs an unconditional scalar-count publication inside the database transaction. The systems do not commit atomically. A timed-out remote write can arrive late (local sink counterexample); an actual backend worker killed after sink publication rolls SQL back while the sink retains its count. A repeated call repairs these examples but is not durable reconciliation.

The existing cache configuration lists file/Redis/database alternatives but proves neither an active shared durable store nor suitable retention. Moving authoritative presence into an evictable/local cache would create a new reliability assumption. Reusing `leave`, `deleted_at` or timestamps to encode undocumented connection metadata would break the existing model. Creating connection nodes in Firebase would still require a new data contract, rules review, client integration and SQL reconciliation; the shipped rules/client source are absent.

## Recommended minimal durable design — approve before implementation

Use the existing SQL database, Firebase RTDB and scheduler. No new server, queue service, roles or financial behavior. Two proposed additive tables, not migration files yet:

| Proposed table | Fields and constraints | Purpose |
| --- | --- | --- |
| `live_viewer_connections` | auction ID, authenticated user ID, connection UUID, last event sequence, server expiry, terminal leave timestamp, created/updated timestamps; unique `(auction,user,connection)` and expiry index | Distinguish connections while counting distinct users. Reserved `legacy` connection has no expiry. |
| `live_viewer_publications` | auction ID primary key, monotonic revision, desired active count, acknowledged revision, timestamps | Durable latest-state delivery record, committed with presence; restart/retry recovery without a separate queue service. |

Backfill only a reserved legacy connection for each existing active auction/user pair. Preserve every historical `video_views` row and existing presence records. Keep `live_video_users.leave` as the existing aggregate projection: active iff that user has an unexpired modern connection or an active legacy connection. Recompute it and the distinct-user count under the existing auction lock. Historical recording stays on the existing unique-user join path. Never delete accumulated view analytics.

The publication row and presence mutation commit in one SQL transaction. Publish **after** commit, outside the auction lock, so remote latency does not hold the auction row. The existing scheduler retries pending publication and expires modern connections in bounded batches; reopening a crashed worker must not lose pending work. Acknowledgement advances only to the revision actually confirmed remotely, never over newer pending work.

Firebase keeps `live/{id}/view` numeric for compatibility. Add a private publication revision marker to that existing auction node. Use an ETag-conditional transaction to update the count and revision together; skip older revisions and re-read on conflicts. Do not retry a stale unconditional PATCH. Root-level compare-and-set must preserve all current status/item/bid children and respect the existing auction node lifecycle; it must not recreate deleted auction nodes. This requires root reads and contention testing, since the legacy scalar and its revision need one atomic update. Do not deploy this approach without measuring that cost. A versioned count subtree is an alternative requiring an explicit client-listener change and offering no strict ordering guarantee for the old scalar until all readers migrate.

This proposal requires approval for the two tables/backfill, the scheduler task, and Firebase revision metadata/conditional publication. Approval to implement is not approval to run migrations or access any live service; those remain separate.

## Proposed compatible API extension

Retain existing add/leave URLs, authentication, API key, permissions and legacy response behavior when the new optional fields are absent. Modern clients supply a fresh connection UUID for each viewing incarnation and a monotonically increasing event sequence. The server derives user identity from authentication, never from request user IDs.

- Join: `{connection_id: UUID, sequence: 1}` on successful channel entry. Duplicate identical requests are idempotent and must not extend a lease. Leave received before a delayed initial join creates a terminal tombstone so that join cannot resurrect it.
- Heartbeat: an explicitly added authenticated live heartbeat endpoint, scoped to auction/user/connection. Only a newer sequence on a still-live connection renews its server-timed lease. Expired/closed connections cannot be reopened; reconnect uses a new UUID. Reject malformed or cross-account identifiers through existing authorization behavior.
- Leave: same existing URL plus connection ID and sequence. It closes only that connection, never another device's connection. A delayed old leave cannot close a new UUID. Closed tombstones must remain long enough to reject replay; retention must be defined before cleanup is enabled.
- Reconcile: an authenticated read for the caller's connection state plus committed aggregate count and publication revision. An API mutation response must distinguish committed membership from pending publication without changing legacy response types; modern clients can use an explicitly versioned response contract.

**Lease timing for approval:** proposed heartbeat every 20 seconds, lease 90 seconds, expiry scan every minute. These are proposed operational values, not existing business rules. Cleanup is bounded by lease expiry plus scan/delivery delay, never instantaneous disconnect detection. Background behavior must follow the product's existing definition of active viewing. Do not silently expire a client that legitimately continues viewing in the background.

## Legacy-client boundary that cannot be hidden

Requests without connection identity remain a reserved legacy slot. A legacy leave clears only that slot, so it cannot remove modern connections. Legacy callers still cannot distinguish their own multiple devices and cannot renew a lease. They retain their previous explicit-leave semantics and may remain stale after termination.

Complete disconnect reliability for all viewers therefore requires a coordinated modern-client rollout. Expiring legacy slots or requiring a client upgrade would change legacy behavior and needs explicit approval. Neither is included implicitly in this proposal.

## Required Flutter/iOS changes after backend approval

No mobile source is available, so these are integration requirements, not claimed source edits.

1. Create a UUID per successful viewing incarnation. Queue ordered presence events with per-connection sequences; reuse the same ID/sequence on transport retry. Allocate a fresh UUID on reconnect/new entry; cancel old queued events. HTTP cancellation cannot recall requests already executing remotely.
2. Start heartbeats only while confirmed joined/viewing. On back/close/switch, await a scoped leave through a shared exit handler, then run existing Agora/navigation cleanup. Stop that connection's heartbeats. Do not count devices; display the server's distinct-user aggregate.
3. Flutter: use the explicit navigation/back handler and existing lifecycle observer. iOS: use explicit viewing/channel callbacks and scene activation/reconnect. `dispose`, `deinit` and termination callbacks cannot guarantee a network leave. Missed leaves expire server-side under the approved lease.
4. On foreground/reconnect, reconcile actual channel membership and server connection state. If the lease ended, start a new connection; never replay an old leave against it. Renew only while viewing. Treat publication-pending separately from membership failure.
5. Keep one Firebase value listener for `live/{id}/view`, including zero; ignore callbacks from old screen generations. On reconnect, fetch committed server state and then follow listener updates. Do not display historical `views_count` as active, locally decrement, or attach `onDisconnect` to the shared scalar. Absence/offline cache data is not proof of zero.
6. Actual device QA must cover two devices sharing an account, rapid reconnect, delayed join/leave, airplane mode, OS termination, background viewing, Firebase listener reconnection and offline cached values. Do not infer audience presence from Agora broadcaster callbacks without checking SDK roles.

## Approval and verification gate

Before deploying anything: independently concurrent SQL workers for two modern sessions/one user, stale events and expiry; crash/restart recovery of publication; duplicate/malformed/cross-account calls; historical/legacy regression checks; Firebase emulator or equivalent actual SDK-to-emulator transport tests for delayed conditional writes, conflicts and revision ordering; actual device lifecycle tests. Existing characterization tests remain unchanged in strength. No claim of Firebase disconnect reliability follows from a local file sink or reference-model test.

**BLOCKED pending approval:** durable session/lease state and revisioned recovery cannot be implemented under the current no-migration constraint without adding a different persistent data contract. The preceding architecture is concrete for review, but is not implemented or claimed verified.
