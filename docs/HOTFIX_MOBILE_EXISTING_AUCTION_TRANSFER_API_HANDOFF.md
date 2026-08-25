# Mobile Existing-Auction Transfer Hotfix API Handoff

This handoff covers only mobile transfer of an unsold item to an existing
auction. No Agora, recording, replay, or live-streaming work is included.

The isolated hotfix commit SHA is reported in the delivery report and Git
history; this document intentionally avoids a self-referencing commit hash.

## Headers

```http
x-api-key: <configured project API key>
Authorization: Bearer <user access token>
Accept-Language: ar
Content-Type: application/json
```

## Endpoints

Primary routes:

```http
GET  /api/live/transferable/{source_auction_id}
POST /api/live/transfer
```

Mobile-compatible aliases:

```http
GET  /api/live/items/transferable/{source_auction_id}
POST /api/live/items/transfer
```

The target-auction details endpoint used by Flutter is:

```http
GET /api/live/video/{target_auction_id}
```

## Existing-auction transfer request

Single-item form:

```json
{
  "item_id": 123,
  "target_auction_id": 456,
  "transfer_mode": "existing"
}
```

Array form:

```json
{
  "source_auction_id": 100,
  "item_ids": [123],
  "target_auction_id": 456,
  "transfer_mode": "existing"
}
```

`target_auction_id` is an ID from `live_videos.id`. It is not a city ID or a
location ID. The legacy `target_location_id` field is accepted only as an
auction-ID compatibility alias; an unknown or non-owned value is rejected and
cannot produce a success response.

## Persistence contract

On success, the backend creates a new `live_video_items` row and persists it
through the destination auction relation. The row must have:

```text
live_video_id = target auction ID
transferred_from_item_id = original item ID
transfer_origin_item_id = root transfer origin ID
```

Seller, media, quantity, bid/opening values, and related pieces are preserved.
The response is returned only after the created row and its destination
relation have been verified. The response contains the target auction and a
non-empty `transferred_items` collection.

After success, Flutter must refetch `/api/live/video/{target_auction_id}` (or
invalidate and refresh its target-auction state) and render the item from the
returned `video_items` collection. A success toast alone is not sufficient.

## Failure behavior

The API rejects non-ended sources, ownership failures, invalid target auction
IDs, duplicate transfers, sold/ordered items, and persistence failures. These
failures return an error response and do not report a false success.

No Agora or recording fields are changed by this hotfix.
