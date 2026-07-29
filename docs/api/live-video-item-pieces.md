# Auction Item Pieces — Add / Update / Delete

Three new endpoints to manage a single entry inside an auction item's `pieces` array (the individual physical animal units within a `video_items` row, e.g. `{"id": 113, "piece_number": 1, "age": "تام", "weight": 80, "identifier": "ضاني 1", "baham_count": 3}`) — independent of the item's other fields and without touching the rest of the pieces.

This is separate from the existing bulk `pieces` array accepted by `POST /api/live/items/update/{id}` (which replaces the *whole* set and renumbers everything). These new endpoints add/edit/remove **one** piece at a time.

Requires an authenticated user (Passport, `auth:api` middleware — an unauthenticated request gets a `401` before reaching the controller at all). **Only the organizer who owns the auction the item belongs to** (`LiveVideo.user_id = auth('api')->user()->id`) may call these — not just any authenticated user, not even another organizer.

Adding or deleting a piece keeps the parent item's `quantity` field in sync with the real remaining piece count (`quantity = count(pieces)`), so it never drifts out of sync with what's actually in the `pieces` array. Editing a piece's own fields does **not** change `quantity`.

## Add a piece

```
POST /api/live/items/pieces/add/{live_video_item_id}
Authorization: Bearer {token}
x-api-key: {api key}
Accept-Language: ar|en
```

Body (all fields optional):

| Field | Type | Notes |
|---|---|---|
| `age` | string | |
| `weight` | numeric | |
| `identifier` | string | max 255 |
| `baham_count` | string | max 255 — matches the DB column type (send as a string, not a number) |

The new piece is appended with the next `piece_number` (`max(existing piece_number) + 1`).

### Response — success

```json
{
  "success": true,
  "code": 200,
  "message": "Piece Added Successfully",
  "data": {
    "id": 399,
    "quantity": "2",
    "pieces": [
      {"id": 113, "piece_number": 1, "age": "تام", "weight": 80, "identifier": "ضاني 1", "baham_count": 3},
      {"id": 114, "piece_number": 2, "age": "سديس", "weight": 90, "identifier": "ضاني 2", "baham_count": 1}
    ]
  }
}
```

`data` is the full item (`VideoItemResource`), same shape as everywhere else this item already appears in the API — so the client can just replace its local copy with the response.

## Update a piece

```
POST /api/live/items/pieces/update/{piece_id}
Authorization: Bearer {token}
x-api-key: {api key}
Accept-Language: ar|en
```

Body — same fields as add, all optional. **Only fields actually present in the request are changed** — omitted fields keep their current value (partial update). `piece_number` itself can't be changed through this endpoint.

Response shape is the same as add (the full parent item).

## Delete a piece

```
POST /api/live/items/pieces/delete/{piece_id}
Authorization: Bearer {token}
x-api-key: {api key}
Accept-Language: ar|en
```

No body. Response shape is the same as add (the full parent item, with the piece removed and `quantity` recomputed — can drop to `0`).

## Error responses

Not authenticated (blocked by middleware before reaching the controller):

```json
{"success": false, "code": 401, "message": "un-authenticated access"}
```

Item/piece not found:

```json
{"success": false, "code": 404, "message": "Item not found"}
```
```json
{"success": false, "code": 404, "message": "Piece not found"}
```

Authenticated, but not the auction's organizer:

```json
{"success": false, "code": 403, "message": "Un-Authorized Access"}
```

Validation failure (e.g. non-numeric `weight`):

```json
{"success": false, "code": 422, "message": "weight must be a number."}
```

## Relevant source

- Controller: `App\Http\Controllers\api\Live\LiveVideoItemController::addPiece()` / `updatePiece()` / `deletePiece()` (`app/Http/Controllers/api/Live/LiveVideoItemController.php`).
- Ownership check: `LiveVideoItemController::userOwnsItemsAuction()` (private, same file).
- Business logic / `quantity` sync: `App\Services\LiveVideoItemPieceService::addPiece()` / `updatePiece()` / `deletePiece()` (`app/Services/LiveVideoItemPieceService.php`) — the same service class the existing bulk-`pieces` sync (`syncPieces()`) already lives in.
- Requests: `App\Http\Requests\api\Video\item\AddPieceRequest`, `App\Http\Requests\api\Video\item\UpdatePieceRequest`.
- Routes: registered in `routes/api/user.php`, nested under the existing `live/items` group, right after the existing item add/update/delete/start/end/last-auction/auction-award routes.
- Tests: `tests/Feature/Api/Live/LiveVideoItemPiecesTest.php`.
