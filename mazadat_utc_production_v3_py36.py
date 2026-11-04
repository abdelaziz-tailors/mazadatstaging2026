#!/usr/bin/env python3
"""Mazadat UTC scheduling compatibility patch v2. Run in Laravel project root.
Modes: prepare, deploy, rollback. Never backfills ambiguous legacy auctions.
"""
import hashlib
import json
import os
import re
import shutil
import subprocess
import sys
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path.cwd()
STAGE = ROOT / 'storage/app/utc_global_stage_final'
FILES = {
 'app/Console/Commands/CheckRecordedVideoStart.php': 'start',
 'app/Console/Commands/CheckLiveVideoEnd.php': 'end',
 'app/Http/Controllers/Dashboard/VideoController.php': 'dashboard',
 'resources/views/dashboard/pages/videos/_form.blade.php': 'form',
 'app/Http/Controllers/api/Live/LiveVideoController.php': 'api',
 'app/Http/Requests/api/Video/AddVideoRequest.php': 'addreq',
 'app/Http/Requests/api/Video/UpdateVideoRequest.php': 'upreq',
 'app/Http/Requests/api/Video/ValidatesAuctionSchedule.php': 'trait',
}
RESOURCES = ['SingleLiveVideoResource.php', 'MyLiveVideoResource.php', 'HomeVideoResource.php', 'UserVideoFavoriteResource.php']
RESOURCE_DIR = 'app/Http/Resources/User'
for name in RESOURCES:
    FILES[f'{RESOURCE_DIR}/{name}'] = 'resource'
HELPER_PATH = 'app/Support/AuctionScheduleUtc.php'
MIGRATION_PATH = 'database/migrations/2026_11_03_150000_add_utc_schedule_columns_to_live_videos.php'

def safe_unlink(path):
    if path.exists():
        path.unlink()

def single(s, old, new, label):
    n = s.count(old)
    if n != 1:
        raise RuntimeError(f'{label}: required anchor appears {n} times: {old[:100]!r}')
    return s.replace(old, new, 1)

def sha(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()

def php_lint(p):
    r = subprocess.run(['php', '-l', str(p)], stdout=subprocess.PIPE, stderr=subprocess.PIPE, universal_newlines=True)
    if r.returncode:
        raise RuntimeError(f'PHP lint failed on {p}: {r.stdout} {r.stderr}')
    print(f'PHP PASS: {p.name}')

def helper_php():
    return r'''<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/** Legacy date/time columns remain unchanged; new schedule columns are UTC. */
final class AuctionScheduleUtc
{
    public static function fromFields(array $fields, ?string $timezone): array
    {
        // No timezone from a legacy mobile client means NO timezone assumption.
        if (! $timezone) {
            return ['schedule_start_utc' => null, 'schedule_end_utc' => null,
                'schedule_timezone' => null];
        }
        try {
            new \DateTimeZone($timezone);
            foreach (['date_start_at', 'time_start_at', 'date_end_at', 'time_end_at'] as $name) {
                if (! isset($fields[$name])) {
                    throw new \InvalidArgumentException('Incomplete auction schedule.');
                }
            }
            $startText = $fields['date_start_at'].' '.substr($fields['time_start_at'], 0, 5).':00';
            $endText = $fields['date_end_at'].' '.substr($fields['time_end_at'], 0, 5).':00';
            $start = CarbonImmutable::createFromFormat('!Y-m-d H:i:s', $startText, $timezone);
            $end = CarbonImmutable::createFromFormat('!Y-m-d H:i:s', $endText, $timezone);
            // Reject DST gaps and dates normalized by PHP.
            if (! $start || ! $end || $start->format('Y-m-d H:i:s') !== $startText
                || $end->format('Y-m-d H:i:s') !== $endText || ! $end->greaterThan($start)) {
                throw new \InvalidArgumentException('Invalid auction schedule.');
            }
            return ['schedule_start_utc' => $start->utc()->format('Y-m-d H:i:s'),
                'schedule_end_utc' => $end->utc()->format('Y-m-d H:i:s'),
                'schedule_timezone' => $timezone];
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'timezone' => ['Invalid timezone or auction schedule.'],
            ]);
        }
    }

    public static function isoUtc($value): ?string
    {
        if (! $value) { return null; }
        return CarbonImmutable::parse((string) $value, 'UTC')->utc()->format('Y-m-d\TH:i:s\Z');
    }
}
'''

def migration_php():
    return r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('live_videos', function (Blueprint $table) {
            $table->dateTime('schedule_start_utc')->nullable();
            $table->dateTime('schedule_end_utc')->nullable();
            $table->string('schedule_timezone', 64)->nullable();
        });
    }
    public function down()
    {
        Schema::table('live_videos', function (Blueprint $table) {
            $table->dropColumn(['schedule_start_utc', 'schedule_end_utc', 'schedule_timezone']);
        });
    }
};
'''

def apply(rel, s):
    kind = FILES[rel]
    if kind == 'start':
        # Production may already contain the focused recorded-auction hotfix.
        old_start = "$video->update(['status' => 'start', 'start_at' => now()]);"
        if s.count(old_start) > 1: raise RuntimeError('Unexpected duplicate start_at updates')
        s = s.replace(old_start, "$video->update(['status' => 'start']);")
        scope = "            ->where('status', 'pending')"
        n = s.count(scope)
        if n not in (1, 2): raise RuntimeError(f'Unexpected start query structure: {n} status filters')
        s = s.replace(scope, scope + "\n            ->whereNull('schedule_start_utc')")
        anchor = '        foreach ($videos as $video) {'
        insert = '''        // Explicit UTC schedules are evaluated independently of legacy wall-clock columns.
        $nowUtc = now('UTC')->format('Y-m-d H:i:s');
        $utcVideos = LiveVideo::query()
            ->where('type', 'recorded')
            ->where('status', 'pending')
            ->whereNotNull('schedule_start_utc')
            ->whereNotNull('schedule_end_utc')
            ->where('schedule_start_utc', '<=', $nowUtc)
            ->where('schedule_end_utc', '>', $nowUtc)
            ->get();
        $videos = $videos->concat($utcVideos);

'''
        s = single(s, anchor, insert + anchor, 'start UTC query')
        guard = "                if (! $video || $video->status !== 'pending') { return; }"
        guard2 = '''                if ($video->schedule_start_utc !== null) {
                    if (! $video->schedule_end_utc) { return; }
                    $actual = now('UTC');
                    $begin = \\Carbon\\CarbonImmutable::parse((string) $video->schedule_start_utc, 'UTC');
                    $finish = \\Carbon\\CarbonImmutable::parse((string) $video->schedule_end_utc, 'UTC');
                    if ($finish->lte($begin) || $actual->lt($begin) || $actual->gte($finish)) { return; }
                }
'''
        s = single(s, guard, guard + '\n' + guard2, 'start recheck')
    elif kind == 'end':
        scope = "            ->where('status', '!=', 'end')"
        n = s.count(scope)
        if n not in (1, 2): raise RuntimeError(f'Unexpected end query structure: {n} status filters')
        s = s.replace(scope, scope + "\n            ->whereNull('schedule_end_utc')")
        anchor = '        foreach ($videos as $video) {'
        insert = '''        // UTC-backed auctions use one universal end instant; legacy stay on old path.
        $utcVideos = LiveVideo::query()
            ->where('status', '!=', 'end')
            ->whereNotNull('schedule_end_utc')
            ->where('schedule_end_utc', '<=', now('UTC')->format('Y-m-d H:i:s'))
            ->get();
        $videos = $videos->concat($utcVideos);

'''
        s = single(s, anchor, insert + anchor, 'end UTC query')
        guard = "                if (! $video || $video->status === 'end') { return; }"
        guard2 = '''                if ($video->schedule_end_utc !== null &&
                    now('UTC')->lt(\\Carbon\\CarbonImmutable::parse((string) $video->schedule_end_utc, 'UTC'))) {
                    return;
                }
'''
        s = single(s, guard, guard + '\n' + guard2, 'end lock recheck')
    elif kind == 'dashboard':
        cairo = '        // Recorded auction dates entered in Cairo local time.'
        if s.count(cairo) != 2:
            raise RuntimeError('Cairo validation not found')
        for _ in range(2):
            a = s.index(cairo)
            b = s.index('\n        }\n', a) + len('\n        }\n')
            s = s[:a] + s[b:]
        # Two validators: creation and update; both forms carry a browser timezone.
        check = "            'time_end_at'         => 'required',"
        if s.count(check) != 2:
            raise RuntimeError('dashboard store/update expected two timezone rule anchors')
        s = s.replace(check, check + "\n            'timezone'            => 'required|timezone',")
        # Separate store and update at method boundary; ensure both use correct UTC arrays.
        boundary = '    public function update(Request $request, $id)'
        if s.count(boundary) != 1: raise RuntimeError('dashboard update method boundary missing')
        a, b = s.split(boundary, 1)
        snippet = '''        $utcSchedule = \\App\\Support\\AuctionScheduleUtc::fromFields(
            $request->only(['date_start_at', 'time_start_at', 'date_end_at', 'time_end_at']),
            $request->input('timezone')
        );
'''
        snippet += r'''        if ($request->input('type') === 'recorded' &&
            \Carbon\CarbonImmutable::parse($utcSchedule['schedule_start_utc'], 'UTC')->lte(now('UTC'))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'time_start_at' => 'موعد بدء المزاد يجب أن يكون في المستقبل.'
            ]);
        }
'''
        a = single(a, '        $request->validate($rules);', '        $request->validate($rules);\n' + snippet, 'dashboard create timezone')
        b = single(b, '        $request->validate($rules);', '        $request->validate($rules);\n' + snippet, 'dashboard update timezone')
        field1 = "            'time_end_at' => $request->time_end_at,"
        add1 = field1 + "\n            'schedule_start_utc' => $utcSchedule['schedule_start_utc'],\n            'schedule_end_utc' => $utcSchedule['schedule_end_utc'],\n            'schedule_timezone' => $utcSchedule['schedule_timezone'],"
        a = single(a, field1, add1, 'dashboard create UTC save')
        b = single(b, field1, add1, 'dashboard update UTC save')
        s = a + boundary + b
        if s.count("'timezone'            => 'required|timezone'") != 2:
            raise RuntimeError('dashboard timezone rules missing or duplicated')
    elif kind == 'form':
        if 'id="auction_timezone"' in s: raise RuntimeError('Dashboard form already patched')
        # The shared partial is included by the four parent forms; insert before first schedule input.
        script = r'''

{{-- The browser timezone is submitted with the schedule; UTC is never guessed from IP. --}}
<input type="hidden" name="timezone" id="auction_timezone" value="">
@if(isset($data) && !empty($data->schedule_start_utc) && !empty($data->schedule_end_utc) && !$errors->any())
<input type="hidden" id="auction_existing_start_utc" value="{{ \App\Support\AuctionScheduleUtc::isoUtc($data->schedule_start_utc) }}">
<input type="hidden" id="auction_existing_end_utc" value="{{ \App\Support\AuctionScheduleUtc::isoUtc($data->schedule_end_utc) }}">
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const zone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const zoneField = document.getElementById('auction_timezone');
    if (!zone || !zoneField) { return; }
    zoneField.value = zone;
    // Editing a UTC-backed auction: show local wall-clock inputs in this browser.
    for (const part of ['start', 'end']) {
        const el = document.getElementById('auction_existing_' + part + '_utc');
        if (!el || !el.value) { continue; }
        const date = new Date(el.value);
        if (isNaN(date.getTime())) { continue; }
        const pad = n => String(n).padStart(2, '0');
        const dateInput = document.querySelector('[name="date_' + part + '_at"]');
        const timeInput = document.querySelector('[name="time_' + part + '_at"]');
        if (dateInput) dateInput.value = [date.getFullYear(), pad(date.getMonth() + 1), pad(date.getDate())].join('-');
        if (timeInput) timeInput.value = pad(date.getHours()) + ':' + pad(date.getMinutes());
    }
});
</script>
'''
        anchor = "            {!! Form::label('date_start_at',"
        if s.count(anchor) != 1:
            raise RuntimeError('Schedule form date_start_at label anchor missing')
        # Hidden field must live within the surrounding parent <form> element.
        s = s.replace(anchor, script + '\n' + anchor, 1)
    elif kind == 'api':
        # CREATE: client-provided timezone; old clients with no timezone remain legacy.
        create = "            $data = app(\\App\\Services\\SubscriptionService::class)->createAuction(["
        s = single(s, create, "            $utcSchedule = \\App\\Support\\AuctionScheduleUtc::fromFields(\n                $request->only(['date_start_at', 'time_start_at', 'date_end_at', 'time_end_at']),\n                $request->input('timezone')\n            );\n" + create, 'api create calculate')
        # Create array only: before next 'terms_conditions' anchor.
        field = "                'time_end_at' => $request->time_end_at,"
        value = field + "\n                'schedule_start_utc' => $utcSchedule['schedule_start_utc'],\n                'schedule_end_utc' => $utcSchedule['schedule_end_utc'],\n                'schedule_timezone' => $utcSchedule['schedule_timezone'],"
        s = single(s, field, value, 'api create UTC fields')
        # UPDATE: validated() carries timezone, which is NOT a DB column; remove it.
        needle = '        $live_video->update($requestData);'
        replacement = '''        // Only recompute when a schedule field changes; preserve partial-update semantics.
        if ($request->hasAny(['date_start_at', 'time_start_at', 'date_end_at', 'time_end_at'])) {
            $combined = [
                'date_start_at' => $request->input('date_start_at', $live_video->date_start_at),
                'time_start_at' => $request->input('time_start_at', $live_video->time_start_at),
                'date_end_at' => $request->input('date_end_at', $live_video->date_end_at),
                'time_end_at' => $request->input('time_end_at', $live_video->time_end_at),
            ];
            $requestData = array_merge($requestData, \\App\\Support\\AuctionScheduleUtc::fromFields(
                $combined, $request->input('timezone', $live_video->schedule_timezone)
            ));
        }
        unset($requestData['timezone']);
        $live_video->update($requestData);'''
        s = single(s, needle, replacement, 'api update UTC fields')
    elif kind in ('addreq', 'upreq'):
        # ValidatesAuctionSchedule trait verifies the resulting time window.
        if kind == 'addreq':
            needle = "'time_end_at' => 'required|date_format:H:i',"
        else:
            needle = "'time_end_at' => 'nullable|date_format:H:i',"
        s = single(s, needle, needle + "\n            'timezone' => 'nullable|timezone',", f'{kind} timezone field')
    elif kind == 'trait':
        needle = "$timezone = config('app.timezone');"
        s = single(s, needle, "$timezone = $this->input('timezone') ?: ($auction->schedule_timezone ?? config('app.timezone'));", 'API validation timezone')
    elif kind == 'resource':
        needle = "            'time_end_at' => $this['time_end_at'],"
        if s.count(needle) == 1:
            value = needle + "\n            'schedule_start_utc' => \\App\\Support\\AuctionScheduleUtc::isoUtc($this['schedule_start_utc'] ?? null),\n            'schedule_end_utc' => \\App\\Support\\AuctionScheduleUtc::isoUtc($this['schedule_end_utc'] ?? null),\n            'schedule_timezone' => $this['schedule_timezone'] ?? null,"
            s = single(s, needle, value, f'{rel} UTC response')
        else:
            # Some resource variants may not expose dates; leave untouched.
            print(f'INFO: {rel} does not expose raw schedule date/time, skipped.')
    return s

def check_root():
    if not (ROOT / 'artisan').exists() or not (ROOT / 'app/Models/LiveVideo.php').exists():
        raise RuntimeError('Run from ~/public_html/mazadat (Laravel project root).')

def prepare():
    check_root()
    STAGE.mkdir(parents=True, exist_ok=True)
    result = {}
    originals = {}
    for rel in FILES:
        source = ROOT / rel
        if not source.is_file(): raise RuntimeError(f'Expected source missing: {rel}')
        old = source.read_text()
        output = apply(rel, old)
        result[rel] = output
        originals[rel] = sha(source)
    if (ROOT / HELPER_PATH).exists() or (ROOT / MIGRATION_PATH).exists():
        raise RuntimeError('UTC helper/migration already exists; stop to avoid overwrite.')
    result[HELPER_PATH] = helper_php()
    result[MIGRATION_PATH] = migration_php()
    for rel, code in result.items():
        dest = STAGE / rel
        dest.parent.mkdir(parents=True, exist_ok=True)
        dest.write_text(code)
        if rel.endswith('.php') and not rel.endswith('.blade.php'):
            php_lint(dest)
    (STAGE / 'manifest.json').write_text(json.dumps({'originals': originals, 'generated': list(result)}, indent=2))
    print('\nPREPARED: Production remains unchanged. Files:', len(result))
    print('New records with explicit timezone use UTC. Legacy records remain unconverted.')
    print('No bidding/award/item-state code is modified.')
    print('CAUTION: legacy auctions without timezone still use previous legacy logic; their clock may be wrong.')

def deploy():
    check_root()
    manifest_file = STAGE / 'manifest.json'
    if not manifest_file.exists(): raise RuntimeError('Run prepare first.')
    manifest = json.loads(manifest_file.read_text())
    for rel, expected in manifest['originals'].items():
        if sha(ROOT / rel) != expected: raise RuntimeError(f'Production file changed since prepare: {rel}')
    if (ROOT / HELPER_PATH).exists() or (ROOT / MIGRATION_PATH).exists():
        raise RuntimeError('Helper or migration already exists; stop.')
    for rel in manifest['generated']:
        staged = STAGE / rel
        if not staged.is_file(): raise RuntimeError(f'Staged file missing: {rel}')
        if rel.endswith('.php') and not rel.endswith('.blade.php'): php_lint(staged)
    stamp = datetime.now(timezone.utc).strftime('%Y%m%d_%H%M%S')
    backup_dir = ROOT / 'storage/app' / ('utc_deploy_backup_' + stamp)
    for rel in manifest['originals']:
        dest = backup_dir / rel
        dest.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(ROOT / rel, dest)
    (backup_dir / 'manifest.json').write_text(json.dumps(manifest, indent=2))
    print('BACKUP:', backup_dir)
    # Safe additive migration runs before code switch; rollback never drops populated columns.
    migration_dst = ROOT / MIGRATION_PATH
    helper_dst = ROOT / HELPER_PATH
    migration_dst.parent.mkdir(parents=True, exist_ok=True)
    helper_dst.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(STAGE / MIGRATION_PATH, migration_dst)
    try:
        subprocess.run(['php', 'artisan', 'migrate', '--path='+MIGRATION_PATH, '--force'], check=True)
    except Exception:
        safe_unlink(migration_dst)
        raise
    installed = []
    try:
        for rel in manifest['generated']:
            if rel == MIGRATION_PATH: continue
            dest = ROOT / rel
            dest.parent.mkdir(parents=True, exist_ok=True)
            tmp = dest.with_name(dest.name + '.utc_patch_tmp')
            shutil.copy2(STAGE / rel, tmp)
            os.replace(tmp, dest)
            installed.append(rel)
        subprocess.run(['php', 'artisan', 'optimize:clear'], check=True)
    except Exception:
        for rel in installed:
            dest = ROOT / rel
            if rel in manifest['originals']:
                shutil.copy2(backup_dir / rel, dest)
            else:
                safe_unlink(dest)
        print('Code restored after error; additive DB columns remain.')
        raise
    (STAGE / 'last_deploy.txt').write_text(str(backup_dir))
    print('DEPLOYED. Backup:', backup_dir)
    print('Check the API response and a NEW test auction. Do not reinterpret old auction dates.')
    print('Flutter must send timezone and display schedule_*_utc locally.')

def rollback():
    check_root()
    marker = STAGE / 'last_deploy.txt'
    if not marker.exists(): raise RuntimeError('No deployment marker found.')
    backup_dir = Path(marker.read_text().strip())
    manifest = json.loads((backup_dir / 'manifest.json').read_text())
    for rel in manifest['originals']:
        source = backup_dir / rel
        if not source.exists(): raise RuntimeError(f'Missing rollback source: {source}')
    for rel in manifest['originals']:
        shutil.copy2(backup_dir / rel, ROOT / rel)
    safe_unlink(ROOT / HELPER_PATH)
    subprocess.run(['php', 'artisan', 'optimize:clear'], check=True)
    print('ROLLED BACK CODE. Additive UTC columns + migration file kept safely.')

if __name__ == '__main__':
    try:
        mode = sys.argv[1] if len(sys.argv) > 1 else 'prepare'
        if mode == 'prepare': prepare()
        elif mode == 'deploy': deploy()
        elif mode == 'rollback': rollback()
        else: raise RuntimeError('Use: prepare | deploy | rollback')
    except Exception as e:
        print('STOPPED:', e, file=sys.stderr)
        sys.exit(1)
