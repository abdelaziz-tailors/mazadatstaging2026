# Content layout and auction schedule QA

## Content screens

- API: `GET /api/about`, `/api/terms`, `/api/privacy` (APISettings headers apply).
- Source: `pages` IDs 1, 2, 3 respectively; localized JSON `name` and `description`, plus `image`. Dashboard PageController updates these fields. The legacy PolicyPageSeeder uses different fields and is not the runtime source.
- API controller: `app/Http/Controllers/api/Shared/PageController.php`; serialization: `app/Http/Resources/PageResource.php`. Neither adds HTML or layout. The bundled SQL snapshot contains plain text with paragraph breaks. Current production content was not queried.
- Confirmed web defects: `/about`, `/terms`, `/privacy` templates forced Arabic direction in English and used raw line-break spacing instead of paragraphs. Updated `resources/views/front/pages/{about,terms,privacy}.blade.php` to render escaped paragraphs with 16px spacing, collapse blank-line runs, retain single line breaks, use locale direction/start alignment, wrap long content, and reduce header spacing. Existing responsive width and readable line height remain. No stored text or legal wording changed; HTML remains escaped.
- No Dart, Swift, or mobile project is present. The mobile rendering cause cannot be confirmed and the mobile UI is not claimed fixed.

### Required mobile implementation

For these API responses, render `data.description` as **plain text**, not HTML. Normalize CRLF/CR to LF; trim outer whitespace and split paragraphs with `\n[\t ]*\n(?:[\t ]*\n)*`. Do not strip words, punctuation, or internal single line breaks. Do not globally replace whitespace. If production responses contain actual HTML, retain the existing approved sanitizer/allowlist and investigate those payloads before selecting an HTML renderer.

Flutter: use one `SingleChildScrollView`, `Padding(EdgeInsets.all(16))`, and a `Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.stretch)`. Wrap in `Directionality(textDirection: locale.languageCode == 'ar' ? TextDirection.rtl : TextDirection.ltr)`. Render each paragraph with `Text(paragraph, textAlign: TextAlign.start, style: TextStyle(fontSize: 16, height: 1.7))`; insert a 16px `SizedBox` only between paragraphs. Remove fixed content heights, `Expanded`/`Spacer` inside the scroll view, nested scrolling, justified alignment, and reserved image height when the image is absent or fails. Images should use their natural aspect ratio and fit available width.

iOS: use a content-sized UILabel (`numberOfLines = 0`) in a vertical UIStackView inside one UIScrollView, constrained to its contentLayoutGuide and width to frameLayoutGuide minus 32pt. Use 16pt font and NSMutableParagraphStyle with `paragraphSpacing = 16`, `lineHeightMultiple = 1.7`, natural alignment, and baseWritingDirection rightToLeft for Arabic/leftToRight for English. Remove fixed text heights and collapse absent image views.

If the app loads the web routes in WKWebView/Flutter WebView, the template fixes apply. For an app-generated wrapper, HTML-escape each plain-text paragraph, use `<html lang="ar" dir="rtl">` (or English/LTR), viewport `width=device-width, initial-scale=1`, and CSS `body{margin:0;padding:16px;font-size:16px;line-height:1.7;text-align:start;overflow-wrap:anywhere} p{margin:0 0 16px} img{max-width:100%;height:auto}`. Use a single scrolling surface and content-sized height when embedded. Keep scripts disabled for static legal content and preserve existing navigation/sanitization rules.

## Auction scheduling

- Create: `POST /api/live/add`, AddVideoRequest, LiveVideoController::add.
- Update: `POST /api/live/update/{id}`, UpdateVideoRequest, LiveVideoController::update; existing ownership and pending-only checks remain.
- Storage: `live_videos.date_start_at`, `date_end_at` (DATE), `time_start_at`, `time_end_at` (TIME).
- Contract unchanged: dates `YYYY-MM-DD`, times 24-hour `HH:mm`, without offsets. These fields represent **Asia/Kuwait**, configured in `config/app.php`, not the device timezone. Clients must convert a selected instant into Kuwait time before splitting/serializing; never submit UTC fields or device-local fields without conversion.
- Shared ValidatesAuctionSchedule trait combines the fields into immutable timestamps before chronological checks, after format validation. Start must be at or after authoritative server now; end must be strictly later. Partial scheduling updates merge stored values (including MySQL seconds); metadata-only updates do not revalidate old schedules. Non-pending auctions remain non-editable under the controller's existing rules.
- Invalid input fails before controller writes, uploads, or subscription deductions. Existing HTTP 200 validation envelope remains: `code:200`, `success:false`, `message`, and field-keyed `errors`. Chronology errors use `time_start_at` / `time_end_at`; malformed fields retain their own keys. Messages added to `resources/lang/{en,ar}/validation.php`.
- The separately declared `/api/live/update-time/{id}` route points to an absent controller method in the existing code. It is not an implemented scheduling path; do not use it in mobile integration.

### Required picker restrictions

Flutter: initialize timezone data and use `tz.getLocation('Asia/Kuwait')`. Compare combined selections with `tz.TZDateTime.now(location)` or a server-clock-adjusted instant. `showDatePicker.firstDate` is the Kuwait calendar date today. `showTimePicker` does not enforce a combined minimum: after selection, rebuild `tz.TZDateTime(location, year, month, day, hour, minute)` and reject values before now; compare end strictly greater than start. Recheck both at submission and show Laravel field errors. Round a suggested minimum UP to the next minute when now has seconds.

iOS: configure UIDatePicker.timeZone to `TimeZone(identifier: "Asia/Kuwait")`, use `.dateAndTime` with minuteInterval 1, and set minimumDate to current time rounded UP to a minute. End minimumDate is start plus one minute for this minute-resolution contract. Recompute limits on foreground/submission. When serializing, DateFormatter must use `en_US_POSIX`, Gregorian calendar, Kuwait timezone, and `yyyy-MM-dd` / `HH:mm`. For separate date/time pickers, combine in that calendar/timezone before comparison. Backend enforcement is independent of picker behavior.

## Verification

Targeted PHPUnit tests: AuctionCreationValidationTest and PolicyContentLayoutTest, SQLite in-memory only. Includes actual create endpoint persistence and subscription checks, actual content endpoints in both languages, escaped view rendering, partial updates, already-started rejection, malformed/missing fields, and UTC/Kuwait midnight boundaries. No migrations against application databases or production data changes.

Runtime: isolated official PHP 8.4.26 (SHA-256 verified), required by the installed vendor platform check; default PHP 8.2 cannot load these dependencies. Existing Laravel/vendor PHP 8.4 deprecation notices remain unrelated to this patch. Device visual QA remains required on the three mobile screens, with long content, Arabic/English, large text, and narrow widths.

Result: **11 tests passed, 247 assertions**. `git diff --check` passed. The tests' legacy TranslationHelper auto-generated unrelated translation entries; those test side effects were removed.
