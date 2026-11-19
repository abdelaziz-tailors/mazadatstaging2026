# Flutter auction details: real Passport verification

Verified locally on 2026-11-17 using PHP 8.2.12 and PHPUnit 9.6.29.

The new cases in `tests/Feature/Api/BiddingPercentageVisibilityTest.php` reuse
the existing `scenario()` and `prepareBearerAuthentication()` helpers. Tokens
are issued through Passport `createToken()` with ephemeral RSA keys and OAuth
tables in SQLite `:memory:`. Requests send actual Authorization bearer headers;
these cases do not use `Passport::actingAs()`.

Requests exercise `GET /api/home/video/{id}`, its real middleware (including
the re-enabled APISettings middleware with the required API key),
`HomeVideoController@show`, `HomeVideoResource`, and `AuctionFeesResource`.
The RequestHandled event captures `$request->user('api')` after the response
pipeline and asserts the exact identity, including null for guests.

| Viewer | Payer | Commission visibility | Result |
| --- | --- | --- | --- |
| Linked seller | Seller | Visible | PASS |
| Auction owner | Seller | Visible | PASS |
| Buyer | Seller | Hidden | PASS |
| Seller | Buyer | Visible | PASS |
| Buyer | Buyer | Visible | PASS |
| Guest without bearer | Seller | Hidden | PASS |

Forged uid/user_id/seller_id/isHost/user_type query parameters grant no access
for either a bearer-authenticated buyer or a guest. The test makes consecutive
bearer and guest requests with fresh guards to avoid retained test identity.

Malformed, revoked and expired bearer tokens each return HTTP 200 with a
successful public response, null API identity, and hidden commission fields.
The public home route has no `auth:api` requirement. The installed Passport
TokenGuard catches OAuth validation failures and clears the Authorization
header; the public controller continues as a guest. The existing protected
`/api/live/video/{id}` rejection tests remain unchanged and pass.

Contract assertions cover exact envelope and home-detail field names, the Agora
field names, auction fee field names, item collection/favorite types, auction
identity, service fee, authorized commission values, and null hidden commission
values with recursive checks for commission leaks. No production logic or API
contract was changed, and no reproducible backend defect was found.

Commands and exact results:

```text
php vendor/bin/phpunit -c phpunit.visibility.xml --filter test_home_detail
OK (10 tests, 252 assertions)
Time: 00:47.581, Memory: 44.00 MB

php vendor/bin/phpunit -c phpunit.visibility.xml
OK (130 tests, 2254 assertions)
Time: 00:37.730, Memory: 66.00 MB

php -l tests/Feature/Api/BiddingPercentageVisibilityTest.php
No syntax errors detected

git diff --check
Passed (line-ending normalization warning only)
```

The existing visibility PHPUnit configuration forces testing, SQLite memory
databases, empty database URLs and MySQL override, array cache/session, sync
queue, and disabled Telescope. Fixtures assert the active SQLite connection is
in memory. Only disposable test schemas were used; application data was not
reset, migrated, seeded, or edited. The previously documented Herd PHP path was
absent, so the available `php` executable was used.

Files changed for this task: the existing visibility test file (additive changes,
preserving pre-existing edits) and this report. The pre-existing untracked
`BIDDING_PERCENTAGE_BACKEND_VERIFICATION_2026_11_16.md` report was not edited.
