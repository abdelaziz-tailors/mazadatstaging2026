# Auction business authorization compatibility audit

Baseline: Git HEAD 7c55fea, before the uncommitted concurrency changes.
Read-only tracing preceded changes. This audit adds tests and this report only;
no implementation, permission, formula, migration or existing test was changed.

## Actual roles and identity

Business roles are Subscriber, Seller, Buyer and Super Admin. There is no
Organizer role, guard, permission or role seeder. Historical documentation
already used that word for the auction-owning Subscriber; the new award error
repeats that misleading label.

- API users: users.user_type = vendor (Subscriber), seller, buyer, buyer_vendor
  (Subscriber + Buyer). User does not use Spatie HasRoles.
- Dashboard: admins, session admin guard, Spatie roles with guard_name=admin.
  FirstRolesGroupSeeder creates Super Admin; permission seeders grant explicit
  permissions. AuthServiceProvider has no active policies or Super Admin Gate
  override. Order/payment controllers use AuthAdmin plus PartnerDashboardScope,
  without a separate can/role check. Existing non-partner admin accounts are
  also unscoped: this is not an explicit hasRole('Super Admin') check.
- API: Passport api guard resolves User, independently of the admin session.
  Admin.user_id links the accounts; the link does not inherit API privileges.
  Never compare an admins.id directly to an authenticated users.id.
- LiveVideo.user_id is the API auction owner. LiveVideo.admin_id is the dashboard
  account used for management scope. Item.user_id can be its submitter/partner;
  item.seller_id is its consignor, falling back to item.user_id on order creation.
  user_finished_id and Order.buyer_id identify the winning purchaser.
- LiveVideo.partner_id is historically inconsistent: API creation stores an
  Admin ID, dashboard creation stores a User ID. partner() uses admin_id;
  partnerData() interprets partner_id as User ID. Settlement first tries
  Admin(partner_id).user_id, then User(partner_id). This ambiguity predates the
  fixes and cannot establish delegated award permission.
- Dashboard auction creation uses the linked User for type=partner accounts,
  otherwise request.partner_id for both user_id and partner_id. That field is
  nullable; a dashboard-created auction can therefore have no API owner.
- API auction creation checks linked Admin and active subscription/quota, not
  simply user_type. Its existing admin->partner check differs from Admin.type;
  this audit does not reinterpret or repair that historical condition.

## Current matrix (relationships, not invented role entitlements)

| Action | Subscriber | Seller | Buyer | Super Admin |
| --- | --- | --- | --- | --- |
| Submit valid bid | API token | API token | API token | Admin session alone: 401; linked API User follows same bid rules |
| Manual award / select winner | Only if auction.user_id equals API user | Same ownership predicate; seller_id/item.user_id alone insufficient | Same ownership predicate | No dashboard award route found; linked API User has no admin override |
| API order fee CRUD | Only own Order.buyer_id; editable state required | Same | Same | Admin session alone does not authenticate API |
| Dashboard payment / settlement / fee management | Only through linked dashboard account, scoped to auction.admin_id if Admin.type=partner | No API role grant | No API role grant | Authenticated admin account with type != partner is unscoped |
| Scheduled award | System command, no interactive business role | Same | Same | Same |
| Create order from win | Internal OrderService, called by manual/scheduled award and existing backfill/read paths | Same | Same | Same |

buyer_vendor follows the same ownership rules. Fee writes reject paid, delivered
or settled orders under the existing editability policy. Settlement credits
buyer/seller/partner relationships, not a user_type equality.

## Original versus current, confirmed by source and baseline execution

1. Bids retain authentication-only role access; no new role-based 403.
2. Original routed item award accepted any authenticated API user and any
   comment ID globally. Current owner-only check adds HTTP 403 for non-owners,
   including the item submitter/seller and linked admin User. Original acceptance
   is an authorization vulnerability, not evidence of intended delegation.
   Existing owner-only piece CRUD and the unrouted legacy auction award method
   support ownership as a pattern, but do not specify the item-award policy.
3. Owners originally could select a lower valid bid. Current service requires
   the highest numeric top-level bid on that exact auction/item. Current winner
   is immutable; same-winner retry is idempotent. Lower-bid rejection is a
   confirmed business/API behavior change; discretionary selection requirements
   are unverified. Unrestricted reassignment/foreign-comment acceptance must
   not be restored as a compatibility fix.
4. Scheduled awards originally queried video_id=item.id and latest comment ID;
   now they select the exact item's numeric highest bid atomically, preserve
   already awarded legacy winners, and create orders in the same transaction.
   Wrong-parent selection was a defect. Legacy lower/latest selection differences
   still need business-policy acknowledgement.
5. A later lot for the same buyer/auction originally changed an already-paid
   order's total. Now it is rejected. Existing lines cannot move/reprice, and
   archived orders cannot receive new lines. These preserve financial integrity,
   but mid-auction payment, reassignment and reopening workflows need an explicit
   supported policy. No safe permission-only rollback resolves them.
6. Dashboard settlement and API fee ownership are unchanged. The extra locked
   dashboard scope recheck does not block Super Admin. Tests exercise actual
   admin role assignment, HTTP middleware, payment, settled_at and ledger rows.
7. Other API item bulk update and item/auction start/end methods still lack
   parent ownership checks in the examined baseline/current code. For example,
   bulk update accepts seller_id/partner_id with authorize()=true. These are
   pre-existing security gaps, not intended permissions or new regressions.
   No live data or external notification endpoints were used to investigate.

## Provisional policy supplied during this audit

The user explicitly labels this security-safe guidance, not confirmed business
requirements: the auction-owning Subscriber may award the eligible highest bid;
Buyers and unrelated Subscribers may not; seller_id alone grants nothing;
existing Super Admin/delegated rights must be inspected before any decision;
preserve highest-only selection, atomicity, locks, idempotency and order
protections. No new permission assumptions may be implemented, and business
compatibility remains BLOCKED until requirements are confirmed.

The current service checks owner ID only. A Buyer or Seller whose ID is stored
as LiveVideo.user_id can therefore award, as demonstrated by the role tests.
This is a mismatch with the provisional Subscriber-only/Buyer-denial guidance,
not a newly introduced role grant: original code also accepted those users.
Whether this requires user_type eligibility, subscription eligibility or fixing
auction-owner assignment must be confirmed before changing implementation.
No evidence of a delegated award permission or routed Super Admin award action
was found. That absence does not prove delegation is forbidden by requirements.
Highest-only selection is consistent with the supplied provisional default;
the original selection change remains documented for final approval.
## Minimal recommendations and unresolved requirements

- Replace the award error's word Organizer with auction owner; use Subscriber
  only where the actual requirement is user_type/subscription eligibility.
- Confirm manual-award delegation and whether an authorized owner may
  intentionally select a lower valid bid. If delegation is required, define the
  guard and ownership relationship explicitly and check it under the existing
  transaction locks; do not introduce a role or numeric ID fallback.
- Confirm how auctions without an API owner are manually awarded and whether
  payment is allowed before all of a buyer's auction lots are awarded.
- Preserve locking, exact item/comment association, atomic winner/order writes,
  settled-money protection and idempotency. Fix pre-existing endpoint security
  separately against approved permissions; do not restore unrestricted access.
- Flutter should not infer permission from seller_id, item.user_id, partner_id
  or role labels. Admin sessions are not API tokens. Handle new award 403 and
  stale/immutable award failures, refresh authoritative state after timeouts.
- Firebase/FCM delivery remains best-effort after commit with possible loss or
  reordering, independently of this authorization review. No notification redesign.

## Executed verification

AuctionAuthorizationCompatibilityTest is opt-in AUTH_COMPATIBILITY_TESTS=1.
It checks PHP >=8.4, testing environment, MySQL driver and mazadat_bid_test before
starting transactional fixtures. Authentication and authorization middleware run;
only static API-key middleware and dashboard locale redirection are excluded.
Firebase is mocked; these sequential role tests do not prove concurrency safety.

Current: 23 tests / 205 assertions pass.
Original: 23 tests / 204 assertions pass with AUTH_COMPATIBILITY_BASELINE=1,
exact HEAD overlays for the two API controllers, dashboard OrderController,
OrderService, PieceServiceService and AuctionWalletSettlement. A no-network
FirebaseController test double replaces original direct construction. The
baseline mode intentionally asserts the measured differences, not their business
legitimacy. Original classes were overlaid only inside the disposable runner.
Both runs use rollback-isolated fixtures on the disposable database.

Combined current role + bid/award/financial concurrency selection: 57 tests / 723 assertions pass, zero failures/errors/skips. This includes the 23 new role tests and all 34 existing focused concurrency/financial tests. PHP 8.4 syntax and git diff --check pass. This is not a full-system regression claim.
PHP 8.4.25, MariaDB 12.2.2, REPEATABLE READ, innodb_snapshot_isolation=ON.
Private Docker network, tmpfs database, read-only source mount, temporary
application storage/cache and Passport keys; no development/production DB access. Containers, tmpfs data, keys, private network and temporary baseline overlays were removed after verification.

Verdict: BLOCKED for full business-compatibility sign-off pending the manual
award/delegation, discretionary selection and paid-order continuation policies.
No proven loss of existing Super Admin dashboard settlement access.