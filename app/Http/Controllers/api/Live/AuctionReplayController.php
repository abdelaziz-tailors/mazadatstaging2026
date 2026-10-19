<?php

namespace App\Http\Controllers\api\Live;

use App\Http\Controllers\Controller;
use App\Models\AuctionRecording;
use App\Services\AuctionRecordingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class AuctionReplayController extends Controller
{
    public function show(int $id, Request $request, AuctionRecordingService $service)
    {
        abort_unless(config('recording.enabled'), 404);
        $recording = AuctionRecording::where('live_video_id', $id)->firstOrFail();
        $auction = $recording->auction;
        // Conservative access policy: only the auction organizer can replay.
        abort_unless($auction && (int) $auction->user_id === (int) auth('api')->id(), 403);
        abort_unless($auction->isEnded() && $recording->replayAvailable(), 410);
        $expires = now()->addMinutes(5)->min($recording->expires_at);
        if (!$request->has('file')) {
            $urls = [];
            foreach ($service->playbackFiles($recording) as $file) {
                $urls[] = URL::temporarySignedRoute('auction.replay', $expires,
                    ['id' => $id, 'file' => $file]);
            }
            return response()->json(['urls' => $urls, 'expires_at' => $expires])->header('Cache-Control', 'private, no-store');
        }
        abort_unless($request->hasValidSignature(), 403);
        $file = $request->query('file');
        abort_unless(is_string($file) && in_array($file, $service->playbackFiles($recording), true), 404);
        $disk = Storage::disk($recording->disk);
        if ($recording->driver === 'agora') {
            return redirect()->away($disk->temporaryUrl($file, $expires,
                ['ResponseContentType' => 'video/mp4', 'ResponseCacheControl' => 'private, no-store']))
                ->header('Cache-Control', 'private, no-store');
        }
        return $disk->response($file, null, ['Cache-Control' => 'private, no-store', 'Content-Type' => 'video/mp4']);
    }
}
