# Staging release: live association repair

## Findings and scope

The checked-out branch was main at 99504a6; the share-link work was uncommitted at the start of this follow-up. The old public files have already been moved to config/deep_link_associations in the working tree. LiteSpeed/Apache's ordinary !-f front-controller rule leaves existing files untouched, so stale deployed files bypass Laravel. An upload-only release can leave deleted repository files in the live document root. The actual production document root, deployment state and CDN rules cannot be inspected from this workspace; those remain operator checks. Requests to the supplied production host failed from this environment, so no live HTTPS success is claimed.

public/.htaccess now routes only the three exact association URLs to index.php before the existing-file check. A separate LiteSpeed rewrite environment flag bypasses its page cache; Laravel also returns Cache-Control: no-store and X-LiteSpeed-Cache-Control: no-cache. See [LiteSpeed cache controls](https://docs.litespeedtech.com/lscache/devguide/controls/). Other URLs and certificate-validation files are untouched. The rule needs to be in the effective public document root; a parent/static context/CDN can still intercept requests before it. Existing cache objects require an operator-managed purge. No global cache purge or web-server restart is prescribed.

The existing .cpanel.yml runs composer update and php artisan migrate, and does not copy this patch or remove stale public files. **Do not invoke that task for this release.** No dependencies or migrations are required. It is left unchanged because it governs unrelated deployments.

## Required staging settings

A staging hostname, document root, SSH access and release-build entitlement inventory have not been supplied. Do not substitute the production host for staging. Preserve all existing single-app settings, additional apps, store links and custom schemes. Merge these verified entries into existing additional-app arrays using the server's normal secret-safe editor; if the arrays are empty, their complete values are:

```dotenv
DEEP_LINK_ANDROID_APPS='[{"package_name":"com.anaamworld.app","sha256_cert_fingerprints":["95:6F:EB:6B:22:37:B4:53:1A:94:9D:68:E3:BC:F5:F6:0C:DC:A6:38:84:B8:A4:5D:4B:F8:48:C8:68:E5:AC:1C"]}]'
DEEP_LINK_IOS_APPS='[{"appID":"S2QF6L6CJZ.com.anaamworldd.app","paths":["/u/*","/share/auction/*","/share/lot/*"]}]'
```

The double d in com.anaamworldd.app is intentional and user-verified. Do not replace an older configured com.anaamworld.app iOS association: preserve it separately alongside the verified new entry. The legacy com.vllog.app certificates and S2QF6L6CJZ.com.vlogs.app /reels/* are preserved in the shipped legacy configuration. DEEP_LINK_BASE_URL must use the real staging HTTPS origin for staging previews. Keep all existing DEEP_LINK_PATHS entries and add the three sharing patterns if absent. Native apps must actually authorize the staging host; server JSON alone cannot change mobile entitlements or handlers.

## Exact manual staging release (Bash on the server)

Obtain the committed release SHA from git log after this patch is committed. Set the following variables to operator-verified values; the backup directory must be private and outside every web document root. SOURCE is a checkout containing the committed patch; STAGING_APP is the Laravel root whose public directory is the effective staging document root. Commands intentionally avoid git checkout in a live directory, composer, migrations, global cache clearing and the cPanel task.

```bash
set -euo pipefail
SOURCE='<verified-source-checkout>'
STAGING_APP='<verified-staging-laravel-root>'
STAGING_ORIGIN='https://<verified-staging-host>'
RELEASE_SHA='<committed-release-sha>'
BACKUP='<new-private-backup-directory-outside-webroots>'
PHP_BIN='<verified-php-8.4-or-newer-binary>'
SOURCE=$(realpath "$SOURCE")
STAGING_APP=$(realpath "$STAGING_APP")
test -f "$STAGING_APP/artisan"
test -f "$STAGING_APP/public/index.php"
test -d "$STAGING_APP/vendor"
git -C "$SOURCE" cat-file -e "$RELEASE_SHA^{commit}"
# Operator checkpoint: confirm STAGING_APP and BACKUP against the staging vhost.
# Stop if either resolves to production or BACKUP is inside a document root.
umask 077
mkdir -- "$BACKUP"
BACKUP=$(realpath "$BACKUP")
cd "$STAGING_APP"
files=(
  public/.htaccess routes/web.php config/deep_links.php
  app/Http/Controllers/Web/DeepLinkAssociationController.php
  app/Http/Controllers/Web/SharedAuctionController.php
  resources/views/front/pages/shared-auction.blade.php
  config/deep_link_associations/assetlinks.json
  config/deep_link_associations/apple-app-site-association.json
)
printf '%s\n' "${files[@]}" > "$BACKUP/release-files.txt"
: > "$BACKUP/existing-files.txt"
: > "$BACKUP/new-files.txt"
for f in "${files[@]}"; do
  if test -e "$f"; then printf '%s\n' "$f" >> "$BACKUP/existing-files.txt";
  else printf '%s\n' "$f" >> "$BACKUP/new-files.txt"; fi
done
# Preserve obsolete static copies too, including an optional root AASA file.
for f in public/.well-known/assetlinks.json public/.well-known/apple-app-site-association public/apple-app-site-association; do
  if test -e "$f"; then printf '%s\n' "$f" >> "$BACKUP/existing-files.txt"; fi
done
tar -czf "$BACKUP/files-before.tgz" -T "$BACKUP/existing-files.txt"
cp -p -- .env "$BACKUP/env-before"
# Extract only reviewed application files, never .env/vendor or unrelated code.
git -C "$SOURCE" archive "$RELEASE_SHA" -- "${files[@]}" | tar -x -C "$STAGING_APP"
# Verify all legacy entries are present in the private config files BEFORE proceeding.
"$PHP_BIN" -r 'foreach (["config/deep_link_associations/assetlinks.json", "config/deep_link_associations/apple-app-site-association.json"] as $p) { json_decode(file_get_contents($p), true, 512, JSON_THROW_ON_ERROR); }'
# If deployed legacy files differ from those shipped, stop and reconcile every
# association into the private config before moving anything. Never discard entries.
for f in public/.well-known/assetlinks.json public/.well-known/apple-app-site-association public/apple-app-site-association; do
  if test -e "$f"; then
    mkdir -p -- "$BACKUP/retired/$(dirname "$f")"
    mv -- "$f" "$BACKUP/retired/$f"
  fi
done
# Merge the verified settings above; do not print or replace unrelated secrets.
"${EDITOR:-vi}" .env
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:clear
```

Purge only the three association URLs through the actual hosting/CDN cache interface, using the provider's documented procedure. There is no verified cache-control CLI or server credential here, so none is invented. Verify their unmodified URLs after purge; cache-busting query strings alone do not prove association verification will work. Check response Age, X-LiteSpeed-Cache and any CDN cache headers. Require no stale HIT. If static bodies persist, check the effective vhost document root, parent/.well-known .htaccess files, static contexts, CDN overrides and whether .htaccess rewrites are enabled. Do not restart or purge unrelated services as a guess.

## Staging verification (same shell)

Use real public fixture IDs in the staging database; do not create or change records for this task. curl deliberately omits -L. Require HTTP 200 with no Location header for all six routes and JSON content type for associations. Require Cache-Control containing no-store and X-LiteSpeed-Cache-Control: no-cache on associations. Repeat association fetches without query strings after purge and compare bodies.

```bash
PROFILE_ID='<existing-public-profile-id>'
AUCTION_ID='<existing-public-auction-id>'
LOT_ID='<existing-current-public-lot-id>'
for path in /.well-known/assetlinks.json /.well-known/apple-app-site-association /apple-app-site-association "/share/auction/$AUCTION_ID" "/share/lot/$LOT_ID" "/u/$PROFILE_ID"; do
  curl --silent --show-error --max-time 30 --dump-header - "$STAGING_ORIGIN$path"
done
curl --silent --show-error --fail "$STAGING_ORIGIN/.well-known/apple-app-site-association" > "$BACKUP/aasa-well-known.json"
curl --silent --show-error --fail "$STAGING_ORIGIN/apple-app-site-association" > "$BACKUP/aasa-root.json"
cmp "$BACKUP/aasa-well-known.json" "$BACKUP/aasa-root.json"
curl --silent --show-error --fail "$STAGING_ORIGIN/.well-known/assetlinks.json" > "$BACKUP/assetlinks.json"
"$PHP_BIN" -r '
$a=json_decode(file_get_contents($argv[1]),true,512,JSON_THROW_ON_ERROR);
$i=json_decode(file_get_contents($argv[2]),true,512,JSON_THROW_ON_ERROR);
$fp="95:6F:EB:6B:22:37:B4:53:1A:94:9D:68:E3:BC:F5:F6:0C:DC:A6:38:84:B8:A4:5D:4B:F8:48:C8:68:E5:AC:1C";
$ok=false; foreach($a as $x) { if(($x["target"]["package_name"]??"")==="com.anaamworld.app" && in_array($fp,$x["target"]["sha256_cert_fingerprints"]??[],true)) $ok=true; }
if(!$ok) throw new RuntimeException("Missing verified Android identity");
$ok=false; foreach($i["applinks"]["details"] as $x) { if($x["appID"]==="S2QF6L6CJZ.com.anaamworldd.app" && !array_diff(["/u/*","/share/auction/*","/share/lot/*"],$x["paths"])) $ok=true; }
if(!$ok) throw new RuntimeException("Missing verified iOS identity/paths");
foreach(json_decode(file_get_contents("config/deep_link_associations/assetlinks.json"),true) as $old) {
  $ok=false; foreach($a as $x) { if($x["target"]["package_name"]===$old["target"]["package_name"] && !array_diff($old["target"]["sha256_cert_fingerprints"],$x["target"]["sha256_cert_fingerprints"])) $ok=true; }
  if(!$ok) throw new RuntimeException("Missing legacy Android identity/certificates");
}
foreach(json_decode(file_get_contents("config/deep_link_associations/apple-app-site-association.json"),true)["applinks"]["details"] as $old) {
  $ok=false; foreach($i["applinks"]["details"] as $x) { if($x["appID"]===$old["appID"] && !array_diff($old["paths"],$x["paths"])) $ok=true; }
  if(!$ok) throw new RuntimeException("Missing legacy iOS identity/paths");
}
echo "Verified sharing and preserved legacy identities/paths\n";
' "$BACKUP/assetlinks.json" "$BACKUP/aasa-root.json"
```

Inspect OG metadata/canonical IDs and fetch images without authentication. Profile retains its existing redirect behavior and metadata. Then run Android `adb shell pm verify-app-links --re-verify com.anaamworld.app` and `adb shell pm get-app-links com.anaamworld.app`. Test links from Messages/Mail on signed Android/iOS builds with the staging host authorized, cold/foreground/background/logged-out states, ended auctions, transferred originals (unavailable) and copies (new lot/current parent IDs). Verify /reels/* still works. Uninstall and verify fallback/store links. Mobile source remains unavailable here.

## Production gate and rollback

Production changes require explicit approval after staging HTTP/TLS/cache checks, verified legacy inventory, rollback backup and physical-device sign-off. Use the same pinned reviewed release, scope and backup procedure with separately confirmed production paths; do not use the old cPanel task. No production command was executed by this task.

For staging rollback, use the saved shell variables and backup; confirm the target is still staging. Restore only the reviewed files and old environment, remove only files that were absent before, and regenerate configuration. This also restores any prior public static files and therefore may restore the original known association issue; that is an emergency code rollback, not a claim of working deep links.

```bash
cd "$STAGING_APP"
# new-files.txt was generated from the fixed release allowlist, not user input.
while IFS= read -r f; do
  case "$f" in
    public/.htaccess|routes/web.php|config/deep_links.php|app/Http/Controllers/Web/DeepLinkAssociationController.php|app/Http/Controllers/Web/SharedAuctionController.php|resources/views/front/pages/shared-auction.blade.php|config/deep_link_associations/assetlinks.json|config/deep_link_associations/apple-app-site-association.json) rm -f -- "$STAGING_APP/$f" ;;
    *) echo 'Unexpected rollback path; stop' >&2; exit 1 ;;
  esac
done < "$BACKUP/new-files.txt"
tar -xzf "$BACKUP/files-before.tgz" -C "$STAGING_APP"
cp -p -- "$BACKUP/env-before" "$STAGING_APP/.env"
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:clear
```

Purge the same three URLs through the verified cache interface and repeat HTTP/legacy checks. Backups contain secrets: keep them private under the operator's retention policy. No database rollback is needed because this release performs no database changes.

## Local validation result

13 tests / 125 assertions passed on PHP 8.4.26 using an isolated in-memory SQLite connection. PHP syntax and git diff checks passed. Direct local HTTP checks with the verified additive app settings returned 200 application/json, no redirects, Cache-Control: no-store, private and X-LiteSpeed-Cache-Control: no-cache on all three association URLs. The verified Android certificate was present and the AASA bodies matched with the exact double-d iOS identity and all three share paths. These checks use PHP's development server and do not validate LiteSpeed rewrite execution or staging TLS/CDN behavior. Profile/auction/lot responses and edge cases were exercised by the focused feature suite; real staging content IDs and physical devices remain required.
