# Auction cloud recording and 48-hour replay

Implemented on `feature/agora-cloud-recording`, based on local `main`. No deployment,
application database migrations, real Agora requests or S3 operations were performed.

## Existing implementation inspected and reused

- `app/Models/LiveVideo.php`: auction ownership, status and completion timestamp.
- `app/Http/Controllers/api/Live/LiveVideoController.php`: auction creation, manual
  start/end, RTC initialization and token endpoints. Start/end now enforce ownership;
  repeated end preserves the completion timestamp and ended auctions cannot restart.
- `app/Console/Commands/CheckLiveVideoStatus.php`, `CheckLiveVideoEnd.php`,
  `CheckRecordedVideoStart.php`: scheduled lifecycle transitions, including the
  separate photo/prerecorded auction paths; no recording logic duplicated there.
- `app/Services/AgoraService.php` and the existing RTC token builder: REST recording
  methods extend this service without altering existing token generation.
- `AuctionItemTransferService`, dashboard auction controller, live item controller,
  resources, routes, migrations, authentication middleware, service provider,
  queue jobs/configuration, scheduler, filesystems and existing service/API tests.
- Existing `local` (private) and `s3` disks, queue dispatch, cache atomic locks, API
  authentication and scheduler infrastructure are reused. No Firebase, bid,
  settlement or payment behavior is changed.

## Behavior and files

The new migration/model persist one recording per auction, immutable driver,
project/bucket identity, resource ID, sid, storage prefix, state, retry/backoff and
completion/expiry timestamps. A reconciliation command dispatches a small queue
job every minute. Started `type=live` auctions with channels are discovered;
all existing recording rows are reconciled, including deleted auctions and media
deletion tombstones. This also recovers missed queue work and lifecycle updates
made outside the API. Acquire and start use separate passes, so startup can take
up to two scheduler intervals plus queue latency. Only one recording session per
auction is supported; automatic recorder restarts are deliberately excluded.

`AuctionRecordingService` owns state transitions and retry/backoff; `AgoraService`
owns acquire/start/query/stop and channel readiness REST requests. A broadcaster
must be present before acquire and start. Channel presence is a readiness signal,
not proof that media packets are arriving. Query checks recorder availability;
a missing worker transitions to storage processing. Stop retries failures and
partial uploads. MP4 objects must exist before replay becomes ready. HLS files
are retained for cleanup but are not exposed through playback.

The replay controller adds authenticated `GET /api/live/replay/{id}`. The first
request returns a list of signed application URLs for MP4 objects. Requests to
those URLs still require the same API authentication and headers. Each request
rechecks organizer ownership, ended status, signature and expiry. For S3 it
redirects to a private presigned URL. URL lifetimes are capped at five minutes
and the 48-hour deadline. The access policy intentionally allows only the
auction organizer; buyers and other organizers receive 403. No client recording
integration or public media URL is added. Already issued S3 URLs remain valid
until their short expiry; downloaded media cannot be revoked.

Expiry is fixed at the first observed completion timestamp plus 48 hours.
Cleanup deletes only a validated UUID recording prefix and verifies it is empty.
It retries failures with exponential backoff (up to one hour), and continues
sweeping tombstones to remove late uploads. Access expires immediately even if
physical deletion is delayed. If a recorder is still stopping, expired media is
removed before further stop attempts. A deleted auction uses discovery time as
its completion time because its original timestamp is no longer available.

Added files: recording config, migration/model, reconciliation service/job/command,
replay controller, focused tests and this guide. Modified files: AgoraService,
Console Kernel, existing start/end methods, API routes and `.env.example`.

## Safe mock activation and verification

Default settings: `AGORA_RECORDING_ENABLED=false`, `AGORA_RECORDING_DRIVER=mock`,
`AGORA_RECORDING_LIVE_ENABLED=false`. Mock sessions use the private local disk
and never call Agora, generate recording tokens or access S3, even if the live
switch is true. They do not fabricate playable video. An isolated test fixture
MP4 can be placed under the mock session prefix to exercise replay.

In an approved nonproduction environment, apply only the new migration, enable
recording with the mock driver, run the existing scheduler and a queue worker
listening to `recordings` (or `AGORA_RECORDING_QUEUE`). Start/end an owned live
auction and verify state transitions, ownership denial and expired cleanup.
Leave both live settings off until billing and private storage are ready.

Focused suite: `php vendor/bin/phpunit tests/Feature/Services/AuctionRecordingTest.php`.
Tests explicitly select SQLite `:memory:`, create only minimal isolated tables,
fake storage and HTTP, and never use the configured application database.
Coverage includes mock lifecycle, idempotency, lock contention, stop retry/backoff,
ambiguous start recovery, authorization, expiry, late uploads, path validation,
live gates and REST payloads. Run this and relevant existing API/service tests
under a compatible dependency installation before release.

Local validation: PHP lint passed for all changed PHP files and `git diff --check`
passed. PHPUnit was attempted but could not start: installed Composer dependencies
require PHP >=8.4 and the available CLI is PHP 8.2.12. No tests are claimed passed.

## Live prerequisites (not activated)

1. Enable Agora Cloud Recording/billing for the existing project. Supply
   `AGORA_APP_ID`, `AGORA_APP_CERTIFICATE`, `AGORA_CUSTOMER_ID`,
   `AGORA_CUSTOMER_SECRET`; never put values in source control.
2. Reserve a numeric `AGORA_RECORDING_UID` unused by broadcasters/viewers.
   Confirm the existing RTC channel uses live-broadcast profile (`channelType=1`)
   and numeric UIDs. Tokens currently follow the existing 24-hour TTL; auctions
   longer than that require a separately reviewed token renewal strategy.
3. Configure the existing AWS S3 environment variables and install a Laravel 8
   compatible `league/flysystem-aws-s3-v3:^1.0` adapter if absent. The current lock
   does not list it as an installed package. Review that dependency update in the
   target runtime; dependencies were not changed here.
4. Use a private, dedicated bucket or recording prefix; enforce S3 Block Public
   Access and least-privilege Put/Get/List/Delete access. The app and Agora must
   target the same bucket. Set `AGORA_RECORDING_STORAGE_REGION` to Agora's numeric
   S3 region code, not the AWS region string. Bucket versioning must be disabled
   for this prefix/bucket or a separately managed noncurrent-version deletion
   policy is required: ordinary S3 deletion does not purge historical versions.
5. Use a shared atomic cache (Redis recommended) across scheduler and workers.
   Do not use per-process array caches or per-host file caches in multi-host
   deployments. Run a supervised worker with timeout 60 and retry_after >180,
   plus the existing every-minute scheduler. Monitor `failures`, `last_error`,
   `start_unknown`, `stopping`, processing delays and queue lag. Never log raw
   Agora request/response payloads or signed URLs.
6. After staging validation and explicit operational approval, set enabled=true,
   driver=agora and live_enabled=true, rebuild config cache and restart workers.
   Changing the driver never converts existing mock sessions into live sessions.
   Changing project/bucket config blocks affected existing sessions rather than
   sending operations to the wrong project/storage.

The REST shapes follow Agora's [official recording quickstart](https://github.com/AgoraIO/docs-portal/blob/main/content/docs/en/realtime-media/cloud-recording/rest-quickstart.mdx).

## Recovery, deployment and rollback

Deploy only through the normal reviewed release process. Back up the database,
apply the single new migration (do not rerun unrelated migrations), refresh
configuration and restart supervised workers. Keep mock settings for the initial
release. No merge, push or deployment was performed by this task.

If a start response is lost, the persisted `starting` state becomes
`start_unknown`. No automatic start retry is made: Agora has no application
idempotency key here. Find the matching task via Agora operations/support using
the persisted resource ID/channel/UID. Once the correct sid is verified, an
operator can persist it and set status=recording to resume query/stop recovery.
Never reset an ambiguous session to pending until the original task is proven
absent/stopped. A stopped task returning 404 can recover through storage inventory;
partial uploads remain unavailable until stop succeeds or the worker is absent.

Rollback with active live sessions: first stop new live auction starts
operationally; let reconciliation stop every active recorder, verify stop and
retain the cleanup worker until media expiry/deletion. Disabling recording is a
kill switch for all work, including stop/cleanup; it does not stop remote Agora
tasks. If an emergency rollback disables it, stop remote tasks through Agora
operations and arrange private media cleanup separately. Then disable the
feature, refresh config/restart workers and roll back code. Retain the recording
table until all media is removed; dropping it early loses cleanup references.
Do not run a broad migration rollback on this repository.
