<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SharedAuctionController extends Controller
{
    public function auction(string $id)
    {
        // Public auction search includes upcoming, live and archived auctions.
        $auction = LiveVideo::query()->findOrFail($id);

        return $this->landing($auction, $auction, 'auction');
    }

    public function lot(string $id)
    {
        // Match currentVideoItems: transferred originals are no longer listed.
        $lot = LiveVideoItem::without('pieces')->notTransferredAway()
            ->whereHas('videoLive')->with('videoLive')->findOrFail($id);

        return $this->landing($lot, $lot->videoLive, 'lot');
    }

    private function landing($content, LiveVideo $auction, string $type)
    {
        $base = rtrim((string) (config('deep_links.base_url') ?: config('app.url')), '/');
        $shareUrl = $base . '/share/' . $type . '/' . $content->id;
        $arabic = app()->getLocale() === 'ar';
        $title = ($arabic ? $content->title_ar : $content->title)
            ?: ($content->title_ar ?: $content->title) ?: ucfirst($type) . ' #' . $content->id;
        $description = ($arabic ? $content->information_ar : $content->information)
            ?: ($content->information_ar ?: $content->information) ?: 'View this ' . $type . ' in Mazadat.';
        $description = Str::limit(trim(strip_tags($description)), 240);
        $images = json_decode((string) $content->image, true);
        $image = is_array($images) ? reset($images) : $content->image;
        $imageUrl = null;
        if (is_string($image) && $image !== '') {
            $imageUrl = preg_match('~^https?://~i', $image) ? $image : Storage::disk('public')->url($image);
            if (!preg_match('~^https?://~i', $imageUrl)) {
                $imageUrl = $base . '/' . ltrim($imageUrl, '/');
            }
        }
        $template = (string) config('deep_links.' . $type . '_app_url');
        $appUrl = str_replace(['{id}', '{auction_id}'], [(string) $content->id, (string) $auction->id], $template);

        return response()->view('front.pages.shared-auction', [
            'title' => $title, 'description' => $description, 'imageUrl' => $imageUrl,
            'shareUrl' => $shareUrl, 'appUrl' => $appUrl ?: $shareUrl,
            'ended' => $auction->isEnded(),
            'iosStoreUrl' => config('deep_links.ios_store_url'),
            'androidStoreUrl' => config('deep_links.android_store_url'),
        ])->header('Cache-Control', 'no-store');
    }
}
