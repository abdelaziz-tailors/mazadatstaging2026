<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\UserDataResource;
use App\Http\Resources\User\UserMainDataResource;
use App\Http\Resources\User\VideoCommentResource;
use App\Models\Car;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\Order;
use App\Models\Provider;
use App\Models\TripRequest;
use App\Transformers\CarsTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FirebaseController extends Controller
{
    private $database;

    public function __construct()
    {
        $this->database = \App\Services\FirebaseService::connect();
    }

    public function create(LiveVideo $video)
    {
        $this->database
            ->getReference('live/'.$video->id)
            ->set([
                'status' =>$video->status,
                'view' =>0

            ]);

        return response()->json('blog has been created');
    }


    public function create_item(LiveVideoItem $item)
    {
        $this->database
            ->getReference('live/'.$item->live_video_id.'/item/'.$item->id)
            ->update([
                'status' =>$item->status
            ]);

        return response()->json('blog has been created');
    }


    public function AddAuctions($item)
    {
        // Re-read current state: never publish the callback's potentially stale record.
        $this->syncAuctionBidState($item->video_id, $item->live_video_item_id);
    }


    public function removeAuctionBid($auctionId, $itemId, $bidId)
    {
        $this->syncAuctionBidState($auctionId, $itemId);
    }

    public function syncAuctionBidState($auctionId, $itemId)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($auctionId, $itemId) {
            $item = LiveVideoItem::without('pieces')->whereKey($itemId)->lockForUpdate()->first();
            $auction = $item ? LiveVideo::whereKey($item->live_video_id)->sharedLock()->first() : null;
            if (! $item || ! $auction || $item->deleted_at || $auction->deleted_at
                || (int) $auction->id !== (int) $auctionId) { return; }
            $state = app(\App\Services\AuctionBidService::class)->state($item, $auction);
            $bids = \App\Models\VideoComment::where('live_video_item_id', $itemId)->where('video_id', $auctionId)
                ->whereNull('comment_id')->lockForUpdate()->get();
            $history = [];
            foreach ($bids as $bid) {
                $history[$bid->id] = [
                    'id' => $bid->id, 'comment' => $bid->comment, 'date' => $bid->created_at,
                    'user' => new UserMainDataResource($bid->user_Video),
                ];
            }
            $payload = $state['price'] + [
                'highest_bid_id' => $state['highest'] ? $state['highest']->id : null,
                'highest_bidder_id' => $state['highest'] ? $state['highest']->user_id : null,
            ];
            $reference = $this->database->getReference('live/'.$auctionId.'/item/'.$itemId);
            $this->database->runTransaction(function ($transaction) use ($reference, $item, $auction, $history, $payload) {
                $remote = $transaction->snapshot($reference)->getValue();
                // Do not recreate a removed, closed auction/item node.
                if ($remote === null && ($auction->status !== 'start' || $item->status !== 'working')) { return; }
                $remote = is_array($remote) ? $remote : [];
                if (isset($remote['bid_state_version']) && (int) $remote['bid_state_version'] >= (int) $item->bid_state_version) { return; }
                $remote['auctions'] = $history ?: null;
                $remote['bid_state'] = $payload;
                $remote['bid_state_version'] = (int) $item->bid_state_version;
                // ETag conditional set rejects a delayed request if anything changed meanwhile.
                $transaction->set($reference, $remote);
            });
            LiveVideoItem::whereKey($itemId)->update(['bid_state_synced_version' => $item->bid_state_version]);
        }, 3);
    }

    public function UserAuctions($item)
    {

//        dd($item);

        $this->database
            ->getReference('live/'.$item->video_id.'/item/'.$item->live_video_item_id.'/user_take_item/'.$item->id)
            ->set([
                'id' => $item->id ??'',
                'comment' => $item->comment ?? '-',
                'date' => $item->created_at ?? '-',
                'user'=> New UserMainDataResource($item->user_Video),
            ]);

        return response()->json('blog has been created');
    }





    public function ChangeLiveStatus($live_id, $status)
    {
        $this->database->getReference('live/' . $live_id)
            ->update([
                'status'    => $status
            ]);
    }

    public function LiveCount($live_id, $count)
    {
        $this->database->getReference('live/' . $live_id)
            ->update([
                'view'    => $count
            ]);
    }




    public function ChangeLiveItemStatus($live_id,$item_id, $status)
    {
        $this->database
        ->getReference('live/'.$live_id.'/item/'.$item_id)
            ->update([
                'status'    => $status
            ]);
    }

    public function removeLiveItem($live_id,$item_id)
    {
        $this->database->getReference('live/'.$live_id.'/item/'.$item_id)->remove();
    }
    public function removeLive($video_id)
    {

        $this->database->getReference('live/'.$video_id)->remove();

        return response()->json('live has been remove');
    }


}
