# Mazadat API — Postman Guide

This directory contains a Postman collection and environment covering every
current Mazadat mobile/API endpoint, generated directly from the Laravel
route table (`php artisan route:list --path=api`) on 2026-08-26.

```text
docs/postman/Mazadat_API.postman_collection.json      — the collection (Postman v2.1)
docs/postman/Mazadat_Production.postman_environment.json — matching environment (secrets left empty)
```

## 1) Import into Postman

1. Postman → **Import** → select `Mazadat_API.postman_collection.json`.
2. Postman → **Import** → select `Mazadat_Production.postman_environment.json`.
3. Select the **Mazadat Production** environment from the environment picker
   (top-right) before sending requests.

## 2) Set `base_url`

The environment ships with:
```text
base_url = https://mazadat.anaamworld.com.sa/api
```
For local/staging testing against `mazadatstaging`, edit the environment
variable to your local URL, e.g.:
```text
http://localhost/mazadatstaging/public/api
```

## 3) Set `x-api-key`

Every endpoint that isn't a Telescope/internal route requires the
`x-api-key` header (enforced by the `APISettings` middleware). Set the
`api_key` environment variable to the project's configured key. Requests
without it, or with the wrong value, receive:
```json
{"success": false, "code": 403, "message": "unauthorized_access"}
```
The two exceptions are the Nafath callback/request routes, which run without
`APISettings` and so do not require `x-api-key` — see the **Nafath (Identity
Verification)** folder.

## 4) Set `access_token`

Most endpoints beyond public lookups (categories, cities, packages, etc.)
also require `Authorization: Bearer {{access_token}}` (Passport, guard
`api`). Get a token from **Auth → POST /user/login** (or `/user/register`),
copy `data.token` (or the field your build returns) from the response, and
set it as the `access_token` environment variable. Every request that needs
it already has the `Authorization` header pre-filled with `{{access_token}}`
in the collection — requests that don't need auth simply omit that header.

## 5) Switch `Accept-Language`

Every request sends `Accept-Language: ar` by default (matches the
`APISettings` middleware, which falls back to `config('app.locale')` if the
header is absent and rejects anything other than `ar`/`en`). Edit the header
on a request (or duplicate the folder) to send `en` instead.

## 6) Basic auth workflow

1. `POST /user/register` (or `/user/login` if the account already exists).
2. Copy the returned access token into `{{access_token}}`.
3. Call any authenticated endpoint — the `Authorization` header is already
   configured on those requests.
4. `POST /user/logout` to invalidate the token when done.

## 7) Example: auction item transfer flow

See the **Auction Item Transfer** folder. Read this carefully — it's easy to
assume the `GET` step performs the transfer; it does not.

1. **`GET /live/transferable/{item_id}`** (or the `/live/items/transferable/{item_id}`
   alias) — previews/validates which items are eligible to transfer. Add
   `?target_auction_id={{target_auction_id}}` to also exclude items already
   transferred into that specific target. **This step does not move
   anything.**
2. **`POST /live/transfer`** (or the `/live/items/transfer` alias) — this is
   the only call that actually performs the transfer. Body:
   ```json
   { "item_id": {{item_id}}, "target_auction_id": {{target_auction_id}}, "transfer_mode": "existing" }
   ```
   or the array form:
   ```json
   { "source_auction_id": {{auction_id}}, "item_ids": [{{item_id}}], "target_auction_id": {{target_auction_id}}, "transfer_mode": "existing" }
   ```
3. `target_auction_id` must be a real `live_videos.id` — never a city or
   location ID. The response's `transferred_from_item_id` points back at the
   original item; `transfer_origin_item_id` preserves the root identity even
   across multiple hops. The newly created item gets its own, different, ID.

To create a brand-new auction and transfer into it in one call, set
`"transfer_mode": "new"` and include a `new_auction` object instead of
`target_auction_id` — see the request body already filled in on that item in
the collection.

## 8) File upload requests

Endpoints that accept files (registration's `commercial_register`, profile
image/files, subscription payment proof, seller submission images/video,
auction win video, cart payment proof, story media, live item images/video,
etc.) are pre-configured as `form-data` bodies with `type: file` fields. In
Postman, click each file field and choose a local file before sending —
Postman does not ship example binaries. Do not switch these requests to
`raw`/JSON; the underlying Laravel validation requires `file`/`mimes` rules
that only work with a real multipart upload.

## 9) Notes

- The **temporary diagnostic logging** added to the existing-auction
  transfer endpoint (`MOBILE_TRANSFER_REQUEST` / `_NORMALIZED` / `_SUCCESS`
  / `_FAILED` log entries) is an internal troubleshooting aid only — it is
  not part of the public API contract and isn't reflected in this
  collection.
- No Agora, Cloud Recording, Replay, or 48-hour-recording endpoints exist in
  the current route table; none are documented here because none exist.
