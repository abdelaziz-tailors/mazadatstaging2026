# Production Hotfix API Handoff

This document covers only the isolated production hotfix for auction transfer
persistence and existing live Agora initialization. It does not include Agora
Cloud Recording, replay, 48-hour logic, recording webhooks, jobs, or storage.

## Required headers

All endpoints are authenticated and require:

```http
x-api-key: <the configured project API key>
Authorization: Bearer <user access token>
Accept-Language: ar
Content-Type: application/json
```

The authenticated user must own the source auction and, when applicable, the
destination auction.

## Transfer to an existing auction

### List transferable unsold items

```http
GET /api/live/transferable/{source_auction_id}
```

Optional query parameter:

```text
target_auction_id=<destination auction id>
```

The response contains only items from an ended source auction that are unsold,
have no order, and have not already been transferred.

### Transfer items

```http
POST /api/live/transfer
```

Request body:

```json
{
  "source_auction_id": 374,
  "item_ids": [1201, 1202],
  "transfer_mode": "existing",
  "target_auction_id": 380
}
```

On success, each selected item is persisted as a destination item through the
destination auction relation. The new copy has `live_video_id` equal to the
destination auction ID, preserves seller/media/item/quantity/bid data, and
retains the transfer lineage fields. The response includes the persisted
destination auction and transferred items.

The mobile-compatible aliases are also available:

```http
GET /api/live/items/transferable/{source_auction_id}
POST /api/live/items/transfer
```

The POST alias accepts the same body. A single `item_id` and
`target_location_id` are also normalized by the existing mobile compatibility
logic.

## Transfer and create a new live auction

Use the same transfer endpoint with `transfer_mode: "new"`:

```http
POST /api/live/transfer
```

Request body:

```json
{
  "source_auction_id": 374,
  "item_ids": [1201],
  "transfer_mode": "new",
  "new_auction": {
    "type": "live",
    "title_ar": "مزاد مباشر جديد",
    "title": "New live auction",
    "date_start_at": "2026-08-30",
    "date_end_at": "2026-08-30",
    "time_start_at": "10:00",
    "time_end_at": "12:00",
    "start_price": 100,
    "information_ar": "",
    "information": "",
    "terms_conditions_ar": "",
    "terms_conditions": "",
    "city_id": 3
  }
}
```

The new live auction is initialized through the existing `AgoraService` path.
The success response must include non-empty credentials:

```json
{
  "success": true,
  "data": {
    "target_auction": {
      "id": 381,
      "video_type": "live",
      "agora": {
        "app_id": "string",
        "channel_name": "string",
        "token": "string",
        "uid": 119
      }
    },
    "transferred_items": []
  }
}
```

Flutter must not start the live session if any required Agora field is empty.
If credentials cannot be generated, the transaction rolls back and the API
returns HTTP 422 instead of reporting a false successful live initialization.

## Failure behavior

Validation, ownership, duplicate-transfer, persistence, or Agora-initialization
failures return an error response and roll back the transaction where
applicable. An item cannot be transferred from a non-ended auction, to itself,
or more than once to the same destination origin.

No Cloud Recording or replay API is included in this hotfix.
