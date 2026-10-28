# Notifications

Replaces the old, broken `UserProfileController::notifications()` (which listed every notification ever created after the user's registration date, with no per-user targeting, no read state, and no pagination) with a proper per-user notification inbox.

All endpoints below require an authenticated user (Passport bearer token). Like other `Profile`-style endpoints in this app, the route itself has no `auth:api` middleware — the controller checks `auth('api')->user()` manually and returns an "Un Authenticated" failure if missing.

## `GET /api/user/notifications`

Paginated list of the user's own notifications, plus any legacy/admin "broadcast" rows (`user_id IS NULL`, e.g. from the dashboard's manual notification broadcast feature).

### Query params
| Param | Type | Notes |
|---|---|---|
| `limit` | int | Page size, default 15. |
| `page` | int | Pagination page number. |

### Response
```json
{
  "success": true,
  "code": 200,
  "message": "Successfully",
  "data": [
    {
      "id": 12,
      "type": "identity_verified",
      "icon": "shield-check",
      "color": "success",
      "title": "تم التحقق من هويتك",
      "description": "تم توثيق حسابك عبر نفاذ بنجاح.",
      "data": [],
      "is_read": false,
      "time": "2 hours ago",
      "created_at": "2026-07-07T12:00:00.000000Z"
    }
  ],
  "unread_count": 2,
  "pagination": { "total": 5, "count": 5, "per_page": 15, "current_page": 1, "total_pages": 1 }
}
```

`icon`/`color` are derived server-side from `type` (see `NotificationResource::TYPE_META`) so the app doesn't need its own type→icon mapping:

| `type` | icon | color | Triggered by |
|---|---|---|---|
| `identity_verified` | shield-check | success | First successful Nafath verification (see below). |
| `upcoming_auction` | gavel | info | An auction the seller has items in starts within 1 hour (see below). |
| `payment_reminder` | dollar | warning | A `UserSubscription` expires within 24 hours (see below). |
| `auction_won` | trophy | success | Inbox type remains unwired; the existing scheduled winner congratulations push is preserved. |
| `outbid` | close | danger | *Not wired up yet — no trigger exists for this event.* |

## `GET /api/user/notifications/unread-count`
```json
{ "success": true, "code": 200, "message": null, "data": { "unread_count": 2 } }
```

## `POST /api/user/notifications/{id}/mark-read`
Marks one notification read. Returns a failure if the notification doesn't belong to the user (or doesn't exist) — same response either way, so a caller can't probe other users' notification IDs.

## `POST /api/user/notifications/mark-all-read`
Marks every unread notification (owned by the user) as read.

---

## Existing triggers and lifecycle notifications

### 1. Identity verified (Nafath)
`app/Http/Controllers/NafathController.php`. Since `POST /api/nafath/request` was never tied to an app user (no auth on that route, no `user_id` in the payload — see `NafathService`), it now **optionally accepts a `user_id`** and persists a `nafath_verification_requests` row (`request_id` ↔ `user_id` ↔ `national_id`) so the later, asynchronous `POST /api/nafath/callback` can look the requester back up.

On a `COMPLETED` callback: if the linked user's `nafath_verified_at` is still null (i.e. this is their *first* successful verification), it's set to now and an `identity_verified` notification is created. Repeated callbacks for an already-verified user are a no-op (idempotent).

**Client change needed**: the mobile app should now pass the logged-in user's `id` as `user_id` when calling `POST /api/nafath/request`, or this trigger silently won't fire (the verification itself still works — it just won't be attributable to a user).

### 2. Upcoming auction (1 hour before start)

`notifications:upcoming-auction-reminders` still runs every five minutes and uses the existing one-hour window and `upcoming_reminder_sent_at` marker. It now selects both `pending` and legacy NULL statuses, locks/rechecks each auction, and commits the marker and notifications together.

Recipients are active, non-deleted users connected to that auction: its organizer and linked partner account, sellers and winners of current items, organizer followers, item favorites, viewers, and item bid/comment participants. Transferred-away items and mismatched auction/item comments cannot grant eligibility. Shared `video_favorites` IDs are deliberately excluded because that table's `video_id` belongs to the separate `Video` model. Unrelated buyers no longer receive a broadcast. Overlapping relationships produce one notification per event and user.

Titles and bodies use each recipient's `app_lang`, with Arabic fallback for unset/unsupported values. These notifications are persisted and pushed after commit through the existing `FCMService`; failed delivery remains recoverable by the scheduled `notifications:retry-pushes` command.

### 3. Subscription/trial expiring (24 hours left)
New scheduled command `notifications:subscription-expiry-reminders` (`app/Console/Commands/SendSubscriptionExpiryReminders.php`), registered `hourly()`.

Finds `UserSubscription`s (`status = 'approved'`) expiring within the next 24 hours and not yet reminded (`expiry_reminder_sent_at IS NULL`), notifies the owning user, then stamps `expiry_reminder_sent_at`.

> **Note on "trial" vs "paid package"**: the codebase has no separate trial concept (confirmed by grepping for "trial" — zero matches). Every subscription, free or paid, is the same `UserSubscription` row with the same `expires_at`. This one job covers both cases described in the request; there's no schema distinction to notify differently.

## Auction, seller-request, and purchased-order transitions

| Type | Trigger | Recipient |
|---|---|---|
| `auction_started` | Actual change to `start` | Eligible auction users described above |
| `auction_ended` | Actual change to `end` | Eligible auction users described above |
| `auction_status_changed` | Actual change back to supported `pending` | Eligible auction users described above |
| `seller_request_status_changed` | Change to `pending`, `approved`, `rejected`, or `needs edit` | Submitting `seller_submissions.user_id` only |
| `order_status_changed` | Change to `pending`, `confirmed`, `preparation`, `ready_for_delivery`, `shipping`, or `delivered` | `orders.buyer_id` only |
| `order_payment_status_changed` | Change between `unpaid` and `paid` | `orders.buyer_id` only |

Initial creation, unrelated edits, and repeated status saves do not create lifecycle notifications. State changes use Eloquent model hooks, row locks, and the same database transaction as their receipts. Existing order normalization (`paid` + `pending` becomes `confirmed`) is preserved. Order items have no independent fulfillment status; order status is the actual purchased-animal fulfillment workflow.

The notification inbox envelope and read APIs above are unchanged. `data` includes the existing-compatible `live_video_id`, applicable `seller_submission_id` or `order_id`, `event`, `event_id`, `occurred_at`, `entity_type`, `entity_id`, transition `attribute/from_status/to_status`, and additive `navigation` entity metadata. Push values are strings; nested navigation data is JSON encoded. Both storage and push use `click_action=FLUTTER_NOTIFICATION_CLICK` and `screen=notification`, matching the existing manual broadcast convention. Push also includes `notification_id` and `type`.

The dashboard broadcast manager lists/views/deletes only rows with NULL `user_id`; it cannot expose or delete private app inbox history. App inboxes retain their existing own-user-plus-broadcast visibility.

## Known limits

- No Flutter source/client execution was available. The existing inbox destination and entity metadata are covered by backend tests; actual client navigation and entity detail routing remain unverified.
- The existing scheduled winner-only congratulations push is preserved. `auction_won` inbox records and `outbid` remain unwired; this change does not add duplicate winner messages or alter awards.
- FCM transport is at-least-once after an ambiguous send/crash. Database receipts are unique and completed duplicate jobs do not resend; stable `notification_id` enables client deduplication, whose implementation is unverified.
- The scheduler and queue worker must run as already required by the application. No active device token means push is marked complete without sending; inbox history remains.
- Bulk SQL/query-builder status writes bypass Eloquent hooks. The traced application status writers use model updates. Transaction/lock guarantees require InnoDB; the new migration checks it without converting tables.

See [implementation and verification report](../NOTIFICATION_LIFECYCLE_REPORT.md) for the schema, changed files, isolated test commands, and results.

## Relevant source
- Migrations: `database/migrations/2026_07_07_143935_add_user_fields_to_notifications_table.php`, `..._144622_add_nafath_verified_at_to_users_table.php`, `..._145054_create_nafath_verification_requests_table.php`, `..._145200_add_upcoming_reminder_sent_at_to_live_videos_table.php`, `..._145201_add_expiry_reminder_sent_at_to_user_subscriptions_table.php`
- Models: `app/Models/Notification.php`, `app/Models/NafathVerificationRequest.php`
- Service: `app/Services/NotificationService.php`
- Controller/Resource: `app/Http/Controllers/api/User/NotificationController.php`, `app/Http/Resources/User/NotificationResource.php`
- Routes: `routes/api/user.php`
- Commands: `app/Console/Commands/SendUpcomingAuctionReminders.php`, `app/Console/Commands/SendSubscriptionExpiryReminders.php`, scheduled in `app/Console/Kernel.php`
- Tests: `tests/Feature/Api/User/NotificationControllerTest.php`, `tests/Feature/Console/SendUpcomingAuctionRemindersTest.php`, `tests/Feature/Console/SendSubscriptionExpiryRemindersTest.php`, `tests/Feature/NafathControllerTest.php`
