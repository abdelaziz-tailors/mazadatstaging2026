# User Login

`POST /api/user/login`

Authenticates a user with either their **phone number** or **email address**, plus their password. The account type (`user_type`) must also be supplied and must match the account's actual type.

## Headers

| Header | Required | Notes |
|---|---|---|
| `x-api-key` | Yes | Shared API key required by the `APISettings` middleware for every `/api/*` route. |
| `Accept-Language` | Yes | `ar` or `en`. Any other value returns a `language_not_supported` error. |
| `Content-Type` | Yes | `application/json` |

## Request body

| Field | Type | Required | Notes |
|---|---|---|---|
| `phone` | string | Yes | Either the account's **phone number** or its **email address**. The API detects which one was sent — if the value passes email validation it is matched against the `email` column, otherwise it is matched against the `phone` column. The field name stays `phone` for backward compatibility with existing clients. |
| `password` | string | Yes | Account password. |
| `user_type` | string | Yes | One of `buyer`, `vendor`, `buyer_vendor`, `seller`. Must match the account's registered type (see below). |

### `user_type` matching rules

- If the value equals the account's stored `user_type`, the login proceeds.
- Accounts registered as `buyer_vendor` may log in as either `buyer` or `vendor`.
- Any other mismatch is rejected with the `account_type_not_match` error, and the session is not created.

## Responses

### Success — `200`

```json
{
  "success": true,
  "code": 200,
  "message": "your account logged in successfully",
  "data": {
    "id": 1,
    "name": "Ahmed",
    "email": "ahmed@example.com",
    "phone": "0555555555",
    "user_name": "ahmed01",
    "share_url": "https://mazadat.anaamworld.com.sa/u/1",
    "is_verified": true,
    "user_type": "buyer",
    "image": null,
    "token": "<oauth-access-token>"
  }
}
```

### Validation error — `200` (missing/invalid `phone`, `password`, or `user_type`)

```json
{
  "success": false,
  "code": 422,
  "message": "please enter user type"
}
```

> Note: FormRequest validation failures on this endpoint currently return HTTP status `200` with `code: 422` in the body (pre-existing behavior of `LoginRequest::failedValidation`), not an HTTP `422` status.

### Wrong credentials — `200`

```json
{
  "success": false,
  "code": 200,
  "message": "phone or password not correct"
}
```

### Account type mismatch — `422`

```json
{
  "success": false,
  "code": 422,
  "message": "The selected account type does not match your registered account type"
}
```

## Examples

### Login with phone

```bash
curl -X POST https://mazadat.anaamworld.com.sa/api/user/login \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Content-Type: application/json" \
  -d '{
        "phone": "0555555555",
        "password": "secret123",
        "user_type": "buyer"
      }'
```

### Login with email

```bash
curl -X POST https://mazadat.anaamworld.com.sa/api/user/login \
  -H "x-api-key: <api-key>" \
  -H "Accept-Language: ar" \
  -H "Content-Type: application/json" \
  -d '{
        "phone": "ahmed@example.com",
        "password": "secret123",
        "user_type": "vendor"
      }'
```

## Relevant source

- Controller: `app/Http/Controllers/api/User/Auth/LoginController.php`
- Validation: `app/Http/Requests/api/User/Auth/LoginRequest.php`
- Tests: `tests/Feature/Api/User/Auth/LoginControllerTest.php`, `tests/Unit/Http/Requests/Api/User/Auth/LoginRequestTest.php`

## Known gap (registration, not this endpoint)

Registration (`RegisterRequest`) currently requires `email` and allows `phone` to be blank, but the OTP verification flow (`VerifyAccountController`, `UserOtp`) is keyed entirely on `phone` and OTPs are only ever delivered by SMS/WhatsApp (`SmsService`) — there is no email delivery path. In practice, phone is the real required identifier for completing registration. This doesn't affect login (which simply looks up whichever identifier the account already has), but it means an account created without a phone would not reliably be able to log in by phone (only by email, if one was set).
