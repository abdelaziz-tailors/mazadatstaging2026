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
| `auction_won` | trophy | success | *Not wired up yet — no trigger exists for this event.* |
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

## The three wired-up triggers

### 1. Identity verified (Nafath)
`app/Http/Controllers/NafathController.php`. Since `POST /api/nafath/request` was never tied to an app user (no auth on that route, no `user_id` in the payload — see `NafathService`), it now **optionally accepts a `user_id`** and persists a `nafath_verification_requests` row (`request_id` ↔ `user_id` ↔ `national_id`) so the later, asynchronous `POST /api/nafath/callback` can look the requester back up.

On a `COMPLETED` callback: if the linked user's `nafath_verified_at` is still null (i.e. this is their *first* successful verification), it's set to now and an `identity_verified` notification is created. Repeated callbacks for an already-verified user are a no-op (idempotent).

**Client change needed**: the mobile app should now pass the logged-in user's `id` as `user_id` when calling `POST /api/nafath/request`, or this trigger silently won't fire (the verification itself still works — it just won't be attributable to a user).

### 2. Upcoming auction (1 hour before start)
New scheduled command `notifications:upcoming-auction-reminders` (`app/Console/Commands/SendUpcomingAuctionReminders.php`), registered `everyFiveMinutes()` in `app/Console/Kernel.php`. Requires the server cron to run Laravel's scheduler (`* * * * * php artisan schedule:run`) — same as the existing `livevideo:check` etc. jobs.

Finds `LiveVideo`s that haven't started (`status IS NULL`), start within the next hour, and haven't already gotten a reminder (`upcoming_reminder_sent_at IS NULL`), then notifies **all three account roles**:
- The **"مشترك" partner who created this specific auction** (`LiveVideo.user_id` — the account with a commercial registration + tax card that ran `POST /api/live/add`) — a real, targeted, single notification.
- Every **"بائع" (seller) with an item in that specific auction** (`LiveVideoItem.seller_id`) — a real, targeted relationship. Skipped if they're also the organizer (already notified above), so the organizer never gets duplicated.
- **Every active "مشتري" (buyer)** (`user_type` in `buyer`/`buyer_vendor`, `is_active = 1`) as a general broadcast — since there's no per-auction "interested buyers" list to target more precisely (confirmed: no "join"/"follow"/RSVP endpoint exists anywhere in the API — buyers only browse whatever's live right now), buyers get notified about *any* auction starting soon, not just ones they've engaged with. Excludes the organizer too, if they happen to be a `buyer_vendor`. Dispatched in chunks of 200 to keep memory flat regardless of buyer count.

`upcoming_reminder_sent_at` is stamped on the auction afterward so the whole job (all three groups) is idempotent per auction.

### 3. Subscription/trial expiring (24 hours left)
New scheduled command `notifications:subscription-expiry-reminders` (`app/Console/Commands/SendSubscriptionExpiryReminders.php`), registered `hourly()`.

Finds `UserSubscription`s (`status = 'approved'`) expiring within the next 24 hours and not yet reminded (`expiry_reminder_sent_at IS NULL`), notifies the owning user, then stamps `expiry_reminder_sent_at`.

> **Note on "trial" vs "paid package"**: the codebase has no separate trial concept (confirmed by grepping for "trial" — zero matches). Every subscription, free or paid, is the same `UserSubscription` row with the same `expires_at`. This one job covers both cases described in the request; there's no schema distinction to notify differently.

## Known gaps
- `auction_won` and `outbid` notification types are defined (icon/color mapped) but nothing creates them yet — no trigger exists in the codebase for "you won an item" or "you were outbid".
- The "upcoming auction" reminder can't target *specific* interested buyers (no follow/RSVP feature exists), so every active buyer gets notified about every auction starting soon, not just ones relevant to them. If a follow/RSVP feature is added later, this should switch from a broadcast to a targeted list.

## Relevant source
- Migrations: `database/migrations/2026_07_07_143935_add_user_fields_to_notifications_table.php`, `..._144622_add_nafath_verified_at_to_users_table.php`, `..._145054_create_nafath_verification_requests_table.php`, `..._145200_add_upcoming_reminder_sent_at_to_live_videos_table.php`, `..._145201_add_expiry_reminder_sent_at_to_user_subscriptions_table.php`
- Models: `app/Models/Notification.php`, `app/Models/NafathVerificationRequest.php`
- Service: `app/Services/NotificationService.php`
- Controller/Resource: `app/Http/Controllers/api/User/NotificationController.php`, `app/Http/Resources/User/NotificationResource.php`
- Routes: `routes/api/user.php`
- Commands: `app/Console/Commands/SendUpcomingAuctionReminders.php`, `app/Console/Commands/SendSubscriptionExpiryReminders.php`, scheduled in `app/Console/Kernel.php`
- Tests: `tests/Feature/Api/User/NotificationControllerTest.php`, `tests/Feature/Console/SendUpcomingAuctionRemindersTest.php`, `tests/Feature/Console/SendSubscriptionExpiryRemindersTest.php`, `tests/Feature/NafathControllerTest.php`
