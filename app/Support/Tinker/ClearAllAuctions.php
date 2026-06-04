<?php

namespace App\Support\Tinker;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClearAllAuctions
{
    private const CONFIRMATION_TOKEN = 'DELETE_ALL_AUCTIONS';
    private const BATCH_SIZE = 500;

    /**
     * Usage from tinker:
     * 1) Preview:
     *    \App\Support\Tinker\ClearAllAuctions::run(dryRun: true);
     *
     * 2) Execute:
     *    \App\Support\Tinker\ClearAllAuctions::run(confirm: 'DELETE_ALL_AUCTIONS');
     */
    public static function run(?string $confirm = null, bool $dryRun = false): array
    {
        $counts = self::counts();

        if ($dryRun) {
            return [
                'mode' => 'dry-run',
                'confirmation_token_required' => self::CONFIRMATION_TOKEN,
                'counts' => $counts,
            ];
        }

        if ($confirm !== self::CONFIRMATION_TOKEN) {
            throw new RuntimeException(
                'Deletion blocked. Pass confirm: "'.self::CONFIRMATION_TOKEN.'" to execute.'
            );
        }

        $deleted = [
            'user_gifts' => 0,
            'live_video_likes' => 0,
            'live_video_users' => 0,
            'live_video_items' => 0,
            'live_videos' => 0,
        ];

        DB::transaction(function () use (&$deleted): void {
            DB::table('live_videos')
                ->select('id')
                ->orderBy('id')
                ->chunkById(self::BATCH_SIZE, function ($rows) use (&$deleted): void {
                    $ids = $rows->pluck('id')->all();
                    if ($ids === []) {
                        return;
                    }

                    $deleted['user_gifts'] += DB::table('user_gifts')
                        ->whereIn('video_id', $ids)
                        ->delete();

                    $deleted['live_video_likes'] += DB::table('live_video_likes')
                        ->whereIn('live_video_id', $ids)
                        ->delete();

                    $deleted['live_video_users'] += DB::table('live_video_users')
                        ->whereIn('live_video_id', $ids)
                        ->delete();

                    $deleted['live_video_items'] += DB::table('live_video_items')
                        ->whereIn('live_video_id', $ids)
                        ->delete();

                    $deleted['live_videos'] += DB::table('live_videos')
                        ->whereIn('id', $ids)
                        ->delete();
                });
        });

        return [
            'mode' => 'delete',
            'deleted' => $deleted,
            'before' => $counts,
            'after' => self::counts(),
        ];
    }

    private static function counts(): array
    {
        $liveVideosSubQuery = DB::table('live_videos')->select('id');

        return [
            'live_videos' => DB::table('live_videos')->count(),
            'live_video_items' => DB::table('live_video_items')
                ->whereIn('live_video_id', $liveVideosSubQuery)
                ->count(),
            'live_video_likes' => DB::table('live_video_likes')
                ->whereIn('live_video_id', DB::table('live_videos')->select('id'))
                ->count(),
            'live_video_users' => DB::table('live_video_users')
                ->whereIn('live_video_id', DB::table('live_videos')->select('id'))
                ->count(),
            'user_gifts' => DB::table('user_gifts')
                ->whereIn('video_id', DB::table('live_videos')->select('id'))
                ->count(),
        ];
    }
}
