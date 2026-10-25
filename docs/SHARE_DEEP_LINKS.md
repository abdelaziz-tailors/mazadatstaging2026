# Share deep links

Routes: `/u/{id}` (unchanged), `/share/auction/{id}`, `/share/lot/{id}`.
Named routes: `shared.profile`, `shared.auction`, `shared.lot`.
Generate canonical HTTPS links with the configured public origin, never a custom scheme for sharing.

The public auction search/detail code has no publication/private flag and includes upcoming and archived auctions. Landing pages follow that visibility and expose only title, description, image and ended status. Lots follow `currentVideoItems` / `notTransferredAway` and require an existing parent auction. No bidding, viewer registration, tokens, buyer or seller details are exposed. Missing/invalid IDs and unavailable lots return 404. HTML/OG content is rendered server-side and escaped. Empty images omit og:image.

## Required configuration

Set DEEP_LINK_BASE_URL (or APP_URL) to the canonical HTTPS origin; configure the public storage disk URL to serve accessible HTTPS images.
Set DEEP_LINK_ANDROID_PACKAGE_NAME and comma-separated DEEP_LINK_ANDROID_SHA256_CERT_FINGERPRINTS to the real release/Play App Signing values.
Set DEEP_LINK_IOS_TEAM_ID and DEEP_LINK_IOS_BUNDLE_IDS to the real app identifiers.
Set DEEP_LINK_PATHS=/u/*,/share/auction/*,/share/lot/* (merge any additional existing paths). An existing environment override of /u/* must be updated explicitly.
Set DEEP_LINK_IOS_STORE_URL and DEEP_LINK_ANDROID_STORE_URL to the approved store listings.
Optional DEEP_LINK_AUCTION_APP_URL and DEEP_LINK_LOT_APP_URL accept {id} and {auction_id}; configure only after confirming the actual mobile router. Without templates the Open button uses the canonical HTTPS link. The server never guesses a new custom-scheme route or forces a store redirect. Existing profile behavior/configuration is preserved.
Apply settings through the normal configuration cache process when releasing. No production settings or deployment were changed here.

## Mobile work still required

No mobile project was found in this repository; its framework is unknown.
Android: configure autoVerify HTTPS VIEW/DEFAULT/BROWSABLE intent filters for the public host and all three path prefixes. Verify release signing certificates match assetlinks.json.
iOS: enable Associated Domains with applinks:<public-host>, matching the served AASA appID and paths.
Both: parse and validate the exact IDs from HTTPS links; dispatch profile, auction, and lot destinations. Route both initial launch and incoming URLs while running, wait for navigation/auth readiness, retain the destination through login, fetch current authorized content, handle 404/deletion/transfer, and display archived content for ended auctions without enabling bidding. A web fallback cannot implement these native lifecycle handlers.

## Verification

Run tests/Feature/ShareDeepLinksTest.php using PHP >=8.4 with pdo_sqlite and mbstring; it uses only a dedicated in-memory database.
On staging HTTPS, check both AASA endpoints and /.well-known/assetlinks.json return 200 application/json without authentication or redirects. Check real IDs and preview images using a social preview debugger.
Verify Android domain approval (`adb shell pm verify-app-links --re-verify <package>` and `adb shell pm get-app-links <package>`); launch each HTTPS path from an external app.
On physical Android and iOS devices test profile/auction/lot links with the release app installed: cold launch, foreground, background, logged out then login, ended auctions, invalid IDs and transferred/deleted lots. Confirm exact destination and normal back navigation.
Uninstall the app and open the same links: confirm readable landing pages and the correct store buttons. Check English/Arabic content and missing images. iOS same-domain Safari navigation may remain in Safari; test taps from Messages/Mail as well.

## Follow-up audit (2026-10-25)

No Android/iOS source or mobile dependency manifest is available in this checkout or its parent directory. package.json is Laravel Mix tooling. Native routing/manifest/entitlement changes cannot be implemented here.
The local .env currently selects http://localhost, overrides DEEP_LINK_PATHS with /u/*, and has no Android SHA-256 fingerprints. App identifiers and store settings are present but have not been validated against a signed app or approved listings. These settings were not changed or inferred. No staging HTTPS origin or release app was supplied, so deployed TLS, redirects, public images, store destinations, and device routing remain unverified.
Profile HTML retains its existing title/description/url OG fields and legacy redirect behavior; it has no og:image. Auction/lot previews include og:image when an image exists. Profile behavior was deliberately left unchanged.
The additional transfer test verifies that the original lot returns 404, while the transferred copy keeps its own lot ID and uses its new parent auction ID in the configured navigation template.

### Exact staging/device checklist

Replace every angle-bracket placeholder with a verified value. Do not execute against localhost for domain verification.

1. Set the verified HTTPS origin, signing fingerprints, app identifiers, store URLs and all required paths through the normal staging configuration process. Confirm fingerprints against the Play Console App Signing certificate (or the certificate of the installed build). Do not use the upload certificate unless it actually signs that build.
2. Run each command without -L; require HTTP 200, application/json and no Location header. Inspect the JSON for the exact installed application identifiers, signing fingerprints and all three iOS paths:

   ```powershell
   curl.exe -i "https://<host>/.well-known/assetlinks.json"
   curl.exe -i "https://<host>/.well-known/apple-app-site-association"
   curl.exe -i "https://<host>/apple-app-site-association"
   ```

3. Fetch `/u/<profile-id>`, `/share/auction/<auction-id>` and `/share/lot/<lot-id>` with curl.exe against the same HTTPS origin. Confirm HTTP 200, canonical content IDs and server-rendered OG fields. Open og:image URLs without login and confirm image responses. Run the links through the intended social platform's preview debugger.
4. After the mobile team adds the host/path filters and routing, install the correctly signed Android build and run:

   ```powershell
   adb shell pm verify-app-links --re-verify <package>
   adb shell pm get-app-links <package>
   adb shell am start -W -a android.intent.action.VIEW -c android.intent.category.BROWSABLE -d "https://<host>/u/<profile-id>"
   adb shell am start -W -a android.intent.action.VIEW -c android.intent.category.BROWSABLE -d "https://<host>/share/auction/<auction-id>"
   adb shell am start -W -a android.intent.action.VIEW -c android.intent.category.BROWSABLE -d "https://<host>/share/lot/<lot-id>"
   ```

   Require the host to be verified and each destination to show the exact ID. Repeat from an external message with the app closed, foregrounded and backgrounded, and after logging out. Complete login and confirm the original destination is retained.
5. On a physical iOS device, install the signed build with the matching Associated Domains entitlement. Tap each URL from Messages or Mail in the same lifecycle/auth states; confirm the exact screen and ID. Test fresh installation after association changes because the OS caches AASA.
6. Test an ended auction and its lot: show archived state with no bidding. Test nonnumeric, nonexistent and deleted IDs: show a controlled unavailable screen. For a transferred lot, the original URL must be unavailable; share the copy ID and confirm its new parent auction, not its source auction.
7. Uninstall the apps and tap all three URLs again. Auction/lot links must remain readable web pages with working configured store links; profile must retain its existing fallback behavior. Verify the stores lead to the intended apps. Installed-app success cannot be signed off until both device matrices pass.

## Association conflict resolution (2026-10-25)

The former public/.well-known/assetlinks.json and apple-app-site-association files were moved byte-for-byte into config/deep_link_associations/. They are configuration data, no longer public web resources. Their legacy Android package and all three certificates, and the legacy iOS app's /reels/* path, are merged into the existing Laravel responses. No legacy identifiers were reassigned or removed. Both AASA URLs now use the same controller response, application/json, without a redirect. Unrelated .well-known/pki-validation files are untouched.

The existing single-app environment settings remain supported. The configured iOS sharing app receives /u/*, /share/auction/* and /share/lot/* even if an older DEEP_LINK_PATHS override contains only /u/*. Additional existing configured paths are retained. The legacy reels app does not automatically receive sharing paths. Android statements are domain-level associations; each Android app must constrain its own intent filters to its intended paths. A statement does not prove that an app actually implements a route.

Optional additional apps can be configured with JSON arrays in DEEP_LINK_ANDROID_APPS (entries containing package_name and sha256_cert_fingerprints) and DEEP_LINK_IOS_APPS (entries containing appID and paths). For example, use the following shapes, substituting verified values; do not copy placeholder certificates:

```dotenv
DEEP_LINK_ANDROID_APPS='[{"package_name":"<verified-package>","sha256_cert_fingerprints":["<verified-colon-separated-SHA256>"]}]'
DEEP_LINK_IOS_APPS='[{"appID":"<verified-team>.<verified-bundle>","paths":["/u/*","/share/auction/*","/share/lot/*"]}]'
```

Entries for the same Android app merge certificates; entries for the same iOS app merge paths. Invalid Android SHA-256 strings are excluded; an app without a valid certificate is not added. The legacy JSON configuration files retain previously published values without claiming their current ownership or signing status has been verified. Review additional app settings before use; keep separate apps' identities and certificates separate.

Release prerequisite: ensure the two obsolete public files are actually absent from the deployed document root. An upload-only deployment may leave stale files behind even though this repository moved them. Also remove any equivalent CDN/web-server static override through the normal approved release process, refresh relevant CDN caches, and rebuild the Laravel configuration cache after setting the real environment. This task performed none of those production operations. Nginx must forward nonexistent resources to Laravel (normal try_files front-controller configuration); Apache's existing !-f rewrite already does so. Do not change certificate-validation routes.

Local verification: all three endpoints returned HTTP 200 with application/json, valid JSON and no Location header via the PHP HTTP server using server.php. AASA bodies were identical; legacy /reels/* and sharing /u/*, /share/auction/*, /share/lot/* were present on their respective apps. Tests bypassed the pre-existing local configuration cache via a temporary APP_CONFIG_CACHE path, without modifying that cache or .env. HTTPS/TLS, CDN behavior, production identities and native device routing still require staging verification.

### Staging commands after normal release preparation

Use the real staging host. curl.exe does not follow redirects here; require 200, application/json, and no Location header on every response:

```powershell
curl.exe --silent --show-error --dump-header - "https://<host>/.well-known/assetlinks.json"
curl.exe --silent --show-error --dump-header - "https://<host>/.well-known/apple-app-site-association"
curl.exe --silent --show-error --dump-header - "https://<host>/apple-app-site-association"
$a = Invoke-RestMethod "https://<host>/.well-known/apple-app-site-association"
$b = Invoke-RestMethod "https://<host>/apple-app-site-association"
($a | ConvertTo-Json -Depth 20 -Compress) -ceq ($b | ConvertTo-Json -Depth 20 -Compress)
$a.applinks.details | ConvertTo-Json -Depth 20
Invoke-RestMethod "https://<host>/.well-known/assetlinks.json" | ConvertTo-Json -Depth 20
adb shell pm verify-app-links --re-verify <verified-package>
adb shell pm get-app-links <verified-package>
```

Require the JSON comparison to be True. Compare every returned package, certificate, appID and path against the approved release inventory; the legacy Android entry must retain all three original fingerprints and the legacy iOS entry must retain /reels/*. Confirm the verified sharing apps have the required associations. Repeat the device and uninstalled-app matrix above, adding existing /reels/* links to catch legacy regressions.

See [the staging release runbook](DEEP_LINK_STAGING_RELEASE.md) for the verified Mazadat identities (including the iOS bundle's double d), exact scoped deployment commands, LiteSpeed safeguards, cache handling, and rollback. Earlier local configuration observations are historical and do not establish current live settings.
